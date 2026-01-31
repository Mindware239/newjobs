<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class IpWhitelistMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($ip === '') {
            $response->setStatusCode(403);
            $response->json(['error' => 'IP not detected']);
            return;
        }

        try {
            $db = Database::getInstance();
            $rows = $db->fetchAll('SELECT ip_address FROM ip_whitelist WHERE active = 1');
            if (!$rows || count($rows) === 0) {
                return; // no whitelist configured => allow
            }
            $allowed = array_map(fn($r) => $r['ip_address'], $rows);
            if (!in_array($ip, $allowed, true)) {
                $response->setStatusCode(403);
                $response->json(['error' => 'Access denied from IP']);
                return;
            }
        } catch (\Throwable $t) {
            // fail open to avoid locking admin out if table missing
            return;
        }
    }
}

