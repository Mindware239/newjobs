<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\CandidateView;
use App\Models\JobView;
use App\Models\Application;
use App\Models\Job;

class AnalyticsController extends ApiController
{
    /**
     * GET /analytics/dashboard
     * Get analytics summary
     */
    public function dashboard(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        if ($user->role === 'candidate') {
            $data = $this->getCandidateDashboard($user->id);
        } else if ($user->role === 'employer') {
            $data = $this->getEmployerDashboard($user->id);
        } else {
            $data = [];
        }

        $this->success($response, $data);
    }

    /**
     * GET /analytics/profile-views
     * Get profile views
     */
    public function profileViews(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('per_page', 20);
        $days = (int)$request->query('days', 30);

        $fromDate = date('Y-m-d', strtotime("-$days days"));

        $views = CandidateView::where('candidate_id', '=', $user->id)
            ->where('created_at', '>=', $fromDate)
            ->with('viewer')
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage, $page);

        $data = [];
        foreach ($views['data'] as $view) {
            $data[] = [
                'id' => $view->id,
                'viewer_name' => $view->viewer?->email,
                'viewed_at' => $view->created_at,
                'viewer_role' => $view->viewer?->role
            ];
        }

        $this->success($response, [
            'views' => $data,
            'total_views' => $views['total'],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($views['total'] / $perPage)
            ]
        ]);
    }

    /**
     * GET /analytics/job/{id}/stats
     * Get job-specific analytics
     */
    public function jobStats(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $job = Job::find($id);
        if (!$job || $job->employer_id !== $user->id) {
            $this->error($response, 'Job not found', 404);
            return;
        }

        $views = JobView::where('job_id', '=', $id)->count();
        $applications = Application::where('job_id', '=', $id)->count();
        $applicationsStats = [
            'total' => $applications,
            'pending' => Application::where('job_id', '=', $id)->where('status', '=', 'pending')->count(),
            'shortlisted' => Application::where('job_id', '=', $id)->where('shortlisted', '=', true)->count(),
            'rejected' => Application::where('job_id', '=', $id)->where('status', '=', 'rejected')->count(),
            'offered' => Application::where('job_id', '=', $id)->where('status', '=', 'offered')->count(),
            'accepted' => Application::where('job_id', '=', $id)->where('status', '=', 'accepted')->count()
        ];

        $viewToAppRatio = $views > 0 ? round(($applications / $views) * 100, 2) : 0;

        $this->success($response, [
            'job_id' => $job->id,
            'job_title' => $job->title,
            'views' => $views,
            'applications' => $applicationsStats,
            'conversion_rate' => $viewToAppRatio . '%',
            'posted_at' => $job->created_at,
            'closing_date' => $job->closing_date ?? null
        ]);
    }

    /**
     * POST /analytics/event
     * Track custom events
     */
    public function trackEvent(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'event_name' => 'required|string',
            'event_data' => 'sometimes|array'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        // Store event in analytics table
        // Implementation depends on your analytics tracking setup

        $this->success($response, [], 'Event tracked');
    }

    private function getCandidateDashboard(int $userId): array
    {
        $applications = Application::where('candidate_id', '=', $userId)->count();
        $appliedPast30Days = Application::where('candidate_id', '=', $userId)
            ->where('created_at', '>=', date('Y-m-d', strtotime('-30 days')))
            ->count();

        $profileViews = CandidateView::where('candidate_id', '=', $userId)
            ->where('created_at', '>=', date('Y-m-d', strtotime('-30 days')))
            ->count();

        $interviews = \App\Models\Interview::whereIn('application_id', function($q) use ($userId) {
            $q->select('id')->from('applications')->where('candidate_id', '=', $userId);
        })
        ->where('status', '=', 'scheduled')
        ->count();

        return [
            'total_applications' => $applications,
            'applications_this_month' => $appliedPast30Days,
            'profile_views' => $profileViews,
            'upcoming_interviews' => $interviews
        ];
    }

    private function getEmployerDashboard(int $userId): array
    {
        $jobs = Job::where('employer_id', '=', $userId)->count();
        $activeJobs = Job::where('employer_id', '=', $userId)
            ->where('status', '=', 'active')
            ->count();

        $pendingApplications = Application::whereIn('job_id', function($q) use ($userId) {
            $q->select('id')->from('jobs')->where('employer_id', '=', $userId);
        })
        ->where('status', '=', 'pending')
        ->count();

        $totalViews = JobView::whereIn('job_id', function($q) use ($userId) {
            $q->select('id')->from('jobs')->where('employer_id', '=', $userId);
        })
        ->count();

        return [
            'total_jobs_posted' => $jobs,
            'active_jobs' => $activeJobs,
            'pending_applications' => $pendingApplications,
            'total_views' => $totalViews
        ];
    }
}
