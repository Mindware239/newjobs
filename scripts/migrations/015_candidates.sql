-- Candidate Profile Table
CREATE TABLE IF NOT EXISTS candidates (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL UNIQUE,
  full_name VARCHAR(255) NULL,
  dob DATE NULL,
  gender ENUM('male','female','other','prefer_not_to_say') NULL,
  mobile VARCHAR(32) NULL,
  city VARCHAR(100) NULL,
  state VARCHAR(100) NULL,
  country VARCHAR(100) NULL,
  profile_picture VARCHAR(512) NULL,
  resume_url VARCHAR(512) NULL,
  video_intro_url VARCHAR(512) NULL,
  video_intro_type ENUM('upload','youtube') NULL,
  self_introduction TEXT NULL,
  expected_salary_min INT NULL,
  expected_salary_max INT NULL,
  current_salary INT NULL,
  notice_period INT NULL COMMENT 'Days',
  preferred_job_location VARCHAR(255) NULL,
  portfolio_url VARCHAR(512) NULL,
  linkedin_url VARCHAR(512) NULL,
  github_url VARCHAR(512) NULL,
  website_url VARCHAR(512) NULL,
  profile_strength INT DEFAULT 0 COMMENT 'Percentage 0-100',
  is_profile_complete TINYINT(1) DEFAULT 0,
  is_verified TINYINT(1) DEFAULT 0,
  is_premium TINYINT(1) DEFAULT 0,
  premium_expires_at DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX(user_id),
  INDEX(is_profile_complete),
  INDEX(is_premium),
  INDEX(profile_strength)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Candidate Education
CREATE TABLE IF NOT EXISTS candidate_education (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidate_id BIGINT UNSIGNED NOT NULL,
  degree VARCHAR(255) NOT NULL,
  field_of_study VARCHAR(255) NULL,
  institution VARCHAR(255) NOT NULL,
  start_date DATE NULL,
  end_date DATE NULL,
  is_current TINYINT(1) DEFAULT 0,
  grade VARCHAR(50) NULL,
  description TEXT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
  INDEX(candidate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Candidate Experience
CREATE TABLE IF NOT EXISTS candidate_experience (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidate_id BIGINT UNSIGNED NOT NULL,
  job_title VARCHAR(255) NOT NULL,
  company_name VARCHAR(255) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NULL,
  is_current TINYINT(1) DEFAULT 0,
  description TEXT NULL,
  location VARCHAR(255) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
  INDEX(candidate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Candidate Skills (Many-to-Many with skills table)
CREATE TABLE IF NOT EXISTS candidate_skills (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidate_id BIGINT UNSIGNED NOT NULL,
  skill_id BIGINT UNSIGNED NOT NULL,
  proficiency_level ENUM('beginner','intermediate','advanced','expert') DEFAULT 'intermediate',
  years_of_experience INT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
  FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
  UNIQUE KEY unique_candidate_skill (candidate_id, skill_id),
  INDEX(candidate_id),
  INDEX(skill_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Candidate Languages
CREATE TABLE IF NOT EXISTS candidate_languages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidate_id BIGINT UNSIGNED NOT NULL,
  language VARCHAR(100) NOT NULL,
  proficiency ENUM('basic','conversational','fluent','native') DEFAULT 'conversational',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
  UNIQUE KEY unique_candidate_language (candidate_id, language),
  INDEX(candidate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Job Bookmarks
CREATE TABLE IF NOT EXISTS job_bookmarks (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidate_id BIGINT UNSIGNED NOT NULL,
  job_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
  FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
  UNIQUE KEY unique_bookmark (candidate_id, job_id),
  INDEX(candidate_id),
  INDEX(job_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Job Views (for recently viewed)
CREATE TABLE IF NOT EXISTS job_views (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidate_id BIGINT UNSIGNED NOT NULL,
  job_id BIGINT UNSIGNED NOT NULL,
  viewed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
  FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
  INDEX(candidate_id),
  INDEX(job_id),
  INDEX(viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Candidate Premium/Boost Purchases
CREATE TABLE IF NOT EXISTS candidate_premium_purchases (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidate_id BIGINT UNSIGNED NOT NULL,
  plan_type ENUM('boost_7days','boost_30days','premium_monthly','premium_yearly') NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_method ENUM('razorpay','stripe','paypal') NOT NULL,
  payment_id VARCHAR(255) NULL,
  status ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
  expires_at DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
  INDEX(candidate_id),
  INDEX(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

