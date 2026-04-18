<?php

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Services\NotificationService;
use App\Services\PaymentService;

class UserApiController
{
    public function updatePreferences(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->json(['error' => 'Unauthorized'], 401);
            return;
        }
        
        $data = $request->getJsonBody() ?? [];
        $prefs = [
            'in_app' => isset($data['in_app']) ? (bool)$data['in_app'] : true,
            'email' => isset($data['email']) ? (bool)$data['email'] : true,
            'push' => isset($data['push']) ? (bool)$data['push'] : false,
            'whatsapp' => isset($data['whatsapp']) ? (bool)$data['whatsapp'] : false
        ];

        if (NotificationService::updatePreferences((int)$userId, $prefs)) {
            $response->json(['success' => true, 'preferences' => $prefs]);
        } else {
            $response->json(['error' => 'update_failed'], 500);
        }
    }

    public function validateDiscount(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->json(['error' => 'Unauthorized'], 401);
            return;
        }
        
        $data = $request->getJsonBody() ?? [];
        $code = $data['code'] ?? '';
        $planId = (int)($data['plan_id'] ?? 0);
        $billingCycle = $data['billing_cycle'] ?? 'monthly';
        
        $service = new PaymentService();
        $result = $service->validateDiscount((string)$code, (int)$userId, $planId, $billingCycle);
        
        $response->json($result);
    }
}
