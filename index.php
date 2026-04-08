<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/storage/logs/php_errors.log');

use App\Core\Application;
use App\Core\Router;
use App\Middlewares\CsrfMiddleware;
use App\Middlewares\RateLimitMiddleware;

try {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');

    // ... (rest of session logic)
    $hostHeader = (string)($_SERVER['HTTP_HOST'] ?? '');
    $cookieDomain = '';
    if ($hostHeader) {
        $hostNoPort = preg_replace('/:\d+$/', '', $hostHeader);

        // For localhost and raw IPs, use host-specific session cookie (no domain wildcard)
        if (preg_match('/^(localhost|127\.0\.0\.1)$/i', $hostNoPort) || filter_var($hostNoPort, FILTER_VALIDATE_IP)) {
            $cookieDomain = '';
        } else {
            $parts = explode('.', $hostNoPort);
            if (count($parts) >= 2) {
                $apex = implode('.', array_slice($parts, -2));
                $cookieDomain = '.' . $apex;
            }
        }
    }

    // Ensure session cookie is accessible and secure on live server
    ini_set('session.cookie_httponly', '1');
    if ($isHttps) {
        ini_set('session.cookie_secure', '1');
    }
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $cookieDomain,
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }

    $currentCsrfCookie = $_COOKIE['XSRF-TOKEN'] ?? '';
    if ($currentCsrfCookie !== ($_SESSION['csrf_token'] ?? '')) {
        setcookie('XSRF-TOKEN', $_SESSION['csrf_token'], [
            'expires' => time() + 3600,
            'path' => '/',
            'domain' => $cookieDomain,
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    // Load .env from public_html
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    } catch (Exception $e) {
        // ignore
    }

    $app = new Application();

    $app->addMiddleware(new CsrfMiddleware());
    $app->addMiddleware(new RateLimitMiddleware());

    $router = Router::getInstance();

    // Load routes (NO ../)
    require_once __DIR__ . '/routes/front.php';
    require_once __DIR__ . '/routes/employer.php';
    require_once __DIR__ . '/routes/candidate.php';
    require_once __DIR__ . '/routes/admin.php';
    require_once __DIR__ . '/routes/api.php';
    require_once __DIR__ . '/routes/api_v1.php';
    require_once __DIR__ . '/routes/masteradmin.php';
    require_once __DIR__ . '/routes/sales.php';
    require_once __DIR__ . '/routes/bulk.php';

    $app->setRouter($router);
    $app->run();

} catch (\Throwable $e) {
    echo "<h1>Bootstrap Error</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " on line " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
