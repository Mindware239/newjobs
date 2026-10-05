<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Core\Database;
use App\Models\PortalRegistration;

/**
 * "One company = one entity" rule for providers (companies offering jobs/internships,
 * training institutes and mentors): if ANY of Email, Mobile or GST matches an existing
 * provider, it is the same company and a new registration is refused.
 *
 * Existing providers = employer accounts + paid skill-provider/mentor registrations.
 */
class ProviderIdentity
{
    public const MESSAGES = [
        'email' => ['यह Email पहले से रजिस्टर्ड है', 'This Email is already registered with another company'],
        'mobile' => ['यह Mobile नंबर पहले से रजिस्टर्ड है', 'This Mobile number is already registered'],
        'gstin' => ['यह GST नंबर पहले से रजिस्टर्ड है', 'This GST number is already registered'],
    ];

    public const GST_PATTERN = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][A-Z0-9]{3}$/';

    /** @return array<string, array{0:string,1:string}> field => [hi, en] for every identifier already in use */
    public static function conflicts(?string $email, ?string $mobile, ?string $gstin): array
    {
        $out = [];
        if ($email !== null && $email !== '' && self::emailExists($email)) {
            $out['email'] = self::MESSAGES['email'];
        }
        if ($mobile !== null && $mobile !== '' && self::mobileExists($mobile)) {
            $out['mobile'] = self::MESSAGES['mobile'];
        }
        if ($gstin !== null && $gstin !== '' && self::gstExists($gstin)) {
            $out['gstin'] = self::MESSAGES['gstin'];
        }
        return $out;
    }

    public static function normalizeGst(?string $gstin): string
    {
        return strtoupper(preg_replace('/\s+/', '', (string)$gstin));
    }

    public static function emailExists(string $email): bool
    {
        $email = strtolower(trim($email));
        return self::any([
            ["SELECT 1 FROM users WHERE LOWER(email) = ? AND role = 'employer' LIMIT 1", [$email]],
            ["SELECT 1 FROM portal_registrations WHERE type IN ('provider', 'ngo', 'internpro', 'jobpro') AND payment_status = 'paid' AND LOWER(email) = ? LIMIT 1", [$email]],
        ]);
    }

    public static function mobileExists(string $mobile): bool
    {
        $last10 = substr(preg_replace('/\D/', '', $mobile), -10);
        if (strlen($last10) !== 10) {
            return false;
        }
        return self::any([
            ["SELECT 1 FROM users WHERE role = 'employer'
              AND RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(phone, ''), ' ', ''), '-', ''), '+', ''), '(', ''), 10) = ? LIMIT 1", [$last10]],
            ["SELECT 1 FROM portal_registrations WHERE type IN ('provider', 'ngo', 'internpro', 'jobpro') AND payment_status = 'paid' AND mobile = ? LIMIT 1", [$last10]],
        ]);
    }

    public static function gstExists(string $gstin): bool
    {
        $gstin = self::normalizeGst($gstin);
        return self::any([
            ["SELECT 1 FROM employers WHERE UPPER(REPLACE(tax_id, ' ', '')) = ? LIMIT 1", [$gstin]],
            ["SELECT 1 FROM portal_registrations WHERE type IN ('provider', 'ngo', 'internpro', 'jobpro') AND payment_status = 'paid' AND gstin = ? LIMIT 1", [$gstin]],
        ]);
    }

    private static function any(array $queries): bool
    {
        PortalRegistration::ensureSchema();
        $db = Database::getInstance();
        foreach ($queries as [$sql, $params]) {
            if ($db->fetchOne($sql, $params)) {
                return true;
            }
        }
        return false;
    }
}
