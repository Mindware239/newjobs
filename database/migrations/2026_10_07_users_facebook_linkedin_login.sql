-- Facebook / LinkedIn login (SocialOAuthService also adds these on first use).
ALTER TABLE users
    ADD COLUMN facebook_id VARCHAR(255) NULL,
    ADD COLUMN facebook_email VARCHAR(255) NULL,
    ADD COLUMN facebook_name VARCHAR(255) NULL,
    ADD UNIQUE KEY uq_users_facebook_id (facebook_id),
    ADD COLUMN linkedin_id VARCHAR(255) NULL,
    ADD COLUMN linkedin_email VARCHAR(255) NULL,
    ADD COLUMN linkedin_name VARCHAR(255) NULL,
    ADD UNIQUE KEY uq_users_linkedin_id (linkedin_id);
