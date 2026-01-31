<?php

declare(strict_types=1);

namespace App\Controllers\MasterAdmin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class SystemController extends BaseController
{
    public function cron(Request $request, Response $response): void
    {
        $metrics = [
            'auto_applies_today' => 0,
            'failed_auto_applies_today' => 0,
            'avg_match_score_today' => 0.0,
            'load_alert' => false
        ];
        $autoApply = [
            'enabled' => 1,
            'daily_global_limit' => 1000
        ];
        try {
            $db = \App\Core\Database::getInstance();
            $rowA = $db->fetchOne("SELECT COUNT(*) as c, AVG(match_score) as avg FROM applications WHERE application_method = 'auto' AND DATE(applied_at) = CURDATE()");
            $metrics['auto_applies_today'] = (int)($rowA['c'] ?? 0);
            $metrics['avg_match_score_today'] = (float)($rowA['avg'] ?? 0.0);
            $rowF = $db->fetchOne("SELECT COUNT(*) as c FROM audit_logs WHERE action = 'auto_apply_error' AND DATE(created_at) = CURDATE()");
            $metrics['failed_auto_applies_today'] = (int)($rowF['c'] ?? 0);
        } catch (\Throwable $t) {}
        try {
            $enabled = (int)(\App\Models\SystemSetting::get('auto_apply_enabled', (string)($_ENV['AUTO_APPLY_ENABLED'] ?? '1')));
            $limit = (int)(\App\Models\SystemSetting::get('auto_apply_daily_global_limit', (string)($_ENV['AUTO_APPLY_DAILY_GLOBAL_LIMIT'] ?? '1000')));
            $autoApply['enabled'] = $enabled;
            $autoApply['daily_global_limit'] = $limit;
            $metrics['load_alert'] = ($limit > 0) ? ($metrics['auto_applies_today'] >= (int)floor($limit * 0.8)) : false;
        } catch (\Throwable $t) {
            $limit = (int)($_ENV['AUTO_APPLY_DAILY_GLOBAL_LIMIT'] ?? 1000);
            $autoApply['enabled'] = (int)($_ENV['AUTO_APPLY_ENABLED'] ?? 1);
            $autoApply['daily_global_limit'] = $limit;
            $metrics['load_alert'] = ($limit > 0) ? ($metrics['auto_applies_today'] >= (int)floor($limit * 0.8)) : false;
        }
        $response->view('masteradmin/system/cron', [
            'title' => 'Cron Manager',
            'metrics' => $metrics,
            'autoApply' => $autoApply
        ], 200, 'masteradmin/layout');
    }

    public function runCron(Request $request, Response $response): void
    {
        $task = (string)$request->post('task', '');
        $cronService = new \App\Services\CronService();
        $cronService->runTask($task);
        $response->redirect('/master/system/cron');
    }

