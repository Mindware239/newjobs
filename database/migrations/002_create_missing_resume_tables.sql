-- Migration: Create Missing Resume Builder Tables
-- Date: 2025-12-26
-- Description: Creates ai_resume_suggestions and video_resumes tables that may be missing

-- 6. ai_resume_suggestions (AI ENHANCEMENTS)
-- Check if table exists before creating
SET @db_name = DATABASE();
SET @table_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'ai_resume_suggestions'
);

SET @sql = IF(@table_exists = 0, 
    'CREATE TABLE `ai_resume_suggestions` (
      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      `resume_id` bigint(20) UNSIGNED NOT NULL,
      `section_type` varchar(50) DEFAULT NULL,
      `suggestion_type` enum(\'summary\',\'keyword\',\'experience\',\'skill\',\'ats_optimization\') NOT NULL,
      `suggestion_text` text NOT NULL,
      `score` int(11) DEFAULT 0 COMMENT \'Relevance score 0-100\',
      `is_applied` tinyint(1) DEFAULT 0,
      `created_at` datetime DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_resume_id` (`resume_id`),
      KEY `idx_suggestion_type` (`suggestion_type`),
      CONSTRAINT `fk_ai_suggestions_resume` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'SELECT "Table ai_resume_suggestions already exists" AS msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 7. video_resumes (ENHANCED VIDEO SUPPORT)
-- Check if table exists before creating
SET @table_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.TABLES 
    WHERE TABLE_SCHEMA = @db_name 
    AND TABLE_NAME = 'video_resumes'
);

SET @sql = IF(@table_exists = 0, 
    'CREATE TABLE `video_resumes` (
      `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      `resume_id` bigint(20) UNSIGNED NOT NULL,
      `file_path` varchar(512) NOT NULL,
      `thumbnail_path` varchar(512) DEFAULT NULL,
      `duration` int(11) DEFAULT NULL COMMENT \'Duration in seconds\',
      `file_size` bigint(20) DEFAULT NULL COMMENT \'File size in bytes\',
      `mime_type` varchar(100) DEFAULT NULL,
      `transcription` text DEFAULT NULL COMMENT \'AI-generated transcript\',
      `is_premium` tinyint(1) DEFAULT 0,
      `created_at` datetime DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `idx_resume_id` (`resume_id`),
      CONSTRAINT `fk_video_resumes_resume` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    'SELECT "Table video_resumes already exists" AS msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

