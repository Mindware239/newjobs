<?php

declare(strict_types=1);

namespace App\Controllers\Api\Employer;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Employer;
use App\Models\EmployerSetting;
use App\Models\User;
use App\Services\AuthService;
use App\Services\VerificationService;

class SettingsController extends ApiController
{
    /**
     * GET /api/v1/employer/settings
     * Get employer settings and account information
     */
    public function index(Request $request, Response $response): void
    {
        $employer = $this->employer($request, $response);
        if (!$employer) {
            return;
        }

        $user = $this->user($request);
        $settings = $employer->settings();

        if (!$settings) {
            // Create default settings if not exists
            $settings = new EmployerSetting();
            $settings->fill([
                'employer_id' => $employer->id,
                'billing_plan' => 'free',
                'credits' => 0,
                'timezone' => 'Asia/Kolkata',
                'notification_pref' => json_encode([
                    'job_application' => ['email' => true, 'sms' => false, 'push' => true, 'whatsapp' => false],
                    'interview_schedule' => ['email' => true, 'sms' => false, 'push' => true, 'whatsapp' => false],
                    'new_message' => ['email' => true, 'sms' => false, 'push' => true, 'whatsapp' => false],
                    'candidate_shortlisted' => ['email' => true, 'sms' => false, 'push' => false, 'whatsapp' => false],
                    'marketing' => ['email' => false, 'sms' => false, 'push' => false, 'whatsapp' => false]
                ])
            ]);
            $settings->save();
        }

        $notificationPrefs = $user->getNotificationPreferences();
        if (empty($notificationPrefs)) {
            $rawPref = $settings->notification_pref;
            $notificationPrefs = is_string($rawPref) ? json_decode($rawPref, true) : $rawPref;
        }

        $this->success($response, [
            'account' => [
                'email' => $user->email,
                'phone' => $user->phone,
                'is_email_verified' => (bool)$user->is_email_verified,
                'is_phone_verified' => (bool)$user->is_phone_verified,
                'additional_mobile' => $notificationPrefs['contact']['additional_mobile'] ?? null
            ],
            'preferences' => [
                'timezone' => $settings->timezone ?? 'Asia/Kolkata',
                'notification_pref' => $notificationPrefs
            ],
            'company' => [
                'name' => $employer->company_name,
                'website' => $employer->website,
                'industry' => $employer->industry,
                'size' => $employer->size,
                'description' => $employer->description
            ]
        ]);
    }

    /**
     * PUT /api/v1/employer/settings/account
     */
    public function updateAccount(Request $request, Response $response): void
    {
        $user = $this->user($request);
        $employer = $this->employer($request, $response);
        if (!$employer) {
            return;
        }

        $data = $request->getJsonBody();

        // Update email
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $existingUser = User::where('email', '=', $data['email'])->where('id', '!=', $user->id)->first();
            if ($existingUser) {
                $this->error($response, 'Email already in use', 422);
                return;
            }
            $user->email = $data['email'];
            $user->is_email_verified = 0;
        }

        // Update phone
        if (isset($data['phone']) && $data['phone'] !== $user->phone) {
            $normalizedPhone = AuthService::normalizePhoneNumber((string)$data['phone']);
            $existingPhoneUser = (new AuthService())->findUserByPhone($normalizedPhone);
            if ($normalizedPhone !== '' && $existingPhoneUser && (int)$existingPhoneUser->id !== (int)$user->id) {
                $this->error($response, 'Phone number is already in use', 422);
                return;
            }
            $user->phone = $normalizedPhone ?: null;
            $user->is_phone_verified = 0;
        }

        if (array_key_exists('additional_mobile', $data)) {
            $this->storeAdditionalMobile($user, $employer, (string)$data['additional_mobile']);
        }

        $user->save();

