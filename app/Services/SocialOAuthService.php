<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\OAuthRedirect;

/**
 * Facebook Login and "Sign In with LinkedIn using OpenID Connect" (plain OAuth 2.0 over curl).
 * Returns the same user shape as GoogleOAuthService: id, email, name, picture, verified_email.
 * (Instagram is not offered: Meta ended Instagram login for personal accounts in Dec 2024 and the
 * business-account login returns no email.)
 */
class SocialOAuthService
{
    public const PROVIDERS = ['facebook', 'linkedin'];

    private array $cfg;

    public function __construct(private string $provider)
    {
        if (!in_array($provider, self::PROVIDERS, true)) {
            throw new \InvalidArgumentException('Unknown provider');
        }
        $this->cfg = (require dirname(__DIR__, 2) . '/config/social.php')[$provider];
    }

    /** Is the provider configured (both keys set)? Buttons are shown only then. */
    public static function enabled(string $provider): bool
    {
        static $all = null;
        $all ??= require dirname(__DIR__, 2) . '/config/social.php';
        return !empty($all[$provider]['client_id']) && !empty($all[$provider]['client_secret']);
    }

    public function authUrl(string $state): string
    {
        $q = ['client_id' => $this->cfg['client_id'], 'redirect_uri' => OAuthRedirect::uri($this->provider), 'state' => $state, 'response_type' => 'code'];
        if ($this->provider === 'facebook') {
            return 'https://www.facebook.com/' . $this->cfg['graph'] . '/dialog/oauth?' . http_build_query($q + ['scope' => 'email,public_profile']);
        }
        return 'https://www.linkedin.com/oauth/v2/authorization?' . http_build_query($q + ['scope' => 'openid profile email'], '', '&', PHP_QUERY_RFC3986);
    }

    /** Exchange the code and fetch the profile. Throws on any failure. */
    public function userFromCode(string $code): array
    {
        $redirect = OAuthRedirect::uri($this->provider);
        if ($this->provider === 'facebook') {
            $graph = 'https://graph.facebook.com/' . $this->cfg['graph'];
            $tok = self::http('GET', $graph . '/oauth/access_token?' . http_build_query([
                'client_id' => $this->cfg['client_id'], 'client_secret' => $this->cfg['client_secret'], 'redirect_uri' => $redirect, 'code' => $code,
            ]));
            $access = (string)($tok['access_token'] ?? '');
            if ($access === '') {
                throw new \RuntimeException('Facebook token error: ' . json_encode($tok['error'] ?? $tok));
            }
            $me = self::http('GET', $graph . '/me?' . http_build_query([
                'fields' => 'id,name,email,picture.type(large)', 'access_token' => $access,
                'appsecret_proof' => hash_hmac('sha256', $access, (string)$this->cfg['client_secret']),
            ]));
            return [
                'id' => (string)($me['id'] ?? ''),
                'email' => strtolower(trim((string)($me['email'] ?? ''))),
                'name' => (string)($me['name'] ?? ''),
                'picture' => (string)($me['picture']['data']['url'] ?? ''),
                'verified_email' => !empty($me['email']), // Facebook only returns confirmed email addresses
            ];
        }
        $tok = self::http('POST', 'https://www.linkedin.com/oauth/v2/accessToken', [
            'grant_type' => 'authorization_code', 'code' => $code, 'redirect_uri' => $redirect,
            'client_id' => $this->cfg['client_id'], 'client_secret' => $this->cfg['client_secret'],
        ]);
        $access = (string)($tok['access_token'] ?? '');
        if ($access === '') {
            throw new \RuntimeException('LinkedIn token error: ' . json_encode($tok));
        }
        $me = self::http('GET', 'https://api.linkedin.com/v2/userinfo', null, ['Authorization: Bearer ' . $access]);
        return [
            'id' => (string)($me['sub'] ?? ''),
            'email' => strtolower(trim((string)($me['email'] ?? ''))),
            'name' => (string)($me['name'] ?? trim(($me['given_name'] ?? '') . ' ' . ($me['family_name'] ?? ''))),
            'picture' => (string)($me['picture'] ?? ''),
            'verified_email' => !empty($me['email_verified']),
        ];
    }

    private static function http(string $method, string $url, ?array $form = null, array $headers = []): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(['Accept: application/json'], $headers));
        if (is_file($ca = dirname(__DIR__, 2) . '/resources/certs/cacert.pem')) {
            curl_setopt($ch, CURLOPT_CAINFO, $ca);
        }
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query((array)$form));
        }
        $body = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($body === false) {
            throw new \RuntimeException('HTTP error: ' . $err);
        }
        $data = json_decode((string)$body, true);
        return is_array($data) ? $data : [];
    }

    /** users.facebook_* / users.linkedin_* columns (added on first use; SQL also in database/migrations). */
    public static function ensureSchema(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        $db = Database::getInstance();
        foreach (self::PROVIDERS as $p) {
            try {
                if (!$db->fetchOne("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = ?", [$p . '_id'])) {
                    $db->getConnection()->exec("ALTER TABLE users ADD COLUMN {$p}_id VARCHAR(255) NULL, ADD COLUMN {$p}_email VARCHAR(255) NULL,
                        ADD COLUMN {$p}_name VARCHAR(255) NULL, ADD UNIQUE KEY uq_users_{$p}_id ({$p}_id)");
                }
            } catch (\Throwable $e) {
                error_log('SocialOAuthService schema (' . $p . '): ' . $e->getMessage());
            }
        }
    }
}
