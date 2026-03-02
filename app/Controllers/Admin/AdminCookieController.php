<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Services\CookieService;
use App\Middlewares\CsrfMiddleware;

class AdminCookieController extends BaseController
{
    private function requireAdmin(Request $request, Response $response): bool
    {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $response->redirect('/admin/login');
            return false;
        }
        return true;
    }

    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        error_log('AdminCookieController:index start');
        try { CookieService::ensureSchema(); } catch (\Throwable $e) { error_log('Cookie ensureSchema error: ' . $e->getMessage()); }
        $db = Database::getInstance();
        $categories = [];
        $definitions = [];
        $versions = [];
        try { $categories = $db->fetchAll("SELECT * FROM cookie_categories ORDER BY sort_order ASC, name ASC"); error_log('AdminCookieController:index categories=' . count($categories)); } catch (\Throwable $e) { error_log('AdminCookieController:index categories error ' . $e->getMessage()); }
        try { $definitions = $db->fetchAll("SELECT d.*, c.name as category_name FROM cookie_definitions d INNER JOIN cookie_categories c ON c.id = d.category_id ORDER BY c.sort_order ASC, d.cookie_name ASC"); error_log('AdminCookieController:index definitions=' . count($definitions)); } catch (\Throwable $e) { error_log('AdminCookieController:index definitions error ' . $e->getMessage()); }
        try { $versions = $db->fetchAll("SELECT * FROM cookie_policy_versions ORDER BY effective_from DESC"); error_log('AdminCookieController:index versions=' . count($versions)); } catch (\Throwable $e) { error_log('AdminCookieController:index versions error ' . $e->getMessage()); }
        $stats = [
            'total' => 0,
            'optional_any' => 0,
            'functional' => 0,
            'analytics' => 0,
            'marketing' => 0,
            'performance' => 0
        ];
        try { $stats['total'] = (int)($db->fetchOne("SELECT COUNT(*) c FROM user_cookie_consents WHERE revoked_at IS NULL")['c'] ?? 0); } catch (\Throwable $e) {}
        try { $stats['functional'] = (int)($db->fetchOne("SELECT COUNT(*) c FROM user_cookie_consents WHERE revoked_at IS NULL AND functional=1")['c'] ?? 0); } catch (\Throwable $e) {}
        try { $stats['analytics'] = (int)($db->fetchOne("SELECT COUNT(*) c FROM user_cookie_consents WHERE revoked_at IS NULL AND analytics=1")['c'] ?? 0); } catch (\Throwable $e) {}
        try { $stats['marketing'] = (int)($db->fetchOne("SELECT COUNT(*) c FROM user_cookie_consents WHERE revoked_at IS NULL AND marketing=1")['c'] ?? 0); } catch (\Throwable $e) {}
        try { $stats['performance'] = (int)($db->fetchOne("SELECT COUNT(*) c FROM user_cookie_consents WHERE revoked_at IS NULL AND performance=1")['c'] ?? 0); } catch (\Throwable $e) {}
        try { $stats['optional_any'] = (int)($db->fetchOne("SELECT COUNT(*) c FROM user_cookie_consents WHERE revoked_at IS NULL AND (functional=1 OR analytics=1 OR marketing=1 OR performance=1)")['c'] ?? 0); } catch (\Throwable $e) {}
        $breakdown = [
            'full_accept' => 0,
            'partial' => 0,
            'rejected' => 0,
            'total' => (int)($stats['total'] ?? 0)
        ];
        try {
            $breakdown['full_accept'] = (int)($db->fetchOne("SELECT COUNT(*) c FROM user_cookie_consents WHERE revoked_at IS NULL AND functional=1 AND analytics=1 AND marketing=1 AND performance=1")['c'] ?? 0);
            $breakdown['rejected'] = (int)($db->fetchOne("SELECT COUNT(*) c FROM user_cookie_consents WHERE revoked_at IS NULL AND functional=0 AND analytics=0 AND marketing=0 AND performance=0")['c'] ?? 0);
            $optAny = (int)($stats['optional_any'] ?? 0);
            $breakdown['partial'] = max(0, $optAny - $breakdown['full_accept']);
        } catch (\Throwable $e) {}
        $trends = [
            'today_total' => 0,
            'acceptance_delta' => 0.0,
            'marketing_delta' => 0.0,
            'analytics_delta' => 0.0
        ];
        try {
            $trends['today_total'] = (int)($db->fetchOne("SELECT COUNT(*) c FROM user_cookie_consents WHERE revoked_at IS NULL AND consent_timestamp >= DATE_SUB(NOW(), INTERVAL 1 DAY)")['c'] ?? 0);
            $weekTotal = (int)($db->fetchOne("SELECT COUNT(*) c FROM user_cookie_consents WHERE revoked_at IS NULL AND consent_timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['c'] ?? 0);
            $weekOptional = (int)($db->fetchOne("SELECT COUNT(*) c FROM user_cookie_consents WHERE revoked_at IS NULL AND (functional=1 OR analytics=1 OR marketing=1 OR performance=1) AND consent_timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['c'] ?? 0);
            $weekMarketing = (int)($db->fetchOne("SELECT COUNT(*) c FROM user_cookie_consents WHERE revoked_at IS NULL AND marketing=1 AND consent_timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['c'] ?? 0);
            $weekAnalytics = (int)($db->fetchOne("SELECT COUNT(*) c FROM user_cookie_consents WHERE revoked_at IS NULL AND analytics=1 AND consent_timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['c'] ?? 0);
            $currTotal = max(1,(int)($stats['total'] ?? 1));
            $currOptRate = ((int)($stats['optional_any'] ?? 0)) / $currTotal;
            $currMktRate = ((int)($stats['marketing'] ?? 0)) / $currTotal;
            $currAnRate = ((int)($stats['analytics'] ?? 0)) / $currTotal;
            $prevOptRate = $weekTotal > 0 ? ($weekOptional / $weekTotal) : 0.0;
            $prevMktRate = $weekTotal > 0 ? ($weekMarketing / $weekTotal) : 0.0;
            $prevAnRate = $weekTotal > 0 ? ($weekAnalytics / $weekTotal) : 0.0;
            $trends['acceptance_delta'] = round(($currOptRate - $prevOptRate) * 100, 1);
            $trends['marketing_delta'] = round(($currMktRate - $prevMktRate) * 100, 1);
            $trends['analytics_delta'] = round(($currAnRate - $prevAnRate) * 100, 1);
        } catch (\Throwable $e) {}
        $acceptance_pct = 0;
        $full_accept_pct = 0;
        try {
            $t = (int)($stats['total'] ?? 0);
            $opt = (int)($stats['optional_any'] ?? 0);
            $acceptance_pct = $t > 0 ? round(($opt / $t) * 100) : 0;
            $full_accept_pct = $t > 0 ? round(((int)$breakdown['full_accept'] / $t) * 100) : 0;
        } catch (\Throwable $e) {}
        $activities = [];
        try {
            $activities = $db->fetchAll("
                SELECT l.id, l.action, l.created_at, l.new_values, u.email, c.country_code, c.region_code, c.browser_name, c.ip_hash, c.anonymous_id
                FROM cookie_consent_audit_logs l
                LEFT JOIN user_cookie_consents c ON c.id = l.consent_id
                LEFT JOIN users u ON u.id = c.user_id
                ORDER BY l.created_at DESC
                LIMIT 15
            ");
        } catch (\Throwable $e) {}
        CsrfMiddleware::generateToken();
        error_log('AdminCookieController:index rendering view');
        $response->view('admin/cookies/panel', [
            'title' => 'Cookie & Consent Management',
            'categories' => $categories,
            'definitions' => $definitions,
            'versions' => $versions,
            'stats' => $stats,
            'breakdown' => $breakdown,
            'trends' => $trends,
            'acceptance_pct' => $acceptance_pct,
            'full_accept_pct' => $full_accept_pct,
            'activities' => $activities,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
        error_log('AdminCookieController:index end');
    }

    public function toggleCategory(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        $id = (int)$request->post('id');
        $field = (string)$request->post('field');
        $value = (int)$request->post('value') ? 1 : 0;
        if (!in_array($field, ['is_active','is_mandatory'], true)) {
            $response->json(['error' => 'Invalid field'], 422); return;
        }
        Database::getInstance()->execute("UPDATE cookie_categories SET {$field} = :v, updated_at = NOW() WHERE id = :id", ['v' => $value, 'id' => $id]);
        $response->json(['success' => true]);
    }

    public function upsertDefinition(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        $d = $request->getJsonBody() ?? $request->all();
        $id = (int)($d['id'] ?? 0);
        $params = [
            'category_id' => (int)$d['category_id'],
            'cookie_name' => (string)$d['cookie_name'],
            'provider' => (string)($d['provider'] ?? 'internal'),
            'purpose' => (string)($d['purpose'] ?? ''),
            'duration_type' => in_array(($d['duration_type'] ?? 'session'), ['session','persistent'], true) ? $d['duration_type'] : 'session',
            'duration_days' => (int)($d['duration_days'] ?? 0),
            'is_third_party' => (int)($d['is_third_party'] ?? 0),
            'is_http_only' => (int)($d['is_http_only'] ?? 0),
            'is_secure' => (int)($d['is_secure'] ?? 0),
            'same_site' => in_array(($d['same_site'] ?? 'Lax'), ['Lax','Strict','None'], true) ? $d['same_site'] : 'Lax',
            'is_active' => (int)($d['is_active'] ?? 1)
        ];
        $db = Database::getInstance();
        if ($id > 0) {
            $db->execute("UPDATE cookie_definitions SET category_id=:category_id,cookie_name=:cookie_name,provider=:provider,purpose=:purpose,duration_type=:duration_type,duration_days=:duration_days,is_third_party=:is_third_party,is_http_only=:is_http_only,is_secure=:is_secure,same_site=:same_site,is_active=:is_active,updated_at=NOW() WHERE id=:id", $params + ['id' => $id]);
        } else {
            $db->execute("INSERT INTO cookie_definitions (category_id,cookie_name,provider,purpose,duration_type,duration_days,is_third_party,is_http_only,is_secure,same_site,is_active) VALUES (:category_id,:cookie_name,:provider,:purpose,:duration_type,:duration_days,:is_third_party,:is_http_only,:is_secure,:same_site,:is_active)", $params);
        }
        $response->json(['success' => true]);
    }

    public function updatePolicy(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        $d = $request->getJsonBody() ?? $request->all();
        $ver = (string)($d['version_number'] ?? '');
        $text = (string)($d['policy_text'] ?? '');
        $reconsent = (int)($d['requires_reconsent'] ?? 0) ? 1 : 0;
        $db = Database::getInstance();
        $db->execute("UPDATE cookie_policy_versions SET is_active = 0");
        $db->execute("INSERT INTO cookie_policy_versions (version_number, policy_text, effective_from, is_active, requires_reconsent, created_by_admin_id) VALUES (:v, :t, NOW(), 1, :r, :aid)", [
            'v' => $ver !== '' ? $ver : ('v-' . date('Ymd-His')),
            't' => $text !== '' ? $text : 'Policy updated',
            'r' => $reconsent,
            'aid' => (int)($this->currentUser->id ?? 0)
        ]);
        $response->json(['success' => true]);
    }

    public function forceReconsent(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        Database::getInstance()->execute("UPDATE cookie_policy_versions SET requires_reconsent = 1 WHERE is_active = 1");
        $response->json(['success' => true]);
    }

    public function exportLogs(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        $db = Database::getInstance();
        $rows = $db->fetchAll("SELECT c.id, c.user_id, c.anonymous_id, c.session_id, c.country_code, c.device_type, c.browser_name, c.essential, c.functional, c.analytics, c.marketing, c.performance, c.consent_timestamp, c.expires_at, v.version_number FROM user_cookie_consents c INNER JOIN cookie_policy_versions v ON v.id = c.consent_version_id ORDER BY c.consent_timestamp DESC LIMIT 10000");
        $response->setHeader('Content-Type', 'text/csv; charset=utf-8');
        $response->setHeader('Content-Disposition', 'attachment; filename=' . 'consents.csv');
        $out = fopen('php://output', 'w');
        if ($out) {
            fputcsv($out, array_keys($rows[0] ?? ['id' => 0]));
            foreach ($rows as $r) { fputcsv($out, $r); }
            fclose($out);
        }
    }

    public function deleteConsent(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        $targetAnon = (string)($request->post('anonymous_id') ?? '');
        $targetUser = (int)($request->post('user_id') ?? 0);
        $db = Database::getInstance();
        if ($targetAnon !== '') {
            $db->execute("DELETE FROM cookie_consent_audit_logs WHERE consent_id IN (SELECT id FROM user_cookie_consents WHERE anonymous_id = :a)", ['a' => $targetAnon]);
            $db->execute("DELETE FROM user_cookie_consents WHERE anonymous_id = :a", ['a' => $targetAnon]);
        }
        if ($targetUser > 0) {
            $db->execute("DELETE FROM cookie_consent_audit_logs WHERE consent_id IN (SELECT id FROM user_cookie_consents WHERE user_id = :u)", ['u' => $targetUser]);
            $db->execute("DELETE FROM user_cookie_consents WHERE user_id = :u", ['u' => $targetUser]);
        }
        $response->json(['success' => true]);
    }

    public function heatmap(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) { return; }
        $page = $request->get('page') ?? '';
        $db = Database::getInstance();
        $sql = "SELECT page_url, click_x, click_y, scroll_depth, viewport_width, viewport_height, created_at FROM heatmap_events";
        $params = [];
        if ($page) { $sql .= " WHERE page_url = :p"; $params['p'] = $page; }
        $sql .= " ORDER BY created_at DESC LIMIT 500";
        $events = $db->fetchAll($sql, $params);
        CsrfMiddleware::generateToken();
        $response->view('admin/cookies/heatmap', [
            'title' => 'Heatmap Dashboard',
            'events' => $events,
            'page' => $page
        ], 200, 'admin/layout');
    }
}
