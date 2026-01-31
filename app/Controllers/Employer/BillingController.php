<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\EmployerSubscription;
use App\Models\SubscriptionPayment;
use App\Models\EmployerPayment;
use App\Models\SubscriptionPlan;
use App\Models\Employer;

class BillingController extends BaseController
{
    public function overview(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $subscription = EmployerSubscription::getCurrentForEmployer((int)$employer->id);

        $unpaidRows = SubscriptionPayment::where('employer_id', '=', $employer->id)
            ->where('status', '!=', 'completed')->get();
        $unpaid = 0.0;
        foreach ($unpaidRows as $r) {
            $unpaid += (float)($r->attributes['amount'] ?? 0);
        }
        $lastPayment = SubscriptionPayment::where('employer_id', '=', $employer->id)
            ->orderBy('created_at', 'DESC')->first();

        $plan = null;
        $upcomingAmount = null;
        $upcomingDate = null;
        if ($subscription) {
            $plan = $subscription->plan();
            $upcomingDate = $subscription->attributes['next_billing_date'] ?? null;
            $upcomingAmount = $plan ? ($plan->attributes['price_monthly'] ?? $plan->attributes['price'] ?? null) : null;
        }

        $combined = [];
        try {
            $subPayments = SubscriptionPayment::where('employer_id', '=', $employer->id)
                ->orderBy('created_at', 'DESC')->limit(5)->get();
            $rows = array_map(fn($p) => $p->toArray() + ['kind' => 'subscription'], $subPayments);
            $addonPayments = EmployerPayment::where('employer_id', '=', $employer->id)
                ->orderBy('created_at', 'DESC')->limit(5)->get();
            $rows = array_merge($rows, array_map(fn($p) => $p->toArray() + ['kind' => 'addon'], $addonPayments));
            usort($rows, function ($a, $b) {
                $ta = strtotime($a['created_at'] ?? '1970-01-01');
                $tb = strtotime($b['created_at'] ?? '1970-01-01');
                return $tb <=> $ta;
            });
            $combined = array_slice($rows, 0, 5);
        } catch (\Throwable $t) {
            $combined = [];
        }

        $response->view('employer/billing/overview', [
            'title' => 'Billing Overview',
            'employer' => $employer,
            'subscription' => $subscription,
            'plan' => $plan,
            'balanceDue' => $unpaid,
            'upcomingDate' => $upcomingDate,
            'upcomingAmount' => $upcomingAmount,
            'lastPayment' => $lastPayment ? $lastPayment->toArray() : null,
            'recentTransactions' => $combined
        ], 200, 'employer/layout');
    }

