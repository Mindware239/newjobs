<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Request;
use App\Models\User;

class AuthFlowService
{
    public function resolveDeliverableEmail(User $user, string $fallbackEmail): string
    {
        return (string)($user->email ?: $user->google_email ?: $user->apple_email ?: $fallbackEmail);
    }

    public function hasBlockedSalesRole(array $roleSlugs): bool
    {
        $blocked = ['sales_manager', 'sales_executive'];
        foreach ($roleSlugs as $slug) {
            if (in_array((string)$slug, $blocked, true)) {
                return true;
            }
        }
        return false;
    }

    public function buildResetLink(Request $request, string $token): string
    {
        $isAdminContext = str_starts_with($request->getPath(), '/admin/');
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = ($isAdminContext ? '/admin/reset-password' : '/reset-password') . "?token={$token}";
        return $scheme . '://' . $host . $path;
    }

    public function shouldExposeResetLinkForLocal(): bool
    {
        $env = getenv('APP_ENV') ?: '';
        $hostName = $_SERVER['HTTP_HOST'] ?? '';
        return in_array(strtolower($env), ['local', 'development', 'dev'], true)
            || in_array($hostName, ['localhost', '127.0.0.1'], true);
    }

    public function normalizeTokenData(?array $row): ?array
    {
        if (!is_array($row)) {
            return null;
        }
        return [
            'user_id' => $row['user_id'] ?? null,
            'email' => $row['email'] ?? null,
            'expires_at' => $row['expires_at'] ?? null
        ];
    }
}
