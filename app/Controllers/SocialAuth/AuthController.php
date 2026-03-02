<?php

declare(strict_types=1);

namespace App\Controllers\SocialAuth;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;

class AuthController extends BaseController
{
    private function canonicalRole(string $role): string
    {
        $r = strtolower(trim($role));
        if ($r === 'social_candidate' || $r === 'social-candidate') return 'candidate';
        if ($r === 'social_employer' || $r === 'social-employer') return 'employer';
        return $r;
    }

public function login(Request $request, Response $response): void
{
    if ($request->getMethod() === 'GET') {
        $redirect = (string)$request->get('redirect', '');
        \App\Middlewares\CsrfMiddleware::generateToken();
        if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
            $role = $this->canonicalRole((string)$_SESSION['user_role']);
            if ($redirect !== '') {
                $response->redirect($redirect);
                return;
            }
            if ($role === 'candidate') {
                $response->redirect('/social-candidate/accountcandidate');
                return;
            }
            if ($role === 'employer') {
                $response->redirect('/social-employer/account');
                return;
            }
        }
        $response->view('social-services/login', ['redirect' => $redirect]);
        return;
    }

    $data = $request->all();
$email = trim((string)($data['email'] ?? ''));
    $password = (string)($data['password'] ?? '');

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $rateKey = 'login_attempt:' . strtolower((string)$email) . ':' . $ip;
    $attempts = 0;
    try {
        $redis = \App\Core\RedisClient::getInstance();
        $val = $redis->isAvailable() ? $redis->get($rateKey) : null;
        $attempts = is_array($val) ? (int)($val['count'] ?? 0) : (int)($val ?? 0);
        if ($attempts >= 5) {
            $response->view('social-services/login', ['error' => 'Too many attempts. Please try again later.']);
            return;
        }
    } catch (\Throwable $t) {}

    if ($email === '' || $password === '') {
        $response->view('social-services/login', [
            'error' => 'Email and password are required.'
        ]);
        return;
    }

    // Find user
    $user = User::where('email', '=', $email)->first();

    if (!$user) {
        $response->view('social-services/login', [
            'error' => 'Invalid email or password'
        ]);
        try {
            if (isset($redis) && $redis->isAvailable()) {
                $redis->set($rateKey, ['count' => $attempts + 1], 300);
            }
        } catch (\Throwable $t) {}
        return;
    }

    // Verify password using User model API (uses password_hash field)
    if (!$user->verifyPassword($password)) {
        $legacyHash = $user->password;
        $migrated = false;
        if (is_string($legacyHash) && $legacyHash !== '') {
            if (password_verify($password, $legacyHash)) {
                $migrated = true;
            } elseif (hash_equals($legacyHash, $password)) {
                // Legacy plaintext password stored; migrate securely
                $migrated = true;
            }
        }
        if ($migrated) {
            $user->setPassword($password);
            $user->save();
        } else {
            $response->view('social-services/login', [
                'error' => 'Invalid email or password'
            ]);
            try {
                if (isset($redis) && $redis->isAvailable()) {
                    $redis->set($rateKey, ['count' => $attempts + 1], 300);
                }
            } catch (\Throwable $t) {}
            return;
        }
    }

    try {
        if (isset($redis) && $redis->isAvailable()) {
            $redis->set($rateKey, 0, 300);
        }
    } catch (\Throwable $t) {}

    // Normalize role to canonical to satisfy middleware checks
    if ($user) {
        $canon = $this->canonicalRole((string)$user->role);
        if ($canon !== (string)$user->role) {
            $user->role = $canon;
            try { $user->save(); } catch (\Throwable $t) {}
        }
    }

    // Check status
    if (($user->status ?? 'pending') !== 'active') {
        $response->view('social-services/login', [
            'error' => 'Account not active.'
        ]);
        return;
    }

    // Update last login
    $user->last_login = date('Y-m-d H:i:s');
    $user->save();

    // Start session (make sure session_start() is called globally)
    $_SESSION['user_id'] = $user->id;
    $_SESSION['user_role'] = (string)$user->role;
    $_SESSION['user_role_canonical'] = $this->canonicalRole((string)$user->role);

    /* ========= ROLE BASED REDIRECT ========= */

    $role = $this->canonicalRole((string)$user->role);
    $returnTo = trim((string)$request->get('redirect', ''));
    if ($role === 'candidate') {

        $candidate = \App\Models\Candidate::findByUserId((int)$user->id);

        if (!$candidate) {
            $candidate = \App\Models\Candidate::createForUser((int)$user->id);
        }

       if ($candidate && isset($candidate->id)) {
    $_SESSION['candidate_id'] = (int)$candidate->id;
}


        if ($returnTo !== '' && strpos($returnTo, '/social-candidate') === 0) {
            $response->redirect($returnTo);
            return;
        }
        $response->redirect('/social-candidate/accountcandidate');
        return;
    }

