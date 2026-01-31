<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;

class CsrfMiddleware implements MiddlewareInterface
{
    private array $excludedMethods = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, Response $response): void
    {
        $method = $request->getMethod();
        
        if (in_array($method, $this->excludedMethods)) {
            return;
        }

        // Skip for API routes
        if (strpos($request->getPath(), '/api/') === 0) {
            return;
        }

        // Get token from header or POST data
        $token = $request->header('X-CSRF-Token') ?? $request->post('_token') ?? ($_COOKIE['XSRF-TOKEN'] ?? null);
        $sessionToken = $_SESSION['csrf_token'] ?? null;

        // Debug logging
        error_log("CSRF Check - Token: " . ($token ? 'present' : 'missing') . ", Session: " . ($sessionToken ? 'present' : 'missing'));
        error_log("CSRF Check - Path: " . $request->getPath());

        if (!$token || !$sessionToken) {
            error_log("CSRF token missing - Token: " . ($token ? 'yes' : 'no') . ", Session: " . ($sessionToken ? 'yes' : 'no'));
            $response->setStatusCode(403);
            $response->json(['error' => 'CSRF token missing. Please refresh the page.']);
            return;
        }

        if (!hash_equals($sessionToken, $token)) {
            error_log("CSRF token mismatch - Expected: " . substr($sessionToken, 0, 10) . "... Got: " . substr($token, 0, 10) . "...");
            $response->setStatusCode(403);
            $response->json(['error' => 'CSRF token mismatch. Please refresh the page and try again.']);
            return;
        }
    }

    public static function generateToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

