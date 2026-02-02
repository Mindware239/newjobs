CREATE TABLE IF NOT EXISTS interviews (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  employer_id BIGINT UNSIGNED NOT NULL,
  scheduled_by BIGINT UNSIGNED NOT NULL,
  interview_type ENUM('phone','video','onsite') DEFAULT 'phone',
  scheduled_start DATETIME NOT NULL,
  scheduled_end DATETIME NOT NULL,
  timezone VARCHAR(64) DEFAULT 'Asia/Kolkata',
  location VARCHAR(512) DEFAULT NULL,
  meeting_link VARCHAR(1024) DEFAULT NULL,
  status ENUM('scheduled','rescheduled','completed','cancelled') DEFAULT 'scheduled',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
  FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE,
  INDEX (application_id),
  INDEX (employer_id),
  INDEX (scheduled_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

