-- Migration: Create candidate_job_scores table for AI-powered job matching
-- This table stores AI-generated match scores between candidates and jobs

CREATE TABLE IF NOT EXISTS candidate_job_scores (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    candidate_id BIGINT UNSIGNED NOT NULL COMMENT 'References candidates.id',
    job_id BIGINT UNSIGNED NOT NULL COMMENT 'References jobs.id',
    overall_match_score INT NOT NULL DEFAULT 0 COMMENT 'Overall match score 0-100',
    skill_score INT NOT NULL DEFAULT 0 COMMENT 'Skill match score 0-100',
    experience_score INT NOT NULL DEFAULT 0 COMMENT 'Experience match score 0-100',
    education_score INT NOT NULL DEFAULT 0 COMMENT 'Education match score 0-100',
    matched_skills JSON NULL COMMENT 'Array of matched skill names',
    missing_skills JSON NULL COMMENT 'Array of missing/required skills',
    extra_relevant_skills JSON NULL COMMENT 'Array of candidate skills not in job but relevant',
    summary TEXT NULL COMMENT 'AI-generated recruiter summary (1-3 lines)',
    recommendation ENUM('Reject', 'Review', 'Shortlist', 'Strong Hire') NULL COMMENT 'AI recommendation',
    ai_parsed_at DATETIME NULL COMMENT 'When AI scoring was last performed',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uniq_candidate_job (candidate_id, job_id),
    KEY idx_job_score (job_id, overall_match_score DESC),
    KEY idx_candidate_score (candidate_id, overall_match_score DESC),
    KEY idx_recommendation (recommendation, overall_match_score DESC),
    KEY idx_ai_parsed (ai_parsed_at),
    
    CONSTRAINT fk_cjs_candidate FOREIGN KEY (candidate_id) 
        REFERENCES candidates(id) ON DELETE CASCADE,
    CONSTRAINT fk_cjs_job FOREIGN KEY (job_id) 
        REFERENCES jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='AI-generated match scores between candidates and jobs';

