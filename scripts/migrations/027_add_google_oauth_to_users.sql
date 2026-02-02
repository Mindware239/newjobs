-- Add Google OAuth fields to users table
ALTER TABLE users 
ADD COLUMN google_id VARCHAR(255) NULL UNIQUE AFTER email,
ADD COLUMN google_email VARCHAR(255) NULL AFTER google_id,
ADD COLUMN google_name VARCHAR(255) NULL AFTER google_email,
ADD COLUMN google_picture TEXT NULL AFTER google_name,
ADD INDEX idx_google_id (google_id);

