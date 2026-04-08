<?php

declare(strict_types=1);

namespace App\Controllers\Candidate;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Candidate;
use App\Services\AuthService;
use App\Services\VerificationService;

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
        $candidate = Candidate::findByUserId((int)$this->currentUser->id);

        $response->view('candidate/settings', [
            'title' => 'Settings',
            'user' => $this->currentUser,
            'notificationPrefs' => $notificationPrefs,
            'candidate' => $candidate
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
        $candidate = Candidate::findByUserId((int)$user->id);

        if (isset($data['email']) && $data['email'] !== $user->email) {
            $existingUser = \App\Models\User::where('email', '=', $data['email'])
                ->where('id', '!=', $user->id)
                ->first();
            if ($existingUser) {
                $response->json(['error' => 'Email already in use'], 422);
                return;
            }
            $user->attributes['email'] = $data['email'];
            $user->attributes['is_email_verified'] = 0;
        }

        if (array_key_exists('phone', $data)) {
            $normalizedPhone = AuthService::normalizePhoneNumber((string)$data['phone']);
            $currentPhone = AuthService::normalizePhoneNumber((string)($user->phone ?? ''));
            if ($normalizedPhone !== $currentPhone) {
                $existingPhoneUser = (new AuthService())->findUserByPhone($normalizedPhone);
                if ($normalizedPhone !== '' && $existingPhoneUser && (int)$existingPhoneUser->id !== (int)$user->id) {
                    $response->json(['error' => 'Phone number is already in use'], 422);
                    return;
                }

                $user->attributes['phone'] = $normalizedPhone ?: null;
                $user->attributes['is_phone_verified'] = 0;
                if ($candidate) {
                    $candidate->attributes['mobile'] = $normalizedPhone ?: null;
                    $candidate->save();
                }
            }
        }

        if (array_key_exists('additional_mobile', $data)) {
            $this->storeAdditionalMobile($user, $candidate, (string)$data['additional_mobile']);
        }

        // Update Notification Preferences
        if (isset($data['notification_pref']) && is_array($data['notification_pref'])) {
            $user->setNotificationPreferences($data['notification_pref']);
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
        }

        $user->save();

        $response->json(['success' => true, 'message' => 'Settings updated successfully']);
    }

    public function sendPhoneOtp(Request $request, Response $response): void
    {
        if (!$this->currentUser) {
            $response->json(['error' => 'Unauthorized'], 401);
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
            $candidate = Candidate::findByUserId((int)$this->currentUser->id);
            if ($candidate) {
                $candidate->attributes['mobile'] = $phone;
                $candidate->save();
            }
        }

        $payload = ['success' => true, 'message' => 'OTP sent successfully'];
        if (!empty($result['otp_preview'])) {
            $payload['otp_preview'] = $result['otp_preview'];
        }
        $response->json($payload);
    }

    public function verifyPhoneOtp(Request $request, Response $response): void
    {
        if (!$this->currentUser) {
            $response->json(['error' => 'Unauthorized'], 401);
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
            $candidatePrefs = json_decode((string)($candidate->attributes['preferences_data'] ?? '{}'), true);
            if (!is_array($candidatePrefs)) {
                $candidatePrefs = [];
            }
            $candidatePrefs['contact'] = is_array($candidatePrefs['contact'] ?? null) ? $candidatePrefs['contact'] : [];
            $candidatePrefs['contact']['additional_mobile'] = $normalized ?: null;
            $candidate->attributes['preferences_data'] = json_encode($candidatePrefs);
            $candidate->save();
        }
    }
}
