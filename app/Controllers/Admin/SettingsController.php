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
        $db = Database::getInstance();
        $hideExternal = $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'hide_external_jobs'")['setting_value'] ?? '0';

        $settings = [
            'platform_name' => SystemSetting::get('platform_name', 'Jobsence'),
            'maintenance_mode' => (int)SystemSetting::get('maintenance_mode', 0),
            'notifications_email' => (int)SystemSetting::get('notifications_email', 1),
            'notifications_push' => (int)SystemSetting::get('notifications_push', 1),
            'notifications_in_app' => (int)SystemSetting::get('notifications_in_app', 1),
            'notifications_whatsapp' => (int)SystemSetting::get('notifications_whatsapp', 0),
            'hide_external_jobs' => (int)$hideExternal,
            'profile_reminder_enabled' => (int)SystemSetting::get('profile_reminder_enabled', 1),
            'profile_reminder_frequency_days' => (int)SystemSetting::get('profile_reminder_frequency_days', 3),
            'profile_reminder_max_per_week' => (int)SystemSetting::get('profile_reminder_max_per_week', 2),
            'profile_reminder_min_strength' => (int)SystemSetting::get('profile_reminder_min_strength', 100),
            'usd_inr_rate' => (float)SystemSetting::get('usd_inr_rate', 88),
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
        $hideExternalJobs = $request->post('hide_external_jobs') ? 1 : 0;
        
        $profileReminderEnabled = $request->post('profile_reminder_enabled') ? 1 : 0;
        $profileReminderFreq = (int)$request->post('profile_reminder_frequency_days', 3);
        $profileReminderMax = (int)$request->post('profile_reminder_max_per_week', 2);
        $profileReminderMin = (int)$request->post('profile_reminder_min_strength', 100);

        // Save settings using the Model (Key-Value store)
        SystemSetting::set('platform_name', $platformName, 'general');
        SystemSetting::set('maintenance_mode', (string)$maintenanceMode, 'general');
        
        SystemSetting::set('notifications_email', (string)$notificationsEmail, 'general');
        SystemSetting::set('notifications_push', (string)$notificationsPush, 'general');
        SystemSetting::set('notifications_in_app', (string)$notificationsInApp, 'general');
        SystemSetting::set('notifications_whatsapp', (string)$notificationsWhatsapp, 'general');
        
        SystemSetting::set('profile_reminder_enabled', (string)$profileReminderEnabled, 'general');
        SystemSetting::set('profile_reminder_frequency_days', (string)$profileReminderFreq, 'general');
        SystemSetting::set('profile_reminder_max_per_week', (string)$profileReminderMax, 'general');
        SystemSetting::set('profile_reminder_min_strength', (string)$profileReminderMin, 'general');
        $usdRate = (float)$request->post('usd_inr_rate', 88);
        if ($usdRate >= 40 && $usdRate <= 250) {
            SystemSetting::set('usd_inr_rate', (string)round($usdRate, 2), 'payments');
        }

        // Save hide_external_jobs to the specific settings table as requested
        $db = Database::getInstance();
        $db->query(
            "INSERT INTO settings (setting_key, setting_value) VALUES ('hide_external_jobs', :val) 
             ON DUPLICATE KEY UPDATE setting_value = :val_upd",
            ['val' => (string)$hideExternalJobs, 'val_upd' => (string)$hideExternalJobs]
        );

        $this->logAction('update_settings', [
            'platform_name' => $platformName,
            'maintenance_mode' => $maintenanceMode,
            'notifications_email' => $notificationsEmail,
            'notifications_push' => $notificationsPush,
            'notifications_in_app' => $notificationsInApp,
            'notifications_whatsapp' => $notificationsWhatsapp,
            'hide_external_jobs' => $hideExternalJobs
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

