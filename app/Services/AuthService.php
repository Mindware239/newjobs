<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Core\RedisClient;

class AuthService
{
    public function login(string $email, string $password): ?User
    {
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

        // Set session
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_role'] = $user->role;

        return $user;
    }

    public function logout(): void
    {
        session_destroy();
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
        // Would check employer_api_keys table
        return null;
    }
}

