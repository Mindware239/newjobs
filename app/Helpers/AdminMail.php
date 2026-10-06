<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Where admin / owner notifications go: always gm@jobsence.com (the Hostinger mailbox – read it in any
 * mail app over IMAP imap.hostinger.com), plus any extra address still set in ADMIN_MAIL / OWNER_MAIL
 * (comma-separated allowed) – remove those from .env to receive everything only at gm@jobsence.com.
 */
final class AdminMail
{
    public const PRIMARY = 'gm@jobsence.com';

    /** @return string[] unique, valid addresses, PRIMARY first */
    public static function list(): array
    {
        $all = [self::PRIMARY];
        foreach (['ADMIN_MAIL', 'OWNER_MAIL'] as $key) {
            $value = (string)($_ENV[$key] ?? getenv($key) ?: '');
            foreach (explode(',', $value) as $addr) {
                $addr = strtolower(trim($addr));
                if ($addr !== '' && filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                    $all[] = $addr;
                }
            }
        }
        return array_values(array_unique($all));
    }

    /** Comma-separated list for MailService::sendEmail(). */
    public static function to(): string
    {
        return implode(',', self::list());
    }
}
