<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Employer;
use App\Models\EmployerSubscription;

class SubscriptionGuard
{
    public static function can(Employer $employer, string $action): bool
    {
        $subscription = EmployerSubscription::getCurrentForEmployer($employer->id);

        if (!$subscription || !$subscription->isActive()) {
            return false; // No active subscription
        }

        $plan = $subscription->plan();
        if (!$plan) {
            return false; // No plan associated
        }

        $limit = $plan->getLimit($action);
        if ($limit === null || $limit < 0) {
            return true; // Unlimited
        }

        $usageKeyMap = [
            'max_job_posts' => 'job_posts_used',
            'max_resume_downloads' => 'resume_downloads_used_this_month',
            'max_contacts_per_month' => 'contacts_used_this_month',
        ];

        $usageKey = $usageKeyMap[$action] ?? $action . '_used';
        $used = (int)($subscription->attributes[$usageKey] ?? 0);
        
        return $used < $limit;
    }

    public static function increment(Employer $employer, string $action): void
    {
        $subscription = EmployerSubscription::getCurrentForEmployer($employer->id);
        if ($subscription && $subscription->isActive()) {
            $field = $action . '_used';
            $subscription->increment($field);
        }
    }
}
