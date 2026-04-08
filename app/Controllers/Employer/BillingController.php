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
use App\Models\PaymentMethod;

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
        
        $savedMethods = PaymentMethod::getForEmployer((int)$employer->id);
        $methods = array_map(function($m) {
            $attr = $m->attributes;
            if ($attr['method_type'] === 'card') {
                $attr['label'] = ($attr['brand'] ?: 'Card') . ' • • • • ' . $attr['last4'];
                $attr['details'] = 'Expires ' . $attr['exp_month'] . '/' . $attr['exp_year'];
            } elseif ($attr['method_type'] === 'upi') {
                $attr['label'] = 'UPI';
                $attr['details'] = $attr['token']; // We use token field for VPA in this simple setup
            }
            return $attr;
        }, $savedMethods);

        $response->view('employer/billing/payment_methods', [
            'title' => 'Payment Methods',
            'employer' => $employer,
            'methods' => $methods
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
        
        $data = $request->getJsonBody() ?? $request->all();
        $type = $data['method_type'] ?? 'card';
        $setDefault = (int)($data['set_default'] ?? 0) === 1;

        try {
            $db = \App\Core\Database::getInstance();
            $db->beginTransaction();

            if ($type === 'card') {
                $cardNumber = preg_replace('/\D+/', '', (string)($data['card_number'] ?? ''));
                $last4 = substr($cardNumber, -4);
                $brand = ucfirst((string)($data['brand'] ?? 'card'));
                $expiry = (string)($data['card_expiry'] ?? '');
                $expParts = explode('/', $expiry);
                
                if (strlen($cardNumber) < 13 || count($expParts) !== 2) {
                    throw new \Exception("Invalid card data provided");
                }

                $method = new PaymentMethod();
                $method->fill([
                    'employer_id' => $employer->id,
                    'gateway' => 'razorpay',
                    'token' => 'tok_' . bin2hex(random_bytes(8)), 
                    'method_type' => 'card',
                    'last4' => $last4,
                    'brand' => $brand,
                    'exp_month' => (int)$expParts[0],
                    'exp_year' => (int)$expParts[1],
                    'is_default' => $setDefault ? 1 : 0
                ]);
            } else {
                $upiId = strtolower(trim((string)($data['upi_id'] ?? '')));
                if (!preg_match('/^[\w.-]+@[\w.-]+$/', $upiId)) {
                    throw new \Exception("Invalid UPI ID format");
                }

                $method = new PaymentMethod();
                $method->fill([
                    'employer_id' => $employer->id,
                    'gateway' => 'razorpay',
                    'token' => $upiId,
                    'method_type' => 'upi',
                    'is_default' => $setDefault ? 1 : 0
                ]);
            }

            if ($setDefault) {
                $db->execute("UPDATE payment_methods SET is_default = 0 WHERE employer_id = :eid", ['eid' => $employer->id]);
            }

            if (!$method->save()) {
                throw new \Exception("Failed to save to database");
            }

            $db->commit();
            
            if ($request->isXmlHttpRequest() || $request->header('Accept') === 'application/json') {
                $response->json(['status' => true, 'message' => 'Payment method saved successfully']);
                return;
            }
        } catch (\Exception $e) {
            if ($db->inTransaction()) $db->rollback();
            if ($request->isXmlHttpRequest()) {
                $response->error($e->getMessage());
                return;
            }
            $message = $e->getMessage();
        }

        $response->redirect('/employer/billing/payment-methods');
    }

    public function deletePaymentMethod(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $id = (int)$request->param('id');

        $method = PaymentMethod::find($id);
        if ($method && (int)$method->employer_id === (int)$employer->id) {
            $method->delete();
            $response->json(['status' => true, 'message' => 'Payment method deleted']);
        } else {
            $response->error('Payment method not found', 404);
        }
    }

    public function setDefaultPaymentMethod(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $id = (int)$request->param('id');

        $db = \App\Core\Database::getInstance();
        $db->beginTransaction();
        try {
            // Reset all
            $db->execute("UPDATE payment_methods SET is_default = 0 WHERE employer_id = :eid", ['eid' => $employer->id]);
            // Set new
            $db->execute("UPDATE payment_methods SET is_default = 1 WHERE id = :id AND employer_id = :eid", ['id' => $id, 'eid' => $employer->id]);
            $db->commit();
            $response->json(['status' => true, 'message' => 'Default method updated']);
        } catch (\Exception $e) {
            $db->rollback();
            $response->error($e->getMessage());
        }
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
