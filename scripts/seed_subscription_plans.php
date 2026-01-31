<?php
/**
 * Seed Subscription Plans
 * Run this script to ensure subscription plans exist in the database
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = Database::getInstance();

// Check if plans exist
$result = $db->query("SELECT COUNT(*) as count FROM subscription_plans");
$count = $result->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

if ($count > 0) {
    echo "✓ Subscription plans already exist ($count plans)\n";
    // Update is_active to 1 for all plans
    $db->query("UPDATE subscription_plans SET is_active = 1 WHERE is_active IS NULL OR is_active = 0");
    echo "✓ Updated is_active status for all plans\n";
} else {
    echo "No plans found. Inserting default plans...\n";
    
    // Insert default plans
    $sql = "INSERT INTO subscription_plans (name, slug, tier, description, price_monthly, price_quarterly, price_annual, 
      max_job_posts, max_contacts_per_month, max_resume_downloads, max_chat_messages,
      job_post_boost, priority_support, advanced_filters, candidate_mobile_visible, 
      resume_download_enabled, chat_enabled, ai_matching, analytics_dashboard, is_active, is_featured, sort_order) VALUES
    ('Free', 'free', 'free', 'Perfect for startups and small businesses', 0.00, 0.00, 0.00,
      1, 50, 0, 0,
      0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1),
    ('Basic', 'basic', 'basic', 'Essential features for growing businesses', 400.00, 1100.00, 4000.00,
      5, 200, 10, 100,
      0, 0, 1, 0, 1, 1, 0, 0, 1, 0, 2),
    ('Premium', 'premium', 'premium', 'Advanced features for established companies', 850.00, 2300.00, 8500.00,
      -1, -1, -1, -1,
      1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 3),
    ('Enterprise', 'enterprise', 'enterprise', 'Custom solutions for large organizations', 1650.00, 4500.00, 16500.00,
      -1, -1, -1, -1,
      1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 4)";
    
    try {
        $db->query($sql);
        echo "✓ Successfully inserted 4 default plans\n";
    } catch (\Exception $e) {
        echo "✗ Error inserting plans: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Verify plans
$result = $db->query("SELECT id, name, slug, price_monthly, is_active FROM subscription_plans ORDER BY sort_order");
$plans = $result->fetchAll(\PDO::FETCH_ASSOC);

echo "\nCurrent plans in database:\n";
echo str_repeat("-", 60) . "\n";
foreach ($plans as $plan) {
    echo sprintf("ID: %d | %s (%s) | ₹%.2f/month | Active: %s\n", 
        $plan['id'], 
        $plan['name'], 
        $plan['slug'],
        $plan['price_monthly'],
        $plan['is_active'] ? 'Yes' : 'No'
    );
}
echo str_repeat("-", 60) . "\n";
echo "Total: " . count($plans) . " plans\n";

