CREATE TABLE IF NOT EXISTS employer_verification_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employer_id BIGINT UNSIGNED NOT NULL,
    rule_name VARCHAR(100) NOT NULL,
    result JSON NULL,
    risk_score_change INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (employer_id),
    INDEX (rule_name)
);

CREATE TABLE IF NOT EXISTS employer_risk_scores (
    employer_id BIGINT UNSIGNED PRIMARY KEY,
    score INT NOT NULL DEFAULT 0,
    risk_level ENUM('low','medium','high','blocked') NOT NULL DEFAULT 'medium',
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    flagged TINYINT(1) DEFAULT 0,
    notes TEXT NULL
);

CREATE TABLE IF NOT EXISTS employer_blacklist (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('email','domain','gst','ip','company') NOT NULL,
    value VARCHAR(255) NOT NULL,
    reason VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY type_value_unique (type, value)
);

CREATE TABLE IF NOT EXISTS document_ocr_results (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    employer_id BIGINT UNSIGNED NOT NULL,
    extracted_name VARCHAR(255) NULL,
    extracted_gst VARCHAR(25) NULL,
    extracted_cin VARCHAR(25) NULL,
    extracted_address TEXT NULL,
    extracted_registration_date DATE NULL,
    confidence_score DECIMAL(5,2) DEFAULT 0,
    raw_text LONGTEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (document_id),
    INDEX (employer_id)
);

CREATE TABLE IF NOT EXISTS job_review_queue (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id BIGINT UNSIGNED NOT NULL,
    employer_id BIGINT UNSIGNED NOT NULL,
    review_reason VARCHAR(255) NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    reviewer_id BIGINT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    comments TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (job_id),
    INDEX (employer_id),
    INDEX (status)
);


