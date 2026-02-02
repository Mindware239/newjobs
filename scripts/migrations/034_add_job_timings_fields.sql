-- Add job_timings, interview_timings, and job_address fields to jobs table
-- These fields were missing and causing "Column not found" errors
-- Run this migration if you see error: "Unknown column 'job_timings' in 'field list'"

ALTER TABLE jobs
ADD COLUMN job_timings VARCHAR(255) NULL COMMENT 'Job working hours/timings',
ADD COLUMN interview_timings VARCHAR(255) NULL COMMENT 'Interview schedule timings',
ADD COLUMN job_address TEXT NULL COMMENT 'Complete job address';
