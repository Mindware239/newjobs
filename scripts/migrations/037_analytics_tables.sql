-- Analytics Tables for Employer Analytics System

-- Job Engagement Metrics
CREATE TABLE IF NOT EXISTS job_engagement (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id BIGINT UNSIGNED NOT NULL,
    views_count INT UNSIGNED DEFAULT 0,
    saves_count INT UNSIGNED DEFAULT 0,
    shares_count INT UNSIGNED DEFAULT 0,
    applications_count INT UNSIGNED DEFAULT 0,
    engagement_score DECIMAL(5,2) DEFAULT 0.00,
    last_viewed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_job (job_id),
    INDEX (engagement_score),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Job Views Tracking
CREATE TABLE IF NOT EXISTS job_views_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    viewed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    INDEX (job_id),
    INDEX (viewed_at),
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Job Saves/Bookmarks (if not exists)
CREATE TABLE IF NOT EXISTS job_saves_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    saved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_job_user (job_id, user_id),
    INDEX (job_id),
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Job Shares Tracking
CREATE TABLE IF NOT EXISTS job_shares_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    share_platform VARCHAR(50) NULL, -- 'email', 'linkedin', 'twitter', 'facebook', 'whatsapp', etc.
    shared_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    INDEX (job_id),
    INDEX (shared_at),
    INDEX (share_platform)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Activity Logs
CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_type ENUM('employer','candidate','admin','system') NOT NULL,
    actor_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL, -- 'job_created', 'application_viewed', 'resume_downloaded', etc.
    target_type VARCHAR(50) NULL, -- 'job', 'application', 'candidate', etc.
    target_id BIGINT UNSIGNED NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (actor_type, actor_id),
    INDEX (action),
    INDEX (target_type, target_id),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Communication Logs
CREATE TABLE IF NOT EXISTS communication_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employer_id BIGINT UNSIGNED NOT NULL,
    candidate_id BIGINT UNSIGNED NULL,
    application_id BIGINT UNSIGNED NULL,
    communication_type ENUM('message','email','sms','whatsapp','call','interview_invite') NOT NULL,
    direction ENUM('sent','received') NOT NULL,
    subject VARCHAR(255) NULL,
    content TEXT NULL,
    status ENUM('sent','delivered','read','failed') DEFAULT 'sent',
    response_time_seconds INT NULL, -- Time to response in seconds
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    delivered_at DATETIME NULL,
    read_at DATETIME NULL,
    FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE SET NULL,
    INDEX (employer_id),
    INDEX (candidate_id),
    INDEX (application_id),
    INDEX (communication_type),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notification Logs
CREATE TABLE IF NOT EXISTS notification_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employer_id BIGINT UNSIGNED NULL,
    candidate_id BIGINT UNSIGNED NULL,
    channel ENUM('email','sms','whatsapp','push','in_app') NOT NULL,
    template_key VARCHAR(100) NULL,
    subject VARCHAR(255) NULL,
    content TEXT NULL,
    status ENUM('sent','delivered','opened','failed','bounced') DEFAULT 'sent',
    metadata JSON NULL,
    error_message TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    delivered_at DATETIME NULL,
    opened_at DATETIME NULL,
    FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE SET NULL,
    FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX (employer_id),
    INDEX (candidate_id),
    INDEX (channel),
    INDEX (status),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Candidate Quality Scores
CREATE TABLE IF NOT EXISTS candidate_quality_scores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id BIGINT UNSIGNED NOT NULL,
    resume_completeness_score DECIMAL(5,2) DEFAULT 0.00, -- 0-100
    skill_match_percentage DECIMAL(5,2) DEFAULT 0.00, -- 0-100
    interview_score DECIMAL(5,2) NULL, -- 0-100 or 1-5
    employer_rating TINYINT UNSIGNED NULL, -- 1-5 stars
    employer_feedback TEXT NULL,
    overall_score DECIMAL(5,2) DEFAULT 0.00,
    calculated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    UNIQUE KEY unique_application (application_id),
    INDEX (overall_score),
    INDEX (calculated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Hiring Funnel Tracking
CREATE TABLE IF NOT EXISTS hiring_funnel_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id BIGINT UNSIGNED NOT NULL,
    stage ENUM('applied','shortlisted','interviewed','offered','hired','rejected') NOT NULL,
    entered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    exited_at DATETIME NULL,
    days_in_stage INT NULL,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    INDEX (application_id),
    INDEX (stage),
    INDEX (entered_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Login History / Security Logs
CREATE TABLE IF NOT EXISTS login_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    user_type ENUM('employer','candidate','admin') NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    login_successful TINYINT(1) DEFAULT 1,
    failure_reason VARCHAR(255) NULL,
    logged_in_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    logged_out_at DATETIME NULL,
    session_duration_seconds INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id, user_type),
    INDEX (login_successful),
    INDEX (logged_in_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Export Logs
CREATE TABLE IF NOT EXISTS data_export_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employer_id BIGINT UNSIGNED NOT NULL,
    export_type VARCHAR(50) NOT NULL, -- 'applications', 'jobs', 'analytics', etc.
    format ENUM('csv','pdf','excel') NOT NULL,
    file_path VARCHAR(512) NULL,
    record_count INT UNSIGNED DEFAULT 0,
    filters JSON NULL,
    exported_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE,
    INDEX (employer_id),
    INDEX (export_type),
    INDEX (exported_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

