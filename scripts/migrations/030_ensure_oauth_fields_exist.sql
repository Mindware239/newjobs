-- Ensure OAuth fields exist in users table
-- This migration checks if fields exist before adding them to avoid errors

-- Google OAuth fields
SET @dbname = DATABASE();
SET @tablename = "users";
SET @columnname = "google_id";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN google_id VARCHAR(255) NULL UNIQUE AFTER email, ADD COLUMN google_email VARCHAR(255) NULL AFTER google_id, ADD COLUMN google_name VARCHAR(255) NULL AFTER google_email, ADD COLUMN google_picture TEXT NULL AFTER google_name, ADD INDEX idx_google_id (google_id);")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Apple OAuth fields
SET @columnname = "apple_id";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN apple_id VARCHAR(255) NULL UNIQUE AFTER google_picture, ADD COLUMN apple_email VARCHAR(255) NULL AFTER apple_id, ADD COLUMN apple_name VARCHAR(255) NULL AFTER apple_email, ADD INDEX idx_apple_id (apple_id);")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

