-- Fix subscription_plans is_active column
-- This script ensures all plans are marked as active

UPDATE subscription_plans 
SET is_active = 1 
WHERE is_active IS NULL OR is_active = 0;

-- Verify the update
SELECT id, name, slug, is_active, is_featured, sort_order 
FROM subscription_plans 
ORDER BY sort_order ASC;

