<?php

declare(strict_types=1);

namespace App\Controllers\Api\Candidate;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Candidate;
use App\Models\SubscriptionPlan;
use App\Models\CandidatePremiumPurchase;

class PremiumController extends ApiController
{
    public function plans(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $plans = SubscriptionPlan::getActivePlansFor('candidate');
        
        $this->success($response, [
            'plans' => $plans
        ], 'Plans retrieved successfully');
    }

    public function initiatePayment(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $body = $request->getJsonBody();
        $planId = $body['plan_id'] ?? null;
        if (!$planId) {
            $this->error($response, 'Plan ID is required', 400);
            return;
        }

        // Dummy order creation
        $this->success($response, [
            'order_id' => 'order_' . time(),
            'amount' => 1000,
            'currency' => 'INR'
        ], 'Payment initiated');
    }

    public function billing(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $candidate = Candidate::findByUserId((int)$user->id);
        if (!$candidate) {
            $this->error($response, 'Candidate not found', 404);
            return;
        }

        $history = CandidatePremiumPurchase::where('candidate_id', '=', $candidate->attributes['id'])
            ->orderBy('created_at', 'DESC')
            ->get();

        $this->success($response, [
            'history' => $history
        ], 'Billing history retrieved');
    }
}