    public function transactions(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $from = $request->get('from');
        $to = $request->get('to');
        $status = $request->get('status');
        $method = $request->get('method');
        $product = $request->get('product');

        $subQ = SubscriptionPayment::where('employer_id', '=', $employer->id);
        if ($status && $status !== 'all') { $subQ = $subQ->where('status', '=', $status); }
        if ($from) { $subQ = $subQ->where('created_at', '>=', $from); }
        if ($to) { $subQ = $subQ->where('created_at', '<=', $to); }
        if ($method && $method !== 'all') { $subQ = $subQ->where('gateway', '=', $method); }
        $subscriptionPayments = $subQ->orderBy('created_at', 'DESC')->limit(300)->get();

        $addQ = EmployerPayment::where('employer_id', '=', $employer->id);
        if ($status && $status !== 'all') { $addQ = $addQ->where('status', '=', $status); }
        if ($from) { $addQ = $addQ->where('created_at', '>=', $from); }
        if ($to) { $addQ = $addQ->where('created_at', '<=', $to); }
        $employerPayments = $addQ->orderBy('created_at', 'DESC')->limit(300)->get();

        // Summary metrics
        $subArr = array_map(fn($p) => $p->toArray() + ['kind' => 'subscription'], $subscriptionPayments);
        $addArr = array_map(fn($p) => $p->toArray() + ['kind' => 'addon'], $employerPayments);
        $rows = array_merge($subArr, $addArr);

        // Sort by date DESC
        usort($rows, function ($a, $b) {
            $ta = strtotime($a['created_at'] ?? 'now');
            $tb = strtotime($b['created_at'] ?? 'now');
            return $tb <=> $ta;
        });

        $totalTransactions = count($rows);
        $totalPaid = 0.0;
        $pendingAmount = 0.0;
        $failedCount = 0;
        foreach ($rows as $row) {
            $amt = (float)($row['amount'] ?? 0);
            $st = strtolower((string)($row['status'] ?? ''));
            if ($st === 'completed' || $st === 'success') { $totalPaid += $amt; }
            elseif ($st === 'pending') { $pendingAmount += $amt; }
            elseif ($st === 'failed' || $st === 'refunded') { $failedCount += 1; }
        }

        // Pagination (simple client-side style for now, or just pass all since limit is 300)
        $page = (int)($request->get('page') ?? 1);
        $perPage = 20;
        $total = count($rows);
        $pages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $pagedRows = array_slice($rows, $offset, $perPage);

        $response->view('employer/billing/transactions', [
            'title' => 'Transactions',
            'employer' => $employer,
            'rows' => $pagedRows,
            'filters' => [ 'from' => $from, 'to' => $to, 'status' => $status, 'method' => $method, 'product' => $product ],
            'summary' => [
                'total' => $totalTransactions,
                'paid' => $totalPaid,
                'pending' => $pendingAmount,
                'failed' => $failedCount
            ],
            'pagination' => [
                'page' => $page,
                'pages' => $pages,
                'total' => $total,
                'per_page' => $perPage
            ]
        ], 200, 'employer/layout');
    }

    public function invoices(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $from = $request->get('from');
        $to = $request->get('to');
        $status = $request->get('status');

        $query = SubscriptionPayment::where('employer_id', '=', $employer->id);
        if ($status && $status !== 'all') { $query = $query->where('status', '=', $status); }
        if ($from) { $query = $query->where('created_at', '>=', $from); }
        if ($to) { $query = $query->where('created_at', '<=', $to); }
        $payments = $query->orderBy('created_at', 'DESC')->limit(300)->get();

        $response->view('employer/billing/invoices', [
            'title' => 'Invoices',
            'employer' => $employer,
            'invoices' => array_map(fn($p) => $p->toArray(), $payments),
            'filters' => [ 'from' => $from, 'to' => $to, 'status' => $status ]
        ], 200, 'employer/layout');
    }

    public function paymentMethods(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $savedMethods = [];
        $response->view('employer/billing/payment_methods', [
            'title' => 'Payment Methods',
            'employer' => $employer,
            'methods' => $savedMethods
        ], 200, 'employer/layout');
    }

    public function settings(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $response->view('employer/billing/settings', [
            'title' => 'Billing Settings',
            'employer' => $employer
        ], 200, 'employer/layout');
    }

