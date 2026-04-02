<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;

class PermissionMiddleware implements MiddlewareInterface
{
    protected string $permission;

    public function __construct(string $permission)
    {
        $this->permission = $permission;
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $user = $request->user();
        if (!$user || !$user->can($this->permission)) {
             $response->setStatusCode(403);
             $response->view('errors/403', ['title' => 'Forbidden']);
             return;
        }

        $next($request, $response);
    }
}
