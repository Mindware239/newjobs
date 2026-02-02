-- Discount Codes Table
CREATE TABLE IF NOT EXISTS discount_codes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(64) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  
  -- Discount Details
  discount_type ENUM('percentage','fixed_amount') DEFAULT 'percentage',
  discount_value DECIMAL(10,2) NOT NULL,
  min_amount DECIMAL(10,2) DEFAULT 0.00,
  max_discount DECIMAL(10,2) NULL,
  
  -- Validity
  valid_from DATETIME NOT NULL,
  valid_until DATETIME NOT NULL,
  
  -- Usage Limits
  max_uses INT DEFAULT NULL,
  used_count INT DEFAULT 0,
  max_uses_per_user INT DEFAULT 1,
  
  -- Applicability
  applicable_plans JSON NULL, -- Array of plan IDs or 'all'
  applicable_billing_cycles JSON NULL, -- ['monthly','quarterly','annual'] or null for all
  
  -- Status
  is_active TINYINT(1) DEFAULT 1,
  
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX(code),
  INDEX(is_active),
  INDEX(valid_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

