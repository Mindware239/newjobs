<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Models\User;
use App\Core\Request;
use App\Core\Response;

class MasterOnlyMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response, callable $next): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->redirect('/master/login');
            return;
        }

        $user = User::find((int)$userId);

        if (!$user || $user->role !== 'master') {
            // no permission – kick to some 403 or main dashboard
            $response->redirect('/admin/dashboard');
            return;
        }

        $request->setUser($user);

        $next($request, $response);
    }
}
