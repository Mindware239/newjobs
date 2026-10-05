<?php

declare(strict_types=1);

namespace App\Controllers\Api\Candidate;

use App\Controllers\Api\ApiController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Candidate;
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

        $candidate = Candidate::findByUserId((int)$user->id);
        if (!$candidate) {
            $this->error($response, 'Candidate not found', 404);
            return;
        }

        $existing = JobBookmark::where('candidate_id', '=', (int)$candidate->id)
            ->where('job_id', '=', $id)
            ->first();

        if ($existing) {
            $this->success($response, [], 'Already bookmarked');
            return;
        }

        $bookmark = new JobBookmark();
        $bookmark->fill([
            'candidate_id' => (int)$candidate->id,
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

        $candidate = Candidate::findByUserId((int)$user->id);
        if (!$candidate) {
            $this->error($response, 'Candidate not found', 404);
            return;
        }

        JobBookmark::where('candidate_id', '=', (int)$candidate->id)
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

        $candidate = Candidate::findByUserId((int)$user->id);
        if (!$candidate) {
            $this->error($response, 'Candidate not found', 404);
            return;
        }

        $page = max(1, (int)$request->query('page', 1));
        $perPage = min(50, max(1, (int)$request->query('per_page', 10)));
        $offset = ($page - 1) * $perPage;
        $db = Database::getInstance();

        $totalRow = $db->fetchOne(
            'SELECT COUNT(*) AS total FROM job_bookmarks WHERE candidate_id = :candidate_id',
            ['candidate_id' => (int)$candidate->id]
        );
        $total = (int)($totalRow['total'] ?? 0);

        $jobs = $db->fetchAll(
            "SELECT
                j.id,
                j.title,
                j.slug,
                j.company_name AS company,
                j.locations,
                j.salary_min,
                j.salary_max,
                j.currency,
                j.employment_type,
                j.remote_policy,
                j.status,
                jb.created_at AS bookmarked_at
             FROM job_bookmarks jb
             INNER JOIN jobs j ON j.id = jb.job_id
             WHERE jb.candidate_id = :candidate_id
             ORDER BY jb.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            ['candidate_id' => (int)$candidate->id]
        );

        $this->success($response, [
            'jobs' => $jobs,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int)ceil($total / $perPage)
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

        $body = $request->getJsonBody();
        $jobIds = $body['job_ids'] ?? null;
        $errors = [];
        if (!is_array($jobIds) || empty($jobIds)) {
            $errors['job_ids'] = 'The job_ids field must be a non-empty array.';
        }

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $candidate = Candidate::findByUserId((int)$user->id);
        if (!$candidate) {
            $this->error($response, 'Candidate not found', 404);
            return;
        }

        JobBookmark::where('candidate_id', '=', (int)$candidate->id)
            ->whereIn('job_id', array_map('intval', $jobIds))
            ->delete();

        $this->success($response, [], 'Bookmarks deleted');
    }
}
