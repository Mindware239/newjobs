<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Candidate;

class CandidateSubscriptionMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response, callable $next): void
    {
        $userId = $_SESSION['user_id'] ?? null;

        // 🔒 Not logged in
        if (!$userId) {
            $response->redirect('/login');
            return;
        }

        // 👤 Get user
        $user = User::find((int)$userId);

        if (!$user || !$user->isCandidate()) {
            $response->redirect('/');
            return;
        }

        // 📄 Get candidate profile
        $candidate = Candidate::findByUserId((int)$userId);

        // Auto create candidate profile if missing
        if (!$candidate) {
            $candidate = Candidate::createForUser((int)$userId);
        }

        // 💎 Subscription check
        $isPremium = (int)($candidate->attributes['is_premium'] ?? 0) === 1;
        $expiresAt = $candidate->attributes['premium_expires_at'] ?? null;

        $isExpired = false;
        if ($expiresAt) {
            $isExpired = strtotime((string)$expiresAt) <= time();
        }

        // ❌ Not premium or expired
        if (!$isPremium || $isExpired) {

            // AJAX request
            if ($request->isAjax()) {
                $response->setStatusCode(402);
                $response->json([
                    'error' => 'Premium required',
                    'redirect' => '/candidate/premium/plans'
                ]);
                return;
            }

            // Normal request
            $_SESSION['upgrade_message'] = 'Upgrade to Premium to access this feature.';
            $response->redirect('/candidate/premium/plans');
            return;
        }

        // ✅ Attach candidate to request (useful downstream)
        $request->setAttribute('candidate', $candidate);

        // 🚀 Continue middleware chain
        $next($request, $response);
    }
}