    if ($role === 'employer') {
        if ($returnTo !== '' && strpos($returnTo, '/social-employer') === 0) {
            $response->redirect($returnTo);
            return;
        }
        $employer = \App\Models\Employer::where('user_id','=',(int)$user->id)->first();
        if (!$employer) {
            $slug = 'employer-' . (int)$user->id;
            $employer = new \App\Models\Employer();
            $employer->fill([
                'user_id' => (int)$user->id,
                'company_name' => '',
                'company_slug' => $slug,
                'verified' => 0
            ]);
            $employer->save();
        }
        // Ensure social employer profile exists and set session to social profile id (used by social pages)
        try {
            $profileModel = new \App\Models\EmployerProfile();
            $profile = $profileModel->findByUser((int)$user->id);
            if (!$profile) {
                $fullName = (string)($user->full_name ?? '');
                $parts = array_values(array_filter(explode(' ', $fullName)));
                $first = $parts[0] ?? 'Employer';
                $last = $parts[1] ?? '';
                $newId = $profileModel->create([
                    'user_id' => (int)$user->id,
                    'full_name' => $fullName,
                    'preferred_name' => $fullName,
                    'pronouns' => '',
                    'prefix' => '',
                    'first_name' => $first,
                    'middle_name' => '',
                    'last_name' => $last,
                    'suffix' => ''
                ]);
                $_SESSION['employer_id'] = (int)$newId;
            } else {
                $_SESSION['employer_id'] = (int)($profile['id'] ?? 0);
            }
        } catch (\Throwable $t) {}
        $response->redirect('/social-employer/account');
        return;
    }

    if (in_array($user->role, ['admin', 'super_admin'], true)) {
        $response->redirect('/admin/dashboard');
        return;
    }

