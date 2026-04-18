<?php

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Services\UtilityService;
use App\Services\SkillService;
use App\Services\GeoService;

class UtilityApiController
{
    private UtilityService $utilityService;
    private SkillService $skillService;
    private GeoService $geoService;

    public function __construct()
    {
        $this->utilityService = new UtilityService();
        $this->skillService = new SkillService();
        $this->geoService = new GeoService();
    }

    public function getFcmConfig(Request $request, Response $response): void
    {
        $response->json($this->utilityService->getFcmWebConfig());
    }

    public function suggestSkills(Request $request, Response $response): void
    {
        $q = trim((string)($request->get('q') ?? ''));
        $title = trim((string)($request->get('title') ?? ''));
        $category = trim((string)($request->get('category') ?? ''));
        $limit = (int)($request->get('limit') ?? 10);
        
        $result = $this->skillService->getSuggestions($q, $title, $category, $limit);
        $response->json($result);
    }

    public function searchJobTitles(Request $request, Response $response): void
    {
        $query = $request->get('q') ?? '';
        $limit = (int)($request->get('limit') ?? 10);
        
        $result = $this->utilityService->searchJobTitles((string)$query, $limit);
        $response->json($result);
    }

    public function searchLocations(Request $request, Response $response): void
    {
        $query = trim($request->get('q') ?? '');
        $limit = (int)($request->get('limit') ?? 10);

        $result = $this->utilityService->searchLocations((string)$query, $limit);
        $response->json($result);
    }

    public function getAllLocations(Request $request, Response $response): void
    {
        $result = $this->utilityService->getAllLocations();
        $response->json($result);
    }

    public function getAllIndustries(Request $request, Response $response): void
    {
        $limit = (int)($request->get('limit') ?? 0);
        $result = $this->utilityService->getIndustries($limit);
        $response->json($result);
    }

    public function detectLocation(Request $request, Response $response): void
    {
        $acceptLang = $request->header('Accept-Language', '');
        $location = $this->geoService->detectLocation($acceptLang);
        $response->json($location);
    }

    public function getCountries(Request $request, Response $response): void
    {
        $result = $this->geoService->getCountries();
        $response->json($result);
    }

    public function getStates(Request $request, Response $response): void
    {
        $country = $request->get('country', '');
        $result = $this->geoService->getStates((string)$country);
        $response->json($result);
    }

    public function getCities(Request $request, Response $response): void
    {
        $state = $request->get('state', '');
        $country = $request->get('country', '');
        $result = $this->geoService->getCities((string)$state, (string)$country);
        $response->json($result);
    }
}
