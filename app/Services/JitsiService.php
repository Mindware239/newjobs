<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmployerSubscription;

class JitsiService
{
    public function getDomain(): string
    {
        $domain = (string)($_ENV['JITSI_DOMAIN'] ?? 'meet.jit.si');
        $domain = trim($domain);
        $domain = preg_replace('#^https?://#i', '', $domain);
        $domain = rtrim((string)$domain, '/');
        return $domain !== '' ? $domain : 'meet.jit.si';
    }

    public function getAppName(): string
    {
        $name = (string)($_ENV['JITSI_APP_NAME'] ?? ($_ENV['APP_NAME'] ?? 'Job Portal'));
        return $name !== '' ? $name : 'Job Portal';
    }

    public function isRecordingEnabled(): bool
    {
        return (string)($_ENV['JITSI_RECORDING_ENABLED'] ?? 'false') === 'true';
    }

    public function generateRoomName(): string
    {
        return 'mi-' . $this->uuidV4();
    }

    public function generateRoomPassword(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
    }

    public function encrypt(string $plaintext): ?string
    {
        $key = $this->getCryptoKey();
        if ($key === null) {
            return null;
        }
        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($ciphertext === false) {
            return null;
        }
        return base64_encode($iv . $tag . $ciphertext);
    }

    public function decrypt(?string $encrypted): ?string
    {
        if (!$encrypted) return null;
        $key = $this->getCryptoKey();
        if ($key === null) {
            return null;
        }
        $raw = base64_decode($encrypted, true);
        if ($raw === false || strlen($raw) < (12 + 16 + 1)) {
            return null;
        }
        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $ciphertext = substr($raw, 28);
        $plain = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        return $plain === false ? null : $plain;
    }

    public function getCurrentSubscriptionForEmployer(int $employerId): ?EmployerSubscription
    {
        return EmployerSubscription::getCurrentForEmployer($employerId);
    }

    public function isPremiumForEmployer(int $employerId): bool
    {
        $sub = $this->getCurrentSubscriptionForEmployer($employerId);
        if (!$sub || (!$sub->isActive() && !$sub->isInGracePeriod())) {
            return false;
        }
        $plan = $sub->plan();
        $slug = (string)($plan?->attributes['slug'] ?? '');
        return in_array($slug, ['premium', 'enterprise'], true);
    }

    public function canUseBranding(int $employerId): bool
    {
        $sub = $this->getCurrentSubscriptionForEmployer($employerId);
        if (!$sub) return false;
        return $sub->canAccessFeature('custom_branding') || $this->isPremiumForEmployer($employerId);
    }

    public function canUseAnalytics(int $employerId): bool
    {
        $sub = $this->getCurrentSubscriptionForEmployer($employerId);
        if (!$sub) return false;
        return $sub->canAccessFeature('analytics_dashboard') || $this->isPremiumForEmployer($employerId);
    }

    public function canUseAdminIntervention(int $employerId): bool
    {
        return $this->isPremiumForEmployer($employerId);
    }

    public function canUseMuteAll(int $employerId): bool
    {
        return $this->isPremiumForEmployer($employerId);
    }

    public function canUsePriorityQuality(int $employerId): bool
    {
        return $this->isPremiumForEmployer($employerId);
    }

    public function canUseRecording(int $employerId): bool
    {
        if (!$this->isRecordingEnabled()) return false;
        return $this->isPremiumForEmployer($employerId);
    }

    private function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        $hex = bin2hex($data);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    private function getCryptoKey(): ?string
    {
        $secret = (string)($_ENV['JITSI_CRYPTO_SECRET'] ?? ($_ENV['JWT_SECRET'] ?? ($_ENV['CSRF_SECRET'] ?? '')));
        if ($secret === '') {
            return null;
        }
        return hash('sha256', $secret, true);
    }
}

