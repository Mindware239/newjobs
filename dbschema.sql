CREATE TABLE candidate_views (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employer_id BIGINT UNSIGNED NOT NULL,
    candidate_id BIGINT UNSIGNED NOT NULL,
    viewed_at TIMESTAMP NOT NULL,
    FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE
);