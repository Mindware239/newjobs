<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Api\ApiController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\Candidate;
use App\Models\Review;

class ReviewController extends ApiController
{
    /**
     * GET /reviews/company/{id}
     * List company reviews
     */
    public function companyReviews(Request $request, Response $response, int $id): void
    {
        $page = max(1, (int)$request->query('page', 1));
        $perPage = min(50, max(1, (int)$request->query('per_page', 10)));
        $sortBy = $request->query('sort', 'latest'); // latest, helpful, rating_high, rating_low

        $query = Review::where('company_id', '=', $id)
            ->where('status', '=', 'approved');

        $query = match($sortBy) {
            'rating_high' => $query->orderBy('rating', 'DESC'),
            'rating_low' => $query->orderBy('rating', 'ASC'),
            default => $query->orderBy('created_at', 'DESC')
        };

        $reviews = $query->paginate($perPage, $page);

        $this->success($response, [
            'reviews' => $reviews['data'],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $reviews['total'],
                'last_page' => ceil($reviews['total'] / $perPage)
            ]
        ]);
    }

    /**
     * POST /reviews
     * Create review
     */
    public function create(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'rating' => 'required|numeric|min:1|max:5',
            'title' => 'required|string',
            'review_text' => 'required|string|min:20'
        ]);

        $companyId = $this->resolveCompanyId($request->getJsonBody());
        if ($companyId <= 0) {
            $errors['company_id'] = 'The company_id field is required.';
        }

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        // Check if user worked for this employer
        // Implementation depends on employment verification logic

        $review = new Review();
        try {
            $candidate = Candidate::where('user_id', '=', (int)$user->id)->first();
            $candidateId = $candidate ? (int)$candidate->id : null;

            $review->fill([
                'company_id' => $companyId,
                'user_id' => (int)$user->id,
                'candidate_id' => $candidateId,
                'reviewer_name' => (string)($user->name ?: 'Anonymous'),
                'rating' => (int)$request->input('rating'),
                'title' => trim((string)$request->input('title')),
                'review_text' => trim((string)$request->input('review_text')),
                'status' => 'approved'
            ])->save();
        } catch (\Throwable $e) {
            error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
            $this->error($response, 'Database error occurred. Please try again.', 500);
            return;
        }

        $this->success($response, ['id' => $review->id], 'Review created', 201);
    }

    /**
     * GET /reviews/my-reviews
     * Get reviews I gave
     */
    public function myReviews(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $page = max(1, (int)$request->query('page', 1));
        $perPage = min(50, max(1, (int)$request->query('per_page', 10)));

        $reviews = Review::where('user_id', '=', (int)$user->id)
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage, $page);

        $this->success($response, [
            'reviews' => $reviews['data'],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $reviews['total'],
                'last_page' => ceil($reviews['total'] / $perPage)
            ]
        ]);
    }

    /**
     * PUT /reviews/{id}
     * Update review
     */
    public function update(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $review = Review::find($id);
        if (!$review || !$this->canManageReview($review, (int)$user->id)) {
            $this->error($response, 'Review not found', 404);
            return;
        }

        // Only allow editing within 30 days
        if (strtotime($review->created_at) < time() - (30 * 24 * 60 * 60)) {
            $this->error($response, 'Cannot edit review after 30 days', 400);
            return;
        }

        try {
            $data = $request->getJsonBody();
            $updates = [];

            if (array_key_exists('rating', $data)) {
                $rating = (int)$data['rating'];
                if ($rating < 1 || $rating > 5) {
                    $this->validationError($response, ['rating' => 'The rating must be between 1 and 5.']);
                    return;
                }
                $updates['rating'] = $rating;
            }

            if (array_key_exists('title', $data)) {
                $title = trim((string)$data['title']);
                if ($title === '') {
                    $this->validationError($response, ['title' => 'The title field is required.']);
                    return;
                }
                $updates['title'] = $title;
            }

            if (array_key_exists('review_text', $data)) {
                $reviewText = trim((string)$data['review_text']);
                if (strlen($reviewText) < 20) {
                    $this->validationError($response, ['review_text' => 'The review_text must be at least 20 characters.']);
                    return;
                }
                $updates['review_text'] = $reviewText;
            }

            if (empty($updates)) {
                $this->validationError($response, ['review' => 'No editable review fields provided.']);
                return;
            }

            $updates['updated_at'] = date('Y-m-d H:i:s');
            $review->fill($updates)->save();
        } catch (\Throwable $e) {
            error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
            $this->error($response, 'Database error occurred. Please try again.', 500);
            return;
        }

        $this->success($response, ['id' => $review->id]);
    }

    /**
     * DELETE /reviews/{id}
     * Delete review
     */
    public function delete(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $review = Review::find($id);
        if (!$review || !$this->canManageReview($review, (int)$user->id)) {
            $this->error($response, 'Review not found', 404);
            return;
        }

        try {
            $review->delete();
        } catch (\Throwable $e) {
            error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
            $this->error($response, 'Database error occurred. Please try again.', 500);
            return;
        }

        $this->success($response, [], 'Review deleted');
    }

    /**
     * GET /reviews/company/{id}/stats
     * Get company review statistics
     */
    public function companyStats(Request $request, Response $response, int $id): void
    {
        $reviews = Review::where('company_id', '=', $id)
            ->where('status', '=', 'approved')
            ->get();

        if (empty($reviews)) {
            $this->success($response, [
                'total_reviews' => 0,
                'average_rating' => 0,
                'rating_distribution' => []
            ]);
            return;
        }

        $ratings = array_map(static fn(Review $review): int => (int)$review->rating, $reviews);
        $avgRating = array_sum($ratings) / count($ratings);

        $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($ratings as $rating) {
            $distribution[(int)$rating]++;
        }

        $this->success($response, [
            'total_reviews' => count($reviews),
            'average_rating' => round($avgRating, 1),
            'rating_distribution' => [
                '5_stars' => $distribution[5],
                '4_stars' => $distribution[4],
                '3_stars' => $distribution[3],
                '2_stars' => $distribution[2],
                '1_star' => $distribution[1]
            ]
        ]);
    }

    private function resolveCompanyId(array $data): int
    {
        $companyId = (int)($data['company_id'] ?? 0);
        if ($companyId > 0) {
            return $companyId;
        }

        $employerId = (int)($data['employer_id'] ?? 0);
        if ($employerId <= 0) {
            return 0;
        }

        $row = Database::getInstance()->fetchOne(
            'SELECT id FROM companies WHERE employer_id = :employer_id LIMIT 1',
            ['employer_id' => $employerId]
        );

        return (int)($row['id'] ?? 0);
    }

    private function canManageReview(Review $review, int $userId): bool
    {
        if ((int)$review->user_id === $userId) {
            return true;
        }

        $candidate = Candidate::where('user_id', '=', $userId)->first();
        return $candidate && (int)$review->candidate_id === (int)$candidate->id;
    }
}
