-- Migration: Add profile_reminders table and system settings
-- Description: Tracks automated reminders sent to candidates with incomplete profiles.

CREATE TABLE IF NOT EXISTS `profile_reminders` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `candidate_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `last_reminder_at` DATETIME NOT NULL,
    `reminder_count` INT UNSIGNED DEFAULT 1,
    `metadata` JSON DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_candidate` (`candidate_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_last_reminder` (`last_reminder_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default system settings for profile reminders
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_group`) 
VALUES 
('profile_reminder_enabled', '1', 'general'),
('profile_reminder_frequency_days', '3', 'general'),
('profile_reminder_max_per_week', '2', 'general'),
('profile_reminder_min_strength', '100', 'general')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);
