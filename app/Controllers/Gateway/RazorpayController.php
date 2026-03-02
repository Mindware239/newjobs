<?php

declare(strict_types=1);

namespace App\Controllers\Gateway;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use Razorpay\Api\Api;

class RazorpayController extends BaseController
{
    private function configureSslCa(): void
    {
        $envPath = $_ENV['CA_BUNDLE_PATH'] ?? getenv('CA_BUNDLE_PATH') ?: null;
        if ($envPath && file_exists((string)$envPath)) {
            @ini_set('curl.cainfo', (string)$envPath);
            @putenv('CURL_CA_BUNDLE=' . (string)$envPath);
            @putenv('SSL_CERT_FILE=' . (string)$envPath);
        }
    }

    public function createOrder(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        $employer = $this->currentUser->employer();
        $paymentId = (int)$request->get('payment_id');
        $db = Database::getInstance();
        $config = require __DIR__ . '/../../../config/razorpay.php';

        // If subscription payment already completed, skip creating a new order
        $existingSubPay = $db->fetchOne('SELECT status FROM subscription_payments WHERE id = :id AND employer_id = :eid', [
            'id' => $paymentId,
            'eid' => (int)$employer->id
        ]);
        if ($existingSubPay && ($existingSubPay['status'] ?? '') === 'completed') {
            $response->redirect('/employer/billing/success?sub_pay_id=' . (int)$paymentId);
            return;
        }

        $payRow = $db->fetchOne('SELECT * FROM subscription_payments WHERE id = :id AND employer_id = :eid', [
            'id' => $paymentId,
            'eid' => (int)$employer->id
        ]);
        if (!$payRow) {
            $response->view('employer/billing/payment_methods', [
                'title' => 'Choose a Payment Method',
                'employer' => $employer,
                'methods' => [],
                'message' => 'Payment not found'
            ], 200, 'employer/layout');
            return;
        }

        $amount = (float)($payRow['amount'] ?? 0);
        if ($amount <= 0) {
            $response->view('employer/billing/payment_methods', [
                'title' => 'Choose a Payment Method',
                'employer' => $employer,
                'methods' => [],
                'message' => 'Invalid amount'
            ], 200, 'employer/layout');
            return;
        }

        $db->beginTransaction();
        try {
            $db->query(
                'INSERT INTO employer_payments (employer_id, subscription_payment_id, amount, currency, gateway, payment_method, status, txn_id, meta, created_at)
                 VALUES (:employer_id, :subscription_payment_id, :amount, :currency, :gateway, :payment_method, :status, :txn_id, :meta, NOW())',
                [
                    'employer_id' => (int)$employer->id,
                    'subscription_payment_id' => $paymentId,
                    'amount' => $amount,
                    'currency' => 'INR',
                    'gateway' => 'razorpay',
                    'payment_method' => 'checkout',
                    'status' => 'pending',
                    'txn_id' => null,
                    'meta' => json_encode(['subscription_payment_id' => $paymentId, 'billing_cycle' => $payRow['billing_cycle'] ?? 'monthly'])
                ]
            );
            $employerPaymentId = (int)$db->lastInsertId();

            if (($config['key_id'] ?? '') === 'rzp_test_key' || ($config['key_secret'] ?? '') === 'rzp_test_secret') {
                throw new \RuntimeException('Razorpay test keys not configured. Set RAZORPAY_KEY and RAZORPAY_SECRET.');
            }
            $this->configureSslCa();
            $api = new Api($config['key_id'], $config['key_secret']);
            $order = $api->order->create([
                'receipt' => 'EMP-' . $employerPaymentId,
                'amount' => (int)round($amount * 100),
                'currency' => 'INR',
                'payment_capture' => 1,
                'notes' => [
                    'employer_id' => (int)$employer->id,
                    'subscription_payment_id' => $paymentId,
                ]
            ]);
            $db->query('UPDATE employer_payments SET txn_id = :txn, meta = :meta WHERE id = :id', [
                'txn' => $order['id'],
                'meta' => json_encode(['subscription_payment_id' => $paymentId, 'order' => $order]),
                'id' => $employerPaymentId
            ]);
            $db->commit();
        } catch (\Throwable $e) {
            try {
                $db->query('UPDATE employer_payments SET status = "failed", error_message = :msg WHERE id = :id', [
                    'msg' => $e->getMessage(),
                    'id' => $employerPaymentId ?? 0
                ]);
                $db->commit();
            } catch (\Throwable $inner) {
                $db->rollback();
            }
            $response->view('employer/billing/failed', [
                'title' => 'Payment Failed',
                'employer' => $employer,
                'reason' => 'Payment initialization failed'
            ], 200, 'employer/layout');
            return;
        }

        $response->view('employer/billing/checkout', [
            'title' => 'Pay with Razorpay',
            'employer' => $employer,
            'orderId' => $order['id'],
            'amount' => $amount,
            'empPayId' => $employerPaymentId,
            'subscriptionPaymentId' => $paymentId,
            'key' => $config['key_id'],
            'csrfToken' => \App\Middlewares\CsrfMiddleware::generateToken()
        ], 200, 'employer/layout');
    }

