<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;

class AdminMiddleware implements MiddlewareInterface
{
    private array $permissions = [];

    public function __construct(array $permissions = [])
    {
        $this->permissions = $permissions;
    }

    public function handle(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            $acceptHeader = $request->header('Accept') ?? '';
            if (strpos($acceptHeader, 'application/json') === false && $request->getMethod() === 'GET') {
                $response->redirect('/admin/login?redirect=' . urlencode($request->getPath()));
                return;
            }
            $response->setStatusCode(401);
            $response->json(['error' => 'Unauthorized']);
            return;
        }

        $user = User::find((int)$userId);
        if (!$user) {
            $acceptHeader = $request->header('Accept') ?? '';
            if (strpos($acceptHeader, 'application/json') === false && $request->getMethod() === 'GET') {
                $response->redirect('/admin/login?error=access_denied');
                return;
            }
            $response->setStatusCode(403);
            $response->json(['error' => 'Forbidden - Admin access required']);
            return;
        }
        $hasRbacAdmin = false;
        try {
            $db = \App\Core\Database::getInstance();
            $row = $db->fetchOne(
                "SELECT 1 FROM role_user ru INNER JOIN roles r ON r.id = ru.role_id WHERE ru.user_id = :uid AND r.slug IN ('admin','super_admin')",
                ['uid' => (int)$user->id]
            );
            $hasRbacAdmin = (bool)$row;
        } catch (\Throwable $t) {}
        if (!$user->isAdmin() || !$hasRbacAdmin) {
            $acceptHeader = $request->header('Accept') ?? '';
            if (strpos($acceptHeader, 'application/json') === false && $request->getMethod() === 'GET') {
                $response->redirect('/admin/login?error=access_denied');
                return;
            }
            $response->setStatusCode(403);
            $response->json(['error' => 'Forbidden - Admin access required']);
            return;
        }

        if ($user->status !== 'active') {
            $response->setStatusCode(403);
            $response->json(['error' => 'Account is not active']);
            return;
        }

        // Check permissions if specified
        if (!empty($this->permissions)) {
            $userPermissions = $this->getUserPermissions($user);
            $hasPermission = false;
            
            foreach ($this->permissions as $permission) {
                if (in_array($permission, $userPermissions) || $user->role === 'super_admin') {
                    $hasPermission = true;
                    break;
                }
            }

            if (!$hasPermission) {
                $response->setStatusCode(403);
                $response->json(['error' => 'Insufficient permissions']);
                return;
            }
        }

        // Log admin activity
        $this->logAdminActivity($user, $request);
    }

    private function getUserPermissions(User $user): array
    {
        // For now, return all permissions for admin/super_admin
        // Later, implement proper RBAC with database
        if ($user->role === 'super_admin') {
            return ['*']; // All permissions
        }

        // Get permissions from database (will implement later)
        $db = \App\Core\Database::getInstance();
        try {
            $permissions = $db->fetchAll(
                "SELECT permission FROM admin_role_permissions arp
                 INNER JOIN admin_user_roles aur ON aur.role_id = arp.role_id
                 WHERE aur.user_id = :user_id",
                ['user_id' => $user->id]
            );
            return array_column($permissions, 'permission');
        } catch (\Exception $e) {
            // Table doesn't exist yet, return empty array
            return [];
        }
    }

    private function logAdminActivity(User $user, Request $request): void
    {
        try {
            $db = \App\Core\Database::getInstance();
            $db->query(
                "INSERT INTO activity_logs (user_id, action, ip_address, user_agent, created_at)
                 VALUES (:user_id, :action, :ip_address, :user_agent, NOW())",
                [
                    'user_id' => $user->id,
                    'action' => 'admin_access:' . $request->getPath(),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]
            );
        } catch (\Exception $e) {
            // Silently fail if table doesn't exist
        }
    }
}