    // Fallback
    $response->redirect('/');
}


   public function register(Request $request, Response $response): void
{
    if ($request->getMethod() === 'GET') {
        \App\Middlewares\CsrfMiddleware::generateToken();
        $response->view('social-services/login', ['initialMode' => 'register']);
        return;
    }

    $data = $request->all();

    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $roleRaw = (string)($data['role'] ?? '');
    $roleCanon = $this->canonicalRole($roleRaw);

    if ($email === '' || $password === '' || !in_array($roleCanon, ['candidate','employer'], true)) {
        die("Invalid input");
    }

    $existing = User::where('email','=',$email)->first();
    if ($existing) {
        die("Email already exists");
    }

    $user = new User();
    $user->fill([
        'email' => strtolower($email),
        'role' => $roleCanon,
        'status' => 'active',
        'last_login' => date('Y-m-d H:i:s')
    ]);
    $user->setPassword($password);
    if (!$user->save()) {
        $response->view('social-services/login', ['error' => 'Registration failed']);
        return;
    }
    
    $_SESSION['user_id'] = (int)$user->id;
    $_SESSION['user_role'] = $roleRaw;
    $_SESSION['user_role_canonical'] = $roleCanon;
    
    if ($roleCanon === 'candidate') {
        $candidate = \App\Models\Candidate::createForUser((int)$user->id);
        if ($candidate && isset($candidate->id)) {
            $_SESSION['candidate_id'] = (int)$candidate->id;
        }
        $response->redirect('/social-candidate/accountcandidate');
        return;
    }
    
    if ($roleCanon === 'employer') {
        $existingEmp = \App\Models\Employer::where('user_id', '=', (int)$user->id)->first();
        if ($existingEmp && isset($existingEmp->id)) {
            $_SESSION['employer_id'] = (int)$existingEmp->id;
        } else {
            $slug = 'employer-' . (int)$user->id;
            $newEmp = new \App\Models\Employer();
            $newEmp->fill([
                'user_id' => (int)$user->id,
                'company_name' => '',
                'company_slug' => $slug,
                'verified' => 0
            ]);
            $newEmp->save();
            if (isset($newEmp->id)) {
                $_SESSION['employer_id'] = (int)$newEmp->id;
            }
        }
        $redir = trim((string)($data['redirect'] ?? ''));
        if ($redir !== '' && strpos($redir, '/social-employer') === 0) {
            $response->redirect($redir);
        } else {
            $response->redirect('/social-employer/account');
        }
        return;
    }
    
    $response->redirect('/');
}
public function logout(Request $request, Response $response): void
{
    // Start session if not started
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Clear session data
    $_SESSION = [];

    // Delete session cookie (VERY IMPORTANT)
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy session
    session_destroy();

    // Regenerate new clean session
    session_start();
    session_regenerate_id(true);

    $response->redirect('/social-services/login');
}

    public function forgotPassword(Request $request, Response $response): void
    {
        if ($request->getMethod() === 'GET') {
            \App\Middlewares\CsrfMiddleware::generateToken();
            $response->view('social-services/forgot-password', []);
            return;
        }
        $data = $request->getJsonBody() ?? $request->all();
        $email = trim((string)($data['email'] ?? ''));
        if ($email === '') {
            $response->json(['error' => 'Email is required'], 422);
            return;
        }
        $user = \App\Models\User::where('email', '=', $email)->first();
        $resetLink = null;
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = gmdate('Y-m-d H:i:s', strtotime('+1 hour'));
            $redis = \App\Core\RedisClient::getInstance();
            if ($redis->isAvailable()) {
                $redis->set("password_reset:{$token}", [
                    'user_id' => (int)$user->id,
                    'email' => (string)$user->email,
                    'expires_at' => $expiresAt
                ], 3600);
            }
            $db = \App\Core\Database::getInstance();
            try {
                $db->query("
                    CREATE TABLE IF NOT EXISTS password_resets (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        email VARCHAR(255) NOT NULL,
                        token VARCHAR(128) NOT NULL,
                        user_id INT NOT NULL,
                        expires_at DATETIME NOT NULL,
                        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ");
                $db->query("DELETE FROM password_resets WHERE user_id = :uid OR expires_at < UTC_TIMESTAMP()", ['uid' => (int)$user->id]);
                $db->query(
                    "INSERT INTO password_resets (email, token, user_id, expires_at) VALUES (:email, :token, :uid, :exp)",
                    ['email' => (string)$user->email, 'token' => $token, 'uid' => (int)$user->id, 'exp' => $expiresAt]
                );
            } catch (\Throwable $t) {
                error_log('Failed to persist password reset token: ' . $t->getMessage());
            }
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $resetLink = $scheme . '://' . $host . '/social-services/reset-password?token=' . $token;
            \App\Services\MailService::sendPasswordReset((string)$user->email, $resetLink);
        }
        $response->json([
            'success' => true,
            'message' => 'If an account exists, a reset link has been sent.',
            'reset_link' => $resetLink
        ]);
    }

    public function resetPassword(Request $request, Response $response): void
    {
        $token = (string)($request->get('token') ?? '');
        if ($request->getMethod() === 'GET') {
            \App\Middlewares\CsrfMiddleware::generateToken();
            if ($token === '') {
                $response->view('social-services/reset-password', ['error' => 'Invalid reset token', 'token' => '']);
                return;
            }
            $redis = \App\Core\RedisClient::getInstance();
            $tokenData = $redis->isAvailable() ? $redis->get("password_reset:{$token}") : null;
            if (!$tokenData) {
                $db = \App\Core\Database::getInstance();
                try {
                    $row = $db->fetchOne(
                        "SELECT user_id, email FROM password_resets WHERE token = :token AND expires_at > UTC_TIMESTAMP()",
                        ['token' => $token]
                    );
                    if ($row) {
                        $tokenData = ['user_id' => (int)$row['user_id'], 'email' => (string)$row['email']];
                    }
                } catch (\Throwable $t) {}
            }
            if (!$tokenData) {
                $response->view('social-services/reset-password', ['error' => 'Invalid or expired token', 'token' => '']);
                return;
            }
            $response->view('social-services/reset-password', ['error' => '', 'token' => $token]);
            return;
        }
        $data = $request->getJsonBody() ?? $request->all();
        $token = (string)($data['token'] ?? $token);
        $password = (string)($data['password'] ?? '');
        $confirm = (string)($data['password_confirm'] ?? '');
        if ($token === '') {
            $response->json(['error' => 'Reset token is required'], 422);
            return;
        }
        if (strlen($password) < 8) {
            $response->json(['error' => 'Password must be at least 8 characters'], 422);
            return;
        }
        if ($password !== $confirm) {
            $response->json(['error' => 'Passwords do not match'], 422);
            return;
        }
        $redis = \App\Core\RedisClient::getInstance();
        $tokenData = $redis->isAvailable() ? $redis->get("password_reset:{$token}") : null;
        if (!$tokenData) {
            $db = \App\Core\Database::getInstance();
            try {
                $row = $db->fetchOne(
                    "SELECT user_id, email FROM password_resets WHERE token = :token AND expires_at > UTC_TIMESTAMP()",
                    ['token' => $token]
                );
                if ($row) {
                    $tokenData = ['user_id' => (int)$row['user_id'], 'email' => (string)$row['email']];
                }
            } catch (\Throwable $t) {}
        }
        if (!$tokenData) {
            $response->json(['error' => 'Invalid or expired reset token'], 400);
            return;
        }
        $user = \App\Models\User::find((int)$tokenData['user_id']);
        if (!$user) {
            $response->json(['error' => 'User not found'], 404);
            return;
        }
        $user->setPassword($password);
        if (!$user->save()) {
            $response->json(['error' => 'Failed to update password'], 500);
            return;
        }
        if ($redis->isAvailable()) {
            $redis->delete("password_reset:{$token}");
        }
        try {
            \App\Core\Database::getInstance()->query("DELETE FROM password_resets WHERE token = :token", ['token' => $token]);
        } catch (\Throwable $t) {}
        $response->json(['success' => true, 'message' => 'Password reset successfully. You can now login.']);
    }
}

