<?php

declare(strict_types=1);

namespace App\Controllers\Api\Employer;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Candidate;
use App\Models\CandidateInterest;
use App\Services\NotificationService;

class CandidateController extends ApiController
{
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    /**
     * GET /api/v1/employer/candidates
     * Search candidates
     */
    public function search(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $page = (int)$request->input('page', 1);
        $limit = (int)$request->input('limit', 20);
        $offset = ($page - 1) * $limit;

        // Build search query
        $query = User::where('role', '=', 'candidate')
            ->where('status', '=', 'active');

        // Search by skills
        if ($request->input('skills')) {
            $skills = explode(',', $request->input('skills'));
            // This would require a join with CandidateSkills table
            // Simplified version shown here
        }

        // Search by location
        if ($request->input('location')) {
            // Join with CandidateProfile and filter by location
        }

        // Search by experience
        if ($request->input('min_experience')) {
            // Filter by experience years
        }

        $total = $query->count();
        $candidates = $query->offset($offset)
            ->limit($limit)
            ->get();

        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
        $protocol = $isSecure ? 'https://' : 'http://';
        $baseUrl = $_ENV['APP_URL'] ?? ($protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $baseUrl = rtrim($baseUrl, '/');

        $data = [];
        foreach ($candidates as $userModel) {
            $profile = Candidate::where('user_id', '=', $userModel->id)->first();
            
            $data[] = [
                'id' => $userModel->id,
                'full_name' => $profile->full_name ?? $userModel->name,
                'email' => $userModel->email,
                'phone' => $userModel->phone,
                'profile_image' => $this->formatImageUrl($profile->profile_picture ?? null, $baseUrl),
                'location' => [
                    'city' => $profile->city ?? null,
                    'state' => $profile->state ?? null,
                    'country' => $profile->country ?? null
                ],
                'professional_title' => $profile->professional_title ?? null,
                'experience' => $this->formatExperience($profile->experience_data ?? null),
                'skills' => json_decode($profile->skills_data ?? '[]', true),
                'is_verified' => (bool)($profile->is_verified ?? false),
                'shortlisted' => CandidateInterest::where('employer_id', '=', $user->id)
                    ->where('candidate_id', '=', $userModel->id)
                    ->where('interest_level', '=', 'shortlisted')
                    ->exists()
            ];
        }

        $this->success($response, [
            'candidates' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    /**
     * GET /api/v1/employer/candidates/{id}
     * View candidate profile
     */
    public function show(Request $request, Response $response, array $params): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $candidate = User::find((int)$params['id']);
        if (!$candidate || $candidate->role !== 'candidate') {
            $this->error($response, 'Candidate not found', 404);
            return;
        }

        $profile = Candidate::where('user_id', '=', $candidate->id)->first();

        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
        $protocol = $isSecure ? 'https://' : 'http://';
        $baseUrl = $_ENV['APP_URL'] ?? ($protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $baseUrl = rtrim($baseUrl, '/');

        // Track profile view
        $this->logProfileView($user->id, $candidate->id);

        $this->success($response, [
            'id' => $candidate->id,
            'full_name' => $profile->full_name ?? $candidate->name,
            'email' => $candidate->email,
            'phone' => $candidate->phone,
            'profile_image' => $this->formatImageUrl($profile->profile_picture ?? null, $baseUrl),
            'professional_title' => $profile->professional_title ?? null,
            'profile' => $profile ? [
                'bio' => $profile->self_introduction,
                'location' => [
                    'city' => $profile->city,
                    'state' => $profile->state,
                    'country' => $profile->country
                ],
                'experience_years' => $this->formatExperience($profile->experience_data),
                'experience' => json_decode($profile->experience_data ?? '[]', true),
                'education' => json_decode($profile->education_data ?? '[]', true),
                'skills' => json_decode($profile->skills_data ?? '[]', true),
                'languages' => json_decode($profile->languages_data ?? '[]', true),
                'resume_url' => !empty($profile->resume_url) ? $baseUrl . $profile->resume_url : null,
                'portfolio_url' => $profile->portfolio_url,
                'linkedin_url' => $profile->linkedin_url,
                'github_url' => $profile->github_url,
                'expected_salary' => [
                    'min' => $profile->expected_salary_min,
                    'max' => $profile->expected_salary_max
                ],
                'is_verified' => (bool)$profile->is_verified,
                'is_premium' => (bool)$profile->is_premium
            ] : null,
            'shortlisted' => CandidateInterest::where('employer_id', '=', $user->id)
                ->where('candidate_id', '=', $candidate->id)
                ->where('interest_level', '=', 'shortlisted')
                ->exists()
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
        
        $years = (int)floor($totalMonths / 12);
        $months = (int)($totalMonths % 12);
        
        $result = [];
        if ($years > 0) $result[] = $years . " Year" . ($years > 1 ? "s" : "");
        if ($months > 0) $result[] = $months . " Month" . ($months > 1 ? "s" : "");
        
        return !empty($result) ? implode(" ", $result) : "Fresher";
    }

    public function invite(Request $request, Response $response, array $params): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $candidate = User::find((int)$params['id']);
        if (!$candidate || $candidate->role !== 'candidate') {
            $this->error($response, 'Candidate not found', 404);
            return;
        }

        $data = $request->getJsonBody();
        $errors = $this->validate($data, [
            'job_id' => 'required|numeric'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        // Send notification
        $this->notificationService->notify(
            $candidate->id,
            'Job Invitation',
            'You have been invited to apply for a job',
            'job_invitation'
        );

        $this->success($response, null, 'Invitation sent');
    }

    public function shortlist(Request $request, Response $response, array $params): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $candidate = User::find((int)$params['id']);
        if (!$candidate || $candidate->role !== 'candidate') {
            $this->error($response, 'Candidate not found', 404);
            return;
        }

        // Check if already shortlisted
        $existing = CandidateInterest::where('employer_id', '=', $user->id)
            ->where('candidate_id', '=', $candidate->id)
            ->where('interest_level', '=', 'shortlisted')
            ->first();

        if ($existing) {
            $this->error($response, 'Candidate already shortlisted', 400);
            return;
        }

        CandidateInterest::recordInterest(
            (int)$candidate->id,
            (int)$user->id,
            'shortlisted'
        );

        // Send notification
        $this->notificationService->notify(
            $candidate->id,
            'Shortlisted',
            'You have been shortlisted by ' . ($user->full_name ?? 'an employer'),
            'candidate_shortlisted'
        );

        $this->success($response, null, 'Candidate shortlisted');
    }

    public function shortlists(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $page = (int)$request->input('page', 1);
        $limit = (int)$request->input('limit', 20);
        $offset = ($page - 1) * $limit;

        $shortlists = CandidateInterest::where('employer_id', '=', $user->id)
            ->where('interest_level', '=', 'shortlisted')
            ->orderBy('created_at', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
        $protocol = $isSecure ? 'https://' : 'http://';
        $baseUrl = $_ENV['APP_URL'] ?? ($protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $baseUrl = rtrim($baseUrl, '/');

        $data = [];
        foreach ($shortlists as $shortlist) {
            $candidateUser = User::find((int)$shortlist->candidate_id);
            if ($candidateUser) {
                $profile = Candidate::where('user_id', '=', $candidateUser->id)->first();
                $data[] = [
                    'id' => $candidateUser->id,
                    'full_name' => $profile->full_name ?? $candidateUser->name,
                    'email' => $candidateUser->email,
                    'phone' => $candidateUser->phone,
                    'profile_image' => $this->formatImageUrl($profile->profile_picture ?? null, $baseUrl),
                    'professional_title' => $profile->professional_title ?? null,
                    'location' => [
                        'city' => $profile->city,
                        'state' => $profile->state,
                        'country' => $profile->country
                    ],
                    'shortlisted_at' => $shortlist->created_at
                ];
            }
        }

        $total = CandidateInterest::where('employer_id', '=', $user->id)
            ->where('interest_level', '=', 'shortlisted')
            ->count();

        $this->success($response, [
            'shortlists' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    private function logProfileView(int $employerId, int $candidateId): void
    {
        // Implementation for logging view
    }
}
