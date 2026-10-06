<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Free job posts on the state & city job board (/jobs-by-state). Any employer account – private limited
 * company, proprietorship, partnership, LLP, shop – can post its requirement free of cost; a post stays
 * live for LIVE_DAYS days. Admin can hide a post; the poster can close it.
 */
class FreeJobPost
{
    public const LIVE_DAYS = 30;
    public const MAX_PER_DAY = 10;
    /** Employers pay: this many posts a month are free, every further post costs EXTRA_POST_FEE (₹200 + 18% GST). */
    public const FREE_POSTS_PER_MONTH = 3;
    public const EXTRA_POST_FEE = 236.00;
    /** Contact reveals per user per day (contacts are never in the page HTML – see StateJobsController::contact). */
    public const CONTACTS_PER_DAY = 30;

    public const COMPANY_TYPES = [
        'pvt_ltd' => ['प्राइवेट लिमिटेड कंपनी', 'Private Limited Company'],
        'proprietorship' => ['प्रोप्राइटरशिप फ़र्म', 'Proprietorship Firm'],
        'partnership' => ['पार्टनरशिप फ़र्म', 'Partnership Firm'],
        'llp' => ['LLP', 'LLP'],
        'opc' => ['वन पर्सन कंपनी (OPC)', 'One Person Company (OPC)'],
        'public_ltd' => ['पब्लिक लिमिटेड कंपनी', 'Public Limited Company'],
        'shop' => ['दुकान / स्थानीय व्यवसाय', 'Shop / local business'],
        'other' => ['अन्य', 'Other'],
    ];

    public const JOB_TYPES = [
        'full_time' => ['फ़ुल-टाइम', 'Full-time'],
        'part_time' => ['पार्ट-टाइम', 'Part-time'],
        'wfh' => ['वर्क फ्रॉम होम', 'Work from home'],
        'contract' => ['कॉन्ट्रैक्ट', 'Contract'],
        'internship' => ['इंटर्नशिप', 'Internship'],
        'apprentice' => ['अप्रेंटिस', 'Apprentice'],
    ];

