<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Services\GeoService;

class GeoController extends ApiController
{
    private GeoService $geoService;

    public function __construct()
    {
        $this->geoService = new GeoService();
    }

    /**
     * GET /api/v1/countries
     * Migrated from api.php - List supported countries
     */
    public function countries(Request $request, Response $response): void
    {
        $result = $this->geoService->getCountries();
        $this->success($response, $result);
    }

    /**
     * GET /api/v1/states
     * Migrated from api.php - List states for a country
     */
    public function states(Request $request, Response $response): void
    {
        $country = $request->get('country', '');
        if (empty($country)) {
            $this->error($response, 'Country is required', 400);
            return;
        }

        $result = $this->geoService->getStates($country);
        $this->success($response, $result);
    }

    /**
     * GET /api/v1/cities
     * Migrated from api.php - List cities for a state
     */
    public function cities(Request $request, Response $response): void
    {
        $state = $request->get('state', '');
        $country = $request->get('country', '');

        if (empty($state)) {
            $this->error($response, 'State is required', 400);
            return;
        }

        $result = $this->geoService->getCities($state, $country);
        $this->success($response, $result);
    }

    /**
     * POST /api/v1/location/detect
     * Migrated from api.php - Detect user location via Accept-Language
     */
    public function detectLocation(Request $request, Response $response): void
    {
        $acceptLang = $request->header('Accept-Language', '');
        $location = $this->geoService->detectLocation($acceptLang);
        $this->success($response, $location);
    }
}
