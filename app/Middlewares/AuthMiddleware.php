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

    public function handle(Request $request, Response $response, callable $next = null): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $user = null;

        if ($userId) {
            $user = User::find((int)$userId);
        }

        // JWT fallback
        if (!$user) {
            $token = null;

            $authHeader = $request->header('Authorization');
            if ($authHeader && preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
            } elseif (!empty($_COOKIE['access_token'])) {
                $token = $_COOKIE['access_token'];
            }

            if ($token) {
                $payload = $this->authService->validateToken($token);

                if (is_array($payload) && !empty($payload['sub'])) {
                    $user = User::find((int)$payload['sub']);

                    if ($user) {
                        $_SESSION['user_id'] = $user->id;
                        $_SESSION['user_role'] = $user->role;
                    }
                }
            }
        }

        // Unauthorized
        if (!$user) {
            $apiKey = $request->header('X-API-Key');

            if ($apiKey) {
                $user = $this->verifyApiKey($apiKey);

                if ($user) {
                    $request->setUser($user);

                    if ($next) {
                        $next($request, $response);
                    }
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

        // Inactive user
        if ($user->status !== 'active') {
            $response->setStatusCode(401);
            $response->json(['error' => 'Unauthorized']);
            return;
        }

        $request->setUser($user);

        // Role check
        if (isset($this->options['role'])) {
            $requiredRole = $this->options['role'];

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

        // SAFE NEXT CALL
        if ($next) {
            $next($request, $response);
        }
    }

    private function verifyApiKey(string $key): ?User
    {
        $sql = "SELECT eak.*, e.user_id 
                FROM employer_api_keys eak
                INNER JOIN employers e ON eak.employer_id = e.id
                WHERE eak.revoked = 0 AND eak.secret_hash = :hash";

        $hash = hash('sha256', $key);

        $db = \App\Core\Database::getInstance();

        $result = $db->fetchOne($sql, ['hash' => $hash]);

        if ($result) {
            $db->query(
                "UPDATE employer_api_keys SET last_used_at = NOW() WHERE id = :id",
                ['id' => $result['id']]
            );

            return User::find((int)$result['user_id']);
        }

        return null;
    }
}