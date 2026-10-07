<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Models\PortalRegistration;

/**
 * Paid "contact passes": a paid, still-valid registration of a payer type unlocks the contact
 * details (mobile, address) of registrations of the matching listed type during its validity.
 *
 *   nearseek  (₹295 = ₹250 + GST, 3 months) → Near Me service providers (nearpro), matched by PIN code
 *   hospital  (per role)       → healthcare job seekers (healthcare) of the roles paid for
 *   hirer     (₹5,900, 2 days) → part-time / gig workers (parttime), keyword search
 *
 * The pass is carried by its secret registration token in an HttpOnly cookie, set from the
 * link on the receipt page and in the confirmation email (/pass/{token}).
 */
class ContactPass
{
    /** payer type => [search page, cookie name] */
    public const PAGES = [
        'nearseek' => '/near-me',
        'hospital' => '/hospital-talent',
        'hirer' => '/talent-search',
    ];

    public static function cookieName(string $type): string
    {
        return 'jsx_pass_' . $type;
    }

    /** Active pass of this type for the current visitor, or null. */
    public static function active(string $type): ?array
    {
        $token = (string)($_SESSION['contact_pass'][$type] ?? $_COOKIE[self::cookieName($type)] ?? '');
        if ($token === '') {
            return null;
        }
        $reg = PortalRegistration::findByToken($token);
        if (!$reg || $reg['type'] !== $type || !PortalRegistration::isValid($reg) || empty($reg['valid_until'])) {
            return null;
        }
        return $reg;
    }

    /** Remember a paid pass for this browser until it expires. Returns the search page or null. */
    public static function remember(array $reg): ?string
    {
        $type = (string)$reg['type'];
        if (!isset(self::PAGES[$type]) || !PortalRegistration::isValid($reg) || empty($reg['valid_until'])) {
            return null;
        }
        $_SESSION['contact_pass'][$type] = $reg['token'];
        if (!headers_sent()) {
            setcookie(self::cookieName($type), (string)$reg['token'], [
                'expires' => (int)strtotime((string)$reg['valid_until']),
                'path' => '/',
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        return self::PAGES[$type];
    }

    public static function accessUrl(array $reg): ?string
    {
        return isset(self::PAGES[$reg['type'] ?? '']) ? '/pass/' . $reg['token'] : null;
    }

    /** Only the first name and the initial of the surname, for locked results. */
    public static function maskName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $first = $parts[0] ?? '';
        $last = count($parts) > 1 ? mb_substr((string)end($parts), 0, 1) . '.' : '';
        return trim($first . ' ' . $last);
    }

    public static function maskMobile(string $mobile): string
    {
        return strlen($mobile) >= 10 ? substr($mobile, 0, 2) . 'XXXXXX' . substr($mobile, -2) : 'XXXXXXXXXX';
    }
}
