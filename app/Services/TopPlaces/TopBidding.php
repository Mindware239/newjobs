<?php

declare(strict_types=1);

namespace App\Services\TopPlaces;

use App\Core\Database;

/**
 * Paid top places (user, 2026-10-07). All prices + 18% GST.
 *
 * Companies – "Top Hiring Companies" (homepage + /jobs), COMPANY_SLOTS places per day:
 *   - daily auction for a future day: bids from ₹500, steps of ₹500; bidding only 10:00–18:00 IST,
 *     the auction for day D closes at 18:00 on D-1; highest bid = highest place.
 *   - "Top place for a month": place 1 for 30 days for ₹15,000, no bidding (one company at a time);
 *     the daily auctions then fill places 2…COMPANY_SLOTS.
 *   - company logo in its top places: ₹1,200 once per company (admin can hide a logo).
 * Job seekers – resume boost: RESUME_SLOTS places per job category per 15-day period, bids from ₹500,
 *   steps of ₹500, same 10:00–18:00 window, closing at 18:00 the day before the period starts; shown to at
 *   most RESUME_MAX_VIEWS different employers (employer dashboard "Top Candidates").
 * Winners pay after the close (PAY_HOURS); an unpaid win lapses and the next highest bid is offered the place.
 * Losing bidders pay nothing.
 */
class TopBidding
{
    public const GST = 0.18;
    public const MIN_BID = 500;
    public const STEP = 500;
    public const OPEN_HOUR = 10;
    public const CLOSE_HOUR = 18;
    public const COMPANY_SLOTS = 5;
    public const RESUME_SLOTS = 10;
    public const RESUME_DAYS = 15;
    public const RESUME_MAX_VIEWS = 500;
    public const MONTH_PRICE = 15000;
    public const MONTH_DAYS = 30;
    public const LOGO_PRICE = 1200;
    public const PAY_HOURS = 5;            // winners pay within 5 hours of the close (18:00 → 23:00)
    public const COMPANY_DAYS_AHEAD = 7;   // companies can bid for the next 7 days
    public const RESUME_PERIODS_AHEAD = 2;
    /** 15-day resume periods are counted from this date. */
    private const RESUME_EPOCH = '2026-10-01';

    public static function withGst(float $base): float
    {
        return round($base * (1 + self::GST), 2);
    }

    // ------------------------------------------------------------------ time rules

    public static function biddingOpenNow(): bool
    {
        $h = (int)date('G');
        return $h >= self::OPEN_HOUR && $h < self::CLOSE_HOUR;
    }

    /** Bidding for a slot starting on $date closes at 18:00 the day before. */
    public static function closesAt(string $date): int
    {
        return (int)strtotime($date . ' ' . self::CLOSE_HOUR . ':00:00 -1 day');
    }

    /** Company days that can still be bid for, earliest first. */
    public static function companyDates(): array
    {
        $out = [];
        for ($i = 1; $i <= self::COMPANY_DAYS_AHEAD + 1 && count($out) < self::COMPANY_DAYS_AHEAD; $i++) {
            $d = date('Y-m-d', strtotime("+{$i} day"));
            if (self::closesAt($d) > time()) {
                $out[] = $d;
            }
        }
        return $out;
    }

    /** Start dates of the next resume periods still open for bidding. */
    public static function resumePeriods(): array
    {
        $epoch = strtotime(self::RESUME_EPOCH);
        $n = max(0, (int)floor((time() - $epoch) / 86400 / self::RESUME_DAYS));
        $out = [];
        for ($i = $n; count($out) < self::RESUME_PERIODS_AHEAD && $i < $n + 10; $i++) {
            $start = date('Y-m-d', $epoch + $i * self::RESUME_DAYS * 86400);
            if (self::closesAt($start) > time()) {
                $out[] = $start;
            }
        }
        return $out;
    }

