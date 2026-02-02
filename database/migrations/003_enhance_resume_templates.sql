-- Migration: Enhance Resume Templates with Advanced Filters
-- Date: 2025-01-XX
-- Description: Adds photo support, layout types, color schemes, and job categories

-- Add new columns to resume_templates table
ALTER TABLE `resume_templates`
ADD COLUMN IF NOT EXISTS `has_photo` tinyint(1) DEFAULT 0 COMMENT 'Template supports photo/headshot',
ADD COLUMN IF NOT EXISTS `layout_type` varchar(50) DEFAULT 'single-column' COMMENT 'single-column, two-column, three-column',
ADD COLUMN IF NOT EXISTS `color_scheme` varchar(50) DEFAULT NULL COMMENT 'blue, green, purple, red, black, custom',
ADD COLUMN IF NOT EXISTS `tags` text DEFAULT NULL COMMENT 'JSON array of tags for filtering';

-- Update existing templates with default values
UPDATE `resume_templates` SET 
    `has_photo` = 0,
    `layout_type` = 'single-column',
    `color_scheme` = 'blue'
WHERE `has_photo` IS NULL;

-- Note: In MySQL, IF NOT EXISTS is not supported for ALTER TABLE ADD COLUMN
-- This migration should be run manually or use a tool that checks column existence first

