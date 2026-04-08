<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\RedisClient;

class RateLimitMiddleware implements MiddlewareInterface
{
    private int $maxRequests;
    private int $windowSeconds;
    private RedisClient $redis;

    public function __construct(int $maxRequests = 100, int $windowSeconds = 60)
    {
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
        $this->redis = RedisClient::getInstance();
    }

    public function handle(Request $request, Response $response, ?callable $next = null): void
    {
        // Skip rate limiting if Redis is not available
        if (!$this->redis->isAvailable()) {
            if (is_callable($next)) {
                $next($request, $response);
            }
            return;
        }

        $identifier = $this->getIdentifier($request);
        $key = "rate_limit:" . $identifier;

        $connection = $this->redis->getConnection();
        if (!$connection) {
            if (is_callable($next)) {
                $next($request, $response);
            }
            return;
        }

        $current = $connection->get($key);
        
        if ($current === false) {
            $connection->setex($key, $this->windowSeconds, 1);
            if (is_callable($next)) {
                $next($request, $response);
            }
            return;
        }

        $count = (int)$current;
        
        if ($count >= $this->maxRequests) {
            $response->setStatusCode(429);
            $roleLimitLimit = (string)$this->maxRequests;
            $response->setHeader('X-RateLimit-Limit', $roleLimitLimit);
            $response->setHeader('X-RateLimit-Remaining', '0');
            $response->setHeader('Retry-After', (string)$this->windowSeconds);
            $response->json(['error' => 'Too many requests']);
            return;
        }

        $connection->incr($key);
        $remaining = $this->maxRequests - ($count + 1);
        
        $response->setHeader('X-RateLimit-Limit', (string)$this->maxRequests);
        $response->setHeader('X-RateLimit-Remaining', (string)$remaining);

        if (is_callable($next)) {
            $next($request, $response);
        }
    }

    private function getIdentifier(Request $request): string
    {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            return 'user:' . $userId;
        }

        return 'ip:' . $request->ip();
    }
}

