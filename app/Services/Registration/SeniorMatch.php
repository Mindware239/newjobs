<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Core\Database;
use App\Models\PortalRegistration;

/**
 * Senior citizen jobs are always "near me" (user, 2026-10-06): a senior is matched only with work close to home.
 *  - Distance from the browser location (details.geo) of both sides, within the senior's chosen radius
 *    (details.max_distance, km); without a location on either side: same PIN code, else same city / locality.
 *  - Organisations (seniorhire, ₹500 per job, 7 days) see matching seniors with phone; seniors see nearby jobs.
 * Emergency contacts and full addresses are never shown here.
 */
class SeniorMatch
{
    public const RADII = [1, 3, 5, 10];
    private const DEFAULT_KM = 3;

    /** Seniors near this organisation's job, nearest first. */
    public static function seniorsFor(array $org): array
    {
        $rows = self::active('senior');
        $out = [];
        foreach ($rows as $s) {
            $m = self::match($s, $org, (float)($s['details']['max_distance'] ?? self::DEFAULT_KM));
            if ($m !== null && self::workFits($s, $org)) {
                $out[] = $s + ['match' => $m];
            }
        }
        usort($out, static fn($a, $b) => $a['match']['rank'] <=> $b['match']['rank']);
        return array_slice($out, 0, 100);
    }

    /** Senior jobs near this senior, nearest first. */
    public static function jobsFor(array $senior): array
    {
        $out = [];
        foreach (self::active('seniorhire') as $o) {
            $m = self::match($senior, $o, (float)($senior['details']['max_distance'] ?? self::DEFAULT_KM));
            if ($m !== null && self::workFits($senior, $o)) {
                $out[] = $o + ['match' => $m];
            }
        }
        usort($out, static fn($a, $b) => $a['match']['rank'] <=> $b['match']['rank']);
        return array_slice($out, 0, 50);
    }

    /** ['label' => '1.2 km' | 'same PIN' | 'same area', 'rank' => sortable] or null when too far. */
    public static function match(array $senior, array $org, float $radiusKm): ?array
    {
        $a = self::geo($senior);
        $b = self::geo($org);
        if ($a && $b) {
            $km = self::km($a[0], $a[1], $b[0], $b[1]);
            return $km <= $radiusKm ? ['label' => $km . ' km', 'rank' => $km] : null;
        }
        if (!empty($senior['pincode']) && $senior['pincode'] === ($org['pincode'] ?? null)) {
            return ['label' => 'same PIN ' . $senior['pincode'], 'rank' => 50];
        }
        $same = static fn($x, $y) => $x !== '' && mb_strtolower(trim((string)$x)) === mb_strtolower(trim((string)$y));
        // Locality / colony is kept in details (not a column).
        if ($same($senior['details']['village'] ?? '', $org['details']['village'] ?? '') && $same($senior['city'] ?? '', $org['city'] ?? '')) {
            return ['label' => 'same area', 'rank' => 60];
        }
        return null; // never far – senior jobs are near-me only
    }

    /** Community-service seniors match community-service needs; paid work matches full / part time. */
    private static function workFits(array $senior, array $org): bool
    {
        $s = (string)($senior['details']['work_type'] ?? '');
        $o = (string)($org['details']['work_type'] ?? '');
        return $s === '' || $o === '' || ($s === 'community') === ($o === 'community');
    }

    /** Paid / completed registrations of a type that are still valid. */
    private static function active(string $type): array
    {
        $rows = Database::getInstance()->fetchAll(
            "SELECT id FROM portal_registrations WHERE type = ? AND payment_status = 'paid' AND status NOT IN ('rejected','expired')
               AND (valid_until IS NULL OR valid_until > NOW()) ORDER BY id DESC LIMIT 2000",
            [$type]
        );
        $out = [];
        foreach ($rows as $r) {
            if ($reg = PortalRegistration::find((int)$r['id'])) {
                $out[] = $reg;
            }
        }
        return $out;
    }

    private static function geo(array $reg): ?array
    {
        return preg_match('/^(-?[\d.]+),(-?[\d.]+)$/', (string)($reg['details']['geo'] ?? ''), $g) ? [(float)$g[1], (float)$g[2]] : null;
    }

    private static function km(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return round(6371.0 * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
    }
}
