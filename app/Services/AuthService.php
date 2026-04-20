<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Candidate;
use App\Models\Employer;
use App\Models\EmployerSetting;
use App\Core\Database;
use App\Core\RedisClient;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService
{
    private string $jwtSecret;
    private string $jwtAlgo = 'HS256';
    private int $tokenExpiry = 86400; // 24 hours

    public function __construct()
    {
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? 'your-very-secure-fallback-secret-key-12345';

        $appEnv = strtolower((string)($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'local'));
        if ($appEnv === 'production' && $this->jwtSecret === 'your-very-secure-fallback-secret-key-12345') {
            throw new \RuntimeException('JWT_SECRET must be configured in production.');
        }
    }

    public function login(string $email, string $password): ?User
    {
        /** @var User|null $user */
        $user = User::where('email', '=', $email)->first();
        
        if (!$user || !$user->verifyPassword($password)) {
            return null;
        }

        if ($user->status !== 'active') {
            return null;
        }

        // Update last login
        $user->last_login = date('Y-m-d H:i:s');
        try {
            $user->save();
        } catch (\Throwable $e) {
            error_log("AuthService Login Save Error: " . $e->getMessage());
        }

        return $user;
    }

    public function registerCandidate(array $data): ?User
    {
        if (User::where('email', '=', $data['email'])->first()) {
            return null;
        }

        $user = new User();
        $user->fill([
            'email' => $data['email'],
            'role' => 'candidate',
            'status' => 'active'
        ]);
        $user->setPassword($data['password']);

        try {
            $user->save();
            Candidate::createForUser((int)$user->id, [
                'full_name' => $data['full_name'],
                'mobile' => $data['mobile'] ?? null
            ]);
            return $user;
        } catch (\Throwable $e) {
            error_log("AuthService Register Candidate Error: " . $e->getMessage());
            return null;
        }
    }

    public function registerEmployer(array $data): ?User
    {
        if (User::where('email', '=', $data['email'])->first()) {
            return null;
        }

        $user = new User();
        $user->fill([
            'email' => $data['email'],
            'role' => 'employer',
            'status' => 'active',
            'phone' => $data['phone'] ?? null
        ]);
        $user->setPassword($data['password']);

        try {
            $user->save();
            $employer = new Employer();
            $employer->fill([
                'user_id' => $user->id,
                'company_name' => $data['company_name'],
                'company_slug' => $employer->generateSlug($data['company_name']),
                'kyc_status' => 'pending'
            ]);
            $employer->save();
            return $user;
        } catch (\Throwable $e) {
            error_log("AuthService Register Employer Error: " . $e->getMessage());
            return null;
        }
    }

    public function loginByPhone(string $phone): ?User
    {
        $user = $this->findUserByPhone($phone);
        if (!$user || $user->status !== 'active') {
            return null;
        }

        $user->last_login = date('Y-m-d H:i:s');
        try {
            $user->save();
        } catch (\Throwable $e) {
            error_log("AuthService LoginByPhone Save Error: " . $e->getMessage());
        }

        return $user;
    }

    public function registerCandidateWithPhone(array $data): array
    {
        $phone = self::normalizePhoneNumber((string)($data['phone'] ?? ''));
        if ($phone === '') {
            return ['success' => false, 'error' => 'Valid phone number is required'];
        }

        $existingPhoneUser = $this->findUserByPhone($phone);
        if ($existingPhoneUser) {
            if ($existingPhoneUser->role === 'candidate') {
                return ['success' => false, 'error' => 'Phone number is already registered. Please login with OTP.'];
            }

            return ['success' => false, 'error' => 'This phone number is already used by another account type.'];
        }

        $email = $this->resolveRegistrationEmail($data, $phone);
        $existingEmailUser = User::where('email', '=', $email)->first();
        if ($existingEmailUser) {
            return ['success' => false, 'error' => 'Email already registered'];
        }

        $user = new User();
        $user->fill([
            'email' => $email,
            'role' => 'candidate',
            'status' => 'active',
            'phone' => $phone,
            'is_phone_verified' => 1,
        ]);
        $user->setPassword((string)($data['password'] ?? bin2hex(random_bytes(16))));

        try {
            $user->save();
            
            Candidate::createForUser((int)$user->id, [
                'full_name' => $data['full_name'] ?? null,
                'mobile' => $phone,
                'created_by' => 'self',
                'source' => 'phone_otp_registration',
                'profile_status' => 'unverified',
                'visibility' => 'limited',
                'is_profile_complete' => 0,
            ]);

            $additionalMobile = $this->storeAdditionalMobile($user, (string)($data['additional_mobile'] ?? ($data['alternate_phone'] ?? '')));

            return [
                'success' => true,
                'user' => $user,
                'additional_mobile' => $additionalMobile,
            ];
        } catch (\Throwable $e) {
            error_log("AuthService RegisterCandidateWithPhone Error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to create candidate account. Please try again.'];
        }
    }

    public function registerEmployerWithPhone(array $data): array
    {
        $phone = self::normalizePhoneNumber((string)($data['phone'] ?? ''));
        if ($phone === '') {
            return ['success' => false, 'error' => 'Valid phone number is required'];
        }

        $companyName = trim((string)($data['company_name'] ?? ''));
        if ($companyName === '') {
            return ['success' => false, 'error' => 'Company name is required'];
        }

        $existingPhoneUser = $this->findUserByPhone($phone);
        if ($existingPhoneUser) {
            if ($existingPhoneUser->role === 'employer') {
                return ['success' => false, 'error' => 'Phone number is already registered. Please login with OTP.'];
            }

            return ['success' => false, 'error' => 'This phone number is already used by another account type.'];
        }

        $email = $this->resolveRegistrationEmail($data, $phone);
        $existingEmailUser = User::where('email', '=', $email)->first();
        if ($existingEmailUser) {
            return ['success' => false, 'error' => 'Email already registered'];
        }

        $user = new User();
        $user->fill([
            'email' => $email,
            'role' => 'employer',
            'status' => 'active',
            'phone' => $phone,
            'is_phone_verified' => 1,
        ]);
        $user->setPassword((string)($data['password'] ?? bin2hex(random_bytes(16))));

        try {
            $user->save();

            $employer = new Employer();
            $employer->fill([
                'user_id' => $user->id,
                'company_name' => $companyName,
                'company_slug' => $employer->generateSlug($companyName),
                'website' => $data['website'] ?? null,
                'description' => $data['description'] ?? null,
                'industry' => $data['industry'] ?? null,
                'size' => $data['company_size'] ?? null,
                'country' => $data['country'] ?? null,
                'state' => $data['state'] ?? null,
                'city' => $data['city'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'kyc_status' => 'pending',
            ]);

            $employer->save();

            $settings = new EmployerSetting();
            $settings->fill([
                'employer_id' => (int)$employer->id,
                'billing_plan' => 'free',
                'credits' => 0,
                'timezone' => (($data['country'] ?? '') === 'India') ? 'Asia/Kolkata' : 'UTC',
            ]);
            $settings->save();

            $additionalMobile = $this->storeAdditionalMobile($user, (string)($data['additional_mobile'] ?? ($data['alternate_phone'] ?? '')));

            return [
                'success' => true,
                'user' => $user,
                'additional_mobile' => $additionalMobile,
            ];
        } catch (\Throwable $e) {
            error_log("AuthService RegisterEmployerWithPhone Error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to create employer account. Please try again.'];
        }
    }

    public static function getCookieDomain(): string
    {
        $hostHeader = (string)($_SERVER['HTTP_HOST'] ?? '');
        if (!$hostHeader) {
            return '';
        }

        $hostNoPort = preg_replace('/:\d+$/', '', $hostHeader);
        if (preg_match('/^(localhost|127\.0\.0\.1)$/i', $hostNoPort) || filter_var($hostNoPort, FILTER_VALIDATE_IP)) {
            return '';
        }

        $parts = explode('.', $hostNoPort);
        if (count($parts) >= 2) {
            return '.' . implode('.', array_slice($parts, -2));
        }

        return '';
    }

    public function generateToken(User $user): string
    {
        $payload = [
            'iss' => $_ENV['APP_URL'] ?? 'http://localhost',
            'iat' => time(),
            'exp' => time() + $this->tokenExpiry,
            'sub' => $user->id,
            'role' => $user->role,
            'email' => $user->email
        ];

        return JWT::encode($payload, $this->jwtSecret, $this->jwtAlgo);
    }

    public function setTokenCookie(string $token, int $expiry = null): void
    {
        if (headers_sent()) {
            return;
        }

        $expiry = $expiry ?? $this->tokenExpiry;
        $domain = self::getCookieDomain();
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');

        setcookie('access_token', $token, [
            'expires' => time() + $expiry,
            'path' => '/',
            'domain' => $domain,
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, $this->jwtAlgo));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function getCurrentUser(): ?User
    {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            return User::find((int)$userId);
        }
        return null;
    }

    public function verifyApiKey(string $key): ?User
    {
        // API key verification stub
        return null;
    }

    public function findUserByPhone(string $phone): ?User
    {
        $normalized = self::normalizePhoneNumber($phone);
        if ($normalized === '') {
            return null;
        }

        $user = User::where('phone', '=', $normalized)->first();
        if ($user) {
            return $user;
        }

        $digits = preg_replace('/\D+/', '', $normalized);
        if ($digits === '' || $digits === null) {
            return null;
        }

        $db = Database::getInstance();
        $row = $db->fetchOne(
            "SELECT * FROM users
             WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), '(', ''), ')', '') = :digits
             LIMIT 1",
            ['digits' => $digits]
        );

        return $row ? new User($row) : null;
    }

    public static function normalizePhoneNumber(?string $phone): string
    {
        $phone = trim((string)$phone);
        if ($phone === '') {
            return '';
        }

        $hasPlus = str_starts_with($phone, '+');
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === null || $digits === '') {
            return '';
        }

        if ($hasPlus) {
            return '+' . $digits;
        }

        if (strlen($digits) === 10) {
            return '+91' . $digits;
        }

        if (strlen($digits) > 10) {
            return '+' . $digits;
        }

        return '';
    }

    private function resolveRegistrationEmail(array $data, string $phone): string
    {
        $email = trim((string)($data['email'] ?? ''));
        if ($email !== '') {
            return strtolower($email);
        }

        $digits = preg_replace('/\D+/', '', $phone) ?: uniqid();
        return 'phone+' . $digits . '@mobile.local';
    }

    private function storeAdditionalMobile(User $user, string $additionalPhone): ?string
    {
        $normalized = self::normalizePhoneNumber($additionalPhone);
        if ($normalized === '' || $normalized === ($user->phone ?? '')) {
            return null;
        }

        $prefs = $user->getNotificationPreferences();
        if (!is_array($prefs)) {
            $prefs = [];
        }
        $prefs['contact'] = is_array($prefs['contact'] ?? null) ? $prefs['contact'] : [];
        $prefs['contact']['additional_mobile'] = $normalized;
        $user->setNotificationPreferences($prefs);
        $user->save();

        if ($user->role === 'candidate') {
            $candidate = Candidate::findByUserId((int)$user->id);
            if ($candidate) {
                $preferences = json_decode((string)($candidate->attributes['preferences_data'] ?? '{}'), true);
                if (!is_array($preferences)) {
                    $preferences = [];
                }
                $preferences['contact'] = is_array($preferences['contact'] ?? null) ? $preferences['contact'] : [];
                $preferences['contact']['additional_mobile'] = $normalized;
                $candidate->setAttribute('preferences_data', json_encode($preferences));
                $candidate->save();
            }
        } elseif ($user->role === 'employer') {
            $employer = Employer::findByUserId((int)$user->id);
            if ($employer) {
                $settings = EmployerSetting::where('employer_id', '=', (int)($employer->id ?? 0))->first() ?? new EmployerSetting();
                $existing = json_decode((string)($settings->attributes['notification_pref'] ?? '{}'), true);
                if (!is_array($existing)) {
                    $existing = [];
                }
                $existing['contact'] = is_array($existing['contact'] ?? null) ? $existing['contact'] : [];
                $existing['contact']['additional_mobile'] = $normalized;
                $settings->fill([
                    'employer_id' => (int)$employer->id,
                    'notification_pref' => json_encode($existing),
                ]);
                $settings->save();
            }
        }

        return $normalized;
    }
}
