<?php

use App\Core\Router;
use App\Core\Request;
use App\Core\Response;

$router = \App\Core\Router::getInstance();

$router->get('/api/fcm-web-config', function(Request $request, Response $response) {
    $service = new \App\Services\UtilityService();
    $response->json($service->getFcmWebConfig());
});

$router->post('/api/push/register', function(Request $request, Response $response) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        $response->json(['error' => 'Unauthorized'], 401);
        return;
    }
    $data = $request->getJsonBody() ?? [];
    $token = trim((string)($data['token'] ?? ''));
    if ($token === '') {
        $response->json(['error' => 'token_required'], 400);
        return;
    }

    try {
        $device = isset($data['device']) ? (string)$data['device'] : (string)($_SERVER['HTTP_SEC_CH_UA_PLATFORM'] ?? '');
        $browser = isset($data['browser']) ? (string)$data['browser'] : (string)($_SERVER['HTTP_USER_AGENT'] ?? '');

        $ok = \App\Services\NotificationService::registerToken((int)$userId, $token, $device, $browser);
        if ($ok) {
            $response->json(['success' => true]);
        } else {
            $response->json(['error' => 'update_failed'], 500);
        }
    } catch (\Throwable $t) {
        $response->json(['error' => 'update_failed', 'message' => $t->getMessage()], 500);
    }
});

// Skills Autocomplete API (Public - no auth required)
$router->get('/api/qualifications/suggest', function(Request $request, Response $response) {
    $q = trim((string)($request->get('q') ?? ''));
    $title = trim((string)($request->get('title') ?? ''));
    $category = trim((string)($request->get('category') ?? ''));
    $limit = (int)($request->get('limit') ?? 10);
    
    $service = new \App\Services\SkillService();
    $result = $service->getSuggestions($q, $title, $category, $limit);
    
    $response->json($result);
});

$router->post('/api/push/unsubscribe', function(Request $request, Response $response) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        $response->json(['error' => 'Unauthorized'], 401);
        return;
    }
    $data = $request->getJsonBody() ?? [];
    $token = trim((string)($data['token'] ?? ''));
    if ($token === '') {
        $response->json(['error' => 'token_required'], 400);
        return;
    }
    
    if (\App\Services\NotificationService::unregisterToken((int)$userId, $token)) {
        $response->json(['success' => true]);
    } else {
        $response->json(['error' => 'unsubscribe_failed'], 500);
    }
});

$router->post('/api/push/test', function(Request $request, Response $response) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        $response->json(['error' => 'Unauthorized'], 401);
        return;
    }
    $ok = \App\Services\NotificationService::sendPush((int)$userId, 'Test Notification', 'Browser push is working', '/');
    $response->json(['success' => $ok]);
});

$router->post('/api/notifications/preferences', function(Request $request, Response $response) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        $response->json(['error' => 'Unauthorized'], 401);
        return;
    }
    $data = $request->getJsonBody() ?? [];
    $prefs = [
        'in_app' => isset($data['in_app']) ? (bool)$data['in_app'] : true,
        'email' => isset($data['email']) ? (bool)$data['email'] : true,
        'push' => isset($data['push']) ? (bool)$data['push'] : false,
        'whatsapp' => isset($data['whatsapp']) ? (bool)$data['whatsapp'] : false
    ];

    if (\App\Services\NotificationService::updatePreferences((int)$userId, $prefs)) {
        $response->json(['success' => true, 'preferences' => $prefs]);
    } else {
        $response->json(['error' => 'update_failed'], 500);
    }
});

// Discount Code Validation API (requires auth)
$router->post('/api/discount-code/validate', function(Request $request, Response $response) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        $response->json(['error' => 'Unauthorized'], 401);
        return;
    }
    
    $data = $request->getJsonBody() ?? [];
    $code = $data['code'] ?? '';
    $planId = (int)($data['plan_id'] ?? 0);
    $billingCycle = $data['billing_cycle'] ?? 'monthly';
    
    $service = new \App\Services\PaymentService();
    $result = $service->validateDiscount((string)$code, (int)$userId, $planId, $billingCycle);
    
    $response->json($result);
});

// Job Titles Autocomplete API (Public - no auth required)
$router->get('/api/job-titles/search', function(Request $request, Response $response) {
    $query = $request->get('q') ?? '';
    $limit = (int)($request->get('limit') ?? 10);
    
    $service = new \App\Services\UtilityService();
    $result = $service->searchJobTitles((string)$query, $limit);
    
    $response->json($result);
});

// Location Autocomplete API (Public - no auth required)
$router->get('/api/locations/search', function(Request $request, Response $response) {
    $query = trim($request->get('q') ?? '');
    $limit = (int)($request->get('limit') ?? 10);

    $service = new \App\Services\UtilityService();
    $result = $service->searchLocations((string)$query, $limit);

    return $response->json($result);
});

// Get All Locations for Filter (Public - no auth required)
$router->get('/api/locations/all', function(Request $request, Response $response) {
    $service = new \App\Services\UtilityService();
    $result = $service->getAllLocations();
    $response->json($result);
});

// Get All Industries for Filter (Public - no auth required)
$router->get('/api/industries/all', function(Request $request, Response $response) {
    $limit = (int)($request->get('limit') ?? 0);
    $service = new \App\Services\UtilityService();
    $result = $service->getIndustries($limit);
    
    $response->json($result);
});

// Detect user location (country) server-side without external IP APIs
$router->post('/api/location/detect', function(Request $request, Response $response) {
    $acceptLang = $request->header('Accept-Language', '');
    $service = new \App\Services\GeoService();
    $location = $service->detectLocation($acceptLang);
    $response->json($location);
});

// Get Countries API
$router->get('/api/countries', function(Request $request, Response $response) {
    $service = new \App\Services\GeoService();
    $result = $service->getCountries();
    $response->json($result);
});

// Get States API
$router->get('/api/states', function(Request $request, Response $response) {
    $country = $request->get('country', '');
    $service = new \App\Services\GeoService();
    $result = $service->getStates((string)$country);
    $response->json($result);
});

// Get Cities API
$router->get('/api/cities', function(Request $request, Response $response) {
    $state = $request->get('state', '');
    $country = $request->get('country', '');
    $service = new \App\Services\GeoService();
    $result = $service->getCities((string)$state, (string)$country);
    $response->json($result);
});
