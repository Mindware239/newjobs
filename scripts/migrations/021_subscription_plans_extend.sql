-- Extend subscription_plans with missing columns for admin plans UI
ALTER TABLE subscription_plans
  ADD COLUMN IF NOT EXISTS plan_for ENUM('employer','candidate') NOT NULL DEFAULT 'employer' AFTER tier,
  ADD COLUMN IF NOT EXISTS default_billing_cycle ENUM('monthly','quarterly','annual') NOT NULL DEFAULT 'monthly' AFTER price_annual,
  ADD COLUMN IF NOT EXISTS features TEXT NULL AFTER sort_order;

CREATE INDEX IF NOT EXISTS idx_subscription_plans_plan_for ON subscription_plans (plan_for);
CREATE INDEX IF NOT EXISTS idx_subscription_plans_default_cycle ON subscription_plans (default_billing_cycle);
