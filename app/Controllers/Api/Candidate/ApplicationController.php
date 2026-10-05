<?php

declare(strict_types=1);

namespace App\Controllers\Api\Candidate;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Application;
use App\Models\Job;

class ApplicationController extends ApiController
{
    /**
     * GET /candidate/applications
     * List candidate applications
     */
    public function index(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('per_page', 10);
        $status = $request->query('status');

        $query = Application::where('candidate_user_id', '=', $user->id);
        
        if ($status) {
            $query->where('status', '=', $status);
        }

        $paginated = $query->orderBy('applied_at', 'DESC')->paginate($perPage, $page);

        $formattedApplications = [];
        foreach ($paginated['data'] as $application) {
            $job = $application->job();
            $employer = $job ? $job->employer() : null;

            $formattedApplications[] = [
                'id' => $application->id,
                'job_id' => $application->job_id,
                'status' => $application->status,
                'applied_at' => $application->applied_at,
                'job' => $job ? [
                    'id' => $job->id,
                    'title' => $job->title,
                    'slug' => $job->slug,
                    'job_type' => $job->job_type,
                    'location' => $job->location,
                    'company_name' => $employer ? $employer->company_name : ($job->company_name ?: 'N/A'),
                    'company_logo' => $employer ? $employer->logo_url : ($job->company_logo ?: null),
                ] : null
            ];
        }

        $this->success($response, [
            'applications' => $formattedApplications,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $paginated['total'],
                'last_page' => ceil($paginated['total'] / $perPage)
            ]
        ]);
    }

    /**
     * GET /candidate/applications/{id}
     * Get application details
     */
    public function show(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $application = Application::find($id);
        if (!$application) {
            $this->error($response, 'Application not found', 404);
            return;
        }

        // Check authorization based on role
        if ($user->role === 'candidate' && (int)$application->candidate_user_id !== (int)$user->id) {
            $this->error($response, 'Forbidden', 403);
            return;
        }

        if ($user->role === 'employer') {
            $employer = $user->employer();
            if (!$employer) {
                $this->error($response, 'Employer profile is incomplete', 409);
                return;
            }

            $job = Job::find($application->job_id);
            if (!$job || (int)$job->employer_id !== (int)$employer->id) {
                $this->error($response, 'Forbidden', 403);
                return;
            }
        }

        $job = $application->job();
        $this->success($response, [
            'id' => $application->id,
            'job_id' => $application->job_id,
            'job_title' => $job ? $job->title : null,
            'candidate_user_id' => $application->candidate_user_id,
            'status' => $application->status,
            'applied_at' => $application->applied_at,
            'cover_letter' => $application->cover_letter,
            'resume_id' => $application->resume_id,
            'feedback' => $application->feedback,
            'updated_at' => $application->updated_at
        ]);
    }

    /**
     * POST /candidate/applications/{id}/withdraw
     * Withdraw application
     */
    public function withdraw(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $application = Application::find($id);
        if (!$application || (int)$application->candidate_user_id !== (int)$user->id) {
            $this->error($response, 'Application not found', 404);
            return;
        }

        $currentStatus = strtolower($application->status ?? '');
        if (in_array($currentStatus, ['rejected', 'withdrawn', 'accepted'])) {
            $this->error($response, 'Cannot withdraw application in current status: ' . $currentStatus, 400);
            return;
        }

        $application->status = 'withdrawn';
        $application->save();

        // Notify employer about withdrawal
        try {
            $job = $application->job();
            if ($job && $job->employer_id) {
                $employer = \App\Models\Employer::find((int)$job->employer_id);
                if ($employer && $employer->user_id) {
                    $employerUser = \App\Models\User::find((int)$employer->user_id);
                    if ($employerUser) {
                        \App\Services\NotificationService::send(
                            (int)$employerUser->id,
                            'application_withdrawn',
                            "Application Withdrawn: {$user->name} for {$job->title}",
                            "{$user->name} has withdrawn their application for the position of {$job->title}.",
                            [
                                'candidate_name' => $user->name,
                                'job_title' => $job->title,
                                'application_id' => 'APP-' . date('Y') . '-' . $application->id,
                                'reference_id' => $application->id
                            ]
                        );
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log("Failed to send withdrawal notification: " . $e->getMessage());
        }

        $this->success($response, [], 'Application withdrawn successfully');
    }

    /**
     * POST /candidate/applications/{id}/accept-offer
     * Accept job offer
     */
    public function acceptOffer(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $application = Application::find($id);
        if (!$application || (int)$application->candidate_user_id !== (int)$user->id) {
            $this->error($response, 'Application not found', 404);
            return;
        }

        if ($application->status !== 'offered') {
            $this->error($response, 'No offer to accept', 400);
            return;
        }

        $application->status = 'accepted';
        $application->accepted_at = date('Y-m-d H:i:s');
        $application->save();

        $this->success($response, [], 'Offer accepted successfully');
    }

    /**
     * POST /candidate/applications/{id}/reject-offer
     * Reject job offer
     */
    public function rejectOffer(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $application = Application::find($id);
        if (!$application || (int)$application->candidate_user_id !== (int)$user->id) {
            $this->error($response, 'Application not found', 404);
            return;
        }

        if ($application->status !== 'offered') {
            $this->error($response, 'No offer to reject', 400);
            return;
        }

        $application->status = 'rejected';
        $application->rejected_at = date('Y-m-d H:i:s');
        $application->save();

        $this->success($response, [], 'Offer rejected');
    }

    /**
     * GET /employer/applications
     * List applications received by employer
     */
    public function employerApplications(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $employer = $user->employer();
        if (!$employer) {
            $this->error($response, 'Employer profile is incomplete', 409);
            return;
        }

        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('per_page', 10);
        
        $filters = [
            'job_id' => $request->query('job_id'),
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'score_min' => $request->query('score_min'),
            'city' => $request->query('city'),
            'is_verified' => $request->query('is_verified'),
            'sort_by' => $request->query('sort_by', 'latest')
        ];

        $paginated = Application::paginateEmployerApplications($employer->id, $filters, $perPage, $page);
        
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
        $protocol = $isSecure ? 'https://' : 'http://';
        $baseUrl = $_ENV['APP_URL'] ?? ($protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $baseUrl = rtrim($baseUrl, '/');

        $formattedApplications = [];
        foreach ($paginated['data'] as $app) {
            // Get latest interview
            $interview = \App\Models\Interview::where('application_id', '=', $app['id'])
                ->orderBy('scheduled_start', 'DESC')
                ->first();

            $formattedInterview = null;
            if ($interview) {
                $formattedInterview = [
                    'scheduled' => true,
                    'date' => date('Y-m-d', strtotime($interview->scheduled_start)),
                    'time' => date('h:i A', strtotime($interview->scheduled_start)),
                    'mode' => $interview->interview_type ?? 'Online',
                    'status' => $interview->status
                ];
            }

            $formattedApplications[] = [
                'id' => $app['id'],
                'job_id' => $app['job_id'],
                'candidate_user_id' => $app['candidate_user_id'],
                'resume_url' => $app['resume_url'],
                'resume_full_url' => !empty($app['resume_url']) ? $baseUrl . $app['resume_url'] : null,
                'resume_preview_image' => !empty($app['resume_preview_image']) ? $baseUrl . $app['resume_preview_image'] : null,
                'cover_letter' => $app['cover_letter'],
                'expected_salary' => $app['expected_salary'],
                'status' => $app['status'],
                'is_bookmarked' => $app['status'] === 'shortlisted',
                'score' => $app['overall_match_score'] ?? $app['score'],
                'applied_at' => $app['applied_at'],
                'applied_ago' => \App\Helpers\FormatHelper::timeAgo($app['applied_at']),
                'updated_at' => $app['updated_at'],
                
                'candidate' => [
                    'id' => $app['candidate_user_id'],
                    'candidate_id' => $app['candidate_id'],
                    'full_name' => $app['display_name'] ?? $app['user_name'],
                    'email' => $app['candidate_email'],
                    'phone' => $app['candidate_phone'],
                    'profile_image' => $this->formatImageUrl($app['profile_picture'], $baseUrl),
                    'city' => $app['city'],
                    'state' => $app['state'],
                    'country' => $app['country'],
                    'experience' => $this->formatExperience($app['experience_data']),
                    'skills' => json_decode($app['skills_data'] ?? '[]', true),
                    'education' => $this->formatEducation($app['education_data']),
                    'current_company' => $this->getCurrentCompany($app['experience_data']),
                    'current_salary' => $app['current_salary'],
                    'expected_salary_range' => [
                        'min' => $app['expected_salary_min'],
                        'max' => $app['expected_salary_max']
                    ],
                    'is_verified' => (bool)($app['is_verified'] ?? false),
                    'email_verified' => (bool)($app['is_email_verified'] ?? false),
                    'phone_verified' => (bool)($app['is_phone_verified'] ?? false),
                    'last_active' => $app['last_active']
                ],

                'job' => [
                    'id' => $app['job_id'],
                    'title' => $app['job_title'],
                    'company_name' => $app['company_name'],
                    'location' => $app['city'] . ($app['state'] ? ', ' . $app['state'] : ''), // Fixed: Use candidate city/state or job data if available
                    'job_type' => \App\Helpers\FormatHelper::formatEmploymentType($app['job_type'])
                ],

                'interview' => $formattedInterview,
                
                'match_analysis' => [
                    'overall_score' => $app['overall_match_score'],
                    'skills_match' => $app['skill_score'],
                    'experience_match' => $app['experience_score'],
                    'education_match' => $app['education_score']
                ],
                
                'admin_notes' => $app['feedback'] ?? null // Assuming feedback is used for notes
            ];
        }

        $this->success($response, [
            'applications' => $formattedApplications,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $paginated['total'],
                'last_page' => $paginated['last_page'],
                'has_next_page' => $page < $paginated['last_page'],
                'has_previous_page' => $page > 1
            ]
        ]);
    }

    private function formatImageUrl(?string $path, string $baseUrl): ?string
    {
        if (empty($path)) return null;
        if (strpos($path, 'http') === 0) return $path;
        return $baseUrl . '/' . ltrim($path, '/');
    }

    private function formatExperience(?string $data): string
    {
        $exp = json_decode($data ?? '[]', true);
        if (empty($exp)) return 'Fresher';
        
        $totalMonths = 0;
        foreach ($exp as $e) {
            $start = isset($e['start_date']) ? strtotime($e['start_date']) : null;
            $end = isset($e['end_date']) && $e['end_date'] !== 'Present' ? strtotime($e['end_date']) : time();
            
            if ($start && $end) {
                $totalMonths += (int)(($end - $start) / (30 * 24 * 3600));
            }
        }

        if ($totalMonths === 0) return count($exp) . " role(s)";
        
        $years = floor($totalMonths / 12);
        $months = $totalMonths % 12;
        
        $result = [];
        if ($years > 0) $result[] = $years . " Year" . ($years > 1 ? "s" : "");
        if ($months > 0) $result[] = $months . " Month" . ($months > 1 ? "s" : "");
        
        return !empty($result) ? implode(" ", $result) : "Fresher";
    }

    private function formatEducation(?string $data): string
    {
        $edu = json_decode($data ?? '[]', true);
        if (empty($edu)) return 'N/A';
        
        $latest = $edu[0];
        return ($latest['degree'] ?? '') . (!empty($latest['field_of_study']) ? " in " . $latest['field_of_study'] : "");
    }

    private function getCurrentCompany(?string $data): ?string
    {
        $exp = json_decode($data ?? '[]', true);
        if (empty($exp)) return null;
        
        // Assuming the first one is current or latest
        return $exp[0]['company'] ?? null;
    }

    /**
     * GET /employer/applications/{id}/resume
     * Download candidate resume for application
     */
    public function downloadResume(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        $employer = $user->employer();
        if (!$employer) {
            $this->error($response, 'Employer profile is incomplete', 409);
            return;
        }

        $application = Application::find($id);
        if (!$application) {
            $this->error($response, 'Application not found', 404);
            return;
        }

        // Verify employer owns this job
        $job = Job::find($application->job_id);
        if (!$job || (int)$job->employer_id !== (int)$employer->id) {
            $this->error($response, 'Forbidden', 403);
            return;
        }

        // Download resume logic here
        $this->success($response, [
            'download_url' => '/api/v1/resumes/' . $application->resume_id . '/download'
        ]);
    }

    /**
     * POST /employer/applications/{id}/shortlist
     * Shortlist candidate
     */
    public function shortlist(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        $employer = $user->employer();
        if (!$employer) {
            $this->error($response, 'Employer profile is incomplete', 409);
            return;
        }

        $application = Application::find($id);
        if (!$application) {
            $this->error($response, 'Application not found', 404);
            return;
        }
        $job = Job::find($application->job_id);
        if (!$job || (int)$job->employer_id !== (int)$employer->id) {
            $this->error($response, 'Forbidden', 403);
            return;
        }

        $application->shortlisted = true;
        $application->status = 'shortlisted';
        $application->shortlisted_at = date('Y-m-d H:i:s');
        $application->save();

        $this->success($response, [], 'Candidate shortlisted');
    }

    /**
     * POST /employer/applications/{id}/reject
     * Reject application
     */
    public function reject(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        $employer = $user->employer();
        if (!$employer) {
            $this->error($response, 'Employer profile is incomplete', 409);
            return;
        }

        $application = Application::find($id);
        if (!$application) {
            $this->error($response, 'Application not found', 404);
            return;
        }
        $job = Job::find($application->job_id);
        if (!$job || (int)$job->employer_id !== (int)$employer->id) {
            $this->error($response, 'Forbidden', 403);
            return;
        }

        $application->status = 'rejected';
        $application->rejection_reason = $request->input('reason');
        $application->save();

        $this->success($response, [], 'Application rejected');
    }

    /**
     * POST /employer/applications/{id}/send-offer
     * Send job offer
     */
    public function sendOffer(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        $employer = $user->employer();
        if (!$employer) {
            $this->error($response, 'Employer profile is incomplete', 409);
            return;
        }

        $application = Application::find($id);
        if (!$application) {
            $this->error($response, 'Application not found', 404);
            return;
        }
        $job = Job::find($application->job_id);
        if (!$job || (int)$job->employer_id !== (int)$employer->id) {
            $this->error($response, 'Forbidden', 403);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'offer_letter' => 'required',
            'salary' => 'required|numeric',
            'joining_date' => 'required|date'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $application->status = 'offered';
        $application->offer_details = json_encode($request->getJsonBody());
        $application->offered_at = date('Y-m-d H:i:s');
        $application->save();

        $this->success($response, [], 'Job offer sent successfully');
    }
}
