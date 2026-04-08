<?php

declare(strict_types=1);

namespace App\Controllers\Gateway;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\SubscriptionPayment;
use App\Helpers\PaymentLogger;

class RazorpayWebhookController
{
    public function handle(Request $request, Response $response): void
    {
        $payload = file_get_contents('php://input');
        $headers = $request->headers();
        $signature = $headers['x-razorpay-signature'] ?? '';

        PaymentLogger::logWebhook('razorpay', 'Webhook received', ['signature' => $signature]);

        $config = require __DIR__ . '/../../../config/razorpay.php';
        $secret = (string)($config['webhook_secret'] ?? '');

        if (!$secret || !$signature) {
            PaymentLogger::logError('razorpay', 'Webhook missing secret or signature');
            $response->json(['error' => 'unauthorized'], 401);
            return;
        }

        $calc = hash_hmac('sha256', $payload, $secret);
        if (!hash_equals($calc, $signature)) {
            PaymentLogger::logError('razorpay', 'Signature verification failed');
            $response->json(['error' => 'signature_verification_failed'], 401);
            return;
        }

        $data = json_decode($payload, true);
        if (!$data) {
            PaymentLogger::logError('razorpay', 'Invalid JSON payload');
            $response->json(['error' => 'invalid_payload'], 400);
            return;
        }

        $event = $data['event'] ?? '';
        PaymentLogger::logWebhook('razorpay', "Event: $event", ['event' => $event]);

        // Event allowlist: Prevent noise events
        if (!in_array($event, ['payment.captured', 'payment.failed', 'refund.processed'])) {
            $response->json(['message' => 'ignored']);
            return;
        }

        $entity = $data['payload']['payment']['entity'] ?? [];
        $gatewayPaymentId = $entity['id'] ?? null;
        $orderId = $entity['order_id'] ?? null;
        $notes = $entity['notes'] ?? [];
        $paymentId = isset($notes['subscription_payment_id']) ? (int)$notes['subscription_payment_id'] : 0;
        $employerIdNote = isset($notes['employer_id']) ? (int)$notes['employer_id'] : null;

        // Store webhook log in DB
        try {
            $eventId = $data['id'] ?? (string)($gatewayPaymentId ?: ($data['payload']['refund']['entity']['id'] ?? ($orderId ?? md5($payload))));
            $db = Database::getInstance();
            $db->query(
                'INSERT INTO webhooks (gateway, event_type, event_id, signature, payload, processed, received_at, employer_id) 
                 VALUES (:gateway, :event_type, :event_id, :signature, :payload, 0, NOW(), :employer_id)
                 ON DUPLICATE KEY UPDATE received_at = NOW()',
                [
                    'gateway' => 'razorpay',
                    'event_type' => (string)$event,
                    'event_id' => $eventId,
                    'signature' => $signature,
                    'payload' => $payload,
                    'employer_id' => $employerIdNote
                ]
            );
        } catch (\Throwable $t) {
            PaymentLogger::logError('razorpay', 'Failed to store webhook in DB', ['error' => $t->getMessage()]);
        }

        if (!$paymentId) {
            PaymentLogger::logWebhook('razorpay', 'Payment ID missing in notes, ignoring');
            $response->json(['message' => 'ignored']);
            return;
        }

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $subPay = $db->fetchOne('SELECT * FROM subscription_payments WHERE id = :id FOR UPDATE', ['id' => $paymentId]);
            
            if (!$subPay) {
                $db->rollback();
                PaymentLogger::logError('razorpay', 'Subscription payment not found', ['id' => $paymentId]);
                $response->json(['error' => 'payment_not_found'], 404);
                return;
            }
            
            $empPay = $db->fetchOne('SELECT * FROM employer_payments WHERE subscription_payment_id = :sid AND employer_id = :eid FOR UPDATE', [
                'sid' => $paymentId,
                'eid' => $subPay['employer_id']
            ]);

            // Idempotency: if already processed
            if (($subPay['status'] ?? '') === 'completed') {
                if ($empPay && ($empPay['status'] ?? '') === 'success') {
                    $db->commit();
                    PaymentLogger::logPayment('razorpay', 'Payment already processed', ['payment_id' => $paymentId]);
                    $response->json(['ok' => true, 'message' => 'already_processed']);
                    return;
                }
            }

            if ($event === 'payment.captured') {
                $paidAt = date('Y-m-d H:i:s');
                $invoiceNumber = $subPay['invoice_number'] ?? 'INV-' . date('Ymd') . '-' . $paymentId;
                $invoiceUrl = $subPay['invoice_url'] ?? '/employer/invoices/' . (int)$paymentId;
                
                PaymentLogger::logPayment('razorpay', 'Processing successful payment', ['payment_id' => $paymentId, 'gateway_id' => $gatewayPaymentId]);

                // Update Subscription Payment
                $db->query('UPDATE subscription_payments SET status = "completed", gateway_payment_id = :pid, gateway_order_id = :oid, paid_at = :paid, invoice_number = :inv, invoice_url = :url WHERE id = :id', [
                    'pid' => $gatewayPaymentId,
                    'oid' => $orderId,
                    'paid' => $paidAt,
                    'inv' => $invoiceNumber,
                    'url' => $invoiceUrl,
                    'id' => $paymentId
                ]);

                if ($empPay) {
                     $meta = json_decode($empPay['meta'] ?? '{}', true);
                     $meta['razorpay'] = ['payment_id' => $gatewayPaymentId, 'order_id' => $orderId];
                     $db->query('UPDATE employer_payments SET status = "success", txn_id = :txn, meta = :meta, gateway = "razorpay" WHERE id = :id', [
                         'txn' => $gatewayPaymentId,
                         'meta' => json_encode($meta),
                         'id' => $empPay['id']
                     ]);
                } else {
                    $db->query('INSERT INTO employer_payments (employer_id, subscription_payment_id, amount, currency, gateway, payment_method, status, txn_id, meta, created_at) VALUES (:eid, :sid, :amt, :curr, "razorpay", "webhook", "success", :txn, :meta, NOW())', [
                        'eid' => $subPay['employer_id'],
                        'sid' => $paymentId,
                        'amt' => $subPay['amount'],
                        'curr' => $subPay['currency'],
                        'txn' => $gatewayPaymentId,
                        'meta' => json_encode(['subscription_payment_id' => $paymentId, 'razorpay' => ['payment_id' => $gatewayPaymentId, 'order_id' => $orderId]])
                    ]);
                }

                // Activate Subscription
                $subscriptionId = (int)($subPay['subscription_id'] ?? 0);
                if ($subscriptionId > 0) {
                    $subscription = $db->fetchOne('SELECT * FROM employer_subscriptions WHERE id = :id', ['id' => $subscriptionId]);
                    if ($subscription) {
                        $cycle = strtolower((string)($subscription['billing_cycle'] ?? 'monthly'));
                        $baseTs = isset($subscription['expires_at']) && $subscription['expires_at'] ? max(strtotime((string)$subscription['expires_at']), time()) : time();
                        $expires = match ($cycle) {
                            'quarterly' => date('Y-m-d H:i:s', strtotime('+3 months', $baseTs)),
                            'annual' => date('Y-m-d H:i:s', strtotime('+1 year', $baseTs)),
                            default => date('Y-m-d H:i:s', strtotime('+1 month', $baseTs)),
                        };
                        $db->query('UPDATE employer_subscriptions SET status = "active", expires_at = :exp, next_billing_date = :next WHERE id = :id', [
                            'id' => $subscriptionId,
                            'exp' => $expires,
                            'next' => $expires
                        ]);
                        PaymentLogger::logPayment('razorpay', 'Subscription activated', ['subscription_id' => $subscriptionId, 'expires_at' => $expires]);
                    }
                }
            } elseif ($event === 'payment.failed') {
                PaymentLogger::logPayment('razorpay', 'Payment failed event received', ['payment_id' => $paymentId]);
                $db->query('UPDATE subscription_payments SET status = "failed", failure_reason = "gateway_failed" WHERE id = :id', ['id' => $paymentId]);
                if ($empPay) {
                    $db->query('UPDATE employer_payments SET status = "failed" WHERE id = :id', ['id' => $empPay['id']]);
                }
            }
            
            // Mark webhook as processed
            $db->query('UPDATE webhooks SET processed = 1, processed_at = NOW() WHERE event_id = :eid AND gateway = "razorpay"', ['eid' => $eventId]);
            
            $db->commit();
            PaymentLogger::logPayment('razorpay', 'Transaction committed successfully');
        } catch (\Throwable $e) {
            $db->rollback();
            PaymentLogger::logError('razorpay', 'Internal error processing webhook', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $response->json(['error' => 'internal_error'], 500);
            return;
        }

        $response->json(['ok' => true]);
    }
}
