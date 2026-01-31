<?php

declare(strict_types=1);

namespace App\Controllers\Candidate;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;

class SettingsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        // Ensure user is logged in
        if (!$this->currentUser) {
            $response->redirect('/login');
            return;
        }

        // Get notification preferences
        $notificationPrefs = $this->currentUser->getNotificationPreferences();

        // Default preferences structure for Candidates
        $defaultPrefs = [
            'job_matches' => ['email' => true, 'sms' => true, 'push' => true, 'whatsapp' => true],
            'application_status' => ['email' => true, 'sms' => true, 'push' => true, 'whatsapp' => true],
            'interview_invites' => ['email' => true, 'sms' => true, 'push' => true, 'whatsapp' => true],
            'messages' => ['email' => true, 'sms' => false, 'push' => true, 'whatsapp' => false],
            'marketing' => ['email' => true, 'sms' => false, 'push' => false, 'whatsapp' => false]
        ];

        $notificationPrefs = array_replace_recursive($defaultPrefs, $notificationPrefs ?? []);

        $response->view('candidate/settings', [
            'title' => 'Settings',
            'user' => $this->currentUser,
            'notificationPrefs' => $notificationPrefs
        ], 200, 'candidate/layout');
    }

    public function update(Request $request, Response $response): void
    {
        if (!$this->currentUser) {
            $response->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $data = $request->getJsonBody();
        $user = $this->currentUser;

        // Update Notification Preferences
        if (isset($data['notification_pref']) && is_array($data['notification_pref'])) {
            $user->setNotificationPreferences($data['notification_pref']);
            $user->save();
        }

        // Update Password (if provided)
        if (!empty($data['new_password'])) {
            if (!$user->verifyPassword($data['current_password'] ?? '')) {
                $response->json(['error' => 'Current password is incorrect'], 422);
                return;
            }
            if (strlen($data['new_password']) < 8) {
                $response->json(['error' => 'New password must be at least 8 characters'], 422);
                return;
            }
            if ($data['new_password'] !== ($data['confirm_password'] ?? '')) {
                $response->json(['error' => 'Passwords do not match'], 422);
                return;
            }
            $user->setPassword($data['new_password']);
            $user->save();
        }

        $response->json(['success' => true, 'message' => 'Settings updated successfully']);
    }
}
