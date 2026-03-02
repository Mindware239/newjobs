-- Employer Payments Schema Hardening
-- Run via: php scripts/migrate.php

ALTER TABLE employer_payments
  ADD COLUMN subscription_payment_id BIGINT AFTER employer_id;

CREATE INDEX idx_subscription_payment ON employer_payments (subscription_payment_id);

ALTER TABLE employer_payments
  ADD UNIQUE INDEX uniq_txn (txn_id);

ALTER TABLE employer_payments
  ADD COLUMN updated_at DATETIME NULL,
  ADD COLUMN processed_at DATETIME NULL,
  ADD COLUMN failed_at DATETIME NULL,
  ADD COLUMN refunded_at DATETIME NULL;

-- Optional: if MySQL supports JSON (5.7+), convert meta to JSON
ALTER TABLE employer_payments
  MODIFY meta JSON;
