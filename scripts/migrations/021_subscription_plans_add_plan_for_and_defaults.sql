-- Add audience and defaults to subscription_plans
ALTER TABLE subscription_plans
  ADD COLUMN IF NOT EXISTS plan_for VARCHAR(20) NOT NULL DEFAULT 'employer' AFTER slug,
  ADD COLUMN IF NOT EXISTS default_billing_cycle ENUM('monthly','quarterly','annual') NOT NULL DEFAULT 'monthly' AFTER price_annual,
  ADD COLUMN IF NOT EXISTS sort_order INT NOT NULL DEFAULT 0 AFTER is_featured;

-- Ensure indices for sorting and filtering
ALTER TABLE subscription_plans
  ADD INDEX IF NOT EXISTS idx_subscription_plans_plan_for (plan_for),
  ADD INDEX IF NOT EXISTS idx_subscription_plans_sort_order (sort_order);
