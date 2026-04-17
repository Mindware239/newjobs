<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Services\UtilityService;
use App\Services\SkillService;

class UtilityController extends ApiController
{
    private UtilityService $utilityService;
    private SkillService $skillService;

    public function __construct()
    {
        $this->utilityService = new UtilityService();
        $this->skillService = new SkillService();
    }

    /**
     * GET /api/v1/locations/search
     */
    public function searchLocations(Request $request, Response $response): void
    {
        $query = trim($request->get('q') ?? '');
        $limit = (int)($request->get('limit') ?? 10);
        
        $result = $this->utilityService->searchLocations($query, $limit);
        $this->success($response, $result);
    }

    /**
     * GET /api/v1/locations/all
     */
    public function locations(Request $request, Response $response): void
    {
        $result = $this->utilityService->getAllLocations();
        $this->success($response, $result);
    }

    /**
     * GET /api/v1/job-titles/search
     */
    public function searchJobTitles(Request $request, Response $response): void
    {
        $query = trim($request->get('q') ?? '');
        $limit = (int)($request->get('limit') ?? 10);
        
        $result = $this->utilityService->searchJobTitles($query, $limit);
        $this->success($response, $result);
    }

    /**
     * GET /api/v1/skills/suggest
     */
    public function suggestSkills(Request $request, Response $response): void
    {
        $q = trim((string)($request->get('q') ?? ''));
        $title = trim((string)($request->get('title') ?? ''));
        $category = trim((string)($request->get('category') ?? ''));
        $limit = (int)($request->get('limit') ?? 10);

        $result = $this->skillService->getSuggestions($q, $title, $category, $limit);
        $this->success($response, $result);
    }

    /**
     * GET /api/v1/industries/all
     */
    public function listIndustries(Request $request, Response $response): void
    {
        $limit = (int)($request->get('limit') ?? 0);
        $result = $this->utilityService->getIndustries($limit);
        $this->success($response, $result);
    }

    /**
     * GET /api/v1/fcm-web-config
     */
    public function fcmWebConfig(Request $request, Response $response): void
    {
        $config = $this->utilityService->getFcmWebConfig();
        $this->success($response, $config);
    }

    /**
     * GET /app-version
     */
    public function appVersion(Request $request, Response $response): void
    {
        $currentVersion = $request->query('version', '1.0.0');
        $platform = $request->query('platform', 'ios');

        $latestVersion = '1.2.1';
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
     */
    public function maintenanceStatus(Request $request, Response $response): void
    {
        $this->success($response, [
            'in_maintenance' => false,
            'message' => null,
            'estimated_resumption' => null
        ]);
    }

    /**
     * GET /health
     */
    public function healthCheck(Request $request, Response $response): void
    {
        $this->success($response, [
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
