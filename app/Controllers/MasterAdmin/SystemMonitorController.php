<?php

declare(strict_types=1);

namespace App\Controllers\MasterAdmin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class SystemMonitorController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        $response->view('masteradmin/system/monitor', [
            'title' => 'System Monitor'
        ], 200, 'masteradmin/layout');
    }

    public function trends(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $out = [
            'rpm_1h' => [],
            'rpm_24h' => [],
            'resp_ms_1h' => [],
            'resp_ms_24h' => [],
            'error_1h' => [],
            'error_24h' => [],
            'cpu_1h' => [],
            'cpu_24h' => [],
            'ram_1h' => [],
            'ram_24h' => []
        ];
        try {
            $rows = $db->fetchAll("SELECT message, created_at FROM system_logs WHERE type='monitor_minute' AND created_at >= NOW() - INTERVAL 60 MINUTE ORDER BY created_at ASC");
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $msg = json_decode((string)($row['message'] ?? '{}'), true) ?: [];
                    $out['rpm_1h'][] = (int)($msg['rpm'] ?? 0);
                    $out['resp_ms_1h'][] = (int)($msg['avg_ms'] ?? 0);
                    $out['error_1h'][] = (int)($msg['errors'] ?? 0);
                }
                $missing = 60 - count($out['rpm_1h']);
                for ($k=0; $k<$missing; $k++) { $out['rpm_1h'][] = 0; $out['resp_ms_1h'][] = 0; $out['error_1h'][] = 0; }
            } else {
                for ($i=59; $i>=0; $i--) {
                    $r = $db->fetchOne("SELECT COUNT(*) as c FROM system_logs WHERE type='response_time' AND created_at >= NOW() - INTERVAL :m MINUTE AND created_at < NOW() - INTERVAL :m2 MINUTE", ['m'=>$i+1,'m2'=>$i]);
                    $out['rpm_1h'][] = (int)($r['c'] ?? 0);
                    $a = $db->fetchOne("SELECT AVG(duration_ms) as a FROM system_logs WHERE type='response_time' AND created_at >= NOW() - INTERVAL :m MINUTE AND created_at < NOW() - INTERVAL :m2 MINUTE", ['m'=>$i+1,'m2'=>$i]);
                    $out['resp_ms_1h'][] = (int)($a['a'] ?? 0);
                    $e = $db->fetchOne("SELECT COUNT(*) as c FROM system_logs WHERE type IN ('error','critical') AND created_at >= NOW() - INTERVAL :m MINUTE AND created_at < NOW() - INTERVAL :m2 MINUTE", ['m'=>$i+1,'m2'=>$i]);
                    $out['error_1h'][] = (int)($e['c'] ?? 0);
                }
            }
            for ($i=59; $i>=0; $i--) {
                $s = $db->fetchOne("SELECT message FROM system_logs WHERE type='server_snapshot' AND created_at >= NOW() - INTERVAL :m MINUTE AND created_at < NOW() - INTERVAL :m2 MINUTE ORDER BY created_at DESC LIMIT 1", ['m'=>$i+1,'m2'=>$i]);
                $cpu = null; $ram = null;
                if (!empty($s['message'])) {
                    $data = json_decode((string)$s['message'], true);
                    if (is_array($data)) { $cpu = $data['cpu'] ?? null; $ram = $data['ram'] ?? null; }
                }
                $out['cpu_1h'][] = $cpu ?? null;
                $out['ram_1h'][] = $ram ?? null;
            }
            $rows24 = $db->fetchAll("SELECT message, created_at FROM system_logs WHERE type='monitor_minute' AND created_at >= NOW() - INTERVAL 24 HOUR ORDER BY created_at ASC");
            if (!empty($rows24)) {
                $rpmBuckets = array_fill(0, 24, 0);
                $respBuckets = array_fill(0, 24, 0);
                $respCounts = array_fill(0, 24, 0);
                $errBuckets = array_fill(0, 24, 0);
                $nowTs = time();
                foreach ($rows24 as $row) {
                    $ts = strtotime((string)$row['created_at']);
                    $diffH = (int)floor(($nowTs - $ts) / 3600);
                    $idx = 23 - max(0, min(23, $diffH));
                    $msg = json_decode((string)($row['message'] ?? '{}'), true) ?: [];
                    $rpmBuckets[$idx] += (int)($msg['rpm'] ?? 0);
                    $respBuckets[$idx] += (int)($msg['avg_ms'] ?? 0);
                    $respCounts[$idx] += 1;
                    $errBuckets[$idx] += (int)($msg['errors'] ?? 0);
                }
                for ($i=0; $i<24; $i++) {
                    $out['rpm_24h'][] = $rpmBuckets[$i];
                    $out['resp_ms_24h'][] = $respCounts[$i] > 0 ? (int)round($respBuckets[$i] / $respCounts[$i]) : 0;
                    $out['error_24h'][] = $errBuckets[$i];
                }
            } else {
                for ($i=23; $i>=0; $i--) {
                    $r = $db->fetchOne("SELECT COUNT(*) as c FROM system_logs WHERE type='response_time' AND created_at >= NOW() - INTERVAL :h HOUR AND created_at < NOW() - INTERVAL :h2 HOUR", ['h'=>$i+1,'h2'=>$i]);
                    $out['rpm_24h'][] = (int)($r['c'] ?? 0);
                    $a = $db->fetchOne("SELECT AVG(duration_ms) as a FROM system_logs WHERE type='response_time' AND created_at >= NOW() - INTERVAL :h HOUR AND created_at < NOW() - INTERVAL :h2 HOUR", ['h'=>$i+1,'h2'=>$i]);
                    $out['resp_ms_24h'][] = (int)($a['a'] ?? 0);
                    $e = $db->fetchOne("SELECT COUNT(*) as c FROM system_logs WHERE type IN ('error','critical') AND created_at >= NOW() - INTERVAL :h HOUR AND created_at < NOW() - INTERVAL :h2 HOUR", ['h'=>$i+1,'h2'=>$i]);
                    $out['error_24h'][] = (int)($e['c'] ?? 0);
                }
                for ($i=23; $i>=0; $i--) {
                    $s = $db->fetchOne("SELECT message FROM system_logs WHERE type='server_snapshot' AND created_at >= NOW() - INTERVAL :h HOUR AND created_at < NOW() - INTERVAL :h2 HOUR ORDER BY created_at DESC LIMIT 1", ['h'=>$i+1,'h2'=>$i]);
                    $cpu = null; $ram = null;
                    if (!empty($s['message'])) {
                        $data = json_decode((string)$s['message'], true);
                        if (is_array($data)) { $cpu = $data['cpu'] ?? null; $ram = $data['ram'] ?? null; }
                    }
                    $out['cpu_24h'][] = $cpu ?? null;
                    $out['ram_24h'][] = $ram ?? null;
                }
            }
        } catch (\Throwable $t) {}
        $response->json($out);
    }

    public function dbInsights(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $tables = ['users','jobs','applications','auto_apply_logs','payments'];
        $out = [
            'top_slowest' => [],
            'avg_per_table' => [],
            'slow_trend_1h' => []
        ];
        try {
            $avg = [];
            foreach ($tables as $t) {
                $row = $db->fetchOne("SELECT AVG(duration_ms) as a FROM system_logs WHERE type IN ('query_time','slow_query') AND table_name = :t", ['t'=>$t]);
                $avg[$t] = (float)($row['a'] ?? 0.0);
            }
            arsort($avg);
            $out['avg_per_table'] = $avg;
            $out['top_slowest'] = array_slice(array_keys($avg), 0, 5);
            for ($i=59; $i>=0; $i--) {
                $row = $db->fetchOne("SELECT COUNT(*) as c FROM system_logs WHERE type='slow_query' AND duration_ms >= 1000 AND created_at >= NOW() - INTERVAL :m MINUTE AND created_at < NOW() - INTERVAL :m2 MINUTE", ['m'=>$i+1,'m2'=>$i]);
                $out['slow_trend_1h'][] = (int)($row['c'] ?? 0);
            }
        } catch (\Throwable $t) {}
        $response->json($out);
    }

    public function queueCron(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $out = [
            'processed_per_min' => [],
            'failed_trend_1h' => [],
            'long_running' => [],
            'last_cron' => ['status'=>null,'duration_ms'=>null,'time'=>null]
        ];
        try {
            $hasQueue = $db->fetchOne("SELECT COUNT(*) as c FROM information_schema.tables WHERE table_name = 'queue_jobs'");
            if ((int)($hasQueue['c'] ?? 0) > 0) {
                for ($i=59; $i>=0; $i--) {
                    $p = $db->fetchOne("SELECT COUNT(*) as c FROM queue_jobs WHERE status='processed' AND updated_at >= NOW() - INTERVAL :m MINUTE AND updated_at < NOW() - INTERVAL :m2 MINUTE", ['m'=>$i+1,'m2'=>$i]);
                    $out['processed_per_min'][] = (int)($p['c'] ?? 0);
                    $f = $db->fetchOne("SELECT COUNT(*) as c FROM queue_jobs WHERE status='failed' AND updated_at >= NOW() - INTERVAL :m MINUTE AND updated_at < NOW() - INTERVAL :m2 MINUTE", ['m'=>$i+1,'m2'=>$i]);
                    $out['failed_trend_1h'][] = (int)($f['c'] ?? 0);
                }
                $out['long_running'] = $db->fetchAll("SELECT id, job_type, processing_time_ms, status, updated_at FROM queue_jobs WHERE processing_time_ms IS NOT NULL AND processing_time_ms >= 5000 ORDER BY updated_at DESC LIMIT 10");
            } else {
                for ($i=59; $i>=0; $i--) {
                    $f = $db->fetchOne("SELECT COUNT(*) as c FROM audit_logs WHERE action LIKE '%job_failed%' AND created_at >= NOW() - INTERVAL :m MINUTE AND created_at < NOW() - INTERVAL :m2 MINUTE", ['m'=>$i+1,'m2'=>$i]);
                    $out['failed_trend_1h'][] = (int)($f['c'] ?? 0);
                    $out['processed_per_min'][] = 0;
                }
            }
            $cron = $db->fetchOne("SELECT message, duration_ms, created_at FROM system_logs WHERE type='cron_run' ORDER BY created_at DESC LIMIT 1");
            if ($cron) {
                $out['last_cron'] = [
                    'status' => (string)($cron['message'] ?? ''),
                    'duration_ms' => (int)($cron['duration_ms'] ?? 0),
                    'time' => (string)($cron['created_at'] ?? '')
                ];
            }
        } catch (\Throwable $t) {}
        $response->json($out);
    }

    public function alerts(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $out = ['items' => []];
        try {
            $err = $db->fetchOne("SELECT COUNT(*) as c FROM system_logs WHERE type IN ('error','critical') AND created_at >= NOW() - INTERVAL 10 MINUTE");
            $rpm = $db->fetchOne("SELECT COUNT(*) as c FROM activity_logs WHERE created_at >= NOW() - INTERVAL 1 MINUTE");
            $qf = $db->fetchOne("SELECT COUNT(*) as c FROM queue_jobs WHERE status='failed' AND updated_at >= NOW() - INTERVAL 10 MINUTE");
            $slow = $db->fetchOne("SELECT COUNT(*) as c FROM system_logs WHERE type='slow_query' AND duration_ms >= 1000 AND created_at >= NOW() - INTERVAL 10 MINUTE");
            $snap = $db->fetchOne("SELECT message FROM system_logs WHERE type='server_snapshot' ORDER BY created_at DESC LIMIT 1");
            $cpu = null; $ram = null;
            if (!empty($snap['message'])) {
                $data = json_decode((string)$snap['message'], true);
                if (is_array($data)) { $cpu = $data['cpu'] ?? null; $ram = $data['ram'] ?? null; }
            }
            if ((int)($err['c'] ?? 0) > 5) { $out['items'][] = ['type'=>'error_rate','level'=>'red','value'=>(int)$err['c']]; }
            if ((int)($rpm['c'] ?? 0) > 200) { $out['items'][] = ['type'=>'rpm_spike','level'=>'orange','value'=>(int)$rpm['c']]; }
            if ((int)($qf['c'] ?? 0) > 0) { $out['items'][] = ['type'=>'queue_backlog','level'=>'orange','value'=>(int)$qf['c']]; }
            if ((int)($slow['c'] ?? 0) > 0) { $out['items'][] = ['type'=>'db_slow','level'=>'orange','value'=>(int)$slow['c']]; }
            if (is_numeric($cpu) && $cpu >= 85) { $out['items'][] = ['type'=>'cpu_high','level'=>'red','value'=>$cpu]; }
            if (is_numeric($ram) && $ram >= 85) { $out['items'][] = ['type'=>'ram_high','level'=>'red','value'=>$ram]; }
        } catch (\Throwable $t) {}
        $response->json($out);
    }

    public function trace(Request $request, Response $response): void
    {
        $db = Database::getInstance();
        $rid = (string)$request->get('rid', '');
        $path = (string)$request->get('path', '');
        $out = ['items' => []];
        try {
            if ($rid !== '') {
                $out['items'] = $db->fetchAll("SELECT module, message, duration_ms, created_at FROM system_logs WHERE type='response_time' AND message LIKE :m ORDER BY created_at DESC LIMIT 50", ['m'=>'%'.$rid.'%']);
            } elseif ($path !== '') {
                $out['items'] = $db->fetchAll("SELECT module, message, duration_ms, created_at FROM system_logs WHERE type='response_time' AND module = :p ORDER BY created_at DESC LIMIT 50", ['p'=>$path]);
            } else {
                $out['items'] = $db->fetchAll("SELECT module, message, duration_ms, created_at FROM system_logs WHERE type='response_time' ORDER BY created_at DESC LIMIT 50");
            }
        } catch (\Throwable $t) {}
        $response->json($out);
    }
}
