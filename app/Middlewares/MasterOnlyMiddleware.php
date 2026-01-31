<?php

namespace App\Middleware;

use App\Models\User;
use App\Core\Request;
use App\Core\Response;

class MasterOnlyMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return Response::redirect('/master/login');
        }

        $user = User::find($userId);

        if (!$user || $user->type !== 'master') {
            // no permission – kick to some 403 or main dashboard
            return Response::redirect('/admin/dashboard');
        }

        $request->setUser($user);

        return $next($request);
    }
}
