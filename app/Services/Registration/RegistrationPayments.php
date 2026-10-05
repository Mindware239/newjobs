<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Helpers\SslHelper;
use App\Models\PortalRegistration;
use Razorpay\Api\Api;

/**
 * Razorpay handling for the ₹155 registration fee.
 */
class RegistrationPayments
{
    public static function keyId(): string
    {
        return (string)(self::config()['key_id'] ?? '');
    }

    public static function createOrder(array $reg): ?string
    {
        $api = self::api();
        if (!$api) {
            return null;
        }

        try {
            $order = $api->order->create([
                'receipt' => (string)$reg['reg_no'],
                'amount' => (int)round((float)$reg['total_amount'] * 100),
                'currency' => ($reg['currency'] ?? 'INR') === 'USD' ? 'USD' : 'INR',
                'payment_capture' => 1,
                'notes' => [
                    'purpose' => 'jobsence_registration_fee',
                    'registration_type' => (string)$reg['type'],
                    'reg_no' => (string)$reg['reg_no'],
                ],
            ]);
            $orderId = (string)($order['id'] ?? '');
            if ($orderId !== '') {
                PortalRegistration::setOrderId((int)$reg['id'], $orderId);
                return $orderId;
            }
        } catch (\Throwable $e) {
            error_log('RegistrationPayments createOrder error: ' . $e->getMessage());
        }

        return null;
    }

    public static function verifySignature(string $orderId, string $paymentId, string $signature): bool
    {
        $api = self::api();
        if (!$api || $orderId === '' || $paymentId === '' || $signature === '') {
            return false;
        }

        try {
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ]);
            return true;
        } catch (\Throwable $e) {
            error_log('RegistrationPayments verify error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Marks the registration paid if Razorpay has a captured payment for its order
     * (covers a payment that succeeded but whose browser closed before /verify was posted).
     */
    public static function reconcile(array $reg): bool
    {
        $orderId = (string)($reg['razorpay_order_id'] ?? '');
        $api = $orderId !== '' ? self::api() : null;
        if (!$api) {
            return false;
        }

        try {
            $payments = $api->order->fetch($orderId)->payments();
            foreach (($payments['items'] ?? []) as $payment) {
                if (($payment['status'] ?? '') === 'captured') {
                    self::completePayment($reg, (string)$payment['id']);
                    return true;
                }
            }
        } catch (\Throwable $e) {
            error_log('RegistrationPayments reconcile error: ' . $e->getMessage());
        }

        return false;
    }

    /** Marks paid and sends the confirmation emails exactly once. */
    public static function completePayment(array $reg, string $paymentId, string $signature = ''): void
    {
        if (!PortalRegistration::markPaid((int)$reg['id'], $paymentId, $signature)) {
            return;
        }
        $fresh = PortalRegistration::find((int)$reg['id']) ?? $reg;
        RegistrationMailer::sendThankYou($fresh);
        RegistrationMailer::notifyOwner($fresh);
    }

    private static function api(): ?Api
    {
        $config = self::config();
        $keyId = (string)($config['key_id'] ?? '');
        $keySecret = (string)($config['key_secret'] ?? '');
        if ($keyId === '' || $keySecret === '') {
            return null;
        }
        SslHelper::configureSslCa();
        return new Api($keyId, $keySecret);
    }

    private static function config(): array
    {
        static $config = null;
        return $config ??= require __DIR__ . '/../../../config/razorpay.php';
    }
}
