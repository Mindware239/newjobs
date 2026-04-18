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

        $query = Application::where('candidate_id', '=', $user->id);
        
        if ($status) {
            $query->where('status', '=', $status);
        }

        $applications = $query->orderBy('created_at', 'DESC')->paginate($perPage, $page);

        $this->success($response, [
            'applications' => $applications['data'],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $applications['total'],
                'last_page' => ceil($applications['total'] / $perPage)
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
        if ($user->role === 'candidate' && $application->candidate_id !== $user->id) {
            $this->error($response, 'Forbidden', 403);
            return;
        }

        if ($user->role === 'employer') {
            $job = Job::find($application->job_id);
            if (!$job || $job->employer_id !== $user->id) {
                $this->error($response, 'Forbidden', 403);
                return;
            }
        }

        $job = $application->job();
        $this->success($response, [
            'id' => $application->id,
            'job_id' => $application->job_id,
            'job_title' => $job ? $job->title : null,
            'candidate_id' => $application->candidate_id,
            'status' => $application->status,
            'applied_at' => $application->created_at,
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
        if (!$application || $application->candidate_id !== $user->id) {
            $this->error($response, 'Application not found', 404);
            return;
        }

        if (in_array($application->status, ['rejected', 'withdrawn', 'accepted'])) {
            $this->error($response, 'Cannot withdraw application in current status', 400);
            return;
        }

        $application->status = 'withdrawn';
        $application->save();

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
        if (!$application || $application->candidate_id !== $user->id) {
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
        if (!$application || $application->candidate_id !== $user->id) {
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

        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('per_page', 10);
        $status = $request->query('status');
        $jobId = $request->query('job_id');

        // Get employer's job IDs
        $jobIds = Job::where('employer_id', '=', $user->id)
            ->pluck('id');

        if (empty($jobIds)) {
            $this->success($response, [
                'applications' => [],
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => 0,
                    'last_page' => 0
                ]
            ]);
            return;
        }

        $query = Application::whereIn('job_id', $jobIds);

        if ($status) {
            $query->where('status', '=', $status);
        }

        if ($jobId) {
            $query->where('job_id', '=', $jobId);
        }

        $applications = $query->orderBy('created_at', 'DESC')->paginate($perPage, $page);

        $this->success($response, [
            'applications' => $applications['data'],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $applications['total'],
                'last_page' => ceil($applications['total'] / $perPage)
            ]
        ]);
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

        $application = Application::find($id);
        if (!$application) {
            $this->error($response, 'Application not found', 404);
            return;
        }

        // Verify employer owns this job
        $job = Job::find($application->job_id);
        if (!$job || $job->employer_id !== $user->id) {
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

        $application = Application::find($id);
        if (!$application) {
            $this->error($response, 'Application not found', 404);
            return;
        }

        $application->shortlisted = true;
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

        $application = Application::find($id);
        if (!$application) {
            $this->error($response, 'Application not found', 404);
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

        $application = Application::find($id);
        if (!$application) {
            $this->error($response, 'Application not found', 404);
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
