-- Subscription Credits
CREATE TABLE IF NOT EXISTS subscription_credits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subscription_id INT NOT NULL,
  employer_id INT NOT NULL,
  type VARCHAR(50) NOT NULL,
  amount INT NOT NULL DEFAULT 0,
  note TEXT NULL,
  created_by_user_id INT NULL,
  applied TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_subscription (subscription_id),
  KEY idx_employer (employer_id),
  KEY idx_type (type),
  KEY idx_applied (applied)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Subscription History
CREATE TABLE IF NOT EXISTS subscription_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subscription_id INT NOT NULL,
  employer_id INT NOT NULL,
  plan_id INT NOT NULL,
  from_plan_id INT NULL,
  status VARCHAR(30) NOT NULL,
  started_at DATETIME NOT NULL,
  ends_at DATETIME NOT NULL,
  changed_by_user_id INT NULL,
  change_reason TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_subscription (subscription_id),
  KEY idx_employer (employer_id),
  KEY idx_status (status),
  KEY idx_plan (plan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
