<?php

declare(strict_types=1);

namespace App\Models;

class SubscriptionPayment extends Model
{
    protected string $table = 'subscription_payments';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'subscription_id', 'employer_id', 'amount', 'currency', 'billing_cycle',
        'gateway', 'gateway_payment_id', 'gateway_order_id', 'gateway_signature',
        'status', 'failure_reason', 'invoice_number', 'invoice_url', 'invoice_generated_at',
        'refund_amount', 'refund_reason', 'refunded_at', 'metadata', 'paid_at'
    ];

    public function subscription()
    {
        return EmployerSubscription::find($this->attributes['subscription_id'] ?? 0);
    }

    public function employer()
    {
        return Employer::find($this->attributes['employer_id'] ?? 0);
    }

    public function isCompleted(): bool
    {
        return ($this->attributes['status'] ?? '') === 'completed';
    }

    public function isFailed(): bool
    {
        return ($this->attributes['status'] ?? '') === 'failed';
    }

    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');
        $random = strtoupper(substr(uniqid(), -6));
        return "{$prefix}-{$year}{$month}-{$random}";
    }

    public function markAsCompleted(?string $gatewayPaymentId = null, ?string $gatewayOrderId = null): void
    {
        $this->attributes['status'] = 'completed';
        $this->attributes['paid_at'] = date('Y-m-d H:i:s');
        
        if ($gatewayPaymentId) {
            $this->attributes['gateway_payment_id'] = $gatewayPaymentId;
        }
        if ($gatewayOrderId) {
            $this->attributes['gateway_order_id'] = $gatewayOrderId;
        }
        
        if (empty($this->attributes['invoice_number'])) {
            $this->attributes['invoice_number'] = $this->generateInvoiceNumber();
        }
        
        $this->save();
    }

    public function markAsFailed(string $reason): void
    {
        $this->attributes['status'] = 'failed';
        $this->attributes['failure_reason'] = $reason;
        $this->save();
    }
}

