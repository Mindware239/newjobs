<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\User;

class SalesRoleMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response, callable $next = null): void
    {
        $userId = $_SESSION['user_id'] ?? null;

        // Check login
        if (!$userId) {
            $response->redirect('/login');
            return;
        }

        $user = User::find((int)$userId);

        if (!$user) {
            $response->redirect('/login');
            return;
        }

        // Get path
        $path = $request->getPath();
        $requiredSlug = null;

        if (strpos($path, '/sales-manager') === 0 || strpos($path, '/sales/manager') === 0) {
            $requiredSlug = 'sales_manager';
        } elseif (
            strpos($path, '/sales-executive') === 0 ||
            strpos($path, '/sales-exec') === 0 ||
            strpos($path, '/sales/executive') === 0
        ) {
            $requiredSlug = 'sales_executive';
        }

        // If no role required → continue safely
        if ($requiredSlug === null) {
            if ($next) {
                $next($request, $response);
            }
            return;
        }

        $db = Database::getInstance();

        try {
            // Super Admin Check
            $super = $db->fetchOne(
                "SELECT 1 FROM role_user ru 
                 INNER JOIN roles r ON r.id = ru.role_id 
                 WHERE ru.user_id = :uid AND r.slug = 'super_admin'",
                ['uid' => (int)$user->id]
            );

            //  Exact Role Check
            $hasRole = $db->fetchOne(
                "SELECT 1 FROM role_user ru 
                 INNER JOIN roles r ON r.id = ru.role_id 
                 WHERE ru.user_id = :uid AND r.slug = :slug",
                [
                    'uid' => (int)$user->id,
                    'slug' => $requiredSlug
                ]
            );

            //  Manager can access Executive
            $isManager = false;

            if (!$hasRole && $requiredSlug === 'sales_executive') {
                $isManager = $db->fetchOne(
                    "SELECT 1 FROM role_user ru 
                     INNER JOIN roles r ON r.id = ru.role_id 
                     WHERE ru.user_id = :uid AND r.slug = 'sales_manager'",
                    ['uid' => (int)$user->id]
                );
            }

            //  Access Denied
            if (!$hasRole && !$super && !$isManager) {
                $response->setStatusCode(403);
                $response->view(
                    'errors/403',
                    [
                        'title' => 'Forbidden',
                        'message' => 'You do not have access to this panel.'
                    ],
                    403,
                    'masteradmin/layout'
                );
                return;
            }

        } catch (\Throwable $t) {
            //  Server Error
            $response->setStatusCode(500);
            $response->json(['error' => 'Internal Server Error']);
            return;
        }

        // Continue middleware chain safely
        if ($next) {
            $next($request, $response);
        }
    }
}