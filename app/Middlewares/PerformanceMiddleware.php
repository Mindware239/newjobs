<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\User;

class PerformanceMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response): void
    {
        $start = microtime(true);
        $rid = bin2hex(random_bytes(8));
        $response->setHeader('X-Request-Id', $rid);
        $role = null;
        try {
            $uid = (int)($_SESSION['user_id'] ?? 0);
            if ($uid > 0) {
                $u = User::find($uid);
                if ($u) { $role = $u->role ?? null; }
            }
        } catch (\Throwable $t) {}
        register_shutdown_function(function() use ($request, $start) {
            $duration = (int)round((microtime(true) - $start) * 1000);
            try {
                $db = Database::getInstance();
                $db->query(
                    "INSERT INTO system_logs (type, module, message, user_id, duration_ms, created_at)
                     VALUES (:type, :module, :message, :user_id, :duration_ms, NOW())",
                    [
                        'type' => 'response_time',
                        'module' => $request->getPath(),
                        'message' => json_encode([
                            'request_id' => $rid,
                            'status' => http_response_code(),
                            'role' => $role
                        ]),
                        'user_id' => (int)($_SESSION['user_id'] ?? 0),
                        'duration_ms' => $duration
                    ]
                );
            } catch (\Throwable $t) {}
        });
        try {
            $cpu = null; $ram = null; $disk = null;
            if (function_exists('sys_getloadavg')) {
                $load = sys_getloadavg();
                $cores = (int)($_ENV['CPU_CORES'] ?? ($_SERVER['NUMBER_OF_PROCESSORS'] ?? 1));
                $cpu = $cores > 0 ? min(100, round(($load[0] / $cores) * 100)) : null;
            }
            $totalDisk = @disk_total_space(__DIR__ . '/../../') ?: 0;
            $freeDisk = @disk_free_space(__DIR__ . '/../../') ?: 0;
            if ($totalDisk > 0) { $disk = round((($totalDisk - $freeDisk) / $totalDisk) * 100); }
            $db = Database::getInstance();
            $db->query(
                "INSERT INTO system_logs (type, module, message, user_id, created_at)
                 VALUES (:type, :module, :message, :user_id, NOW())",
                [
                    'type' => 'server_snapshot',
                    'module' => $request->getPath(),
                    'message' => json_encode(['cpu'=>$cpu,'ram'=>$ram,'disk'=>$disk]),
                    'user_id' => (int)($_SESSION['user_id'] ?? 0)
                ]
            );
        } catch (\Throwable $t) {}
    }
}
