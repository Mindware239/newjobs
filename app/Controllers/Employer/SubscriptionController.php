<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Employer;
use App\Models\SubscriptionPlan;
use App\Models\EmployerSubscription;
use App\Models\SubscriptionPayment;
use App\Models\DiscountCode;
use App\Models\SubscriptionUsageLog;
use App\Core\Database;
use Razorpay\Api\Api;

class SubscriptionController extends BaseController
{
    /**
     * Employer Subscription Dashboard
     */
    public function dashboard(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->redirect('/register-employer');
            return;
        }

        $subscription = EmployerSubscription::getCurrentForEmployer($employer->id);
        $plan = $subscription ? $subscription->plan() : null;
        $payments = SubscriptionPayment::where('employer_id', '=', $employer->id)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        $usage = [
            'job_posts' => [
                'used' => $subscription ? ($subscription->job_posts_used ?? 0) : 0,
                'limit' => $plan ? $plan->max_job_posts : 0
            ],
            'resume_views' => [
                'used' => $subscription ? ($subscription->resume_downloads_used_this_month ?? 0) : 0,
                'limit' => $plan ? $plan->max_resume_downloads : 0
            ],
            'contacts_views' => [
                'used' => $subscription ? ($subscription->contacts_used_this_month ?? 0) : 0,
                'limit' => $plan ? $plan->max_contacts_per_month : 0
            ]
        ];

        // Format for view
        $paymentsData = array_map(fn($p) => $p->attributes, $payments);

