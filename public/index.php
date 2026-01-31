<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Don't display errors to users
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../storage/logs/php_errors.log');

use App\Core\Application;
use App\Core\Router;
use App\Middlewares\CsrfMiddleware;
use App\Middlewares\RateLimitMiddleware;

session_start();

// Add CSRF token to meta tag for JavaScript
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
setcookie('XSRF-TOKEN', $_SESSION['csrf_token'], [
    'expires' => time() + 3600,
    'path' => '/',
    'secure' => false,
    'httponly' => false,
    'samesite' => 'Lax',
]);

// Load environment variables
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    // .env file not found, using defaults
    // Make sure to create .env file from .env.example
}

// Initialize application
$app = new Application();

// Register global middlewares
$app->addMiddleware(new CsrfMiddleware());
$app->addMiddleware(new RateLimitMiddleware());

// Load routes
$router = Router::getInstance();

// Front routes
require_once __DIR__ . '/../routes/front.php';

// Employer routes
require_once __DIR__ . '/../routes/employer.php';

// Candidate routes
require_once __DIR__ . '/../routes/candidate.php';

// Admin routes
require_once __DIR__ . '/../routes/admin.php';

// API routes
require_once __DIR__ . '/../routes/api.php';

// Master Admin routes
require_once __DIR__ . '/../routes/masteradmin.php';

// Master Admin routes
require_once __DIR__ . '/../routes/masteradmin.php';

// Sales CRM routes
require_once __DIR__ . '/../routes/sales.php';

$app->setRouter($router);
$app->run();

