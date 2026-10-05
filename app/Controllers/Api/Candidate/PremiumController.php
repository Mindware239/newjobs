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
            'plans' => array_map(fn(SubscriptionPlan $plan): array => $this->formatPlanForApi($plan), $plans)
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

        $plan = SubscriptionPlan::findFor((int)$planId, 'candidate');
        if (!$plan) {
            $this->candidatePlanNotFound($response, (int)$planId);
            return;
        }
        if (strtolower((string)$plan->plan_for) !== 'candidate') {
            $this->error($response, 'Selected plan is not available for candidates', 422);
            return;
        }

        // Real order creation logic would go here (Razorpay/Cashfree)
        $this->success($response, [
            'order_id' => 'order_' . time(),
            'plan_id' => (int)$planId,
            'amount' => (float)$plan->price_monthly,
            'currency' => 'INR',
            'plan' => $this->formatPlanForApi($plan),
            'key' => $_ENV['RAZORPAY_KEY'] ?? ''
        ], 'Payment initiated');
    }

    public function verifyPayment(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $body = $request->getJsonBody();
        $errors = $this->validate($body, [
            'order_id' => 'required',
            'payment_id' => 'required'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        // Logic to verify payment and activate premium
        $candidate = Candidate::findByUserId((int)$user->id);
        if ($candidate) {
            $plan = null;
            if (!empty($body['plan_id'])) {
                $plan = SubscriptionPlan::findFor((int)$body['plan_id'], 'candidate');
                if (!$plan) {
                    $this->candidatePlanNotFound($response, (int)$body['plan_id']);
                    return;
                }
            }

            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
            $candidate->is_premium = 1;
            $candidate->premium_expires_at = $expiresAt;
            $candidate->save();

            // Log purchase
            $purchase = new CandidatePremiumPurchase();
            $purchase->fill([
                'candidate_id' => $candidate->id,
                'plan_type' => $plan->slug ?? $plan->name ?? (string)($body['plan_type'] ?? 'candidate_premium'),
                'amount' => (float)($body['amount'] ?? $plan->price_monthly ?? 0),
                'payment_method' => (string)($body['payment_method'] ?? 'online'),
                'payment_id' => (string)$body['payment_id'],
                'status' => 'completed',
                'expires_at' => $expiresAt
            ])->save();
        }

        $this->success($response, [], 'Payment verified and Premium activated');
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

    private function formatPlanForApi(SubscriptionPlan $plan): array
    {
        $data = $plan->toArray();
        $priceMonthly = (float)($data['price_monthly'] ?? 0);
        $priceQuarterly = (float)($data['price_quarterly'] ?? 0);
        $priceAnnual = (float)($data['price_annual'] ?? 0);

        return [
            'id' => (int)($data['id'] ?? 0),
            'name' => (string)($data['name'] ?? ''),
            'slug' => $this->normalizeSlug((string)($data['slug'] ?? ''), (string)($data['name'] ?? '')),
            'plan_for' => (string)($data['plan_for'] ?? 'candidate'),
            'tier' => $this->normalizeTier((string)($data['tier'] ?? ''), $priceMonthly, $priceQuarterly, $priceAnnual),
            'description' => (string)($data['description'] ?? ''),
            'price_monthly' => $priceMonthly,
            'price_quarterly' => $priceQuarterly,
            'price_annual' => $priceAnnual,
            'default_billing_cycle' => (string)($data['default_billing_cycle'] ?? 'monthly'),
            'currency' => (string)($data['currency'] ?? 'INR'),
            'max_job_posts' => (int)($data['max_job_posts'] ?? 0),
            'max_contacts_per_month' => (int)($data['max_contacts_per_month'] ?? 0),
            'max_resume_downloads' => (int)($data['max_resume_downloads'] ?? 0),
            'max_chat_messages' => (int)($data['max_chat_messages'] ?? 0),
            'job_post_boost' => (bool)($data['job_post_boost'] ?? false),
            'priority_support' => (bool)($data['priority_support'] ?? false),
            'advanced_filters' => (bool)($data['advanced_filters'] ?? false),
            'candidate_mobile_visible' => (bool)($data['candidate_mobile_visible'] ?? false),
            'resume_download_enabled' => (bool)($data['resume_download_enabled'] ?? false),
            'chat_enabled' => (bool)($data['chat_enabled'] ?? false),
            'ai_matching' => (bool)($data['ai_matching'] ?? false),
            'analytics_dashboard' => (bool)($data['analytics_dashboard'] ?? false),
            'custom_branding' => (bool)($data['custom_branding'] ?? false),
            'api_access' => (bool)($data['api_access'] ?? false),
            'trial_days' => (int)($data['trial_days'] ?? 0),
            'trial_enabled' => (bool)($data['trial_enabled'] ?? false),
            'discount_percentage' => (float)($data['discount_percentage'] ?? 0),
            'discount_valid_until' => $data['discount_valid_until'] ?? null,
            'is_active' => (bool)($data['is_active'] ?? false),
            'is_featured' => (bool)($data['is_featured'] ?? false),
            'sort_order' => (int)($data['sort_order'] ?? 0),
            'features' => $this->decodeFeatures($data['features'] ?? []),
            'created_at' => $data['created_at'] ?? null,
            'updated_at' => $data['updated_at'] ?? null,
        ];
    }

    private function normalizeSlug(string $slug, string $name): string
    {
        $slug = trim(strtolower($slug));
        $generated = trim(strtolower((string)preg_replace('/[^a-z0-9]+/i', '-', $name)), '-');

        if ($slug === '' || str_starts_with($slug, '-') || strlen($slug) < 3) {
            return $generated;
        }

        return trim((string)preg_replace('/[^a-z0-9-]+/', '-', $slug), '-') ?: $generated;
    }

    private function normalizeTier(string $tier, float $monthly, float $quarterly, float $annual): string
    {
        $tier = strtolower(trim($tier));
        if ($tier === 'free' && max($monthly, $quarterly, $annual) > 0) {
            return 'paid';
        }

        return $tier !== '' ? $tier : 'paid';
    }

    private function decodeFeatures($features): array
    {
        if (is_array($features)) {
            return $features;
        }

        if (!is_string($features) || trim($features) === '') {
            return [];
        }

        $decoded = json_decode($features, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function candidatePlanNotFound(Response $response, int $requestedPlanId): void
    {
        $availableIds = array_map(
            fn(SubscriptionPlan $plan): int => (int)$plan->id,
            SubscriptionPlan::getActivePlansFor('candidate')
        );

        $this->error($response, 'Candidate plan not found', 404, [
            'plan_id' => "Plan {$requestedPlanId} is not a candidate plan.",
            'available_candidate_plan_ids' => $availableIds,
            'hint' => 'Use a plan_id returned by GET /api/v1/candidate/premium/plans.'
        ]);
    }
}
