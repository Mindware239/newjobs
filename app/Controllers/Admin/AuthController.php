<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Core\Database;
use App\Services\MailService;

class AuthController extends BaseController
{
    public function showLogin(Request $request, Response $response): void
    {
        if ($this->currentUser && $this->currentUser->isAdmin()) {
            $response->redirect('/admin/dashboard');
            return;
        }

        $error = $request->get('error');
        $redirect = $request->get('redirect', '/admin/dashboard');

        // Clear any old captcha
        unset($_SESSION['admin_captcha']);

        $response->view('admin/auth/login', [
            'title' => 'Admin Login',
            'error' => $error,
            'redirect' => $redirect
        ]);
    }

    public function login(Request $request, Response $response): void
    {
        $email = $request->post('email') ?? '';
        $password = $request->post('password') ?? '';
        $captchaCode = strtoupper(trim($request->post('captcha_code') ?? ''));
        $twoFactorCode = $request->post('two_factor_code') ?? '';

        if (empty($email) || empty($password)) {
            $response->view('admin/auth/login', [
                'title' => 'Admin Login',
                'error' => 'Email and password are required'
            ]);
            return;
        }

        // Verify CAPTCHA
        $sessionCaptcha = strtoupper($_SESSION['admin_captcha'] ?? '');
        if (empty($sessionCaptcha) || $captchaCode !== $sessionCaptcha) {
            // Clear captcha on failure
            unset($_SESSION['admin_captcha']);
            $response->view('admin/auth/login', [
                'title' => 'Admin Login',
                'error' => 'Invalid CAPTCHA code. Please try again.',
                'redirect' => $request->post('redirect', '/admin/dashboard')
            ]);
            return;
        }

        // Clear captcha after successful verification
        unset($_SESSION['admin_captcha']);

        $user = User::where('email', '=', $email)->first();

        if (!$user || !$user->verifyPassword($password)) {
            $this->logFailedLogin($email);
            $response->view('admin/auth/login', [
                'title' => 'Admin Login',
                'error' => 'Invalid credentials'
            ]);
            return;
        }

        if (!$user->isAdmin()) {
            $response->view('admin/auth/login', [
                'title' => 'Admin Login',
                'error' => 'Access denied. Admin privileges required.'
            ]);
            return;
        }

        if ($user->status !== 'active') {
            $response->view('admin/auth/login', [
                'title' => 'Admin Login',
                'error' => 'Your account is not active. Please contact support.'
            ]);
            return;
        }

        // At this point email & password are correct and user is admin and active.
        // Generate OTP for email-based 2FA.
        $otp = str_pad((string)rand(100000, 999999), 6, '0', STR_PAD_LEFT);

        $_SESSION['admin_email_otp'] = $otp;
        $_SESSION['admin_email_otp_user_id'] = $user->id;
        $_SESSION['admin_email_otp_expires'] = time() + 600; // 10 minutes
        $_SESSION['admin_email_otp_redirect'] = $request->post('redirect', '/admin/dashboard');

        // Send OTP email
        $emailSent = MailService::sendAdminOtp($user->email, $otp);
        if (!$emailSent) {
            error_log("Admin 2FA OTP email failed to send to {$user->email}");
        }

        // Show OTP verification page
        $response->view('admin/auth/two-factor', [
            'title' => 'Two-Factor Authentication',
            'email' => $user->email,
            'error' => $emailSent ? null : 'Could not send OTP email. Please check mail configuration.'
        ]);
    }

    public function verifyOtp(Request $request, Response $response): void
    {
        $otpInput = trim($request->post('otp') ?? '');
        $storedOtp = $_SESSION['admin_email_otp'] ?? null;
        $userId = $_SESSION['admin_email_otp_user_id'] ?? null;
        $expires = $_SESSION['admin_email_otp_expires'] ?? 0;
        $redirect = $_SESSION['admin_email_otp_redirect'] ?? '/admin/dashboard';

        if (!$storedOtp || !$userId || time() > (int)$expires) {
            // OTP expired or missing
            $response->view('admin/auth/login', [
                'title' => 'Admin Login',
                'error' => 'OTP expired or not found. Please sign in again.'
            ]);
            return;
        }

        $user = User::find((int)$userId);
        if (!$user || !$user->isAdmin()) {
            $response->view('admin/auth/login', [
                'title' => 'Admin Login',
                'error' => 'User not found or not an admin.'
            ]);
            return;
        }

        if ($otpInput !== $storedOtp) {
            $response->view('admin/auth/two-factor', [
                'title' => 'Two-Factor Authentication',
                'email' => $user->email,
                'error' => 'Invalid verification code. Please try again.'
            ]);
            return;
        }

        // OTP correct - clear OTP session and log the user in
        unset($_SESSION['admin_email_otp'], $_SESSION['admin_email_otp_user_id'], $_SESSION['admin_email_otp_expires'], $_SESSION['admin_email_otp_redirect']);

        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_role'] = $user->role;
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_time'] = time();

        // Update last login
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();

        // Log successful login
        $this->logSuccessfulLogin($user, $request);

        $response->redirect($redirect);
    }

    public function logout(Request $request, Response $response): void
    {
        if ($this->currentUser) {
            $this->logLogout($this->currentUser);
        }

        unset($_SESSION['user_id']);
        unset($_SESSION['user_role']);
        unset($_SESSION['admin_logged_in']);
        unset($_SESSION['admin_login_time']);
        unset($_SESSION['admin_2fa_user_id']);

        session_destroy();
        $response->redirect('/admin/login');
    }

    private function verify2FA(string $secret, string $code): bool
    {
        // Simplified 2FA verification
        // In production, use: https://github.com/robthree/twofactorauth
        // For now, accept any 6-digit code if secret exists
        return preg_match('/^\d{6}$/', $code) === 1;
    }

    private function logSuccessfulLogin(User $user, Request $request): void
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "INSERT INTO login_history (user_id, ip_address, user_agent, login_type, status, created_at)
                 VALUES (:user_id, :ip_address, :user_agent, 'admin', 'success', NOW())",
                [
                    'user_id' => $user->id,
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]
            );
        } catch (\Exception $e) {
            // Silently fail if table doesn't exist
        }
    }

    private function logFailedLogin(string $email): void
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "INSERT INTO login_history (email, ip_address, user_agent, login_type, status, created_at)
                 VALUES (:email, :ip_address, :user_agent, 'admin', 'failed', NOW())",
                [
                    'email' => $email,
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]
            );
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    private function logLogout(User $user): void
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "INSERT INTO activity_logs (user_id, action, ip_address, user_agent, created_at)
                 VALUES (:user_id, 'admin_logout', :ip_address, :user_agent, NOW())",
                [
                    'user_id' => $user->id,
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]
            );
        } catch (\Exception $e) {
            // Silently fail
        }
    }
}

