-- Add missing fields to jobs table for job posting form
ALTER TABLE jobs
ADD COLUMN IF NOT EXISTS experience_type ENUM('any', 'fresher', 'experienced') DEFAULT 'any' COMMENT 'Experience requirement type',
ADD COLUMN IF NOT EXISTS min_experience INT NULL COMMENT 'Minimum years of experience',
ADD COLUMN IF NOT EXISTS max_experience INT NULL COMMENT 'Maximum years of experience',
ADD COLUMN IF NOT EXISTS offers_bonus ENUM('yes', 'no') DEFAULT 'no' COMMENT 'Whether job offers bonus',
ADD COLUMN IF NOT EXISTS call_availability ENUM('everyday', 'weekdays', 'weekdays_saturday', 'custom') DEFAULT 'everyday' COMMENT 'When candidates can call',
ADD COLUMN IF NOT EXISTS company_name VARCHAR(255) NULL COMMENT 'Company name (can override employer default)',
ADD COLUMN IF NOT EXISTS contact_person VARCHAR(255) NULL COMMENT 'Contact person name',
ADD COLUMN IF NOT EXISTS phone VARCHAR(32) NULL COMMENT 'Contact phone number',
ADD COLUMN IF NOT EXISTS email VARCHAR(255) NULL COMMENT 'Contact email',
ADD COLUMN IF NOT EXISTS contact_profile ENUM('owner', 'hr', 'recruiter') NULL COMMENT 'Contact person profile',
ADD COLUMN IF NOT EXISTS company_size ENUM('1-10', '11-50', '51-200', '201-500', '501-1000', '1001+') NULL COMMENT 'Company size (can override employer default)',
ADD COLUMN IF NOT EXISTS hiring_urgency ENUM('immediate', 'can_wait') DEFAULT 'immediate' COMMENT 'How soon position needs to be filled',
ADD COLUMN IF NOT EXISTS job_timings VARCHAR(255) NULL COMMENT 'Job working hours/timings',
ADD COLUMN IF NOT EXISTS interview_timings VARCHAR(255) NULL COMMENT 'Interview schedule timings',
ADD COLUMN IF NOT EXISTS job_address TEXT NULL COMMENT 'Complete job address';

