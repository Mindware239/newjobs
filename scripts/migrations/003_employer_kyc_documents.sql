CREATE TABLE IF NOT EXISTS employer_kyc_documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employer_id BIGINT UNSIGNED NOT NULL,
  doc_type ENUM('business_license','tax_id','address_proof','director_id','other') NOT NULL,
  file_url VARCHAR(1024) NOT NULL,
  file_name VARCHAR(255) NULL,
  uploaded_by BIGINT UNSIGNED NULL,
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  review_status ENUM('pending','approved','rejected') DEFAULT 'pending',
  review_notes TEXT NULL,
  reviewed_by BIGINT UNSIGNED NULL,
  reviewed_at DATETIME NULL,
  FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE,
  INDEX(employer_id),
  INDEX(review_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

