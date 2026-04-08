ALTER TABLE subscription_payments ADD COLUMN idempotency_key VARCHAR(64) UNIQUE AFTER employer_id;
