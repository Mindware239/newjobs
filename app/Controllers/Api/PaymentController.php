<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPayment;
use App\Models\EmployerSubscription;
use App\Services\PaymentService;
use App\Services\MailService;

class PaymentController extends ApiController
{
    private PaymentService $paymentService;
    private MailService $mailService;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
        $this->mailService = new MailService();
    }

    /**
     * GET /subscription-plans
     * List subscription plans
     */
    public function listPlans(Request $request, Response $response): void
    {
        $planFor = strtolower(trim((string)($request->input('plan_for') ?? $request->input('for') ?? 'employer')));
        if (!in_array($planFor, ['employer', 'candidate'], true)) {
            $planFor = 'employer';
        }

        $plans = SubscriptionPlan::getActivePlansFor($planFor);

        $data = [];
        foreach ($plans as $plan) {
            $data[] = [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'plan_for' => $plan->plan_for,
                'description' => $plan->description,
                'price' => (float)$plan->getPrice((string)($plan->default_billing_cycle ?? 'monthly')),
                'price_monthly' => (float)$plan->price_monthly,
                'price_quarterly' => (float)$plan->price_quarterly,
                'price_annual' => (float)$plan->price_annual,
                'billing_cycle' => $plan->default_billing_cycle ?? 'monthly',
                'features' => json_decode($plan->features ?? '[]', true),
                'is_popular' => (bool)($plan->is_featured ?? false)
            ];
        }

        $this->success($response, ['plans' => $data]);
    }

    /**
     * POST /payments/initiate
     * Initiate payment
     */
    public function initiate(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $body = $request->getJsonBody();
        $errors = $this->validate($body, [
            'plan_id' => 'required|numeric',
            'coupon_code' => 'sometimes|string'
        ]);

        $gateway = strtolower((string)($body['gateway'] ?? $body['payment_gateway'] ?? ''));
        $paymentMethod = strtolower((string)($body['payment_method'] ?? 'checkout'));
        if ($gateway === '' && in_array($paymentMethod, ['razorpay', 'cashfree', 'stripe'], true)) {
            $gateway = $paymentMethod;
            $paymentMethod = 'checkout';
        }
        if ($gateway === '') {
            $gateway = 'razorpay';
        }
        if (!in_array($gateway, ['razorpay', 'cashfree', 'stripe'], true)) {
            $errors['gateway'] = 'The selected gateway is invalid.';
        }
        if ($paymentMethod !== '' && !in_array($paymentMethod, ['checkout', 'card', 'upi', 'netbanking', 'wallet', 'emi', 'paylater'], true)) {
            $errors['payment_method'] = 'The selected payment_method is invalid.';
        }

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $employer = $user->employer();
        if (!$employer) {
            $this->error($response, 'Employer profile not found', 404);
            return;
        }

        $plan = SubscriptionPlan::findFor((int)$request->input('plan_id'), 'employer');
        if (!$plan) {
            $this->error($response, 'Employer plan not found', 404);
            return;
        }

        $billingCycle = strtolower((string)($body['billing_cycle'] ?? $plan->default_billing_cycle ?? 'monthly'));
        if (!in_array($billingCycle, ['monthly', 'quarterly', 'annual'], true)) {
            $billingCycle = 'monthly';
        }
        $amount = (float)$plan->getPrice($billingCycle);

        // Apply coupon if provided
        if ($request->input('coupon_code')) {
            // Validate and apply coupon - implementation depends on coupon model
            $discount = $this->validateCoupon($request->input('coupon_code'), $user->id);
            if ($discount) {
                $amount = $amount * (1 - $discount / 100);
            }
        }

        // Create pending subscription and payment record.
        $subscription = new EmployerSubscription();
        $payment = new SubscriptionPayment();
        try {
            $subscription->fill([
                'employer_id' => $employer->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
                'status' => 'pending',
                'started_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s'),
                'auto_renew' => 0
            ])->save();

            $payment->fill([
                'subscription_id' => $subscription->id,
                'employer_id' => $employer->id,
                'amount' => $amount,
                'currency' => 'INR',
                'billing_cycle' => $billingCycle,
                'gateway' => $gateway,
                'status' => 'pending',
                'metadata' => json_encode([
                    'coupon_code' => $request->input('coupon_code'),
                    'payment_method' => $paymentMethod,
                    'plan_id' => (int)$plan->id
                ])
            ])->save();
        } catch (\Throwable $e) {
            error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
            $this->error($response, 'Database error occurred. Please try again.', 500);
            return;
        }

        // Generate payment link based on method
        $paymentData = match($gateway) {
            'razorpay' => $this->paymentService->initiateRazorpay($payment),
            'cashfree' => $this->paymentService->initiateCashfree($payment),
            'stripe' => $this->paymentService->initiateStripe($payment),
            default => null
        };

        if (!$paymentData) {
            $this->error($response, 'Failed to initiate payment', 500);
            return;
        }

        $this->success($response, [
            'payment_id' => $payment->id,
            'subscription_id' => $subscription->id,
            'amount' => $amount,
            'currency' => 'INR',
            'gateway' => $gateway,
            'payment_method' => $paymentMethod,
            'payment_url' => $paymentData['payment_url'] ?? null,
            'order_id' => $paymentData['order_id'] ?? null,
            'gateway_order_id' => $paymentData['order_id'] ?? null,
            'key' => $paymentData['key'] ?? null,
            'payment_session_id' => $paymentData['payment_session_id'] ?? null,
            'environment' => $paymentData['environment'] ?? null
        ], 'Payment initiated', 201);
    }

    /**
     * POST /payments/verify
     * Verify payment
     */
    public function verify(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $body = $request->getJsonBody();
        if (!isset($body['payment_id']) && isset($body['subscription_payment_id'])) {
            $body['payment_id'] = $body['subscription_payment_id'];
        }
        $errors = $this->validate($body, [
            'payment_id' => 'required|numeric',
            'razorpay_payment_id' => 'sometimes|string',
            'razorpay_order_id' => 'sometimes|string',
            'razorpay_signature' => 'sometimes|string'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $employer = $user->employer();
        if (!$employer) {
            $this->error($response, 'Employer profile not found', 404);
            return;
        }

        $payment = SubscriptionPayment::find((int)$body['payment_id']);
        if (!$payment || (int)$payment->employer_id !== (int)$employer->id) {
            $this->error($response, 'Payment not found', 404);
            return;
        }

        // Verify payment with gateway
        $isValid = $this->paymentService->verify($payment, $body);

        if (!$isValid) {
            $payment->status = 'failed';
            try {
                $payment->save();
            } catch (\Throwable $e) {
                error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
            }
            $this->error($response, 'Payment verification failed', 400);
            return;
        }

        $payment->status = 'completed';
        $payment->paid_at = date('Y-m-d H:i:s');
        try {
            $payment->save();
        } catch (\Throwable $e) {
            error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
            $this->error($response, 'Database error occurred. Please try again.', 500);
            return;
        }

        $subscription = EmployerSubscription::find((int)$payment->subscription_id);
        if (!$subscription) {
            $this->error($response, 'Subscription not found', 404);
            return;
        }

        $plan = $subscription->plan();
        $cycle = strtolower((string)($subscription->billing_cycle ?? 'monthly'));
        $expiresAt = match ($cycle) {
            'quarterly' => date('Y-m-d H:i:s', strtotime('+3 months')),
            'annual' => date('Y-m-d H:i:s', strtotime('+1 year')),
            default => date('Y-m-d H:i:s', strtotime('+1 month')),
        };
        try {
            $subscription->fill([
                'started_at' => date('Y-m-d H:i:s'),
                'expires_at' => $expiresAt,
                'status' => 'active',
                'auto_renew' => 0,
                'contacts_used_this_month' => 0,
                'resume_downloads_used_this_month' => 0,
                'chat_messages_used_this_month' => 0,
                'job_posts_used' => 0,
                'last_usage_reset_at' => date('Y-m-d H:i:s')
            ])->save();
        } catch (\Throwable $e) {
            error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
            $this->error($response, 'Database error occurred. Please try again.', 500);
            return;
        }

        // Send confirmation email
        $this->mailService->send($user->email, 'subscription_confirmation', [
            'user_name' => $user->email,
            'plan_name' => $plan ? $plan->name : 'Subscription',
            'amount' => $payment->amount,
            'expires_at' => $subscription->expires_at
        ]);

        $this->success($response, [
            'payment_id' => $payment->id,
            'subscription_id' => $subscription->id,
            'status' => 'success',
            'message' => 'Payment verified and subscription activated'
        ]);
    }

    /**
     * GET /payments/history
     * List payment history
     */
    public function history(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('per_page', 10);

        $employer = $user->employer();
        if (!$employer) {
            $this->error($response, 'Employer profile not found', 404);
            return;
        }

        $query = SubscriptionPayment::where('employer_id', '=', $employer->id);

        $payments = $query->orderBy('created_at', 'DESC')->paginate($perPage, $page);

        $this->success($response, [
            'payments' => $payments['data'],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $payments['total'],
                'last_page' => ceil($payments['total'] / $perPage)
            ]
        ]);
    }

    /**
     * GET /payments/status
     */
    public function status(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $orderId = (string)$request->query('order_id');
        if (!$orderId) {
            $this->error($response, 'order_id is required', 400);
            return;
        }

        $employer = $user->employer();
        if (!$employer) {
            $this->error($response, 'Employer profile not found', 404);
            return;
        }

        $payment = SubscriptionPayment::where('employer_id', '=', $employer->id)
            ->where(function($q) use ($orderId) {
                $q->where('gateway_order_id', '=', $orderId)
                  ->orWhere('gateway_payment_id', '=', $orderId);
            })->first();

        if (!$payment) {
            $this->error($response, 'Payment not found', 404);
            return;
        }

        $status = strtolower($payment->status ?? 'pending');
        $paymentStatus = $status === 'completed' || $status === 'success' ? 'success' : ($status === 'failed' ? 'failed' : 'pending');

        $this->success($response, ['payment_status' => $paymentStatus], 'Payment status');
    }

    /**
     * POST /subscription/upgrade
     * Upgrade subscription plan
     */
    public function upgradeSubscription(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'plan_id' => 'required|numeric'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $employer = $user->employer();
        if (!$employer) {
            $this->error($response, 'Employer profile not found', 404);
            return;
        }

        $currentSubscription = EmployerSubscription::where('employer_id', '=', $employer->id)
            ->where('status', '=', 'active')
            ->first();

        if (!$currentSubscription) {
            $this->error($response, 'No active subscription found', 400);
            return;
        }

        $newPlan = SubscriptionPlan::find((int)$request->input('plan_id'));
        if (!$newPlan) {
            $this->error($response, 'Plan not found', 404);
            return;
        }

        $currentPlan = $currentSubscription->plan();
        if ($currentPlan && $newPlan->getPrice('monthly') <= $currentPlan->getPrice('monthly')) {
            $this->error($response, 'Cannot downgrade to a lower plan', 400);
            return;
        }

        // Calculate prorated charge
        $proratedAmount = $this->calculateProration(
            $currentSubscription,
            $newPlan
        );

        $this->success($response, [
            'current_plan' => $currentPlan ? $currentPlan->name : null,
            'new_plan' => $newPlan->name,
            'prorated_charge' => $proratedAmount,
            'upgrade_url' => '/payments/initiate?plan_id=' . $newPlan->id
        ], 'Upgrade available');
    }

    /**
     * POST /subscription/cancel
     * Cancel subscription
     */
    public function cancelSubscription(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $employer = $user->employer();
        if (!$employer) {
            $this->error($response, 'Employer profile not found', 404);
            return;
        }

        $subscription = EmployerSubscription::where('employer_id', '=', $employer->id)
            ->where('status', '=', 'active')
            ->first();

        if (!$subscription) {
            $this->error($response, 'No active subscription found', 400);
            return;
        }

        $subscription->status = 'cancelled';
        $subscription->cancelled_at = date('Y-m-d H:i:s');
        try {
            $subscription->save();
        } catch (\Throwable $e) {
            error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
            $this->error($response, 'Database error occurred. Please try again.', 500);
            return;
        }

        $this->success($response, [], 'Subscription cancelled successfully');
    }

    /**
     * GET /subscription/current
     * Get current subscription details
     */
    public function currentSubscription(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'employer') {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $employer = $user->employer();
        if (!$employer) {
            $this->error($response, 'Employer profile not found', 404);
            return;
        }

        $subscription = EmployerSubscription::getCurrentForEmployer((int)$employer->id);

        if (!$subscription) {
            $this->success($response, [
                'subscription' => null,
                'message' => 'No active subscription'
            ]);
            return;
        }

        $plan = $subscription->plan();
        if (!$plan) {
            $this->error($response, 'Subscription plan not found', 404);
            return;
        }
        $daysRemaining = (strtotime($subscription->expires_at) - time()) / (60 * 60 * 24);

        $this->success($response, [
            'id' => $subscription->id,
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'price' => (float)$plan->getPrice((string)($subscription->billing_cycle ?? 'monthly')),
                'features' => json_decode($plan->features ?? '[]', true)
            ],
            'status' => $subscription->status,
            'started_at' => $subscription->started_at,
            'expires_at' => $subscription->expires_at,
            'days_remaining' => (int)$daysRemaining,
            'auto_renewal' => (bool)$subscription->auto_renew
        ]);
    }

    /**
     * POST /payments/refund
     * Request refund
     */
    public function requestRefund(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'payment_id' => 'required|numeric',
            'reason' => 'required|string'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $payment = SubscriptionPayment::find((int)$request->input('payment_id'));
        $employer = $user->employer();
        if (!$employer || !$payment || (int)$payment->employer_id !== (int)$employer->id) {
            $this->error($response, 'Payment not found', 404);
            return;
        }

        if ($payment->status !== 'completed') {
            $this->error($response, 'Only completed payments can be refunded', 400);
            return;
        }

        $payment->status = 'refund_requested';
        $payment->refund_reason = $request->input('reason');
        try {
            $payment->save();
        } catch (\Throwable $e) {
            error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
            $this->error($response, 'Database error occurred. Please try again.', 500);
            return;
        }

        $this->success($response, [], 'Refund request submitted');
    }

    /**
     * POST /payments/razorpay/webhook
     * Razorpay webhook handler
     */
    public function razorpayWebhook(Request $request, Response $response): void
    {
        $payload = $request->getJsonBody();
        
        // Verify webhook signature
        $isValid = $this->paymentService->verifyRazorpayWebhook($payload);

        if (!$isValid) {
            $this->error($response, 'Invalid webhook signature', 401);
            return;
        }

        // Process webhook event
        $this->paymentService->processRazorpayWebhook($payload);

        $this->success($response, [], 'Webhook processed');
    }

    /**
     * POST /payments/cashfree/webhook
     * Cashfree webhook handler
     */
    public function cashfreeWebhook(Request $request, Response $response): void
    {
        $payload = $request->getJsonBody();
        
        $isValid = $this->paymentService->verifyCashfreeWebhook($payload);

        if (!$isValid) {
            $this->error($response, 'Invalid webhook signature', 401);
            return;
        }

        $this->paymentService->processCashfreeWebhook($payload);

        $this->success($response, [], 'Webhook processed');
    }

    /**
     * GET /invoices
     * List invoices
     */
    public function listInvoices(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('per_page', 10);

        // Fetch invoices from payments
        $employer = $user->employer();
        if (!$employer) {
            $this->error($response, 'Employer profile not found', 404);
            return;
        }

        $payments = SubscriptionPayment::where('employer_id', '=', $employer->id)
            ->where('status', '=', 'completed')
            ->orderBy('paid_at', 'DESC')
            ->paginate($perPage, $page);

        $invoices = [];
        foreach ($payments['data'] as $payment) {
            $invoices[] = [
                'id' => $payment->id,
                'invoice_number' => 'INV-' . $payment->id,
                'plan' => ($payment->subscription() && $payment->subscription()->plan()) ? $payment->subscription()->plan()->name : null,
                'amount' => (float)$payment->amount,
                'date' => $payment->paid_at,
                'status' => 'paid'
            ];
        }

        $this->success($response, [
            'invoices' => $invoices,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $payments['total'],
                'last_page' => ceil($payments['total'] / $perPage)
            ]
        ]);
    }

    /**
     * GET /invoices/{id}/download
     * Download invoice
     */
    public function downloadInvoice(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $payment = SubscriptionPayment::find($id);
        $employer = $user->employer();
        if (!$employer || !$payment || (int)$payment->employer_id !== (int)$employer->id) {
            $this->error($response, 'Invoice not found', 404);
            return;
        }

        // Generate or retrieve invoice PDF
        $invoicePath = $this->generateInvoicePdf($payment);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="invoice_' . $payment->id . '.pdf"');
        readfile($invoicePath);
        exit;
    }

    /**
     * POST /payments/wallet/add
     * Add money to wallet
     */
    public function addToWallet(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'amount' => 'required|numeric|min:100'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        // Initiate wallet topup payment
        $this->success($response, [
            'payment_url' => '/payments/initiate?type=wallet&amount=' . $request->input('amount')
        ], 'Wallet topup initiated', 201);
    }

    /**
     * GET /api/v1/payments/wallet/balance
     * Get wallet balance
     */
    public function walletBalance(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        // Get wallet balance from model
        $balance = $user->getWalletBalance();

        $this->success($response, [
            'balance' => (float)$balance,
            'currency' => 'INR'
        ]);
    }

    /**
     * POST /api/v1/discount/validate
     * Migrated from api.php - Validate discount code
     */
    public function validateDiscount(Request $request, Response $response): void
    {
        $user = $this->user($request);
        $data = $request->getJsonBody() ?? [];
        $code = $data['code'] ?? '';
        $planId = (int)($data['plan_id'] ?? 0);
        $billingCycle = $data['billing_cycle'] ?? 'monthly';

        $result = $this->paymentService->validateDiscount((string)$code, (int)$user->id, $planId, $billingCycle);

        if (isset($result['error']) && empty($code)) {
            $this->error($response, $result['error'], 400);
            return;
        }

        $this->success($response, $result, 'Discount validation result');
    }

    private function validateCoupon(?string $code, int $userId): ?float
    {
        // Implementation depends on coupon model
        return null;
    }

    private function calculateProration($currentSubscription, $newPlan): float
    {
        $currentPlan = $currentSubscription->plan();
        $daysUsed = (time() - strtotime((string)$currentSubscription->started_at)) / (60 * 60 * 24);
        $totalDays = match ((string)($currentSubscription->billing_cycle ?? 'monthly')) {
            'quarterly' => 90,
            'annual' => 365,
            default => 30,
        };
        $daysRemaining = $totalDays - $daysUsed;

        $oldPlanDailyRate = $currentPlan ? $currentPlan->getPrice('monthly') / 30 : 0;
        $newPlanDailyRate = $newPlan->getPrice('monthly') / 30;

        return ($newPlanDailyRate - $oldPlanDailyRate) * $daysRemaining;
    }

    private function generateInvoicePdf($payment): string
    {
        // Generate PDF invoice
        return '/invoices/invoice_' . $payment->id . '.pdf';
    }
}
