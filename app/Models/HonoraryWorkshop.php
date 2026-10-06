<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Honorary Mentorship (user, 2026-10-06): mentors teach free in one-day, 4-hour workshops; learners join free.
 * Mentors pay only a platform fee (honorary-mentor registration, ₹155 incl. GST, every 6 months).
 */
class HonoraryWorkshop
{
    public const HOURS = 4;

    public static function create(int $mentorRegId, array $w): int
    {
        self::ensureSchema();
        $db = Database::getInstance();
        $db->execute(
            'INSERT INTO honorary_workshops (mentor_reg_id, title, skill, workshop_date, start_time, mode, city, venue, language, seats) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$mentorRegId, $w['title'], $w['skill'], $w['date'], $w['start'], $w['mode'], $w['city'], $w['venue'], $w['language'], $w['seats']]
        );
        return (int)$db->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchOne(
            "SELECT w.*, (SELECT COUNT(*) FROM honorary_signups s WHERE s.workshop_id = w.id) AS taken FROM honorary_workshops w WHERE w.id = ?", [$id]
        ) ?: null;
    }

    /** Upcoming open workshops (today onwards), soonest first, with mentor name and seats taken. */
    public static function upcoming(int $limit = 200): array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchAll(
            "SELECT w.*, r.full_name AS mentor_name, r.city AS mentor_city, r.state AS mentor_state,
                    (SELECT COUNT(*) FROM honorary_signups s WHERE s.workshop_id = w.id) AS taken
             FROM honorary_workshops w JOIN portal_registrations r ON r.id = w.mentor_reg_id
             WHERE w.status = 'open' AND w.workshop_date >= CURDATE() AND r.payment_status = 'paid' AND (r.valid_until IS NULL OR r.valid_until > NOW())
             ORDER BY w.workshop_date, w.start_time LIMIT " . max(1, min(500, $limit))
        );
    }

    public static function byMentor(int $mentorRegId): array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchAll(
            "SELECT w.*, (SELECT COUNT(*) FROM honorary_signups s WHERE s.workshop_id = w.id) AS taken FROM honorary_workshops w WHERE w.mentor_reg_id = ? ORDER BY w.workshop_date DESC, w.id DESC",
            [$mentorRegId]
        );
    }

    /** Learners signed up for a workshop (for its mentor). */
    public static function attendees(int $workshopId): array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchAll(
            'SELECT r.full_name, r.mobile, r.email, r.city, s.created_at FROM honorary_signups s JOIN portal_registrations r ON r.id = s.learner_reg_id WHERE s.workshop_id = ? ORDER BY s.id',
            [$workshopId]
        );
    }

    /** Workshops a learner joined, with mentor name and phone. */
    public static function forLearner(int $learnerRegId): array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchAll(
            'SELECT w.*, r.full_name AS mentor_name, r.mobile AS mentor_mobile, r.email AS mentor_email FROM honorary_signups s
             JOIN honorary_workshops w ON w.id = s.workshop_id JOIN portal_registrations r ON r.id = w.mentor_reg_id
             WHERE s.learner_reg_id = ? ORDER BY w.workshop_date, w.start_time',
            [$learnerRegId]
        );
    }

    /** Sign a learner up: 'ok', 'already', 'full' or 'closed'. */
    public static function signup(int $workshopId, int $learnerRegId): string
    {
        $w = self::find($workshopId);
        if (!$w || $w['status'] !== 'open' || strtotime((string)$w['workshop_date']) < strtotime('today')) {
            return 'closed';
        }
        $db = Database::getInstance();
        if ($db->fetchOne('SELECT id FROM honorary_signups WHERE workshop_id = ? AND learner_reg_id = ?', [$workshopId, $learnerRegId])) {
            return 'already';
        }
        if ((int)$w['taken'] >= (int)$w['seats']) {
            return 'full';
        }
        $db->execute('INSERT INTO honorary_signups (workshop_id, learner_reg_id) VALUES (?, ?)', [$workshopId, $learnerRegId]);
        return 'ok';
    }

    public static function cancel(int $workshopId, int $mentorRegId): void
    {
        self::ensureSchema();
        Database::getInstance()->execute("UPDATE honorary_workshops SET status = 'cancelled' WHERE id = ? AND mentor_reg_id = ?", [$workshopId, $mentorRegId]);
    }

    /** "10:00 AM – 2:00 PM" */
    public static function timeRange(string $start): string
    {
        $t = strtotime('2000-01-01 ' . $start);
        return $t ? date('g:i A', $t) . ' – ' . date('g:i A', $t + self::HOURS * 3600) : $start;
    }

    public static function ensureSchema(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $pdo = Database::getInstance()->getConnection();
        if ($pdo) {
            $pdo->exec("CREATE TABLE IF NOT EXISTS honorary_workshops (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                mentor_reg_id BIGINT UNSIGNED NOT NULL,
                title VARCHAR(190) NOT NULL,
                skill VARCHAR(120) NOT NULL,
                workshop_date DATE NOT NULL,
                start_time TIME NOT NULL,
                mode ENUM('online','offline') NOT NULL DEFAULT 'offline',
                city VARCHAR(120) NULL,
                venue VARCHAR(500) NULL,
                language VARCHAR(60) NULL,
                seats SMALLINT UNSIGNED NOT NULL DEFAULT 30,
                status ENUM('open','cancelled') NOT NULL DEFAULT 'open',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_hw_date (status, workshop_date),
                KEY idx_hw_mentor (mentor_reg_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $pdo->exec("CREATE TABLE IF NOT EXISTS honorary_signups (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                workshop_id BIGINT UNSIGNED NOT NULL,
                learner_reg_id BIGINT UNSIGNED NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_hs (workshop_id, learner_reg_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $done = true;
    }
}
