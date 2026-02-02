CREATE TABLE IF NOT EXISTS sales_leads (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employer_id BIGINT UNSIGNED DEFAULT NULL,
  company_name VARCHAR(255) DEFAULT NULL,
  contact_name VARCHAR(255) DEFAULT NULL,
  contact_email VARCHAR(255) DEFAULT NULL,
  contact_phone VARCHAR(64) DEFAULT NULL,
  stage ENUM('new','contacted','demo_done','follow_up','payment_pending','converted','lost') DEFAULT 'new',
  assigned_to BIGINT UNSIGNED DEFAULT NULL,
  source ENUM('import','form','referral','cold_call') DEFAULT 'import',
  notes TEXT DEFAULT NULL,
  next_followup_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (assigned_to), INDEX (stage)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
