-- Subscription Notifications Table
CREATE TABLE IF NOT EXISTS subscription_notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  subscription_id BIGINT UNSIGNED NOT NULL,
  employer_id BIGINT UNSIGNED NOT NULL,
  
  -- Notification Details
  type ENUM('expiry_reminder','payment_due','payment_failed','trial_ending','discount_offer','feature_locked','renewal_success') NOT NULL,
  channel ENUM('email','sms','in_app','push') DEFAULT 'email',
  
  -- Content
  subject VARCHAR(255) NULL,
  message TEXT NOT NULL,
  
  -- Status
  status ENUM('pending','sent','failed','read') DEFAULT 'pending',
  sent_at DATETIME NULL,
  read_at DATETIME NULL,
  
  -- Metadata
  metadata JSON NULL,
  
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (subscription_id) REFERENCES employer_subscriptions(id) ON DELETE CASCADE,
  FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE,
  INDEX(subscription_id),
  INDEX(employer_id),
  INDEX(type),
  INDEX(status),
  INDEX(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

