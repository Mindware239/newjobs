-- Add JSON columns to candidates table to store all profile data
ALTER TABLE candidates
ADD COLUMN IF NOT EXISTS education_data JSON NULL COMMENT 'Stores education records as JSON array',
ADD COLUMN IF NOT EXISTS experience_data JSON NULL COMMENT 'Stores experience records as JSON array',
ADD COLUMN IF NOT EXISTS skills_data JSON NULL COMMENT 'Stores skills as JSON array',
ADD COLUMN IF NOT EXISTS languages_data JSON NULL COMMENT 'Stores languages as JSON array';

