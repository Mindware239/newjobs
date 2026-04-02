<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Candidate;
use App\Models\Employer;
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
        $user->save();

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

        if ($user->save()) {
            Candidate::createForUser((int)$user->id, [
                'full_name' => $data['full_name'],
                'mobile' => $data['mobile'] ?? null
            ]);
            return $user;
        }

        return null;
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

        if ($user->save()) {
            $employer = new Employer();
            $employer->fill([
                'user_id' => $user->id,
                'company_name' => $data['company_name'],
                'company_slug' => $employer->generateSlug($data['company_name']),
                'kyc_status' => 'pending'
            ]);
            $employer->save();
            return $user;
        }

        return null;
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
}
