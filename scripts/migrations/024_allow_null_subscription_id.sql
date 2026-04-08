-- Migration 024: Allow NULL subscription_id and update action_type in subscription_usage_logs
-- Created: 2026-04-08

ALTER TABLE subscription_usage_logs 
MODIFY COLUMN subscription_id BIGINT UNSIGNED NULL,
MODIFY COLUMN action_type ENUM('contact_view','resume_download','chat_message','job_post','filter_used','mobile_view', 'free_job_used') NOT NULL;
