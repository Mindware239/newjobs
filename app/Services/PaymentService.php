<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmployerPayment;

class PaymentService
{
    private string $gateway;

    public function __construct()
    {
        $this->gateway = $_ENV['PAYMENT_GATEWAY'] ?? 'razorpay';
    }

    public function createPayment(int $employerId, float $amount, string $currency = 'INR', array $meta = []): array
    {
        $payment = new EmployerPayment();
        $payment->fill([
            'employer_id' => $employerId,
            'amount' => $amount,
            'currency' => $currency,
            'gateway' => $this->gateway,
            'status' => 'pending',
            'meta' => json_encode($meta)
        ]);
        $payment->save();

        // Create payment with gateway
        $gatewayResponse = $this->createGatewayPayment($payment->id, $amount, $currency, $meta);

        if ($gatewayResponse['success']) {
            $payment->txn_id = $gatewayResponse['txn_id'] ?? null;
            $payment->meta = json_encode(array_merge($meta, $gatewayResponse));
            $payment->save();

            return [
                'payment_id' => $payment->id,
                'gateway_order_id' => $gatewayResponse['order_id'] ?? null,
                'amount' => $amount,
                'currency' => $currency,
                'gateway_data' => $gatewayResponse
            ];
        }

        $payment->status = 'failed';
        $payment->save();

        return ['error' => 'Payment creation failed'];
    }

    public function verifyPayment(int $paymentId, array $gatewayData): bool
    {
        $payment = EmployerPayment::find($paymentId);
        if (!$payment) {
            return false;
        }

        $verified = $this->verifyGatewayPayment($payment, $gatewayData);

        if ($verified) {
            $payment->status = 'success';
            $payment->save();

            // Add credits to employer
            $settings = $payment->employer()->settings();
            if ($settings) {
                $credits = (int)($payment->amount / 100); // 1 credit per 100 currency units
                $settings->addCredits($credits);
            }

            return true;
        }

        $payment->status = 'failed';
        $payment->save();
        return false;
    }

    private function createGatewayPayment(int $paymentId, float $amount, string $currency, array $meta): array
    {
        // Gateway integration stub
        if ($this->gateway === 'razorpay') {
            // Razorpay integration
            return [
                'success' => true,
                'order_id' => 'order_' . bin2hex(random_bytes(8)),
                'txn_id' => null
            ];
        }

        return ['success' => false];
    }

    private function verifyGatewayPayment(EmployerPayment $payment, array $gatewayData): bool
    {
        // Gateway verification stub
        return isset($gatewayData['signature']) && isset($gatewayData['payment_id']);
    }
}

