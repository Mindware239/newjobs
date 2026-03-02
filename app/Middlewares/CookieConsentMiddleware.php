<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Services\CookieService;

class CookieConsentMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response): void
    {
        CookieService::ensureSchema();
        $now = time();
        $secret = $_ENV['COOKIE_SIGN_SECRET'] ?? ($_ENV['APP_KEY'] ?? 'mw-secret');
        $anon = $_COOKIE['anon_id'] ?? '';
        $sig = $_COOKIE['anon_sig'] ?? '';
        $expectedSig = $anon ? hash_hmac('sha256', $anon, $secret) : '';
        $needNew = (!$anon) || (!$sig) || ($expectedSig !== $sig);
        if ($needNew) {
            $anon = bin2hex(random_bytes(16));
            $sig = hash_hmac('sha256', $anon, $secret);
            if (!headers_sent()) {
                setcookie('anon_id', $anon, $now + 31536000, '/', '', false, true);
                setcookie('anon_sig', $sig, $now + 31536000, '/', '', false, true);
            }
        }
        $db = \App\Core\Database::getInstance();
        $ver = $db->fetchOne("SELECT id, version_number, requires_reconsent FROM cookie_policy_versions WHERE is_active = 1 ORDER BY effective_from DESC LIMIT 1");
        if ($ver && (int)($ver['requires_reconsent'] ?? 0) === 1) {
            if (!headers_sent()) {
                setcookie('requires_reconsent', '1', $now + 86400, '/', '', false, false);
            }
        }
        $gs = $db->fetchOne("SELECT geo_based_strict_mode, block_scripts_until_consent FROM global_cookie_settings LIMIT 1");
        $strict = (int)($gs['geo_based_strict_mode'] ?? 0) === 1;
        $block = (int)($gs['block_scripts_until_consent'] ?? 1) === 1;
        if ($strict) {
            $cc = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? ($_SERVER['GEOIP_COUNTRY_CODE'] ?? '');
            $eu = ['AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE','IS','LI','NO'];
            if ($cc && in_array($cc, $eu, true) && $block) {
                if (!headers_sent()) {
                    setcookie('consent_strict', '1', $now + 86400, '/', '', false, false);
                }
            }
        }
    }
}
