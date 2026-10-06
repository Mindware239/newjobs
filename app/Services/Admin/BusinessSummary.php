<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Core\Database;
use App\Models\PortalRegistration;
use App\Services\Registration\FormRegistry;

/**
 * Admin "Registrations & payments by use case": how many registered for each use case (job seeker,
 * employer, skill seeker, skill provider / mentor, internship …) and how much money came in against
 * each part, for a chosen period. Sources: portal_registrations (all /apply forms, plans, passes),
 * users (accounts by role) and subscription_payments (employer subscription plans).
 */
class BusinessSummary
{
    /** Use-case groups → registration types. Types not listed here still appear under "Other". */
    public const GROUPS = [
        'seekers' => ['title' => 'Job seekers (employees)', 'types' => ['fulltime', 'parttime', 'wfh', 'intljob', 'hospitality', 'healthcare']],
        'skill' => ['title' => 'Skill seekers (skill requirement)', 'types' => ['skill']],
        'internship' => ['title' => 'Internship seekers', 'types' => ['internship']],
        'mentors' => ['title' => 'Skill providers / mentors / institutes', 'types' => ['provider', 'mentorplan']],
        'interns' => ['title' => 'Internship providers', 'types' => ['internpro', 'internplan']],
        'employers' => ['title' => 'Employers / hiring companies', 'types' => ['jobpro', 'jobplan', 'hospital', 'hirer']],
        'nearme' => ['title' => 'Near Me (service providers & users)', 'types' => ['nearpro', 'nearseek']],
        'passes' => ['title' => 'Jobs in India Pass & Jobs Abroad unlocks', 'types' => ['jobpass', 'intlcountry']],
        'ngo' => ['title' => 'NGOs / social organisations', 'types' => ['ngo']],
    ];

    public const PERIODS = ['today' => 'Today', '7d' => 'Last 7 days', '30d' => 'Last 30 days', 'month' => 'This month', 'all' => 'All time', 'custom' => 'Custom'];

