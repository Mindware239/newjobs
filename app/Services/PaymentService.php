<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\DiscountCode;
use App\Models\User;

class PaymentService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
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
