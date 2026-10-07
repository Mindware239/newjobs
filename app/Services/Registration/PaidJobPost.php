<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Core\Database;
use App\Models\Employer;
use App\Models\Job;
use App\Models\PortalRegistration;
use App\Services\JobApprovalService;
use App\Services\JobMatchService;

/**
 * Pay per job (user, 2026-10-06): an employer whose free job / plan credits are used can still post a
 * regular job from the dashboard for ₹200 + 18% GST (FormRegistry::EMPLOYER_JOB_POST_FEE). The job is
 * kept as a draft until paid; the payment then sends it to moderation like any other submitted job.
 */
class PaidJobPost
{
    /** Pending (or new) payment registration for this draft job; null if it could not be created. */
    public static function paymentFor(Job $job, Employer $employer, object $user): ?array
    {
        $jobId = (int)($job->attributes['id'] ?? $job->id ?? 0);
        $open = Database::getInstance()->fetchOne(
            "SELECT id FROM portal_registrations WHERE type = 'jobpostpaid' AND payment_status <> 'paid'
               AND JSON_UNQUOTE(JSON_EXTRACT(details, '$.job_id')) = ? ORDER BY id DESC LIMIT 1",
            [(string)$jobId]
        );
        if ($open) {
            return PortalRegistration::find((int)$open['id']);
        }
        $a = $job->attributes;
        $loc = json_decode((string)($a['locations'] ?? ''), true);
        $loc = is_array($loc) && isset($loc[0]) && is_array($loc[0]) ? $loc[0] : [];
        $company = (string)($a['company_name'] ?? '') ?: (string)($employer->attributes['company_name'] ?? '');
        return PortalRegistration::create([
            'type' => 'jobpostpaid',
            'full_name' => mb_substr(trim((string)($a['contact_person'] ?? '')) ?: ($company !== '' ? $company : 'Employer'), 0, 120),
            'mobile' => substr((string)preg_replace('/\D/', '', (string)($a['phone'] ?? $user->phone ?? '')), -10),
            'email' => (string)(($a['email'] ?? '') ?: ($user->email ?? '')),
            'city' => mb_substr((string)($loc['city'] ?? ''), 0, 100),
            'state' => mb_substr((string)($loc['state'] ?? ''), 0, 100),
            'categories' => mb_substr((string)($a['title'] ?? ''), 0, 190),
            'details' => json_encode([
                'job_id' => $jobId, 'job_slug' => (string)($a['slug'] ?? ''), 'employer_id' => (int)$employer->id,
                'user_id' => (int)($user->id ?? 0), 'company' => $company, 'return_to' => '/employer/jobs?saved=paid',
            ], JSON_UNESCAPED_UNICODE),
            'declaration_accepted' => 1,
        ], 'JPE', FormRegistry::EMPLOYER_JOB_POST_FEE);
    }

    /** Has this job been paid for and not yet used? */
    public static function isPaid(int $jobId): bool
    {
        return (bool)Database::getInstance()->fetchOne(
            "SELECT id FROM portal_registrations WHERE type = 'jobpostpaid' AND payment_status = 'paid'
               AND JSON_UNQUOTE(JSON_EXTRACT(details, '$.job_id')) = ? LIMIT 1",
            [(string)$jobId]
        );
    }

    /** Payment captured: submit the draft job for publication (moderation decides published / pending_review). */
    public static function activate(array $reg): void
    {
        $jobId = (int)($reg['details']['job_id'] ?? 0);
        $job = $jobId > 0 ? Job::find($jobId) : null;
        if (!$job) {
            error_log('PaidJobPost: job ' . $jobId . ' not found for ' . ($reg['reg_no'] ?? ''));
            return;
        }
        if (!in_array((string)($job->attributes['status'] ?? 'draft'), ['draft', 'rejected', 'closed', ''], true)) {
            return; // already submitted / live
        }
        $employer = Employer::find((int)$job->attributes['employer_id']);
        if (!$employer) {
            return;
        }
        try {
            \App\Models\SubscriptionUsageLog::logUsage(null, (int)$employer->id, 'job_post', null, $jobId, null, ['source' => 'pay_per_post', 'reg_no' => $reg['reg_no'] ?? '']);
        } catch (\Throwable $t) {
            error_log('PaidJobPost usage log: ' . $t->getMessage());
        }
        $status = (new JobApprovalService())->handle($job, $employer);
        if ($status === 'published') {
            try {
                (new JobMatchService())->findAndNotifyCandidates($job);
            } catch (\Throwable $e) {
                error_log('PaidJobPost matching: ' . $e->getMessage());
            }
        }
    }
}
