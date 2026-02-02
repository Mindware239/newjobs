-- Add Apple OAuth fields to users table
ALTER TABLE users 
ADD COLUMN apple_id VARCHAR(255) NULL UNIQUE AFTER google_picture,
ADD COLUMN apple_email VARCHAR(255) NULL AFTER apple_id,
ADD COLUMN apple_name VARCHAR(255) NULL AFTER apple_email,
ADD INDEX idx_apple_id (apple_id);

