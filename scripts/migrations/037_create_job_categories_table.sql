-- Create job_categories table to store all job categories/industries
-- This allows easy management of categories without code changes

CREATE TABLE IF NOT EXISTS `job_categories` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL,
  `slug` VARCHAR(128) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT(11) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_slug` (`slug`),
  UNIQUE KEY `unique_name` (`name`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert all 50 major job categories
INSERT INTO `job_categories` (`name`, `slug`, `sort_order`) VALUES
('IT / Software', 'it-software', 1),
('Manufacturing', 'manufacturing', 2),
('Sales & Marketing', 'sales-marketing', 3),
('Finance & Accounting', 'finance-accounting', 4),
('Healthcare & Medical', 'healthcare-medical', 5),
('Education & Training', 'education-training', 6),
('Retail & E-commerce', 'retail-ecommerce', 7),
('Hospitality & Tourism', 'hospitality-tourism', 8),
('Construction & Real Estate', 'construction-real-estate', 9),
('Logistics & Supply Chain', 'logistics-supply-chain', 10),
('Banking & Financial Services', 'banking-financial-services', 11),
('Telecommunications', 'telecommunications', 12),
('Automotive', 'automotive', 13),
('Pharmaceuticals & Biotechnology', 'pharmaceuticals-biotechnology', 14),
('Food & Beverage', 'food-beverage', 15),
('Textiles & Apparel', 'textiles-apparel', 16),
('Energy & Power', 'energy-power', 17),
('Media & Entertainment', 'media-entertainment', 18),
('Aviation & Aerospace', 'aviation-aerospace', 19),
('Shipping & Maritime', 'shipping-maritime', 20),
('Agriculture & Farming', 'agriculture-farming', 21),
('Legal Services', 'legal-services', 22),
('Consulting', 'consulting', 23),
('Human Resources', 'human-resources', 24),
('Customer Service', 'customer-service', 25),
('Administrative & Clerical', 'administrative-clerical', 26),
('Engineering', 'engineering', 27),
('Design & Creative', 'design-creative', 28),
('Research & Development', 'research-development', 29),
('Quality Assurance', 'quality-assurance', 30),
('Project Management', 'project-management', 31),
('Operations', 'operations', 32),
('Procurement & Purchasing', 'procurement-purchasing', 33),
('Warehouse & Distribution', 'warehouse-distribution', 34),
('Security & Safety', 'security-safety', 35),
('Maintenance & Repair', 'maintenance-repair', 36),
('Beauty & Wellness', 'beauty-wellness', 37),
('Fitness & Sports', 'fitness-sports', 38),
('Event Management', 'event-management', 39),
('Non-Profit & NGO', 'non-profit-ngo', 40),
('Government & Public Sector', 'government-public-sector', 41),
('Insurance', 'insurance', 42),
('Real Estate', 'real-estate', 43),
('Travel & Tourism', 'travel-tourism', 44),
('Fashion & Apparel', 'fashion-apparel', 45),
('Gaming & Animation', 'gaming-animation', 46),
('Digital Marketing', 'digital-marketing', 47),
('Content Writing', 'content-writing', 48),
('Data Science & Analytics', 'data-science-analytics', 49),
('Cybersecurity', 'cybersecurity', 50)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `sort_order` = VALUES(`sort_order`);

-- Update jobs table to reference job_categories if needed (optional foreign key)
-- Note: This is optional - we'll keep category as VARCHAR for flexibility
-- ALTER TABLE `jobs` ADD CONSTRAINT `fk_job_category` FOREIGN KEY (`category`) REFERENCES `job_categories`(`name`) ON DELETE SET NULL ON UPDATE CASCADE;

