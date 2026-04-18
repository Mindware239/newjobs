<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Candidate;
use App\Models\Job;

class NotificationQueueService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Enqueue candidate notification
     */
    public function queueCandidateNotification(Candidate $candidate, Job $job, int $score): void
    {
        $user = $candidate->user();
        if (!$user) return;

        $isHighSalary = false;
        $jobMinSalary = (int)($job->attributes['salary_min'] ?? 0);
        $candExpMax = (int)($candidate->attributes['expected_salary_max'] ?? 0);
        
        if (($candExpMax > 0 && $jobMinSalary > ($candExpMax * 1.5)) || $jobMinSalary >= 100000) {
            $isHighSalary = true;
        }

        $title = $isHighSalary ? '🚀 High Salary Job Match!' : 'New Job Match Found!';
        $companyName = $job->attributes['company_name'] ?? 'Mindware Infotech';
        $message = "We found a new job that matches {$score}% of your profile: {$job->attributes['title']} at {$companyName}";
        
        $link = "/candidate/jobs/" . ($job->attributes['slug'] ?? $job->id);

        $this->insertIntoQueue((int)$user->id, 'job_match', $title, $message, [
            'job_title' => $job->attributes['title'] ?? '',
            'company_name' => $companyName,
            'match_score' => $score,
            'link' => $link,
            'is_high_salary' => $isHighSalary,
            'reference_id' => "cand_{$candidate->id}_job_{$job->id}"
        ], $link);
    }

    /**
     * Enqueue employer bulk notification
     */
    public function queueEmployerNotification(Job $job, int $count): void
    {
        $employer = $job->employer();
        if (!$employer) return;
        $user = $employer->user();
        if (!$user) return;

        $message = "Great news! We found {$count} candidates matching your new job post: " . ($job->attributes['title'] ?? '');
        $link = "/employer/jobs/" . ($job->attributes['slug'] ?? $job->id) . "/candidates";

        $this->insertIntoQueue((int)$user->id, 'employer_job_match', 'Candidates Found!', $message, [
            'job_title' => $job->attributes['title'] ?? '',
            'count' => $count,
            'link' => $link
        ], $link);
    }

    /**
     * Enqueue single employer candidate match notification
     */
    public function queueEmployerMatchNotification(Job $job, Candidate $candidate): void
    {
        $employer = $job->employer();
        if (!$employer) return;
        $user = $employer->user();
        if (!$user) return;

        $link = "/employer/jobs/" . ($job->attributes['slug'] ?? $job->id) . "/candidates";
        $candidateName = $candidate->attributes['full_name'] ?? 'A candidate';
        
        $this->insertIntoQueue((int)$user->id, 'candidate_match_employer', 'New Candidate Match', "{$candidateName} matches your job.", [
            'job_title' => $job->attributes['title'] ?? '',
            'match_count' => 1,
            'dashboard_link' => $link,
            'reference_id' => "cand_{$candidate->id}_job_{$job->id}"
        ], $link);
    }

    /**
     * Enqueue candidate job match notification
     */
    public function queueJobMatchNotification($user, Job $job, Candidate $candidate, int $score): void
    {
        $link = "/candidate/jobs/" . ($job->attributes['slug'] ?? $job->id);
        
        $this->insertIntoQueue((int)$user->id, 'job_match', 'New Job Match Found!', "A new job matches your profile.", [
            'job_title' => $job->attributes['title'] ?? '',
            'match_score' => $score,
            'link' => $link,
            'reference_id' => "cand_{$candidate->id}_job_{$job->id}"
        ], $link);
    }

    /**
     * Insert notification payload into queue table
     */
    private function insertIntoQueue(int $userId, string $type, string $title, string $message, array $data, ?string $link): void
    {
        try {
            $hasQueueTable = $this->db->fetchOne("SHOW TABLES LIKE 'notification_queue'");
            
            if ($hasQueueTable) {
                // Check if identical reference is pending or sent recently to prevent duplicates
                $ref = $data['reference_id'] ?? null;
                if ($ref) {
                    $exists = $this->db->fetchOne(
                        "SELECT id FROM notification_queue 
                         WHERE user_id = :uid AND JSON_EXTRACT(data, '$.reference_id') = :ref 
                         AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
                        ['uid' => $userId, 'ref' => $ref]
                    );
                    if ($exists) return;
                }

                $this->db->query(
                    "INSERT INTO notification_queue (user_id, type, title, message, data, link, status, retries, created_at) 
                     VALUES (:uid, :type, :title, :msg, :data, :link, 'pending', 0, NOW())",
                    [
                        'uid' => $userId,
                        'type' => $type,
                        'title' => $title,
                        'msg' => $message,
                        'data' => json_encode($data),
                        'link' => $link
                    ]
                );
            } else {
                // Fallback for systems without queue table
                NotificationService::send($userId, $type, $title, $message, $data, $link);
            }
        } catch (\Throwable $t) {
            error_log("Failed to queue notification: " . $t->getMessage());
        }
    }
}
