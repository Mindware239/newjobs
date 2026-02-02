-- Migration: Create Resume Builder Tables
-- Date: 2025-12-26
-- Description: Creates all tables needed for Canva-style resume builder system

-- 1. resumes (CORE TABLE)
CREATE TABLE IF NOT EXISTS `resumes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint(20) UNSIGNED NOT NULL,
  `template_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'My Resume',
  `job_category` varchar(100) DEFAULT NULL,
  `status` enum('draft','active','hidden','archived') DEFAULT 'draft',
  `strength_score` int(11) DEFAULT 0 COMMENT 'Percentage 0-100',
  `ats_score` int(11) DEFAULT 0 COMMENT 'ATS optimization score 0-100',
  `is_primary` tinyint(1) DEFAULT 0 COMMENT 'Default resume for applications',
  `pdf_url` varchar(512) DEFAULT NULL COMMENT 'Generated PDF path',
  `preview_image` varchar(512) DEFAULT NULL COMMENT 'Thumbnail for template selection',
  `version` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_candidate_id` (`candidate_id`),
  KEY `idx_template_id` (`template_id`),
  KEY `idx_status` (`status`),
  KEY `idx_is_primary` (`candidate_id`, `is_primary`),
  CONSTRAINT `fk_resumes_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. resume_sections (STORE VISUAL EDITOR DATA)
CREATE TABLE IF NOT EXISTS `resume_sections` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `resume_id` bigint(20) UNSIGNED NOT NULL,
  `section_type` enum('header','summary','experience','education','skills','languages','certifications','projects','achievements','references') NOT NULL,
  `section_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'JSON: content, styling, layout, position' CHECK (json_valid(`section_data`)),
  `sort_order` int(11) DEFAULT 0,
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_resume_id` (`resume_id`),
  KEY `idx_sort_order` (`resume_id`, `sort_order`),
  CONSTRAINT `fk_resume_sections_resume` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. resume_templates (CANVA-STYLE TEMPLATES)
CREATE TABLE IF NOT EXISTS `resume_templates` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL COMMENT 'Professional, Creative, Modern, Classic',
  `job_category` varchar(100) DEFAULT NULL COMMENT 'IT, Marketing, Finance, etc.',
  `is_premium` tinyint(1) DEFAULT 0,
  `preview_image` varchar(512) DEFAULT NULL,
  `template_schema` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON: default sections, layout, colors, fonts' CHECK (json_valid(`template_schema`)),
  `css_styles` text DEFAULT NULL COMMENT 'Custom CSS for template',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_category` (`category`),
  KEY `idx_is_premium` (`is_premium`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. resume_analytics (TRACK USAGE)
CREATE TABLE IF NOT EXISTS `resume_analytics` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `resume_id` bigint(20) UNSIGNED NOT NULL,
  `event_type` enum('view','download','share','shortlist','application') NOT NULL,
  `employer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON: additional event data' CHECK (json_valid(`metadata`)),
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_resume_id` (`resume_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_resume_analytics_resume` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. resume_share_links (PUBLIC SHARING)
CREATE TABLE IF NOT EXISTS `resume_share_links` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `resume_id` bigint(20) UNSIGNED NOT NULL,
  `token` varchar(64) NOT NULL COMMENT 'UUID v4 for sharing',
  `password` varchar(255) DEFAULT NULL COMMENT 'Optional password protection',
  `expires_at` datetime DEFAULT NULL,
  `max_views` int(11) DEFAULT NULL,
  `view_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_resume_id` (`resume_id`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `fk_resume_share_links_resume` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. ai_resume_suggestions (AI ENHANCEMENTS)
CREATE TABLE IF NOT EXISTS `ai_resume_suggestions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `resume_id` bigint(20) UNSIGNED NOT NULL,
  `section_type` varchar(50) DEFAULT NULL,
  `suggestion_type` enum('summary','keyword','experience','skill','ats_optimization') NOT NULL,
  `suggestion_text` text NOT NULL,
  `score` int(11) DEFAULT 0 COMMENT 'Relevance score 0-100',
  `is_applied` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_resume_id` (`resume_id`),
  KEY `idx_suggestion_type` (`suggestion_type`),
  CONSTRAINT `fk_ai_suggestions_resume` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. video_resumes (ENHANCED VIDEO SUPPORT)
CREATE TABLE IF NOT EXISTS `video_resumes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `resume_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(512) NOT NULL,
  `thumbnail_path` varchar(512) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'Duration in seconds',
  `file_size` bigint(20) DEFAULT NULL COMMENT 'File size in bytes',
  `mime_type` varchar(100) DEFAULT NULL,
  `transcription` text DEFAULT NULL COMMENT 'AI-generated transcript',
  `is_premium` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_resume_id` (`resume_id`),
  CONSTRAINT `fk_video_resumes_resume` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add resume_id to applications table (for selecting specific resume)
-- Note: MySQL/MariaDB doesn't support IF NOT EXISTS for ALTER TABLE
-- This migration checks existence before adding (safe to run multiple times)

-- Step 1: Add column if it doesn't exist
SET @db_name = DATABASE();
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'applications' 
    AND COLUMN_NAME = 'resume_id'
);

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `applications` ADD COLUMN `resume_id` bigint(20) UNSIGNED DEFAULT NULL AFTER `resume_url`',
    'SELECT "Column resume_id already exists" AS msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 2: Add index if it doesn't exist
SET @idx_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'applications' 
    AND INDEX_NAME = 'idx_resume_id'
);

SET @sql = IF(@idx_exists = 0, 
    'ALTER TABLE `applications` ADD KEY `idx_resume_id` (`resume_id`)',
    'SELECT "Index idx_resume_id already exists" AS msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 3: Add foreign key constraint if it doesn't exist
SET @fk_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'applications' 
    AND CONSTRAINT_NAME = 'fk_applications_resume'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @resumes_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'resumes'
);

SET @sql = IF(@fk_exists = 0 AND @resumes_exists > 0, 
    'ALTER TABLE `applications` ADD CONSTRAINT `fk_applications_resume` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE SET NULL',
    'SELECT "Foreign key fk_applications_resume already exists or resumes table missing" AS msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

