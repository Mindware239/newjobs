<?php

use App\Core\Router;
use App\Middlewares\AuthMiddleware;

use App\Controllers\Api\PushApiController;
use App\Controllers\Api\UserApiController;
use App\Controllers\Api\UtilityApiController;

$router = Router::getInstance();

$authMw = new AuthMiddleware();

// ==========================================
// PUBLIC API ROUTES
// ==========================================
$router->get('/api/fcm-web-config', [UtilityApiController::class, 'getFcmConfig']);
$router->get('/api/qualifications/suggest', [UtilityApiController::class, 'suggestSkills']);
$router->get('/api/job-titles/search', [UtilityApiController::class, 'searchJobTitles']);
$router->get('/api/locations/search', [UtilityApiController::class, 'searchLocations']);
$router->get('/api/locations/all', [UtilityApiController::class, 'getAllLocations']);
$router->get('/api/industries/all', [UtilityApiController::class, 'getAllIndustries']);

// Geo API
$router->post('/api/location/detect', [UtilityApiController::class, 'detectLocation']);
$router->get('/api/countries', [UtilityApiController::class, 'getCountries']);
$router->get('/api/states', [UtilityApiController::class, 'getStates']);
$router->get('/api/cities', [UtilityApiController::class, 'getCities']);

// ==========================================
// AUTHENTICATED API ROUTES
// ==========================================
$router->post('/api/push/register', [PushApiController::class, 'register'], [$authMw]);
$router->post('/api/push/unsubscribe', [PushApiController::class, 'unsubscribe'], [$authMw]);
$router->post('/api/push/test', [PushApiController::class, 'test'], [$authMw]);
$router->post('/api/notifications/preferences', [UserApiController::class, 'updatePreferences'], [$authMw]);
$router->post('/api/discount-code/validate', [UserApiController::class, 'validateDiscount'], [$authMw]);
