<?php

declare(strict_types=1);

namespace App\Controllers\Api\Employer;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Employer;
use App\Services\Employer\JobService;

class JobController extends ApiController
{
    private JobService $jobService;

    public function __construct()
    {
        $this->jobService = new JobService();
    }

    public function index(Request $request, Response $response): void
    {
        $employer = $this->employer($request, $response);
        if (!$employer) {
            return;
        }

        $jobs = $this->jobService->getJobsByEmployer($employer->id);
        $this->success($response, ['jobs' => $jobs]);
    }

    public function create(Request $request, Response $response): void
    {
        $employer = $this->employer($request, $response);
        if (!$employer) {
            return;
        }

        $data = $request->getJsonBody();

        $result = $this->jobService->createJob($employer->id, $data);

        if (!$result['success']) {
            $this->error($response, $result['message'], 422, $result['errors'] ?? null);
            return;
        }

        $this->success($response, ['job' => $result['job']], 'Job created successfully', 201);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $employer = $this->employer($request, $response);
        if (!$employer) {
            return;
        }

        $result = $this->jobService->getJob($employer->id, (int)$params['id']);
        if (!$result['success']) {
            $this->error($response, $result['message'], $result['code'] ?? 404);
            return;
        }

        $this->success($response, ['job' => $result['job']]);
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $employer = $this->employer($request, $response);
        if (!$employer) {
            return;
        }

        $jobId = (int)$params['id'];
        $data = $request->getJsonBody();

        $result = $this->jobService->updateJob($employer->id, $jobId, $data);

        if (!$result['success']) {
            $this->error($response, $result['message'], $result['code'] ?? 400, $result['errors'] ?? null);
            return;
        }

        $this->success($response, ['job' => $result['job']], 'Job updated successfully');
    }

    public function delete(Request $request, Response $response, array $params): void
    {
        $employer = $this->employer($request, $response);
        if (!$employer) {
            return;
        }

        $jobId = (int)$params['id'];

        $result = $this->jobService->deleteJob($employer->id, $jobId);

        if (!$result['success']) {
            $this->error($response, $result['message'], $result['code'] ?? 400);
            return;
        }

        $this->success($response, [], 'Job deleted successfully');
    }

    public function duplicate(Request $request, Response $response, array $params): void
    {
        $employer = $this->employer($request, $response);
        if (!$employer) {
            return;
        }

        $result = $this->jobService->duplicateJob($employer->id, (int)$params['id']);
        if (!$result['success']) {
            $this->error($response, $result['message'], $result['code'] ?? 400, $result['errors'] ?? null);
            return;
        }

        $this->success($response, ['job' => $result['job']], 'Job duplicated successfully', 201);
    }

    public function publish(Request $request, Response $response, array $params): void
    {
        $this->setStatus($request, $response, $params, 'published', 'Job published successfully');
    }

    public function unpublish(Request $request, Response $response, array $params): void
    {
        $this->setStatus($request, $response, $params, 'draft', 'Job unpublished successfully');
    }

    private function setStatus(Request $request, Response $response, array $params, string $status, string $message): void
    {
        $employer = $this->employer($request, $response);
        if (!$employer) {
            return;
        }

        $result = $this->jobService->setStatus($employer->id, (int)$params['id'], $status);
        if (!$result['success']) {
            $this->error($response, $result['message'], $result['code'] ?? 400);
            return;
        }

        $this->success($response, ['job' => $result['job']], $message);
    }

    private function employer(Request $request, Response $response): ?Employer
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Employer authentication is required', 403);
            return null;
        }

        $employer = $user->employer();
        if (!$employer) {
            $this->error($response, 'Employer profile is incomplete. Please create or update employer profile first.', 409, [
                'profile_required' => true,
                'profile_endpoint' => '/api/v1/employer/profile',
            ]);
            return null;
        }

        return $employer;
    }
}
