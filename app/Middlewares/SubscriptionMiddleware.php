<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Models\EmployerSubscription;

class SubscriptionMiddleware
{
    private string $requiredFeature;
    private bool $allowGracePeriod;

    public function __construct(string $requiredFeature = '', bool $allowGracePeriod = true)
    {
        $this->requiredFeature = $requiredFeature;
        $this->allowGracePeriod = $allowGracePeriod;
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->redirect('/login');
            return;
        }

        // Get employer
        $user = \App\Models\User::find($userId);
        if (!$user || !$user->isEmployer()) {
            $response->json(['error' => 'Access denied. Employer account required.'], 403);
            return;
        }

        $employer = $user->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found.'], 404);
            return;
        }

        // Get current subscription
        $subscription = EmployerSubscription::getCurrentForEmployer($employer->id);
        
        if (!$subscription) {
            // No active subscription - redirect to plans
            if ($request->isAjax()) {
                $response->json([
                    'error' => 'Subscription required',
                    'redirect' => '/employer/subscription/plans'
                ], 402);
            } else {
                $response->redirect('/employer/subscription/plans?feature=' . urlencode($this->requiredFeature));
            }
            return;
        }

        // Check if feature is required
        if ($this->requiredFeature) {
            if (!$subscription->canAccessFeature($this->requiredFeature)) {
                if ($request->isAjax()) {
                    $response->json([
                        'error' => 'Premium feature. Please upgrade your plan.',
                        'feature' => $this->requiredFeature,
                        'redirect' => '/employer/subscription/plans?upgrade=1'
                    ], 402);
                } else {
                    $response->redirect('/employer/subscription/plans?upgrade=1&feature=' . urlencode($this->requiredFeature));
                }
                return;
            }
        }

        // Check grace period
        if (!$this->allowGracePeriod && $subscription->isInGracePeriod()) {
            if ($request->isAjax()) {
                $response->json([
                    'error' => 'Subscription expired. Please renew to continue.',
                    'redirect' => '/employer/subscription/renew'
                ], 402);
            } else {
                $response->redirect('/employer/subscription/renew');
            }
            return;
        }

        // Attach subscription to request for use in controllers
        $request->setAttribute('subscription', $subscription);
        $request->setAttribute('employer', $employer);

        $next($request, $response);
    }
}

