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
use App\Services\EmployerBillingDataService;

class BillingController extends BaseController
{
    public function overview(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $billing = (new EmployerBillingDataService())->overview($employer);
        $subscription = !empty($billing['subscription']) ? new EmployerSubscription($billing['subscription']) : null;
        $plan = !empty($billing['current_plan']) ? new SubscriptionPlan($billing['current_plan']) : null;

        $response->view('employer/billing/overview', [
            'title' => 'Billing Overview',
            'employer' => $employer,
            'subscription' => $subscription,
            'plan' => $plan,
            'balanceDue' => $billing['balance_due'] ?? 0.0,
            'upcomingDate' => $billing['upcoming_date'] ?? null,
            'upcomingAmount' => $billing['upcoming_amount'] ?? null,
            'lastPayment' => $billing['last_payment'] ?? null,
            'recentTransactions' => $billing['recent_transactions'] ?? [],
            'alerts' => $billing['alerts'] ?? []
        ], 200, 'employer/layout');
    }

    public function transactions(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $billing = (new EmployerBillingDataService())->transactions($employer, $request->get());

        $response->view('employer/billing/transactions', [
            'title' => 'Transactions',
            'employer' => $employer,
            'rows' => $billing['rows'] ?? [],
            'filters' => $billing['filters'] ?? [],
            'summary' => $billing['summary'] ?? [],
            'pagination' => $billing['pagination'] ?? []
        ], 200, 'employer/layout');
    }

    public function invoices(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $billing = (new EmployerBillingDataService())->invoices($employer, $request->get());

        $response->view('employer/billing/invoices', [
            'title' => 'Invoices',
            'employer' => $employer,
            'invoices' => $billing['invoices'] ?? [],
            'filters' => $billing['filters'] ?? []
        ], 200, 'employer/layout');
    }

    public function paymentMethods(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        
        $billing = (new EmployerBillingDataService())->paymentMethods($employer);
        $methods = $billing['methods'] ?? [];

        $response->view('employer/billing/payment_methods', [
            'title' => 'Payment Methods',
            'employer' => $employer,
            'methods' => $methods,
            'defaultMethod' => $billing['default_method'] ?? null
        ], 200, 'employer/layout');
    }

    public function settings(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $billing = (new EmployerBillingDataService())->settings($employer);
        $response->view('employer/billing/settings', [
            'title' => 'Billing Settings',
            'employer' => $employer,
            'billingProfile' => $billing['billing_profile'] ?? [],
            'subscription' => $billing['subscription'] ?? null,
            'currentPlan' => $billing['current_plan'] ?? null,
            'paymentMethods' => $billing['payment_methods'] ?? []
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

    public function paymentSuccess(Request $request, Response $response): void
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