    public static function periodEnd(string $start): string
    {
        return date('Y-m-d', strtotime($start . ' +' . (self::RESUME_DAYS - 1) . ' day'));
    }

    // ------------------------------------------------------------------ bidding

    /** Number of auction places on a company day (place 1 may be taken by a monthly buyer). */
    public static function companyAuctionSlots(string $date): int
    {
        return self::monthHolder($date) ? self::COMPANY_SLOTS - 1 : self::COMPANY_SLOTS;
    }

    /**
     * Place or raise a bid. $kind company|resume. Returns [ok, message].
     * One bid per bidder per auction; a new bid must be higher than the bidder's current one.
     */
    public static function placeBid(string $kind, int $userId, int $ownerId, string $date, string $category, int $amount): array
    {
        self::ensureSchema();
        if (!self::biddingOpenNow()) {
            return [false, 'Bidding is open only from 10 AM to 6 PM (India time). / बोली केवल सुबह 10 से शाम 6 बजे तक।'];
        }
        $valid = $kind === 'company' ? self::companyDates() : self::resumePeriods();
        if (!in_array($date, $valid, true)) {
            return [false, 'Bidding for this date is closed. / इस तारीख की बोली बंद है।'];
        }
        if ($amount < self::MIN_BID || $amount % self::STEP !== 0) {
            return [false, 'Bid must be ₹500 or more, in steps of ₹500 (plus GST). / बोली ₹500 या अधिक, ₹500 के गुणक में।'];
        }
        $db = Database::getInstance();
        $mine = $db->fetchOne(
            "SELECT * FROM top_bids WHERE kind = ? AND slot_date = ? AND category = ? AND user_id = ? AND status = 'active'",
            [$kind, $date, $category, $userId]
        );
        if ($mine) {
            if ($amount <= (int)$mine['amount']) {
                return [false, 'Your new bid must be higher than your current bid of ₹' . number_format((int)$mine['amount']) . '. / नई बोली पिछली से अधिक हो।'];
            }
            $db->execute('UPDATE top_bids SET amount = ?, updated_at = NOW() WHERE id = ?', [$amount, $mine['id']]);
            return [true, 'Bid raised to ₹' . number_format($amount) . ' + GST.'];
        }
        $db->execute(
            "INSERT INTO top_bids (kind, slot_date, category, user_id, owner_id, amount, status) VALUES (?, ?, ?, ?, ?, ?, 'active')",
            [$kind, $date, $category, $userId, $ownerId, $amount]
        );
        return [true, 'Bid placed: ₹' . number_format($amount) . ' + GST.'];
    }

