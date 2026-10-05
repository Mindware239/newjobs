<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\SslHelper;
use App\Models\DiscountCode;
use App\Models\Employer;
use App\Models\SubscriptionPayment;
use App\Models\User;
use GuzzleHttp\Client;
use Razorpay\Api\Api;

class PaymentService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function initiateRazorpay(SubscriptionPayment $payment): ?array
    {
        $config = require __DIR__ . '/../../config/razorpay.php';
        $keyId = (string)($config['key_id'] ?? '');
        $keySecret = (string)($config['key_secret'] ?? '');

        if ($keyId === '' || $keySecret === '') {
            return null;
        }

        SslHelper::configureSslCa();

        try {
            $api = new Api($keyId, $keySecret);
            $amount = (float)($payment->attributes['amount'] ?? 0);
            $order = $api->order->create([
                'receipt' => 'SUB-' . (int)$payment->id,
                'amount' => (int)round($amount * 100),
                'currency' => $payment->attributes['currency'] ?? 'INR',
                'payment_capture' => 1,
                'notes' => [
                    'subscription_payment_id' => (int)$payment->id,
                    'subscription_id' => (int)($payment->attributes['subscription_id'] ?? 0),
                    'employer_id' => (int)($payment->attributes['employer_id'] ?? 0),
                ],
            ]);

            $orderId = (string)($order['id'] ?? '');
            if ($orderId === '') {
                return null;
            }

            $payment->setAttribute('gateway', 'razorpay');
            $payment->setAttribute('gateway_order_id', $orderId);
            $payment->setAttribute('status', 'processing');
            $payment->setAttribute('metadata', json_encode([
                'razorpay_order' => $order,
                'key_id' => $keyId,
            ]));
            $payment->save();

            return [
                'gateway' => 'razorpay',
                'order_id' => $orderId,
                'key' => $keyId,
                'amount' => $amount,
                'currency' => $payment->attributes['currency'] ?? 'INR',
            ];
        } catch (\Throwable $e) {
            error_log('PaymentService initiateRazorpay error: ' . $e->getMessage());
            $payment->markAsFailed($e->getMessage());
            return null;
        }
    }

    public function initiateCashfree(SubscriptionPayment $payment): ?array
    {
        $config = require __DIR__ . '/../../config/cashfree.php';
        if (empty($config['app_id']) || empty($config['secret_key'])) {
            return null;
        }

        $employer = Employer::find((int)($payment->attributes['employer_id'] ?? 0));
        $user = $employer ? User::find((int)($employer->attributes['user_id'] ?? 0)) : null;
        $phone = preg_replace('/\D+/', '', (string)($employer->attributes['phone'] ?? $user->phone ?? '9999999999'));
        if (strlen($phone) > 10) {
            $phone = substr($phone, -10);
        } elseif (strlen($phone) < 10) {
            $phone = str_pad($phone, 10, '0', STR_PAD_LEFT);
        }

        $orderId = 'SUB-' . (int)$payment->id . '-' . time();
        $appUrl = rtrim($_ENV['APP_URL'] ?? ('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')), '/');

        try {
            $client = new Client([
                'base_uri' => $config['base_url'],
                'verify' => SslHelper::configureSslCa(),
                'headers' => [
                    'x-client-id' => $config['app_id'],
                    'x-client-secret' => $config['secret_key'],
                    'x-api-version' => $config['api_version'],
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);

            $res = $client->post('orders', ['json' => [
                'order_id' => $orderId,
                'order_amount' => (float)$payment->attributes['amount'],
                'order_currency' => $payment->attributes['currency'] ?? 'INR',
                'customer_details' => [
                    'customer_id' => 'EMP-' . (int)($payment->attributes['employer_id'] ?? 0),
                    'customer_email' => (string)($user->email ?? ''),
                    'customer_phone' => $phone,
                ],
                'order_meta' => [
                    'return_url' => $appUrl . '/gateway/cashfree/verify?order_id={order_id}',
                    'notify_url' => $appUrl . '/gateway/cashfree/webhook',
                ],
            ]]);

            $body = json_decode((string)$res->getBody(), true);
            $sessionId = (string)($body['payment_session_id'] ?? '');
            if ($sessionId === '') {
                return null;
            }

            $payment->setAttribute('gateway', 'cashfree');
            $payment->setAttribute('gateway_order_id', $orderId);
            $payment->setAttribute('status', 'processing');
            $payment->setAttribute('metadata', json_encode(['payment_session_id' => $sessionId, 'cashfree_order' => $body]));
            $payment->save();

            return [
                'gateway' => 'cashfree',
                'order_id' => $orderId,
                'payment_session_id' => $sessionId,
                'payment_url' => $config['checkout_url'] ?? null,
                'environment' => $config['environment'] ?? 'sandbox',
            ];
        } catch (\Throwable $e) {
            error_log('PaymentService initiateCashfree error: ' . $e->getMessage());
            $payment->markAsFailed($e->getMessage());
            return null;
        }
    }

    public function initiateStripe(SubscriptionPayment $payment): ?array
    {
        return null;
    }

    public function verify(SubscriptionPayment $payment, array $payload): bool
    {
        $gateway = strtolower((string)($payment->attributes['gateway'] ?? 'razorpay'));

        if ($gateway === 'razorpay') {
            $paymentId = (string)($payload['razorpay_payment_id'] ?? $payload['payment_id'] ?? '');
            $orderId = (string)($payload['razorpay_order_id'] ?? $payload['order_id'] ?? $payment->attributes['gateway_order_id'] ?? '');
            $signature = (string)($payload['razorpay_signature'] ?? $payload['signature'] ?? '');

            if ($paymentId === '' || $orderId === '' || $signature === '') {
                return false;
            }

            $config = require __DIR__ . '/../../config/razorpay.php';
            try {
                SslHelper::configureSslCa();
                $api = new Api((string)$config['key_id'], (string)$config['key_secret']);
                $api->utility->verifyPaymentSignature([
                    'razorpay_order_id' => $orderId,
                    'razorpay_payment_id' => $paymentId,
                    'razorpay_signature' => $signature,
                ]);

                $payment->setAttribute('gateway_payment_id', $paymentId);
                $payment->setAttribute('gateway_order_id', $orderId);
                $payment->setAttribute('gateway_signature', $signature);
                return true;
            } catch (\Throwable $e) {
                error_log('PaymentService verify razorpay error: ' . $e->getMessage());
                return false;
            }
        }

        if ($gateway === 'cashfree') {
            $orderId = (string)($payload['order_id'] ?? $payment->attributes['gateway_order_id'] ?? '');
            return $orderId !== '' && hash_equals((string)$payment->attributes['gateway_order_id'], $orderId);
        }

        return false;
    }

    public function verifyRazorpayWebhook(array $payload): bool
    {
        return true;
    }

    public function processRazorpayWebhook(array $payload): void
    {
    }

    public function verifyCashfreeWebhook(array $payload): bool
    {
        return true;
    }

    public function processCashfreeWebhook(array $payload): void
    {
    }

    public function validateDiscount(string $code, int $userId, int $planId = 0, string $billingCycle = 'monthly'): array
    {
        $code = strtoupper(trim($code));
        
        if (empty($code)) {
            return ['valid' => false, 'error' => 'Discount code is required'];
        }

        try {
            $discount = DiscountCode::findByCode($code);
            
            if (!$discount) {
                return ['valid' => false, 'error' => 'Invalid discount code'];
            }
            
            if (!$discount->isValid()) {
                return ['valid' => false, 'error' => 'This discount code is no longer valid'];
            }
            
            // Check if applicable to plan
            if ($planId > 0 && !$discount->isApplicableToPlan($planId, $billingCycle)) {
                return ['valid' => false, 'error' => 'This discount code is not applicable to the selected plan'];
            }
            
            // Check max uses per user if employer
            $maxUsesPerUser = (int)($discount->attributes['max_uses_per_user'] ?? 0);
            if ($maxUsesPerUser > 0) {
                $user = User::find($userId);
                $employer = $user ? $user->employer() : null;
                if ($employer) {
                    $usedByUser = $this->db->fetchOne(
                        "SELECT COUNT(*) as count FROM employer_subscriptions 
                         WHERE employer_id = :employer_id AND discount_code = :code",
                        ['employer_id' => $employer->id, 'code' => $code]
                    );
                    $usedCount = (int)($usedByUser['count'] ?? 0);
                    if ($usedCount >= $maxUsesPerUser) {
                        return ['valid' => false, 'error' => 'You have already used this discount code'];
                    }
                }
            }
            
            $discountType = $discount->attributes['discount_type'] ?? 'percentage';
            $discountValue = (float)($discount->attributes['discount_value'] ?? 0);
            
            return [
                'valid' => true,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'description' => $discount->attributes['description'] ?? ''
            ];
        } catch (\Throwable $e) {
            error_log("PaymentService validateDiscount error: " . $e->getMessage());
            return ['valid' => false, 'error' => 'Error validating discount code'];
        }
    }
}
