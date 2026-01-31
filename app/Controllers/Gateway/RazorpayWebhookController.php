<?php

declare(strict_types=1);

namespace App\Controllers\Gateway;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\SubscriptionPayment;

class RazorpayWebhookController
{
    public function handle(Request $request, Response $response): void
    {
        $payload = file_get_contents('php://input');
        $config = require __DIR__ . '/../../../config/razorpay.php';
        $secret = (string)($config['webhook_secret'] ?? '');
        $signature = (string)($request->header('X-Razorpay-Signature', ''));
        if (!$secret || !$signature) {
            $response->json(['error' => 'unauthorized'], 401);
            return;
        }
        $calc = hash_hmac('sha256', $payload, $secret);
        if (!hash_equals($calc, $signature)) {
            $response->json(['error' => 'signature_verification_failed'], 401);
            return;
        }
        $data = json_decode($payload, true) ?: [];

        $event = $data['event'] ?? '';

        // Event allowlist: Prevent noise events
        if (!in_array($event, ['payment.captured', 'payment.failed', 'refund.processed'])) {
            $response->json(['message' => 'ignored']);
            return;
        }

        $entity = $data['payload']['payment']['entity'] ?? [];
        $gatewayPaymentId = $entity['id'] ?? null;
        $orderId = $entity['order_id'] ?? null;
        $notes = $entity['notes'] ?? [];
        $currency = (string)($entity['currency'] ?? '');
        $amountPaise = (int)($entity['amount'] ?? 0);
        $paymentId = isset($notes['subscription_payment_id']) ? (int)$notes['subscription_payment_id'] : 0;
        $employerIdNote = isset($notes['employer_id']) ? (int)$notes['employer_id'] : null;

        // Optional: log inbound webhook event if table supports it
        try {
            $db = Database::getInstance();
            $db->query(
                'INSERT INTO webhooks (gateway, event_type, payload, processed, received_at, employer_id) 
                 VALUES (:gateway, :event_type, :payload, 0, NOW(), :employer_id)',
                [
                    'gateway' => 'razorpay',
                    'event_type' => (string)$event,
                    'payload' => json_encode($data),
                    'employer_id' => $employerIdNote
                ]
            );
        } catch (\Throwable $t) {
            // silently ignore if table/columns differ
        }

        if (!$paymentId) {
            $response->json(['message' => 'ignored']);
            return;
        }

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            // Lock the subscription payment record
            $subPay = $db->fetchOne('SELECT * FROM subscription_payments WHERE id = :id FOR UPDATE', ['id' => $paymentId]);
            
            if (!$subPay) {
                $db->rollback();
                $response->json(['error' => 'payment_not_found'], 404);
                return;
            }
            
            // Find linked employer_payment
            $empPay = $db->fetchOne('SELECT * FROM employer_payments WHERE meta LIKE :like AND employer_id = :eid FOR UPDATE', [
                'like' => '%"subscription_payment_id":' . $paymentId . '%',
                'eid' => $subPay['employer_id']
            ]);

            // Idempotency: if already processed
            if (($subPay['status'] ?? '') === 'completed' || ($subPay['gateway_payment_id'] ?? '') === $gatewayPaymentId) {
                // Primary Truth: Only return if EmployerPayment (Ledger) is ALSO consistent.
                // If EmployerPayment is missing or not success, we continue to fix it.
                if ($empPay && ($empPay['status'] ?? '') === 'success') {
                    $db->commit();
                    $response->json(['ok' => true, 'message' => 'already_processed']);
                    return;
                }
            }

            // Tamper protection
            $expectedPaise = (int)round(((float)($subPay['amount'] ?? 0)) * 100);
            $expectedCurrency = (string)($subPay['currency'] ?? 'INR');
            $employerNoteId = isset($notes['employer_id']) ? (int)$notes['employer_id'] : 0;
            $expectedEmployerId = (int)($subPay['employer_id'] ?? 0);

            if ($amountPaise !== $expectedPaise || $currency !== $expectedCurrency || ($expectedEmployerId > 0 && $employerNoteId !== $expectedEmployerId)) {
                $db->query('UPDATE subscription_payments SET status = "failed", failure_reason = "validation_mismatch" WHERE id = :id', ['id' => $paymentId]);
                if ($empPay) {
                    $db->query('UPDATE employer_payments SET status = "failed", error_message = "Validation Mismatch" WHERE id = :id', ['id' => $empPay['id']]);
                }
                $db->commit();
                $response->json(['ok' => true]);
                return;
            }

            if ($event === 'payment.captured' || $event === 'payment.success') {
                $paidAt = date('Y-m-d H:i:s');
                $invoiceNumber = $subPay['invoice_number'] ?? null;
                $invoiceUrl = $subPay['invoice_url'] ?? null;

                if (empty($invoiceNumber)) {
                    // Generate Invoice if missing (simple generation logic or call service)
                    // For now, let's assume service call or simple generation
                    $invoiceNumber = 'INV-' . date('Ymd') . '-' . $paymentId;
                }
                if (empty($invoiceUrl)) {
                    $invoiceUrl = '/employer/invoices/' . (int)$paymentId;
                }
                
                // Update Subscription Payment
                $db->query('UPDATE subscription_payments SET status = "completed", gateway_payment_id = :pid, gateway_order_id = :oid, paid_at = :paid, invoice_number = :inv, invoice_url = :url WHERE id = :id', [
                    'pid' => $gatewayPaymentId,
                    'oid' => $orderId,
                    'paid' => $paidAt,
                    'inv' => $invoiceNumber,
                    'url' => $invoiceUrl,
                    'id' => $paymentId
                ]);

                // Update Employer Payment
                if ($empPay) {
                     $meta = json_decode($empPay['meta'] ?? '{}', true);
                     $meta['razorpay'] = ['payment_id' => $gatewayPaymentId, 'order_id' => $orderId];
                     $db->query('UPDATE employer_payments SET status = "success", txn_id = :txn, meta = :meta, gateway = "razorpay" WHERE id = :id', [
                         'txn' => $gatewayPaymentId,
                         'meta' => json_encode($meta),
                         'id' => $empPay['id']
                     ]);
                } else {
                    // Create employer payment record if missing (should exist from createOrder)
                    $db->query('INSERT INTO employer_payments (employer_id, amount, currency, gateway, payment_method, status, txn_id, created_at) VALUES (:eid, :amt, :curr, "razorpay", "webhook", "success", :txn, NOW())', [
                        'eid' => $expectedEmployerId,
                        'amt' => $subPay['amount'],
                        'curr' => $subPay['currency'],
                        'txn' => $gatewayPaymentId
                    ]);
                }

                // Activate Subscription
                $subscriptionId = (int)($subPay['subscription_id'] ?? 0);
                if ($subscriptionId > 0) {
                    $subscription = $db->fetchOne('SELECT * FROM employer_subscriptions WHERE id = :id', ['id' => $subscriptionId]);
                    if ($subscription) {
                        $cycle = strtolower((string)($subscription['billing_cycle'] ?? 'monthly'));
                        $expires = match ($cycle) {
                            'quarterly' => date('Y-m-d H:i:s', strtotime('+3 months')),
                            'annual' => date('Y-m-d H:i:s', strtotime('+1 year')),
                            default => date('Y-m-d H:i:s', strtotime('+1 month')),
                        };
                        $db->query('UPDATE employer_subscriptions SET status = "active", expires_at = :exp, next_billing_date = :exp WHERE id = :id', [
                            'exp' => $expires,
                            'id' => $subscriptionId
                        ]);
                    }
                }
            } elseif ($event === 'payment.failed') {
                $db->query('UPDATE subscription_payments SET status = "failed", failure_reason = "gateway_failed" WHERE id = :id', ['id' => $paymentId]);
                if ($empPay) {
                    $db->query('UPDATE employer_payments SET status = "failed" WHERE id = :id', ['id' => $empPay['id']]);
                }
            } elseif ($event === 'refund.processed') {
                $refundEntity = $data['payload']['refund']['entity'] ?? [];
                $refundAmount = $refundEntity['amount'] ?? 0;
                $refundId = $refundEntity['id'] ?? '';
                
                $db->query('UPDATE subscription_payments SET status = "refunded", refunded_at = NOW(), refund_amount = :amt WHERE id = :id', [
                    'amt' => $refundAmount / 100, // Convert back to main unit
                    'id' => $paymentId
                ]);

                // Check if this refund is already recorded (e.g. initiated by Admin)
                $existingRefund = $db->fetchOne('SELECT id FROM employer_payments WHERE txn_id = :tid', ['tid' => $refundId]);
                
                if (!$existingRefund && $empPay) {
                    // Insert new refund record
                    $db->query(
                        "INSERT INTO employer_payments (employer_id, amount, currency, status, payment_method, txn_id, description, created_at)
                         VALUES (:employer_id, :amount, :currency, 'refunded', 'refund', :txn_id, :description, NOW())",
                        [
                            'employer_id' => $empPay['employer_id'],
                            'amount' => -($refundAmount / 100),
                            'currency' => $refundEntity['currency'] ?? 'INR',
                            'txn_id' => $refundId,
                            'description' => 'Refund processed (Webhook)'
                        ]
                    );
                    
                    // Mark original payment as refunded if full refund
                    $paymentAmountPaise = (int)($entity['amount'] ?? 0); // Original payment amount
                    if ($paymentAmountPaise > 0 && $refundAmount >= $paymentAmountPaise) {
                        $db->query("UPDATE employer_payments SET status = 'refunded' WHERE id = :id", ['id' => $empPay['id']]);
                    }
                }
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollback();
            error_log("Webhook Error: " . $e->getMessage());
            $response->json(['error' => 'internal_error'], 500);
            return;
        }

        $response->json(['ok' => true]);
    }
}
