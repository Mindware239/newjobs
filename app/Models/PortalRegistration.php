<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Jobsence ₹155 registrations: skill development, internship, full-time, part-time,
 * work-from-home candidates, and skill providers / mentors / institutes.
 */
class PortalRegistration
{
    public const FEE = 155.00;          // including GST
    public const GST_RATE = 0.18;
    public const COURSE_FEE = 12000.00; // skill development course fee after selection (+ GST)

    public const TYPES = ['skill', 'internship', 'fulltime', 'parttime', 'wfh', 'provider', 'ngo', 'jobpass', 'nearpro', 'nearseek', 'hospitality', 'healthcare', 'hospital', 'hirer', 'internpro', 'mentorplan', 'internplan', 'jobpro', 'jobplan', 'intljob', 'intlcountry', 'senior', 'seniorhire', 'jobpost', 'honormentor', 'honorlearn', 'jobpostpaid', 'jobcontact', 'topslot', 'toplogo', 'resumeboost'];
    public const STATUSES = ['new', 'under_scrutiny', 'selected', 'rejected', 'completed', 'expired'];

    /** Skill-development registrations are valid for this many months after payment. */
    public const SKILL_VALIDITY_MONTHS = 3;

    /** Share of candidate (skill development) fees passed on to skill development agencies / mentors. */
    public const AGENCY_SHARE = 0.20;

    /** First reminder this many minutes after an unpaid submission, then every N days, at most MAX. */
    public const FIRST_REMINDER_MINUTES = 30;
    public const REMINDER_INTERVAL_DAYS = 2;
    public const MAX_REMINDERS = 5;

    /**
     * Registration validity (months from payment) per form type. Types not listed never expire.
     * Skill development and part-time aspirants: 3 months, not more.
     */
    public const VALIDITY = [
        // Skill development / internship seekers have no end date: valid until matched with a mentor / provider.
        'parttime' => [3, 'MONTH'], 'jobpass' => [1, 'MONTH'],
        'mentorplan' => [30, 'DAY'],    // mentor / institute plan: 30 days, 3 profiles a day
        'internplan' => [10, 'DAY'],    // internship provider plan: 10 days, 3 a day, max 20
        'jobplan' => [30, 'DAY'],       // hiring plan for companies: 30 days, 3 profiles a day
        'nearpro' => [3, 'MONTH'], 'nearseek' => [3, 'MONTH'], 'hospitality' => [3, 'MONTH'],
        'healthcare' => [1, 'MONTH'],   // hospital staff / doctors: 1 month
        'hospital' => [15, 'DAY'],      // hospitals & clinics hiring: 15 days
        'hirer' => [2, 'DAY'],          // part-time talent pass: 2 days
        'senior' => [12, 'MONTH'],      // senior citizens (59+): once a year
        'seniorhire' => [7, 'DAY'],     // a senior-citizen job offered by an organisation: 7 days
        'honormentor' => [6, 'MONTH'],  // honorary mentor platform fee: every 6 months (twice a year)
    ];

    /** [n, 'MONTH'|'DAY'] or null when the registration never expires. */
    public static function validityInterval(string $type): ?array
    {
        return self::VALIDITY[$type] ?? null;
    }

    /** Months of validity for month-based types (null otherwise). */
    public static function validityMonths(string $type): ?int
    {
        $v = self::VALIDITY[$type] ?? null;
        return $v && $v[1] === 'MONTH' ? $v[0] : null;
    }

    /** ['3 महीने', '3 months'] / ['15 दिन', '15 days'] */
    public static function validityLabel(string $type): ?array
    {
        $v = self::VALIDITY[$type] ?? null;
        if (!$v) {
            return null;
        }
        return $v[1] === 'DAY'
            ? [$v[0] . ' दिन', $v[0] . ' day' . ($v[0] > 1 ? 's' : '')]
            : [$v[0] . ' महीने', $v[0] . ' month' . ($v[0] > 1 ? 's' : '')];
    }

    /** Base amount (excluding GST) of a GST-inclusive total. */
    public static function baseAmount(?float $total = null): float
    {
        return round(($total ?? self::FEE) / (1 + self::GST_RATE), 2);
    }

