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
        $plans = SubscriptionPlan::where('active', '=', true)
            ->orderBy('price', 'ASC')
            ->get();

        $data = [];
        foreach ($plans as $plan) {
            $data[] = [
                'id' => $plan->id,
                'name' => $plan->name,
                'description' => $plan->description,
                'price' => (float)$plan->price,
                'billing_cycle' => $plan->billing_cycle,
                'duration_days' => $plan->duration_days,
                'features' => json_decode($plan->features ?? '[]', true),
                'is_popular' => $plan->is_popular ?? false
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

        $errors = $this->validate($request->getJsonBody(), [
            'plan_id' => 'required|numeric',
            'payment_method' => 'required|in:razorpay,cashfree,stripe',
            'coupon_code' => 'sometimes|string'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $plan = SubscriptionPlan::find((int)$request->input('plan_id'));
        if (!$plan) {
            $this->error($response, 'Plan not found', 404);
            return;
        }

        $amount = (float)$plan->price;

        // Apply coupon if provided
        if ($request->input('coupon_code')) {
            // Validate and apply coupon - implementation depends on coupon model
            $discount = $this->validateCoupon($request->input('coupon_code'), $user->id);
            if ($discount) {
                $amount = $amount * (1 - $discount / 100);
            }
        }

        $paymentMethod = $request->input('payment_method');

        // Create payment record
        $payment = new SubscriptionPayment();
        $payment->fill([
            'employer_id' => $user->id,
            'plan_id' => $plan->id,
            'amount' => $amount,
            'currency' => 'INR',
            'payment_method' => $paymentMethod,
            'status' => 'pending',
            'metadata' => json_encode([
                'coupon_code' => $request->input('coupon_code')
            ])
        ])->save();

        // Generate payment link based on method
        $paymentData = match($paymentMethod) {
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
            'amount' => $amount,
            'currency' => 'INR',
            'payment_method' => $paymentMethod,
            'payment_url' => $paymentData['payment_url'] ?? null,
            'order_id' => $paymentData['order_id'] ?? null
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

        $errors = $this->validate($request->getJsonBody(), [
            'payment_id' => 'required|numeric',
            'razorpay_payment_id' => 'sometimes|string',
            'razorpay_order_id' => 'sometimes|string',
            'razorpay_signature' => 'sometimes|string'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $payment = SubscriptionPayment::find((int)$request->input('payment_id'));
        if (!$payment || $payment->employer_id !== $user->id) {
            $this->error($response, 'Payment not found', 404);
            return;
        }

        // Verify payment with gateway
        $isValid = $this->paymentService->verify($payment, $request->getJsonBody());

        if (!$isValid) {
            $payment->status = 'failed';
            $payment->save();
            $this->error($response, 'Payment verification failed', 400);
            return;
        }

        $payment->status = 'completed';
        $payment->completed_at = date('Y-m-d H:i:s');
        $payment->save();

        // Create subscription
        $plan = $payment->plan;
        $subscription = new EmployerSubscription();
        $subscription->fill([
            'employer_id' => $user->id,
            'plan_id' => $plan->id,
            'payment_id' => $payment->id,
            'starts_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+' . $plan->duration_days . ' days')),
            'status' => 'active',
            'auto_renewal' => true
        ])->save();

        // Send confirmation email
        $this->mailService->send($user->email, 'subscription_confirmation', [
            'user_name' => $user->email,
            'plan_name' => $plan->name,
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

        $query = SubscriptionPayment::where('employer_id', '=', $user->id);

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

        $payment = SubscriptionPayment::where('employer_id', '=', $user->id)
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

        $currentSubscription = EmployerSubscription::where('employer_id', '=', $user->id)
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

        if ($newPlan->price <= $currentSubscription->plan->price) {
            $this->error($response, 'Cannot downgrade to a lower plan', 400);
            return;
        }

        // Calculate prorated charge
        $proratedAmount = $this->calculateProration(
            $currentSubscription,
            $newPlan
        );

        $this->success($response, [
            'current_plan' => $currentSubscription->plan->name,
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

        $subscription = EmployerSubscription::where('employer_id', '=', $user->id)
            ->where('status', '=', 'active')
            ->first();

        if (!$subscription) {
            $this->error($response, 'No active subscription found', 400);
            return;
        }

        $subscription->status = 'cancelled';
        $subscription->cancelled_at = date('Y-m-d H:i:s');
        $subscription->save();

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

        $subscription = EmployerSubscription::where('employer_id', '=', $user->id)
            ->where('status', '=', 'active')
            ->first();

        if (!$subscription) {
            $this->success($response, [
                'subscription' => null,
                'message' => 'No active subscription'
            ]);
            return;
        }

        $plan = $subscription->plan;
        $daysRemaining = (strtotime($subscription->expires_at) - time()) / (60 * 60 * 24);

        $this->success($response, [
            'id' => $subscription->id,
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'price' => (float)$plan->price,
                'features' => json_decode($plan->features ?? '[]', true)
            ],
            'status' => $subscription->status,
            'started_at' => $subscription->starts_at,
            'expires_at' => $subscription->expires_at,
            'days_remaining' => (int)$daysRemaining,
            'auto_renewal' => $subscription->auto_renewal
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
        if (!$payment || $payment->employer_id !== $user->id) {
            $this->error($response, 'Payment not found', 404);
            return;
        }

        if ($payment->status !== 'completed') {
            $this->error($response, 'Only completed payments can be refunded', 400);
            return;
        }

        $payment->status = 'refund_requested';
        $payment->refund_reason = $request->input('reason');
        $payment->save();

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
        $payments = SubscriptionPayment::where('employer_id', '=', $user->id)
            ->where('status', '=', 'completed')
            ->orderBy('completed_at', 'DESC')
            ->paginate($perPage, $page);

        $invoices = [];
        foreach ($payments['data'] as $payment) {
            $invoices[] = [
                'id' => $payment->id,
                'invoice_number' => 'INV-' . $payment->id,
                'plan' => $payment->plan->name,
                'amount' => (float)$payment->amount,
                'date' => $payment->completed_at,
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
        if (!$payment || $payment->employer_id !== $user->id) {
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
        $daysUsed = (time() - strtotime($currentSubscription->starts_at)) / (60 * 60 * 24);
        $totalDays = $currentSubscription->plan->duration_days;
        $daysRemaining = $totalDays - $daysUsed;

        $oldPlanDailyRate = $currentSubscription->plan->price / $totalDays;
        $newPlanDailyRate = $newPlan->price / $newPlan->duration_days;

        return ($newPlanDailyRate - $oldPlanDailyRate) * $daysRemaining;
    }

    private function generateInvoicePdf($payment): string
    {
        // Generate PDF invoice
        return '/invoices/invoice_' . $payment->id . '.pdf';
    }
}
