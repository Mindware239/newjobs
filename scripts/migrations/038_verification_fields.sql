ALTER TABLE employers
  ADD COLUMN IF NOT EXISTS kyc_assigned_to BIGINT UNSIGNED NULL AFTER kyc_status,
  ADD COLUMN IF NOT EXISTS kyc_level ENUM('basic','full') DEFAULT 'basic' AFTER kyc_assigned_to,
  ADD COLUMN IF NOT EXISTS kyc_rejection_reason TEXT NULL AFTER kyc_level,
  ADD COLUMN IF NOT EXISTS kyc_escalated TINYINT(1) DEFAULT 0 AFTER kyc_rejection_reason,
  ADD COLUMN IF NOT EXISTS kyc_escalation_reason TEXT NULL AFTER kyc_escalated;

CREATE INDEX IF NOT EXISTS idx_employers_kyc_assigned_to ON employers(kyc_assigned_to);
