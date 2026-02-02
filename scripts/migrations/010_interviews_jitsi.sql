ALTER TABLE interviews
  MODIFY status ENUM('scheduled','rescheduled','live','completed','cancelled') DEFAULT 'scheduled',
  ADD COLUMN room_name VARCHAR(128) NULL AFTER meeting_link,
  ADD COLUMN room_password_enc TEXT NULL AFTER room_name,
  ADD COLUMN is_premium TINYINT(1) NOT NULL DEFAULT 0 AFTER room_password_enc,
  ADD COLUMN started_at DATETIME NULL AFTER is_premium,
  ADD COLUMN ended_at DATETIME NULL AFTER started_at,
  ADD COLUMN recording_url VARCHAR(2048) NULL AFTER ended_at,
  ADD COLUMN created_by_role VARCHAR(32) NULL AFTER recording_url,
  ADD INDEX idx_interviews_room_name (room_name),
  ADD INDEX idx_interviews_started_at (started_at),
  ADD INDEX idx_interviews_ended_at (ended_at),
  ADD INDEX idx_interviews_status (status);

CREATE TABLE IF NOT EXISTS interview_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  interview_id BIGINT UNSIGNED NOT NULL,
  actor_user_id BIGINT UNSIGNED NULL,
  actor_role VARCHAR(32) NULL,
  event_type VARCHAR(64) NOT NULL,
  payload LONGTEXT NULL,
  ip_address VARCHAR(64) NULL,
  user_agent VARCHAR(512) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_interview_events_interview_id (interview_id),
  INDEX idx_interview_events_actor_user_id (actor_user_id),
  INDEX idx_interview_events_event_type (event_type),
  INDEX idx_interview_events_created_at (created_at),
  FOREIGN KEY (interview_id) REFERENCES interviews(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

