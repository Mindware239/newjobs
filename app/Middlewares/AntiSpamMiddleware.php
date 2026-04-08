<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Job;

class AntiSpamMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response, callable $next = null): void
    {
        $userId = $_SESSION['user_id'] ?? null;

        // Not logged in → skip
        if (!$userId) {
            if ($next) {
                $next($request, $response);
            }
            return;
        }

        $user = User::find((int)$userId);

        if (!$user || !method_exists($user, 'employer')) {
            if ($next) {
                $next($request, $response);
            }
            return;
        }

        $employer = $user->employer();

        if (!$employer) {
            if ($next) {
                $next($request, $response);
            }
            return;
        }

        // Daily limit check
        $todayCount = Job::where('employer_id', '=', $employer->id)
            ->where('created_at', '>=', date('Y-m-d 00:00:00'))
            ->count();

        if ($todayCount >= 10) {
            $response->setStatusCode(429);
            $response->json(['error' => 'Daily job posting limit reached']);
            return;
        }

        // Input validation
        $description = $request->post('description', '');
        $salaryMin = (int)$request->post('salary_min', 0);
        $salaryMax = (int)$request->post('salary_max', 0);

        if ($salaryMin && $salaryMax && $salaryMin > $salaryMax) {
            $response->setStatusCode(422);
            $response->json(['error' => 'Invalid salary range']);
            return;
        }

        // Email detection
        if (preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $description)) {
            $response->setStatusCode(422);
            $response->json(['error' => 'Email addresses are not allowed in job description']);
            return;
        }

        // Scam keyword
        if (substr_count(strtolower($description), 'security deposit') > 0) {
            $response->setStatusCode(422);
            $response->json(['error' => 'Potential scam content detected']);
            return;
        }

        // Continue safely
        if ($next) {
            $next($request, $response);
        }
    }
}