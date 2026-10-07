<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Core\Database;
use App\Models\PortalRegistration;

/**
 * Near Me local jobs (user, 2026-10-07): shops / kirana / small businesses (nearjobpro, ₹590 for 6 months) and
 * job seekers living nearby (nearjobseek, ₹295 for 3 months). Matched near home exactly like senior jobs
 * (SeniorMatch::match – browser location within the seeker's radius, else same PIN, else same locality + city),
 * plus at least one shared work category and a compatible work type. Both sides see each other's mobile number.
 */
class NearJobMatch
{
    private const DEFAULT_KM = 3;

    /** Job seekers near this business, nearest first. */
    public static function seekersFor(array $giver): array
    {
        return self::matches(self::active('nearjobseek'), $giver, true);
    }

    /** Businesses hiring near this job seeker, nearest first. */
    public static function giversFor(array $seeker): array
    {
        return self::matches(self::active('nearjobpro'), $seeker, false);
    }

    private static function matches(array $others, array $me, bool $meIsGiver): array
    {
        $out = [];
        foreach ($others as $o) {
            $seeker = $meIsGiver ? $o : $me;
            $giver = $meIsGiver ? $me : $o;
            $m = SeniorMatch::match($seeker, $giver, (float)($seeker['details']['max_distance'] ?? self::DEFAULT_KM));
            if ($m !== null && self::fits($seeker, $giver)) {
                $out[] = $o + ['match' => $m];
            }
        }
        usort($out, static fn($a, $b) => $a['match']['rank'] <=> $b['match']['rank']);
        return array_slice($out, 0, 100);
    }

    /** Same work type (or either side unspecified) and at least one shared category (when both gave categories). */
    private static function fits(array $seeker, array $giver): bool
    {
        $a = (string)($seeker['details']['work_type'] ?? '');
        $b = (string)($giver['details']['work_type'] ?? '');
        if ($a !== '' && $b !== '' && $a !== $b) {
            return false;
        }
        $split = static fn($s) => array_filter(array_map(static fn($x) => mb_strtolower(trim($x)), explode(',', (string)$s)));
        $ca = $split($seeker['categories'] ?? '');
        $cb = $split($giver['categories'] ?? '');
        return !$ca || !$cb || array_intersect($ca, $cb);
    }

    private static function active(string $type): array
    {
        $rows = Database::getInstance()->fetchAll(
            "SELECT id FROM portal_registrations WHERE type = ? AND payment_status = 'paid' AND status NOT IN ('rejected','expired')
               AND (valid_until IS NULL OR valid_until > NOW()) ORDER BY id DESC LIMIT 3000",
            [$type]
        );
        return array_values(array_filter(array_map(static fn($r) => PortalRegistration::find((int)$r['id']), $rows)));
    }
}
