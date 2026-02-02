CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('super_admin','admin','employer','candidate') NOT NULL DEFAULT 'candidate',
  status ENUM('pending','active','suspended','deleted') NOT NULL DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  last_login DATETIME NULL,
  phone VARCHAR(32) NULL,
  is_email_verified TINYINT(1) DEFAULT 0,
  is_phone_verified TINYINT(1) DEFAULT 0,
  twofa_secret VARCHAR(255) NULL,
  INDEX (role),
  INDEX (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

