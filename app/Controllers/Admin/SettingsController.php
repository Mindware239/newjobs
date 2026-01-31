<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class SettingsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $db = Database::getInstance();
        $db->execute(
            "CREATE TABLE IF NOT EXISTS system_settings (
                id TINYINT(1) PRIMARY KEY,
                platform_name VARCHAR(255) NULL,
                maintenance_mode TINYINT(1) NOT NULL DEFAULT 0,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        $row = $db->fetchOne("SELECT * FROM system_settings WHERE id = 1") ?? [];

        $response->view('admin/settings/index', [
            'title' => 'System Settings',
            'settings' => $row,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function update(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $db = Database::getInstance();
        $db->execute(
            "CREATE TABLE IF NOT EXISTS system_settings (
                id TINYINT(1) PRIMARY KEY,
                platform_name VARCHAR(255) NULL,
                maintenance_mode TINYINT(1) NOT NULL DEFAULT 0,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        
        // Ensure platform_name column exists (migration)
        try {
            $db->query("SELECT platform_name FROM system_settings LIMIT 1");
        } catch (\Exception $e) {
            $db->execute("ALTER TABLE system_settings ADD COLUMN platform_name VARCHAR(255) NULL AFTER id");
        }

        $platformName = (string)($request->post('platform_name', 'Job Portal'));
        $maintenanceMode = $request->post('maintenance_mode') ? 1 : 0;

        $db->execute(
            "INSERT INTO system_settings (id, platform_name, maintenance_mode)
             VALUES (1, :platform_name, :maintenance_mode)
             ON DUPLICATE KEY UPDATE platform_name = :platform_name, maintenance_mode = :maintenance_mode",
            [
                'platform_name' => $platformName,
                'maintenance_mode' => $maintenanceMode
            ]
        );

        $this->logAction('update_settings', [
            'platform_name' => $platformName,
            'maintenance_mode' => $maintenanceMode
        ]);

        $response->redirect('/admin/settings');
    }

    private function requireAdmin(Request $request, Response $response): bool
    {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $response->redirect('/admin/login');
            return false;
        }
        return true;
    }

    private function logAction(string $action, array $data = []): void
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "INSERT INTO audit_logs (user_id, action, entity_type, old_value, new_value, ip_address, created_at)
                 VALUES (:user_id, :action, 'settings', :old_value, :new_value, :ip_address, NOW())",
                [
                    'user_id' => $this->currentUser->id,
                    'action' => $action,
                    'old_value' => json_encode([]),
                    'new_value' => json_encode($data),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]
            );
        } catch (\Exception $e) {
            // Silently fail
        }
    }
}

