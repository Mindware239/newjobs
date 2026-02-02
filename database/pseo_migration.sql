-- Create city_areas table for localized SEO
CREATE TABLE IF NOT EXISTS `city_areas` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `city_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `city_areas_city_id_foreign` (`city_id`),
  CONSTRAINT `city_areas_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create seo_page_logs table for crawl/debug visibility
CREATE TABLE IF NOT EXISTS `seo_page_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `url` varchar(500) NOT NULL,
  `page_type` varchar(50) NOT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `status_code` int(3) NOT NULL DEFAULT 200,
  `crawled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `seo_page_logs_url_index` (`url`),
  KEY `seo_page_logs_page_type_index` (`page_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modify job_locations table to link with cities
-- We add the column first. 
-- NOTE: Data migration from text fields to city_id must be done separately or manually.
ALTER TABLE `job_locations` ADD COLUMN `city_id` int(10) UNSIGNED NULL AFTER `job_id`;

-- Add foreign key
ALTER TABLE `job_locations` ADD CONSTRAINT `job_locations_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL;

-- Ensure seo_rules table exists (it was present in dump but good to ensure)
CREATE TABLE IF NOT EXISTS `seo_rules` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_type` varchar(50) NOT NULL,
  `meta_title_template` varchar(255) NOT NULL,
  `meta_description_template` text NOT NULL,
  `meta_keywords_template` text DEFAULT NULL,
  `h1_template` varchar(255) NOT NULL,
  `canonical_rule` enum('dynamic','static') DEFAULT 'dynamic',
  `indexable` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default SEO rules if not exist
INSERT INTO `seo_rules` (`page_type`, `meta_title_template`, `meta_description_template`, `h1_template`) 
SELECT 'home', 'Find Your Dream Job | {job_count}+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in {city} and more.', 'Find Your Next Job'
WHERE NOT EXISTS (SELECT 1 FROM `seo_rules` WHERE `page_type` = 'home');

INSERT INTO `seo_rules` (`page_type`, `meta_title_template`, `meta_description_template`, `h1_template`) 
SELECT 'job_detail', '{job_title} at {company} - Apply Now', 'Hiring: {job_title} at {company} in {city}. Salary: {salary}. Apply today!', '{job_title}'
WHERE NOT EXISTS (SELECT 1 FROM `seo_rules` WHERE `page_type` = 'job_detail');

INSERT INTO `seo_rules` (`page_type`, `meta_title_template`, `meta_description_template`, `h1_template`) 
SELECT 'city_jobs', 'Jobs in {city}, {state} | Apply Now', 'Looking for jobs in {city}? Browse {job_count} openings in {city}, {state}.', 'Jobs in {city}'
WHERE NOT EXISTS (SELECT 1 FROM `seo_rules` WHERE `page_type` = 'city_jobs');

INSERT INTO `seo_rules` (`page_type`, `meta_title_template`, `meta_description_template`, `h1_template`) 
SELECT 'skill_city_jobs', '{skill} Jobs in {city} | Top Opportunities', 'Find the best {skill} jobs in {city}. Apply to top companies hiring for {skill} roles now.', '{skill} Jobs in {city}'
WHERE NOT EXISTS (SELECT 1 FROM `seo_rules` WHERE `page_type` = 'skill_city_jobs');

INSERT INTO `seo_rules` (`page_type`, `meta_title_template`, `meta_description_template`, `h1_template`) 
SELECT 'company_detail', 'Careers at {company} | Job Openings & Reviews', 'Learn about working at {company}. View open jobs, salaries, and employee reviews.', 'Careers at {company}'
WHERE NOT EXISTS (SELECT 1 FROM `seo_rules` WHERE `page_type` = 'company_detail');
