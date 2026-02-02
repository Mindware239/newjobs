CREATE TABLE IF NOT EXISTS applications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id BIGINT UNSIGNED NOT NULL,
  candidate_user_id BIGINT UNSIGNED NOT NULL,
  resume_url VARCHAR(1024) NULL,
  cover_letter TEXT NULL,
  expected_salary INT NULL,
  status ENUM('applied','screening','shortlisted','interview','offer','hired','rejected') DEFAULT 'applied',
  score FLOAT DEFAULT NULL,
  source ENUM('portal','email','referral','agency','import') DEFAULT 'portal',
  applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
  FOREIGN KEY (candidate_user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX(job_id),
  INDEX(candidate_user_id),
  INDEX(status),
  INDEX(applied_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS application_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  actor_user_id BIGINT UNSIGNED NULL,
  from_status VARCHAR(64) NULL,
  to_status VARCHAR(64) NULL,
  comment TEXT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
  INDEX (application_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

