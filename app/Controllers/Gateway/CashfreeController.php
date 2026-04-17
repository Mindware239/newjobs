<?php

declare(strict_types=1);

namespace App\Controllers\Gateway;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\SubscriptionPayment;
use App\Models\Employer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Helpers\SslHelper;

class CashfreeController extends BaseController
{
    private array $config;

    private function isApi(Request $request): bool
    {
        return stripos((string)$request->header('Accept'), 'application/json') !== false;
    }

    private function apiSuccess(Response $response, string $message, array $data = [], int $code = 200): void
    {
        $response->json(['status' => true, 'message' => $message, 'data' => $data, 'errors' => null], $code, $message, true, null);
    }

    private function apiError(Response $response, string $message, int $code = 400, array $data = []): void
    {
        $response->json(['status' => false, 'message' => $message, 'data' => $data, 'errors' => ['error' => $message]], $code, $message, false, ['error' => $message]);
    }

    public function __construct()
    {
        parent::__construct();
        $this->config = require __DIR__ . '/../../../config/cashfree.php';
    }

    /**
     * Create a Cashfree Order
     */
    public function createOrder(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) { return; }
        
        $paymentId = (int)$request->get('payment_id');
        $employer = $this->currentUser->employer();
        $db = Database::getInstance();

        // Get payment details
        $payRow = $db->fetchOne('SELECT * FROM subscription_payments WHERE id = :id AND employer_id = :eid', [
            'id' => $paymentId,
            'eid' => (int)$employer->id
        ]);

        if (!$payRow || ($payRow['status'] ?? '') === 'completed') {
            $response->redirect('/employer/subscription/dashboard');
            return;
        }

        $amount = (float)($payRow['amount'] ?? 0);
        $orderId = 'SUB-' . $paymentId . '-' . time();

        // Sanitize phone: take only digits and ensure it is 10 chars for India
        $phoneRaw = $employer->phone ?? '9999999999';
        $phone = preg_replace('/\D+/', '', $phoneRaw);
        if (strlen($phone) > 10) {
            $phone = substr($phone, -10);
        } elseif (strlen($phone) < 10) {
            $phone = str_pad($phone, 10, '0', STR_PAD_LEFT);
        }

        $client = new Client([
            'base_uri' => $this->config['base_url'],
            'verify' => $this->configureSslCa(), // Configure SSL CA for local environments
            'headers' => [
                'x-client-id' => $this->config['app_id'],
                'x-client-secret' => $this->config['secret_key'],
                'x-api-version' => $this->config['api_version'],
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]);

        try {
            $payload = [
                'order_id' => $orderId,
                'order_amount' => (float)$amount,
                'order_currency' => 'INR',
                'customer_details' => [
                    'customer_id' => 'EMP-' . $employer->id,
                    'customer_email' => $this->currentUser->email ?? '',
                    'customer_phone' => $phone,
                ],
                'order_meta' => [
                    'return_url' => ($_ENV['APP_URL'] ?? 'http://localhost') . '/gateway/cashfree/verify?order_id={order_id}',
                    'notify_url' => ($_ENV['APP_URL'] ?? 'http://localhost') . '/gateway/cashfree/webhook'
                ],
                'order_note' => 'Subscription Payment for ' . ($employer->company_name ?? 'Employer')
            ];

            $res = $client->post('orders', ['json' => $payload]);
            $body = json_decode((string)$res->getBody(), true);

            if (isset($body['payment_session_id'])) {
                // Update payment record
                $db->query(
                    'UPDATE subscription_payments SET gateway = "cashfree", gateway_order_id = :g_order, metadata = :meta WHERE id = :id',
                    [
                        'g_order' => $orderId,
                        'meta' => json_encode(['payment_session_id' => $body['payment_session_id']]),
                        'id' => $paymentId
                    ]
                );

                // Render checkout view
                $responseData = [
                    'payment_session_id' => $body['payment_session_id'],
                    'environment' => $this->config['environment'],
                    'order_id' => $orderId,
                    'payment_url' => $this->config['checkout_url'] ?? null
                ];

                if ($this->isApi($request)) {
                    $this->apiSuccess($response, 'Cashfree order created', $responseData);
                    return;
                }

                $response->view('gateway/cashfree_checkout', [
                    'payment_session_id' => $body['payment_session_id'],
                    'environment' => $this->config['environment'],
                    'order_id' => $orderId
                ], 200, 'employer/layout');
            } else {
                throw new \Exception('Failed to get payment_session_id from Cashfree');
            }

        } catch (RequestException $e) {
            $errorBody = $e->hasResponse() ? (string)$e->getResponse()->getBody() : $e->getMessage();
            error_log('Cashfree Order Creation Failed: ' . $errorBody);
            if ($this->isApi($request)) {
                $this->apiError($response, 'Cashfree API: ' . $errorBody, 500);
                return;
            }
            // Redirect back with error instead of rendering the view to avoid Alpine.js issues
            $response->redirect('/employer/subscription/plans?error=' . urlencode('Cashfree API: ' . $errorBody));
        } catch (\Exception $e) {
            error_log('Cashfree General Error: ' . $e->getMessage());
            if ($this->isApi($request)) {
                $this->apiError($response, 'Payment Error: ' . $e->getMessage(), 500);
                return;
            }
            $response->redirect('/employer/subscription/plans?error=' . urlencode('Payment Error: ' . $e->getMessage()));
        }
    }

