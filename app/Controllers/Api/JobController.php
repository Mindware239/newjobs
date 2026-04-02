<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Services\JobService;
use App\Models\Application;
use App\Models\Candidate;

class JobController extends ApiController
{
    private JobService $jobService;

    public function __construct()
    {
        $this->jobService = new JobService();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/jobs",
     *     summary="List and search jobs",
     *     tags={"Jobs"},
     *     @OA\Parameter(name="keyword", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="location", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="A list of jobs")
     * )
     */
    public function index(Request $request, Response $response): void
    {
        $filters = $request->all();
        $userId = $request->user() ? $request->user()->id : null;

        $result = $this->jobService->searchJobs($filters, $userId);

        $this->success($response, $result);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/jobs/{slug}",
     *     summary="Get job details by slug",
     *     tags={"Jobs"},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Job details")
     * )
     */
    public function show(Request $request, Response $response, array $params): void
    {
        $slug = $params['slug'] ?? '';
        $userId = $request->user() ? $request->user()->id : null;

        $job = $this->jobService->getJobBySlug($slug, $userId);

        if (!$job) {
            $this->error($response, 'Job not found', 404);
            return;
        }

        $this->success($response, $job);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/jobs/{id}/apply",
     *     summary="Apply for a job",
     *     tags={"Jobs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Application successful")
     * )
     */
    public function apply(Request $request, Response $response, array $params): void
    {
        $user = $this->user($request);
        if (!$user || !$user->isCandidate()) {
            $this->error($response, 'Only candidates can apply', 403);
            return;
        }

        $jobId = (int)($params['id'] ?? 0);
        $candidate = Candidate::findByUserId($user->id);

        if (!$candidate) {
            $this->error($response, 'Candidate profile not found', 404);
            return;
        }

        $result = $this->jobService->applyForJob($jobId, $candidate->id, $user->id);

        if (!$result['success']) {
            $this->error($response, $result['message'], $result['code']);
            return;
        }

        $this->success($response, ['application_id' => $result['application_id']], 'Application submitted successfully');
    }
}
