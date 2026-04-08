<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Job;
use App\Models\EmployerSetting;
use App\Models\Application;
use App\Services\AuthService;
use App\Services\VerificationService;

class SettingsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->view('employer/profile-missing', [
                'title' => 'Complete Your Profile',
                'message' => 'Your employer profile was not found.',
                'user' => $this->currentUser
            ], 200, 'employer/layout');
            return;
        }

        // Get employer settings
        $settings = $employer->settings();
        if (!$settings) {
            // Create default settings
            $settings = new EmployerSetting();
            $settings->fill([
                'employer_id' => $employer->id,
                'billing_plan' => 'free',
                'credits' => 0,
                'timezone' => 'Asia/Kolkata',
                'notification_pref' => json_encode([
                    'email_new_application' => true,
                    'email_shortlisted' => true,
                    'email_interview_scheduled' => true,
                    'email_messages' => true,
                    'push_notifications' => true
                ])
            ]);
            $settings->save();
            $settings = $employer->settings();
        }

        // Parse notification preferences
        $notificationPrefs = [];
        if ($settings && !empty($settings->attributes['notification_pref'])) { // Read-only access is likely fine via __get if implemented, or public
            // Assuming attributes is accessible or via getter. 
            // If attributes is protected, we should use getAttribute or similar.
            // But let's check how Model works. If attributes is protected, $settings->attributes will fail unless __get returns it.
            // Let's assume toArray() or similar is better, or direct property access if __get maps to attributes.
            // Safe bet: use toArray() or direct property access if magic getters exist.
            $rawPref = $settings->notification_pref ?? ($settings->attributes['notification_pref'] ?? null);
            $notificationPrefs = is_string($rawPref) 
                ? json_decode($rawPref, true) 
                : $rawPref;
            if (!is_array($notificationPrefs)) {
                $notificationPrefs = [];
            }
        }

        $defaultPrefs = [
            'job_application' => ['email' => true, 'sms' => false, 'push' => true, 'whatsapp' => false],
            'interview_schedule' => ['email' => true, 'sms' => false, 'push' => true, 'whatsapp' => false],
            'new_message' => ['email' => true, 'sms' => false, 'push' => true, 'whatsapp' => false],
            'candidate_shortlisted' => ['email' => true, 'sms' => false, 'push' => false, 'whatsapp' => false],
            'marketing' => ['email' => false, 'sms' => false, 'push' => false, 'whatsapp' => false]
        ];

        $userPrefs = $this->currentUser->getNotificationPreferences();
        if (!is_array($userPrefs)) {
            $userPrefs = [];
        }

        $legacy = $notificationPrefs;
        $convertedLegacy = [];
        if (is_array($legacy) && (isset($legacy['email_new_application']) || isset($legacy['email_interview_scheduled']) || isset($legacy['email_messages']) || isset($legacy['push_notifications']) || isset($legacy['email_shortlisted']))) {
            $convertedLegacy = $defaultPrefs;
            $convertedLegacy['job_application']['email'] = (bool)($legacy['email_new_application'] ?? true);
            $convertedLegacy['job_application']['push'] = (bool)($legacy['push_notifications'] ?? true);
            $convertedLegacy['interview_schedule']['email'] = (bool)($legacy['email_interview_scheduled'] ?? true);
            $convertedLegacy['new_message']['email'] = (bool)($legacy['email_messages'] ?? true);
            $convertedLegacy['candidate_shortlisted']['email'] = (bool)($legacy['email_shortlisted'] ?? true);
        }

        $notificationPrefs = array_replace_recursive($defaultPrefs, !empty($userPrefs) ? $userPrefs : $convertedLegacy);

        // Get counts for sidebar
        $activeJobsCount = Job::where('employer_id', '=', $employer->id)
            ->where('status', '=', 'published')->count();
        $jobIds = Job::where('employer_id', '=', $employer->id)->pluck('id');
        $totalApplications = !empty($jobIds) 
            ? Application::whereIn('job_id', $jobIds)->count()
            : 0;

        $response->view('employer/settings', [
            'title' => 'Settings',
            'employer' => $employer,
            'user' => $this->currentUser,
            'settings' => $settings,
            'notificationPrefs' => $notificationPrefs,
            'jobCount' => $activeJobsCount,
            'applicationCount' => $totalApplications
        ], 200, 'employer/layout');
    }

    public function updateAccount(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        $data = $request->getJsonBody();
        $user = $this->currentUser;

        // Update email
        if (isset($data['email']) && $data['email'] !== $user->email) {
            // Check if email already exists
            $existingUser = \App\Models\User::where('email', '=', $data['email'])
                ->where('id', '!=', $user->id)
                ->first();
            if ($existingUser) {
                $response->json(['error' => 'Email already in use'], 422);
                return;
            }
            $user->attributes['email'] = $data['email'];
            $user->attributes['is_email_verified'] = 0; // Require re-verification
            $user->save();
        }

        // Update phone
        if (isset($data['phone']) && $data['phone'] !== ($user->phone ?? '')) {
            $normalizedPhone = AuthService::normalizePhoneNumber((string)$data['phone']);
            $existingPhoneUser = (new AuthService())->findUserByPhone($normalizedPhone);
            if ($normalizedPhone !== '' && $existingPhoneUser && (int)$existingPhoneUser->id !== (int)$user->id) {
                $response->json(['error' => 'Phone number is already in use'], 422);
                return;
            }
            $user->attributes['phone'] = $normalizedPhone ?: null;
            $user->attributes['is_phone_verified'] = 0;
            $user->save();
        }

        if (array_key_exists('additional_mobile', $data)) {
            $this->storeAdditionalMobile($user, $employer, (string)$data['additional_mobile']);
            $user->save();
        }

        $response->json(['success' => true, 'message' => 'Account updated successfully']);
    }

    public function sendPhoneOtp(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $data = $request->getJsonBody();
        $phone = AuthService::normalizePhoneNumber((string)($data['phone'] ?? ($this->currentUser->phone ?? '')));
        if ($phone === '') {
            $response->json(['error' => 'Valid phone number is required'], 422);
            return;
        }

        $result = VerificationService::sendPhoneOTP((int)$this->currentUser->id, $phone);
        if (empty($result['success'])) {
            $response->json(['error' => $result['error'] ?? 'Failed to send OTP'], 500);
            return;
        }

        if (($this->currentUser->phone ?? '') !== $phone) {
            $this->currentUser->attributes['phone'] = $phone;
            $this->currentUser->attributes['is_phone_verified'] = 0;
            $this->currentUser->save();
        }

        $payload = ['success' => true, 'message' => 'OTP sent successfully'];
        if (!empty($result['otp_preview'])) {
            $payload['otp_preview'] = $result['otp_preview'];
        }
        $response->json($payload);
    }

    public function verifyPhoneOtp(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $data = $request->getJsonBody();
        $otp = trim((string)($data['otp'] ?? ''));
        if ($otp === '') {
            $response->json(['error' => 'OTP is required'], 422);
            return;
        }

        if (!VerificationService::verifyPhone((int)$this->currentUser->id, $otp)) {
            $response->json(['error' => 'Invalid OTP'], 400);
            return;
        }

        $response->json(['success' => true, 'message' => 'Phone verified successfully']);
    }

    public function updatePassword(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $data = $request->getJsonBody();
        $user = $this->currentUser;

        // Verify current password
        if (empty($data['current_password']) || !$user->verifyPassword($data['current_password'])) {
            $response->json(['error' => 'Current password is incorrect'], 422);
            return;
        }

        // Validate new password
        if (empty($data['new_password']) || strlen($data['new_password']) < 8) {
            $response->json(['error' => 'New password must be at least 8 characters'], 422);
            return;
        }

        if ($data['new_password'] !== ($data['confirm_password'] ?? '')) {
            $response->json(['error' => 'New passwords do not match'], 422);
            return;
        }

        // Update password
        $user->setPassword($data['new_password']);
        $user->save();

        $response->json(['success' => true, 'message' => 'Password updated successfully']);
    }

    public function updatePreferences(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        $data = $request->getJsonBody();
        $settings = $employer->settings();
        
        if (!$settings) {
            $settings = new EmployerSetting();
            $settings->attributes['employer_id'] = $employer->id;
        }

        // Update timezone
        if (isset($data['timezone'])) {
            $settings->attributes['timezone'] = $data['timezone'];
            $settings->save();
        }

        // Update notification preferences
        if (isset($data['notification_pref']) && is_array($data['notification_pref'])) {
            // Save to User model (New System)
            $user = $this->currentUser;
            $user->setNotificationPreferences($data['notification_pref']);
            $user->save();

            // Save to legacy settings for backward compatibility (optional, but safe)
            $legacyMap = [
                'email_new_application' => $data['notification_pref']['job_application']['email'] ?? true,
                'email_shortlisted' => true, // Default
                'email_interview_scheduled' => $data['notification_pref']['interview_schedule']['email'] ?? true,
                'email_messages' => $data['notification_pref']['new_message']['email'] ?? true,
                'push_notifications' => $data['notification_pref']['job_application']['push'] ?? true // Rough approximation
            ];
            $settings->attributes['notification_pref'] = json_encode($legacyMap);
            $settings->save();
        }

        $response->json(['success' => true, 'message' => 'Preferences updated successfully']);
    }

    public function updateCompany(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        $data = $request->getJsonBody();
        $updateData = [];

        if (isset($data['company_name'])) {
            $updateData['company_name'] = $data['company_name'];
            $updateData['company_slug'] = $employer->generateSlug($data['company_name']);
        }

        if (isset($data['website'])) {
            $updateData['website'] = $data['website'] ?: null;
        }

        if (isset($data['description'])) {
            $updateData['description'] = $data['description'] ?: null;
        }

        if (isset($data['industry'])) {
            $updateData['industry'] = $data['industry'] ?: null;
        }

        if (isset($data['company_size'])) {
            $updateData['size'] = $data['company_size'];
        }

        if (!empty($updateData)) {
            foreach ($updateData as $key => $value) {
                $employer->attributes[$key] = $value;
            }
            $employer->save();
        }

        $response->json(['success' => true, 'message' => 'Company information updated successfully']);
    }

    private function storeAdditionalMobile(\App\Models\User $user, \App\Models\Employer $employer, string $phone): void
    {
        $normalized = AuthService::normalizePhoneNumber($phone);
        $prefs = $user->getNotificationPreferences();
        if (!is_array($prefs)) {
            $prefs = [];
        }
        $prefs['contact'] = is_array($prefs['contact'] ?? null) ? $prefs['contact'] : [];
        $prefs['contact']['additional_mobile'] = $normalized ?: null;
        $user->setNotificationPreferences($prefs);

        $settings = $employer->settings();
        if (!$settings) {
            $settings = new EmployerSetting();
            $settings->attributes['employer_id'] = $employer->id;
        }
        $legacyPrefs = json_decode((string)($settings->attributes['notification_pref'] ?? '{}'), true);
        if (!is_array($legacyPrefs)) {
            $legacyPrefs = [];
        }
        $legacyPrefs['contact'] = is_array($legacyPrefs['contact'] ?? null) ? $legacyPrefs['contact'] : [];
        $legacyPrefs['contact']['additional_mobile'] = $normalized ?: null;
        $settings->attributes['notification_pref'] = json_encode($legacyPrefs);
        $settings->save();
    }
}

