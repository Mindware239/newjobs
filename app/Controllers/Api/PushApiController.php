<?php

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Services\NotificationService;

class PushApiController
{
    public function register(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->json(['error' => 'Unauthorized'], 401);
            return;
        }
        
        $data = $request->getJsonBody() ?? [];
        $token = trim((string)($data['token'] ?? ''));
        
        if ($token === '') {
            $response->json(['error' => 'token_required'], 400);
            return;
        }

        try {
            $device = isset($data['device']) ? (string)$data['device'] : (string)($_SERVER['HTTP_SEC_CH_UA_PLATFORM'] ?? '');
            $browser = isset($data['browser']) ? (string)$data['browser'] : (string)($_SERVER['HTTP_USER_AGENT'] ?? '');

            $ok = NotificationService::registerToken((int)$userId, $token, $device, $browser);
            if ($ok) {
                $response->json(['success' => true]);
            } else {
                $response->json(['error' => 'update_failed'], 500);
            }
        } catch (\Throwable $t) {
            $response->json(['error' => 'update_failed', 'message' => $t->getMessage()], 500);
        }
    }

    public function unsubscribe(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->json(['error' => 'Unauthorized'], 401);
            return;
        }
        
        $data = $request->getJsonBody() ?? [];
        $token = trim((string)($data['token'] ?? ''));
        
        if ($token === '') {
            $response->json(['error' => 'token_required'], 400);
            return;
        }
        
        if (NotificationService::unregisterToken((int)$userId, $token)) {
            $response->json(['success' => true]);
        } else {
            $response->json(['error' => 'unsubscribe_failed'], 500);
        }
    }

    public function test(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->json(['error' => 'Unauthorized'], 401);
            return;
        }
        
        $ok = NotificationService::sendPush((int)$userId, 'Test Notification', 'Browser push is working', '/');
        $response->json(['success' => $ok]);
    }
}
