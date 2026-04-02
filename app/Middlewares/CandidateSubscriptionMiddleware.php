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
        if (!$userId) {
            $response->redirect('/login');
            return;
        }

        $user = User::find((int)$userId);
        if (!$user || !$user->isCandidate()) {
            $response->redirect('/');
            return;
        }

        $candidate = Candidate::findByUserId((int)$userId);
        if (!$candidate) {
            $candidate = Candidate::createForUser((int)$userId);
        }

        $isPremium = (int)($candidate->attributes['is_premium'] ?? 0) === 1;
        $expiresAt = $candidate->attributes['premium_expires_at'] ?? null;
        $expired = $expiresAt ? (strtotime((string)$expiresAt) <= time()) : false;

        if (!$isPremium || $expired) {
            if ($request->isAjax()) {
                $response->json([
                    'error' => 'Premium required',
                    'redirect' => '/candidate/premium/plans'
                ], 402);
            } else {
                $_SESSION['upgrade_message'] = 'Upgrade to Premium to access this feature.';
                $response->redirect('/candidate/premium/plans');
            }
            return;
        }

        $request->setAttribute('candidate', $candidate);
        
        $next($request, $response);
    }
}
