<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Helpers/GlobalHelpers.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/storage/logs/php_errors.log');

use App\Core\Application;
use App\Core\Router;
use App\Middlewares\CorsMiddleware;
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
        $dotenv = Dotenv\Dotenv::createUnsafeMutable(__DIR__);
        $dotenv->load();
        // Local machine overrides (gitignored, never deployed) – e.g. local DB credentials.
        if (is_file(__DIR__ . '/.env.local')) {
            Dotenv\Dotenv::createUnsafeMutable(__DIR__, '.env.local')->load();
        }
    } catch (Exception $e) {
        // ignore
    }

    $app = new Application();

    $app->addMiddleware(new CorsMiddleware());
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
    // Professional Error UI for Bootstrap Failures
    $isDev = (($_ENV['APP_DEBUG'] ?? 'false') === 'true');
    $errorMessage = $e->getMessage();
    $errorTrace = $e->getTraceAsString();
    $errorFile = $e->getFile();
    $errorLine = $e->getLine();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>System Maintenance | Jobsence</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Plus Jakarta Sans', sans-serif; background: linear-gradient(180deg, #fff1ed 0%, #fff8f5 100%); }
            .error-glow { background: radial-gradient(circle, rgba(255, 90, 54, 0.1) 0%, transparent 70%); filter: blur(80px); }
            .glass-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.5); }
        </style>
    </head>
    <body class="min-h-screen flex items-center justify-center p-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] error-glow -z-10"></div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] error-glow -z-10"></div>

        <div class="max-w-2xl w-full text-center">
            <div class="mb-8 inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-orange-50 text-[#ff5a36] shadow-inner">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>

            <h1 class="text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">System Under Maintenance</h1>
            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                We're currently performing some essential updates to our database systems. 
                Please try refreshing the page in a few minutes.
            </p>

            <?php if ($isDev): ?>
                <div class="glass-card rounded-2xl p-6 text-left mb-8 overflow-hidden">
                    <h3 class="text-sm font-bold text-red-500 uppercase tracking-wider mb-3">Debug Information</h3>
                    <p class="text-xs font-mono text-gray-800 break-words mb-2"><strong>Error:</strong> <?= htmlspecialchars($errorMessage) ?></p>
                    <p class="text-xs font-mono text-gray-500 mb-4"><strong>File:</strong> <?= htmlspecialchars($errorFile) ?> (Line <?= $errorLine ?>)</p>
                    <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                        <pre class="text-[10px] text-green-400 font-mono"><?= htmlspecialchars($errorTrace) ?></pre>
                    </div>
                </div>
            <?php endif; ?>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <button onclick="window.location.reload()" class="w-full sm:w-auto px-8 py-3 bg-[#ff5a36] text-white font-bold rounded-full shadow-lg hover:bg-[#e54e2d] transition-all">
                    Try Refreshing
                </button>
                <a href="mailto:gm@jobsence.com" class="w-full sm:w-auto px-8 py-3 bg-white text-gray-700 border border-gray-200 font-bold rounded-full hover:bg-gray-50 transition-all">
                    Contact Support
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
}
