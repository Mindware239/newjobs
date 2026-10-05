<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Services\JobService;
use App\Models\Application;
use App\Models\Candidate;

class JobController extends ApiController
{
    private JobService $jobService;

    public function __construct()
    {
        $this->jobService = new JobService();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/jobs",
     *     summary="List and search jobs",
     *     tags={"Jobs"},
     *     @OA\Parameter(name="keyword", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="location", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="A list of jobs")
     * )
     */
    public function index(Request $request, Response $response): void
    {
        $filters = $this->normalizeSearchFilters($request->all());
        $userId = $request->user() ? $request->user()->id : null;

        $result = $this->formatJobsForMobile($this->jobService->searchJobs($filters, $userId));
        $message = !empty($result['message'])
            ? (string)$result['message']
            : ($this->hasNoJobs($result)
            ? 'No jobs found. Try different keyword or location'
            : 'Success');

        $this->success($response, $result, $message);
    }

    private function normalizeSearchFilters(array $filters): array
    {
        $normalized = $filters;

        foreach ($filters as $key => $value) {
            $lowerKey = strtolower((string)$key);
            if (!array_key_exists($lowerKey, $normalized)) {
                $normalized[$lowerKey] = $value;
            }
        }

        return $normalized;
    }

    private function hasNoJobs(array $result): bool
    {
        if (isset($result['pagination']['total'])) {
            return (int)$result['pagination']['total'] === 0;
        }

        return isset($result['jobs']) && count($result['jobs']) === 0;
    }

    private function formatJobsForMobile(array $result): array
    {
        if (empty($result['jobs']) || !is_array($result['jobs'])) {
            return $result;
        }

        $result['jobs'] = array_map(function(array $job): array {
            $job['description'] = $this->plainText($job['description'] ?? '');
            $job['short_description'] = $this->plainText($job['short_description'] ?? $job['description'] ?? '');
            
            // Normalize Locations - use location_names if locations JSON is empty
            $job['locations'] = $this->normalizeLocations($job['locations'] ?? null, $job['location_names'] ?? null);
            
            // Normalize Qualifications
            $job['qualifications'] = $this->normalizeQualifications($job['qualifications'] ?? null);
            
            $job['salary_display'] = $this->formatSalaryDisplay($job);
            $job['experience_display'] = $this->formatExperienceDisplay($job);
            $job['employment_type_display'] = ucfirst(str_replace('_', ' ', (string)($job['employment_type'] ?? 'full_time')));
            $job['posted_at'] = $job['created_at'] ?? null;

            return $job;
        }, $result['jobs']);

        return $result;
    }

    private function plainText($value): string
    {
        $text = html_entity_decode(strip_tags((string)$value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }

    private function normalizeLocations($value, $fallback = null): array
    {
        if (is_array($value) && !empty($value)) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '' && $value !== '[]') {
            $decoded = json_decode($value, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }

        // Use fallback (location_names from JOIN) if available
        if (!empty($fallback)) {
            $parts = explode(' | ', (string)$fallback);
            return array_map(function($loc) {
                return ['display' => trim($loc)];
            }, $parts);
        }

        return [];
    }

    private function normalizeQualifications($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || trim($value) === '' || $value === '[]') {
            return [];
        }

        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return [trim($value)];
    }

    private function formatSalaryDisplay(array $job): string
    {
        $currency = (string)($job['currency'] ?? 'INR');
        $min = $job['salary_min'] ?? null;
        $max = $job['salary_max'] ?? null;

        if ($min !== null && $max !== null) {
            return "{$currency} {$min} - {$max}";
        }

        if ($min !== null) {
            return "{$currency} {$min}+";
        }

        if ($max !== null) {
            return "Up to {$currency} {$max}";
        }

        return 'Not disclosed';
    }

    private function formatExperienceDisplay(array $job): string
    {
        $min = $job['min_experience'] ?? null;
        $max = $job['max_experience'] ?? null;

        if ($min !== null && $max !== null) {
            return "{$min}-{$max} years";
        }

        if ($min !== null) {
            return "{$min}+ years";
        }

        return 'Not specified';
    }

    /**
     * @OA\Get(
     *     path="/api/v1/jobs/{slug}",
     *     summary="Get job details by slug",
     *     tags={"Jobs"},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Job details")
     * )
     */
    public function show(Request $request, Response $response, array $params): void
    {
        $slug = $params['slug'] ?? '';
        $userId = $request->user() ? $request->user()->id : null;

        $job = $this->jobService->getJobBySlug($slug, $userId);

        if (!$job) {
            $this->error($response, 'Job not found', 404);
            return;
        }

        $this->success($response, $job);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/jobs/{id}/apply",
     *     summary="Apply for a job",
     *     tags={"Jobs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Application successful")
     * )
     */
    public function apply(Request $request, Response $response, array $params): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $jobId = (int)($params['id'] ?? 0);
        $data = $request->getJsonBody();

        $applicationService = new \App\Services\ApplicationService();
        $result = $applicationService->submitApplication((int)$user->id, $jobId, $data);

        if (!$result['success']) {
            $this->error($response, $result['message'], $result['code'] ?? 400, [
                'incomplete_profile' => $result['incomplete_profile'] ?? false,
                'missing_fields' => $result['missing_fields'] ?? [],
                'profile_strength' => $result['profile_strength'] ?? 0
            ]);
            return;
        }

        $this->success($response, $result, $result['message'] ?? 'Application submitted successfully');
    }
}
