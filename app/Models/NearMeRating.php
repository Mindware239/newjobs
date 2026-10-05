<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Star ratings (1–5) + feedback that paid Near Me seekers give to service providers.
 * Ratings belong to the provider's mobile number, so they carry over when the provider renews.
 * One rating per seeker pass per provider (a new rating replaces the old one).
 */
class NearMeRating
{
    /** Bayesian prior: new providers start near 3.5 stars until real ratings arrive. */
    private const PRIOR_MEAN = 3.5;
    private const PRIOR_WEIGHT = 3;

    public static function save(array $provider, array $seeker, int $stars, string $feedback): void
    {
        self::ensureSchema();
        $stars = max(1, min(5, $stars));
        $feedback = mb_substr(trim(strip_tags($feedback)), 0, 500);
        Database::getInstance()->execute(
            'INSERT INTO near_me_ratings (provider_mobile, provider_reg_id, seeker_reg_id, seeker_name, stars, feedback)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE provider_reg_id = VALUES(provider_reg_id), stars = VALUES(stars), feedback = VALUES(feedback), status = \'visible\', updated_at = NOW()',
            [(string)$provider['mobile'], (int)$provider['id'], (int)$seeker['id'], mb_substr((string)$seeker['full_name'], 0, 150), $stars, $feedback !== '' ? $feedback : null]
        );
    }

    /** mobile => ['avg' => float, 'count' => int, 'score' => float] */
    public static function summaries(array $mobiles): array
    {
        $mobiles = array_values(array_unique(array_filter($mobiles)));
        if (!$mobiles) {
            return [];
        }
        self::ensureSchema();
        $rows = Database::getInstance()->fetchAll(
            "SELECT provider_mobile, AVG(stars) AS avg_stars, COUNT(*) AS n, SUM(stars) AS total
             FROM near_me_ratings WHERE status = 'visible' AND provider_mobile IN (" . implode(',', array_fill(0, count($mobiles), '?')) . ')
             GROUP BY provider_mobile',
            $mobiles
        );
        $out = [];
        foreach ($rows as $r) {
            $out[$r['provider_mobile']] = [
                'avg' => round((float)$r['avg_stars'], 1),
                'count' => (int)$r['n'],
                'score' => ((float)$r['total'] + self::PRIOR_MEAN * self::PRIOR_WEIGHT) / ((int)$r['n'] + self::PRIOR_WEIGHT),
            ];
        }
        return $out;
    }

    public static function priorScore(): float
    {
        return self::PRIOR_MEAN;
    }

    /** Latest visible feedback for a provider. */
    public static function recent(string $mobile, int $limit = 2): array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchAll(
            "SELECT seeker_name, stars, feedback, updated_at FROM near_me_ratings
             WHERE provider_mobile = ? AND status = 'visible' AND feedback IS NOT NULL
             ORDER BY updated_at DESC LIMIT " . (int)$limit,
            [$mobile]
        );
    }

    public static function mine(int $seekerId, string $mobile): ?array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchOne(
            'SELECT stars, feedback FROM near_me_ratings WHERE seeker_reg_id = ? AND provider_mobile = ?',
            [$seekerId, $mobile]
        );
    }

    public static function all(int $limit = 200): array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchAll(
            'SELECT r.*, p.full_name AS provider_name, p.reg_no AS provider_reg_no FROM near_me_ratings r
             LEFT JOIN portal_registrations p ON p.id = r.provider_reg_id ORDER BY r.updated_at DESC LIMIT ' . (int)$limit
        );
    }

    public static function setStatus(int $id, string $status): void
    {
        if (in_array($status, ['visible', 'hidden'], true)) {
            Database::getInstance()->execute('UPDATE near_me_ratings SET status = ? WHERE id = ?', [$status, $id]);
        }
    }

    /** How many times this mobile has paid for a Near Me provider listing (renewals rank higher). */
    public static function paidTerms(array $mobiles): array
    {
        $mobiles = array_values(array_unique(array_filter($mobiles)));
        if (!$mobiles) {
            return [];
        }
        $out = [];
        foreach (Database::getInstance()->fetchAll(
            "SELECT mobile, COUNT(*) AS n FROM portal_registrations WHERE type = 'nearpro' AND payment_status = 'paid'
               AND mobile IN (" . implode(',', array_fill(0, count($mobiles), '?')) . ') GROUP BY mobile',
            $mobiles
        ) as $r) {
            $out[$r['mobile']] = (int)$r['n'];
        }
        return $out;
    }

    public static function ensureSchema(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        Database::getInstance()->getConnection()?->exec(
            "CREATE TABLE IF NOT EXISTS near_me_ratings (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                provider_mobile VARCHAR(15) NOT NULL,
                provider_reg_id BIGINT UNSIGNED NOT NULL,
                seeker_reg_id BIGINT UNSIGNED NOT NULL,
                seeker_name VARCHAR(150) NULL,
                stars TINYINT UNSIGNED NOT NULL,
                feedback VARCHAR(500) NULL,
                status ENUM('visible','hidden') NOT NULL DEFAULT 'visible',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_nmr_pair (provider_mobile, seeker_reg_id),
                KEY idx_nmr_provider (provider_mobile, status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $done = true;
    }
}
