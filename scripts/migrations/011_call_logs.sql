CREATE TABLE IF NOT EXISTS call_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employer_id BIGINT UNSIGNED NULL,
  candidate_user_id BIGINT UNSIGNED NULL,
  initiated_by BIGINT UNSIGNED NULL,
  call_start DATETIME NULL,
  call_end DATETIME NULL,
  call_status ENUM('completed','missed','failed') DEFAULT 'failed',
  provider VARCHAR(128) NULL,
  recording_url VARCHAR(1024) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX(employer_id),
  INDEX(candidate_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

