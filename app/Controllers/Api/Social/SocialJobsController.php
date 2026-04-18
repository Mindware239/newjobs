<?php

declare(strict_types=1);

namespace App\Controllers\Api\Social;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;

class SocialJobsController extends ApiController
{
    public function index(Request $request, Response $response): void
    {
        $this->success($response, ['jobs' => []], 'Social jobs retrieved');
    }

    public function store(Request $request, Response $response): void
    {
        $this->success($response, ['job_id' => rand(100, 999)], 'Social job created');
    }

    public function show(Request $request, Response $response, string $id): void
    {
        $this->success($response, ['job' => ['id' => $id]], 'Social job details');
    }

    public function update(Request $request, Response $response, string $id): void
    {
        $this->success($response, ['job' => ['id' => $id]], 'Social job updated');
    }

    public function delete(Request $request, Response $response, string $id): void
    {
        $this->success($response, [], 'Social job deleted');
    }
}