    /**
     * Verify Payment after Return
     */
    public function verifyPayment(Request $request, Response $response): void
    {
        $isApi = $this->isApi($request);
        $orderId = $request->get('order_id');
        if (!$orderId) {
            if ($isApi) {
                $this->apiError($response, 'order_id is required', 400);
            } else {
                $response->redirect('/employer/subscription/dashboard');
            }
            return;
        }

        $client = new Client([
            'base_uri' => $this->config['base_url'],
            'verify' => $this->configureSslCa(), // Configure SSL CA
            'headers' => [
                'x-client-id' => $this->config['app_id'],
                'x-client-secret' => $this->config['secret_key'],
                'x-api-version' => $this->config['api_version'],
                'Accept' => 'application/json',
            ]
        ]);

        try {
            $res = $client->get("orders/{$orderId}");
            $body = json_decode((string)$res->getBody(), true);

            if (($body['order_status'] ?? '') === 'PAID') {
                // Extract payment ID from order details
                $paymentsRes = $client->get("orders/{$orderId}/payments");
                $payments = json_decode((string)$paymentsRes->getBody(), true);
                $latestPayment = $payments[0] ?? null;

                if ($latestPayment && $latestPayment['payment_status'] === 'SUCCESS') {
                    $this->processSuccessfulPayment($orderId, $latestPayment);
                    if ($isApi) {
                        $this->apiSuccess($response, 'Payment verified successfully', ['payment_status' => 'success', 'order_id' => $orderId]);
                        return;
                    }
                    $response->redirect('/employer/billing/success');
                } else {
                    if ($isApi) {
                        $this->apiError($response, 'Payment failed', 400);
                        return;
                    }
                    $response->redirect('/employer/billing/failed');
                }
            } else {
                if ($isApi) {
                    $this->apiError($response, 'Payment not completed', 400);
                    return;
                }
                $response->redirect('/employer/billing/failed');
            }
        } catch (\Exception $e) {
            error_log('Cashfree Verification Failed: ' . $e->getMessage());
            if ($isApi) {
                $this->apiError($response, 'Cashfree verification error: ' . $e->getMessage(), 500);
                return;
            }
            $response->redirect('/employer/billing/failed');
        }
    }

    private function configureSslCa(): bool|string
    {
        return SslHelper::configureSslCa();
    }

    /**
     * Handle Webhook
     */
    public function webhook(Request $request, Response $response): void
    {
        $payload = (string)$request->getBody();
        $headers = $request->headers();
        $signature = $headers['x-webhook-signature'] ?? '';
        $timestamp = $headers['x-webhook-timestamp'] ?? '';

        if (!$this->verifyWebhookSignature($signature, $payload, $timestamp)) {
            $response->json(['error' => 'Invalid signature'], 401);
            return;
        }

        $data = json_decode($payload, true);
        $type = $data['type'] ?? '';

        if ($type === 'PAYMENT_SUCCESS_WEBHOOK') {
            $orderId = $data['data']['order']['order_id'] ?? '';
            $paymentData = $data['data']['payment'] ?? null;
            if ($orderId && $paymentData) {
                $this->processSuccessfulPayment($orderId, $paymentData);
            }
        }

        $response->json(['status' => 'ok']);
    }

