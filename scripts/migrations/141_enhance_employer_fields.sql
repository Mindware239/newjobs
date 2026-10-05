-- Migration: Add tax_id and register_as to employers table
-- Description: Supports Indian GSTIN and Individual/Company registration types.

ALTER TABLE `employers` ADD COLUMN IF NOT EXISTS `tax_id` VARCHAR(20) DEFAULT NULL AFTER `postal_code`;
ALTER TABLE `employers` ADD COLUMN IF NOT EXISTS `register_as` ENUM('company', 'individual') DEFAULT 'company' AFTER `user_id`;
ALTER TABLE `employers` ADD COLUMN IF NOT EXISTS `company_type` VARCHAR(100) DEFAULT NULL AFTER `industry`;
ALTER TABLE `employers` ADD COLUMN IF NOT EXISTS `profession_type` VARCHAR(100) DEFAULT NULL AFTER `company_type`;
ALTER TABLE `employers` ADD COLUMN IF NOT EXISTS `service_category` VARCHAR(255) DEFAULT NULL AFTER `profession_type`;
