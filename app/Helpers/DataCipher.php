<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * AES-256-GCM encryption for sensitive registration data (e.g. mentor bank account numbers).
 * Key: PORTAL_DATA_KEY (recommended – set it once and never change it), falling back to JWT_SECRET.
 */
class DataCipher
{
    private const PREFIX = 'enc1:';

    public static function encrypt(string $plain): string
    {
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) {
            throw new \RuntimeException('Encryption failed');
        }
        return self::PREFIX . base64_encode($iv . $tag . $cipher);
    }

    public static function decrypt(?string $value): ?string
    {
        if ($value === null || !str_starts_with($value, self::PREFIX)) {
            return $value;
        }
        $raw = base64_decode(substr($value, strlen(self::PREFIX)), true);
        if ($raw === false || strlen($raw) < 29) {
            return null;
        }
        $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
        return $plain === false ? null : $plain;
    }

    private static function key(): string
    {
        $secret = (string)($_ENV['PORTAL_DATA_KEY'] ?? $_ENV['JWT_SECRET'] ?? '');
        if ($secret === '') {
            throw new \RuntimeException('PORTAL_DATA_KEY is not configured');
        }
        return hash('sha256', $secret, true);
    }
}
