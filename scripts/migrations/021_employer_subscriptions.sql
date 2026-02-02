-- Employer Subscriptions Table
CREATE TABLE IF NOT EXISTS employer_subscriptions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employer_id BIGINT UNSIGNED NOT NULL,
  plan_id BIGINT UNSIGNED NOT NULL,
  
  -- Subscription Details
  status ENUM('active','expired','cancelled','trial','suspended') DEFAULT 'trial',
  billing_cycle ENUM('monthly','quarterly','annual') DEFAULT 'monthly',
  started_at DATETIME NOT NULL,
  expires_at DATETIME NOT NULL,
  trial_ends_at DATETIME NULL,
  grace_period_ends_at DATETIME NULL,
  
  -- Auto-renewal
  auto_renew TINYINT(1) DEFAULT 0,
  next_billing_date DATETIME NULL,
  
  -- Usage Tracking (reset monthly)
  contacts_used_this_month INT DEFAULT 0,
  resume_downloads_used_this_month INT DEFAULT 0,
  chat_messages_used_this_month INT DEFAULT 0,
  job_posts_used INT DEFAULT 0,
  last_usage_reset_at DATETIME NULL,
  
  -- Referral & Discounts
  referral_code VARCHAR(32) NULL,
  discount_code VARCHAR(32) NULL,
  discount_percentage DECIMAL(5,2) DEFAULT 0.00,
  
  -- Cancellation
  cancelled_at DATETIME NULL,
  cancellation_reason TEXT NULL,
  
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE,
  FOREIGN KEY (plan_id) REFERENCES subscription_plans(id) ON DELETE RESTRICT,
  INDEX(employer_id),
  INDEX(plan_id),
  INDEX(status),
  INDEX(expires_at),
  INDEX(next_billing_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

