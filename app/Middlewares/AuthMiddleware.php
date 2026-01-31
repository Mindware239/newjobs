<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;

class AuthMiddleware implements MiddlewareInterface
{
    private array $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function handle(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            // Check for API key
            $apiKey = $request->header('X-API-Key');
            if ($apiKey) {
                $user = $this->verifyApiKey($apiKey);
                if ($user) {
                    return;
                }
            }

            // For normal browser GET requests, redirect to login (even if Accept contains JSON).
            // Only return JSON for AJAX or explicit API calls.
            if ($request->getMethod() === 'GET' && !$request->isAjax()) {
                $response->redirect('/login?redirect=' . urlencode($request->getPath()));
                return;
            }
            $response->setStatusCode(401);
            $response->json(['error' => 'Unauthorized']);
            return;
        }

        $user = User::find((int)$userId);
        if (!$user || $user->status !== 'active') {
            $response->setStatusCode(401);
            $response->json(['error' => 'Unauthorized']);
            return;
        }

        // Check role if specified
        if (isset($this->options['role'])) {
            $requiredRole = $this->options['role'];
            if (is_array($requiredRole)) {
                if (!in_array($user->role, $requiredRole)) {
                    $response->setStatusCode(403);
                    $response->json(['error' => 'Forbidden']);
                    return;
                }
            } elseif ($user->role !== $requiredRole) {
                $response->setStatusCode(403);
                $response->json(['error' => 'Forbidden']);
                return;
            }
        }
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