    public function verify(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { 
            return; 
        }
        $token = $request->header('X-CSRF-Token') ?? $request->post('_token');
        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token))
        {
                $response->redirect('/employer/billing/failed?reason=csrf_mismatch');
                return;
        }

        $employer = $this->currentUser->employer();
        $config = require __DIR__ . '/../../../config/razorpay.php';
        $this->configureSslCa();
        $api = new Api($config['key_id'], $config['key_secret']);
        $db = Database::getInstance();

        $paymentId = (string)($request->post('razorpay_payment_id') ?? $request->get('razorpay_payment_id'));
        $orderId = (string)($request->post('razorpay_order_id') ?? $request->get('razorpay_order_id'));
        $signature = (string)($request->post('razorpay_signature') ?? $request->get('razorpay_signature'));
        $subscriptionPaymentId = (int)($request->post('subscription_payment_id') ?? $request->get('subscription_payment_id'));

        if (!$paymentId || !$orderId || !$signature) {
            $response->redirect('/employer/billing/failed?reason=invalid_request');
            return;
        }

        $db->beginTransaction();
        try {
            $empPay = $db->fetchOne('SELECT * FROM employer_payments WHERE txn_id = :oid AND employer_id = :eid FOR UPDATE', [
                'oid' => $orderId,
                'eid' => (int)$employer->id
            ]);
            if (!$empPay) {
                $db->rollback();
                $response->redirect('/employer/billing/failed?reason=payment_record_missing');
                return;
            }
            if (($empPay['status'] ?? '') === 'success') {
                $db->rollback();
                $response->redirect('/employer/billing/success?sub_pay_id=' . (int)$subscriptionPaymentId);
                return;
            }
        } catch (\Throwable $t) {
            $db->rollback();
            $response->redirect('/employer/billing/failed?reason=payment_record_error');
            return;
        }

        try {
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ]);
        } catch (\Exception $e) {
            $response->redirect('/employer/billing/failed?reason=signature_verification_failed');
            return;
        }
        
        // Verify amount and status with Razorpay API directly
        try {
            $rzpPayment = $api->payment->fetch($paymentId);
            if ($rzpPayment->order_id !== $orderId) {
                throw new \Exception('Order ID mismatch');
            }
            if (strtoupper((string)$rzpPayment->currency) !== 'INR') {
                throw new \Exception('Currency mismatch');
            }
            $notes = (array)($rzpPayment->notes ?? []);
            $noteSub = isset($notes['subscription_payment_id']) ? (int)$notes['subscription_payment_id'] : 0;
            $noteEmp = isset($notes['employer_id']) ? (int)$notes['employer_id'] : 0;
            if ($subscriptionPaymentId > 0 && $noteSub !== $subscriptionPaymentId) {
                throw new \Exception('Subscription note mismatch');
            }
            if ((int)$employer->id > 0 && $noteEmp !== (int)$employer->id) {
                throw new \Exception('Employer note mismatch');
            }
            if ($rzpPayment->status !== 'captured' && $rzpPayment->status !== 'authorized') {
                // If authorized but not captured, capture it
                if ($rzpPayment->status === 'authorized') {
                    $rzpPayment->capture(['amount' => $rzpPayment->amount, 'currency' => $rzpPayment->currency]);
                } else {
                     throw new \Exception('Payment not successful');
                }
            }
        } catch (\Exception $e) {
             $response->redirect('/employer/billing/failed?reason=payment_verification_failed');
             return;
        }

        try {
            // Verify Amount
            $storedAmount = (int)round((float)$empPay['amount'] * 100);
            if ($storedAmount !== (int)$rzpPayment->amount) {
                 $db->rollback();
                 $response->redirect('/employer/billing/failed?reason=amount_mismatch');
                 return;
            }

            // Order ID must match the previously stored Razorpay order id
            if ((string)($empPay['txn_id'] ?? '') !== $orderId) {
                $db->rollback();
                $response->redirect('/employer/billing/failed?reason=order_mismatch');
                return;
            }
            $existing = $db->fetchOne('SELECT id FROM employer_payments WHERE txn_id = :pid AND status = "success"', ['pid' => $paymentId]);
            if ($existing) {
                $db->rollback();
                $response->redirect('/employer/billing/success?sub_pay_id=' . (int)$subscriptionPaymentId);
                return;
            }

            $amount = (float)$empPay['amount'];
            $meta = json_decode($empPay['meta'] ?? '{}', true);
            $meta['razorpay'] = ['payment_id' => $paymentId, 'order_id' => $orderId];
            $db->query('UPDATE employer_payments SET status = "success", txn_id = :txn, meta = :meta, gateway = "razorpay", payment_method = "checkout" WHERE id = :id', [
                'txn' => $paymentId,
                'meta' => json_encode($meta),
                'id' => (int)$empPay['id']
            ]);

            $invoiceId = \App\Services\InvoiceService::generate((int)$employer->id, $amount, 'INR', [
                'subscription_payment_id' => $subscriptionPaymentId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_order_id' => $orderId,
            ]);

            if ($invoiceId) {
                $db->query('UPDATE employer_payments SET invoice_id = :inv WHERE id = :id', ['inv' => $invoiceId, 'id' => (int)$empPay['id']]);
            }

            if ($subscriptionPaymentId > 0) {
                $subPay = $db->fetchOne('SELECT * FROM subscription_payments WHERE id = :id FOR UPDATE', ['id' => $subscriptionPaymentId]);
                if ($subPay) {
                    // Idempotency: skip if already completed
                    if (($subPay['status'] ?? '') !== 'completed') {
                        $subscriptionId = (int)($subPay['subscription_id'] ?? 0);
                        if ($subscriptionId > 0) {
                            $subscription = $db->fetchOne('SELECT * FROM employer_subscriptions WHERE id = :id', ['id' => $subscriptionId]);
                            if ($subscription) {
                                $cycle = strtolower($subscription['billing_cycle'] ?? 'monthly');
                                $expires = match ($cycle) {
                                    'quarterly' => date('Y-m-d H:i:s', strtotime('+3 months')),
                                    'annual' => date('Y-m-d H:i:s', strtotime('+1 year')),
                                    default => date('Y-m-d H:i:s', strtotime('+1 month')),
                                };
                                $db->query('UPDATE employer_subscriptions SET status = "active", expires_at = :exp WHERE id = :id', [
                                    'exp' => $expires,
                                    'id' => $subscriptionId
                                ]);
                            }
                        }
                        $invRow = $db->fetchOne('SELECT invoice_number FROM invoices WHERE id = :id', ['id' => $invoiceId]);
                        $invoiceNumber = $invRow['invoice_number'] ?? null;
                        $invoiceUrl = '/employer/invoices/' . (int)$subscriptionPaymentId;
                        $db->query('UPDATE subscription_payments SET status = "completed", gateway_payment_id = :pid, gateway_order_id = :oid, paid_at = NOW(), invoice_number = :invnum, invoice_url = :url WHERE id = :id', [
                            'pid' => $paymentId,
                            'oid' => $orderId,
                            'invnum' => $invoiceNumber,
                            'url' => $invoiceUrl,
                            'id' => $subscriptionPaymentId
                        ]);
                    }
                }
            }
            $db->commit();
        } catch (\Throwable $t) {
            $db->rollback();
            // Log error safely
            error_log('Payment Processing Error: ' . $t->getMessage());
            $response->redirect('/employer/billing/failed?reason=processing_error');
            return;
        }

        $empUser = $db->fetchOne('SELECT u.id as user_id, u.email FROM users u INNER JOIN employers e ON e.user_id = u.id WHERE e.id = :eid', ['eid' => (int)$employer->id]);
        $userId = (int)($empUser['user_id'] ?? 0);
        if ($userId > 0) {
            $invoiceUrl = '/employer/invoices/' . (int)$subscriptionPaymentId;
            \App\Services\NotificationService::send(
                $userId,
                'payment_receipt',
                'Payment Receipt',
                "Thank you for your payment. Payment ID: {$paymentId}. Amount: ₹" . number_format($amount, 2),
                [
                    'payment_id' => $paymentId,
                    'amount' => $amount,
                    'currency' => 'INR',
                    'invoice_url' => $invoiceUrl,
                    'email_template' => 'payment_receipt'
                ],
                $invoiceUrl,
                ['email','in_app','push']
            );
        }

        $response->redirect('/employer/billing/success?sub_pay_id=' . (int)$subscriptionPaymentId);
    }
}

