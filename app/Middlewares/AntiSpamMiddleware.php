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

        // Daily limit check (new jobs only – editing an existing job is not a new posting)
        if ($request->getMethod() === 'POST') {
            $todayCount = Job::where('employer_id', '=', $employer->id)
                ->where('created_at', '>=', date('Y-m-d 00:00:00'))
                ->count();

            if ($todayCount >= 10) {
                $response->json(['error' => 'daily_limit', 'message' => 'You can post up to 10 jobs per day. Please try again tomorrow.'], 429);
                return;
            }
        }

        // Input validation
        $description = $request->post('description', '');
        $salaryMin = (int)$request->post('salary_min', 0);
        $salaryMax = (int)$request->post('salary_max', 0);

        if ($salaryMin && $salaryMax && $salaryMin > $salaryMax) {
            $this->reject($response, 'salary_max', 'Maximum salary cannot be less than the minimum salary.');
            return;
        }

        // Email detection (candidates must apply through the portal)
        if (preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', strip_tags((string)$description))) {
            $this->reject($response, 'description', 'Please remove email addresses from the job description – candidates apply through Jobsence.');
            return;
        }

        // Scam keywords (asking candidates for money)
        $lower = strtolower(strip_tags((string)$description));
        foreach (['security deposit', 'registration fee', 'pay to apply', 'refundable deposit'] as $phrase) {
            if (str_contains($lower, $phrase)) {
                $this->reject($response, 'description', 'Job posts cannot ask candidates to pay money (found: "' . $phrase . '"). Please edit the description.');
                return;
            }
        }

        // Continue safely
        if ($next) {
            $next($request, $response);
        }
    }

    private function reject(Response $response, string $field, string $message): void
    {
        $response->json(['error' => 'validation_failed', 'message' => $message, 'errors' => [$field => $message]], 422);
    }
}