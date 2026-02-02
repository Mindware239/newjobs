CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entity_type VARCHAR(64),
  entity_id BIGINT UNSIGNED,
  action VARCHAR(128),
  performed_by BIGINT UNSIGNED NULL,
  metadata JSON NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX(entity_type, entity_id),
  INDEX(performed_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

