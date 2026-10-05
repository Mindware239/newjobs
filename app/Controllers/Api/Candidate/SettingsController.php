<?php

declare(strict_types=1);

namespace App\Controllers\Api\Candidate;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Candidate;
use App\Services\AuthService;
use App\Services\VerificationService;

class SettingsController extends ApiController
{
    /**
     * GET /api/v1/candidate/settings
     * Get candidate settings and notification preferences
     */
    public function index(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $notificationPrefs = $user->getNotificationPreferences();
        
        // Default preferences structure for Candidates
        $defaultPrefs = [
            'job_matches' => ['email' => true, 'sms' => true, 'push' => true, 'whatsapp' => true],
            'application_status' => ['email' => true, 'sms' => true, 'push' => true, 'whatsapp' => true],
            'interview_invites' => ['email' => true, 'sms' => true, 'push' => true, 'whatsapp' => true],
            'messages' => ['email' => true, 'sms' => false, 'push' => true, 'whatsapp' => false],
            'marketing' => ['email' => true, 'sms' => false, 'push' => false, 'whatsapp' => false]
        ];

        $notificationPrefs = array_replace_recursive($defaultPrefs, $notificationPrefs ?? []);
        $candidate = Candidate::findByUserId((int)$user->id);

        $this->success($response, [
            'email' => $user->email,
            'phone' => $user->phone,
            'is_email_verified' => (bool)$user->is_email_verified,
            'is_phone_verified' => (bool)$user->is_phone_verified,
            'notification_preferences' => $notificationPrefs,
            'additional_mobile' => $notificationPrefs['contact']['additional_mobile'] ?? null,
            'account_status' => $user->status,
            'created_at' => $user->created_at
        ]);
    }

    /**
     * PUT /api/v1/candidate/settings
     * Update candidate account settings and notification preferences
     */
    public function update(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $data = $request->getJsonBody();
        $candidate = Candidate::findByUserId((int)$user->id);

        // Update Email
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $existingUser = \App\Models\User::where('email', '=', $data['email'])
                ->where('id', '!=', $user->id)
                ->first();
            if ($existingUser) {
                $this->error($response, 'Email already in use', 422);
                return;
            }
            $user->email = $data['email'];
            $user->is_email_verified = 0;
        }

        // Update Phone
        if (array_key_exists('phone', $data)) {
            $normalizedPhone = AuthService::normalizePhoneNumber((string)$data['phone']);
            $currentPhone = AuthService::normalizePhoneNumber((string)($user->phone ?? ''));
            
            if ($normalizedPhone !== $currentPhone) {
                if ($normalizedPhone !== '') {
                    $existingPhoneUser = (new AuthService())->findUserByPhone($normalizedPhone);
                    if ($existingPhoneUser && (int)$existingPhoneUser->id !== (int)$user->id) {
                        $this->error($response, 'Phone number is already in use', 422);
                        return;
                    }
                }

                $user->phone = $normalizedPhone ?: null;
                $user->is_phone_verified = 0;
                if ($candidate) {
                    $candidate->mobile = $normalizedPhone ?: null;
                    $candidate->save();
                }
            }
        }

        // Update Notification Preferences
        if (isset($data['notification_preferences']) && is_array($data['notification_preferences'])) {
            $user->setNotificationPreferences($data['notification_preferences']);
        }

        // Update Additional Mobile
        if (array_key_exists('additional_mobile', $data)) {
            $this->storeAdditionalMobile($user, $candidate, (string)$data['additional_mobile']);
        }

        $user->save();

        $this->success($response, [], 'Settings updated successfully');
    }

    /**
     * POST /api/v1/candidate/settings/password
     * Change password
     */
    public function changePassword(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $data = $request->getJsonBody();
        
        $errors = $this->validate($data, [
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required|same:new_password'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        if (!$user->verifyPassword($data['current_password'])) {
            $this->error($response, 'Current password is incorrect', 422);
            return;
        }

        $user->setPassword($data['new_password']);
        $user->save();

        $this->success($response, [], 'Password changed successfully');
    }

    /**
     * POST /api/v1/candidate/settings/send-otp
     * Send phone OTP for verification
     */
    public function sendPhoneOtp(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

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

        // Update phone if different
        if (($user->phone ?? '') !== $phone) {
            $user->phone = $phone;
            $user->is_phone_verified = 0;
            $user->save();
            
            $candidate = Candidate::findByUserId((int)$user->id);
            if ($candidate) {
                $candidate->mobile = $phone;
                $candidate->save();
            }
        }

        $payload = ['message' => 'OTP sent successfully'];
        if (!empty($result['otp_preview'])) {
            $payload['otp_preview'] = $result['otp_preview'];
        }
        
        $this->success($response, $payload);
    }

    /**
     * POST /api/v1/candidate/settings/verify-otp
     * Verify phone OTP
     */
    public function verifyPhoneOtp(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

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
     * DELETE /api/v1/candidate/settings/account
     * Request account deletion
     */
    public function deleteAccount(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        // In a real app, you might just mark it as 'to_be_deleted' or 'inactive'
        $user->status = 'inactive';
        $user->save();

        $this->success($response, [], 'Account deactivated. It will be permanently deleted after 30 days.');
    }

    private function storeAdditionalMobile(\App\Models\User $user, ?Candidate $candidate, string $phone): void
    {
        $normalized = AuthService::normalizePhoneNumber($phone);
        $prefs = $user->getNotificationPreferences();
        if (!is_array($prefs)) {
            $prefs = [];
        }
        $prefs['contact'] = is_array($prefs['contact'] ?? null) ? $prefs['contact'] : [];
        $prefs['contact']['additional_mobile'] = $normalized ?: null;
        $user->setNotificationPreferences($prefs);

        if ($candidate) {
            $candidatePrefs = json_decode((string)($candidate->preferences_data ?? '{}'), true);
            if (!is_array($candidatePrefs)) {
                $candidatePrefs = [];
            }
            $candidatePrefs['contact'] = is_array($candidatePrefs['contact'] ?? null) ? $candidatePrefs['contact'] : [];
            $candidatePrefs['contact']['additional_mobile'] = $normalized ?: null;
            $candidate->preferences_data = json_encode($candidatePrefs);
            $candidate->save();
        }
    }
}
