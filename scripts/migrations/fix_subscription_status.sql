ALTER TABLE employer_subscriptions MODIFY COLUMN status ENUM('active','expired','cancelled','trial','suspended','pending') DEFAULT 'pending';
ALTER TABLE subscription_payments MODIFY COLUMN status ENUM('pending','processing','completed','failed','refunded','cancelled') DEFAULT 'pending';
