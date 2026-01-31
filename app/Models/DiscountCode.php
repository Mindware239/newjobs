<?php

declare(strict_types=1);

namespace App\Models;

class DiscountCode extends Model
{
    protected string $table = 'discount_codes';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'code', 'description', 'discount_type', 'discount_value', 'min_amount',
        'max_discount', 'valid_from', 'valid_until', 'max_uses', 'used_count',
        'max_uses_per_user', 'applicable_plans', 'applicable_billing_cycles', 'is_active'
    ];

    public static function findByCode(string $code): ?self
    {
        $instance = new self();
        return $instance->where('code', '=', strtoupper($code))
            ->where('is_active', '=', 1)
            ->first();
    }

    public function isValid(): bool
    {
        if (!$this->attributes['is_active']) {
            return false;
        }
        
        $now = time();
        $validFrom = strtotime($this->attributes['valid_from'] ?? '1970-01-01');
        $validUntil = strtotime($this->attributes['valid_until'] ?? '1970-01-01');
        
        if ($now < $validFrom || $now > $validUntil) {
            return false;
        }
        
        $maxUses = $this->attributes['max_uses'] ?? null;
        $usedCount = (int)($this->attributes['used_count'] ?? 0);
        
        if ($maxUses !== null && $usedCount >= $maxUses) {
            return false;
        }
        
        return true;
    }

    public function isApplicableToPlan(int $planId, string $billingCycle): bool
    {
        $applicablePlans = json_decode($this->attributes['applicable_plans'] ?? '[]', true);
        if ($applicablePlans === null) {
            $applicablePlans = [];
        }
        
        if (!empty($applicablePlans) && !in_array($planId, $applicablePlans) && !in_array('all', $applicablePlans)) {
            return false;
        }
        
        $applicableCycles = json_decode($this->attributes['applicable_billing_cycles'] ?? '[]', true);
        if ($applicableCycles === null) {
            $applicableCycles = [];
        }
        
        if (!empty($applicableCycles) && !in_array($billingCycle, $applicableCycles)) {
            return false;
        }
        
        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if (!$this->isValid()) {
            return 0.00;
        }
        
        $minAmount = (float)($this->attributes['min_amount'] ?? 0.00);
        if ($amount < $minAmount) {
            return 0.00;
        }
        
        $discountType = $this->attributes['discount_type'] ?? 'percentage';
        $discountValue = (float)($this->attributes['discount_value'] ?? 0.00);
        
        if ($discountType === 'percentage') {
            $discount = ($amount * $discountValue) / 100;
            $maxDiscount = $this->attributes['max_discount'] ?? null;
            if ($maxDiscount !== null && $discount > (float)$maxDiscount) {
                $discount = (float)$maxDiscount;
            }
            return round($discount, 2);
        } else {
            return min($discountValue, $amount);
        }
    }

    public function incrementUsage(): void
    {
        $usedCount = (int)($this->attributes['used_count'] ?? 0);
        $this->attributes['used_count'] = $usedCount + 1;
        $this->save();
    }
}

