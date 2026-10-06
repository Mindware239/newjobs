<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Services\JobBoard\StateJobBoard;
use App\Services\Registration\Mentoring;

/**
 * Monthly Jobsence newsletter for paid members, matched to their side:
 *  - job seekers (job registrations, Jobs Pass) → new openings in their state + all India;
 *  - skill / internship seekers                → how many new mentors / internship providers joined, top skills offered;
 *  - mentors, internship providers, hiring companies with an active plan → how many new seekers want their field.
 * Runs from the cron task "monthly_newsletter" (hourly): from the 1st of each month it sends BATCH mails per run until
 * every member has this month's issue. Every mail has a one-click unsubscribe link.
 */
class NewsletterService
{
    public const BATCH = 40;

    /** Paid registration types → audience. */
    private const AUDIENCE = [
        'fulltime' => 'jobs', 'parttime' => 'jobs', 'wfh' => 'jobs', 'intljob' => 'jobs', 'hospitality' => 'jobs', 'healthcare' => 'jobs', 'jobpass' => 'jobs', 'senior' => 'jobs',
        'skill' => 'skill', 'internship' => 'internship',
        'mentorplan' => 'mentor', 'internplan' => 'internpro', 'jobplan' => 'hirer', 'hirer' => 'hirer', 'hospital' => 'hirer',
    ];

    /** Send the next batch of this month's issue. Returns a status line. */
    public static function runMonthly(): string
    {
        self::ensureSchema();
        $period = date('Y-m');
        $db = Database::getInstance();
        $types = "'" . implode("','", array_keys(self::AUDIENCE)) . "'";
        $rows = $db->fetchAll(
            "SELECT r.id, r.type, r.email, r.full_name, r.state, r.categories FROM portal_registrations r
             WHERE r.type IN ($types) AND r.payment_status = 'paid' AND r.total_amount > 0
               AND (r.valid_until IS NULL OR r.valid_until > NOW()) AND r.email IS NOT NULL AND r.email <> ''
               AND NOT EXISTS (SELECT 1 FROM newsletter_sends s WHERE s.email = r.email AND s.period = ?)
               AND NOT EXISTS (SELECT 1 FROM newsletter_optouts o WHERE o.email = r.email)
             ORDER BY r.id DESC LIMIT " . (self::BATCH * 3),
            [$period]
        );
        $sent = $failed = 0;
        $done = [];
        foreach ($rows as $r) {
            $email = strtolower(trim((string)$r['email']));
            if (isset($done[$email]) || !filter_var($email, FILTER_VALIDATE_EMAIL) || str_ends_with($email, '@example.test')) {
                continue; // one issue per email per month (the newest paid registration decides the content)
            }
            $done[$email] = true;
            [$subject, $html] = self::issue(self::AUDIENCE[$r['type']], $r);
            $ok = MailService::sendEmail($email, $subject, $html, (string)($_ENV['SKILL_MAIL_FROM'] ?? 'gm@jobsence.com'), 'Team Jobsence');
            $db->execute('INSERT IGNORE INTO newsletter_sends (email, period, audience, reg_id, ok) VALUES (?, ?, ?, ?, ?)',
                [$email, $period, self::AUDIENCE[$r['type']], (int)$r['id'], $ok ? 1 : 0]);
            $ok ? $sent++ : $failed++;
            if ($sent + $failed >= self::BATCH) {
                break;
            }
        }
        return "newsletter $period: $sent sent, $failed failed" . (count($rows) < self::BATCH ? ' (all members done for this month)' : '');
    }