    /** [from, to] as 'Y-m-d H:i:s' (to exclusive), or [null, null] for all time. */
    public static function range(string $period, string $from = '', string $to = ''): array
    {
        $today = date('Y-m-d');
        return match ($period) {
            'today' => [$today . ' 00:00:00', date('Y-m-d', strtotime('+1 day')) . ' 00:00:00'],
            '7d' => [date('Y-m-d', strtotime('-6 days')) . ' 00:00:00', date('Y-m-d', strtotime('+1 day')) . ' 00:00:00'],
            '30d' => [date('Y-m-d', strtotime('-29 days')) . ' 00:00:00', date('Y-m-d', strtotime('+1 day')) . ' 00:00:00'],
            'month' => [date('Y-m-01') . ' 00:00:00', date('Y-m-d', strtotime('first day of next month')) . ' 00:00:00'],
            'custom' => [
                preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) ? $from . ' 00:00:00' : null,
                preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) ? date('Y-m-d', strtotime($to . ' +1 day')) . ' 00:00:00' : null,
            ],
            default => [null, null],
        };
    }

    /**
     * Registrations per type in the period: registered (created), paid / completed, pending payment,
     * ₹ received incl. GST, GST part, USD received (paid in the period).
     */
    public static function registrations(?string $from, ?string $to): array
    {
        PortalRegistration::stats(); // makes sure the table exists
        [$cw, $cp] = self::between('created_at', $from, $to);
        [$pw, $pp] = self::between('paid_at', $from, $to);
        $rows = Database::getInstance()->fetchAll(
            "SELECT type,
                    SUM($cw) AS registered,
                    SUM($cw AND payment_status = 'paid') AS completed,
                    SUM($cw AND payment_status <> 'paid') AS pending,
                    COALESCE(SUM(CASE WHEN payment_status = 'paid' AND total_amount > 0 AND $pw THEN 1 END), 0) AS payments,
                    COALESCE(SUM(CASE WHEN payment_status = 'paid' AND currency <> 'USD' AND $pw THEN total_amount END), 0) AS inr,
                    COALESCE(SUM(CASE WHEN payment_status = 'paid' AND currency <> 'USD' AND $pw THEN gst_amount END), 0) AS gst,
                    COALESCE(SUM(CASE WHEN payment_status = 'paid' AND currency = 'USD' AND $pw THEN total_amount END), 0) AS usd
             FROM portal_registrations GROUP BY type",
            array_merge($cp, $cp, $cp, $pp, $pp, $pp, $pp)
        );
        $byType = [];
        foreach ($rows as $r) {
            $byType[$r['type']] = array_map(static fn($v) => is_numeric($v) ? $v + 0 : $v, $r);
        }
        return $byType;
    }

    /** Groups with their type rows and subtotals; unknown types under "other". */
    public static function grouped(array $byType): array
    {
        $labels = [];
        foreach (FormRegistry::all() as $slug => $f) {
            $labels[$f['type']] = ['title' => $f['title'][1], 'slug' => $slug, 'fee' => $f['fee'] ?? null];
        }
        $groups = self::GROUPS;
        $seen = array_merge(...array_column($groups, 'types'));
        $other = array_values(array_diff(array_keys($byType), $seen));
        if ($other) {
            $groups['other'] = ['title' => 'Other', 'types' => $other];
        }
        $out = [];
        foreach ($groups as $key => $g) {
            $rows = [];
            $sum = ['registered' => 0, 'completed' => 0, 'pending' => 0, 'payments' => 0, 'inr' => 0.0, 'gst' => 0.0, 'usd' => 0.0];
            foreach ($g['types'] as $type) {
                $r = $byType[$type] ?? ['registered' => 0, 'completed' => 0, 'pending' => 0, 'payments' => 0, 'inr' => 0, 'gst' => 0, 'usd' => 0];
                $r['type'] = $type;
                $r['label'] = $labels[$type]['title'] ?? ucfirst($type);
                $r['slug'] = $labels[$type]['slug'] ?? null;
                $rows[] = $r;
                foreach ($sum as $k => $v) {
                    $sum[$k] = $v + ($r[$k] ?? 0);
                }
            }
            $out[$key] = ['title' => $g['title'], 'rows' => $rows, 'sum' => $sum];
        }
        return $out;
    }

    /** New accounts by role (users table) in the period. */
    public static function accounts(?string $from, ?string $to): array
    {
        [$w, $p] = self::between('created_at', $from, $to);
        try {
            $rows = Database::getInstance()->fetchAll("SELECT role, COUNT(*) AS n FROM users WHERE $w GROUP BY role ORDER BY n DESC", $p);
        } catch (\Throwable $e) {
            return [];
        }
        return array_column($rows, 'n', 'role');
    }

    /** Employer subscription payments (completed) by plan in the period. */
    public static function subscriptions(?string $from, ?string $to): array
    {
        [$w, $p] = self::between('COALESCE(sp.paid_at, sp.created_at)', $from, $to);
        try {
            return Database::getInstance()->fetchAll(
                "SELECT COALESCE(pl.name, 'Plan') AS plan, sp.currency, COUNT(*) AS payments, COALESCE(SUM(sp.amount), 0) AS amount
                 FROM subscription_payments sp
                 LEFT JOIN employer_subscriptions es ON es.id = sp.subscription_id
                 LEFT JOIN subscription_plans pl ON pl.id = es.plan_id
                 WHERE sp.status IN ('completed', 'success', 'paid') AND $w
                 GROUP BY pl.name, sp.currency ORDER BY amount DESC",
                $p
            );
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Daily ₹ received from registrations (last 30 days) for the trend strip. */
    public static function daily(int $days = 30): array
    {
        $rows = Database::getInstance()->fetchAll(
            "SELECT DATE(paid_at) AS d, COALESCE(SUM(CASE WHEN currency <> 'USD' THEN total_amount END), 0) AS inr, COUNT(*) AS n
             FROM portal_registrations WHERE payment_status = 'paid' AND total_amount > 0 AND paid_at >= ? GROUP BY DATE(paid_at)",
            [date('Y-m-d', strtotime('-' . ($days - 1) . ' days'))]
        );
        $by = array_column($rows, null, 'd');
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $out[] = ['d' => $d, 'inr' => (float)($by[$d]['inr'] ?? 0), 'n' => (int)($by[$d]['n'] ?? 0)];
        }
        return $out;
    }

    /** SQL condition (and params) "column within [from, to)" – always true for all time. */
    private static function between(string $col, ?string $from, ?string $to): array
    {
        $w = [];
        $p = [];
        if ($from !== null) {
            $w[] = "$col >= ?";
            $p[] = $from;
        }
        if ($to !== null) {
            $w[] = "$col < ?";
            $p[] = $to;
        }
        return [$w ? '(' . implode(' AND ', $w) . ')' : '1=1', $p];
    }
}
