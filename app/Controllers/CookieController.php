<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Middlewares\CsrfMiddleware;
use App\Services\CookieService;

class CookieController extends BaseController
{
    public function showBanner(Request $request, Response $response): void
    {
        CookieService::ensureSchema();
        $response->json(['success' => true]);
    }

    public function getConsentStatus(Request $request, Response $response): void
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        CookieService::ensureSchema();
        $db = Database::getInstance();
        $version = $db->fetchOne("SELECT id, version_number, requires_reconsent FROM cookie_policy_versions WHERE is_active = 1 ORDER BY effective_from DESC LIMIT 1");
        $settingsRow = ['block_scripts_until_consent' => 1];
        try {
            $settingsRow = $db->fetchOne("SELECT block_scripts_until_consent FROM global_cookie_settings LIMIT 1") ?: $settingsRow;
        } catch (\Throwable $e) {
            $settingsRow = $settingsRow;
        }
        $sessionId = session_id() ?: null;
        $anon = $_COOKIE['anon_id'] ?? $sessionId;
        $userId = $_SESSION['user_id'] ?? null;
        $consent = null;
        $verId = (int)($version['id'] ?? 0);
        if ($anon || $sessionId) {
            $consent = $db->fetchOne("SELECT * FROM user_cookie_consents WHERE consent_version_id = :v AND revoked_at IS NULL AND (anonymous_id = :a OR session_id = :sid) ORDER BY id DESC LIMIT 1", ['a' => $anon, 'sid' => $sessionId, 'v' => $verId]);
        }
        if (!$consent && $userId) {
            $consent = $db->fetchOne("SELECT * FROM user_cookie_consents WHERE user_id = :u AND consent_version_id = :v AND revoked_at IS NULL ORDER BY id DESC LIMIT 1", ['u' => (int)$userId, 'v' => $verId]);
        }
        $response->json([
            'version' => $version,
            'settings' => ['block_scripts_until_consent' => (int)($settingsRow['block_scripts_until_consent'] ?? 1) === 1],
            'consent' => $consent ? [
                'essential' => (int)$consent['essential'] === 1,
                'functional' => (int)$consent['functional'] === 1,
                'analytics' => (int)$consent['analytics'] === 1,
                'marketing' => (int)$consent['marketing'] === 1,
                'performance' => (int)$consent['performance'] === 1,
                'timestamp' => $consent['consent_timestamp'] ?? null,
                'consent_version_id' => (int)($consent['consent_version_id'] ?? 0),
                'expires_at' => $consent['expires_at'] ?? null
            ] : null
        ]);
    }

    public function saveConsent(Request $request, Response $response): void
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        CsrfMiddleware::generateToken();
        $token = $request->header('X-CSRF-Token') ?? $request->post('_token');
        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $response->json(['error' => 'Invalid CSRF token'], 403);
            return;
        }
        CookieService::ensureSchema();
        $data = $request->getJsonBody() ?? $request->all();
        $db = Database::getInstance();
        $version = $db->fetchOne("SELECT id FROM cookie_policy_versions WHERE is_active = 1 ORDER BY effective_from DESC LIMIT 1");
        $verId = (int)($version['id'] ?? 0);
        $anonId = $_COOKIE['anon_id'] ?? bin2hex(random_bytes(16));
        $sessionId = session_id() ?: bin2hex(random_bytes(8));
        $userId = $_SESSION['user_id'] ?? null;
        $userEmail = null;
        if ($userId) {
            try {
                $row = $db->fetchOne("SELECT email FROM users WHERE id = :id LIMIT 1", ['id' => (int)$userId]);
                $userEmail = (string)($row['email'] ?? '');
            } catch (\Throwable $e) {}
        }
        if (!isset($_COOKIE['anon_id'])) {
            if (!headers_sent()) {
                setcookie('anon_id', $anonId, [
                    'expires' => time() + 31536000,
                    'path' => '/',
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }
        }
        $consentVals = [
            'essential' => 1,
            'functional' => (int)($data['functional'] ?? 0) === 1 ? 1 : 0,
            'analytics' => (int)($data['analytics'] ?? 0) === 1 ? 1 : 0,
            'marketing' => (int)($data['marketing'] ?? 0) === 1 ? 1 : 0,
            'performance' => (int)($data['performance'] ?? 0) === 1 ? 1 : 0
        ];
        $expiresMonths = (int)($db->fetchOne("SELECT auto_expiry_months FROM global_cookie_settings LIMIT 1")['auto_expiry_months'] ?? 12);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $secret = $_ENV['IP_HASH_SECRET'] ?? ($_ENV['APP_KEY'] ?? 'mw-secret');
        $ipHash = $ip ? hash('sha256', $ip . $secret) : null;
        $existing = null;
        if ($userId) {
            try {
                $existing = $db->fetchOne("SELECT * FROM user_cookie_consents WHERE user_id = :uid AND consent_version_id = :ver AND revoked_at IS NULL ORDER BY id DESC LIMIT 1", ['uid' => (int)$userId, 'ver' => $verId]);
            } catch (\Throwable $e) { $existing = null; }
        } else {
            try {
                $existing = $db->fetchOne("SELECT * FROM user_cookie_consents WHERE consent_version_id = :ver AND revoked_at IS NULL AND (anonymous_id = :anon OR session_id = :sid) ORDER BY id DESC LIMIT 1", ['ver' => $verId, 'anon' => $anonId, 'sid' => $sessionId]);
            } catch (\Throwable $e) { $existing = null; }
        }
        if ($existing) {
            $prev = [
                'essential' => (int)($existing['essential'] ?? 1) === 1,
                'functional' => (int)($existing['functional'] ?? 0) === 1,
                'analytics' => (int)($existing['analytics'] ?? 0) === 1,
                'marketing' => (int)($existing['marketing'] ?? 0) === 1,
                'performance' => (int)($existing['performance'] ?? 0) === 1
            ];
            $params = [
                'id' => (int)$existing['id'],
                'ess' => 1,
                'fun' => $consentVals['functional'],
                'an' => $consentVals['analytics'],
                'mkt' => $consentVals['marketing'],
                'perf' => $consentVals['performance'],
                'src' => ($data['source'] ?? 'banner'),
                'met' => ($data['method'] ?? 'explicit'),
                'cc' => $data['country_code'] ?? null,
                'rc' => $data['region_code'] ?? null,
                'dev' => $data['device_type'] ?? null,
                'br' => $data['browser_name'] ?? null,
                'months' => $expiresMonths
            ];
            if ($userId) {
                $params['uid'] = (int)$userId;
                $params['email'] = $userEmail !== '' ? $userEmail : null;
                $params['anon'] = null;
                $params['linked_at'] = 1;
                $db->execute("
                    UPDATE user_cookie_consents 
                    SET user_id = :uid, email = :email, anonymous_id = NULL, consent_linked_at = NOW(),
                        essential = :ess, functional = :fun, analytics = :an, marketing = :mkt, performance = :perf,
                        consent_source = :src, consent_method = :met, country_code = :cc, region_code = :rc, device_type = :dev, browser_name = :br,
                        consent_timestamp = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL :months MONTH)
                    WHERE id = :id
                ", $params);
            } else {
                $params['uid'] = null;
                $params['email'] = null;
                $params['anon'] = $existing['anonymous_id'] ?? $anonId;
                $db->execute("
                    UPDATE user_cookie_consents 
                    SET user_id = NULL, email = NULL,
                        essential = :ess, functional = :fun, analytics = :an, marketing = :mkt, performance = :perf,
                        consent_source = :src, consent_method = :met, country_code = :cc, region_code = :rc, device_type = :dev, browser_name = :br,
                        consent_timestamp = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL :months MONTH)
                    WHERE id = :id
                ", $params);
            }
            $db->execute("
                INSERT INTO cookie_consent_audit_logs (consent_id, action, previous_values, new_values, ip_hash, user_agent)
                VALUES (:cid, 'updated', :prev, :newv, :ip_hash, :ua)
            ", [
                'cid' => (int)$existing['id'],
                'prev' => json_encode($prev),
                'newv' => json_encode($consentVals),
                'ip_hash' => $ipHash,
                'ua' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } else {
            $db->execute("
                INSERT INTO user_cookie_consents
                (user_id, email, anonymous_id, session_id, ip_hash, country_code, region_code, device_type, browser_name, consent_version_id, essential, functional, analytics, marketing, performance, consent_source, consent_method, consent_timestamp, consent_linked_at, expires_at)
                VALUES (:user_id, :email, :anon, :sid, :ip_hash, :cc, :rc, :dev, :br, :ver, :ess, :fun, :an, :mkt, :perf, :src, :met, NOW(), :linked_at, DATE_ADD(NOW(), INTERVAL :months MONTH))
            ", [
                'user_id' => $userId ? (int)$userId : null,
                'email' => $userId ? ($userEmail !== '' ? $userEmail : null) : null,
                'anon' => $userId ? null : $anonId,
                'sid' => $sessionId,
                'ip_hash' => $ipHash,
                'cc' => $data['country_code'] ?? null,
                'rc' => $data['region_code'] ?? null,
                'dev' => $data['device_type'] ?? null,
                'br' => $data['browser_name'] ?? null,
                'ver' => $verId,
                'ess' => 1,
                'fun' => $consentVals['functional'],
                'an' => $consentVals['analytics'],
                'mkt' => $consentVals['marketing'],
                'perf' => $consentVals['performance'],
                'src' => ($data['source'] ?? 'banner'),
                'met' => ($data['method'] ?? 'explicit'),
                'linked_at' => $userId ? date('Y-m-d H:i:s') : null,
                'months' => $expiresMonths
            ]);
            $cid = (int)$db->lastInsertId();
            $db->execute("
                INSERT INTO cookie_consent_audit_logs (consent_id, action, previous_values, new_values, ip_hash, user_agent)
                VALUES (:cid, 'given', NULL, :newv, :ip_hash, :ua)
            ", [
                'cid' => $cid,
                'newv' => json_encode($consentVals),
                'ip_hash' => $ipHash,
                'ua' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        }
        $response->json(['success' => true]);
    }

    public function withdrawConsent(Request $request, Response $response): void
    {
        CsrfMiddleware::generateToken();
        $token = $request->header('X-CSRF-Token') ?? $request->post('_token');
        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $response->json(['error' => 'Invalid CSRF token'], 403);
            return;
        }
        $db = Database::getInstance();
        $anon = $_COOKIE['anon_id'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $secret = $_ENV['IP_HASH_SECRET'] ?? ($_ENV['APP_KEY'] ?? 'mw-secret');
        $ipHash = $ip ? hash('sha256', $ip . $secret) : null;
        $row = null;
        if ($anon) {
            $row = $db->fetchOne("SELECT id, essential, functional, analytics, marketing, performance FROM user_cookie_consents WHERE anonymous_id = :a AND revoked_at IS NULL ORDER BY id DESC LIMIT 1", ['a' => $anon]);
        }
        if (!$row && $userId) {
            $row = $db->fetchOne("SELECT id, essential, functional, analytics, marketing, performance FROM user_cookie_consents WHERE user_id = :u AND revoked_at IS NULL ORDER BY id DESC LIMIT 1", ['u' => (int)$userId]);
        }
        if ($row) {
            $db->execute("UPDATE user_cookie_consents SET revoked_at = NOW() WHERE id = :id", ['id' => (int)$row['id']]);
            $db->execute("
                INSERT INTO cookie_consent_audit_logs (consent_id, action, previous_values, new_values, ip_hash, user_agent)
                VALUES (:cid, 'revoked', :prev, :newv, :ip_hash, :ua)
            ", [
                'cid' => (int)$row['id'],
                'prev' => json_encode([
                    'essential' => (int)$row['essential'] === 1,
                    'functional' => (int)$row['functional'] === 1,
                    'analytics' => (int)$row['analytics'] === 1,
                    'marketing' => (int)$row['marketing'] === 1,
                    'performance' => (int)$row['performance'] === 1
                ]),
                'newv' => json_encode(['essential' => true, 'functional' => false, 'analytics' => false, 'marketing' => false, 'performance' => false]),
                'ip_hash' => $ipHash,
                'ua' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        }
        $response->json(['success' => true]);
    }

    public function serveConsentJS(Request $request, Response $response): void
    {
        $path = __DIR__ . '/../../resources/js/consent-manager.js';
        $response->setHeader('Content-Type', 'application/javascript; charset=utf-8');
        if (file_exists($path)) {
            echo file_get_contents($path);
        } else {
            echo "document.addEventListener('DOMContentLoaded',function(){})";
        }
    }

    public function serveScriptLoaderJS(Request $request, Response $response): void
    {
        $path = __DIR__ . '/../../resources/js/script-loader.js';
        $response->setHeader('Content-Type', 'application/javascript; charset=utf-8');
        if (file_exists($path)) {
            echo file_get_contents($path);
        } else {
            echo "document.addEventListener('DOMContentLoaded',function(){})";
        }
    }

    public function getScriptControls(Request $request, Response $response): void
    {
        CookieService::ensureSchema();
        $db = Database::getInstance();
        $rows = [];
        try {
            $rows = $db->fetchAll("SELECT id, script_name, script_src, category_required, is_blocked_by_default, is_active FROM consent_script_controls WHERE is_active = 1 ORDER BY id ASC");
        } catch (\Throwable $e) {}
        $response->json(['scripts' => $rows]);
    }

    public function policy(Request $request, Response $response): void
    {
        CookieService::ensureSchema();
        $db = Database::getInstance();
        $versionNum = trim((string)($request->get('version') ?? ''));
        $policy = null;
        if ($versionNum !== '') {
            $policy = $db->fetchOne("SELECT * FROM cookie_policy_versions WHERE version_number = :v LIMIT 1", ['v' => $versionNum]);
        }
        if (!$policy) {
            $policy = $db->fetchOne("SELECT * FROM cookie_policy_versions WHERE is_active = 1 ORDER BY effective_from DESC LIMIT 1");
        }
        if (!$policy) {
            $response->view('about', ['title' => 'Cookie Policy', 'message' => 'No policy published'], 404, 'layout');
            return;
        }
        $response->view('legal/cookie_policy', [
            'title' => 'Cookie Policy',
            'policy' => $policy
        ], 200, 'layout');
    }
}
