CREATE TABLE IF NOT EXISTS employment_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  candidate_id INT NOT NULL,
  company_name VARCHAR(255) NOT NULL,
  designation VARCHAR(255),
  employee_id VARCHAR(100),
  start_date DATE,
  end_date DATE,
  status_overall ENUM('under_review','verified','not_verified') DEFAULT 'under_review',
  status_level1 ENUM('pending','auto_flagged','approved','rejected') DEFAULT 'pending',
  status_level2 ENUM('pending','verified','mismatch','no_response','declined') DEFAULT 'pending',
  status_level3 ENUM('pending','checks_passed','checks_failed') DEFAULT 'pending',
  risk_score INT DEFAULT 0,
  risk_category ENUM('fully_verified','partially_verified','unverified') DEFAULT NULL,
  consent_given TINYINT(1) DEFAULT 0,
  consent_at DATETIME NULL,
  verified_badge TINYINT(1) DEFAULT 0,
  admin_override ENUM('none','approved','rejected') DEFAULT 'none',
  verification_date DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_candidate (candidate_id),
  INDEX idx_company (company_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS employment_documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employment_id INT NOT NULL,
  doc_type ENUM('offer_letter','relieving_letter','experience_letter','salary_slip','bank_statement','form16') NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  file_hash CHAR(64) NOT NULL,
  mime_type VARCHAR(100) NOT NULL,
  size_bytes INT NOT NULL,
  metadata JSON NULL,
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_employment (employment_id),
  INDEX idx_type (doc_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS employment_document_texts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  document_id INT NOT NULL,
  extracted_text LONGTEXT,
  language VARCHAR(10) DEFAULT 'en',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_doc (document_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS verification_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employment_id INT NOT NULL,
  hr_email VARCHAR(255) NOT NULL,
  hr_phone VARCHAR(40) NULL,
  manager_email VARCHAR(255) NULL,
  company_website VARCHAR(255) NULL,
  cin VARCHAR(50) NULL,
  gst VARCHAR(50) NULL,
  token VARCHAR(128) NOT NULL,
  expires_at DATETIME NOT NULL,
  status ENUM('pending','email_sent','expired') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_emp (employment_id),
  INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS verification_responses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  request_id INT NOT NULL,
  status ENUM('verified','mismatch','no_response','declined') NOT NULL,
  confirmed_working TINYINT(1) DEFAULT 0,
  duration_text VARCHAR(255) NULL,
  designation VARCHAR(255) NULL,
  rehire_eligibility ENUM('yes','no','unknown') DEFAULT 'unknown',
  misconduct TINYINT(1) DEFAULT 0,
  remarks TEXT NULL,
  responder_ip VARCHAR(64) NULL,
  responded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_req (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS verification_scores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employment_id INT NOT NULL,
  score INT NOT NULL,
  category ENUM('fully_verified','partially_verified','unverified') NOT NULL,
  breakdown JSON NULL,
  calculated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_emp (employment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS verification_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employment_id INT NOT NULL,
  event VARCHAR(100) NOT NULL,
  metadata JSON NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_emp (employment_id),
  INDEX idx_event (event)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS employer_unlocks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employment_id INT NOT NULL,
  employer_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  currency VARCHAR(10) DEFAULT 'INR',
  status ENUM('pending','paid','failed') DEFAULT 'pending',
  payment_id INT NULL,
  invoice_number VARCHAR(50) NULL,
  invoice_url VARCHAR(255) NULL,
  unlocked_at DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_emp (employment_id),
  INDEX idx_employer (employer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

