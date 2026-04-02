<?php

declare(strict_types=1);

namespace App\Controllers\Api\Employer;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Job;
use App\Models\Application;
use App\Models\Interview;

class DashboardController extends ApiController
{
    /**
     * GET /api/v1/employer/dashboard
     * Get employer dashboard overview
     */
    public function index(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $activejobs = Job::where('employer_id', '=', $user->id)
            ->whereNull('deleted_at')
            ->count();

        $totalApplications = Application::where('employer_id', '=', $user->id)
            ->count();

        $pendingApplications = Application::where('employer_id', '=', $user->id)
            ->where('status', '=', 'pending')
            ->count();

        $upcomingInterviews = Interview::where('employer_id', '=', $user->id)
            ->where('interview_date', '>', date('Y-m-d H:i:s'))
            ->whereNull('completed_at')
            ->count();

        $this->success($response, [
            'total_active_jobs' => $activejobs,
            'total_applications' => $totalApplications,
            'pending_applications' => $pendingApplications,
            'upcoming_interviews' => $upcomingInterviews
        ]);
    }

    /**
     * GET /api/v1/employer/dashboard/stats
     * Get detailed dashboard statistics
     */
    public function stats(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $jobs = Job::where('employer_id', '=', $user->id)
            ->select('id', 'title', 'status', 'created_at')
            ->get();

        $jobIds = $jobs->pluck('id')->toArray();

        $applicationsPerJob = [];
        $shortlistedPerJob = [];
        foreach ($jobIds as $jobId) {
            $applicationsPerJob[$jobId] = Application::where('job_id', '=', $jobId)->count();
            $shortlistedPerJob[$jobId] = Application::where('job_id', '=', $jobId)
                ->where('status', '=', 'shortlisted')
                ->count();
        }

        $this->success($response, [
            'total_jobs' => count($jobIds),
            'applications_per_job' => $applicationsPerJob,
            'shortlisted_per_job' => $shortlistedPerJob,
            'conversion_rate' => count($jobIds) > 0 ? (array_sum($shortlistedPerJob) / array_sum($applicationsPerJob)) * 100 : 0
        ]);
    }

    /**
     * GET /api/v1/employer/dashboard/recent-applications
     * Get recent applications
     */
    public function recentApplications(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $limit = (int)$request->input('limit', 10);
        $applications = Application::where('employer_id', '=', $user->id)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();

        $data = [];
        foreach ($applications as $app) {
            $data[] = [
                'id' => $app->id,
                'job_id' => $app->job_id,
                'candidate_id' => $app->candidate_id,
                'status' => $app->status,
                'applied_at' => $app->created_at
            ];
        }

        $this->success($response, ['applications' => $data]);
    }

    /**
     * GET /api/v1/employer/dashboard/active-jobs
     * Get active jobs
     */
    public function activeJobs(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $limit = (int)$request->input('limit', 10);
        $jobs = Job::where('employer_id', '=', $user->id)
            ->where('status', '=', 'published')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();

        $data = [];
        foreach ($jobs as $job) {
            $data[] = [
                'id' => $job->id,
                'title' => $job->title,
                'position' => $job->position ?? 'Not specified',
                'applications_count' => Application::where('job_id', '=', $job->id)->count(),
                'posted_at' => $job->created_at
            ];
        }

        $this->success($response, ['jobs' => $data]);
    }

    /**
     * GET /api/v1/employer/dashboard/upcoming-interviews
     * Get upcoming interviews
     */
    public function upcomingInterviews(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $limit = (int)$request->input('limit', 10);
        $interviews = Interview::where('employer_id', '=', $user->id)
            ->where('interview_date', '>', date('Y-m-d H:i:s'))
            ->whereNull('completed_at')
            ->orderBy('interview_date', 'ASC')
            ->limit($limit)
            ->get();

        $data = [];
        foreach ($interviews as $interview) {
            $data[] = [
                'id' => $interview->id,
                'application_id' => $interview->application_id,
                'interview_type' => $interview->type ?? 'phone',
                'scheduled_at' => $interview->interview_date,
                'duration_minutes' => $interview->duration ?? 30
            ];
        }

        $this->success($response, ['interviews' => $data]);
    }

    /**
     * GET /api/v1/employer/team-members
     * Get team members
     */
    public function teamMembers(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        // Assuming there's a team_members table linking users to employer accounts
        // This is a simplified implementation
        $members = User::where('employer_id', '=', $user->id)
            ->orderBy('created_at', 'DESC')
            ->get();

        $data = [];
        foreach ($members as $member) {
            $data[] = [
                'id' => $member->id,
                'name' => $member->full_name ?? $member->name,
                'email' => $member->email,
                'role' => $member->member_role ?? 'team_member',
                'status' => $member->status,
                'joined_at' => $member->created_at
            ];
        }

        $this->success($response, ['team_members' => $data]);
    }

    /**
     * POST /api/v1/employer/team-members
     * Add team member
     */
    public function addMember(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $data = $request->getJsonBody();
        $errors = $this->validate($data, [
            'email' => 'required|email',
            'role' => 'required|in:admin,recruiter,team_member'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        // Check if user already exists
        $existingUser = User::where('email', '=', $data['email'])->first();
        if ($existingUser) {
            // Add as team member to employer
            $existingUser->employer_id = $user->id;
            $existingUser->member_role = $data['role'];
            $existingUser->save();
        } else {
            // Create invitation for new user
            // Implementation depends on your invitation system
        }

        $this->success($response, null, 'Team member added');
    }

    /**
     * PUT /api/v1/employer/team-members/{id}
     * Update team member
     */
    public function updateMember(Request $request, Response $response, array $params): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $member = User::find((int)$params['id']);
        if (!$member || $member->employer_id !== $user->id) {
            $this->error($response, 'Member not found', 404);
            return;
        }

        $data = $request->getJsonBody();
        if (isset($data['role'])) {
            $member->member_role = $data['role'];
            $member->save();
        }

        $this->success($response, null, 'Member updated');
    }

    /**
     * DELETE /api/v1/employer/team-members/{id}
     * Remove team member
     */
    public function removeMember(Request $request, Response $response, array $params): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $member = User::find((int)$params['id']);
        if (!$member || $member->employer_id !== $user->id) {
            $this->error($response, 'Member not found', 404);
            return;
        }

        // Don't delete, just remove association
        $member->employer_id = null;
        $member->member_role = null;
        $member->save();

        $this->success($response, null, 'Member removed');
    }
}