    /** Active bids of one auction, highest first (earlier bid wins a tie). */
    public static function ranking(string $kind, string $date, string $category = ''): array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchAll(
            "SELECT * FROM top_bids WHERE kind = ? AND slot_date = ? AND category = ? AND status IN ('active','won','paid','waiting')
             ORDER BY amount DESC, created_at ASC, id ASC",
            [$kind, $date, $category]
        );
    }

    /** Smallest bid that would currently get a place in this auction. */
    public static function minToEnter(string $kind, string $date, string $category = ''): int
    {
        $slots = $kind === 'company' ? self::companyAuctionSlots($date) : self::RESUME_SLOTS;
        $bids = array_values(array_filter(self::ranking($kind, $date, $category), static fn($b) => $b['status'] === 'active'));
        return count($bids) < $slots ? self::MIN_BID : (int)$bids[$slots - 1]['amount'] + self::STEP;
    }

    public static function myBids(int $userId): array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchAll('SELECT * FROM top_bids WHERE user_id = ? ORDER BY slot_date DESC, id DESC LIMIT 50', [$userId]);
    }

    public static function find(int $id): ?array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchOne('SELECT * FROM top_bids WHERE id = ?', [$id]) ?: null;
    }

    // ------------------------------------------------------------------ closing (hourly cron)

    /** Close finished auctions, lapse unpaid wins and offer those places to the next bidders. */
    public static function runCron(): array
    {
        self::ensureSchema();
        $db = Database::getInstance();
        $log = [];
        // 1. close auctions whose 18:00 deadline passed
        $groups = $db->fetchAll("SELECT DISTINCT kind, slot_date, category FROM top_bids WHERE status = 'active'");
        foreach ($groups as $g) {
            if (self::closesAt((string)$g['slot_date']) > time()) {
                continue;
            }
            $slots = $g['kind'] === 'company' ? self::companyAuctionSlots((string)$g['slot_date']) : self::RESUME_SLOTS;
            $bids = $db->fetchAll(
                "SELECT * FROM top_bids WHERE kind = ? AND slot_date = ? AND category = ? AND status = 'active' ORDER BY amount DESC, created_at ASC, id ASC",
                [$g['kind'], $g['slot_date'], $g['category']]
            );
            foreach ($bids as $i => $b) {
                if ($i < $slots) {
                    // 18:00 + PAY_HOURS, but never less than 3 hours from now (cron may run late)
                    self::win($b, max(self::closesAt((string)$g['slot_date']) + self::PAY_HOURS * 3600, time() + 3 * 3600));
                } else {
                    $db->execute("UPDATE top_bids SET status = 'waiting', updated_at = NOW() WHERE id = ?", [$b['id']]);
                }
            }
            $log[] = "top_bids: closed {$g['kind']} {$g['slot_date']} {$g['category']} – " . min(count($bids), $slots) . ' winner(s)';
        }
        // 2. unpaid wins after the deadline lapse; the next waiting bid gets the place
        foreach ($db->fetchAll("SELECT * FROM top_bids WHERE status = 'won' AND pay_deadline < NOW()") as $b) {
            $db->execute("UPDATE top_bids SET status = 'lapsed', updated_at = NOW() WHERE id = ?", [$b['id']]);
            $lastDay = $b['kind'] === 'company' ? (string)$b['slot_date'] : self::periodEnd((string)$b['slot_date']);
            if (strtotime($lastDay . ' 23:59:59') <= time()) {
                continue;
            }
            $next = $db->fetchOne(
                "SELECT * FROM top_bids WHERE kind = ? AND slot_date = ? AND category = ? AND status = 'waiting' ORDER BY amount DESC, created_at ASC, id ASC LIMIT 1",
                [$b['kind'], $b['slot_date'], $b['category']]
            );
            if ($next) {
                self::win($next, time() + 3 * 3600);
                $log[] = "top_bids: #{$b['id']} lapsed, offered to #{$next['id']}";
            }
        }
        // 3. losing bids of finished periods
        $db->execute("UPDATE top_bids SET status = 'lost', updated_at = NOW() WHERE status = 'waiting' AND kind = 'company' AND slot_date < CURDATE()");
        $db->execute("UPDATE top_bids SET status = 'lost', updated_at = NOW() WHERE status = 'waiting' AND kind = 'resume' AND slot_date < CURDATE() - INTERVAL " . self::RESUME_DAYS . ' DAY');
        return $log;
    }

    private static function win(array $bid, int $deadline): void
    {
        Database::getInstance()->execute(
            "UPDATE top_bids SET status = 'won', pay_deadline = ?, updated_at = NOW() WHERE id = ?",
            [date('Y-m-d H:i:s', $deadline), $bid['id']]
        );
        self::mailWinner($bid, $deadline);
    }

    private static function mailWinner(array $bid, int $deadline): void
    {
        $u = Database::getInstance()->fetchOne('SELECT email FROM users WHERE id = ?', [$bid['user_id']]);
        $to = strtolower(trim((string)($u['email'] ?? '')));
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        $base = rtrim((string)($_ENV['APP_URL'] ?? 'https://jobsence.com'), '/');
        $what = $bid['kind'] === 'company'
            ? 'a Top Hiring Companies place on ' . date('d M Y', strtotime((string)$bid['slot_date']))
            : 'a Top Candidates place for ' . $bid['category'] . ' from ' . date('d M', strtotime((string)$bid['slot_date'])) . ' to ' . date('d M Y', strtotime(self::periodEnd((string)$bid['slot_date'])));
        $amount = '₹' . number_format((int)$bid['amount']) . ' + 18% GST = ₹' . number_format(self::withGst((float)$bid['amount']), 2);
        $html = "<div style='font-family:Arial,sans-serif;font-size:15px;line-height:1.6'><p>Congratulations – your bid won {$what}.</p>"
            . "<p>Amount: <b>{$amount}</b><br>Please pay before <b>" . date('d M Y, h:i A', $deadline) . "</b> (India time). If it is not paid in time, the place goes to the next bidder.</p>"
            . "<p><a href='{$base}/top-bid/pay/" . (int)$bid['id'] . "' style='background:#f05537;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:bold'>Pay now</a></p>"
            . '<p>Team Jobsence – gm@jobsence.com</p></div>';
        try {
            \App\Services\MailService::sendEmail($to, 'Your Jobsence bid won – pay to confirm your place', $html, (string)($_ENV['MAIL_FROM_ADDRESS'] ?? 'gm@jobsence.com'), 'Team Jobsence');
        } catch (\Throwable $e) {
            error_log('TopBidding mail: ' . $e->getMessage());
        }
    }

    /** Payment captured (RegistrationPayments hook). */
    public static function markPaid(int $bidId, int $regId): void
    {
        self::ensureSchema();
        Database::getInstance()->execute(
            "UPDATE top_bids SET status = 'paid', registration_id = ?, paid_at = NOW(), updated_at = NOW() WHERE id = ? AND status IN ('won','lapsed')",
            [$regId, $bidId]
        );
    }

    // ------------------------------------------------------------------ month place 1 and logo

    /** Paid monthly holder of place 1 on $date, or null. */
    public static function monthHolder(string $date): ?array
    {
        self::ensureSchema();
        return Database::getInstance()->fetchOne(
            "SELECT * FROM top_months WHERE status = 'paid' AND start_date <= ? AND end_date >= ? ORDER BY id LIMIT 1", [$date, $date]
        ) ?: null;
    }

    /** First start date from which place 1 is free for MONTH_DAYS days (auction for that day still open). */
    public static function nextMonthStart(): ?string
    {
        foreach (self::companyDates() as $d) {
            $end = date('Y-m-d', strtotime($d . ' +' . (self::MONTH_DAYS - 1) . ' day'));
            $clash = Database::getInstance()->fetchOne(
                "SELECT id FROM top_months WHERE status IN ('paid','pending') AND start_date <= ? AND end_date >= ?
                   AND (status = 'paid' OR created_at > NOW() - INTERVAL 2 HOUR)", [$end, $d]
            );
            if (!$clash) {
                return $d;
            }
        }
        return null;
    }

    /** Reserve the month for 2 hours while the company pays. Returns the top_months id. */
    public static function reserveMonth(int $userId, int $employerId, string $start): int
    {
        self::ensureSchema();
        $db = Database::getInstance();
        $db->execute(
            "INSERT INTO top_months (user_id, employer_id, start_date, end_date, status) VALUES (?, ?, ?, ?, 'pending')",
            [$userId, $employerId, $start, date('Y-m-d', strtotime($start . ' +' . (self::MONTH_DAYS - 1) . ' day'))]
        );
        return (int)$db->lastInsertId();
    }

    public static function monthPaid(int $monthId, int $regId): void
    {
        self::ensureSchema();
        $db = Database::getInstance();
        $m = $db->fetchOne("SELECT * FROM top_months WHERE id = ? AND status = 'pending'", [$monthId]);
        if (!$m) {
            return;
        }
        // Paid after the 2-hour hold and someone else booked meanwhile: move to the next free 30 days (no double place 1).
        $start = (string)$m['start_date'];
        while ($clash = $db->fetchOne(
            "SELECT end_date FROM top_months WHERE status = 'paid' AND id <> ? AND start_date <= ? AND end_date >= ? ORDER BY end_date DESC LIMIT 1",
            [$monthId, date('Y-m-d', strtotime($start . ' +' . (self::MONTH_DAYS - 1) . ' day')), $start]
        )) {
            $start = date('Y-m-d', strtotime($clash['end_date'] . ' +1 day'));
        }
        $db->execute(
            "UPDATE top_months SET status = 'paid', registration_id = ?, paid_at = NOW(), start_date = ?, end_date = ? WHERE id = ?",
            [$regId, $start, date('Y-m-d', strtotime($start . ' +' . (self::MONTH_DAYS - 1) . ' day')), $monthId]
        );
    }

    public static function hasLogo(int $employerId): bool
    {
        self::ensureSchema();
        return (bool)Database::getInstance()->fetchOne("SELECT id FROM top_logo_rights WHERE employer_id = ? AND status = 'active'", [$employerId]);
    }

    public static function logoPaid(int $employerId, int $regId): void
    {
        self::ensureSchema();
        Database::getInstance()->execute(
            "INSERT INTO top_logo_rights (employer_id, registration_id, status) VALUES (?, ?, 'active')
             ON DUPLICATE KEY UPDATE registration_id = VALUES(registration_id), status = 'active'", [$employerId, $regId]
        );
    }

    // ------------------------------------------------------------------ display

    /** Today's top companies: monthly holder first, then paid auction winners by bid. */
    public static function topCompaniesToday(): array
    {
        self::ensureSchema();
        $db = Database::getInstance();
        $today = date('Y-m-d');
        $ids = [];
        if ($m = self::monthHolder($today)) {
            $ids[] = (int)$m['employer_id'];
        }
        foreach ($db->fetchAll(
            "SELECT owner_id FROM top_bids WHERE kind = 'company' AND slot_date = ? AND status = 'paid' ORDER BY amount DESC, created_at ASC LIMIT " . self::COMPANY_SLOTS,
            [$today]
        ) as $r) {
            $ids[] = (int)$r['owner_id'];
        }
        $out = [];
        foreach (array_slice(array_values(array_unique($ids)), 0, self::COMPANY_SLOTS) as $eid) {
            $e = $db->fetchOne('SELECT id, company_name, company_slug, logo_url, city, industry FROM employers WHERE id = ?', [$eid]);
            if ($e) {
                $e['show_logo'] = !empty($e['logo_url']) && self::hasLogo($eid);
                $e['open_jobs'] = (int)($db->fetchOne("SELECT COUNT(*) n FROM jobs WHERE employer_id = ? AND status = 'published'", [$eid])['n'] ?? 0);
                $out[] = $e;
            }
        }
        return $out;
    }

    /**
     * Boosted candidates an employer sees on the dashboard (their job categories first), and count the view.
     * A boost stops after RESUME_MAX_VIEWS different employers have seen it.
     */
    public static function topCandidatesFor(int $employerId, array $categories, int $limit = 10): array
    {
        self::ensureSchema();
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            "SELECT b.*, (SELECT COUNT(*) FROM resume_boost_views v WHERE v.bid_id = b.id) AS views,
                    EXISTS(SELECT 1 FROM resume_boost_views v2 WHERE v2.bid_id = b.id AND v2.employer_id = ?) AS seen
             FROM top_bids b
             WHERE b.kind = 'resume' AND b.status = 'paid' AND b.slot_date <= CURDATE() AND b.slot_date > CURDATE() - INTERVAL " . self::RESUME_DAYS . " DAY
             ORDER BY b.amount DESC, b.created_at ASC LIMIT 200",
            [$employerId]
        );
        $catLower = array_map('mb_strtolower', $categories);
        usort($rows, static fn($a, $b) => (int)!in_array(mb_strtolower((string)$a['category']), $catLower, true) <=> (int)!in_array(mb_strtolower((string)$b['category']), $catLower, true)
            ?: (int)$b['amount'] <=> (int)$a['amount']);
        $out = [];
        foreach ($rows as $b) {
            if (count($out) >= $limit) {
                break;
            }
            if (!$b['seen'] && (int)$b['views'] >= self::RESUME_MAX_VIEWS) {
                continue;
            }
            $c = $db->fetchOne('SELECT id, full_name, professional_title, city, state, profile_picture FROM candidates WHERE id = ?', [$b['owner_id']]);
            if (!$c) {
                continue;
            }
            if (!$b['seen']) {
                $db->execute('INSERT IGNORE INTO resume_boost_views (bid_id, employer_id) VALUES (?, ?)', [$b['id'], $employerId]);
            }
            $out[] = $c + ['category' => $b['category']];
        }
        return $out;
    }

    public static function boostViews(int $bidId): int
    {
        self::ensureSchema();
        return (int)(Database::getInstance()->fetchOne('SELECT COUNT(*) n FROM resume_boost_views WHERE bid_id = ?', [$bidId])['n'] ?? 0);
    }

    public static function adminList(string $kind = '', string $status = ''): array
    {
        self::ensureSchema();
        $where = [];
        $p = [];
        if (in_array($kind, ['company', 'resume'], true)) {
            $where[] = 'b.kind = ?';
            $p[] = $kind;
        }
        if ($status !== '') {
            $where[] = 'b.status = ?';
            $p[] = $status;
        }
        return Database::getInstance()->fetchAll(
            'SELECT b.*, u.email, COALESCE(e.company_name, c.full_name) AS bidder_name FROM top_bids b
             LEFT JOIN users u ON u.id = b.user_id
             LEFT JOIN employers e ON b.kind = \'company\' AND e.id = b.owner_id
             LEFT JOIN candidates c ON b.kind = \'resume\' AND c.id = b.owner_id ' . ($where ? 'WHERE ' . implode(' AND ', $where) : '') .
            ' ORDER BY b.slot_date DESC, b.amount DESC LIMIT 500', $p
        );
    }

    public static function ensureSchema(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        $pdo = Database::getInstance()->getConnection();
        $pdo->exec("CREATE TABLE IF NOT EXISTS top_bids (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            kind ENUM('company','resume') NOT NULL,
            slot_date DATE NOT NULL,
            category VARCHAR(150) NOT NULL DEFAULT '',
            user_id BIGINT UNSIGNED NOT NULL,
            owner_id BIGINT UNSIGNED NOT NULL,
            amount INT UNSIGNED NOT NULL,
            status ENUM('active','won','waiting','paid','lapsed','lost','cancelled') NOT NULL DEFAULT 'active',
            pay_deadline DATETIME NULL,
            registration_id BIGINT UNSIGNED NULL,
            paid_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_tb_auction (kind, slot_date, category, status, amount),
            KEY idx_tb_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $pdo->exec("CREATE TABLE IF NOT EXISTS top_months (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            employer_id BIGINT UNSIGNED NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            status ENUM('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
            registration_id BIGINT UNSIGNED NULL,
            paid_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_tm_dates (status, start_date, end_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $pdo->exec("CREATE TABLE IF NOT EXISTS top_logo_rights (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            employer_id BIGINT UNSIGNED NOT NULL,
            registration_id BIGINT UNSIGNED NULL,
            status ENUM('active','hidden') NOT NULL DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_tlr_employer (employer_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $pdo->exec("CREATE TABLE IF NOT EXISTS resume_boost_views (
            bid_id BIGINT UNSIGNED NOT NULL,
            employer_id BIGINT UNSIGNED NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (bid_id, employer_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}
