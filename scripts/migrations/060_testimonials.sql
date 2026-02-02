CREATE TABLE IF NOT EXISTS testimonials (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  testimonial_type VARCHAR(50) NOT NULL,
  name VARCHAR(255) NOT NULL,
  designation VARCHAR(255) NULL,
  company VARCHAR(255) NULL,
  message TEXT NULL,
  video_url VARCHAR(512) NULL,
  image VARCHAR(512) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_testimonials_type ON testimonials (testimonial_type);
CREATE INDEX idx_testimonials_active ON testimonials (is_active);
CREATE INDEX idx_testimonials_created ON testimonials (created_at);
