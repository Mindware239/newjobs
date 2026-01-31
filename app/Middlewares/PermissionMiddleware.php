<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class PermissionMiddleware
{
    protected string $permission;

    public function __construct(string $permission)
    {
        $this->permission = $permission;
    }

    public function handle(Request $request, \Closure $next)
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission($this->permission)) {
            return Response::view('errors/403.php', [], 403);
        }

        return $next($request);
    }
}
