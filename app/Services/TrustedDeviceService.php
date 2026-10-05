<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * "Remember this device": after an email OTP on a PC / laptop / phone, that browser may log in to the
 * same account again with just the mobile number or email for TRUST_DAYS days.
 *
 * The browser keeps a random device key in an HttpOnly cookie; the database stores only its SHA-256
 * hash per user, so a leaked table cannot be replayed and a device trusted for one account never
 * opens another.
 */
class TrustedDeviceService
{
    public const TRUST_DAYS = 90;
    private const COOKIE = 'jsx_device';

    /** This browser's device key (created on first use). */
    public static function deviceKey(bool $create = true): ?string
    {
        $key = (string)($_COOKIE[self::COOKIE] ?? '');
        if (preg_match('/^[a-f0-9]{64}$/', $key)) {
            return $key;
        }
        if (!$create) {
            return null;
        }
        $key = bin2hex(random_bytes(32));
        $_COOKIE[self::COOKIE] = $key;
        if (!headers_sent()) {
            setcookie(self::COOKIE, $key, [
                'expires' => time() + 86400 * 400,
                'path' => '/',
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        return $key;
    }

    public static function isTrusted(int $userId): bool
    {
        $key = self::deviceKey(false);
        if ($key === null) {
            return false;
        }
        self::ensureSchema();
        $row = Database::getInstance()->fetchOne(
            'SELECT id FROM trusted_devices WHERE user_id = ? AND device_hash = ? AND revoked_at IS NULL AND expires_at > NOW() LIMIT 1',
            [$userId, hash('sha256', $key)]
        );
        if ($row) {
            Database::getInstance()->execute('UPDATE trusted_devices SET last_used_at = NOW() WHERE id = ?', [(int)$row['id']]);
        }
        return (bool)$row;
    }

    /** Trust this browser for the user (renews the 90 days if already trusted). */
    public static function trust(int $userId): void
    {
        self::ensureSchema();
        $hash = hash('sha256', (string)self::deviceKey());
        $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
        $ip = substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
        Database::getInstance()->execute(
            'INSERT INTO trusted_devices (user_id, device_hash, label, ip_address, last_used_at, expires_at)
             VALUES (?, ?, ?, ?, NOW(), NOW() + INTERVAL ' . self::TRUST_DAYS . ' DAY)
             ON DUPLICATE KEY UPDATE label = VALUES(label), ip_address = VALUES(ip_address), last_used_at = NOW(),
                 expires_at = NOW() + INTERVAL ' . self::TRUST_DAYS . ' DAY, revoked_at = NULL',
            [$userId, $hash, self::label($ua), $ip]
        );
    }

    /** Active trusted devices of a user, newest first; 'current' marks this browser. */
    public static function listFor(int $userId): array
    {
        self::ensureSchema();
        $mine = ($k = self::deviceKey(false)) ? hash('sha256', $k) : '';
        $rows = Database::getInstance()->fetchAll(
            'SELECT id, device_hash, label, ip_address, created_at, last_used_at, expires_at FROM trusted_devices
             WHERE user_id = ? AND revoked_at IS NULL AND expires_at > NOW() ORDER BY last_used_at DESC',
            [$userId]
        );
        foreach ($rows as &$r) {
            $r['current'] = hash_equals((string)$r['device_hash'], $mine);
            unset($r['device_hash']);
        }
        unset($r);
        return $rows;
    }

    public static function revoke(int $userId, int $id): void
    {
        self::ensureSchema();
        Database::getInstance()->execute('UPDATE trusted_devices SET revoked_at = NOW() WHERE id = ? AND user_id = ?', [$id, $userId]);
    }

    public static function revokeAll(int $userId): void
    {
        self::ensureSchema();
        Database::getInstance()->execute('UPDATE trusted_devices SET revoked_at = NOW() WHERE user_id = ? AND revoked_at IS NULL', [$userId]);
    }

    /** "Chrome on Windows" style label from the user agent. */
    private static function label(string $ua): string
    {
        $browser = match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'OPR/') => 'Opera',
            str_contains($ua, 'SamsungBrowser') => 'Samsung Internet',
            str_contains($ua, 'Chrome/') => 'Chrome',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/') => 'Safari',
            default => 'Browser',
        };
        $os = match (true) {
            str_contains($ua, 'Android') => 'Android phone',
            str_contains($ua, 'iPhone') => 'iPhone',
            str_contains($ua, 'iPad') => 'iPad',
            str_contains($ua, 'Windows') => 'Windows PC',
            str_contains($ua, 'Mac OS X') => 'Mac',
            str_contains($ua, 'Linux') => 'Linux PC',
            default => 'device',
        };
        return $browser . ' on ' . $os;
    }

    public static function ensureSchema(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $pdo = Database::getInstance()->getConnection();
        if ($pdo) {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS trusted_devices (
                    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    user_id BIGINT UNSIGNED NOT NULL,
                    device_hash CHAR(64) NOT NULL,
                    label VARCHAR(80) NULL,
                    ip_address VARCHAR(45) NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    last_used_at DATETIME NULL,
                    expires_at DATETIME NOT NULL,
                    revoked_at DATETIME NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY uq_trusted_device (user_id, device_hash),
                    KEY idx_trusted_device_hash (device_hash)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        }
        $done = true;
    }
}
