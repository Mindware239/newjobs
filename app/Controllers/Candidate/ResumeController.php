<?php

declare(strict_types=1);

namespace App\Controllers\Candidate;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Services\ResumeTextExtractor;
use App\Services\AIResumeParser;
use App\Services\CandidateProfileService;

/**
 * Resume Controller
 * 
 * Handles resume parsing and profile updates
 */
class ResumeController extends BaseController
{
    /**
     * Parse resume and update candidate profile
     * 
     * POST /candidate/resume/parse
     */
    public function parseResume(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $candidate = Candidate::findByUserId((int)$userId);
        if (!$candidate) {
            $response->json(['error' => 'Candidate profile not found'], 404);
            return;
        }

        $resumeUrl = $candidate->attributes['resume_url'] ?? null;
        if (empty($resumeUrl)) {
            $response->json(['error' => 'No resume uploaded'], 400);
            return;
        }

        // Convert URL to file path
        $resumePath = $this->urlToFilePath($resumeUrl);
        if (!file_exists($resumePath)) {
            $response->json(['error' => 'Resume file not found'], 404);
            return;
        }

        try {
            // Initialize services
            $extractor = new ResumeTextExtractor();
            $aiParser = new AIResumeParser($extractor);
            $profileService = new CandidateProfileService();

            // Parse resume
            $parsedData = $aiParser->parseResume($resumePath);

            // Update candidate profile
            $success = $profileService->updateProfileFromParsedData((int)$userId, $parsedData);

            if ($success) {
                $response->json([
                    'success' => true,
                    'message' => 'Resume parsed and profile updated successfully',
                    'data' => [
                        'skills_count' => count($parsedData['skills'] ?? []),
                        'education_count' => count($parsedData['education'] ?? []),
                        'experience_count' => count($parsedData['experience'] ?? [])
                    ]
                ]);
            } else {
                $response->json(['error' => 'Failed to update profile'], 500);
            }

        } catch (\Exception $e) {
            error_log("Resume parsing error: " . $e->getMessage());
            $response->json([
                'error' => 'Failed to parse resume',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert resume URL to file system path
     * 
     * @param string $url Resume URL
     * @return string File path
     */
    private function urlToFilePath(string $url): string
    {
        // Remove protocol and domain
        $path = str_replace(['http://', 'https://'], '', $url);
        $path = preg_replace('/^[^\/]+/', '', $path); // Remove domain
        
        // Convert to absolute path
        $basePath = $_SERVER['DOCUMENT_ROOT'] ?? __DIR__ . '/../../..';
        $fullPath = $basePath . $path;

        return realpath($fullPath) ?: $fullPath;
    }
}

