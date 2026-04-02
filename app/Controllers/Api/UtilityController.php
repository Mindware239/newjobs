<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Skill;
use App\Models\City;
use App\Models\JobTitle;
use App\Models\Company;

class UtilityController extends ApiController
{
    /**
     * GET /locations
     * List all cities/locations
     */
    public function locations(Request $request, Response $response): void
    {
        $search = $request->query('search', '');
        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('per_page', 20);

        $query = City::query();

        if ($search) {
            $query->where('name', 'LIKE', '%' . $search . '%');
        }

        $locations = $query->orderBy('name', 'ASC')->paginate($perPage, $page);

        $data = [];
        foreach ($locations['data'] as $location) {
            $data[] = [
                'id' => $location->id,
                'name' => $location->name,
                'state' => $location->state ?? null,
                'country' => $location->country ?? 'India'
            ];
        }

        $this->success($response, [
            'locations' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $locations['total'],
                'last_page' => ceil($locations['total'] / $perPage)
            ]
        ]);
    }

    /**
     * GET /job-titles
     * List job titles (autocomplete)
     */
    public function jobTitles(Request $request, Response $response): void
    {
        $search = $request->query('search', '');
        $limit = (int)$request->query('limit', 10);

        $query = JobTitle::query();

        if ($search) {
            $query->where('title', 'LIKE', '%' . $search . '%');
        }

        $titles = $query->orderBy('frequency', 'DESC')
            ->limit($limit)
            ->pluck('title')
            ->toArray();

        $this->success($response, [
            'job_titles' => $titles
        ]);
    }

    /**
     * GET /skills
     * List skills (autocomplete)
     */
    public function skills(Request $request, Response $response): void
    {
        $search = $request->query('search', '');
        $limit = (int)$request->query('limit', 10);

        $query = Skill::query();

        if ($search) {
            $query->where('name', 'LIKE', '%' . $search . '%');
        }

        $skills = $query->orderBy('name', 'ASC')
            ->limit($limit)
            ->pluck('name')
            ->toArray();

        $this->success($response, [
            'skills' => $skills
        ]);
    }

    /**
     * GET /companies
     * List companies
     */
    public function companies(Request $request, Response $response): void
    {
        $search = $request->query('search', '');
        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('per_page', 20);

        $query = Company::query();

        if ($search) {
            $query->where('name', 'LIKE', '%' . $search . '%');
        }

        $companies = $query->orderBy('name', 'ASC')->paginate($perPage, $page);

        $data = [];
        foreach ($companies['data'] as $company) {
            $data[] = [
                'id' => $company->id,
                'name' => $company->name,
                'logo' => $company->logo ?? null,
                'location' => $company->location ?? null,
                'industry' => $company->industry ?? null
            ];
        }

        $this->success($response, [
            'companies' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $companies['total'],
                'last_page' => ceil($companies['total'] / $perPage)
            ]
        ]);
    }

    /**
     * POST /feedback
     * Submit user feedback
     */
    public function submitFeedback(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'type' => 'required|in:bug,feature,improvement,other',
            'title' => 'required|string',
            'description' => 'required|string',
            'rating' => 'sometimes|numeric|min:1|max:5'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        // Store feedback in database
        // Implementation depends on your feedback model

        $this->success($response, [], 'Thank you for your feedback!', 201);
    }

    /**
     * POST /report
     * Report content/user
     */
    public function reportContent(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'report_type' => 'required|in:user,job,review,message',
            'target_id' => 'required|numeric',
            'reason' => 'required|string',
            'description' => 'sometimes|string'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        // Store report in database
        // Implementation depends on your report model

        $this->success($response, [], 'Report submitted. Thank you for helping us maintain a safe community.', 201);
    }

    /**
     * GET /app-version
     * Check app version and update status
     */
    public function appVersion(Request $request, Response $response): void
    {
        $currentVersion = $request->query('version', '1.0.0');
        $platform = $request->query('platform', 'ios'); // ios, android

        $latestVersion = match($platform) {
            'ios' => '1.2.1',
            'android' => '1.2.1',
            default => '1.2.1'
        };

        $updateRequired = version_compare($currentVersion, $latestVersion, '<');
        $updateAvailable = version_compare($currentVersion, $latestVersion, '!=');

        $this->success($response, [
            'current_version' => $currentVersion,
            'latest_version' => $latestVersion,
            'update_available' => $updateAvailable,
            'update_required' => $updateRequired,
            'download_url' => $updateRequired ? 'https://example.com/download/' . $platform : null,
            'release_notes' => 'Bug fixes and performance improvements'
        ]);
    }

    /**
     * GET /maintenance-status
     * Check app maintenance status
     */
    public function maintenanceStatus(Request $request, Response $response): void
    {
        // Check system settings or cache for maintenance status
        $inMaintenance = false;
        $maintenanceMessage = null;
        $estimatedResumption = null;

        $this->success($response, [
            'in_maintenance' => $inMaintenance,
            'message' => $maintenanceMessage,
            'estimated_resumption' => $estimatedResumption
        ]);
    }
}
