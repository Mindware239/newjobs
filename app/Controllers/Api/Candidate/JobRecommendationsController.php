<?php

declare(strict_types=1);

namespace App\Controllers\Api\Candidate;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Candidate;
use App\Repositories\CandidateRecommendationRepository;

class JobRecommendationsController extends ApiController
{
    private CandidateRecommendationRepository $recommendationRepository;

    public function __construct()
    {
        $this->recommendationRepository = new CandidateRecommendationRepository();
    }

    public function getRecommendedJobs(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $candidate = Candidate::findByUserId((int)$user->id);
        if (!$candidate) {
            $this->error($response, 'Candidate profile not found', 404);
            return;
        }

        $candidateId = $candidate->attributes['id'];
        $recommendedJobs = $this->recommendationRepository->getRecommendedJobs((int)$candidateId, 20);
        
        $this->success($response, [
            'recommended_jobs' => $recommendedJobs ?: []
        ], 'Recommendations fetched successfully');
    }
}
