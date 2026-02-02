-- Candidate Interest Tracking Table
CREATE TABLE IF NOT EXISTS candidate_interest (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidate_id BIGINT UNSIGNED NOT NULL,
  employer_id BIGINT UNSIGNED NOT NULL,
  job_id BIGINT UNSIGNED NULL,
  
  -- Interest Level
  interest_level ENUM('viewed','applied','shortlisted','high_interest') DEFAULT 'viewed',
  
  -- Metadata
  metadata JSON NULL,
  
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
  FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE,
  FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
  UNIQUE KEY unique_interest (candidate_id, employer_id, job_id),
  INDEX(candidate_id),
  INDEX(employer_id),
  INDEX(job_id),
  INDEX(interest_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

