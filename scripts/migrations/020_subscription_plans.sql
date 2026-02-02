-- Subscription Plans Table
CREATE TABLE IF NOT EXISTS subscription_plans (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(128) NOT NULL,
  slug VARCHAR(128) NOT NULL UNIQUE,
  tier ENUM('free','basic','premium','enterprise') NOT NULL DEFAULT 'free',
  description TEXT NULL,
  price_monthly DECIMAL(10,2) DEFAULT 0.00,
  price_quarterly DECIMAL(10,2) DEFAULT 0.00,
  price_annual DECIMAL(10,2) DEFAULT 0.00,
  currency VARCHAR(8) DEFAULT 'INR',
  
  -- Feature Limits
  max_job_posts INT DEFAULT 1,
  max_contacts_per_month INT DEFAULT 50,
  max_resume_downloads INT DEFAULT 10,
  max_chat_messages INT DEFAULT 100,
  job_post_boost TINYINT(1) DEFAULT 0,
  priority_support TINYINT(1) DEFAULT 0,
  advanced_filters TINYINT(1) DEFAULT 0,
  candidate_mobile_visible TINYINT(1) DEFAULT 0,
  resume_download_enabled TINYINT(1) DEFAULT 0,
  chat_enabled TINYINT(1) DEFAULT 0,
  ai_matching TINYINT(1) DEFAULT 0,
  analytics_dashboard TINYINT(1) DEFAULT 0,
  custom_branding TINYINT(1) DEFAULT 0,
  api_access TINYINT(1) DEFAULT 0,
  
  -- Trial & Discounts
  trial_days INT DEFAULT 0,
  trial_enabled TINYINT(1) DEFAULT 0,
  discount_percentage DECIMAL(5,2) DEFAULT 0.00,
  discount_valid_until DATETIME NULL,
  
  -- Status
  is_active TINYINT(1) DEFAULT 1,
  is_featured TINYINT(1) DEFAULT 0,
  sort_order INT DEFAULT 0,
  
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX(tier),
  INDEX(is_active),
  INDEX(slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Default Plans
INSERT INTO subscription_plans (name, slug, tier, description, price_monthly, price_quarterly, price_annual, 
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
  1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 4);

