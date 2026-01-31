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

        $plans = SubscriptionPlan::getActivePlans();
        
        // Debug: Log plans count
        error_log("SubscriptionController::plans() - Found " . count($plans) . " plans");
        
        // If still empty, try direct SQL query as fallback
        if (empty($plans)) {
            $db = \App\Core\Database::getInstance();
            $sql = "SELECT * FROM subscription_plans ORDER BY sort_order ASC, price_monthly ASC LIMIT 10";
            $results = $db->fetchAll($sql);
            $plans = array_map(function($row) {
                return new SubscriptionPlan($row);
            }, $results);
            error_log("SubscriptionController::plans() - Fallback query found " . count($plans) . " plans");
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

        // Check if should hide free plan (after first job posting)
        $hideFree = $request->get('hide_free') === '1';
        $postedJobsCount = \App\Models\Job::where('employer_id', '=', $employer->id)->count();
        try {
            $db = \App\Core\Database::getInstance();
            $usedRow = $db->fetchOne(
                "SELECT id FROM subscription_usage_logs WHERE employer_id = :eid AND action_type = 'free_job_used' LIMIT 1",
                ['eid' => $employer->id]
            );
            $hasConsumedFree = $usedRow !== null;
        } catch (\Throwable $t) {
            $hasConsumedFree = false;
        }
        if ($postedJobsCount > 0 || $hasConsumedFree) {
            $hideFree = true; // Hide free plan after first job or once consumed
        }

        // Process plans to ensure unique IDs and proper data structure
        $plansData = [];
        $seenIds = [];
        foreach ($plans as $plan) {
            // Handle both Model instances and arrays
            $planAttrs = is_array($plan) ? $plan : ($plan->attributes ?? []);
            $planId = $planAttrs['id'] ?? null;
            $planSlug = $planAttrs['slug'] ?? '';
            
            // Skip free plan if hide_free is true
            if ($hideFree && $planSlug === 'free') {
                continue;
            }
            
            if ($planId && !in_array($planId, $seenIds)) {
                $seenIds[] = $planId;
                // Ensure all price fields are present and numeric
                $planAttrs['price_monthly'] = (float)($planAttrs['price_monthly'] ?? 0);
                $planAttrs['price_quarterly'] = (float)($planAttrs['price_quarterly'] ?? 0);
                $planAttrs['price_annual'] = (float)($planAttrs['price_annual'] ?? 0);
                // Ensure all feature fields are present
                $planAttrs['max_job_posts'] = (int)($planAttrs['max_job_posts'] ?? 0);
                $planAttrs['max_contacts_per_month'] = (int)($planAttrs['max_contacts_per_month'] ?? 0);
                $planAttrs['resume_download_enabled'] = (int)($planAttrs['resume_download_enabled'] ?? 0);
                $planAttrs['chat_enabled'] = (int)($planAttrs['chat_enabled'] ?? 0);
                $planAttrs['candidate_mobile_visible'] = (int)($planAttrs['candidate_mobile_visible'] ?? 0);
                $planAttrs['job_post_boost'] = (int)($planAttrs['job_post_boost'] ?? 0);
                $planAttrs['ai_matching'] = (int)($planAttrs['ai_matching'] ?? 0);
                $planAttrs['analytics_dashboard'] = (int)($planAttrs['analytics_dashboard'] ?? 0);
                $planAttrs['is_featured'] = (int)($planAttrs['is_featured'] ?? 0);
                $plansData[] = $planAttrs;
            }
        }
        
        error_log("SubscriptionController::plans() - After processing: " . count($plansData) . " plans");
        
        error_log("SubscriptionController::plans() - Processed " . count($plansData) . " plans for display");
        
        // Final fallback: If still no plans, create default plans data structure
        if (empty($plansData)) {
            error_log("SubscriptionController::plans() - No plans found, using fallback");
            // Try one more time with direct SQL
            try {
                $db = \App\Core\Database::getInstance();
                $sql = "SELECT * FROM subscription_plans LIMIT 10";
                $results = $db->fetchAll($sql);
                foreach ($results as $row) {
                    if ($hideFree && ($row['slug'] ?? '') === 'free') {
                        continue;
                    }
                    $row['price_monthly'] = (float)($row['price_monthly'] ?? 0);
                    $row['price_quarterly'] = (float)($row['price_quarterly'] ?? 0);
                    $row['price_annual'] = (float)($row['price_annual'] ?? 0);
                    $plansData[] = $row;
                }
                error_log("SubscriptionController::plans() - Direct SQL found " . count($plansData) . " plans");
            } catch (\Exception $e) {
                error_log("SubscriptionController::plans() - Direct SQL error: " . $e->getMessage());
            }
        }

        // Inject test plans if still empty
        if (empty($plansData)) {
            $plansData = [
                ['id' => 0, 'slug' => 'free', 'name' => 'Free', 'price_monthly' => 0, 'price_quarterly' => 0, 'price_annual' => 0, 'max_job_posts' => 1],
                ['id' => 1, 'slug' => 'basic', 'name' => 'Basic', 'price_monthly' => 10, 'price_quarterly' => 30, 'price_annual' => 100, 'max_job_posts' => 3],
                ['id' => 2, 'slug' => 'premium', 'name' => 'Premium', 'price_monthly' => 20, 'price_quarterly' => 60, 'price_annual' => 200, 'max_job_posts' => 10],
                ['id' => 3, 'slug' => 'enterprise', 'name' => 'Enterprise', 'price_monthly' => 50, 'price_quarterly' => 150, 'price_annual' => 500, 'max_job_posts' => 50],
            ];
        }

        // Get upgrade message from session
        $upgradeMessage = $_SESSION['upgrade_message'] ?? null;
        if ($upgradeMessage) {
            unset($_SESSION['upgrade_message']);
        }

        $response->view('employer/subscription/plans', [
            'title' => 'Subscription Plans',
            'employer' => $employer,
            'plans' => $plansData,
            'currentSubscription' => $currentSubscription ? $currentSubscription->attributes : null,
            'discountCode' => $discountCode,
            'discount' => $discount ? $discount->attributes : null,
            'upgrade' => $request->get('upgrade') === '1',
            'feature' => $request->get('feature'),
            'upgradeMessage' => $upgradeMessage
        ], 200, 'employer/layout');
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        $data = $request->getJsonBody() ?? $request->all();
        $planSlug = $data['plan_slug'] ?? '';
        $billingCycle = $data['billing_cycle'] ?? 'monthly';
        $discountCode = $data['discount_code'] ?? '';
        $autoRenew = isset($data['auto_renew']) ? (bool)$data['auto_renew'] : false;

        if (!in_array($billingCycle, ['monthly', 'quarterly', 'annual'])) {
            $response->json(['error' => 'Invalid billing cycle'], 400);
            return;
        }

        $plan = SubscriptionPlan::findBySlug($planSlug);
        if (!$plan) {
            $response->json(['error' => 'Plan not found'], 404);
            return;
        }

        // Check if already has active subscription - allow upgrade/downgrade
        $currentSubscription = EmployerSubscription::getCurrentForEmployer($employer->id);
        if ($currentSubscription && $currentSubscription->isActive()) {
            // Allow plan change if it's a different plan (upgrade/downgrade)
            $currentPlan = $currentSubscription->plan();
            if ($currentPlan && $currentPlan->id === $plan->id) {
                $response->json(['error' => 'You are already subscribed to this plan. Use "Change Plan" to switch billing cycles.'], 400);
                return;
            }
            // If different plan, use changePlan method instead
            // But for now, allow direct subscription (will create new subscription)
        }

        // Calculate price
        $basePrice = $plan->getPrice($billingCycle);
        $discountAmount = 0.00;
        $discount = null;

        if ($discountCode) {
            $discount = DiscountCode::findByCode($discountCode);
            if (!$discount) {
                $response->json(['error' => 'Invalid discount code'], 400);
                return;
            }
            if (!$discount->isValid()) {
                $response->json(['error' => 'This discount code is no longer valid'], 400);
                return;
            }
            if (!$discount->isApplicableToPlan($plan->id, $billingCycle)) {
                $response->json(['error' => 'This discount code is not applicable to the selected plan or billing cycle'], 400);
                return;
            }
            
            // Check max uses per user
            $maxUsesPerUser = (int)($discount->attributes['max_uses_per_user'] ?? 0);
            if ($maxUsesPerUser > 0) {
                $db = \App\Core\Database::getInstance();
                $usedByUser = $db->fetchOne(
                    "SELECT COUNT(*) as count FROM employer_subscriptions 
                     WHERE employer_id = :employer_id AND discount_code = :code",
                    ['employer_id' => $employer->id, 'code' => $discountCode]
                );
                $usedCount = (int)($usedByUser['count'] ?? 0);
                if ($usedCount >= $maxUsesPerUser) {
                    $response->json(['error' => 'You have already used this discount code the maximum number of times'], 400);
                    return;
                }
            }
            
            $discountAmount = $discount->calculateDiscount($basePrice);
        }

        $finalPrice = $basePrice - $discountAmount;

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // Create subscription
            $subscription = new EmployerSubscription();
            $startDate = date('Y-m-d H:i:s');
            
            // Calculate expiry date based on billing cycle
            $expiryDate = $this->calculateExpiryDate($startDate, $billingCycle);
            
            // Check if plan has trial
            $trialEndsAt = null;
            if ($plan->hasFeature('trial_enabled') && $plan->attributes['trial_days'] > 0) {
                $trialDays = (int)$plan->attributes['trial_days'];
                $trialEndsAt = date('Y-m-d H:i:s', strtotime("+{$trialDays} days", strtotime($startDate)));
            }

            $initialStatus = $trialEndsAt ? 'trial' : (($finalPrice > 0) ? 'pending' : 'active');
            $subscription->fill([
                'employer_id' => $employer->id,
                'plan_id' => $plan->id,
                'status' => $initialStatus,
                'billing_cycle' => $billingCycle,
                'started_at' => $startDate,
                'expires_at' => $expiryDate,
                'trial_ends_at' => $trialEndsAt,
                'auto_renew' => $autoRenew ? 1 : 0,
                'next_billing_date' => ($initialStatus === 'pending') ? null : ($autoRenew ? $expiryDate : null),
                'discount_code' => $discountCode ?: null,
                'discount_percentage' => $discount ? (float)$discount->attributes['discount_value'] : 0.00,
                'last_usage_reset_at' => $startDate
            ]);

            if (!$subscription->save()) {
                throw new \Exception('Failed to create subscription');
            }

            // Create payment record if not free plan
            if ($finalPrice > 0) {
                $payment = new SubscriptionPayment();
                $payment->fill([
                    'subscription_id' => $subscription->attributes['id'],
                    'employer_id' => $employer->id,
                    'amount' => $finalPrice,
                    'currency' => 'INR',
                    'billing_cycle' => $billingCycle,
                    'status' => 'pending'
                ]);
                $payment->save();

                // Increment discount code usage
                if ($discount) {
                    $discount->incrementUsage();
                }

                if ($db->inTransaction()) {
                    $db->commit();
                }

                // Return payment gateway details
                $response->json([
                    'success' => true,
                    'subscription_id' => $subscription->attributes['id'],
                    'payment_id' => $payment->attributes['id'],
                    'amount' => $finalPrice,
                    'requires_payment' => true,
                    'payment_gateway' => $this->initiatePayment($payment, $employer)
                ]);
            } else {
                if ($db->inTransaction()) {
                    $db->commit();
                }

                // Free plan - activate immediately
                $redirectUrl = '/employer/jobs/create';
                if ($request->get('feature') === 'job_posting' || $request->get('upgrade') === '1') {
                    $redirectUrl = '/employer/jobs/create?subscription=activated';
                }
                
                $response->json([
                    'success' => true,
                    'subscription_id' => $subscription->attributes['id'],
                    'requires_payment' => false,
                    'message' => 'Subscription activated successfully',
                    'redirect' => $redirectUrl
                ]);
            }
        } catch (\Exception $e) {
            if ($db->inTransaction()) { $db->rollback(); }
            error_log('Subscription Error: ' . $e->getMessage());
            $response->json(['error' => 'Failed to process subscription: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Process payment callback
     */
    public function paymentCallback(Request $request, Response $response): void
    {
        $data = $request->all();
        $paymentId = $data['payment_id'] ?? $request->param('payment_id');
        
        if (!$paymentId) {
            $response->json(['error' => 'Payment ID required'], 400);
            return;
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // Lock the payment row to prevent race conditions
            $paymentRow = $db->fetchOne("SELECT * FROM subscription_payments WHERE id = ? FOR UPDATE", [$paymentId]);
            
            if (!$paymentRow) {
                if ($db->inTransaction()) { $db->rollback(); }
                $response->json(['error' => 'Payment not found'], 404);
                return;
            }
            
            $payment = new SubscriptionPayment($paymentRow);

            // Duplicate prevention
            $currentStatus = $payment->attributes['status'] ?? 'pending';
            if (in_array($currentStatus, ['completed','failed','refunded'])) {
                if ($db->inTransaction()) { $db->commit(); }
                $response->json(['message' => 'This transaction is already processed.']);
                return;
            }

            // Find linked employer_payment (ledger) to ensure consistency with Webhook
            // We use the same logic as Webhook to find it via meta
            $empPay = $db->fetchOne('SELECT * FROM employer_payments WHERE meta LIKE :like AND employer_id = :eid FOR UPDATE', [
                'like' => '%"subscription_payment_id":' . $paymentId . '%',
                'eid' => $payment->attributes['employer_id']
            ]);

            // Verify payment with gateway
            // Note: External API call inside transaction is necessary here to maintain the lock
            // and prevent double-verification. The lock is row-level specific to this payment.
            $verified = $this->verifyPayment($payment, $data);
            
            if ($verified) {
                $payment->setAttribute('status', 'completed');
                $payment->setAttribute('paid_at', date('Y-m-d H:i:s'));
                if (!empty($data['gateway_payment_id'])) {
                    $payment->setAttribute('gateway_payment_id', $data['gateway_payment_id']);
                }
                if (!empty($data['gateway_order_id'])) {
                    $payment->setAttribute('gateway_order_id', $data['gateway_order_id']);
                }
                if (empty($payment->getAttributes()['invoice_number'] ?? '')) {
                    $payment->setAttribute('invoice_number', $payment->generateInvoiceNumber());
                }
                if (empty($payment->getAttributes()['invoice_url'] ?? '')) {
                    $payment->setAttribute('invoice_url', '/employer/invoices/' . (int)($payment->getAttributes()['id'] ?? 0));
                }
                $payment->save();

                // Update/Create EmployerPayment (Ledger)
                $gatewayPaymentId = $payment->attributes['gateway_payment_id'];
                $orderId = $payment->attributes['gateway_order_id'];
                
                if ($empPay) {
                     $meta = json_decode($empPay['meta'] ?? '{}', true);
                     $meta['razorpay'] = ['payment_id' => $gatewayPaymentId, 'order_id' => $orderId];
                     $db->query('UPDATE employer_payments SET status = "success", txn_id = :txn, meta = :meta, gateway = "razorpay" WHERE id = :id', [
                         'txn' => $gatewayPaymentId,
                         'meta' => json_encode($meta),
                         'id' => $empPay['id']
                     ]);
                } else {
                    // Create employer payment record if missing
                    $db->query('INSERT INTO employer_payments (employer_id, amount, currency, gateway, payment_method, status, txn_id, meta, created_at) VALUES (:eid, :amt, :curr, "razorpay", "checkout", "success", :txn, :meta, NOW())', [
                        'eid' => $payment->attributes['employer_id'],
                        'amt' => $payment->attributes['amount'],
                        'curr' => $payment->attributes['currency'],
                        'txn' => $gatewayPaymentId,
                        'meta' => json_encode(['subscription_payment_id' => $paymentId, 'razorpay' => ['payment_id' => $gatewayPaymentId, 'order_id' => $orderId]])
                    ]);
                }

                // Activate subscription
                $subscription = \App\Models\EmployerSubscription::find((int)($payment->attributes['subscription_id'] ?? 0));
                if ($subscription) {
                    $subscription->attributes['status'] = 'active';
                    $subscription->save();
                }

                if ($db->inTransaction()) { $db->commit(); }

                $response->json([
                    'success' => true,
                    'message' => 'Payment successful',
                    'subscription_id' => $subscription ? $subscription->attributes['id'] : null
                ]);
            } else {
                // Pending state for UPI/netbanking in test mode
                if (((string)($data['upi_id'] ?? '')) === 'pending@razorpay' || ($data['method_type'] ?? '') === 'netbanking') {
                    $payment->setAttribute('status', 'pending');
                    $payment->save();
                    if ($db->inTransaction()) { $db->commit(); }
                    $response->json(['message' => 'Payment under processing']);
                } else {
                    $payment->setAttribute('status', 'failed');
                    $payment->setAttribute('failure_reason', 'Payment verification failed');
                    $payment->save();
                    if ($db->inTransaction()) { $db->commit(); }
                    $response->json(['error' => 'Payment verification failed'], 400);
                }
            }
        } catch (\Exception $e) {
            if ($db->inTransaction()) { $db->rollback(); }
            error_log('Payment Callback Error: ' . $e->getMessage());
            $response->json(['error' => 'An error occurred processing payment'], 500);
        }
    }

    /**
     * View current subscription dashboard
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
        
        // Get usage statistics
        $usage = [
            'contacts_used' => $subscription ? (int)$subscription->attributes['contacts_used_this_month'] : 0,
            'contacts_limit' => $plan ? $plan->getLimit('max_contacts_per_month') : 0,
            'resume_downloads_used' => $subscription ? (int)$subscription->attributes['resume_downloads_used_this_month'] : 0,
            'resume_downloads_limit' => $plan ? $plan->getLimit('max_resume_downloads') : 0,
            'chat_messages_used' => $subscription ? (int)$subscription->attributes['chat_messages_used_this_month'] : 0,
            'chat_messages_limit' => $plan ? $plan->getLimit('max_chat_messages') : 0,
            'job_posts_used' => $subscription ? (int)$subscription->attributes['job_posts_used'] : 0,
            'job_posts_limit' => $plan ? $plan->getLimit('max_job_posts') : 0
        ];

        // Get payment history
        $payments = $subscription ? $subscription->payments() : [];
        $payments = array_map(fn($p) => $p->attributes, $payments);

        $response->view('employer/subscription/dashboard', [
            'title' => 'Subscription Dashboard',
            'employer' => $employer,
            'subscription' => $subscription ? $subscription->attributes : null,
            'plan' => $plan ? $plan->attributes : null,
            'usage' => $usage,
            'payments' => $payments
        ], 200, 'employer/layout');
    }

    /**
     * Upgrade or downgrade subscription
     */
    public function changePlan(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        $data = $request->getJsonBody() ?? $request->all();
        $newPlanSlug = $data['plan_slug'] ?? '';
        $billingCycle = $data['billing_cycle'] ?? 'monthly';

        $currentSubscription = EmployerSubscription::getCurrentForEmployer($employer->id);
        if (!$currentSubscription) {
            $response->json(['error' => 'No active subscription found'], 404);
            return;
        }

        $newPlan = SubscriptionPlan::findBySlug($newPlanSlug);
        if (!$newPlan) {
            $response->json(['error' => 'Plan not found'], 404);
            return;
        }

        // Calculate prorated amount
        $remainingDays = $this->getRemainingDays($currentSubscription->attributes['expires_at']);
        $currentPlan = $currentSubscription->plan();
        $currentPrice = $currentPlan ? $currentPlan->getPrice($currentSubscription->attributes['billing_cycle']) : 0;
        $newPrice = $newPlan->getPrice($billingCycle);

        $proratedAmount = $this->calculateProratedAmount(
            $currentPrice,
            $newPrice,
            $remainingDays,
            $currentSubscription->attributes['billing_cycle'],
            $billingCycle
        );

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // Update subscription (avoid indirect modification notices)
            $currentSubscription->setAttribute('plan_id', $newPlan->id);
            $currentSubscription->setAttribute('billing_cycle', $billingCycle);
            $currentSubscription->setAttribute('expires_at', $this->calculateExpiryDate(
                date('Y-m-d H:i:s'),
                $billingCycle
            ));
            $currentSubscription->save();

            // Create payment if upgrade
            if ($proratedAmount > 0) {
                $payment = new SubscriptionPayment();
                $payment->fill([
                    'subscription_id' => $currentSubscription->attributes['id'],
                    'employer_id' => $employer->id,
                    'amount' => $proratedAmount,
                    'currency' => 'INR',
                    'billing_cycle' => $billingCycle,
                    'status' => 'pending',
                    'metadata' => json_encode(['type' => 'plan_change', 'prorated' => true])
                ]);
                $payment->save();

                $db->commit();

                $response->json([
                    'success' => true,
                    'message' => 'Plan changed successfully',
                    'requires_payment' => true,
                    'amount' => $proratedAmount,
                    'payment_id' => $payment->attributes['id']
                ]);
            } else {
                $db->commit();

                $response->json([
                    'success' => true,
                    'message' => 'Plan changed successfully',
                    'requires_payment' => false
                ]);
            }
        } catch (\Exception $e) {
            $db->rollback();
            error_log('Change Plan Error: ' . $e->getMessage());
            $response->json(['error' => 'Failed to change plan'], 500);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        $data = $request->getJsonBody() ?? $request->all();
        $reason = $data['reason'] ?? '';

        $subscription = EmployerSubscription::getCurrentForEmployer($employer->id);
        if (!$subscription) {
            $response->json(['error' => 'No active subscription found'], 404);
            return;
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $subscription->attributes['status'] = 'cancelled';
            $subscription->attributes['cancelled_at'] = date('Y-m-d H:i:s');
            $subscription->attributes['cancellation_reason'] = $reason;
            $subscription->attributes['auto_renew'] = 0;
            $subscription->save();

            $db->commit();

            $response->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully'
            ]);
        } catch (\Exception $e) {
            $db->rollback();
            error_log('Cancel Subscription Error: ' . $e->getMessage());
            $response->json(['error' => 'Failed to cancel subscription'], 500);
        }
    }

    /**
     * Renew subscription
     */
    public function renew(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        $subscription = EmployerSubscription::getCurrentForEmployer((int)$employer->id);

        if (!$subscription) {
            $response->json(['error' => 'No subscription found'], 404);
            return;
        }

        $plan = $subscription->plan();
        if (!$plan) {
            $response->json(['error' => 'Plan not found'], 404);
            return;
        }

        $billingCycle = $subscription->attributes['billing_cycle'] ?? 'monthly';
        $price = $plan->getPrice($billingCycle);

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // Create new payment
            $payment = new SubscriptionPayment();
            $payment->fill([
                'subscription_id' => $subscription->attributes['id'],
                'employer_id' => $employer->id,
                'amount' => $price,
                'currency' => 'INR',
                'billing_cycle' => $billingCycle,
                'status' => 'pending'
            ]);
            $payment->save();

            $db->commit();

            $response->json([
                'success' => true,
                'payment_id' => $payment->attributes['id'],
                'amount' => $price,
                'payment_gateway' => $this->initiatePayment($payment, $employer)
            ]);
        } catch (\Exception $e) {
            $db->rollback();
            error_log('Renew Subscription Error: ' . $e->getMessage());
            $response->json(['error' => 'Failed to renew subscription'], 500);
        }
    }

    private function configureSslCa(): void
    {
        $envPath = $_ENV['CA_BUNDLE_PATH'] ?? getenv('CA_BUNDLE_PATH') ?: null;
        $possible = [
            $envPath,
            __DIR__ . '/../../../vendor/guzzlehttp/guzzle/src/cacert.pem',
            'E:/xampp/php/extras/ssl/cacert.pem',
            'C:/xampp/php/extras/ssl/cacert.pem',
            'E:/xampp/apache/bin/curl-ca-bundle.crt',
            'C:/xampp/apache/bin/curl-ca-bundle.crt',
            ini_get('curl.cainfo'),
            ini_get('openssl.cafile'),
        ];
        foreach ($possible as $path) {
            if ($path && file_exists((string)$path)) {
                @ini_set('curl.cainfo', (string)$path);
                @putenv('CURL_CA_BUNDLE=' . (string)$path);
                @putenv('SSL_CERT_FILE=' . (string)$path);
                break;
            }
        }
    }

    /**
     * Run auto-renew for subscriptions due now
     */
    public function runAutoRenew(Request $request, Response $response): void
    {
        $now = date('Y-m-d H:i:s');
        $db = \App\Core\Database::getInstance();
        $due = $db->fetchAll("SELECT * FROM employer_subscriptions WHERE auto_renew = 1 AND next_billing_date IS NOT NULL AND next_billing_date <= ?", [$now]);
        $processed = [];
        foreach ($due as $row) {
            $sub = new EmployerSubscription($row);
            $plan = $sub->plan();
            if (!$plan) { continue; }
            $price = $plan->getPrice($sub->attributes['billing_cycle'] ?? 'monthly');
            $payment = new SubscriptionPayment();
            $payment->fill([
                'subscription_id' => $sub->attributes['id'],
                'employer_id' => $sub->attributes['employer_id'],
                'amount' => $price,
                'currency' => 'INR',
                'billing_cycle' => $sub->attributes['billing_cycle'] ?? 'monthly',
                'status' => 'pending'
            ]);
            $payment->save();
            
            // NOTE: Automatic charge is not implemented here. 
            // We just create the pending payment. A separate process or user action is needed to pay.
            
            $processed[] = $payment->attributes['id'];
        }
        $response->json(['processed' => $processed]);
    }

    // Helper methods
    private function calculateExpiryDate(string $startDate, string $billingCycle): string
    {
        $days = [
            'monthly' => 30,
            'quarterly' => 90,
            'annual' => 365
        ];
        
        $daysToAdd = $days[$billingCycle] ?? 30;
        return date('Y-m-d H:i:s', strtotime("+{$daysToAdd} days", strtotime($startDate)));
    }

    private function getRemainingDays(string $expiryDate): int
    {
        $expiry = strtotime($expiryDate);
        $now = time();
        $diff = $expiry - $now;
        return max(0, (int)ceil($diff / 86400));
    }

    private function calculateProratedAmount(
        float $currentPrice,
        float $newPrice,
        int $remainingDays,
        string $currentCycle,
        string $newCycle
    ): float {
        // Simple proration: calculate daily rate and apply
        $currentDays = ['monthly' => 30, 'quarterly' => 90, 'annual' => 365][$currentCycle] ?? 30;
        $newDays = ['monthly' => 30, 'quarterly' => 90, 'annual' => 365][$newCycle] ?? 30;
        
        $currentDailyRate = $currentPrice / $currentDays;
        $newDailyRate = $newPrice / $newDays;
        
        $refund = $currentDailyRate * $remainingDays;
        $charge = $newDailyRate * $newDays;
        
        return max(0, $charge - $refund);
    }

    private function initiatePayment(SubscriptionPayment $payment, Employer $employer): array
    {
        $config = require __DIR__ . '/../../../config/razorpay.php';
        $this->configureSslCa();

        $api = new Api($config['key_id'], $config['key_secret']);
        $amount = (int)round($payment->attributes['amount'] * 100);

        try {
            $order = $api->order->create([
                'receipt' => 'SUB-' . $payment->attributes['id'],
                'amount' => $amount,
                'currency' => 'INR',
                'payment_capture' => 1,
                'notes' => [
                    'employer_id' => (int)$employer->id,
                    'subscription_payment_id' => (int)$payment->attributes['id'],
                ]
            ]);

            // Save order ID to payment record
            $payment->setAttribute('gateway_order_id', $order['id']);
            $payment->save();

            return [
                'gateway' => 'razorpay',
                'order_id' => $order['id'],
                'amount' => $amount,
                'currency' => 'INR',
                'key' => $config['key_id'],
                'name' => 'MindInfotech Subscription',
                'description' => 'Payment for subscription',
                'prefill' => [
                    'name' => $employer->company_name ?? '',
                    'email' => $this->currentUser->email ?? '',
                    'contact' => $employer->phone ?? '',
                ],
                'callback_url' => ($_ENV['APP_URL'] ?? 'http://localhost') . '/employer/subscription/payment/callback'
            ];
        } catch (\Exception $e) {
            error_log('Razorpay Order Creation Failed: ' . $e->getMessage());
            throw new \RuntimeException('Payment initialization failed: ' . $e->getMessage());
        }
    }

    private function verifyPayment(SubscriptionPayment $payment, array $data): bool
    {
        $config = require __DIR__ . '/../../../config/razorpay.php';
        $this->configureSslCa();
        $api = new Api($config['key_id'], $config['key_secret']);

        try {
            // If we have payment_id but no signature (e.g. manual check or fallback), check status
            if (empty($data['razorpay_signature']) && !empty($data['razorpay_payment_id'])) {
                 // Fetch payment
                 $rzpPayment = $api->payment->fetch($data['razorpay_payment_id']);
                 if ($rzpPayment->status === 'captured' || $rzpPayment->status === 'authorized') {
                     if ($rzpPayment->status === 'authorized') {
                         $rzpPayment->capture(['amount' => $rzpPayment->amount, 'currency' => $rzpPayment->currency]);
                     }
                     return true;
                 }
                 return false;
            }

            $attributes = [
                'razorpay_order_id' => $data['razorpay_order_id'] ?? '',
                'razorpay_payment_id' => $data['razorpay_payment_id'] ?? '',
                'razorpay_signature' => $data['razorpay_signature'] ?? ''
            ];
            $api->utility->verifyPaymentSignature($attributes);
            
            // Fetch payment to confirm status and amount
            $rzpPayment = $api->payment->fetch($attributes['razorpay_payment_id']);
            
            // Check if amount matches
            $expectedAmount = (int)round($payment->attributes['amount'] * 100);
            if ((int)$rzpPayment->amount !== $expectedAmount) {
                error_log("Amount mismatch: expected $expectedAmount, got {$rzpPayment->amount}");
                return false;
            }

            if ($rzpPayment->status === 'authorized') {
                 $rzpPayment->capture(['amount' => $rzpPayment->amount, 'currency' => $rzpPayment->currency]);
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("Payment verification failed: " . $e->getMessage());
            return false;
        }
    }
}
