<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Job;
use App\Models\Employer;

/**
 * Moderation for employer-submitted jobs.
 *
 * Status lifecycle (jobs.status):
 *   draft ─submit→ pending_review ─admin approve→ published ⇄ paused → closed / archived
 *                        └─admin reject→ rejected        published ─admin take down→ taken_down
 * Employers with a trust score >= 80 skip the queue and are published immediately.
 */
class JobApprovalService
{
    public const AUTO_APPROVE_SCORE = 80;

    /** Every status the application uses; the DB enum must contain all of them. */
    public const ALL_STATUSES = ['draft', 'pending_review', 'published', 'paused', 'closed', 'archived', 'rejected', 'taken_down'];

    /**
     * Submit a job for publication: auto-publish trusted employers, otherwise queue for admin review.
     * Returns the resulting status ('published' or 'pending_review').
     */
    public function handle(Job $job, Employer $employer): string
    {
        self::ensureStatusEnum();
        $db = Database::getInstance();

        $risk = $db->fetchOne('SELECT score FROM employer_risk_scores WHERE employer_id = :id', ['id' => $employer->id]);
        $score = (int)($risk['score'] ?? 0);

        if ($score >= self::AUTO_APPROVE_SCORE) {
            $job->status = 'published';
            $job->save();
            return 'published';
        }

        $job->status = 'pending_review';
        $job->save();

        $jobId = (int)($job->attributes['id'] ?? $job->id ?? 0);
        $alreadyQueued = $db->fetchOne(
            "SELECT id FROM job_review_queue WHERE job_id = :job_id AND status = 'pending' LIMIT 1",
            ['job_id' => $jobId]
        );
        if (!$alreadyQueued) {
            $db->query(
                'INSERT INTO job_review_queue (job_id, employer_id, review_reason) VALUES (:job_id, :employer_id, :reason)',
                [
                    'job_id' => $jobId,
                    'employer_id' => $employer->id,
                    'reason' => $score > 0 ? 'Trust score below ' . self::AUTO_APPROVE_SCORE : 'No trust score available',
                ]
            );
        }

        return 'pending_review';
    }

    /** Close the open review-queue entry when an admin decides on a job. */
    public static function resolveQueue(int $jobId, string $decision, ?int $reviewerId, ?string $comments = null): void
    {
        try {
            Database::getInstance()->query(
                "UPDATE job_review_queue SET status = :decision, reviewer_id = :reviewer, reviewed_at = NOW(), comments = :comments
                 WHERE job_id = :job_id AND status = 'pending'",
                ['decision' => $decision, 'reviewer' => $reviewerId, 'comments' => $comments, 'job_id' => $jobId]
            );
        } catch (\Throwable $e) {
            error_log('JobApprovalService::resolveQueue: ' . $e->getMessage());
        }
    }

    /**
     * The original jobs.status enum lacked pending_review / rejected / taken_down, so MySQL (non-strict mode)
     * silently stored '' for those jobs. Widen the enum once and repair rows that were blanked.
     */
    /** jobs.employment_type: add 'one_time' (plumber / electrician / carpenter for a single job). */
    private static function ensureEmploymentTypeEnum(Database $db): void
    {
        $col = $db->fetchOne(
            "SELECT COLUMN_TYPE AS t FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'jobs' AND COLUMN_NAME = 'employment_type'"
        );
        $type = (string)($col['t'] ?? '');
        if ($type !== '' && str_starts_with($type, 'enum(') && strpos($type, "'one_time'") === false) {
            preg_match_all("/'([^']*)'/", $type, $m);
            $values = array_values(array_unique(array_merge($m[1], ['one_time'])));
            $enum = implode(',', array_map(static fn($v) => "'" . str_replace("'", "''", $v) . "'", $values));
            $db->getConnection()?->exec("ALTER TABLE jobs MODIFY employment_type ENUM({$enum}) DEFAULT 'full_time'");
        }
    }

    public static function ensureStatusEnum(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        try {
            $db = Database::getInstance();
            $col = $db->fetchOne(
                "SELECT COLUMN_TYPE AS t FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'jobs' AND COLUMN_NAME = 'status'"
            );
            self::ensureEmploymentTypeEnum($db);
            $type = (string)($col['t'] ?? '');
            if ($type === '' || !str_starts_with($type, 'enum(')) {
                return;
            }
            $missing = array_filter(self::ALL_STATUSES, static fn($s) => strpos($type, "'{$s}'") === false);
            if (!$missing) {
                return;
            }

            preg_match_all("/'([^']*)'/", $type, $m);
            $values = array_values(array_unique(array_merge($m[1], self::ALL_STATUSES)));
            $enum = implode(',', array_map(static fn($v) => "'" . str_replace("'", "''", $v) . "'", $values));
            $db->getConnection()?->exec("ALTER TABLE jobs MODIFY status ENUM({$enum}) DEFAULT 'draft'");

            // Repair jobs that were stored with a blank status.
            $db->query(
                "UPDATE jobs j SET j.status = 'pending_review'
                 WHERE j.status = '' AND EXISTS (SELECT 1 FROM job_review_queue q WHERE q.job_id = j.id AND q.status = 'pending')"
            );
            $db->query("UPDATE jobs SET status = 'draft' WHERE status = ''");
        } catch (\Throwable $e) {
            error_log('JobApprovalService::ensureStatusEnum: ' . $e->getMessage());
        }
    }
}