        $this->success($response, [], 'Account updated successfully');
    }

    /**
     * POST /api/v1/employer/settings/password
     */
    public function updatePassword(Request $request, Response $response): void
    {
        $user = $this->user($request);
        $data = $request->getJsonBody();

        $errors = $this->validate($data, [
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        if (!$user->verifyPassword($data['current_password'])) {
            $this->error($response, 'Current password is incorrect', 422);
            return;
        }

        if ($data['new_password'] !== $data['confirm_password']) {
            $this->error($response, 'New passwords do not match', 422);
            return;
        }

        $user->setPassword($data['new_password']);
        $user->save();

        $this->success($response, [], 'Password updated successfully');
    }

    /**
     * PUT /api/v1/employer/settings/preferences
     */
    public function updatePreferences(Request $request, Response $response): void
    {
        $employer = $this->employer($request, $response);
        if (!$employer) {
            return;
        }

        $user = $this->user($request);
        $data = $request->getJsonBody();
        $settings = $employer->settings();

        if (!$settings) {
            $settings = new EmployerSetting();
            $settings->employer_id = $employer->id;
        }

        if (isset($data['timezone'])) {
            $settings->timezone = $data['timezone'];
            $settings->save();
        }

        if (isset($data['notification_pref']) && is_array($data['notification_pref'])) {
            $user->setNotificationPreferences($data['notification_pref']);
            $user->save();

            // Sync to legacy settings
            $settings->notification_pref = json_encode($data['notification_pref']);
            $settings->save();
        }

        $this->success($response, [], 'Preferences updated successfully');
    }

    /**
     * POST /api/v1/employer/settings/account/send-otp
     */
    public function sendPhoneOtp(Request $request, Response $response): void
    {
        $user = $this->user($request);
        $data = $request->getJsonBody();
        $phone = AuthService::normalizePhoneNumber((string)($data['phone'] ?? ($user->phone ?? '')));

        if ($phone === '') {
            $this->error($response, 'Valid phone number is required', 422);
            return;
        }

        $result = VerificationService::sendPhoneOTP((int)$user->id, $phone);
        if (empty($result['success'])) {
            $this->error($response, $result['error'] ?? 'Failed to send OTP', 500);
            return;
        }

        if ($user->phone !== $phone) {
            $user->phone = $phone;
            $user->is_phone_verified = 0;
            $user->save();
        }

        $this->success($response, [
            'otp_preview' => $result['otp_preview'] ?? null
        ], 'OTP sent successfully');
    }

    /**
     * POST /api/v1/employer/settings/account/verify-otp
     */
    public function verifyPhoneOtp(Request $request, Response $response): void
    {
        $user = $this->user($request);
        $data = $request->getJsonBody();
        $otp = trim((string)($data['otp'] ?? ''));

        if ($otp === '') {
            $this->error($response, 'OTP is required', 422);
            return;
        }

        if (!VerificationService::verifyPhone((int)$user->id, $otp)) {
            $this->error($response, 'Invalid OTP', 400);
            return;
        }

        $this->success($response, [], 'Phone verified successfully');
    }

    /**
     * PUT /api/v1/employer/settings/company
     */
    public function updateCompany(Request $request, Response $response): void
    {
        $employer = $this->employer($request, $response);
        if (!$employer) {
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

        if (isset($data['company_type'])) {
            $updateData['company_type'] = $data['company_type'] ?: null;
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

        $this->success($response, [], 'Company information updated successfully');
    }

    private function employer(Request $request, Response $response): ?Employer
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Employer authentication required', 403);
            return null;
        }

        $employer = $user->employer();
        if (!$employer) {
            $this->error($response, 'Employer profile incomplete', 404);
            return null;
        }

        return $employer;
    }

    private function storeAdditionalMobile(User $user, Employer $employer, string $phone): void
    {
        $normalized = AuthService::normalizePhoneNumber($phone);
        $prefs = $user->getNotificationPreferences();
        if (!is_array($prefs)) {
            $prefs = [];
        }
        $prefs['contact'] = $prefs['contact'] ?? [];
        $prefs['contact']['additional_mobile'] = $normalized ?: null;
        $user->setNotificationPreferences($prefs);

        $settings = $employer->settings();
        if ($settings) {
            $legacyPrefs = is_string($settings->notification_pref) ? json_decode($settings->notification_pref, true) : $settings->notification_pref;
            if (!is_array($legacyPrefs)) $legacyPrefs = [];
            $legacyPrefs['contact'] = $legacyPrefs['contact'] ?? [];
            $legacyPrefs['contact']['additional_mobile'] = $normalized ?: null;
            $settings->notification_pref = json_encode($legacyPrefs);
            $settings->save();
        }
    }
}
