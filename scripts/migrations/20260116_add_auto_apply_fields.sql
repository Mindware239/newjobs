ALTER TABLE `candidates`
  ADD COLUMN `auto_apply_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `preferences_data`,
  ADD COLUMN `auto_apply_threshold` INT NOT NULL DEFAULT 70 AFTER `auto_apply_enabled`,
  ADD COLUMN `auto_apply_cooldown_minutes` INT NOT NULL DEFAULT 1440 AFTER `auto_apply_threshold`,
  ADD COLUMN `auto_apply_last_run_at` DATETIME NULL AFTER `auto_apply_cooldown_minutes`;

ALTER TABLE `applications`
  ADD COLUMN `application_method` ENUM('manual','auto') NOT NULL DEFAULT 'manual' AFTER `source`,
  ADD COLUMN `match_score` INT NULL AFTER `score`;

