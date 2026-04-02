<?php

declare(strict_types=1);

namespace App\Controllers\Api\Candidate;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\JobBookmark;

class BookmarkController extends ApiController
{
    /**
     * POST /candidate/jobs/{id}/bookmark
     * Save job
     */
    public function bookmark(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $existing = JobBookmark::where('candidate_id', '=', $user->id)
            ->where('job_id', '=', $id)
            ->first();

        if ($existing) {
            $this->success($response, [], 'Already bookmarked');
            return;
        }

        $bookmark = new JobBookmark();
        $bookmark->fill([
            'candidate_id' => $user->id,
            'job_id' => $id
        ])->save();

        $this->success($response, ['id' => $bookmark->id], 'Job bookmarked', 201);
    }

    /**
     * DELETE /candidate/jobs/{id}/bookmark
     * Remove bookmark
     */
    public function unbookmark(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        JobBookmark::where('candidate_id', '=', $user->id)
            ->where('job_id', '=', $id)
            ->delete();

        $this->success($response, [], 'Bookmark removed');
    }

    /**
     * GET /candidate/bookmarks
     * List saved jobs
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

        $bookmarks = JobBookmark::where('candidate_id', '=', $user->id)
            ->with('job')
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage, $page);

        $jobs = [];
        foreach ($bookmarks['data'] as $bookmark) {
            if ($bookmark->job) {
                $jobs[] = [
                    'id' => $bookmark->job->id,
                    'title' => $bookmark->job->title,
                    'company' => $bookmark->job->company,
                    'location' => $bookmark->job->location,
                    'salary_range' => $bookmark->job->salary_range,
                    'bookmarked_at' => $bookmark->created_at
                ];
            }
        }

        $this->success($response, [
            'jobs' => $jobs,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $bookmarks['total'],
                'last_page' => ceil($bookmarks['total'] / $perPage)
            ]
        ]);
    }

    /**
     * POST /candidate/bookmarks/bulk-delete
     * Bulk delete bookmarks
     */
    public function bulkDelete(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'job_ids' => 'required|array'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        JobBookmark::where('candidate_id', '=', $user->id)
            ->whereIn('job_id', $request->input('job_ids'))
            ->delete();

        $this->success($response, [], 'Bookmarks deleted');
    }
}
