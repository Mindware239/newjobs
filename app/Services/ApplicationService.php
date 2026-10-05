<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\Job;
use App\Models\User;
use App\Models\ApplicationEvent;
use App\Services\NotificationService;
use App\Services\JobMatchService;

class ApplicationService
{
    /**
     * Check if a candidate can apply for a job
     */
    public function canApply(int $userId, int $jobId): array
    {
        $user = User::find($userId);
        if (!$user || !$user->isCandidate()) {
            return ['success' => false, 'message' => 'Only candidates can apply for jobs', 'code' => 403];
        }

        // Only live jobs accept applications – check this before anything else so the message is accurate.
        $jobRow = Job::find($jobId);
        if (!$jobRow) {
            return ['success' => false, 'message' => 'Job not found', 'code' => 404];
        }
        if (($jobRow->attributes['status'] ?? '') !== 'published') {
            return ['success' => false, 'message' => 'This job is not accepting applications right now.', 'code' => 410];
        }

        $candidate = Candidate::findByUserId($userId);
        if (!$candidate) {
            return ['success' => false, 'message' => 'Candidate profile not found', 'code' => 404];
        }

        // Check if already applied
        $existing = Application::where('job_id', '=', $jobId)
            ->where('candidate_user_id', '=', $userId)
            ->where('status', '!=', 'withdrawn')
            ->first();

        if ($existing) {
            return ['success' => false, 'message' => 'You have already applied for this job', 'code' => 400];
        }

        // Check profile completion (Professional requirement)
        if (!$candidate->isProfileComplete()) {
            $missingFields = $candidate->getMissingFields();
            return [
                'success' => false, 
                'message' => 'Your profile is incomplete. Please complete your profile to apply.', 
                'code' => 422,
                'incomplete_profile' => true,
                'missing_fields' => $missingFields,
                'profile_strength' => $candidate->calculateProfileStrength()
            ];
        }

        return ['success' => true];
    }

    /**
     * Submit an application
     */
    public function submitApplication(int $userId, int $jobId, array $data = []): array
    {
        $canApply = $this->canApply($userId, $jobId);
        if (!$canApply['success']) {
            return $canApply;
        }

        $candidate = Candidate::findByUserId($userId);
        $job = Job::find($jobId);
        if (!$job) {
            return ['success' => false, 'message' => 'Job not found', 'code' => 404];
        }
        // Only live jobs accept applications (not drafts, jobs under review, paused, closed, rejected or taken down).
        if (($job->attributes['status'] ?? '') !== 'published') {
            return ['success' => false, 'message' => 'This job is not accepting applications right now.', 'code' => 410];
        }

        // Handle external jobs
        if (($job->attributes['job_type'] ?? 'internal') === 'external') {
            $applyLink = trim((string)($job->attributes['apply_link'] ?? ''));
            if ($applyLink !== '') {
                return [
                    'success' => true,
                    'external' => true,
                    'redirect_url' => $applyLink,
                    'message' => 'Redirecting to company website'
                ];
            }
            return ['success' => false, 'message' => 'External apply link is unavailable', 'code' => 422];
        }

        // Create application snapshot (Professional requirement)
        $snapshot = $this->createProfileSnapshot($candidate);

        // Calculate match score
        $matchScore = 0;
        try {
            $matchService = new JobMatchService();
            $matchResult = $matchService->calculateMatch($candidate->id, $jobId, false);
            $matchScore = $matchResult['overall_match_score'] ?? 0;
        } catch (\Exception $e) {
            error_log("Error calculating match score in ApplicationService: " . $e->getMessage());
        }

        $application = new Application();
        $application->fill([
            'job_id' => $jobId,
            'candidate_user_id' => $userId,
            'resume_url' => $data['resume_url'] ?? $candidate->attributes['resume_url'] ?? '',
            'cover_letter' => $data['cover_letter'] ?? '',
            'expected_salary' => $data['expected_salary'] ?? $candidate->attributes['expected_salary_min'] ?? null,
            'status' => 'applied',
            'score' => $matchScore,
            'match_score' => $matchScore,
            'source' => $data['source'] ?? 'portal',
            'profile_snapshot' => json_encode($snapshot)
        ]);

        if ($application->save()) {
            $applicationId = $application->attributes['id'] ?? $application->id;

            // Log application event
            $this->logEvent((int)$applicationId, $userId, 'applied', 'Application submitted');

            // Send notifications
            $this->sendNotifications((int)$applicationId, $userId, $jobId);

            return [
                'success' => true,
                'message' => 'Application submitted successfully!',
                'application_id' => $applicationId
            ];
        }

        return ['success' => false, 'message' => 'Failed to submit application', 'code' => 500];
    }

    /**
     * Create a snapshot of the candidate's profile
     */
    private function createProfileSnapshot(Candidate $candidate): array
    {
        return [
            'full_name' => $candidate->attributes['full_name'] ?? '',
            'professional_title' => $candidate->attributes['professional_title'] ?? '',
            'email' => $candidate->user()->email ?? '',
            'mobile' => $candidate->attributes['mobile'] ?? '',
            'location' => [
                'city' => $candidate->attributes['city'] ?? '',
                'state' => $candidate->attributes['state'] ?? '',
                'country' => $candidate->attributes['country'] ?? ''
            ],
            'education' => $candidate->education(),
            'experience' => $candidate->experience(),
            'skills' => $candidate->skills(),
            'languages' => $candidate->languages(),
            'certificates' => $candidate->certificates(),
            'resume_url' => $candidate->attributes['resume_url'] ?? '',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Log an application event
     */
    private function logEvent(int $applicationId, int $userId, string $status, string $comment): void
    {
        try {
            $event = new ApplicationEvent();
            $event->fill([
                'application_id' => $applicationId,
                'actor_user_id' => $userId,
                'from_status' => null,
                'to_status' => $status,
                'comment' => $comment
            ]);
            $event->save();
        } catch (\Exception $e) {
            error_log("Failed to save application event: " . $e->getMessage());
        }
    }

    /**
     * Send notifications to both employer and candidate
     */
    private function sendNotifications(int $applicationId, int $userId, int $jobId): void
    {
        try {
            NotificationService::notifyApplicationSubmitted($applicationId);
        } catch (\Exception $e) {
            error_log("Failed to send application notifications via NotificationService: " . $e->getMessage());
        }
    }
}
