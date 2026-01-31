<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;

class SuperAdminMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            $response->redirect('/admin/login');
            return;
        }

        $user = User::find((int)$userId);
        if (!$user || !$user->isSuperAdmin()) {
            $response->setStatusCode(403);
            $response->json(['error' => 'Forbidden - Super Admin access required']);
            return;
        }
    }
}

