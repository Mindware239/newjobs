<?php

declare(strict_types=1);

namespace App\Controllers\Company;

use App\Core\Request;
use App\Core\Response;
use App\Models\Company;

class CompanyReviewController
{
    public function store(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->json(['error' => 'Login required'], 401);
            return;
        }

        $companyId = (int)$request->param('id');
        if ($companyId <= 0) {
            $response->json(['error' => 'Invalid company id'], 422);
            return;
        }

        $data = $request->all();
        $title = trim((string)($data['title'] ?? ''));
        $text = trim((string)($data['review_text'] ?? ''));
        $rating = (int)($data['rating'] ?? 0);
        $reviewer = trim((string)($data['reviewer_name'] ?? ''));
        if ($title === '' || $text === '' || $rating < 1 || $rating > 5) {
            $response->json(['error' => 'Invalid review data'], 422);
            return;
        }

        $db = \App\Core\Database::getInstance();
        try {
            // Get candidate_id if user is a candidate
            $candidateId = null;
            try {
                $candidate = \App\Models\Candidate::where('user_id', '=', (int)$userId)->first();
                if ($candidate) {
                    $candidateId = $candidate->attributes['id'] ?? $candidate->id ?? null;
                }
            } catch (\Exception $e) {
                // Candidate not found, continue without candidate_id
            }
            
            $sql = "INSERT INTO reviews (company_id, user_id, candidate_id, reviewer_name, rating, title, review_text, status, created_at) 
                    VALUES (:cid, :uid, :candidate_id, :name, :rating, :title, :text, 'approved', NOW())";
            $db->execute($sql, [
                'cid' => $companyId,
                'uid' => (int)$userId,
                'candidate_id' => $candidateId,
                'name' => $reviewer !== '' ? $reviewer : 'Anonymous',
                'rating' => $rating,
                'title' => $title,
                'text' => $text
            ]);
        } catch (\Throwable $e) {
            $companyModel = new Company();
            $company = $companyModel->find($companyId);
            if (!$company) {
                $response->json(['error' => 'Company not found'], 404);
                return;
            }
            $parsed = [];
            $raw = $company['description'] ?? '';
            if (is_string($raw)) {
                $dec = json_decode($raw, true);
                $parsed = is_array($dec) ? $dec : [];
            }
            $reviews = isset($parsed['reviews']) && is_array($parsed['reviews']) ? $parsed['reviews'] : [];
            $reviews[] = [
                'reviewer_name' => $reviewer !== '' ? $reviewer : 'Anonymous',
                'rating' => $rating,
                'title' => $title,
                'review_text' => $text,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $parsed['reviews'] = $reviews;
            $companyModel->updateCompany($companyId, [
                'description' => json_encode($parsed, JSON_UNESCAPED_UNICODE)
            ]);
        }

        $companyModel = new Company();
        $companyModel->recalculateCompanyStats($companyId);
        $response->json(['success' => true]);
    }
}