    public function apiKeys(Request $request, Response $response): void
    {
        $keys = [];
        try {
            $db = \App\Core\Database::getInstance();
            $keys = $db->fetchAll("
                SELECT k.*, e.company_name, u.email 
                FROM employer_api_keys k 
                LEFT JOIN employers e ON k.employer_id = e.id 
                LEFT JOIN users u ON e.user_id = u.id 
                ORDER BY k.created_at DESC
            ");
        } catch (\Throwable $t) {}

        $response->view('masteradmin/system/api-keys', [
            'title' => 'API Keys',
            'keys' => $keys
        ], 200, 'masteradmin/layout');
    }

    public function logs(Request $request, Response $response): void
    {
        $logPath = __DIR__ . '/../../../storage/logs/php_errors.log';
        $logs = file_exists($logPath) ? file_get_contents($logPath) : 'No logs found.';
        
        // Simple tail (last 100 lines)
        $lines = explode("\n", $logs);
        $logs = implode("\n", array_slice($lines, -100));

        $response->view('masteradmin/system/logs', [
            'title' => 'System Logs',
            'logs' => $logs
        ], 200, 'masteradmin/layout');
    }

    public function support(Request $request, Response $response): void
    {
        $response->view('masteradmin/system/support', [
            'title' => 'Support Tickets'
        ], 200, 'masteradmin/layout');
    }

    public function ipWhitelist(Request $request, Response $response): void
    {
        $items = [];
        try {
            $db = \App\Core\Database::getInstance();
            $items = $db->fetchAll('SELECT * FROM ip_whitelist ORDER BY created_at DESC');
        } catch (\Throwable $t) {}
        $response->view('masteradmin/system/ip-whitelist', [
            'title' => 'IP Whitelist',
            'items' => $items
        ], 200, 'masteradmin/layout');
    }

    public function saveIpWhitelist(Request $request, Response $response): void
    {
        $ip = trim((string)$request->post('ip_address', ''));
        $label = trim((string)$request->post('label', ''));
        if ($ip !== '') {
            try {
                $db = \App\Core\Database::getInstance();
                $db->query('INSERT INTO ip_whitelist (ip_address, label, active, created_at) VALUES (:ip, :label, 1, NOW()) ON DUPLICATE KEY UPDATE label = VALUES(label), active = 1', ['ip' => $ip, 'label' => $label]);
            } catch (\Throwable $t) {}
        }
        $response->redirect('/master/system/ip-whitelist');
    }

    public function toggleIpWhitelist(Request $request, Response $response): void
    {
        $ip = trim((string)$request->post('ip_address', ''));
        if ($ip !== '') {
            try {
                $db = \App\Core\Database::getInstance();
                $row = $db->fetchOne('SELECT active FROM ip_whitelist WHERE ip_address = :ip', ['ip' => $ip]);
                if ($row) {
                    $new = (int)($row['active'] ?? 1) ? 0 : 1;
                    $db->query('UPDATE ip_whitelist SET active = :new WHERE ip_address = :ip', ['new' => $new, 'ip' => $ip]);
                }
            } catch (\Throwable $t) {}
        }
        $response->redirect('/master/system/ip-whitelist');
    }

    public function deleteIpWhitelist(Request $request, Response $response): void
    {
        $ip = trim((string)$request->post('ip_address', ''));
        if ($ip !== '') {
            try {
                $db = \App\Core\Database::getInstance();
                $db->query('DELETE FROM ip_whitelist WHERE ip_address = :ip', ['ip' => $ip]);
            } catch (\Throwable $t) {}
        }
        $response->redirect('/master/system/ip-whitelist');
    }

    public function impersonate(Request $request, Response $response): void
    {
        $userId = (int)$request->param('id');
        if ($userId > 0) {
            $_SESSION['impersonator_id'] = $_SESSION['user_id'] ?? null;
            $_SESSION['user_id'] = $userId;
        }
        $response->redirect('/');
    }

    public function stopImpersonate(Request $request, Response $response): void
    {
        if (!empty($_SESSION['impersonator_id'])) {
            $_SESSION['user_id'] = $_SESSION['impersonator_id'];
            unset($_SESSION['impersonator_id']);
        }
        $response->redirect('/master/dashboard');
    }

    public function panelBuilder(Request $request, Response $response): void
    {
        $response->view('masteradmin/system/panel-builder', [
            'title' => 'Auto-Create Panel Engine'
        ], 200, 'masteradmin/layout');
    }

    public function seedPermissions(Request $request, Response $response): void
    {
        $modules = [
            'sales' => ['sales.view','sales.assign','sales.leads.view','sales.leads.update','sales.reports.view'],
            'verification' => ['verification.view','verification.review','verification.override','verification.assign'],
            'support' => ['support.handle','support.tickets.view','support.tickets.assign','support.tickets.reply','support.tickets.close','support.escalate'],
            'finance' => ['payments.view','payments.approve','payments.refund','invoices.view'],
            'system' => ['system.manage','impersonate.user','ip_whitelist.manage','cron.manage','api.manage','audit.view']
        ];

        foreach ($modules as $module => $perms) {
            $this->ensurePermissions($perms);
        }

        $response->view('masteradmin/system/panel-builder', [
            'title' => 'Auto-Create Panel Engine',
            'success' => 'Permissions seeded successfully'
        ], 200, 'masteradmin/layout');
    }

    public function seedSampleTickets(Request $request, Response $response): void
    {
        try {
            $db = \App\Core\Database::getInstance();
            $db->query(
                'INSERT INTO support_tickets (subject, description, status, priority, category, created_by, created_at) VALUES 
                 ("Payment not reflecting", "Payment completed but not marked.", "open", "high", "payment", :uid, NOW()),
                 ("Job not visible", "Published job not visible in search.", "open", "medium", "job_visibility", :uid, NOW())',
                ['uid' => (int)($_SESSION['user_id'] ?? 1)]
            );
        } catch (\Throwable $t) {}
        $response->redirect('/support-exec/tickets');
    }

    public function seedSampleLeads(Request $request, Response $response): void
    {
        try {
            $db = \App\Core\Database::getInstance();
            $db->query(
                'INSERT INTO sales_leads (company_name, contact_name, contact_email, contact_phone, stage, source, created_at) VALUES 
                 ("Acme Corp", "Rahul Verma", "rahul@acme.com", "+91-9876543210", "new", "referral", NOW()),
                 ("Globex", "Anita Sharma", "anita@globex.com", "+91-9988776655", "contacted", "cold_call", NOW()),
                 ("Initech", "Sanjay Patel", "sanjay@initech.com", "+91-8877665544", "demo_done", "form", NOW())'
            );
        } catch (\Throwable $t) {}
        $response->redirect('/sales-manager/dashboard');
    }

    public function generatePanel(Request $request, Response $response): void
    {
        $type = (string)$request->post('panel_type', '');
        $count = max(1, (int)$request->post('count', 1));
        $prefix = trim((string)$request->post('prefix', ''));
        if ($prefix === '') { $prefix = $type; }
        $password = trim((string)$request->post('password', ''));

        $created = [];
        switch ($type) {
            case 'sales_executive':
                $this->ensurePermissions(['sales.view','sales.assign']);
                $roleId = $this->ensureRole('sales_executive', 'Sales Executive');
                $this->assignRolePermissions($roleId, ['sales.view','sales.assign']);
                $created = $this->createUsers($prefix, $count, $roleId, $password);
                break;
            case 'verification_executive':
                $this->ensurePermissions(['verification.view','verification.review']);
                $roleId = $this->ensureRole('verification_executive', 'Verification Executive');
                $this->assignRolePermissions($roleId, ['verification.view','verification.review']);
                $created = $this->createUsers($prefix, $count, $roleId, $password);
                break;
            case 'support_executive':
                $this->ensurePermissions(['support.handle']);
                $roleId = $this->ensureRole('support_executive', 'Support Executive');
                $this->assignRolePermissions($roleId, ['support.handle']);
                $created = $this->createUsers($prefix, $count, $roleId, $password);
                break;
            case 'finance_manager':
                $this->ensurePermissions(['payments.view']);
                $roleId = $this->ensureRole('finance_manager', 'Finance Manager');
                $this->assignRolePermissions($roleId, ['payments.view']);
                $created = $this->createUsers($prefix, $count, $roleId, $password);
                break;
            case 'system_auditor':
                $this->ensurePermissions(['audit.view']);
                $roleId = $this->ensureRole('system_auditor', 'System Auditor');
                $this->assignRolePermissions($roleId, ['audit.view']);
                $created = $this->createUsers($prefix, $count, $roleId, $password);
                break;
            default:
                $response->view('masteradmin/system/panel-builder', [
                    'title' => 'Auto-Create Panel Engine',
                    'error' => 'Unsupported panel type'
                ], 422, 'masteradmin/layout');
                return;
        }

        $response->view('masteradmin/system/panel-builder', [
            'title' => 'Auto-Create Panel Engine',
            'success' => 'Created users: ' . implode(', ', $created)
        ], 200, 'masteradmin/layout');
    }

    private function ensurePermissions(array $slugs): void
    {
        $db = Database::getInstance();
        foreach ($slugs as $slug) {
            $row = $db->fetchOne("SELECT id FROM permissions WHERE slug = :slug", ['slug' => $slug]);
            if (!$row) {
                $p = new Permission(['name' => ucfirst(str_replace(['.','_'],' ', $slug)), 'slug' => $slug, 'module' => explode('.', $slug)[0]]);
                $p->save();
            }
        }
    }

    private function ensureRole(string $slug, string $name): int
    {
        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT id FROM roles WHERE slug = :slug", ['slug' => $slug]);
        if ($row) return (int)$row['id'];
        $role = new Role(['name' => $name, 'slug' => $slug]);
        $role->save();
        return (int)$role->id;
    }

    private function assignRolePermissions(int $roleId, array $permSlugs): void
    {
        $db = Database::getInstance();
        foreach ($permSlugs as $slug) {
            $perm = $db->fetchOne("SELECT id FROM permissions WHERE slug = :slug", ['slug' => $slug]);
            if (!$perm) continue;
            $exists = $db->fetchOne("SELECT 1 FROM permission_role WHERE role_id = :rid AND permission_id = :pid", ['rid' => $roleId, 'pid' => (int)$perm['id']]);
            if (!$exists) {
                $db->query("INSERT INTO permission_role (role_id, permission_id) VALUES (:rid, :pid)", ['rid' => $roleId, 'pid' => (int)$perm['id']]);
            }
        }
    }

    private function createUsers(string $prefix, int $count, int $roleId, ?string $password = null): array
    {
        $db = Database::getInstance();
        $created = [];
        $roleRow = $db->fetchOne("SELECT slug FROM roles WHERE id = :id", ['id' => $roleId]);
        $userRole = (string)($roleRow['slug'] ?? 'admin');
        for ($i=1; $i<=$count; $i++) {
            $email = $prefix . $i . '@portal.com';
            $user = $db->fetchOne("SELECT id FROM users WHERE email = :email", ['email' => $email]);
            if ($user) { $created[] = $email; continue; }
            $u = new User(['email' => $email, 'role' => $userRole, 'status' => 'active']);
            $u->setPassword(($password !== null && $password !== '') ? $password : 'password');
            $u->save();
            $db->query("INSERT INTO role_user (user_id, role_id) VALUES (:uid, :rid)", ['uid' => (int)$u->id, 'rid' => $roleId]);
            $created[] = $email;
        }
        return $created;
    }
}
