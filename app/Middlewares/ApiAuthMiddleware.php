<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Models\User;

class ApiAuthMiddleware implements MiddlewareInterface
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $authHeader = $request->header('Authorization');
        $token = null;
        
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        if ($token) {
            $decoded = $this->authService->validateToken($token);

            if (!$decoded) {
                $response->error('Unauthorized: Invalid or expired token', 401);
                return;
            }

            $userId = (int)$decoded['sub'];
        } else {
            // Fallback to session for web requests
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                $response->error('Unauthorized: Authentication required', 401);
                return;
            }
        }

        // Set the user in the request attributes for downstream use
        $user = User::find((int)$userId);
        if (!$user) {
            $response->error('Unauthorized: User not found', 401);
            return;
        }

        // Add user to request for controller access
        $request->setUser($user);

        $next($request, $response);
    }
}
