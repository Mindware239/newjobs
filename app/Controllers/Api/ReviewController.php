<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Review;

class ReviewController extends ApiController
{
    /**
     * GET /reviews/company/{id}
     * List company reviews
     */
    public function companyReviews(Request $request, Response $response, int $id): void
    {
        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('per_page', 10);
        $sortBy = $request->query('sort', 'latest'); // latest, helpful, rating_high, rating_low

        $query = Review::where('employer_id', '=', $id)
            ->where('is_published', '=', true);

        $query = match($sortBy) {
            'helpful' => $query->orderBy('helpful_count', 'DESC'),
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
            'employer_id' => 'required|numeric',
            'rating' => 'required|numeric|min:1|max:5',
            'title' => 'required|string',
            'review_text' => 'required|string|min:20'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        // Check if user worked for this employer
        // Implementation depends on employment verification logic

        $review = new Review();
        $review->fill([
            'employer_id' => (int)$request->input('employer_id'),
            'candidate_id' => $user->id,
            'rating' => (int)$request->input('rating'),
            'title' => $request->input('title'),
            'review_text' => $request->input('review_text'),
            'is_published' => true
        ])->save();

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

        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('per_page', 10);

        $reviews = Review::where('candidate_id', '=', $user->id)
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
        if (!$review || $review->candidate_id !== $user->id) {
            $this->error($response, 'Review not found', 404);
            return;
        }

        // Only allow editing within 30 days
        if (strtotime($review->created_at) < time() - (30 * 24 * 60 * 60)) {
            $this->error($response, 'Cannot edit review after 30 days', 400);
            return;
        }

        $review->fill($request->getJsonBody())->save();

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
        if (!$review || $review->candidate_id !== $user->id) {
            $this->error($response, 'Review not found', 404);
            return;
        }

        $review->delete();

        $this->success($response, [], 'Review deleted');
    }

    /**
     * GET /reviews/company/{id}/stats
     * Get company review statistics
     */
    public function companyStats(Request $request, Response $response, int $id): void
    {
        $reviews = Review::where('employer_id', '=', $id)
            ->where('is_published', '=', true)
            ->get();

        if ($reviews->isEmpty()) {
            $this->success($response, [
                'total_reviews' => 0,
                'average_rating' => 0,
                'rating_distribution' => []
            ]);
            return;
        }

        $ratings = $reviews->pluck('rating')->toArray();
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
}
