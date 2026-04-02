<?php

declare(strict_types=1);

namespace App\Controllers\Api\Employer;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Candidate;
use App\Models\CandidateProfile;
use App\Models\ShortlistedCandidate;
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

        $data = [];
        foreach ($candidates as $candidate) {
            $profile = CandidateProfile::where('candidate_id', '=', $candidate->id)->first();
            $data[] = [
                'id' => $candidate->id,
                'name' => $candidate->full_name ?? $candidate->name,
                'email' => $candidate->email,
                'phone' => $candidate->phone,
                'location' => $profile->location ?? null,
                'headline' => $profile->headline ?? null,
                'photo' => $candidate->photo ?? null,
                'shortlisted' => ShortlistedCandidate::where('employer_id', '=', $user->id)
                    ->where('candidate_id', '=', $candidate->id)
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

        $profile = CandidateProfile::where('candidate_id', '=', $candidate->id)->first();

        // Track profile view
        $this->logProfileView($user->id, $candidate->id);

        $this->success($response, [
            'id' => $candidate->id,
            'name' => $candidate->full_name ?? $candidate->name,
            'email' => $candidate->email,
            'phone' => $candidate->phone,
            'photo' => $candidate->photo,
            'profile' => $profile ? [
                'headline' => $profile->headline,
                'bio' => $profile->bio,
                'location' => $profile->location,
                'experience_years' => $profile->years_of_experience ?? 0,
                'skills' => json_decode($profile->skills ?? '[]', true),
            ] : null,
            'shortlisted' => ShortlistedCandidate::where('employer_id', '=', $user->id)
                ->where('candidate_id', '=', $candidate->id)
                ->exists()
        ]);
    }

    /**
     * POST /api/v1/employer/candidates/{id}/invite
     * Invite candidate to apply for a job
     */
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

        // Create job invitation record
        // Implementation depends on your job invitation model

        // Send notification
        $this->notificationService->notify(
            $candidate->id,
            'Job Invitation',
            'You have been invited to apply for a job',
            'job_invitation'
        );

        $this->success($response, null, 'Invitation sent');
    }

    /**
     * POST /api/v1/employer/candidates/{id}/shortlist
     * Shortlist a candidate
     */
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
        $existing = ShortlistedCandidate::where('employer_id', '=', $user->id)
            ->where('candidate_id', '=', $candidate->id)
            ->first();

        if ($existing) {
            $this->error($response, 'Candidate already shortlisted', 400);
            return;
        }

        $shortlist = new ShortlistedCandidate();
        $shortlist->fill([
            'employer_id' => $user->id,
            'candidate_id' => $candidate->id
        ]);
        $shortlist->save();

        // Send notification
        $this->notificationService->notify(
            $candidate->id,
            'Shortlisted',
            'You have been shortlisted by ' . ($user->full_name ?? 'an employer'),
            'shortlisted'
        );

        $this->success($response, null, 'Candidate shortlisted');
    }

    /**
     * GET /api/v1/employer/shortlists
     * Get shortlisted candidates
     */
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

        $shortlists = ShortlistedCandidate::where('employer_id', '=', $user->id)
            ->orderBy('created_at', 'DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $data = [];
        foreach ($shortlists as $shortlist) {
            $candidate = User::find($shortlist->candidate_id);
            if ($candidate) {
                $profile = CandidateProfile::where('candidate_id', '=', $candidate->id)->first();
                $data[] = [
                    'id' => $shortlist->id,
                    'candidate_id' => $candidate->id,
                    'name' => $candidate->full_name ?? $candidate->name,
                    'email' => $candidate->email,
                    'location' => $profile->location ?? null,
                    'shortlisted_at' => $shortlist->created_at
                ];
            }
        }

        $total = ShortlistedCandidate::where('employer_id', '=', $user->id)->count();
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

    /**
     * Log profile view for analytics
     */
    private function logProfileView(int $employer_id, int $candidate_id): void
    {
        // Log profile view in analytics
        // Implementation depends on your analytics model
    }
}