    private function verifyWebhookSignature(string $signature, string $payload, string $timestamp): bool
    {
        $rawData = $timestamp . $payload;
        $expected = base64_encode(hash_hmac('sha256', $rawData, $this->config['secret_key'], true));
        return hash_equals($expected, $signature);
    }

    private function processSuccessfulPayment(string $gatewayOrderId, array $paymentData): void
    {
        $db = Database::getInstance();
        $paymentRow = $db->fetchOne('SELECT * FROM subscription_payments WHERE gateway_order_id = :goid', ['goid' => $gatewayOrderId]);
        
        if (!$paymentRow || $paymentRow['status'] === 'completed') {
            return;
        }

        $payment = new SubscriptionPayment($paymentRow);
        $db->beginTransaction();

        try {
            // Reusing verification logic similar to SubscriptionController
            $payment->setAttribute('status', 'completed');
            $payment->setAttribute('paid_at', date('Y-m-d H:i:s'));
            $payment->setAttribute('gateway_payment_id', (string)$paymentData['cf_payment_id']);
            $payment->setAttribute('gateway_signature', (string)($paymentData['payment_group'] ?? 'cashfree'));
            
            if (empty($payment->attributes['invoice_number'] ?? '')) {
                $payment->setAttribute('invoice_number', $payment->generateInvoiceNumber());
            }
            if (empty($payment->attributes['invoice_url'] ?? '')) {
                $payment->setAttribute('invoice_url', '/employer/invoices/' . (int)$payment->id);
            }
            $payment->save();

            // Update EmployerPayment (Ledger)
            $db->query(
                'INSERT INTO employer_payments (employer_id, subscription_payment_id, amount, currency, gateway, payment_method, status, txn_id, meta, created_at) 
                 VALUES (:eid, :sid, :amt, :curr, "cashfree", :meth, "success", :txn, :meta, NOW())',
                [
                    'eid' => $payment->attributes['employer_id'],
                    'sid' => $payment->id,
                    'amt' => $payment->attributes['amount'],
                    'curr' => $payment->attributes['currency'],
                    'meth' => (string)($paymentData['payment_method'] ?? 'checkout'),
                    'txn' => (string)$paymentData['cf_payment_id'],
                    'meta' => json_encode(['cashfree' => $paymentData])
                ]
            );

            // Activate subscription
            $subscription = \App\Models\EmployerSubscription::find((int)$payment->attributes['subscription_id']);
            if ($subscription) {
                $cycle = strtolower((string)($subscription->attributes['billing_cycle'] ?? 'monthly'));
                $base = $subscription->attributes['expires_at'] ?? null;
                $baseTs = $base ? max(strtotime((string)$base), time()) : time();
                $startDate = date('Y-m-d H:i:s', $baseTs);
                
                // Calculate expiry using the helper from SubscriptionController or re-implement here
                $days = ['monthly' => 30, 'quarterly' => 90, 'annual' => 365];
                $daysToAdd = $days[$cycle] ?? 30;
                $newExpiry = date('Y-m-d H:i:s', strtotime("+{$daysToAdd} days", strtotime($startDate)));

                $subscription->setAttribute('status', 'active');
                $subscription->setAttribute('expires_at', $newExpiry);
                $subscription->setAttribute('next_billing_date', ($subscription->attributes['auto_renew'] ?? 0) == 1 ? $newExpiry : null);
                
                // CRITICAL: Reset usage counts if this was a new plan purchase/upgrade
                $subscription->setAttribute('contacts_used_this_month', 0);
                $subscription->setAttribute('resume_downloads_used_this_month', 0);
                $subscription->setAttribute('chat_messages_used_this_month', 0);
                $subscription->setAttribute('job_posts_used', 0);
                $subscription->setAttribute('last_usage_reset_at', date('Y-m-d H:i:s'));
                
                $subscription->save();
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            error_log('Cashfree Process Success Failed: ' . $e->getMessage());
        }
    }
}
