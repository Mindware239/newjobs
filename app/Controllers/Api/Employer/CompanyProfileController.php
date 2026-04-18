<?php

declare(strict_types=1);

namespace App\Controllers\Api\Employer;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;

class CompanyProfileController extends ApiController
{
    public function getBlogs(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        $this->success($response, ['blogs' => []], 'Blogs fetched');
    }

    public function createBlog(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        $this->success($response, ['blog_id' => 1], 'Blog created', 201);
    }

    public function deleteBlog(Request $request, Response $response, string $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        $this->success($response, [], 'Blog deleted');
    }

    public function getReviews(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        $this->success($response, ['reviews' => []], 'Reviews fetched');
    }

    public function getFollowers(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }
        $this->success($response, ['followers' => []], 'Followers fetched');
    }
}