    public static function gstAmount(?float $total = null): float
    {
        $total ??= self::FEE;
        return round($total - self::baseAmount($total), 2);
    }

    /** @param float|null $fee total incl. GST for this form (defaults to the ₹155 fee) */
    public static function create(array $data, string $prefix, ?float $fee = null): ?array
    {
        self::ensureSchema();
        $db = Database::getInstance();
        $fee ??= self::FEE;

        $data['token'] = bin2hex(random_bytes(16));
        $data['currency'] = ($data['currency'] ?? 'INR') === 'USD' ? 'USD' : 'INR';
        // USD fees (clients outside India) are export of services: no Indian GST added.
        $data['fee_base'] = $data['currency'] === 'USD' ? $fee : self::baseAmount($fee);
        $data['gst_amount'] = $data['currency'] === 'USD' ? 0.0 : self::gstAmount($fee);
        $data['total_amount'] = $fee;

        $columns = array_keys($data);
        $sql = 'INSERT INTO portal_registrations (' . implode(', ', $columns) . ') VALUES ('
            . implode(', ', array_fill(0, count($columns), '?')) . ')';

        if (!$db->execute($sql, array_values($data))) {
            return null;
        }

        $id = (int)$db->lastInsertId();
        $db->execute('UPDATE portal_registrations SET reg_no = ? WHERE id = ?', [
            $prefix . date('y') . str_pad((string)$id, 6, '0', STR_PAD_LEFT), $id,
        ]);

        return self::find($id);
    }

    public static function find(int $id): ?array
    {
        self::ensureSchema();
        return self::hydrate(Database::getInstance()->fetchOne('SELECT * FROM portal_registrations WHERE id = ?', [$id]));
    }

