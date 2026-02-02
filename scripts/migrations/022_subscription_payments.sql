-- Subscription Payments Table
CREATE TABLE IF NOT EXISTS subscription_payments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  subscription_id BIGINT UNSIGNED NOT NULL,
  employer_id BIGINT UNSIGNED NOT NULL,
  
  -- Payment Details
  amount DECIMAL(10,2) NOT NULL,
  currency VARCHAR(8) DEFAULT 'INR',
  billing_cycle ENUM('monthly','quarterly','annual') NOT NULL,
  
  -- Payment Gateway
  gateway VARCHAR(32) DEFAULT 'razorpay',
  gateway_payment_id VARCHAR(255) NULL,
  gateway_order_id VARCHAR(255) NULL,
  gateway_signature VARCHAR(512) NULL,
  
  -- Status
  status ENUM('pending','processing','completed','failed','refunded','cancelled') DEFAULT 'pending',
  failure_reason TEXT NULL,
  
  -- Invoice
  invoice_number VARCHAR(64) UNIQUE NULL,
  invoice_url VARCHAR(512) NULL,
  invoice_generated_at DATETIME NULL,
  
  -- Refund
  refund_amount DECIMAL(10,2) DEFAULT 0.00,
  refund_reason TEXT NULL,
  refunded_at DATETIME NULL,
  
  -- Metadata
  metadata JSON NULL,
  
  paid_at DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (subscription_id) REFERENCES employer_subscriptions(id) ON DELETE CASCADE,
  FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE,
  INDEX(subscription_id),
  INDEX(employer_id),
  INDEX(status),
  INDEX(gateway_payment_id),
  INDEX(invoice_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

