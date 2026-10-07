<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Callback URL for social logins (Google, Facebook, LinkedIn).
 *
 * The callback must come back to the site the visitor is on, or the login session is lost. The old
 * GOOGLE_REDIRECT_URI pointed at www.mindwareinfotech.com, so Google sent everyone to another domain.
 * Rule: use the current host when it is the APP_URL host, its www / non-www twin or localhost; otherwise
 * APP_URL. A {PROVIDER}_REDIRECT_URI from .env is honoured only if it points at one of those hosts.
 * Every URL produced here must be listed in the provider's console (see docs in config/social.php).
 */
class OAuthRedirect
{
    public static function uri(string $provider): string
    {
        $path = '/auth/' . $provider . '/callback';
        $env = trim((string)($_ENV[strtoupper($provider) . '_REDIRECT_URI'] ?? ''));
        if ($env !== '' && self::allowedHost((string)parse_url($env, PHP_URL_HOST))) {
            $envHost = strtolower((string)parse_url($env, PHP_URL_HOST));
            $cur = self::currentHost();
            // An .env value for the live domain must not hijack a localhost (or www / non-www) visit.
            if ($cur === '' || $envHost === $cur) {
                return $env;
            }
        }
        $cur = self::currentHost();
        if ($cur !== '' && self::allowedHost($cur)) {
            $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
            $port = (string)parse_url('http://' . (string)($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_PORT);
            return ($https ? 'https' : 'http') . '://' . $cur . ($port !== '' ? ':' . $port : '') . $path;
        }
        return rtrim((string)($_ENV['APP_URL'] ?? 'https://jobsence.com'), '/') . $path;
    }

    private static function currentHost(): string
    {
        return strtolower((string)parse_url('http://' . (string)($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST));
    }

    private static function allowedHost(string $host): bool
    {
        $host = strtolower($host);
        $app = strtolower((string)parse_url((string)($_ENV['APP_URL'] ?? 'https://jobsence.com'), PHP_URL_HOST));
        $bare = preg_replace('/^www\./', '', $app);
        return $host !== '' && in_array($host, [$app, $bare, 'www.' . $bare, 'jobsence.com', 'www.jobsence.com', 'localhost', '127.0.0.1'], true);
    }
}
