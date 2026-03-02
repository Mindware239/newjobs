<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\SystemSetting;

class SettingsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        // Retrieve settings using the Model (Key-Value store)
        $settings = [
            'platform_name' => SystemSetting::get('platform_name', 'Mindware Infotech'),
            'maintenance_mode' => (int)SystemSetting::get('maintenance_mode', 0),
            'notifications_email' => (int)SystemSetting::get('notifications_email', 1),
            'notifications_push' => (int)SystemSetting::get('notifications_push', 1),
            'notifications_in_app' => (int)SystemSetting::get('notifications_in_app', 1),
            'notifications_whatsapp' => (int)SystemSetting::get('notifications_whatsapp', 0)
        ];

        $response->view('admin/settings/index', [
            'title' => 'System Settings',
            'settings' => $settings,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function update(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $platformName = (string)($request->post('platform_name', 'Job Portal'));
        $maintenanceMode = $request->post('maintenance_mode') ? 1 : 0;
        
        $notificationsEmail = $request->post('notifications_email') ? 1 : 0;
        $notificationsPush = $request->post('notifications_push') ? 1 : 0;
        $notificationsInApp = $request->post('notifications_in_app') ? 1 : 0;
        $notificationsWhatsapp = $request->post('notifications_whatsapp') ? 1 : 0;

        // Save settings using the Model (Key-Value store)
        SystemSetting::set('platform_name', $platformName, 'general');
        SystemSetting::set('maintenance_mode', (string)$maintenanceMode, 'general');
        
        SystemSetting::set('notifications_email', (string)$notificationsEmail, 'general');
        SystemSetting::set('notifications_push', (string)$notificationsPush, 'general');
        SystemSetting::set('notifications_in_app', (string)$notificationsInApp, 'general');
        SystemSetting::set('notifications_whatsapp', (string)$notificationsWhatsapp, 'general');

        $this->logAction('update_settings', [
            'platform_name' => $platformName,
            'maintenance_mode' => $maintenanceMode,
            'notifications_email' => $notificationsEmail,
            'notifications_push' => $notificationsPush,
            'notifications_in_app' => $notificationsInApp,
            'notifications_whatsapp' => $notificationsWhatsapp
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