    public static function findByToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
            return null;
        }
        self::ensureSchema();
        return self::hydrate(Database::getInstance()->fetchOne('SELECT * FROM portal_registrations WHERE token = ?', [$token]));
    }

    public static function findByOrderId(string $orderId): ?array
    {
        if ($orderId === '') {
            return null;
        }
        self::ensureSchema();
        return self::hydrate(Database::getInstance()->fetchOne('SELECT * FROM portal_registrations WHERE razorpay_order_id = ? LIMIT 1', [$orderId]));
    }

    public static function paidExists(string $type, string $mobile): ?string
    {
        self::ensureSchema();
        // An expired (3-month) skill registration does not block registering again.
        $row = Database::getInstance()->fetchOne(
            "SELECT reg_no FROM portal_registrations WHERE type = ? AND mobile = ? AND payment_status = 'paid'
               AND status <> 'expired' AND (valid_until IS NULL OR valid_until > NOW()) LIMIT 1",
            [$type, $mobile]
        );
        return $row['reg_no'] ?? null;
    }

    /** Paid, still-valid registration of this type for an email (Jobs Pass: one pass per email). */
    public static function paidExistsByEmail(string $type, string $email): ?string
    {
        self::ensureSchema();
        $row = Database::getInstance()->fetchOne(
            "SELECT reg_no FROM portal_registrations WHERE type = ? AND email = ? AND payment_status = 'paid'
               AND status <> 'expired' AND (valid_until IS NULL OR valid_until > NOW()) LIMIT 1",
            [$type, strtolower(trim($email))]
        );
        return $row['reg_no'] ?? null;
    }

    /** Latest paid, valid registration of a type for an email (e.g. the active Jobs Pass). */
    public static function activeByEmail(string $type, string $email): ?array
    {
        if ($email === '') {
            return null;
        }
        self::ensureSchema();
        return self::hydrate(Database::getInstance()->fetchOne(
            "SELECT * FROM portal_registrations WHERE type = ? AND email = ? AND payment_status = 'paid'
               AND status <> 'expired' AND valid_until IS NOT NULL AND valid_until > NOW()
             ORDER BY valid_until DESC LIMIT 1",
            [$type, strtolower(trim($email))]
        ));
    }

    public static function setOrderId(int $id, string $orderId): void
    {
        Database::getInstance()->execute('UPDATE portal_registrations SET razorpay_order_id = ? WHERE id = ?', [$orderId, $id]);
    }

    /** Returns true only for the call that actually flipped the row to paid. */
    public static function markPaid(int $id, string $paymentId, string $signature = ''): bool
    {
        $pdo = Database::getInstance()->getConnection();
        if (!$pdo) {
            return false;
        }
        $stmt = $pdo->prepare(
            "UPDATE portal_registrations
             SET payment_status = 'paid', razorpay_payment_id = ?, razorpay_signature = ?, paid_at = NOW()
             WHERE id = ? AND payment_status <> 'paid'"
        );
        $stmt->execute([$paymentId, $signature, $id]);
        $flipped = $stmt->rowCount() === 1;
        if ($flipped) {
            // Skill / part-time registrations are valid for 3 months from payment – never longer.
            foreach (self::VALIDITY as $type => [$n, $unit]) {
                Database::getInstance()->execute(
                    "UPDATE portal_registrations SET valid_until = paid_at + INTERVAL " . (int)$n . ($unit === 'DAY' ? ' DAY' : ' MONTH') . " WHERE id = ? AND type = ?",
                    [$id, $type]
                );
            }
            // Plans sold by days (hiring plans: 1, 3 or 30 days) carry their own length.
            $reg = self::find($id);
            if (!empty($reg['details']['plan_days'])) {
                Database::getInstance()->execute(
                    'UPDATE portal_registrations SET valid_until = paid_at + INTERVAL ' . (int)$reg['details']['plan_days'] . ' DAY WHERE id = ?',
                    [$id]
                );
                $reg = self::find($id);
            }
            // A renewal paid before the old term ended continues from the old expiry date.
            $prev = !empty($reg['details']['renews_reg_id']) ? self::find((int)$reg['details']['renews_reg_id']) : null;
            $iv = !empty($reg['details']['plan_days']) ? [(int)$reg['details']['plan_days'], 'DAY'] : self::validityInterval((string)($reg['type'] ?? ''));
            if ($prev && $iv && !empty($prev['valid_until']) && strtotime((string)$prev['valid_until']) > strtotime((string)$reg['paid_at'])) {
                Database::getInstance()->execute(
                    'UPDATE portal_registrations SET valid_until = ? + INTERVAL ' . (int)$iv[0] . ($iv[1] === 'DAY' ? ' DAY' : ' MONTH') . ' WHERE id = ?',
                    [$prev['valid_until'], $id]
                );
            }
        }
        return $flipped;
    }

    public static function markFailed(int $id): void
    {
        Database::getInstance()->execute(
            "UPDATE portal_registrations SET payment_status = 'failed' WHERE id = ? AND payment_status = 'pending'",
            [$id]
        );
    }

    public static function updateAdmin(int $id, string $status, string $notes): void
    {
        if (in_array($status, self::STATUSES, true)) {
            Database::getInstance()->execute(
                'UPDATE portal_registrations SET status = ?, admin_notes = ? WHERE id = ?',
                [$status, $notes, $id]
            );
            if ($status === 'selected') {
                Database::getInstance()->execute(
                    "UPDATE portal_registrations SET details = JSON_SET(COALESCE(details, '{}'), '$.verified', 1) WHERE id = ?",
                    [$id]
                );
            }
        }
    }

    /** Types that can be renewed (skill development is 3 months only – never renewed). */
    public const RENEWABLE = ['parttime', 'jobpass', 'nearpro', 'nearseek', 'hospitality', 'healthcare', 'hospital', 'hirer', 'senior', 'seniorhire', 'honormentor'];

    /** Days before expiry at which the 3 renewal reminders go out. */
    public const EXPIRY_REMINDER_DAYS = [10, 5, 1];

    /** Renewing after the term has ended costs 10% extra. */
    public const LATE_RENEWAL_SURCHARGE = 0.10;

    /** Fee (incl. GST) to renew: the normal fee, plus 10% when the old term has already ended. */
    public static function renewalFee(array $old, float $fee): float
    {
        $late = !empty($old['valid_until']) && strtotime((string)$old['valid_until']) <= time();
        return $late ? round($fee * (1 + self::LATE_RENEWAL_SURCHARGE)) : $fee;
    }

    public static function reprice(int $id, float $fee): void
    {
        Database::getInstance()->execute(
            "UPDATE portal_registrations SET total_amount = ?, fee_base = ?, gst_amount = ?, razorpay_order_id = NULL WHERE id = ? AND payment_status <> 'paid'",
            [$fee, self::baseAmount($fee), self::gstAmount($fee), $id]
        );
    }

    /** Unpaid renewal already opened for this registration, if any. */
    public static function openRenewal(int $id): ?array
    {
        return self::hydrate(Database::getInstance()->fetchOne(
            "SELECT * FROM portal_registrations WHERE payment_status <> 'paid'
               AND JSON_UNQUOTE(JSON_EXTRACT(details, '$.renews_reg_id')) = ? ORDER BY id DESC LIMIT 1",
            [(string)$id]
        ));
    }

    /** Copy a paid registration into a new unpaid one for the next term. */
    public static function createRenewal(array $old, string $prefix, float $fee): ?array
    {
        $details = $old['details'];
        $details['renews_reg_id'] = (int)$old['id'];
        unset($details['expiry_reminders']);
        $data = [];
        foreach (['type', 'full_name', 'dob', 'gender', 'mobile', 'whatsapp', 'email', 'aadhaar_last4', 'gstin', 'district', 'city', 'state', 'pincode',
                     'qualification', 'categories', 'preferred_location', 'resume_path', 'video_path', 'email_verified_at', 'ui_language'] as $col) {
            if (array_key_exists($col, $old) && $old[$col] !== null) {
                $data[$col] = $old[$col];
            }
        }
        $data['details'] = json_encode($details, JSON_UNESCAPED_UNICODE);
        $data['declaration_accepted'] = 1;
        // Verified providers stay verified when they renew.
        $data['status'] = !empty($details['verified']) || $old['status'] === 'selected' ? 'selected' : 'new';
        return self::create($data, $prefix, $fee);
    }

    /**
     * Paid, renewable registrations ending within 10 days that are due their next reminder
     * (10, 5 and 1 day before expiry – at most 3) and have not been renewed yet.
     */
    public static function dueForExpiryReminder(int $limit = 200): array
    {
        self::ensureSchema();
        $types = "'" . implode("','", self::RENEWABLE) . "'";
        $rows = array_map([self::class, 'hydrate'], Database::getInstance()->fetchAll(
            "SELECT r.* FROM portal_registrations r
             WHERE r.type IN ($types) AND r.payment_status = 'paid' AND r.status NOT IN ('rejected')
               AND r.email IS NOT NULL AND r.email <> ''
               AND r.valid_until > NOW() AND r.valid_until <= NOW() + INTERVAL " . (int)max(self::EXPIRY_REMINDER_DAYS) . " DAY
               AND NOT EXISTS (SELECT 1 FROM portal_registrations n WHERE n.payment_status = 'paid'
                               AND JSON_UNQUOTE(JSON_EXTRACT(n.details, '$.renews_reg_id')) = CAST(r.id AS CHAR))
             ORDER BY r.valid_until ASC LIMIT " . (int)$limit
        ));
        $due = [];
        foreach ($rows as $r) {
            $sent = (int)($r['details']['expiry_reminders'] ?? 0);
            $daysLeft = (int)ceil((strtotime((string)$r['valid_until']) - time()) / 86400);
            if ($sent < count(self::EXPIRY_REMINDER_DAYS) && $daysLeft <= self::EXPIRY_REMINDER_DAYS[$sent]) {
                $r['days_left'] = max(0, $daysLeft);
                $due[] = $r;
            }
        }
        return $due;
    }

    public static function markExpiryReminded(int $id): void
    {
        Database::getInstance()->execute(
            "UPDATE portal_registrations SET details = JSON_SET(COALESCE(details, '{}'), '$.expiry_reminders',
                COALESCE(JSON_EXTRACT(details, '$.expiry_reminders'), 0) + 1) WHERE id = ?",
            [$id]
        );
    }

    /** Cron: mark skill registrations whose 3-month validity is over as expired. Returns rows changed. */
    public static function expireValidity(): int
    {
        self::ensureSchema();
        $pdo = Database::getInstance()->getConnection();
        if (!$pdo) {
            return 0;
        }
        return (int)$pdo->exec("UPDATE portal_registrations SET status = 'expired'
            WHERE valid_until IS NOT NULL AND valid_until < NOW() AND status NOT IN ('expired', 'completed', 'rejected')");
    }

    /** ₹ per USD used when a USD price is paid in rupees (admin setting usd_inr_rate). */
    public static function usdInrRate(): float
    {
        try {
            $rate = (float)\App\Models\SystemSetting::get('usd_inr_rate', 88);
        } catch (\Throwable $e) {
            $rate = 88.0;
        }
        return $rate >= 40 && $rate <= 250 ? $rate : 88.0;
    }

    /**
     * Unpaid registration with a dual price → switch the currency it will be paid in.
     * details.price_inr / price_usd (both set, e.g. per-country unlock) or details.usd_price (₹ = USD × rate, GST incl.).
     */
    public static function switchCurrency(array $reg, string $currency): bool
    {
        $d = $reg['details'] ?? [];
        $currency = $currency === 'USD' ? 'USD' : 'INR';
        if ($reg['payment_status'] === 'paid' || ($reg['currency'] ?? 'INR') === $currency || (empty($d['usd_price']) && empty($d['price_usd']))) {
            return false;
        }
        if ($currency === 'USD') {
            $total = (float)($d['price_usd'] ?? $d['usd_price']);
            [$base, $gst] = [$total, 0.0];
        } else {
            $total = !empty($d['price_inr']) ? (float)$d['price_inr'] : round((float)$d['usd_price'] * self::usdInrRate());
            [$base, $gst] = [self::baseAmount($total), self::gstAmount($total)];
        }
        Database::getInstance()->execute(
            'UPDATE portal_registrations SET currency = ?, total_amount = ?, fee_base = ?, gst_amount = ?, razorpay_order_id = NULL WHERE id = ? AND payment_status <> \'paid\'',
            [$currency, $total, $base, $gst, (int)$reg['id']]
        );
        return true;
    }

    /** Both ways a dual-priced registration can be paid: ['USD' => 10.0, 'INR' => 1180.0] (empty if single-currency). */
    public static function priceChoices(array $reg): array
    {
        $d = $reg['details'] ?? [];
        if (empty($d['usd_price']) && empty($d['price_usd'])) {
            return [];
        }
        return [
            'USD' => (float)($d['price_usd'] ?? $d['usd_price']),
            'INR' => !empty($d['price_inr']) ? (float)$d['price_inr'] : round((float)$d['usd_price'] * self::usdInrRate()),
        ];
    }

    /** "₹1,829" or "USD 12" for a registration's total. */
    public static function money(array $reg, ?float $amount = null): string
    {
        $amount ??= (float)($reg['total_amount'] ?? 0);
        return ($reg['currency'] ?? 'INR') === 'USD'
            ? 'USD ' . rtrim(rtrim(number_format($amount, 2), '0'), '.')
            : '₹' . number_format($amount, fmod($amount, 1.0) > 0.0049 ? 2 : 0);
    }

    public static function isValid(array $reg): bool
    {
        return ($reg['payment_status'] ?? '') === 'paid' && ($reg['status'] ?? '') !== 'expired'
            && (empty($reg['valid_until']) || strtotime((string)$reg['valid_until']) > time());
    }

    /**
     * Unpaid registrations due a reminder: first one FIRST_REMINDER_MINUTES after submission,
     * then every REMINDER_INTERVAL_DAYS days, up to MAX_REMINDERS.
     */
    public static function dueForReminder(int $limit = 200): array
    {
        self::ensureSchema();
        $first = (int)self::FIRST_REMINDER_MINUTES;
        $days = (int)self::REMINDER_INTERVAL_DAYS;

        return array_map([self::class, 'hydrate'], Database::getInstance()->fetchAll(
            "SELECT * FROM portal_registrations
             WHERE payment_status <> 'paid'
               AND email IS NOT NULL AND email <> ''
               AND reminder_count < ?
               AND (
                    (reminder_count = 0 AND created_at <= NOW() - INTERVAL {$first} MINUTE)
                 OR (reminder_count > 0 AND last_reminder_at <= NOW() - INTERVAL {$days} DAY)
               )
             ORDER BY id ASC
             LIMIT " . (int)$limit,
            [self::MAX_REMINDERS]
        ));
    }

    public static function markReminded(int $id): void
    {
        Database::getInstance()->execute(
            'UPDATE portal_registrations SET reminder_count = reminder_count + 1, last_reminder_at = NOW() WHERE id = ?',
            [$id]
        );
    }

    /** @return array{rows: array, total: int} */
    public static function search(array $filters, int $page = 1, int $perPage = 25): array
    {
        self::ensureSchema();
        [$where, $params] = self::buildWhere($filters);
        $db = Database::getInstance();

        $total = (int)($db->fetchOne("SELECT COUNT(*) AS c FROM portal_registrations $where", $params)['c'] ?? 0);
        $rows = $db->fetchAll(
            "SELECT * FROM portal_registrations $where ORDER BY id DESC LIMIT " . (int)$perPage . ' OFFSET ' . max(0, ($page - 1) * $perPage),
            $params
        );

        return ['rows' => array_map([self::class, 'hydrate'], $rows), 'total' => $total];
    }

    public static function allForExport(array $filters): array
    {
        self::ensureSchema();
        [$where, $params] = self::buildWhere($filters);
        return array_map([self::class, 'hydrate'], Database::getInstance()->fetchAll(
            "SELECT * FROM portal_registrations $where ORDER BY id DESC",
            $params
        ));
    }

    /** Per-type counts and revenue. */
    public static function stats(): array
    {
        self::ensureSchema();
        $rows = Database::getInstance()->fetchAll(
            "SELECT type,
                    COUNT(*) AS total,
                    SUM(payment_status = 'paid') AS paid,
                    SUM(payment_status <> 'paid') AS unpaid,
                    COALESCE(SUM(CASE WHEN payment_status = 'paid' AND currency = 'INR' THEN total_amount END), 0) AS revenue,
                    COALESCE(SUM(CASE WHEN payment_status = 'paid' AND currency = 'USD' THEN total_amount END), 0) AS revenue_usd
             FROM portal_registrations GROUP BY type"
        );
        $out = [];
        foreach ($rows as $r) {
            $out[$r['type']] = array_map(static fn($v) => $v ?? 0, $r);
        }
        return $out;
    }

    private static function buildWhere(array $filters): array
    {
        $clauses = [];
        $params = [];

        if (in_array($filters['type'] ?? '', self::TYPES, true)) {
            $clauses[] = 'type = ?';
            $params[] = $filters['type'];
        }
        if (in_array($filters['payment_status'] ?? '', ['pending', 'paid', 'failed'], true)) {
            $clauses[] = 'payment_status = ?';
            $params[] = $filters['payment_status'];
        }
        if (in_array($filters['status'] ?? '', self::STATUSES, true)) {
            $clauses[] = 'status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['state'])) {
            $clauses[] = 'state = ?';
            $params[] = $filters['state'];
        }
        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $clauses[] = '(full_name LIKE ? OR mobile LIKE ? OR email LIKE ? OR reg_no LIKE ? OR district LIKE ? OR city LIKE ? OR pincode LIKE ? OR categories LIKE ?)';
            array_push($params, ...array_fill(0, 8, '%' . $q . '%'));
        }

        return [$clauses ? 'WHERE ' . implode(' AND ', $clauses) : '', $params];
    }

    private static function hydrate(?array $row): ?array
    {
        if ($row === null) {
            return null;
        }
        $row['details'] = json_decode((string)($row['details'] ?? ''), true) ?: [];
        return $row;
    }

    /** "enum('a','b')" → ['a', 'b'] */
    private static function enumValues(string $columnType): array
    {
        preg_match_all("/'([^']*)'/", $columnType, $m);
        return $m[1];
    }

    public static function ensureSchema(): void
    {
        static $done = false;
        if ($done) {
            return;
        }

        $pdo = Database::getInstance()->getConnection();
        if (!$pdo) {
            return;
        }

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS portal_registrations (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                reg_no VARCHAR(20) NULL,
                token CHAR(32) NOT NULL,
                type ENUM('" . implode("','", self::TYPES) . "') NOT NULL,
                full_name VARCHAR(150) NOT NULL,
                dob DATE NULL,
                gender ENUM('male','female','other') NULL,
                mobile VARCHAR(15) NOT NULL,
                whatsapp VARCHAR(15) NULL,
                email VARCHAR(190) NULL,
                aadhaar_last4 CHAR(4) NULL,
                gstin VARCHAR(15) NULL,
                district VARCHAR(120) NULL,
                city VARCHAR(120) NULL,
                state VARCHAR(80) NULL,
                pincode CHAR(6) NULL,
                qualification VARCHAR(40) NULL,
                categories TEXT NULL,
                preferred_location VARCHAR(190) NULL,
                resume_path VARCHAR(255) NULL,
                video_path VARCHAR(255) NULL,
                email_verified_at DATETIME NULL,
                details JSON NULL,
                ui_language VARCHAR(10) NULL,
                declaration_accepted TINYINT(1) NOT NULL DEFAULT 0,
                fee_base DECIMAL(10,2) NOT NULL,
                gst_amount DECIMAL(10,2) NOT NULL,
                total_amount DECIMAL(10,2) NOT NULL,
                currency CHAR(3) NOT NULL DEFAULT 'INR',
                payment_status ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
                razorpay_order_id VARCHAR(64) NULL,
                razorpay_payment_id VARCHAR(64) NULL,
                razorpay_signature VARCHAR(190) NULL,
                paid_at DATETIME NULL,
                valid_until DATETIME NULL,
                status ENUM('new','under_scrutiny','selected','rejected','completed','expired') NOT NULL DEFAULT 'new',
                admin_notes TEXT NULL,
                reminder_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
                last_reminder_at DATETIME NULL,
                ip_address VARCHAR(45) NULL,
                user_agent VARCHAR(255) NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_portal_reg_token (token),
                KEY idx_portal_reg_no (reg_no),
                KEY idx_portal_reg_type_pay (type, payment_status),
                KEY idx_portal_reg_mobile (mobile),
                KEY idx_portal_reg_email (email),
                KEY idx_portal_reg_gstin (gstin),
                KEY idx_portal_reg_state (state),
                KEY idx_portal_reg_order (razorpay_order_id),
                KEY idx_portal_reg_reminder (payment_status, reminder_count)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        // Existing installs: widen the type enum when new form types are added.
        try {
            $col = Database::getInstance()->fetchOne(
                "SELECT COLUMN_TYPE AS t FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'portal_registrations' AND COLUMN_NAME = 'type'"
            );
            if ($col && array_diff(self::TYPES, self::enumValues((string)$col['t']))) {
                $pdo->exec("ALTER TABLE portal_registrations MODIFY type ENUM('" . implode("','", self::TYPES) . "') NOT NULL");
            }
            $st = Database::getInstance()->fetchOne(
                "SELECT COLUMN_TYPE AS t FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'portal_registrations' AND COLUMN_NAME = 'status'"
            );
            if ($st && strpos((string)$st['t'], "'expired'") === false) {
                $pdo->exec("ALTER TABLE portal_registrations MODIFY status ENUM('" . implode("','", self::STATUSES) . "') NOT NULL DEFAULT 'new'");
            }
            $cur = Database::getInstance()->fetchOne(
                "SELECT 1 AS ok FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'portal_registrations' AND COLUMN_NAME = 'currency'"
            );
            if (!$cur) {
                $pdo->exec("ALTER TABLE portal_registrations ADD COLUMN currency CHAR(3) NOT NULL DEFAULT 'INR' AFTER total_amount");
            }
            $vu = Database::getInstance()->fetchOne(
                "SELECT 1 AS ok FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'portal_registrations' AND COLUMN_NAME = 'valid_until'"
            );
            if (!$vu) {
                $pdo->exec("ALTER TABLE portal_registrations ADD COLUMN valid_until DATETIME NULL AFTER paid_at, ADD KEY idx_portal_reg_valid (valid_until)");
                $pdo->exec("UPDATE portal_registrations SET valid_until = paid_at + INTERVAL " . (int)self::SKILL_VALIDITY_MONTHS . " MONTH WHERE type = 'skill' AND paid_at IS NOT NULL");
            }
        } catch (\Throwable $e) {
            error_log('PortalRegistration::ensureSchema enum upgrade: ' . $e->getMessage());
        }

        $done = true;
    }
}
