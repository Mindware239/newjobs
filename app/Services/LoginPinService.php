<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Optional login PIN (4–6 digits): a user who sets one logs in with mobile / email + PIN instead of
 * waiting for an email OTP. The email OTP always stays available as the fallback.
 *
 * Only a password_hash() of the PIN is stored. MAX_TRIES wrong PINs lock it for LOCK_MINUTES (email OTP
 * still works meanwhile). Admin / sales staff never use a PIN – they always confirm with an email OTP.
 */
class LoginPinService
{
    public const MAX_TRIES = 5;
    public const LOCK_MINUTES = 30;

    public static function has(int $userId): bool
    {
        return self::row($userId) !== null;
    }

    public static function isLocked(int $userId): bool
    {
        $r = self::row($userId);
        return $r !== null && $r['locked_until'] !== null && strtotime((string)$r['locked_until']) > time();
    }

    /** Why a PIN is not acceptable, or null. */
    public static function problem(string $pin): ?array
    {
        if (!preg_match('/^\d{4,6}$/', $pin)) {
            return ['PIN 4 से 6 अंकों का होना चाहिए', 'The PIN must be 4 to 6 digits'];
        }
        // Easy guesses: one digit repeated (1111) or a straight run (1234, 9876).
        $same = count(array_unique(str_split($pin))) === 1;
        $up = str_contains('0123456789', $pin);
        $down = str_contains('9876543210', $pin);
        if ($same || $up || $down) {
            return ['यह PIN बहुत आसान है – 1111 या 1234 जैसा PIN न रखें', 'This PIN is too easy to guess – avoid PINs like 1111 or 1234'];
        }
        return null;
    }

    public static function set(int $userId, string $pin): void
    {
        self::ensureSchema();
        Database::getInstance()->execute(
            'INSERT INTO login_pins (user_id, pin_hash, failed, locked_until, updated_at) VALUES (?, ?, 0, NULL, NOW())
             ON DUPLICATE KEY UPDATE pin_hash = VALUES(pin_hash), failed = 0, locked_until = NULL, updated_at = NOW()',
            [$userId, password_hash($pin, PASSWORD_DEFAULT)]
        );
    }

    public static function remove(int $userId): void
    {
        self::ensureSchema();
        Database::getInstance()->execute('DELETE FROM login_pins WHERE user_id = ?', [$userId]);
    }

    /**
     * Check a PIN. Returns ['ok' => true] or ['ok' => false, 'locked' => bool, 'left' => tries left].
     */
    public static function verify(int $userId, string $pin): array
    {
        $r = self::row($userId);
        if ($r === null) {
            return ['ok' => false, 'locked' => false, 'left' => 0];
        }
        if ($r['locked_until'] !== null && strtotime((string)$r['locked_until']) > time()) {
            return ['ok' => false, 'locked' => true, 'left' => 0];
        }
        $db = Database::getInstance();
        if (preg_match('/^\d{4,6}$/', $pin) && password_verify($pin, (string)$r['pin_hash'])) {
            $db->execute('UPDATE login_pins SET failed = 0, locked_until = NULL WHERE user_id = ?', [$userId]);
            return ['ok' => true];
        }
        $failed = (int)$r['failed'] + 1;
        if ($failed >= self::MAX_TRIES) {
            $db->execute('UPDATE login_pins SET failed = 0, locked_until = NOW() + INTERVAL ' . self::LOCK_MINUTES . ' MINUTE WHERE user_id = ?', [$userId]);
            return ['ok' => false, 'locked' => true, 'left' => 0];
        }
        $db->execute('UPDATE login_pins SET failed = ? WHERE user_id = ?', [$failed, $userId]);
        return ['ok' => false, 'locked' => false, 'left' => self::MAX_TRIES - $failed];
    }

    private static function row(int $userId): ?array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchOne('SELECT pin_hash, failed, locked_until FROM login_pins WHERE user_id = ?', [$userId]) ?: null;
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
                "CREATE TABLE IF NOT EXISTS login_pins (
                    user_id BIGINT UNSIGNED NOT NULL,
                    pin_hash VARCHAR(255) NOT NULL,
                    failed TINYINT UNSIGNED NOT NULL DEFAULT 0,
                    locked_until DATETIME NULL,
                    updated_at DATETIME NULL,
                    PRIMARY KEY (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        }
        $done = true;
    }
}
