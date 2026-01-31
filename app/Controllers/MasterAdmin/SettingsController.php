<?php

declare(strict_types=1);

namespace App\Controllers\MasterAdmin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Core\RedisClient;
use App\Models\SystemSetting;
use App\Services\ESService;

class SettingsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $app = [
            'name' => $_ENV['APP_NAME'] ?? 'Job Portal',
            'url' => $_ENV['APP_URL'] ?? 'http://localhost:8000',
            'debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
            'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Asia/Kolkata',
            'locale' => $_ENV['APP_LOCALE'] ?? 'en',
        ];

        try {
            $db = Database::getInstance();
            $db->execute("
                CREATE TABLE IF NOT EXISTS system_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    type VARCHAR(50) NOT NULL,
                    module VARCHAR(255) NULL,
                    table_name VARCHAR(255) NULL,
                    message TEXT NULL,
                    user_id INT NULL,
                    duration_ms INT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } catch (\Throwable $t) {}

        $dbStatus = false;
        try {
            $db = Database::getInstance();
            $row = $db->fetchOne('SELECT 1 as ok');
            $dbStatus = (int)($row['ok'] ?? 0) === 1;
        } catch (\Throwable $t) {
            $dbStatus = false;
        }

        $redis = RedisClient::getInstance();
        $redisStatus = $redis->isAvailable();

        $esStatus = false;
        try {
            $es = new ESService();
            $esStatus = true;
        } catch (\Throwable $t) {
            $esStatus = false;
        }

        $googleConfigured = !empty($_ENV['GOOGLE_CLIENT_ID'] ?? '') && !empty($_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
        $appleConfigured = !empty($_ENV['APPLE_CLIENT_ID'] ?? '') && !empty($_ENV['APPLE_KEY_ID'] ?? '');
        $razorpayConfigured = !empty($_ENV['RAZORPAY_KEY'] ?? '') && !empty($_ENV['RAZORPAY_SECRET'] ?? '');

        $autoApply = [
            'enabled' => (int)($_ENV['AUTO_APPLY_ENABLED'] ?? 1),
            'min_match_score' => (int)($_ENV['AUTO_APPLY_MIN_MATCH_SCORE'] ?? 70),
            'max_per_candidate_per_day' => (int)($_ENV['AUTO_APPLY_MAX_PER_CANDIDATE_PER_DAY'] ?? 3),
            'company_cooldown_days' => (int)($_ENV['AUTO_APPLY_COMPANY_COOLDOWN_DAYS'] ?? 30),
            'daily_global_limit' => (int)($_ENV['AUTO_APPLY_DAILY_GLOBAL_LIMIT'] ?? 1000),
            'min_profile_strength' => (int)($_ENV['AUTO_APPLY_MIN_PROFILE_STRENGTH'] ?? 60),
            'mandatory_sections' => (string)($_ENV['AUTO_APPLY_MANDATORY_SECTIONS'] ?? ''),
            'pause_rejections_threshold' => (int)($_ENV['AUTO_APPLY_PAUSE_REJECTIONS_THRESHOLD'] ?? 0),
            'pause_rejections_days' => (int)($_ENV['AUTO_APPLY_PAUSE_REJECTIONS_DAYS'] ?? 30),
            'blacklist_candidates' => (string)($_ENV['AUTO_APPLY_BLACKLIST_CANDIDATES'] ?? ''),
            'blacklist_employers' => (string)($_ENV['AUTO_APPLY_BLACKLIST_EMPLOYERS'] ?? '')
        ];
        $metrics = [
            'auto_applies_today' => 0,
            'failed_auto_applies_today' => 0,
            'avg_match_score_today' => 0.0,
            'load_alert' => false
        ];
        try {
            $db = Database::getInstance();
            $rowA = $db->fetchOne("SELECT COUNT(*) as c, AVG(match_score) as avg FROM applications WHERE application_method = 'auto' AND DATE(applied_at) = CURDATE()");
            $metrics['auto_applies_today'] = (int)($rowA['c'] ?? 0);
            $metrics['avg_match_score_today'] = (float)($rowA['avg'] ?? 0.0);
            $rowF = $db->fetchOne("SELECT COUNT(*) as c FROM audit_logs WHERE action = 'auto_apply_error' AND DATE(created_at) = CURDATE()");
            $metrics['failed_auto_applies_today'] = (int)($rowF['c'] ?? 0);
            $limit = (int)$autoApply['daily_global_limit'];
            $metrics['load_alert'] = ($limit > 0) ? ($metrics['auto_applies_today'] >= (int)floor($limit * 0.8)) : false;
        } catch (\Throwable $t) {}

        $liveEvents = [];
        try {
            $db = Database::getInstance();
            $rows = $db->fetchAll("SELECT type, module, message, created_at FROM system_logs ORDER BY created_at DESC LIMIT 20");
            foreach ($rows as $r) {
                $type = (string)($r['type'] ?? 'info');
                $title = (string)($r['message'] ?? ($r['module'] ?? 'system event'));
                $liveEvents[] = [
                    'title' => $title,
                    'time' => date('h:i:s A', strtotime((string)$r['created_at'])),
                    'type' => $type,
                    'new' => (time() - strtotime((string)$r['created_at'])) < 600
                ];
            }
            usort($liveEvents, function($x,$y){ return strtotime($y['time']) <=> strtotime($x['time']); });
            $liveEvents = array_slice($liveEvents, 0, 20);
        } catch (\Throwable $t) {}

        $response->view('masteradmin/settings/index', [
            'title' => 'System Settings',
            'app' => $app,
            'status' => [
                'database' => $dbStatus,
                'redis' => $redisStatus,
                'elasticsearch' => $esStatus,
                'google_oauth' => $googleConfigured,
                'apple_oauth' => $appleConfigured,
                'razorpay' => $razorpayConfigured,
            ],
            'autoApply' => $autoApply,
            'metrics' => $metrics,
            'liveEvents' => $liveEvents
        ], 200, 'masteradmin/layout');
    }

    public function live(Request $request, Response $response): void
    {
        $out = [
            'active_users_total' => 0,
            'active_users_roles' => ['Admin'=>0,'Employer'=>0,'Candidate'=>0],
            'rpm_current' => 0,
            'rpm_peak_5m' => 0,
            'errors' => [],
            'queue' => ['queued'=>0,'failed'=>0,'avg_ms'=>0],
            'db_slow' => [],
            'server' => ['cpu'=>null,'ram'=>null,'disk'=>null],
            'tables' => []
        ];
        try {
            $db = Database::getInstance();
            // Active users in last 5 minutes by role
            $users = $db->fetchAll("SELECT DISTINCT u.id, u.role FROM activity_logs al LEFT JOIN users u ON u.id = al.user_id WHERE al.created_at >= NOW() - INTERVAL 5 MINUTE");
            $out['active_users_total'] = count($users);
            foreach ($users as $u) {
                $r = ucfirst((string)($u['role'] ?? ''));
                if (isset($out['active_users_roles'][$r])) { $out['active_users_roles'][$r]++; }
            }
            // RPM current and peak (last 5 minutes)
            $perMin = [];
            for ($i=0;$i<5;$i++){
                $row = $db->fetchOne("SELECT COUNT(*) as c FROM activity_logs WHERE created_at >= NOW() - INTERVAL ".($i+1)." MINUTE AND created_at < NOW() - INTERVAL ".$i." MINUTE");
                $perMin[] = (int)($row['c'] ?? 0);
            }
            $out['rpm_current'] = $perMin[0] ?? 0;
            $out['rpm_peak_5m'] = max($perMin);
            // Errors (latest 10)
            $out['errors'] = $db->fetchAll("SELECT type, module, message, created_at FROM system_logs WHERE type IN ('error','critical') ORDER BY created_at DESC LIMIT 10");
            // Queue metrics
            $hasQueue = $db->fetchOne("SELECT COUNT(*) as c FROM information_schema.tables WHERE table_name = 'queue_jobs'");
            if ((int)($hasQueue['c'] ?? 0) > 0) {
                $q = $db->fetchOne("SELECT COUNT(*) as c FROM queue_jobs WHERE status IN ('queued','pending')");
                $f = $db->fetchOne("SELECT COUNT(*) as c FROM queue_jobs WHERE status = 'failed'");
                $avg = $db->fetchOne("SELECT AVG(processing_time_ms) as a FROM queue_jobs WHERE processing_time_ms IS NOT NULL AND created_at >= NOW() - INTERVAL 1 DAY");
                $out['queue'] = ['queued'=>(int)($q['c'] ?? 0),'failed'=>(int)($f['c'] ?? 0),'avg_ms'=>(int)($avg['a'] ?? 0)];
            } else {
                $failAudit = $db->fetchOne("SELECT COUNT(*) as c FROM audit_logs WHERE action LIKE '%job_failed%' AND created_at >= NOW() - INTERVAL 1 DAY");
                $out['queue']['failed'] = (int)($failAudit['c'] ?? 0);
            }
            // DB slow queries via system_logs
            $out['db_slow'] = $db->fetchAll("SELECT table_name, duration_ms, created_at FROM system_logs WHERE type='slow_query' AND duration_ms >= 1000 ORDER BY created_at DESC LIMIT 10");
            // Server snapshot
            $cpu = null; $ram = null; $disk = null;
            if (function_exists('sys_getloadavg')) {
                $load = sys_getloadavg();
                $cores = (int)($_ENV['CPU_CORES'] ?? ($_SERVER['NUMBER_OF_PROCESSORS'] ?? 1));
                $cpu = $cores > 0 ? min(100, round(($load[0] / $cores) * 100)) : null;
            }
            $totalDisk = @disk_total_space(__DIR__ . '/../../../') ?: 0;
            $freeDisk = @disk_free_space(__DIR__ . '/../../../') ?: 0;
            if ($totalDisk > 0) { $disk = round((($totalDisk - $freeDisk) / $totalDisk) * 100); }
            $ram = null; // OS-level RAM requires extensions; can be added later
            $out['server'] = ['cpu'=>$cpu,'ram'=>$ram,'disk'=>$disk];
            // Database load analysis for specific tables
            $tables = ['users','jobs','applications','auto_apply_logs','payments','system_logs'];
            foreach ($tables as $t){
                $exists = $db->fetchOne("SELECT COUNT(*) as c FROM information_schema.tables WHERE table_name = ?", [$t]);
                if ((int)($exists['c'] ?? 0) === 0) { continue; }
                $total = $db->fetchOne("SELECT COUNT(*) as c FROM {$t}");
                $ins = $db->fetchOne("SELECT COUNT(*) as c FROM {$t} WHERE created_at >= NOW() - INTERVAL 1 MINUTE");
                $upd = $db->fetchOne("SELECT COUNT(*) as c FROM {$t} WHERE updated_at >= NOW() - INTERVAL 1 MINUTE");
                $avg = $db->fetchOne("SELECT AVG(duration_ms) as a FROM system_logs WHERE type='query_time' AND table_name = ?", [$t]);
                $out['tables'][$t] = [
                    'rows'=>(int)($total['c'] ?? 0),
                    'ins_min'=>(int)($ins['c'] ?? 0),
                    'upd_min'=>(int)($upd['c'] ?? 0),
                    'avg_ms'=>(float)($avg['a'] ?? 0.0)
                ];
            }
        } catch (\Throwable $t) {}
        $response->json($out);
    }

    public function update(Request $request, Response $response): void
    {
        $name = trim((string)$request->post('app_name', ''));
        $url = trim((string)$request->post('app_url', ''));
        $timezone = trim((string)$request->post('app_timezone', ''));
        $locale = trim((string)$request->post('app_locale', ''));
        $debug = (string)$request->post('app_debug', 'false') === 'true' ? 'true' : 'false';

        // Save System Settings
        $footer = trim((string)$request->post('email_footer', ''));
        if ($footer !== '') {
            SystemSetting::set('email_footer', $footer);
        }

        $pairs = [];
        if ($name !== '') { $pairs['APP_NAME'] = $name; }
        if ($url !== '') { $pairs['APP_URL'] = rtrim($url, '/'); }
        if ($timezone !== '') { $pairs['APP_TIMEZONE'] = $timezone; }
        if ($locale !== '') { $pairs['APP_LOCALE'] = $locale; }
        $pairs['APP_DEBUG'] = $debug;
        $pairs['AUTO_APPLY_ENABLED'] = $request->post('auto_apply_enabled') ? '1' : '0';
        $pairs['AUTO_APPLY_MIN_MATCH_SCORE'] = (string)(int)$request->post('auto_apply_min_match_score', (string)($_ENV['AUTO_APPLY_MIN_MATCH_SCORE'] ?? '70'));
        $pairs['AUTO_APPLY_MAX_PER_CANDIDATE_PER_DAY'] = (string)(int)$request->post('auto_apply_max_per_candidate_per_day', (string)($_ENV['AUTO_APPLY_MAX_PER_CANDIDATE_PER_DAY'] ?? '3'));
        $pairs['AUTO_APPLY_COMPANY_COOLDOWN_DAYS'] = (string)(int)$request->post('auto_apply_company_cooldown_days', (string)($_ENV['AUTO_APPLY_COMPANY_COOLDOWN_DAYS'] ?? '30'));
        $pairs['AUTO_APPLY_DAILY_GLOBAL_LIMIT'] = (string)(int)$request->post('auto_apply_daily_global_limit', (string)($_ENV['AUTO_APPLY_DAILY_GLOBAL_LIMIT'] ?? '1000'));
        $pairs['AUTO_APPLY_MIN_PROFILE_STRENGTH'] = (string)(int)$request->post('auto_apply_min_profile_strength', (string)($_ENV['AUTO_APPLY_MIN_PROFILE_STRENGTH'] ?? '60'));
        $pairs['AUTO_APPLY_MANDATORY_SECTIONS'] = (string)$request->post('auto_apply_mandatory_sections', (string)($_ENV['AUTO_APPLY_MANDATORY_SECTIONS'] ?? ''));
        $pairs['AUTO_APPLY_PAUSE_REJECTIONS_THRESHOLD'] = (string)(int)$request->post('auto_apply_pause_rejections_threshold', (string)($_ENV['AUTO_APPLY_PAUSE_REJECTIONS_THRESHOLD'] ?? '0'));
        $pairs['AUTO_APPLY_PAUSE_REJECTIONS_DAYS'] = (string)(int)$request->post('auto_apply_pause_rejections_days', (string)($_ENV['AUTO_APPLY_PAUSE_REJECTIONS_DAYS'] ?? '30'));
        $pairs['AUTO_APPLY_BLACKLIST_CANDIDATES'] = (string)$request->post('auto_apply_blacklist_candidates', (string)($_ENV['AUTO_APPLY_BLACKLIST_CANDIDATES'] ?? ''));
        $pairs['AUTO_APPLY_BLACKLIST_EMPLOYERS'] = (string)$request->post('auto_apply_blacklist_employers', (string)($_ENV['AUTO_APPLY_BLACKLIST_EMPLOYERS'] ?? ''));

        // Persist to SystemSetting for dynamic reads
        SystemSetting::set('auto_apply_enabled', $pairs['AUTO_APPLY_ENABLED'], 'auto_apply');
        SystemSetting::set('auto_apply_min_match_score', $pairs['AUTO_APPLY_MIN_MATCH_SCORE'], 'auto_apply');
        SystemSetting::set('auto_apply_max_per_candidate_per_day', $pairs['AUTO_APPLY_MAX_PER_CANDIDATE_PER_DAY'], 'auto_apply');
        SystemSetting::set('auto_apply_company_cooldown_days', $pairs['AUTO_APPLY_COMPANY_COOLDOWN_DAYS'], 'auto_apply');
        SystemSetting::set('auto_apply_daily_global_limit', $pairs['AUTO_APPLY_DAILY_GLOBAL_LIMIT'], 'auto_apply');
        SystemSetting::set('auto_apply_min_profile_strength', $pairs['AUTO_APPLY_MIN_PROFILE_STRENGTH'], 'auto_apply');
        SystemSetting::set('auto_apply_mandatory_sections', $pairs['AUTO_APPLY_MANDATORY_SECTIONS'], 'auto_apply');
        SystemSetting::set('auto_apply_pause_rejections_threshold', $pairs['AUTO_APPLY_PAUSE_REJECTIONS_THRESHOLD'], 'auto_apply');
        SystemSetting::set('auto_apply_pause_rejections_days', $pairs['AUTO_APPLY_PAUSE_REJECTIONS_DAYS'], 'auto_apply');
        SystemSetting::set('auto_apply_blacklist_candidates', $pairs['AUTO_APPLY_BLACKLIST_CANDIDATES'], 'auto_apply');
        SystemSetting::set('auto_apply_blacklist_employers', $pairs['AUTO_APPLY_BLACKLIST_EMPLOYERS'], 'auto_apply');

        $this->writeEnv($pairs);
        $response->redirect('/master/settings');
    }

    private function writeEnv(array $pairs): void
    {
        $envPath = __DIR__ . '/../../../.env';
        $existing = file_exists($envPath) ? file_get_contents($envPath) : '';
        $lines = $existing !== '' ? preg_split('/\r?\n/', $existing) : [];
        $map = [];
        foreach ($lines as $line) {
            if ($line === '' || strpos($line, '=') === false || str_starts_with(trim($line), '#')) continue;
            $pos = strpos($line, '=');
            $k = substr($line, 0, $pos);
            $map[$k] = $line;
        }
        foreach ($pairs as $k => $v) {
            $val = preg_match('/\s/', $v) ? '"' . $v . '"' : $v;
            $map[$k] = $k . '=' . $val;
        }
        $out = [];
        $seen = [];
        foreach ($lines as $line) {
            if ($line === '' || strpos($line, '=') === false || str_starts_with(trim($line), '#')) { $out[] = $line; continue; }
            $pos = strpos($line, '=');
            $k = substr($line, 0, $pos);
            if (isset($pairs[$k])) {
                $out[] = $map[$k];
                $seen[$k] = true;
            } else {
                $out[] = $line;
            }
        }
        foreach ($pairs as $k => $v) {
            if (!isset($seen[$k])) $out[] = $map[$k];
        }
        file_put_contents($envPath, implode(PHP_EOL, $out));
    }
}
