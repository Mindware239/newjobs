<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Services\AuthService;

class AuthMiddleware implements MiddlewareInterface
{
    private array $options;
    private AuthService $authService;

    public function __construct(array $options = [])
    {
        $this->options = $options;
        $this->authService = new AuthService();
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $user = null;

        if ($userId) {
            $user = User::find((int)$userId);
        }

        // Fallback: JWT from Authorization header or access_token cookie
        if (!$user) {
            $token = null;
            $authHeader = $request->header('Authorization');
            if ($authHeader && preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
            } elseif (!empty($_COOKIE['access_token'])) {
                $token = $_COOKIE['access_token'];
            }

            $jwtCookieEnabled = ($_ENV['WEB_JWT_COOKIE'] ?? '1') === '1';
            if (!$token && !$jwtCookieEnabled) {
                error_log('AuthMiddleware: WEB_JWT_COOKIE disabled and access_token cookie missing');
            }

            if ($token) {
                $payload = $this->authService->validateToken($token);
                if (is_array($payload) && !empty($payload['sub'])) {
                    $user = User::find((int)$payload['sub']);
                    if ($user) {
                        // keep session and user context in sync
                        $_SESSION['user_id'] = $user->id;
                        $_SESSION['user_role'] = $user->role;
                    }
                }
            }
        }

        if (!$user) {
            // Check for API key
            $apiKey = $request->header('X-API-Key');
            if ($apiKey) {
                $user = $this->verifyApiKey($apiKey);
                if ($user) {
                    $request->setUser($user);
                    $next($request, $response);
                    return;
                }
            }

            if ($request->getMethod() === 'GET' && !$request->isAjax()) {
                $response->redirect('/login?redirect=' . urlencode($request->getPath()));
                return;
            }
            $response->setStatusCode(401);
            $response->json(['error' => 'Unauthorized']);
            return;
        }

        if ($user->status !== 'active') {
            $response->setStatusCode(401);
            $response->json(['error' => 'Unauthorized']);
            return;
        }

        $request->setUser($user);

        // Check role if specified
        if (isset($this->options['role'])) {
            $requiredRole = $this->options['role'];
            
            // Allow admin/super_admin to bypass role checks for other roles
            $isAdmin = in_array($user->role, ['admin', 'super_admin']);
            
            if (is_array($requiredRole)) {
                if (!in_array($user->role, $requiredRole) && !$isAdmin) {
                    $response->setStatusCode(403);
                    $response->json(['error' => 'Forbidden']);
                    return;
                }
            } elseif ($user->role !== $requiredRole && !$isAdmin) {
                $response->setStatusCode(403);
                $response->json(['error' => 'Forbidden']);
                return;
            }
        }

        $next($request, $response);
    }

    private function verifyApiKey(string $key): ?User
    {
        // API key verification
        $sql = "SELECT eak.*, e.user_id 
                FROM employer_api_keys eak
                INNER JOIN employers e ON eak.employer_id = e.id
                WHERE eak.revoked = 0 AND eak.secret_hash = :hash";
        
        $hash = hash('sha256', $key);
        $result = \App\Core\Database::getInstance()->fetchOne($sql, ['hash' => $hash]);
        
        if ($result) {
            // Update last used
            \App\Core\Database::getInstance()->query(
                "UPDATE employer_api_keys SET last_used_at = NOW() WHERE id = :id",
                ['id' => $result['id']]
            );
            
            return User::find((int)$result['user_id']);
        }

        return null;
    }
}