        $response->view('employer/subscription/dashboard', [
            'title' => 'Subscription Dashboard',
            'employer' => $employer,
            'subscription' => $subscription ? $subscription->attributes : null,
            'plan' => $plan ? $plan->attributes : null,
            'usage' => $usage,
            'payments' => $paymentsData
        ], 200, 'employer/layout');
    }

    /**
     * View all subscription plans
     */
    public function plans(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->redirect('/register-employer');
            return;
        }

        $plans = SubscriptionPlan::getActivePlansFor('employer');
        
        // Fallback query if empty
        if (empty($plans)) {
            $db = \App\Core\Database::getInstance();
            $sql = "SELECT * FROM subscription_plans WHERE plan_for = 'employer' ORDER BY sort_order ASC, price_monthly ASC LIMIT 10";
            $results = $db->fetchAll($sql);
            $plans = array_map(function($row) {
                return new SubscriptionPlan($row);
            }, $results);
        }
        
        $currentSubscription = EmployerSubscription::getCurrentForEmployer($employer->id);
        
        // Get discount code if provided
        $discountCode = $request->get('discount');
        $discount = null;
        if ($discountCode) {
            $discount = DiscountCode::findByCode($discountCode);
            if ($discount && !$discount->isValid()) {
                $discount = null;
            }
        }

        // Process plans data
        $plansData = [];
        foreach ($plans as $plan) {
            $planAttrs = is_array($plan) ? $plan : ($plan->attributes ?? []);
            // Format features list
            $featuresJson = $planAttrs['features'] ?? '[]';
            if (is_string($featuresJson)) {
                $decoded = json_decode($featuresJson, true);
                $planAttrs['features_list'] = array_values(array_filter(array_map(function ($item) {
                    if (is_string($item)) return trim($item);
                    if (is_array($item)) return ($item['is_enabled'] ?? 1) ? trim((string)$item['feature_text']) : '';
                    return '';
                }, is_array($decoded) ? $decoded : [])));
            }
            $plansData[] = $planAttrs;
        }

        $response->view('employer/subscription/plans', [
            'title' => 'Subscription Plans',
            'employer' => $employer,
            'plans' => $plansData,
            'currentSubscription' => $currentSubscription ? $currentSubscription->attributes : null,
            'discountCode' => $discountCode,
            'discount' => $discount ? $discount->attributes : null,
            'upgrade' => $request->get('upgrade') === '1'
        ], 200, 'employer/layout');
    }

    /**
     * Production-grade idempotent subscribe
     */
    public function subscribe(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) return;

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->error('Employer profile not found. Please complete your registration.', 404);
            return;
        }

        $data = $request->getJsonBody() ?? $request->all();
        
        $planSlug = $data['plan_slug'] ?? '';
        $billingCycle = $data['billing_cycle'] ?? 'monthly';
        $idempotencyKey = $data['idempotency_key'] ?? null;
    $gateway = $data['gateway'] ?? 'razorpay';

        if (!$idempotencyKey) {
            $response->error('Idempotency key required', 400);
            return;
        }

        // 1. IDEMPOTENCY CHECK: Reuse existing payment if key matches
        $existingPayment = SubscriptionPayment::where('idempotency_key', '=', $idempotencyKey)->first();
        if ($existingPayment) {
            try {
                $paymentGateway = $this->initiatePayment($existingPayment, $employer, $gateway);
                $response->json([
                    'success' => true,
                    'is_reused' => true,
                    'payment_id' => (int)$existingPayment->id,
                    'requires_payment' => true,
                    'payment_gateway' => $paymentGateway
                ]);
            } catch (\Throwable $e) {
                error_log("Razorpay Idempotency Error: " . $e->getMessage());
                $response->error('Payment initiation failed: ' . $e->getMessage(), 500);
            }
            return;
        }

        $plan = SubscriptionPlan::findBySlug($planSlug);
        if (!$plan) { $response->error('Plan not found', 404); return; }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // 2. STATE CONTROL: Lock employer row
            $db->query("SELECT id FROM employers WHERE id = :id FOR UPDATE", ['id' => $employer->id]);
            
            // 2.1 PREVENT DUPLICATES: Check for existing PENDING payment for same plan/cycle
            // This handles cases where idempotency key might be different (e.g. page refresh)
            $pendingPayment = SubscriptionPayment::where('employer_id', '=', $employer->id)
                ->where('billing_cycle', '=', $billingCycle)
                ->where('status', '=', 'pending')
                ->orderBy('id', 'DESC')
                ->first();

            if ($pendingPayment) {
                // Verify it belongs to the same plan
                $pendingSub = EmployerSubscription::find((int)$pendingPayment->subscription_id);
                if ($pendingSub && (int)$pendingSub->plan_id === (int)$plan->id) {
                    $db->rollback(); // No changes needed
                    
                    $paymentGateway = $this->initiatePayment($pendingPayment, $employer, $gateway);
                    $response->json([
                        'success' => true,
                        'is_reused' => true,
                        'payment_id' => (int)$pendingPayment->id,
                        'requires_payment' => true,
                        'payment_gateway' => $paymentGateway
                    ]);
                    return;
                }
            }

            $currentSub = EmployerSubscription::where('employer_id', '=', $employer->id)
                ->whereIn('status', ['active', 'pending', 'trial'])
                ->orderBy('id', 'DESC')
                ->first();

            $basePrice = $plan->getPrice($billingCycle);
            $finalPrice = $basePrice;
            
            // Apply Discount if any
            if (!empty($data['discount_code'])) {
                $discount = DiscountCode::findByCode($data['discount_code']);
                if ($discount && $discount->isValid() && $discount->isApplicableToPlan($plan->id, $billingCycle)) {
                    $finalPrice -= $discount->calculateDiscount($basePrice);
                }
            }

            // Determine Subscription ID
            $subscriptionId = null;
            if ($currentSub && (int)$currentSub->plan_id === (int)$plan->id && $currentSub->billing_cycle === $billingCycle) {
                $subscriptionId = $currentSub->id;
            } else {
                $subscription = new EmployerSubscription();
                $subscription->fill([
                    'employer_id' => $employer->id,
                    'plan_id' => $plan->id,
                    'status' => 'pending', 
                    'billing_cycle' => $billingCycle,
                    'started_at' => date('Y-m-d H:i:s'),
                    'expires_at' => $this->calculateExpiryDate(date('Y-m-d H:i:s'), $billingCycle)
                ]);
                if (!$subscription->save()) {
                    throw new \Exception("Failed to create subscription record");
                }
                $subscriptionId = $subscription->id;
            }

            if ($finalPrice > 0) {
                // 3. SAFE PAYMENT CREATION
                $payment = new SubscriptionPayment();
                $payment->fill([
                    'subscription_id' => $subscriptionId,
                    'employer_id' => $employer->id,
                    'idempotency_key' => $idempotencyKey,
                    'amount' => $finalPrice,
                    'currency' => 'INR',
                    'billing_cycle' => $billingCycle,
                    'gateway' => $gateway,
                    'status' => 'pending'
                ]);
                
                if (!$payment->save()) {
                    throw new \Exception("Failed to create payment record");
                }

                $db->commit();

                // 4. INITIATE EXTERNAL PAYMENT
                try {
                    $paymentGateway = $this->initiatePayment($payment, $employer, $gateway);
                    $response->json([
                        'success' => true,
                        'payment_id' => (int)$payment->id,
                        'requires_payment' => true,
                        'payment_gateway' => $paymentGateway
                    ]);
                } catch (\Throwable $e) {
                    error_log("External Payment Error: " . $e->getMessage());
                    $response->error('External Payment Error: ' . $e->getMessage(), 500);
                }
            } else {
                // 3. AUTO-ACTIVATE FREE PLAN
                $db->query("UPDATE employer_subscriptions SET status = 'active' WHERE id = :id", ['id' => $subscriptionId]);
                $db->query("UPDATE employer_subscriptions SET status = 'cancelled' WHERE employer_id = :eid AND id != :id AND status IN ('active','trial','grace')", ['eid' => $employer->id, 'id' => $subscriptionId]);
                
                $db->commit();
                $response->json(['success' => true, 'requires_payment' => false], 200, 'Subscription activated');
            }
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollback();
            }
            error_log("Subscribe Controller Error: " . $e->getMessage());
            $response->error($e->getMessage(), 500);
        }
    }

    /**
     * Payment Callback (Critical Section)
     */
    public function paymentCallback(Request $request, Response $response): void
    {
        $data = $request->getJsonBody() ?? $request->all();
        $paymentId = $data['payment_id'] ?? null;
        
        if (!$paymentId) { $response->error('Payment ID required', 400); return; }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $paymentRow = $db->fetchOne("SELECT * FROM subscription_payments WHERE id = :id FOR UPDATE", ['id' => $paymentId]);
            if (!$paymentRow) throw new \Exception("Payment not found");

            if ($paymentRow['status'] !== 'pending') {
                $db->commit();
                $response->json(['success' => true], 200, 'Already processed');
                return;
            }

            $payment = new SubscriptionPayment($paymentRow);
            if ($this->verifyPayment($payment, $data)) {
                $payment->markAsCompleted($data['razorpay_payment_id'] ?? null, $data['razorpay_order_id'] ?? null);
                
                $db->query("UPDATE employer_subscriptions SET status = 'active' WHERE id = :id", ['id' => $payment->subscription_id]);
                $db->query(
                    "UPDATE employer_subscriptions SET status = 'cancelled' 
                     WHERE employer_id = :eid AND id != :id AND status IN ('active','trial','grace')",
                    ['eid' => $payment->employer_id, 'id' => $payment->subscription_id]
                );
                
                $db->commit();
                $response->json(['success' => true]);
            } else {
                throw new \Exception("Verification failed");
            }
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollback();
            }
            error_log("Payment Callback Error: " . $e->getMessage());
            $response->error($e->getMessage(), 400);
        }
    }

    public function cancel(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $this->subscriptionActionResponse($request, $response, false, 'Employer profile not found', 404);
            return;
        }

        $subscription = EmployerSubscription::getCurrentForEmployer((int)$employer->id);
        if (!$subscription) {
            $this->subscriptionActionResponse($request, $response, false, 'No active subscription found', 404);
            return;
        }

        $data = $request->getJsonBody() ?? $request->all();
        $status = (string)($subscription->attributes['status'] ?? 'inactive');

        $subscription->attributes['auto_renew'] = 0;
        $subscription->attributes['cancelled_at'] = date('Y-m-d H:i:s');
        if (!empty($data['reason'])) {
            $subscription->attributes['cancellation_reason'] = trim((string)$data['reason']);
        }

        // Cancel pending subscriptions immediately; keep active ones available until expiry.
        if (in_array($status, ['pending', 'inactive'], true)) {
            $subscription->attributes['status'] = 'cancelled';
        }

        $subscription->save();

        $message = in_array($status, ['active', 'trial', 'grace'], true)
            ? 'Auto-renew disabled. Your current subscription will remain active until it expires.'
            : 'Subscription cancelled successfully.';

        $this->subscriptionActionResponse($request, $response, true, $message, 200, [
            'redirect_url' => '/employer/subscription/dashboard',
            'auto_renew' => false,
            'status' => $subscription->attributes['status'] ?? $status
        ]);
    }

    public function renew(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $this->subscriptionActionResponse($request, $response, false, 'Employer profile not found', 404);
            return;
        }

        $subscription = EmployerSubscription::where('employer_id', '=', (int)$employer->id)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$subscription) {
            $this->subscriptionActionResponse($request, $response, false, 'No subscription found to renew', 404);
            return;
        }

        $subscription->attributes['auto_renew'] = 1;
        if (($subscription->attributes['status'] ?? '') === 'cancelled') {
            $expiresAt = $subscription->attributes['expires_at'] ?? null;
            if ($expiresAt && strtotime((string)$expiresAt) >= time()) {
                $subscription->attributes['status'] = 'active';
            }
        }
        $subscription->save();

        $this->subscriptionActionResponse($request, $response, true, 'Auto-renew enabled successfully.', 200, [
            'redirect_url' => '/employer/subscription/dashboard',
            'auto_renew' => true,
            'status' => $subscription->attributes['status'] ?? 'active'
        ]);
    }

    public function changePlan(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $this->subscriptionActionResponse($request, $response, false, 'Employer profile not found', 404);
            return;
        }

        $data = $request->getJsonBody() ?? $request->all();
        $planSlug = trim((string)($data['plan_slug'] ?? ''));
        $billingCycle = trim((string)($data['billing_cycle'] ?? 'monthly'));

        if ($planSlug === '') {
            $this->subscriptionActionResponse($request, $response, false, 'Plan is required', 422);
            return;
        }

        $plan = SubscriptionPlan::findBySlug($planSlug);
        if (!$plan) {
            $this->subscriptionActionResponse($request, $response, false, 'Selected plan not found', 404);
            return;
        }

        $allowedCycles = ['monthly', 'quarterly', 'annual'];
        if (!in_array($billingCycle, $allowedCycles, true)) {
            $billingCycle = 'monthly';
        }

        $currentSubscription = EmployerSubscription::getCurrentForEmployer((int)$employer->id);
        if ($currentSubscription
            && (int)($currentSubscription->attributes['plan_id'] ?? 0) === (int)$plan->id
            && (string)($currentSubscription->attributes['billing_cycle'] ?? 'monthly') === $billingCycle) {
            $this->subscriptionActionResponse($request, $response, true, 'You are already on this plan.', 200, [
                'redirect_url' => '/employer/subscription/dashboard'
            ]);
            return;
        }

        $redirectUrl = '/employer/subscription/plans?upgrade=1&selected_plan=' . urlencode($planSlug) . '&billing_cycle=' . urlencode($billingCycle);
        $this->subscriptionActionResponse($request, $response, true, 'Plan selection updated. Continue to payment to complete the change.', 200, [
            'redirect_url' => $redirectUrl,
            'plan_slug' => $planSlug,
            'billing_cycle' => $billingCycle
        ]);
    }

    private function calculateExpiryDate(string $startDate, string $billingCycle): string
    {
        $days = ['monthly' => 30, 'quarterly' => 90, 'annual' => 365][$billingCycle] ?? 30;
        return date('Y-m-d H:i:s', strtotime("+{$days} days", strtotime($startDate)));
    }

    private function initiatePayment(SubscriptionPayment $payment, Employer $employer, string $gateway): array
    {
        if ($gateway === 'cashfree') {
            return ['gateway' => 'cashfree', 'payment_url' => '/gateway/cashfree/create-order?payment_id=' . $payment->id];
        }

        $config = require __DIR__ . '/../../../config/razorpay.php';
        $api = new Api($config['key_id'], $config['key_secret']);
        
        $order = $api->order->create([
            'receipt' => 'SUB-' . $payment->id,
            'amount' => (int)round($payment->amount * 100),
            'currency' => 'INR',
            'notes' => ['payment_id' => $payment->id]
        ]);

        $payment->setAttribute('gateway_order_id', $order['id']);
        $payment->save();

        return [
            'gateway' => 'razorpay',
            'key' => $config['key_id'],
            'amount' => $order['amount'],
            'order_id' => $order['id'],
            'name' => 'MindInfotech',
            'prefill' => [
                'name' => $employer->company_name ?? $this->currentUser->name ?? 'Employer',
                'contact' => $this->currentUser->phone ?? ''
            ]
        ];
    }

    private function verifyPayment(SubscriptionPayment $payment, array $data): bool
    {
        $config = require __DIR__ . '/../../../config/razorpay.php';
        $api = new Api($config['key_id'], $config['key_secret']);
        try {
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $data['razorpay_order_id'],
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_signature' => $data['razorpay_signature']
            ]);
            return true;
        } catch (\Exception $e) { return false; }
    }

    private function configureSslCa(): void
    {
        // Handled by environment usually
    }

    private function subscriptionActionResponse(Request $request, Response $response, bool $success, string $message, int $statusCode = 200, array $data = []): void
    {
        if ($request->isAjax()) {
            if ($success) {
                $response->json(array_merge(['success' => true], $data), $statusCode, $message, true, null);
            } else {
                $response->json(array_merge(['error' => $message], $data), $statusCode, $message, false, ['error' => $message]);
            }
            return;
        }

        $redirectUrl = (string)($data['redirect_url'] ?? '/employer/subscription/dashboard');
        $separator = strpos($redirectUrl, '?') !== false ? '&' : '?';
        $response->redirect($redirectUrl . $separator . ($success ? 'status=' : 'error=') . urlencode($message));
    }
}
