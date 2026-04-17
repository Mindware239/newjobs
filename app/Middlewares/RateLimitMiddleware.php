<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Core\RedisClient;
use App\Core\Logger;

class RateLimitMiddleware implements MiddlewareInterface
{
    private int $maxRequests;
    private int $windowSeconds;
    private string $prefix;

    public function __construct(int $maxRequests = 60, int $windowSeconds = 60, string $prefix = 'default')
    {
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
        $this->prefix = $prefix;
    }

    public function handle(Request $request, Response $response, ?callable $next = null): void
    {
        $redis = RedisClient::getInstance();
        
        if (!$redis->isAvailable()) {
            if (is_callable($next)) {
                $next($request, $response);
            }
            return;
        }

        $identifier = $this->getIdentifier($request);
        $key = "ratelimit:{$this->prefix}:{$identifier}";

        try {
            $conn = $redis->getConnection();
            $current = $conn->get($key);

            if ($current === false) {
                $conn->setex($key, $this->windowSeconds, 1);
            } elseif ((int)$current >= $this->maxRequests) {
                Logger::info("Rate limit exceeded for {$identifier} on {$this->prefix}");
                
                $response->setStatusCode(429);
                $response->setHeader('Retry-After', (string)$this->windowSeconds);
                $response->json([], 429, "Too many requests. Please try again later.", false);
                return;
            } else {
                $conn->incr($key);
            }

            // Set rate limit headers
            $remaining = $this->maxRequests - ((int)$current + 1);
            $response->setHeader('X-RateLimit-Limit', (string)$this->maxRequests);
            $response->setHeader('X-RateLimit-Remaining', (string)max(0, $remaining));

        } catch (\Throwable $e) {
            Logger::error("RateLimit Error: " . $e->getMessage());
        }

        if (is_callable($next)) {
            $next($request, $response);
        }
    }

    private function getIdentifier(Request $request): string
    {
        // Prioritize User ID if authenticated, fallback to IP
        $user = $request->user();
        if ($user) {
            return "user:{$user->id}";
        }

        return "ip:" . $request->ip();
    }
}
