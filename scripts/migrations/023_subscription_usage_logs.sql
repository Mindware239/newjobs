-- Subscription Usage Logs Table
CREATE TABLE IF NOT EXISTS subscription_usage_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  subscription_id BIGINT UNSIGNED NOT NULL,
  employer_id BIGINT UNSIGNED NOT NULL,
  
  -- Usage Type
  action_type ENUM('contact_view','resume_download','chat_message','job_post','filter_used','mobile_view') NOT NULL,
  
  -- Related Entities
  candidate_id BIGINT UNSIGNED NULL,
  job_id BIGINT UNSIGNED NULL,
  application_id BIGINT UNSIGNED NULL,
  
  -- Metadata
  metadata JSON NULL,
  
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (subscription_id) REFERENCES employer_subscriptions(id) ON DELETE CASCADE,
  FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE,
  INDEX(subscription_id),
  INDEX(employer_id),
  INDEX(action_type),
  INDEX(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

