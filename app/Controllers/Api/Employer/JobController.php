<?php

declare(strict_types=1);

namespace App\Controllers\Api\Employer;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
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
        $employer = $this->user($request)->employer();
        $jobs = $this->jobService->getJobsByEmployer($employer->id);
        $this->success($response, ['jobs' => $jobs]);
    }

    public function create(Request $request, Response $response): void
    {
        $employer = $this->user($request)->employer();
        $data = $request->getJsonBody();

        $result = $this->jobService->createJob($employer->id, $data);

        if (!$result['success']) {
            $this->error($response, $result['message'], 422, $result['errors'] ?? null);
            return;
        }

        $this->success($response, ['job' => $result['job']], 'Job created successfully', 201);
    }

    public function update(Request $request, Response $response, array $params): void
    {
        $employer = $this->user($request)->employer();
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
        $employer = $this->user($request)->employer();
        $jobId = (int)$params['id'];

        $result = $this->jobService->deleteJob($employer->id, $jobId);

        if (!$result['success']) {
            $this->error($response, $result['message'], $result['code'] ?? 400);
            return;
        }

        $this->success($response, [], 'Job deleted successfully');
    }
}
