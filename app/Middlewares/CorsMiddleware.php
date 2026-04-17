<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;

class CorsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response, callable $next): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token, Accept, Origin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 86400"); // 24 hours
        header("Vary: Origin");

        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            http_response_code(204);
            exit;
        }

        $next($request, $response);
    }
}
