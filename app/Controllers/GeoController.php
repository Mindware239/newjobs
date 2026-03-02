<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class GeoController extends BaseController
{
    private function proxyFetch(string $url, Response $response): void
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", [
                    'User-Agent: MindwareInfotech/1.0 (Job Portal)',
                    'Accept: application/json',
                    'Accept-Language: en'
                ]),
                'timeout' => 7
            ]
        ]);

        $raw = @file_get_contents($url, false, $context);
        if ($raw === false) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, OPTIONS');
            header('Access-Control-Allow-Headers: *');
            $response->json(['error' => 'Upstream request failed'], 502);
            return;
        }

        $data = json_decode($raw, true);
        if ($data === null) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, OPTIONS');
            header('Access-Control-Allow-Headers: *');
            $response->json(['error' => 'Invalid JSON from upstream'], 502);
            return;
        }

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: *');
        header('Cache-Control: public, max-age=120');
        $response->json($data);
    }

    public function reverse(Request $request, Response $response): void
    {
        $lat = (string)$request->get('lat', '');
        $lon = (string)$request->get('lon', '');
        if ($lat === '' || $lon === '') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, OPTIONS');
            header('Access-Control-Allow-Headers: *');
            $response->json(['error' => 'lat and lon are required'], 400);
            return;
        }
        $url = 'https://nominatim.openstreetmap.org/reverse?' . http_build_query([
            'format' => 'json',
            'lat' => $lat,
            'lon' => $lon,
            'addressdetails' => 1
        ]);
        $this->proxyFetch($url, $response);
    }

    public function search(Request $request, Response $response): void
    {
        $q = (string)$request->get('q', '');
        $limit = (int)$request->get('limit', 1);
        if ($q === '') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, OPTIONS');
            header('Access-Control-Allow-Headers: *');
            $response->json(['error' => 'q is required'], 400);
            return;
        }
        $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
            'q' => $q,
            'format' => 'json',
            'limit' => max(1, min($limit, 5))
        ]);
        $this->proxyFetch($url, $response);
    }
}

