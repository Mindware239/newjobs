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
        $issuedAt = (int)($_SESSION['csrf_token_time'] ?? 0);

        // Debug logging
        error_log("CSRF Check - Token: " . ($token ? 'present' : 'missing') . ", Session: " . ($sessionToken ? 'present' : 'missing'));
        error_log("CSRF Check - Path: " . $request->getPath());

        if (!$token || !$sessionToken) {
            error_log("CSRF token missing - Token: " . ($token ? 'yes' : 'no') . ", Session: " . ($sessionToken ? 'yes' : 'no'));
            $new = self::generateToken();
            if (!headers_sent()) {
                $ttl = 86400;
                setcookie('XSRF-TOKEN', $new, time() + $ttl, '/', '', false, false);
            }
            $response->setStatusCode(403);
            $response->json(['error' => 'Your session was refreshed. Please try again.', 'refresh_csrf' => true, 'csrf_token' => $new]);
            return;
        }

        if (!hash_equals($sessionToken, $token)) {
            error_log("CSRF token mismatch - Expected: " . substr($sessionToken, 0, 10) . "... Got: " . substr($token, 0, 10) . "...");
            $new = self::generateToken();
            if (!headers_sent()) {
                $ttl = 86400;
                setcookie('XSRF-TOKEN', $new, time() + $ttl, '/', '', false, false);
            }
            $response->setStatusCode(403);
            $response->json(['error' => 'We refreshed your session. Please try again.', 'refresh_csrf' => true, 'csrf_token' => $new]);
            return;
        }
        $maxAge = 86400;
        if ($issuedAt > 0 && (time() - $issuedAt) > $maxAge) {
            $new = self::generateToken();
            if (!headers_sent()) {
                $ttl = 86400;
                setcookie('XSRF-TOKEN', $new, time() + $ttl, '/', '', false, false);
            }
            $response->setStatusCode(403);
            $response->json(['error' => 'Your session expired and was refreshed. Please try again.', 'refresh_csrf' => true, 'csrf_token' => $new]);
            return;
        }
    }

    public static function generateToken(): string
    {
        $now = time();
        $issued = (int)($_SESSION['csrf_token_time'] ?? 0);
        $maxAge = 86400;
        if (empty($_SESSION['csrf_token']) || ($issued > 0 && ($now - $issued) > $maxAge)) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = $now;
        }
        return $_SESSION['csrf_token'];
    }
}

