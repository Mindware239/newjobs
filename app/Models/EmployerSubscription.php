<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\NotificationService;

class EmployerSubscription extends Model
{
    protected string $table = 'employer_subscriptions';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'employer_id', 'plan_id', 'status', 'billing_cycle', 'started_at', 'expires_at',
        'trial_ends_at', 'grace_period_ends_at', 'auto_renew', 'next_billing_date',
        'contacts_used_this_month', 'resume_downloads_used_this_month', 'chat_messages_used_this_month',
        'job_posts_used', 'last_usage_reset_at', 'referral_code', 'discount_code',
        'discount_percentage', 'cancelled_at', 'cancellation_reason'
    ];

    public function employer()
    {
        return Employer::find($this->attributes['employer_id'] ?? 0);
    }

    public function plan(): ?SubscriptionPlan
    {
        return SubscriptionPlan::find($this->attributes['plan_id'] ?? 0);
    }

    public function payments()
    {
        return SubscriptionPayment::where('subscription_id', '=', $this->attributes['id'] ?? 0)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function isActive(): bool
    {
        $status = $this->attributes['status'] ?? 'expired';
        return in_array($status, ['active', 'trial']);
    }

    public function isExpired(): bool
    {
        if ($this->isActive()) {
            return false;
        }
        
        $expiresAt = $this->attributes['expires_at'] ?? null;
        if (!$expiresAt) {
            return true;
        }
        
        return strtotime($expiresAt) < time();
    }

    public function isInGracePeriod(): bool
    {
        $gracePeriodEnds = $this->attributes['grace_period_ends_at'] ?? null;
        if (!$gracePeriodEnds) {
            return false;
        }
        
        return strtotime($gracePeriodEnds) >= time();
    }

    public function canAccessFeature(string $feature): bool
    {
        if (!$this->isActive() && !$this->isInGracePeriod()) {
            return false;
        }
        
        $plan = $this->plan();
        if (!$plan) {
            return false;
        }
        
        return $plan->hasFeature($feature);
    }

    public function canUseFeature(string $limitField): bool
    {
        if (!$this->isActive() && !$this->isInGracePeriod()) {
            return false;
        }
        
        $plan = $this->plan();
        if (!$plan) {
            return false;
        }
        
        if ($plan->isUnlimited($limitField)) {
            return true;
        }
        
        $usedField = $this->getUsedFieldForLimit($limitField);
        $used = (int)($this->attributes[$usedField] ?? 0);
        $limit = $plan->getLimit($limitField);
        
        return $used < $limit;
    }

    private function getUsedFieldForLimit(string $limitField): string
    {
        $mapping = [
            'max_contacts_per_month' => 'contacts_used_this_month',
            'max_resume_downloads' => 'resume_downloads_used_this_month',
            'max_chat_messages' => 'chat_messages_used_this_month',
            'max_job_posts' => 'job_posts_used'
        ];
        
        return $mapping[$limitField] ?? '';
    }

    public function incrementUsage(string $limitField, int $amount = 1): void
    {
        $usedField = $this->getUsedFieldForLimit($limitField);
        if ($usedField) {
            $current = (int)($this->attributes[$usedField] ?? 0);
            $newUsage = $current + $amount;
            $this->attributes[$usedField] = $newUsage;
            $this->save();

            // Check for notifications
            $this->checkUsageNotifications($limitField, $newUsage);
        }
    }

    private function checkUsageNotifications(string $limitField, int $usage): void
    {
        $plan = $this->plan();
        if (!$plan || $plan->isUnlimited($limitField)) {
            return;
        }

        $limit = $plan->getLimit($limitField);
        if ($limit <= 0) return;

        $employer = $this->employer();
        if (!$employer || !$employer->attributes['user_id']) return;

        // 100% Limit Reached
        if ($usage == $limit) {
            NotificationService::send(
                (int)$employer->attributes['user_id'],
                'subscription_limit_reached',
                'Limit Reached: ' . $this->getReadableLimitName($limitField),
                "You have reached your limit for {$this->getReadableLimitName($limitField)}. Upgrade your plan to continue.",
                ['limit_field' => $limitField, 'usage' => $usage, 'limit' => $limit],
                '/employer/subscription'
            );
        }
        // 80% Threshold Warning
        elseif ($usage == (int)ceil($limit * 0.8)) {
             NotificationService::send(
                (int)$employer->attributes['user_id'],
                'subscription_limit_warning',
                'Approaching Limit: ' . $this->getReadableLimitName($limitField),
                "You have used 80% of your {$this->getReadableLimitName($limitField)} limit.",
                ['limit_field' => $limitField, 'usage' => $usage, 'limit' => $limit],
                '/employer/subscription'
            );
        }
    }

    private function getReadableLimitName(string $limitField): string
    {
        $mapping = [
            'max_contacts_per_month' => 'Contact Views',
            'max_resume_downloads' => 'Resume Downloads',
            'max_chat_messages' => 'Chat Messages',
            'max_job_posts' => 'Job Posts'
        ];
        return $mapping[$limitField] ?? 'Usage Limit';
    }

    public function resetMonthlyUsage(): void
    {
        $this->attributes['contacts_used_this_month'] = 0;
        $this->attributes['resume_downloads_used_this_month'] = 0;
        $this->attributes['chat_messages_used_this_month'] = 0;
        $this->attributes['last_usage_reset_at'] = date('Y-m-d H:i:s');
        $this->save();
    }

    public static function getActiveForEmployer(int $employerId): ?self
    {
        $instance = new self();
        return $instance->where('employer_id', '=', $employerId)
            ->whereIn('status', ['active', 'trial'])
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    public static function getCurrentForEmployer(int $employerId): ?self
    {
        $instance = new self();
        $subscription = $instance->where('employer_id', '=', $employerId)
            ->orderBy('created_at', 'DESC')
            ->first();
        if ($subscription) {
            $status = (string)($subscription->attributes['status'] ?? 'expired');
            $graceEnds = $subscription->attributes['grace_period_ends_at'] ?? null;
            $active = in_array($status, ['active', 'trial'], true);
            $inGrace = $graceEnds ? (strtotime((string)$graceEnds) >= time()) : false;
            if ($active || $inGrace) {
                return $subscription;
            }
        }
        return null;
    }
}