    /** $pendingPayment: an extra post beyond the free quota – stored, goes live once paid (activatePaid). */
    public static function create(array $p, bool $pendingPayment = false): int
    {
        self::ensureSchema();
        $db = Database::getInstance();
        $db->execute(
            'INSERT INTO free_job_posts (user_id, company_name, company_type, contact_person, phone, email, title, job_type, state, city,
                vacancies, salary, qualification, experience, description, how_to_apply, status, published_at, expires_at, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ' . ($pendingPayment ? "'pending_payment', NULL, NULL" : "'live', NOW(), NOW() + INTERVAL " . self::LIVE_DAYS . ' DAY') . ', ?)',
            [$p['user_id'], $p['company_name'], $p['company_type'], $p['contact_person'], $p['phone'], $p['email'], $p['title'], $p['job_type'],
             $p['state'], $p['city'], $p['vacancies'], $p['salary'], $p['qualification'], $p['experience'], $p['description'], $p['how_to_apply'],
             substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45)]
        );
        return (int)$db->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchOne('SELECT * FROM free_job_posts WHERE id = ?', [$id]) ?: null;
    }

    /** Live, not expired posts (newest first). */
    public static function live(): array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchAll("SELECT * FROM free_job_posts WHERE status = 'live' AND expires_at > NOW() ORDER BY published_at DESC");
    }

    public static function byUser(int $userId): array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchAll('SELECT * FROM free_job_posts WHERE user_id = ? ORDER BY id DESC LIMIT 100', [$userId]);
    }

    /** Posts this user made this calendar month that count against the free quota (unpaid drafts do not). */
    public static function postedThisMonth(int $userId): int
    {
        self::ensureSchema();
        return (int)(Database::getInstance()->fetchOne(
            "SELECT COUNT(*) AS n FROM free_job_posts WHERE user_id = ? AND status <> 'pending_payment' AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')", [$userId]
        )['n'] ?? 0);
    }

    /** A paid extra post goes live for LIVE_DAYS from now. */
    public static function activatePaid(int $id): void
    {
        self::ensureSchema();
        Database::getInstance()->execute(
            "UPDATE free_job_posts SET status = 'live', published_at = NOW(), expires_at = NOW() + INTERVAL " . self::LIVE_DAYS . " DAY WHERE id = ? AND status = 'pending_payment'", [$id]
        );
    }

    public static function postedToday(int $userId): int
    {
        self::ensureSchema();
        return (int)(Database::getInstance()->fetchOne('SELECT COUNT(*) AS n FROM free_job_posts WHERE user_id = ? AND created_at >= CURDATE()', [$userId])['n'] ?? 0);
    }

    public static function setStatus(int $id, string $status): void
    {
        if (in_array($status, ['live', 'hidden', 'closed'], true)) {
            self::ensureSchema();
            Database::getInstance()->execute('UPDATE free_job_posts SET status = ? WHERE id = ?', [$status, $id]);
        }
    }

    /** Record a contact reveal; false when the user is over today's limit (re-opening the same post is free). */
    public static function revealContact(int $userId, int $postId): bool
    {
        self::ensureSchema();
        $db = Database::getInstance();
        if ($db->fetchOne('SELECT id FROM free_job_contact_views WHERE user_id = ? AND post_id = ? AND created_at >= CURDATE()', [$userId, $postId])) {
            return true;
        }
        $n = (int)($db->fetchOne('SELECT COUNT(*) AS n FROM free_job_contact_views WHERE user_id = ? AND created_at >= CURDATE()', [$userId])['n'] ?? 0);
        if ($n >= self::CONTACTS_PER_DAY) {
            return false;
        }
        $db->execute('INSERT INTO free_job_contact_views (user_id, post_id, ip_address) VALUES (?, ?, ?)', [$userId, $postId, substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45)]);
        return true;
    }

    public static function countView(int $id): void
    {
        Database::getInstance()->execute('UPDATE free_job_posts SET views = views + 1 WHERE id = ?', [$id]);
    }

    /** Admin list (all statuses). */
    public static function adminList(string $status = '', string $q = '', int $limit = 300): array
    {
        self::ensureSchema();
        $where = [];
        $params = [];
        if (in_array($status, ['live', 'hidden', 'closed'], true)) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        if ($q !== '') {
            $where[] = '(title LIKE ? OR company_name LIKE ? OR city LIKE ? OR state LIKE ? OR email LIKE ?)';
            array_push($params, ...array_fill(0, 5, '%' . $q . '%'));
        }
        return Database::getInstance()->fetchAll(
            'SELECT * FROM free_job_posts ' . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY id DESC LIMIT ' . max(1, min(1000, $limit)),
            $params
        );
    }

    public static function url(array $p): string
    {
        $slug = strtolower(trim((string)preg_replace('/[^A-Za-z0-9]+/', '-', $p['title'] . ' ' . $p['city']), '-'));
        return '/free-job/' . (int)$p['id'] . '-' . substr($slug !== '' ? $slug : 'job', 0, 90);
    }

    public static function ensureSchema(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $pdo = Database::getInstance()->getConnection();
        if ($pdo) {
            $pdo->exec("CREATE TABLE IF NOT EXISTS free_job_posts (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id BIGINT UNSIGNED NOT NULL,
                company_name VARCHAR(190) NOT NULL,
                company_type VARCHAR(30) NOT NULL,
                contact_person VARCHAR(120) NOT NULL,
                phone VARCHAR(20) NULL,
                email VARCHAR(190) NULL,
                title VARCHAR(190) NOT NULL,
                job_type VARCHAR(20) NOT NULL DEFAULT 'full_time',
                state VARCHAR(80) NOT NULL,
                city VARCHAR(120) NOT NULL,
                vacancies INT UNSIGNED NULL,
                salary VARCHAR(120) NULL,
                qualification VARCHAR(190) NULL,
                experience VARCHAR(80) NULL,
                description TEXT NULL,
                how_to_apply VARCHAR(500) NULL,
                status ENUM('live','hidden','closed','pending_payment') NOT NULL DEFAULT 'live',
                views INT UNSIGNED NOT NULL DEFAULT 0,
                published_at DATETIME NULL,
                expires_at DATETIME NULL,
                ip_address VARCHAR(45) NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_fjp_live (status, expires_at, state, city),
                KEY idx_fjp_user (user_id, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            try {
                $st = Database::getInstance()->fetchOne("SELECT COLUMN_TYPE AS t FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'free_job_posts' AND COLUMN_NAME = 'status'");
                if ($st && !str_contains((string)$st['t'], 'pending_payment')) {
                    $pdo->exec("ALTER TABLE free_job_posts MODIFY status ENUM('live','hidden','closed','pending_payment') NOT NULL DEFAULT 'live'");
                }
            } catch (\Throwable $e) {
                error_log('free_job_posts status upgrade: ' . $e->getMessage());
            }
            $pdo->exec("CREATE TABLE IF NOT EXISTS free_job_contact_views (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id BIGINT UNSIGNED NOT NULL,
                post_id BIGINT UNSIGNED NOT NULL,
                ip_address VARCHAR(45) NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_fjcv_user (user_id, created_at),
                KEY idx_fjcv_post (post_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $done = true;
    }
}
