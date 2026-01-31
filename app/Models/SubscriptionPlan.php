<?php

declare(strict_types=1);

namespace App\Models;

class SubscriptionPlan extends Model
{
    protected string $table = 'subscription_plans';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'name', 'slug', 'tier', 'description', 'price_monthly', 'price_quarterly', 'price_annual',
        'currency', 'max_job_posts', 'max_contacts_per_month', 'max_resume_downloads', 'max_chat_messages',
        'job_post_boost', 'priority_support', 'advanced_filters', 'candidate_mobile_visible',
        'resume_download_enabled', 'chat_enabled', 'ai_matching', 'analytics_dashboard',
        'custom_branding', 'api_access', 'trial_days', 'trial_enabled', 'discount_percentage',
        'discount_valid_until', 'is_active', 'is_featured', 'sort_order'
    ];

    public static function findBySlug(string $slug): ?self
    {
        $instance = new self();
        return $instance->where('slug', '=', $slug)->where('is_active', '=', 1)->first();
    }

    public static function getActivePlans(): array
    {
        $instance = new self();
        $db = $instance->getDb();
        
        // Try to get active plans first (is_active = 1)
        $sql = "SELECT * FROM subscription_plans WHERE (is_active = 1 OR is_active IS NULL) ORDER BY sort_order ASC, price_monthly ASC";
        $results = $db->fetchAll($sql);
        
        if (empty($results)) {
            // If still empty, get all plans regardless of is_active
            $sql = "SELECT * FROM subscription_plans ORDER BY sort_order ASC, price_monthly ASC";
            $results = $db->fetchAll($sql);
        }
        
        // Convert to model instances
        return array_map(function($row) {
            return new self($row);
        }, $results);
    }

    public function getPrice(string $cycle = 'monthly'): float
    {
        $priceField = 'price_' . $cycle;
        return (float)($this->attributes[$priceField] ?? 0.00);
    }

    public function hasFeature(string $feature): bool
    {
        return (bool)($this->attributes[$feature] ?? false);
    }

    public function isUnlimited(string $limitField): bool
    {
        $value = $this->attributes[$limitField] ?? 0;
        return $value === -1;
    }

    public function getLimit(string $limitField): int
    {
        $value = $this->attributes[$limitField] ?? 0;
        return $value === -1 ? PHP_INT_MAX : (int)$value;
    }
}

