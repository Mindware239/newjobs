-- Add category/industry column to jobs table
-- This allows employers to select which category/industry the job belongs to
-- This helps candidates filter jobs by category

ALTER TABLE `jobs` 
ADD COLUMN `category` VARCHAR(128) DEFAULT NULL COMMENT 'Job category/industry (e.g., IT/Software, Manufacturing, Sales & Marketing)' 
AFTER `language`;

-- Add index for faster filtering
ALTER TABLE `jobs` 
ADD INDEX `idx_category` (`category`);

-- Update existing jobs to use employer's industry as category
UPDATE `jobs` j
INNER JOIN `employers` e ON j.employer_id = e.id
SET j.category = e.industry
WHERE j.category IS NULL AND e.industry IS NOT NULL AND e.industry != '';

