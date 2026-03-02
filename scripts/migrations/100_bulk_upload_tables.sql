-- Bulk Uploader Accounts
CREATE TABLE IF NOT EXISTS bulk_upload_accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  username VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  type VARCHAR(50) DEFAULT NULL,
  limit_total INT NOT NULL DEFAULT 0,
  limit_used INT NOT NULL DEFAULT 0,
  expires_at DATETIME NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_bulk_username (username),
  KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Resume Batches
CREATE TABLE IF NOT EXISTS resume_batches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bulk_account_id INT NOT NULL,
  total_files INT NOT NULL DEFAULT 0,
  processed_files INT NOT NULL DEFAULT 0,
  failed_files INT NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  completed_at DATETIME NULL,
  KEY idx_bulk_account (bulk_account_id),
  KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Resume Files
CREATE TABLE IF NOT EXISTS resume_files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  batch_id INT NOT NULL,
  candidate_id INT NULL,
  filename VARCHAR(255) NOT NULL,
  filepath VARCHAR(512) NOT NULL,
  hash CHAR(64) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  failure_reason VARCHAR(255) NULL,
  parsed_data JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  processed_at DATETIME NULL,
  UNIQUE KEY uq_hash (hash),
  KEY idx_batch (batch_id),
  KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
