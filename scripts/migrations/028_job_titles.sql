-- Job Titles Table for Autocomplete
CREATE TABLE IF NOT EXISTS job_titles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL,
  category VARCHAR(100) NULL,
  is_active TINYINT(1) DEFAULT 1,
  usage_count INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_title (title),
  INDEX idx_slug (slug),
  INDEX idx_category (category),
  INDEX idx_active (is_active),
  FULLTEXT idx_title_fulltext (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

