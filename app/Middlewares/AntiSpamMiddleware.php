<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Job;

class AntiSpamMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) { return; }

        $user = User::find((int)$userId);
        if (!$user || !method_exists($user, 'employer')) { return; }

        $employer = $user->employer();
        if (!$employer) { return; }

        // Simple per-day rate limit
        $todayCount = Job::where('employer_id', '=', $employer->id)
            ->where('created_at', '>=', date('Y-m-d 00:00:00'))
            ->count();

        if ($todayCount >= 10) {
            $response->json(['error' => 'Daily job posting limit reached'], 429);
            return;
        }

        $description = $request->post('description', '');
        $salaryMin = (int)$request->post('salary_min', 0);
        $salaryMax = (int)$request->post('salary_max', 0);

        if ($salaryMin && $salaryMax && $salaryMin > $salaryMax) {
            $response->json(['error' => 'Invalid salary range'], 422);
            return;
        }

        if (preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $description)) {
            $response->json(['error' => 'Email addresses are not allowed in job description'], 422);
            return;
        }

        if (substr_count(strtolower($description), 'security deposit') > 0) {
            $response->json(['error' => 'Potential scam content detected'], 422);
            return;
        }

        return;
    }
}