    /** [subject, html] for one member. */
    public static function issue(string $audience, array $r): array
    {
        $base = rtrim((string)($_ENV['APP_URL'] ?? 'https://jobsence.com'), '/');
        $month = date('F Y');
        $name = htmlspecialchars((string)($r['full_name'] ?: 'Friend'), ENT_QUOTES, 'UTF-8');
        $since = date('Y-m-d', strtotime('-31 days'));
        $body = '';
        switch ($audience) {
            case 'jobs':
                $state = StateJobBoard::stateName((string)$r['state']);
                $fresh = array_filter(StateJobBoard::all(), static fn($j) => (string)$j['posted'] >= $since && ($state === null || $j['state'] === $state));
                usort($fresh, static fn($a, $b) => strcmp((string)$b['posted'], (string)$a['posted']));
                $subject = 'नई नौकरियाँ – ' . ($state ?? 'India') . ' | New jobs for you – ' . $month;
                $body .= '<p><b>' . count($fresh) . '</b> new openings were posted in <b>' . htmlspecialchars($state ?? 'India') . '</b> in the last month.</p>' . self::jobList(array_slice($fresh, 0, 10), $base);
                $body .= '<p><a href="' . $base . '/jobs-by-state' . ($state ? '/' . StateJobBoard::slug($state) : '') . '">See all jobs in ' . htmlspecialchars($state ?? 'India') . ' →</a></p>';
                break;
            case 'skill':
            case 'internship':
                $kind = $audience === 'skill' ? 'skill' : 'internship';
                [$count, $top] = self::newRegistrations([Mentoring::KINDS[$kind]['provider']], $since);
                $subject = ($kind === 'skill' ? 'नए मेंटर' : 'नए इंटर्नशिप प्रदाता') . ' | ' . ($kind === 'skill' ? 'New mentors' : 'New internship providers') . ' – ' . $month;
                $body .= '<p><b>' . $count . '</b> new ' . ($kind === 'skill' ? 'mentors and institutes' : 'internship providers') . ' joined Jobsence last month.</p>' . self::topList($top, 'Fields offered');
                $body .= '<p><a href="' . $base . Mentoring::KINDS[$kind]['providers_page'] . '">See them →</a> · <a href="' . $base . '/mentoring">My dashboard →</a></p>';
                break;
            default: // mentor, internpro, hirer
                $kind = ['mentor' => 'skill', 'internpro' => 'internship', 'hirer' => 'job'][$audience] ?? 'skill';
                [$count, $top] = self::newRegistrations(Mentoring::KINDS[$kind]['seekers'], $since);
                $subject = 'नए उम्मीदवार | New ' . strtolower(Mentoring::KINDS[$kind]['seekers_label'][1]) . ' – ' . $month;
                $body .= '<p><b>' . $count . '</b> new ' . strtolower(Mentoring::KINDS[$kind]['seekers_label'][1]) . ' registered last month.</p>' . self::topList($top, 'Most wanted');
                $body .= '<p><a href="' . $base . Mentoring::KINDS[$kind]['seekers_page'] . '">See who is looking →</a> · <a href="' . $base . '/mentoring">My dashboard →</a></p>';
        }
        $unsub = $base . '/newsletter/unsubscribe?e=' . rawurlencode((string)$r['email']) . '&t=' . self::token((string)$r['email']);
        $html = '<div style="font-family:Arial,sans-serif;max-width:620px;margin:auto;color:#111827">'
            . '<p style="font-size:18px;font-weight:bold;color:#f05537;margin:0">Jobsence – ' . $month . '</p>'
            . '<p>नमस्ते / Hello ' . $name . ',</p>' . $body
            . '<p style="color:#4b5563;font-size:13px">Jobsence never asks for money for a job. Apply for Govt jobs only on official websites.</p>'
            . '<p style="color:#9ca3af;font-size:12px">You get this monthly update as a paid Jobsence member. <a href="' . $unsub . '">Unsubscribe</a> · gm@jobsence.com</p></div>';
        return [$subject, $html];
    }

    public static function token(string $email): string
    {
        return substr(hash_hmac('sha256', strtolower(trim($email)), self::secret()), 0, 24);
    }

    public static function unsubscribe(string $email, string $token): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !hash_equals(self::token($email), $token)) {
            return false;
        }
        self::ensureSchema();
        Database::getInstance()->execute('INSERT IGNORE INTO newsletter_optouts (email) VALUES (?)', [strtolower(trim($email))]);
        return true;
    }

    private static function jobList(array $jobs, string $base): string
    {
        if (!$jobs) {
            return '';
        }
        $li = '';
        foreach ($jobs as $j) {
            $li .= '<li style="margin-bottom:6px"><a href="' . $base . htmlspecialchars($j['url'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($j['title'], ENT_QUOTES, 'UTF-8') . '</a>'
                . ' – ' . htmlspecialchars($j['company'], ENT_QUOTES, 'UTF-8') . ($j['city'] !== '' ? ', ' . htmlspecialchars($j['city'], ENT_QUOTES, 'UTF-8') : '') . '</li>';
        }
        return '<ul>' . $li . '</ul>';
    }

    private static function topList(array $top, string $label): string
    {
        return $top ? '<p><b>' . $label . ':</b> ' . htmlspecialchars(implode(' · ', $top), ENT_QUOTES, 'UTF-8') . '</p>' : '';
    }

    /** [count, top 8 categories] of paid registrations of these types since $since. */
    private static function newRegistrations(array $types, string $since): array
    {
        $in = "'" . implode("','", array_map(static fn($t) => preg_replace('/[^a-z]/', '', $t), $types)) . "'";
        $rows = Database::getInstance()->fetchAll(
            "SELECT categories FROM portal_registrations WHERE type IN ($in) AND payment_status = 'paid' AND created_at >= ?", [$since]
        );
        $freq = [];
        foreach ($rows as $row) {
            foreach (array_filter(array_map('trim', explode(',', (string)$row['categories']))) as $c) {
                $freq[$c] = ($freq[$c] ?? 0) + 1;
            }
        }
        arsort($freq);
        return [count($rows), array_slice(array_keys($freq), 0, 8)];
    }

    private static function secret(): string
    {
        foreach (['APP_KEY', 'JWT_SECRET', 'APP_SECRET'] as $k) {
            if (!empty($_ENV[$k])) {
                return (string)$_ENV[$k];
            }
        }
        $file = dirname(__DIR__, 2) . '/storage/cache/newsletter.key';
        if (!is_file($file)) {
            @mkdir(dirname($file), 0775, true);
            @file_put_contents($file, bin2hex(random_bytes(32)));
        }
        return (string)@file_get_contents($file);
    }

    public static function ensureSchema(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $pdo = Database::getInstance()->getConnection();
        if ($pdo) {
            $pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_sends (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                email VARCHAR(190) NOT NULL,
                period CHAR(7) NOT NULL,
                audience VARCHAR(20) NOT NULL,
                reg_id BIGINT UNSIGNED NULL,
                ok TINYINT(1) NOT NULL DEFAULT 1,
                sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_ns_email_period (email, period)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $pdo->exec("CREATE TABLE IF NOT EXISTS newsletter_optouts (
                email VARCHAR(190) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $done = true;
    }
}
