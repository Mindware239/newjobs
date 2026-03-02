-- Webhooks Schema Hardening
-- Run via: php scripts/migrate.php

ALTER TABLE webhooks
  ADD COLUMN event_id VARCHAR(255);

ALTER TABLE webhooks
  ADD UNIQUE INDEX uniq_event (event_id);

ALTER TABLE webhooks
  ADD COLUMN signature VARCHAR(255),
  ADD COLUMN processed_at DATETIME NULL,
  ADD COLUMN processing_time_ms INT NULL;
