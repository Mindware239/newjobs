CREATE TABLE IF NOT EXISTS employer_payments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employer_id BIGINT UNSIGNED NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  currency VARCHAR(8) DEFAULT 'INR',
  gateway VARCHAR(64) NULL,
  status ENUM('pending','success','failed','refunded') DEFAULT 'pending',
  txn_id VARCHAR(255) NULL,
  meta JSON NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE,
  INDEX(employer_id),
  INDEX(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

