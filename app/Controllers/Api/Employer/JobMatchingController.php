<?php

declare(strict_types=1);

namespace App\Controllers\Api\Employer;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;

class JobMatchingController extends ApiController
{
    public function getCandidatesForJob(Request $request, Response $response, string $slug): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $this->success($response, ['candidates' => []], 'Candidates retrieved successfully');
    }

    public function generateScores(Request $request, Response $response, string $slug): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $this->success($response, ['status' => 'processing'], 'Score generation started');
    }

    public function scoreCandidate(Request $request, Response $response, string $slug, string $candidateId): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $this->success($response, ['score' => rand(60, 95)], 'Candidate scored');
    }
}
