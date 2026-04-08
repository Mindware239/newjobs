<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;

class RbacMiddleware implements MiddlewareInterface
{
    private string $permission;

    public function __construct(string $permission)
    {
        $this->permission = $permission;
    }

    public function handle(Request $request, Response $response, callable $next = null): void
    {
        $userId = $_SESSION['user_id'] ?? null;

        //  Not logged in
        if (!$userId) {
            $response->redirect('/admin/login');
            return;
        }

        $user = User::find((int)$userId);

        //  Not admin
        if (!$user || !$user->isAdmin()) {
            $response->setStatusCode(403);
            $response->json(['error' => 'Forbidden']);
            return;
        }

        //  Permission check
        if (!$user->can($this->permission)) {
            $response->setStatusCode(403);
            $response->json(['error' => 'Permission denied']);
            return;
        }

        //  Continue safely
        if ($next) {
            $next($request, $response);
        }
    }
}