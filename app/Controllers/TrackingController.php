<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Services\CookieService;

class TrackingController extends BaseController
{
    private function getConsent(): array
    {
        $db = Database::getInstance();
        $anon = $_COOKIE['anon_id'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;
        $consent = null;
        if ($anon) { $consent = $db->fetchOne("SELECT * FROM user_cookie_consents WHERE anonymous_id = :a AND revoked_at IS NULL ORDER BY id DESC LIMIT 1", ['a' => $anon]); }
        if (!$consent && $userId) { $consent = $db->fetchOne("SELECT * FROM user_cookie_consents WHERE user_id = :u AND revoked_at IS NULL ORDER BY id DESC LIMIT 1", ['u' => (int)$userId]); }
        return [
            'essential' => (int)($consent['essential'] ?? 1) === 1,
            'functional' => (int)($consent['functional'] ?? 0) === 1,
            'analytics' => (int)($consent['analytics'] ?? 0) === 1,
            'marketing' => (int)($consent['marketing'] ?? 0) === 1,
            'performance' => (int)($consent['performance'] ?? 0) === 1
        ];
    }
    public function trackVisitor(Request $request, Response $response): void
    {
        CookieService::ensureSchema();
        $c = $this->getConsent();
        if (!$c['essential']) { $response->json(['success' => true]); return; }
        $db = Database::getInstance();
        $uuid = $_COOKIE['anon_id'] ?? bin2hex(random_bytes(16));
        if (!isset($_COOKIE['anon_id'])) {
            if (!headers_sent()) {
                setcookie('anon_id', $uuid, time() + 31536000, '/', '', false, true);
            }
        }
        $row = $db->fetchOne("SELECT id FROM tracking_visitors WHERE visitor_uuid = :u", ['u' => $uuid]);
        if (!$row) {
            $db->execute("INSERT INTO tracking_visitors (visitor_uuid, user_id, ip_hash, referrer, utm_source, utm_medium, utm_campaign) VALUES (:u, :user, SHA2(:ip,256), :ref, :us, :um, :uc)", [
                'u' => $uuid,
                'user' => (int)($_SESSION['user_id'] ?? 0) ?: null,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'ref' => $_SERVER['HTTP_REFERER'] ?? '',
                'us' => $request->get('utm_source') ?? null,
                'um' => $request->get('utm_medium') ?? null,
                'uc' => $request->get('utm_campaign') ?? null
            ]);
        } else {
            $db->execute("UPDATE tracking_visitors SET last_visit_at = NOW(), total_page_views = total_page_views + 1 WHERE visitor_uuid = :u", ['u' => $uuid]);
        }
        $response->json(['success' => true]);
    }

    public function startSession(Request $request, Response $response): void
    {
        CookieService::ensureSchema();
        $c = $this->getConsent();
        if (!$c['essential']) { $response->json(['success' => true]); return; }
        $db = Database::getInstance();
        $uuid = $_COOKIE['anon_id'] ?? bin2hex(random_bytes(16));
        $vis = $db->fetchOne("SELECT id FROM tracking_visitors WHERE visitor_uuid = :u", ['u' => $uuid]);
        if (!$vis) {
            $db->execute("INSERT INTO tracking_visitors (visitor_uuid) VALUES (:u)", ['u' => $uuid]);
            $visId = (int)$db->lastInsertId();
        } else {
            $visId = (int)$vis['id'];
        }
        $sid = session_id() ?: bin2hex(random_bytes(8));
        $row = $db->fetchOne("SELECT id FROM visitor_sessions WHERE session_id = :sid", ['sid' => $sid]);
        if (!$row) {
            $db->execute("INSERT INTO visitor_sessions (visitor_id, session_id, login_status, device_type, browser, os) VALUES (:vid, :sid, :ls, :dev, :br, :os)", [
                'vid' => $visId,
                'sid' => $sid,
                'ls' => (isset($_SESSION['user_id']) ? 'logged_in' : 'guest'),
                'dev' => $request->get('device') ?? null,
                'br' => $request->get('browser') ?? null,
                'os' => $request->get('os') ?? null
            ]);
            $db->execute("UPDATE tracking_visitors SET total_sessions = total_sessions + 1 WHERE id = :id", ['id' => $visId]);
        }
        $response->json(['success' => true]);
    }

    public function endSession(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $sid = session_id() ?: '';
        if ($sid) {
            $db->execute("UPDATE visitor_sessions SET ended_at = NOW() WHERE session_id = :sid AND ended_at IS NULL", ['sid' => $sid]);
        }
        $response->json(['success' => true]);
    }

    public function trackEvent(Request $request, Response $response): void
    {
        CookieService::ensureSchema();
        $c = $this->getConsent();
        if (!$c['analytics']) { $response->json(['success' => true]); return; }
        $data = $request->getJsonBody() ?? $request->all();
        $db = Database::getInstance();
        $uuid = $_COOKIE['anon_id'] ?? bin2hex(random_bytes(16));
        $vis = $db->fetchOne("SELECT id FROM tracking_visitors WHERE visitor_uuid = :u", ['u' => $uuid]);
        $vid = $vis ? (int)$vis['id'] : 0;
        if (!$vid) {
            $db->execute("INSERT INTO tracking_visitors (visitor_uuid) VALUES (:u)", ['u' => $uuid]);
            $vid = (int)$db->lastInsertId();
        }
        $db->execute("INSERT INTO behavior_events (visitor_id, user_id, event_type, event_category, event_data, page_url, referrer, device_type) VALUES (:vid, :uid, :type, :cat, :data, :url, :ref, :dev)", [
            'vid' => $vid,
            'uid' => (int)($_SESSION['user_id'] ?? 0) ?: null,
            'type' => (string)($data['event_type'] ?? ''),
            'cat' => (string)($data['event_category'] ?? ''),
            'data' => json_encode($data['event_data'] ?? []),
            'url' => (string)($data['page_url'] ?? ''),
            'ref' => (string)($data['referrer'] ?? ''),
            'dev' => (string)($data['device_type'] ?? '')
        ]);
        $response->json(['success' => true]);
    }

    public function trackHeatmap(Request $request, Response $response): void
    {
        CookieService::ensureSchema();
        $c = $this->getConsent();
        if (!$c['performance']) { $response->json(['success' => true]); return; }
        $db = Database::getInstance();
        $data = $request->getJsonBody() ?? $request->all();
        $uuid = $_COOKIE['anon_id'] ?? bin2hex(random_bytes(16));
        $vis = $db->fetchOne("SELECT id FROM tracking_visitors WHERE visitor_uuid = :u", ['u' => $uuid]);
        $vid = $vis ? (int)$vis['id'] : 0;
        if (!$vid) {
            $db->execute("INSERT INTO tracking_visitors (visitor_uuid) VALUES (:u)", ['u' => $uuid]);
            $vid = (int)$db->lastInsertId();
        }
        $db->execute("INSERT INTO heatmap_events (visitor_id, page_url, click_x, click_y, scroll_depth, viewport_width, viewport_height) VALUES (:vid, :url, :x, :y, :sd, :vw, :vh)", [
            'vid' => $vid,
            'url' => (string)($data['page_url'] ?? ''),
            'x' => (int)($data['click_x'] ?? 0),
            'y' => (int)($data['click_y'] ?? 0),
            'sd' => (int)($data['scroll_depth'] ?? 0),
            'vw' => (int)($data['viewport_width'] ?? 0),
            'vh' => (int)($data['viewport_height'] ?? 0)
        ]);
        $response->json(['success' => true]);
    }
}