    public function savePaymentMethod(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $type = $request->post('method_type');
        $last4 = substr(preg_replace('/\D+/', '', (string)$request->post('card_number')), -4) ?: '';
        $upiId = $request->post('upi_id');
        $bank = $request->post('netbanking_bank');
        $paymentId = (int)$request->post('payment_id');
        $otp = (string)$request->post('otp');

        $saved = [];
        if ($type === 'card' && $last4) {
            $saved[] = ['label' => 'Card • • • • ' . $last4, 'details' => 'Saved for ' . ($employer->attributes['company_name'] ?? 'your account')];
        } elseif ($type === 'upi' && $upiId) {
            $saved[] = ['label' => 'UPI', 'details' => $upiId];
        } elseif ($type === 'netbanking' && $bank) {
            $saved[] = ['label' => 'Netbanking', 'details' => $bank];
        }

        // If paying a specific subscription payment, simulate gateway behavior in test mode
        if ($paymentId > 0) {
            $payment = \App\Models\SubscriptionPayment::find($paymentId);
            if ($payment) {
                $currentStatus = $payment->attributes['status'] ?? 'pending';
                if (in_array($currentStatus, ['completed','failed','refunded'])) {
                    $response->view('employer/billing/payment_methods', [
                        'title' => 'Payment Methods',
                        'employer' => $employer,
                        'methods' => $saved,
                        'message' => 'This transaction is already processed.'
                    ], 200, 'employer/layout');
                    return;
                }

                $status = 'pending';
                $reason = null;
                $mode = defined('PAYMENT_MODE') ? \PAYMENT_MODE : ($_ENV['PAYMENT_MODE'] ?? getenv('PAYMENT_MODE') ?? 'test');
                if ($mode === 'test') {
                    if ($type === 'card') {
                        $num = preg_replace('/\D+/', '', (string)$request->post('card_number'));
                        if ($otp !== '' && $otp !== '1234') {
                            $status = 'failed';
                            $reason = 'wrong_otp';
                        } elseif ($num === '4111111111111111' || $num === '4242424242424242') {
                            $status = 'completed';
                        } elseif ($num === '4000000000000002' || $num === '4000000000009995') {
                            $status = 'failed';
                            $reason = 'card_declined';
                        } elseif ($num === '4000030000000001') {
                            $status = 'failed';
                            $reason = 'insufficient_balance';
                        } else {
                            $status = 'completed';
                        }
                    } elseif ($type === 'upi') {
                        if ($upiId === 'success@razorpay') {
                            $status = 'completed';
                        } elseif ($upiId === 'failure@razorpay') {
                            $status = 'failed';
                            $reason = 'upi_failure';
                        } elseif ($upiId === 'pending@razorpay') {
                            $status = 'pending';
                        } else {
                            $status = 'completed';
                        }
                    } elseif ($type === 'netbanking') {
                        // Simulate delayed processing
                        $status = 'pending';
                    }
                }

                if ($status === 'completed') {
                    $payment->markAsCompleted('test_pay_' . uniqid(), 'test_ord_' . uniqid());
                    // Activate subscription
                    $subscription = $payment->subscription();
                    if ($subscription) {
                        $subscription->attributes['status'] = 'active';
                        $subscription->save();
                    }
                    $message = 'Payment successful';
                } elseif ($status === 'failed') {
                    $payment->markAsFailed($reason ?: 'payment_failed');
                    $message = 'Payment failed';
                } else {
                    // Keep pending
                    $payment->attributes['status'] = 'pending';
                    $payment->save();
                    $message = 'Payment under processing';
                }

                $response->view('employer/billing/payment_methods', [
                    'title' => 'Payment Methods',
                    'employer' => $employer,
                    'methods' => $saved,
                    'message' => $message
                ], 200, 'employer/layout');
                return;
            }
        }

        $response->view('employer/billing/payment_methods', [
            'title' => 'Payment Methods',
            'employer' => $employer,
            'methods' => $saved,
            'message' => 'Payment method saved'
        ], 200, 'employer/layout');
    }

    public function pay(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $paymentId = (int)$request->param('id');
        if ($paymentId > 0) {
            $response->redirect('/payment/create-order?payment_id=' . (int)$paymentId);
            return;
        }
        $response->view('employer/billing/payment_methods', [
            'title' => 'Choose a Payment Method',
            'employer' => $employer,
            'methods' => [],
            'paymentId' => $paymentId
        ], 200, 'employer/layout');
    }

    public function success(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $subPayId = (int)$request->get('sub_pay_id');
        $response->view('employer/billing/success', [
            'title' => 'Payment Successful',
            'employer' => $employer,
            'subPayId' => $subPayId
        ], 200, 'employer/layout');
    }

    public function failed(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $reason = (string)($request->get('reason') ?? 'Payment failed');
        $response->view('employer/billing/failed', [
            'title' => 'Payment Failed',
            'employer' => $employer,
            'reason' => $reason
        ], 200, 'employer/layout');
    }
}
