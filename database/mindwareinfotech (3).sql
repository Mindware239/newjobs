-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 07, 2026 at 08:59 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mindwareinfotech`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `actor_type` enum('employer','candidate','admin','system') NOT NULL,
  `actor_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` bigint(20) UNSIGNED DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_resume_suggestions`
--

CREATE TABLE `ai_resume_suggestions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `resume_id` bigint(20) UNSIGNED NOT NULL,
  `section_type` varchar(50) DEFAULT NULL,
  `suggestion_type` enum('summary','keyword','experience','skill','ats_optimization') NOT NULL,
  `suggestion_text` text NOT NULL,
  `score` int(11) DEFAULT 0 COMMENT 'Relevance score 0-100',
  `is_applied` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_id` bigint(20) UNSIGNED NOT NULL,
  `candidate_user_id` bigint(20) UNSIGNED NOT NULL,
  `resume_url` varchar(1024) DEFAULT NULL,
  `resume_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cover_letter` text DEFAULT NULL,
  `expected_salary` int(11) DEFAULT NULL,
  `status` enum('applied','screening','shortlisted','interview','offer','hired','rejected') DEFAULT 'applied',
  `score` float DEFAULT NULL,
  `source` enum('portal','email','referral','agency','import') DEFAULT 'portal',
  `applied_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `job_id`, `candidate_user_id`, `resume_url`, `resume_id`, `cover_letter`, `expected_salary`, `status`, `score`, `source`, `applied_at`, `updated_at`) VALUES
(11, 30, 10, 'http://localhost/mindinfotech/public/storage/uploads/candidates/5/692817b0638e9_sameer_Biswas_CV_2.pdf', NULL, '', 50000, 'applied', 10, 'portal', '2025-12-11 11:00:16', '2025-12-11 11:00:16'),
(12, 31, 35, '', NULL, '', NULL, 'shortlisted', 0, 'portal', '2025-12-13 13:52:35', '2026-01-05 17:04:37'),
(13, 32, 35, '', NULL, '', NULL, 'hired', 0, 'portal', '2025-12-13 17:34:27', '2026-01-07 11:59:08'),
(14, 34, 35, 'http://localhost:8000/storage/uploads/candidates/12/6943bde2dd149_Pd_Resume.pdf', NULL, '', 35000, 'applied', 15, 'portal', '2025-12-29 17:44:06', '2025-12-29 17:44:06'),
(15, 33, 35, 'http://localhost:8000/storage/uploads/candidates/12/6943bde2dd149_Pd_Resume.pdf', NULL, '', 35000, 'shortlisted', 40, 'portal', '2026-01-03 14:02:23', '2026-01-06 14:07:25'),
(16, 39, 40, '', NULL, '', NULL, 'applied', 0, 'portal', '2026-01-06 14:18:49', '2026-01-07 12:45:27');

-- --------------------------------------------------------

--
-- Table structure for table `application_events`
--

CREATE TABLE `application_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `actor_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `from_status` varchar(64) DEFAULT NULL,
  `to_status` varchar(64) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `application_events`
--

INSERT INTO `application_events` (`id`, `application_id`, `actor_user_id`, `from_status`, `to_status`, `comment`, `created_at`) VALUES
(52, 11, 10, NULL, 'applied', 'Application submitted', '2025-12-11 11:00:16'),
(53, 12, 35, NULL, 'applied', 'Application submitted', '2025-12-13 13:52:35'),
(54, 13, 35, NULL, 'applied', 'Application submitted', '2025-12-13 17:34:27'),
(55, 13, 3, 'applied', 'shortlisted', '', '2025-12-18 14:09:03'),
(56, 13, 3, 'interview', 'hired', '', '2025-12-20 13:42:48'),
(57, 13, 3, 'interview', 'rejected', '', '2025-12-20 13:43:49'),
(58, 12, 3, 'interview', 'shortlisted', '', '2025-12-20 13:51:57'),
(59, 12, 3, 'interview', 'shortlisted', '', '2025-12-20 14:13:01'),
(60, 13, 3, 'interview', 'shortlisted', '', '2025-12-20 14:13:29'),
(61, 12, 3, 'interview', 'shortlisted', '', '2025-12-20 14:13:30'),
(62, 13, 3, 'interview', 'shortlisted', '', '2025-12-20 14:13:31'),
(63, 13, 3, 'interview', 'shortlisted', '', '2025-12-20 14:13:33'),
(64, 12, 3, 'interview', 'shortlisted', '', '2025-12-20 14:13:34'),
(65, 13, 3, 'interview', 'rejected', '', '2025-12-20 14:13:51'),
(66, 13, 3, 'interview', 'shortlisted', '', '2025-12-20 14:13:57'),
(67, 12, 3, 'interview', 'shortlisted', '', '2025-12-20 14:14:00'),
(68, 12, 3, 'interview', 'hired', '', '2025-12-20 19:20:18'),
(69, 14, 35, NULL, 'applied', 'Application submitted', '2025-12-29 17:44:06'),
(70, 15, 35, NULL, 'applied', 'Application submitted', '2026-01-03 14:02:23'),
(71, 13, 3, 'interview', 'shortlisted', '', '2026-01-03 14:18:35'),
(72, 12, 3, 'interview', 'hired', '', '2026-01-03 17:05:59'),
(73, 13, 3, 'interview', 'rejected', '', '2026-01-03 17:13:54'),
(74, 13, 3, 'interview', 'rejected', '', '2026-01-03 17:14:26'),
(75, 15, 3, 'applied', 'shortlisted', '', '2026-01-05 10:59:32'),
(76, 13, 3, 'interview', 'shortlisted', '', '2026-01-05 10:59:37'),
(77, 12, 3, 'interview', 'shortlisted', '', '2026-01-05 10:59:39'),
(78, 15, 3, 'applied', 'shortlisted', '', '2026-01-05 10:59:51'),
(79, 13, 3, 'interview', 'shortlisted', '', '2026-01-05 11:09:27'),
(80, 15, 3, 'applied', 'shortlisted', '', '2026-01-05 11:09:33'),
(81, 12, 3, 'interview', 'shortlisted', '', '2026-01-05 11:09:36'),
(82, 15, 3, 'shortlisted', 'shortlisted', '', '2026-01-05 11:38:34'),
(83, 15, 3, 'shortlisted', 'shortlisted', '', '2026-01-05 11:38:35'),
(84, 15, 3, 'shortlisted', 'shortlisted', '', '2026-01-05 11:38:37'),
(85, 15, 3, 'shortlisted', 'rejected', '', '2026-01-05 11:38:43'),
(86, 15, 3, 'rejected', 'rejected', '', '2026-01-05 13:13:05'),
(87, 15, 3, 'rejected', 'rejected', '', '2026-01-05 13:13:06'),
(88, 15, 3, 'rejected', 'rejected', '', '2026-01-05 13:13:07'),
(89, 15, 3, 'rejected', 'rejected', '', '2026-01-05 13:13:08'),
(90, 12, 3, 'shortlisted', 'rejected', '', '2026-01-05 16:25:23'),
(91, 13, 3, 'interview', 'rejected', '', '2026-01-05 16:25:47'),
(92, 13, 3, 'rejected', 'rejected', '', '2026-01-05 16:25:51'),
(93, 12, 3, 'rejected', 'shortlisted', '', '2026-01-05 17:04:37'),
(94, 15, 3, 'rejected', 'shortlisted', '', '2026-01-06 14:07:25'),
(95, 15, 3, 'shortlisted', 'shortlisted', '', '2026-01-06 14:07:26'),
(96, 15, 3, 'shortlisted', 'shortlisted', '', '2026-01-06 14:07:27'),
(97, 15, 3, 'shortlisted', 'shortlisted', '', '2026-01-06 14:07:28'),
(98, 16, 40, NULL, 'applied', 'Application submitted', '2026-01-06 14:18:49'),
(99, 16, 36, 'applied', 'screening', '', '2026-01-06 14:28:53'),
(100, 12, 3, 'shortlisted', 'shortlisted', 'heloo', '2026-01-06 14:36:06'),
(101, 16, 36, 'screening', 'applied', '', '2026-01-06 14:37:18'),
(102, 16, 36, 'applied', 'interview', '', '2026-01-06 16:09:52'),
(103, 16, 36, 'interview', 'interview', '', '2026-01-06 16:09:54'),
(104, 15, 3, 'shortlisted', 'shortlisted', '', '2026-01-06 17:42:09'),
(105, 15, 3, 'shortlisted', 'shortlisted', '', '2026-01-06 17:42:11'),
(106, 13, 3, 'interview', 'rejected', '', '2026-01-06 17:47:51'),
(107, 13, 3, 'rejected', 'interview', '', '2026-01-07 11:01:11'),
(108, 13, 3, 'interview', 'hired', '', '2026-01-07 11:59:08'),
(109, 16, 36, 'interview', 'applied', '', '2026-01-07 12:45:27');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` varchar(64) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(128) DEFAULT NULL,
  `performed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `benefits`
--

CREATE TABLE `benefits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `benefits`
--

INSERT INTO `benefits` (`id`, `name`, `slug`, `category`, `created_at`) VALUES
(1, 'Work from home', 'work-from-home', 'Workplace', '2025-12-01 12:25:56'),
(2, 'Flexitime', 'flexitime', 'Workplace', '2025-12-01 12:25:56'),
(3, 'Company pension', 'company-pension', 'Financial', '2025-12-01 12:25:56'),
(4, 'Referral programme', 'referral-programme', 'Financial', '2025-12-01 12:25:56'),
(5, 'Employee discount', 'employee-discount', 'Perks', '2025-12-01 12:25:56'),
(7, 'On-site parking', 'on-site-parking', 'Facilities', '2025-12-01 12:25:56'),
(9, 'Free parking', 'free-parking', 'Facilities', '2025-12-01 12:25:56'),
(10, 'Subsidised travel', 'subsidised-travel', 'Financial', '2025-12-01 12:25:56'),
(11, 'Life insurance', 'life-insurance', 'Insurance', '2025-12-01 12:25:56'),
(12, 'Private medical insurance', 'private-medical-insurance', 'Insurance', '2025-12-01 12:25:56'),
(16, 'Sick pay', 'sick-pay', 'Leave', '2025-12-01 12:25:56'),
(17, 'Additional leave', 'additional-leave', 'Leave', '2025-12-01 12:25:56'),
(23, 'Enhanced maternity leave', 'enhanced-maternity-leave', 'Leave', '2025-12-01 12:25:56'),
(26, 'On-site gym', 'on-site-gym', 'Health', '2025-12-01 12:25:56'),
(28, 'Casual dress', 'casual-dress', 'Culture', '2025-12-01 12:25:56'),
(29, 'Free food', 'free-food', 'Perks', '2025-12-01 12:25:56'),
(30, 'Canteen', 'canteen', 'Facilities', '2025-12-01 12:25:56'),
(31, 'Company events', 'company-events', 'Culture', '2025-12-01 12:25:56'),
(32, 'Company car', 'company-car', 'Financial', '2025-12-01 12:25:56'),
(33, 'Transport links', 'transport-links', 'Facilities', '2025-12-01 12:25:56'),
(35, 'Relocation assistance', 'relocation-assistance', 'Financial', '2025-12-01 12:25:56'),
(36, 'Housing allowance', 'housing-allowance', 'Financial', '2025-12-01 12:25:56'),
(37, 'Profit sharing', 'profit-sharing', 'Financial', '2025-12-01 12:25:56'),
(40, 'UK visa sponsorship', 'uk-visa-sponsorship', 'Visa', '2025-12-01 12:25:56'),
(41, 'Language training provided', 'language-training', 'Training', '2025-12-01 12:25:56');

-- --------------------------------------------------------

--
-- Table structure for table `billing_profiles`
--

CREATE TABLE `billing_profiles` (
  `id` int(11) NOT NULL,
  `employer_id` int(11) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `billing_email` varchar(255) DEFAULT NULL,
  `gst_number` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `author_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(512) DEFAULT NULL,
  `status_id` tinyint(3) UNSIGNED DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `canonical_url` varchar(255) DEFAULT NULL,
  `view_count` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`id`, `author_id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `status_id`, `published_at`, `meta_title`, `meta_description`, `meta_keywords`, `canonical_url`, `view_count`, `is_featured`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 12, 'Mindware Infotech: A Global Technology Giant Transforming the Job Portal Ecosystem', 'mindware-infotech-a-global-technology-giant-transforming-the-job-portal-ecosystem', '', '<h2><strong>Introduction</strong></h2>\r\n\r\n<p>Mindware Infotech has emerged as a <strong>technology-driven global leader</strong> in the job portal and recruitment technology ecosystem. With a strong focus on innovation, scalability, and performance, Mindware Infotech is redefining how employers and job seekers connect across industries, locations, and skill levels.</p>\r\n\r\n<p>From enterprise hiring platforms to blue-collar workforce solutions, Mindware Infotech delivers <strong>end-to-end job portal solutions</strong> that power recruitment at scale across the world.</p>\r\n\r\n<p><a href=\"https://www.hirist.tech/blog/wp-content/uploads/2025/10/image-16-682x1024.png\">https://www.hirist.tech/blog/wp-content/uploads/2025/10/image-16-682x1024.png</a></p>\r\n\r\n<div style=\"background:#eeeeee;border:1px solid #cccccc;padding:5px 10px;\">Also Read -&nbsp;<a href=\"https://www.hirist.tech/blog/internship-meaning-for-students-freshers-application-for-internship/\" target=\"_blank\">Internship Meaning for Students &amp; Freshers: Application for Internship</a></div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<hr />\r\n<h2><strong>Who Is Mindware Infotech?</strong></h2>\r\n\r\n<p>Mindware Infotech is a <strong>technology-first company</strong> specializing in the design, development, and deployment of advanced job portal platforms. The company builds highly scalable systems used by:</p>\r\n\r\n<ul>\r\n	<li>\r\n	<p>Job portals</p>\r\n	</li>\r\n	<li>\r\n	<p>Recruitment agencies</p>\r\n	</li>\r\n	<li>\r\n	<p>Staffing companies</p>\r\n	</li>\r\n	<li>\r\n	<p>Corporate HR teams</p>\r\n	</li>\r\n	<li>\r\n	<p>Government employment programs</p>\r\n	</li>\r\n	<li>\r\n	<p>Blue-collar hiring platforms</p>\r\n	</li>\r\n</ul>\r\n\r\n<p>Mindware Infotech combines <strong>robust engineering</strong>, <strong>clean UI/UX</strong>, and <strong>SEO-ready architecture</strong> to deliver platforms that are fast, secure, and future-ready.</p>\r\n\r\n<hr />\r\n<h2><strong>A Global Job Portal Technology Leader</strong></h2>\r\n\r\n<p>Mindware Infotech solutions are built to support <strong>global hiring needs</strong>. Whether it is a local city-based hiring platform or a worldwide recruitment network, the architecture supports:</p>\r\n\r\n<ul>\r\n	<li>\r\n	<p>Multi-country job listings</p>\r\n	</li>\r\n	<li>\r\n	<p>Multi-language content</p>\r\n	</li>\r\n	<li>\r\n	<p>Location-based filtering</p>\r\n	</li>\r\n	<li>\r\n	<p>Currency and salary normalization</p>\r\n	</li>\r\n	<li>\r\n	<p>International employer onboarding</p>\r\n	</li>\r\n</ul>\r\n\r\n<p>The platform is designed to perform efficiently even with <strong>millions of job listings and users</strong>.</p>\r\n\r\n<hr />\r\n<h2><strong>Complete Job Portal Features</strong></h2>\r\n\r\n<p>Mindware Infotech job portals are fully loaded with modern recruitment features:</p>\r\n\r\n<h3><strong>For Job Seekers</strong></h3>\r\n\r\n<ul>\r\n	<li>\r\n	<p>Smart job search with filters</p>\r\n	</li>\r\n	<li>\r\n	<p>Location-based and category-based browsing</p>\r\n	</li>\r\n	<li>\r\n	<p>Resume upload and profile management</p>\r\n	</li>\r\n	<li>\r\n	<p>Job alerts and notifications</p>\r\n	</li>\r\n	<li>\r\n	<p>Saved jobs and application tracking</p>\r\n	</li>\r\n	<li>\r\n	<p>Mobile-friendly job browsing</p>\r\n	</li>\r\n</ul>\r\n\r\n<h3><strong>For Employers</strong></h3>\r\n\r\n<ul>\r\n	<li>\r\n	<p>Job posting and management dashboard</p>\r\n	</li>\r\n	<li>\r\n	<p>Applicant tracking system (ATS)</p>\r\n	</li>\r\n	<li>\r\n	<p>Resume shortlisting and downloads</p>\r\n	</li>\r\n	<li>\r\n	<p>Company profile management</p>\r\n	</li>\r\n	<li>\r\n	<p>Premium job listings</p>\r\n	</li>\r\n	<li>\r\n	<p>Analytics and performance reports</p>\r\n	</li>\r\n</ul>\r\n\r\n<hr />\r\n<h2><strong>Advanced Admin Panel</strong></h2>\r\n\r\n<p>Mindware Infotech provides a <strong>powerful admin panel</strong> that allows complete control over the job portal:</p>\r\n\r\n<ul>\r\n	<li>\r\n	<p>Job category management</p>\r\n	</li>\r\n	<li>\r\n	<p>Job type and employment type control</p>\r\n	</li>\r\n	<li>\r\n	<p>Blog and content management</p>\r\n	</li>\r\n	<li>\r\n	<p>SEO meta title, description, keywords</p>\r\n	</li>\r\n	<li>\r\n	<p>Slug and canonical URL control</p>\r\n	</li>\r\n	<li>\r\n	<p>User and employer moderation</p>\r\n	</li>\r\n	<li>\r\n	<p>Reports and analytics</p>\r\n	</li>\r\n	<li>\r\n	<p>Role-based admin access</p>\r\n	</li>\r\n</ul>\r\n\r\n<p>The admin system is designed for <strong>speed, security, and scalability</strong>.</p>\r\n\r\n<hr />\r\n<h2><strong>SEO-Optimized Architecture</strong></h2>\r\n\r\n<p>Search engine visibility is at the core of Mindware Infotech&rsquo;s job portal design. Every platform includes:</p>\r\n\r\n<ul>\r\n	<li>\r\n	<p>SEO-friendly URLs</p>\r\n	</li>\r\n	<li>\r\n	<p>Schema-ready job listings</p>\r\n	</li>\r\n	<li>\r\n	<p>Meta title and meta description support</p>\r\n	</li>\r\n	<li>\r\n	<p>Canonical URLs</p>\r\n	</li>\r\n	<li>\r\n	<p>Sitemap generation</p>\r\n	</li>\r\n	<li>\r\n	<p>Fast page load speed</p>\r\n	</li>\r\n	<li>\r\n	<p>Mobile-first indexing compatibility</p>\r\n	</li>\r\n</ul>\r\n\r\n<p>This ensures <strong>maximum organic traffic</strong> from search engines worldwide.</p>\r\n\r\n<hr />\r\n<h2><strong>Blue-Collar &amp; White-Collar Hiring Support</strong></h2>\r\n\r\n<p>Mindware Infotech platforms support <strong>all types of jobs</strong>, including:</p>\r\n\r\n<ul>\r\n	<li>\r\n	<p>IT &amp; Software</p>\r\n	</li>\r\n	<li>\r\n	<p>Manufacturing</p>\r\n	</li>\r\n	<li>\r\n	<p>Warehouse &amp; Logistics</p>\r\n	</li>\r\n	<li>\r\n	<p>Drivers &amp; Delivery</p>\r\n	</li>\r\n	<li>\r\n	<p>Electricians &amp; Technicians</p>\r\n	</li>\r\n	<li>\r\n	<p>Security Guards</p>\r\n	</li>\r\n	<li>\r\n	<p>Housekeeping &amp; Support Staff</p>\r\n	</li>\r\n	<li>\r\n	<p>Sales &amp; Marketing</p>\r\n	</li>\r\n	<li>\r\n	<p>Healthcare &amp; Education</p>\r\n	</li>\r\n</ul>\r\n\r\n<p>The system is flexible enough to manage <strong>skilled, semi-skilled, and unskilled workforce hiring</strong> seamlessly.</p>\r\n\r\n<hr />\r\n<h2><strong>Modern Technology Stack</strong></h2>\r\n\r\n<p>Mindware Infotech uses a <strong>modern, scalable technology stack</strong>, including:</p>\r\n\r\n<ul>\r\n	<li>\r\n	<p>PHP / Laravel based backend</p>\r\n	</li>\r\n	<li>\r\n	<p>MySQL / MariaDB databases</p>\r\n	</li>\r\n	<li>\r\n	<p>MVC architecture</p>\r\n	</li>\r\n	<li>\r\n	<p>REST APIs</p>\r\n	</li>\r\n	<li>\r\n	<p>Secure authentication systems</p>\r\n	</li>\r\n	<li>\r\n	<p>Mobile-friendly responsive design</p>\r\n	</li>\r\n</ul>\r\n\r\n<p>The platforms are built to handle <strong>high traffic, large datasets, and enterprise-level performance requirements</strong>.</p>\r\n\r\n<hr />\r\n<h2><strong>Security &amp; Performance</strong></h2>\r\n\r\n<p>Security is a top priority at Mindware Infotech. Job portals include:</p>\r\n\r\n<ul>\r\n	<li>\r\n	<p>Secure login and authentication</p>\r\n	</li>\r\n	<li>\r\n	<p>Role-based permissions</p>\r\n	</li>\r\n	<li>\r\n	<p>SQL injection protection</p>\r\n	</li>\r\n	<li>\r\n	<p>XSS and CSRF safeguards</p>\r\n	</li>\r\n	<li>\r\n	<p>Optimized database queries</p>\r\n	</li>\r\n	<li>\r\n	<p>Caching and performance tuning</p>\r\n	</li>\r\n</ul>\r\n\r\n<p>This ensures data safety for both job seekers and employers.</p>\r\n\r\n<hr />\r\n<h2><strong>Customizable &amp; Scalable Solutions</strong></h2>\r\n\r\n<p>Mindware Infotech understands that every job portal has unique requirements. That&rsquo;s why platforms are:</p>\r\n\r\n<ul>\r\n	<li>\r\n	<p>Fully customizable</p>\r\n	</li>\r\n	<li>\r\n	<p>Modular and extendable</p>\r\n	</li>\r\n	<li>\r\n	<p>Ready for third-party integrations</p>\r\n	</li>\r\n	<li>\r\n	<p>Scalable for future growth</p>\r\n	</li>\r\n</ul>\r\n\r\n<p>Whether launching a startup job portal or managing a large recruitment network, Mindware Infotech solutions grow with your business.</p>\r\n\r\n<hr />\r\n<h2><strong>Trusted by Businesses Worldwide</strong></h2>\r\n\r\n<p>Companies across multiple industries trust Mindware Infotech for reliable recruitment technology. The company&rsquo;s commitment to quality, performance, and innovation has made it a <strong>preferred technology partner</strong> in the global job portal industry.</p>\r\n\r\n<hr />\r\n<h2><strong>Why Choose Mindware Infotech?</strong></h2>\r\n\r\n<ul>\r\n	<li>\r\n	<p>Technology-driven approach</p>\r\n	</li>\r\n	<li>\r\n	<p>Global-ready job portal systems</p>\r\n	</li>\r\n	<li>\r\n	<p>SEO-focused architecture</p>\r\n	</li>\r\n	<li>\r\n	<p>Enterprise-grade admin panel</p>\r\n	</li>\r\n	<li>\r\n	<p>Support for all job categories</p>\r\n	</li>\r\n	<li>\r\n	<p>Scalable and secure platforms</p>\r\n	</li>\r\n</ul>\r\n\r\n<p>Mindware Infotech continues to push boundaries in recruitment technology, helping businesses hire smarter and job seekers find better opportunities.</p>\r\n\r\n<hr />\r\n<h2><strong>Conclusion</strong></h2>\r\n\r\n<p>Mindware Infotech is not just a job portal development company&mdash;it is a <strong>technology giant shaping the future of recruitment worldwide</strong>. With full-featured platforms, advanced admin controls, and SEO-optimized systems, Mindware Infotech delivers job portals that perform, scale, and succeed.</p>\r\n\r\n<p>If you are looking for a powerful, future-ready job portal solution, <strong>Mindware Infotech stands at the forefront of innovation</strong>.</p>\r\n', 'http://localhost:8000/storage/uploads/blog/69410a1cb3cda_resume-format-for-freshers.jpg', 1, '2025-12-16 07:31:00', 'Mindware infotech job portal', 'Best job portal Mindware infotech job portal', 'Mindware infotech job portal, Mindware infotech', '', 0, 0, 0, '2025-12-16 12:58:28', '2025-12-16 13:04:45'),
(2, 12, 'test', 'test', 'fdgfd', '<p>dfhgdhghfgh</p>\r\n', 'http://localhost:8000/storage/uploads/blog/6944f0f1b014b_deploy.svg', 1, '2025-12-19 06:29:41', 'fgj', 'fgj', 'ghjhg', '', 0, 0, 0, '2025-12-19 11:58:44', '2025-12-19 12:00:09');

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_categories`
--

INSERT INTO `blog_categories` (`id`, `name`, `slug`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Career Advice', 'career-advice', 'Career Advice features expert tips and practical guidance on job search, interviews, resumes, skill development, and career growth, helping job seekers make smarter career decisions and succeed professionally.', 1, '2025-12-16 12:52:52', '2025-12-16 12:52:52'),
(2, 'DevOps', 'devops', '', 1, '2025-12-19 11:56:05', '2025-12-19 11:56:05'),
(3, 'Data Science', 'data-science', '', 1, '2025-12-19 11:57:22', '2025-12-19 11:57:22');

-- --------------------------------------------------------

--
-- Table structure for table `blog_category_map`
--

CREATE TABLE `blog_category_map` (
  `blog_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_category_map`
--

INSERT INTO `blog_category_map` (`blog_id`, `category_id`) VALUES
(1, 1),
(2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `blog_tags`
--

CREATE TABLE `blog_tags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_tags`
--

INSERT INTO `blog_tags` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(2, 'Career', 'career-advice', '2025-12-16 12:55:32', '2025-12-16 12:59:58'),
(3, 'Mindware Infotech', 'mindware-infotech', '2025-12-16 13:00:24', '2025-12-16 13:00:24');

-- --------------------------------------------------------

--
-- Table structure for table `blog_tag_map`
--

CREATE TABLE `blog_tag_map` (
  `blog_id` bigint(20) UNSIGNED NOT NULL,
  `tag_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_tag_map`
--

INSERT INTO `blog_tag_map` (`blog_id`, `tag_id`) VALUES
(1, 2),
(2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `call_logs`
--

CREATE TABLE `call_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `candidate_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `initiated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `call_start` datetime DEFAULT NULL,
  `call_end` datetime DEFAULT NULL,
  `call_status` enum('completed','missed','failed') DEFAULT 'failed',
  `provider` varchar(128) DEFAULT NULL,
  `recording_url` varchar(1024) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') DEFAULT NULL,
  `mobile` varchar(32) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(512) DEFAULT NULL,
  `resume_url` varchar(512) DEFAULT NULL,
  `video_intro_url` varchar(512) DEFAULT NULL,
  `video_intro_type` enum('upload','youtube') DEFAULT NULL,
  `self_introduction` text DEFAULT NULL,
  `expected_salary_min` int(11) DEFAULT NULL,
  `expected_salary_max` int(11) DEFAULT NULL,
  `current_salary` int(11) DEFAULT NULL,
  `notice_period` int(11) DEFAULT NULL COMMENT 'Days',
  `preferred_job_location` varchar(255) DEFAULT NULL,
  `portfolio_url` varchar(512) DEFAULT NULL,
  `linkedin_url` varchar(512) DEFAULT NULL,
  `github_url` varchar(512) DEFAULT NULL,
  `website_url` varchar(512) DEFAULT NULL,
  `profile_strength` int(11) DEFAULT 0 COMMENT 'Percentage 0-100',
  `is_profile_complete` tinyint(1) DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 0,
  `is_premium` tinyint(1) DEFAULT 0,
  `premium_expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `education_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Stores education records as JSON array' CHECK (json_valid(`education_data`)),
  `experience_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Stores experience records as JSON array' CHECK (json_valid(`experience_data`)),
  `skills_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Stores skills as JSON array' CHECK (json_valid(`skills_data`)),
  `languages_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Stores languages as JSON array' CHECK (json_valid(`languages_data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `user_id`, `full_name`, `dob`, `gender`, `mobile`, `city`, `state`, `country`, `profile_picture`, `resume_url`, `video_intro_url`, `video_intro_type`, `self_introduction`, `expected_salary_min`, `expected_salary_max`, `current_salary`, `notice_period`, `preferred_job_location`, `portfolio_url`, `linkedin_url`, `github_url`, `website_url`, `profile_strength`, `is_profile_complete`, `is_verified`, `is_premium`, `premium_expires_at`, `created_at`, `updated_at`, `education_data`, `experience_data`, `skills_data`, `languages_data`) VALUES
(4, 9, 'Mindware Infotech', NULL, NULL, NULL, NULL, NULL, NULL, 'https://lh3.googleusercontent.com/a/ACg8ocKKrySUOjmxRFhh7Ttwm5m_Z823lRsYOwRfz2WWyvrc9v1-mA=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', 18, 0, 0, 0, NULL, '2025-11-27 11:55:06', '2025-12-19 10:59:30', '[]', '[]', '[{\"skill_id\":null,\"name\":\"Pharma\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":63,\"name\":\"medicine knowledge\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]', NULL),
(5, 10, 'Tags India', '1997-01-01', 'male', '9988774455', 'Dwarka', 'Delhi', 'India', 'http://localhost/mindinfotech/public/storage/uploads/candidates/5/6928178ac17d7_electrician.png', 'http://localhost/mindinfotech/public/storage/uploads/candidates/5/692817b0638e9_sameer_Biswas_CV_2.pdf', NULL, NULL, 'Experienced software developer with expertise in PHP, JavaScript, and web development.', 50000, 80000, 60000, 30, 'Delhi, Noida, Gurgaon', 'https://portfolio.example.com', 'https://linkedin.com/in/tagsindia', 'https://github.com/tagsindia', 'https://tagsindia.com', 100, 1, 0, 0, NULL, '2025-11-27 11:57:57', '2025-12-19 10:59:10', '[{\"degree\": \"Bachelor of Science\", \"field_of_study\": \"Computer Science\", \"institution\": \"Delhi University\", \"start_date\": \"2015-07-01\", \"end_date\": \"2019-06-30\", \"is_current\": 0, \"grade\": \"A\", \"description\": \"Graduated with honors in Computer Science\"}, {\"degree\": \"Master of Science\", \"field_of_study\": \"Software Engineering\", \"institution\": \"IIT Delhi\", \"start_date\": \"2019-07-01\", \"end_date\": \"2021-06-30\", \"is_current\": 0, \"grade\": \"A+\", \"description\": \"Specialized in web development and software architecture\"}]', '[{\"job_title\": \"Senior Software Developer\", \"company_name\": \"Tech Solutions Pvt Ltd\", \"start_date\": \"2021-07-01\", \"end_date\": null, \"is_current\": 1, \"description\": \"Leading development team, building scalable web applications using PHP, JavaScript, and modern frameworks.\", \"location\": \"Delhi\"}, {\"job_title\": \"Software Developer\", \"company_name\": \"Web Innovations Inc\", \"start_date\": \"2019-08-01\", \"end_date\": \"2021-06-30\", \"is_current\": 0, \"description\": \"Developed and maintained web applications, worked with REST APIs, and collaborated with cross-functional teams.\", \"location\": \"Noida\"}]', '[{\"skill_id\": null, \"name\": \"PHP\", \"proficiency_level\": \"advanced\", \"years_of_experience\": 5}, {\"skill_id\": null, \"name\": \"JavaScript\", \"proficiency_level\": \"advanced\", \"years_of_experience\": 4}, {\"skill_id\": null, \"name\": \"MySQL\", \"proficiency_level\": \"intermediate\", \"years_of_experience\": 4}, {\"skill_id\": null, \"name\": \"Laravel\", \"proficiency_level\": \"advanced\", \"years_of_experience\": 3}, {\"skill_id\": null, \"name\": \"React\", \"proficiency_level\": \"intermediate\", \"years_of_experience\": 2}, {\"skill_id\": null, \"name\": \"Node.js\", \"proficiency_level\": \"intermediate\", \"years_of_experience\": 2}]', '[{\"language\": \"English\", \"proficiency\": \"fluent\"}, {\"language\": \"Hindi\", \"proficiency\": \"native\"}, {\"language\": \"Punjabi\", \"proficiency\": \"conversational\"}]'),
(12, 35, 'Prabhat Paswan', '2025-12-13', 'male', '9910112688', 'Dwarka ', 'Delhi', 'India', 'http://localhost:8000/storage/uploads/candidates/12/694244df2e081_Mindware-infotech.png', 'http://localhost:8000/storage/uploads/candidates/12/6943bde2dd149_Pd_Resume.pdf', NULL, NULL, NULL, 35000, 45000, 20000, 7, 'Mumbai, Pune, Noida', '', 'https://linkedin.com', '', 'www.pkrtechvision.com', 91, 1, 0, 1, '2026-01-08 05:13:57', '2025-12-13 12:11:35', '2026-01-01 10:43:57', '[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]', '[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]', '[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]', NULL),
(14, 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL, '2026-01-06 14:17:36', '2026-01-06 14:17:36', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `candidate_education`
--

CREATE TABLE `candidate_education` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `candidate_id` bigint(20) UNSIGNED NOT NULL,
  `degree` varchar(255) NOT NULL,
  `field_of_study` varchar(255) DEFAULT NULL,
  `institution` varchar(255) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `grade` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `candidate_experience`
--

CREATE TABLE `candidate_experience` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `candidate_id` bigint(20) UNSIGNED NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `candidate_interest`
--

CREATE TABLE `candidate_interest` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `candidate_id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `job_id` bigint(20) UNSIGNED DEFAULT NULL,
  `interest_level` enum('viewed','applied','shortlisted','high_interest') DEFAULT 'viewed',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidate_interest`
--

INSERT INTO `candidate_interest` (`id`, `candidate_id`, `employer_id`, `job_id`, `interest_level`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 12, 10, NULL, 'high_interest', NULL, '2025-12-19 12:50:19', '2025-12-19 12:50:19'),
(2, 12, 10, NULL, 'high_interest', NULL, '2025-12-19 12:51:50', '2025-12-19 12:51:50'),
(3, 12, 10, NULL, 'high_interest', NULL, '2025-12-19 12:53:19', '2025-12-19 12:53:19'),
(4, 12, 2, NULL, 'high_interest', NULL, '2025-12-19 17:12:00', '2025-12-19 17:12:00');

-- --------------------------------------------------------

--
-- Table structure for table `candidate_job_scores`
--

CREATE TABLE `candidate_job_scores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `candidate_id` bigint(20) UNSIGNED NOT NULL COMMENT 'References candidates.id',
  `job_id` bigint(20) UNSIGNED NOT NULL COMMENT 'References jobs.id',
  `overall_match_score` int(11) NOT NULL DEFAULT 0 COMMENT 'Overall match score 0-100',
  `skill_score` int(11) NOT NULL DEFAULT 0 COMMENT 'Skill match score 0-100',
  `experience_score` int(11) NOT NULL DEFAULT 0 COMMENT 'Experience match score 0-100',
  `education_score` int(11) NOT NULL DEFAULT 0 COMMENT 'Education match score 0-100',
  `matched_skills` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of matched skill names' CHECK (json_valid(`matched_skills`)),
  `missing_skills` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of missing/required skills' CHECK (json_valid(`missing_skills`)),
  `extra_relevant_skills` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of candidate skills not in job but relevant' CHECK (json_valid(`extra_relevant_skills`)),
  `summary` text DEFAULT NULL COMMENT 'AI-generated recruiter summary (1-3 lines)',
  `recommendation` enum('Reject','Review','Shortlist','Strong Hire') DEFAULT NULL COMMENT 'AI recommendation',
  `ai_parsed_at` datetime DEFAULT NULL COMMENT 'When AI scoring was last performed',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AI-generated match scores between candidates and jobs';

--
-- Dumping data for table `candidate_job_scores`
--

INSERT INTO `candidate_job_scores` (`id`, `candidate_id`, `job_id`, `overall_match_score`, `skill_score`, `experience_score`, `education_score`, `matched_skills`, `missing_skills`, `extra_relevant_skills`, `summary`, `recommendation`, `ai_parsed_at`, `created_at`, `updated_at`) VALUES
(27, 5, 30, 47, 0, 85, 80, '[]', '[\"dgfgfd\",\"fdhfdh\",\"hfdghgfh\",\"ghgf\",\"hgfdh\",\"gfj\",\"fj\",\"hfg\"]', '[\"Php\",\"Javascript\",\"Mysql\",\"Laravel\",\"React\"]', 'Low match. Candidate has 0 of 8 required skills. Missing: dgfgfd, fdhfdh, hfdghgfh', 'Reject', NULL, '2025-12-11 11:00:27', '2025-12-11 11:00:29'),
(29, 12, 31, 33, 0, 50, 80, '[]', '[\"Ability to operate and maintain 3-wheeler vehicles\",\"Basic mechanical knowledge for minor repairs and troubleshooting\",\"Ability to use GPS, route maps, and navigation tools\",\"Handling invoices, delivery slips, tags, and documentation\"]', '[\"Html\",\"Css\",\"Node.js\"]', 'Low match. Candidate has 0 of 4 required skills. Missing: Ability to operate and maintain 3-wheeler vehicles, Basic mechanical knowledge for minor repairs and troubleshooting, Ability to use GPS, route maps, and navigation tools', 'Reject', NULL, '2025-12-13 17:18:34', '2025-12-20 13:52:30'),
(30, 12, 32, 63, 80, 50, 80, '[\"html\",\"Tailwind css\",\"CSS\",\"js\"]', '[\"php\"]', '[]', 'Excellent match! Candidate has 4 of 5 required skills.', 'Review', NULL, '2025-12-13 17:57:01', '2026-01-03 16:54:19'),
(36, 12, 33, 38, 0, 50, 80, '[]', '[\"good communication\",\"Medical care\",\"medicine knowledge\",\"patient care\"]', '[\"Html\",\"Css\",\"Node.js\"]', 'Low match. Candidate has 0 of 4 required skills. Missing: good communication, Medical care, medicine knowledge', 'Reject', NULL, '2026-01-03 14:11:16', '2026-01-07 12:23:33'),
(43, 14, 39, 30, 0, 50, 50, '[]', '[]', '[]', 'No skill requirements specified for this job.', 'Reject', NULL, '2026-01-06 14:18:57', '2026-01-06 16:10:22');

-- --------------------------------------------------------

--
-- Table structure for table `candidate_languages`
--

CREATE TABLE `candidate_languages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `candidate_id` bigint(20) UNSIGNED NOT NULL,
  `language` varchar(100) NOT NULL,
  `proficiency` enum('basic','conversational','fluent','native') DEFAULT 'conversational',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `candidate_premium_purchases`
--

CREATE TABLE `candidate_premium_purchases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `candidate_id` bigint(20) UNSIGNED NOT NULL,
  `plan_type` enum('boost_7days','boost_30days','premium_monthly','premium_yearly') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('razorpay','stripe','paypal') NOT NULL,
  `payment_id` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `candidate_premium_purchases`
--

INSERT INTO `candidate_premium_purchases` (`id`, `candidate_id`, `plan_type`, `amount`, `payment_method`, `payment_id`, `status`, `expires_at`, `created_at`) VALUES
(1, 12, 'boost_7days', 299.00, 'razorpay', NULL, 'pending', NULL, '2025-12-18 16:24:20'),
(2, 12, 'boost_7days', 299.00, 'razorpay', NULL, 'pending', NULL, '2025-12-18 16:25:51'),
(3, 12, 'boost_7days', 299.00, 'razorpay', NULL, 'pending', NULL, '2025-12-18 16:26:03'),
(4, 12, 'boost_7days', 1.00, 'razorpay', NULL, 'pending', NULL, '2025-12-18 16:33:36'),
(39, 12, 'boost_7days', 1.00, 'razorpay', 'pay_RyUkPo82jQYN6A', 'completed', NULL, '2026-01-01 10:43:29');

-- --------------------------------------------------------

--
-- Table structure for table `candidate_quality_scores`
--

CREATE TABLE `candidate_quality_scores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `resume_completeness_score` decimal(5,2) DEFAULT 0.00,
  `skill_match_percentage` decimal(5,2) DEFAULT 0.00,
  `interview_score` decimal(5,2) DEFAULT NULL,
  `employer_rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `employer_feedback` text DEFAULT NULL,
  `overall_score` decimal(5,2) DEFAULT 0.00,
  `calculated_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `candidate_skills`
--

CREATE TABLE `candidate_skills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `candidate_id` bigint(20) UNSIGNED NOT NULL,
  `skill_id` bigint(20) UNSIGNED NOT NULL,
  `proficiency_level` enum('beginner','intermediate','advanced','expert') DEFAULT 'intermediate',
  `years_of_experience` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `state_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `state_id`, `name`, `slug`, `is_featured`, `created_at`) VALUES
(1, 1, 'Noida', 'noida', 1, '2025-12-30 13:20:33'),
(2, 1, 'Ghaziabad', 'ghaziabad', 0, '2025-12-30 13:20:33'),
(3, 2, 'New Delhi', 'new-delhi', 1, '2025-12-30 13:20:33'),
(4, 3, 'Bangalore', 'bangalore', 1, '2025-12-30 13:20:33'),
(5, 4, 'Moscow', 'moscow', 0, '2025-12-30 13:33:53'),
(6, 1, 'Agra', 'agra', 0, '2025-12-30 13:33:53'),
(7, 5, 'Cochin', 'cochin', 0, '2025-12-30 13:33:53'),
(8, 1, 'Gorakhpur', 'gorakhpur', 0, '2025-12-30 13:33:53'),
(9, 1, 'Gohānd', 'goh-nd', 0, '2025-12-30 13:33:53'),
(10, 6, 'Dalar', 'dalar', 0, '2025-12-30 13:33:53');

-- --------------------------------------------------------

--
-- Table structure for table `communication_logs`
--

CREATE TABLE `communication_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `candidate_id` bigint(20) UNSIGNED DEFAULT NULL,
  `application_id` bigint(20) UNSIGNED DEFAULT NULL,
  `communication_type` enum('message','email','sms','whatsapp','call','interview_invite') NOT NULL,
  `direction` enum('sent','received') NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `status` enum('sent','delivered','read','failed') DEFAULT 'sent',
  `response_time_seconds` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `delivered_at` datetime DEFAULT NULL,
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(10) UNSIGNED NOT NULL,
  `employer_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_name` varchar(100) DEFAULT NULL,
  `website` varchar(512) DEFAULT NULL,
  `industry` varchar(255) DEFAULT NULL,
  `headquarters` varchar(255) DEFAULT NULL,
  `founded_year` smallint(5) UNSIGNED DEFAULT NULL,
  `company_size` varchar(100) DEFAULT NULL,
  `revenue` varchar(100) DEFAULT NULL,
  `ceo_name` varchar(255) DEFAULT NULL,
  `ceo_photo` varchar(512) DEFAULT NULL,
  `logo_url` varchar(512) DEFAULT NULL,
  `banner_url` varchar(512) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `views` int(10) UNSIGNED DEFAULT 0,
  `jobs_count` int(10) UNSIGNED DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `featured_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `employer_id`, `name`, `slug`, `short_name`, `website`, `industry`, `headquarters`, `founded_year`, `company_size`, `revenue`, `ceo_name`, `ceo_photo`, `logo_url`, `banner_url`, `description`, `views`, `jobs_count`, `created_at`, `updated_at`, `is_featured`, `featured_order`) VALUES
(1, 1, 'Mindware Infotech', 'mindware', 'Mindware Infotech', 'www.mindwareinfotech.com', 'Software', 'New Delhi', 1997, '100-250', '10cr', 'Gulshan Marwah', 'https://i0.wp.com/www.gulshanmarwah.com/wp-content/uploads/2024/12/gulshan-sir-pic.jpg?w=225&ssl=1', 'https://i0.wp.com/www.gulshanmarwah.com/wp-content/uploads/2023/09/cropped-mindware-bg-remove-logo.png?resize=80%2C81&ssl=1', 'https://images.unsplash.com/photo-1549731816-4176e705b9a4?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D', 'Mindware Infotech is a leading IT solutions company offering advanced software development, automation tools, and digital services for enterprises. Our mission is to empower businesses with smart, secure, and future-ready technology.', 0, 10, '2025-12-01 13:10:38', '2026-01-01 14:26:19', 1, 5),
(2, NULL, 'TSC Printers India', 'tsc-printers', NULL, 'www.tscprintersindia.com', 'Enterprize', 'New Delhi', 1997, '10-20', '10cr', 'Gulshan Marwah', NULL, NULL, NULL, NULL, 0, 0, '2025-12-02 10:52:17', '2026-01-01 14:38:35', 1, 7),
(4, NULL, 'TSC Printers India', 'tscprinters', NULL, 'www.tscprintersindia.com', 'Enterprize', 'New Delhi', 1997, '10-20', '10cr', 'Gulshan Marwah', NULL, NULL, NULL, NULL, 0, 0, '2025-12-02 10:52:48', '2026-01-01 14:26:19', 1, 4),
(5, 3, 'Mindware India', 'mindware-india', 'Mindware India', NULL, NULL, 'delhi', 2000, '11-50', '12cr', 'Gulshan marwah', NULL, 'http://localhost/mindinfotech/public/storage/uploads/companies/3/6931516e508a4_photo-1584697964156-deca98e4439d.avif', 'http://localhost/mindinfotech/public/storage/uploads/companies/3/6931516e510bb_Gemini_Generated_Image_veu972veu972veu9.png', '{\"about\":\"\",\"why_points\":[\"great work life balance\",\"great work life balance\",\"great work life balance\",\"great work life balance\"]}', 0, 0, '2025-12-04 14:46:30', '2026-01-01 14:32:31', 1, 6),
(6, 2, 'Indian Barcode Corporation', 'indian-barcode-corporation', 'Indian Barcode Corporation', 'www.indianbarcodecorporation.com', NULL, 'New Delhi India', 1997, '11-50', '7 Crore', 'Gulshan Marwah', 'http://localhost:8000/storage/uploads/companies/ceo/694cf2a2e9f66_gulshan-sir-pic.webp', 'http://localhost:8000/storage/uploads/companies/logos/694cca4a80c44_mindware-logo.png', 'http://localhost:8000/storage/uploads/companies/banners/694cca4a82008_Gemini_Generated_Image_2eg75q2eg75q2eg7.png', '{\"about\":\"\",\"why_points\":[]}', 0, 0, '2025-12-24 18:34:55', '2026-01-01 14:08:00', 1, 3),
(7, 10, 'Barcode Vault', 'barcode-vault', 'Barcode Vault', 'https://www.barcodevault.com/', 'Manufacturing', NULL, NULL, '51-200', NULL, NULL, NULL, 'http://localhost:8000/storage/uploads/employers/10/69429027d27db_barcode-vault-logo.webp', NULL, 'We are Asia\'s biggest manufacturrers and suppliers in INDIA.', 0, 0, '2025-12-25 10:46:21', '2026-01-01 14:06:09', 1, 1),
(8, 11, 'PKR Techvision', 'pkr-techvision', 'PKR Techvision', 'https://pkrtechvision.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-01-01 14:07:29', '2026-01-01 14:07:37', 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `company_blogs`
--

CREATE TABLE `company_blogs` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `views` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_blogs`
--

INSERT INTO `company_blogs` (`id`, `company_id`, `title`, `slug`, `excerpt`, `content`, `image`, `status`, `views`, `created_at`, `updated_at`) VALUES
(1, 6, 'test', 'test', 'jf', 'cbcv j jghj kghkhg khg khgk hg lkhgflfjgljhl jhljhljhlhlf prabhta paswan , i have almost 3 years of experience in software developmenet company . skilled in laravle , php javascript and', '/uploads/company-blogs/694cdec921a9b.jpg', 'published', 0, '2025-12-25 12:20:49', '2025-12-25 12:20:49');

-- --------------------------------------------------------

--
-- Table structure for table `company_followers`
--

CREATE TABLE `company_followers` (
  `id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_stats`
--

CREATE TABLE `company_stats` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `rating` decimal(3,2) DEFAULT 0.00,
  `reviews_count` int(11) DEFAULT 0,
  `salaries_count` int(11) DEFAULT 0,
  `interviews_count` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `candidate_user_id` bigint(20) UNSIGNED NOT NULL,
  `last_message_id` bigint(20) UNSIGNED DEFAULT NULL,
  `unread_employer` int(11) DEFAULT 0,
  `unread_candidate` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `employer_id`, `candidate_user_id`, `last_message_id`, `unread_employer`, `unread_candidate`, `created_at`, `updated_at`) VALUES
(2, 2, 10, 112, 0, 4, '2025-11-28 17:20:57', '2025-12-20 17:00:43'),
(5, 10, 10, 75, 0, 0, '2025-12-06 17:21:19', '2025-12-11 11:01:56'),
(6, 2, 35, 142, 0, 12, '2025-12-13 14:21:32', '2026-01-07 11:59:10'),
(7, 10, 35, 119, 3, 0, '2025-12-29 17:44:21', '2025-12-31 15:33:44'),
(8, 11, 40, 144, 0, 3, '2026-01-06 14:28:54', '2026-01-07 12:45:28'),
(9, 11, 9, 135, 0, 1, '2026-01-06 16:10:38', '2026-01-06 16:10:38');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'India', 'india', '2025-12-30 13:20:33'),
(2, 'Russia', 'russia', '2025-12-30 13:33:53'),
(3, 'Armenia', 'armenia', '2025-12-30 13:33:53');

-- --------------------------------------------------------

--
-- Table structure for table `data_export_logs`
--

CREATE TABLE `data_export_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `export_type` varchar(50) NOT NULL,
  `format` enum('csv','pdf','excel') NOT NULL,
  `file_path` varchar(512) DEFAULT NULL,
  `record_count` int(10) UNSIGNED DEFAULT 0,
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`filters`)),
  `exported_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discount_codes`
--

CREATE TABLE `discount_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(64) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `discount_type` enum('percentage','fixed_amount') DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `min_amount` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `valid_from` datetime NOT NULL,
  `valid_until` datetime NOT NULL,
  `max_uses` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `max_uses_per_user` int(11) DEFAULT 1,
  `applicable_plans` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applicable_plans`)),
  `applicable_billing_cycles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applicable_billing_cycles`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_ocr_results`
--

CREATE TABLE `document_ocr_results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document_id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `extracted_name` varchar(255) DEFAULT NULL,
  `extracted_gst` varchar(25) DEFAULT NULL,
  `extracted_cin` varchar(25) DEFAULT NULL,
  `extracted_address` text DEFAULT NULL,
  `extracted_registration_date` date DEFAULT NULL,
  `confidence_score` decimal(5,2) DEFAULT 0.00,
  `raw_text` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employers`
--

CREATE TABLE `employers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `company_slug` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo_url` varchar(512) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `industry` varchar(128) DEFAULT NULL,
  `size` enum('1-10','11-50','51-200','201-500','501-1000','1001+') DEFAULT NULL,
  `address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`address`)),
  `country` varchar(64) DEFAULT NULL,
  `state` varchar(128) DEFAULT NULL,
  `city` varchar(128) DEFAULT NULL,
  `postal_code` varchar(32) DEFAULT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `kyc_status` enum('not_submitted','pending','approved','rejected') DEFAULT 'not_submitted',
  `kyc_assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `kyc_level` enum('basic','full') DEFAULT 'basic',
  `kyc_rejection_reason` text DEFAULT NULL,
  `kyc_escalated` tinyint(1) DEFAULT 0,
  `kyc_escalation_reason` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employers`
--

INSERT INTO `employers` (`id`, `user_id`, `company_name`, `company_slug`, `website`, `logo_url`, `description`, `industry`, `size`, `address`, `country`, `state`, `city`, `postal_code`, `verified`, `kyc_status`, `kyc_assigned_to`, `kyc_level`, `kyc_rejection_reason`, `kyc_escalated`, `kyc_escalation_reason`, `created_at`, `updated_at`) VALUES
(2, 3, 'Indian Barcode Corporation', 'indian-barcode-corporation', 'https://www.mindwaretechnologies.com', 'http://localhost:8000/storage/uploads/employers/2/694e8975dbafa_mindware-logo.png', 'MINDWARE INDIA Established in the year 1997, in New Delhi, India. It has been an industry leader in innovative Packaging Materials & Security Labeling Solutions, delivering a portfolio of various products and services focused on specific markets and customer requirements. MINDWARE INDIA  is a pioneer in barcode registration for India, We make labels, tags, and packing material for pharma companies, automotive companies, beverage labels \r\n\r\nWe manufacture and supply of extensive variety of Packaging Materials & Security Labels such as Pallet boxes, Cardboard boxes, Paper Box, Medical & Pharmaceutical Aluminum Foil, Catch Covers, Packing-Slips, Packing-Slips, Form-Label-Combinations, Form-Label-Combinations, Box-Shape-Catch cover, Flap-Shape-Catch-covers, Topical-Medication-Cover, Packing-Slips, Self Adhesive Tapes, Shrink-wrap Roll, Labels and Security solutions Hospital-ID-Bands, Hospital-ID-Bracelets, Medical-ID-Bands, Patient-ID-Single-Bands, Patient Wristband, Amusement Wristband, Tyvek Wristband, RFID Wristband, PVC Cards, Printed Card, Smart Card, RFID Card, we supply these to clients in various shapes, sizes, and colors with best quality and service.\r\n\r\nMINDWARE INDIA has expertise in label manufacturing, preprinted labels, thermal ribbons, Tags, Stickers, and Ribbons, We offer Zebra Labels and Ribbons, Argox Labels and Ribbons, Printronix Labels and Ribbons, Brother Labels and Ribbons, Godex Labels and Ribbons, Sato Labels and Ribbons, Toshiba Labels and Ribbons, TSC Labels and Ribbons, Citizen Labels and Ribbons, Datamax Labels and Ribbons, Dymo Labels and Ribbons, Avery Dennison ( AD ) Labels and Ribbons. We have Different varient of jewelry labels, void, temper evident, the non-void, removable, and ungummed jewelry labels, and YMCK Ribbons for thermal card Printers, (Zebra card printers, Fargo Card printers, evolis card printers )DMP Ribbons, Thermal Ribbons, Colored Ribbons. We also offer RFID Tags and Solutions for various Industries. Over more than 19 years, the company has worked extremely hard with dedication and reliability. Owing to the quality range, we are widely catering to the needs of various export markets, such as Africa, Australia, Canada, USA, and Europe.\r\n\r\nUnder the able and proficient leadership of our owner Mr. Gulshan Marwah, who has 27 years of experience in this field, we have become one of the distinguished names in the field of designing and making of labels of labels. His business ethics and approaches enable us to cater to the demands and requirements of our valued clients in a better way. Our state-of-the-art infrastructure facility is equipped with advanced technology to help us manufacture premium quality Packaging materials & Security labels. Our infrastructure facility comprises several important units such as production, packaging, quality control, and many more. Our quality-compliant processes and ethical business policies have earned us the trust and respect of our clients. Our employees are highly skilled in their respective areas of operation. We have positioned ourselves as one of the leading companies dealing with manufacturing and supplying Packaging Material & Security Labels solutions. We ensure that all our business dealings are carried out ethically and transparently. Our dedication to quality and ethics has made us a highly preferable organization for clients to deal with.', 'IT/Software', '11-50', '{\"state\":\"Delhi\",\"city\":\"Dwarka\",\"postal_code\":\"110078\",\"street\":\"\"}', 'India', 'Delhi', 'Dwarka', '110078', 1, 'approved', 26, 'basic', NULL, 0, NULL, '2025-11-24 16:41:03', '2025-12-26 18:41:21'),
(10, 30, 'Barcode Vault', 'barcode-vault', 'https://www.barcodevault.com/', 'http://localhost:8000/storage/uploads/employers/10/69429027d27db_barcode-vault-logo.webp', 'We are Asia\'s biggest manufacturrers and suppliers in INDIA.', 'Manufacturing', '51-200', '{\"state\":\"Uttar Pradesh\",\"city\":\"Noida\",\"postal_code\":\"201310\",\"street\":\"\"}', 'India', 'Uttar Pradesh', 'Noida', '201310', 1, 'approved', 26, 'basic', NULL, 0, NULL, '2025-12-05 16:36:23', '2025-12-17 16:42:39'),
(11, 36, 'PKR Techvision', 'pkr-techvision', 'https://pkrtechvision.com', NULL, 'PKR Techvision is a global edu tech company', 'Education', '11-50', '{\"street\":\"Pankaj Plaza, Pocket-7, Plot-7, Sector-12, Dwarka , New Delhi, India 110078\",\"city\":\"Dwarka\",\"state\":\"Delhi\",\"postal_code\":\"110078\",\"lat\":28.5908527,\"lng\":77.0433272}', 'India', 'Delhi', 'Dwarka', '110078', 0, 'approved', NULL, 'basic', NULL, 0, NULL, '2025-12-19 16:49:05', '2025-12-20 13:24:17');

-- --------------------------------------------------------

--
-- Table structure for table `employer_api_keys`
--

CREATE TABLE `employer_api_keys` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `secret_hash` varchar(255) NOT NULL,
  `allowed_ips` text DEFAULT NULL,
  `scopes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '[]' CHECK (json_valid(`scopes`)),
  `revoked` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employer_blacklist`
--

CREATE TABLE `employer_blacklist` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('email','domain','gst','ip','company') NOT NULL,
  `value` varchar(255) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employer_kyc_documents`
--

CREATE TABLE `employer_kyc_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `doc_type` enum('business_license','tax_id','address_proof','director_id','other') NOT NULL,
  `file_url` varchar(1024) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp(),
  `review_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `review_notes` text DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employer_kyc_documents`
--

INSERT INTO `employer_kyc_documents` (`id`, `employer_id`, `doc_type`, `file_url`, `file_name`, `uploaded_by`, `uploaded_at`, `review_status`, `review_notes`, `reviewed_by`, `reviewed_at`) VALUES
(1, 2, 'business_license', 'http://localhost/mindinfotech/public/storage/uploads/kyc/2/69243d47d2ffc_RFID & GPS Solutions.pdf', 'RFID & GPS Solutions.pdf', 3, '2025-11-24 16:41:03', 'approved', NULL, NULL, NULL),
(2, 2, 'tax_id', 'http://localhost/mindinfotech/public/storage/uploads/kyc/2/69243d47d6ca4_RFID & GPS Solutions.pdf', 'RFID & GPS Solutions.pdf', 3, '2025-11-24 16:41:03', 'approved', NULL, NULL, NULL),
(3, 2, 'address_proof', 'http://localhost/mindinfotech/public/storage/uploads/kyc/2/69243d47d8337_RFID & GPS Solutions.pdf', 'RFID & GPS Solutions.pdf', 3, '2025-11-24 16:41:03', 'approved', NULL, NULL, NULL),
(11, 10, 'business_license', 'http://localhost/mindinfotech/public/storage/uploads/kyc/10/6932bcaf1e023_Pooja_Jolly_Resume December Month.pdf', 'Pooja_Jolly_Resume December Month.pdf', 30, '2025-12-05 16:36:23', 'approved', '', 12, '2025-12-05 17:12:46'),
(12, 10, 'tax_id', 'http://localhost/mindinfotech/public/storage/uploads/kyc/10/6932bcaf21d16_Abhimanyu Tiwari cv.pdf', 'Abhimanyu Tiwari cv.pdf', 30, '2025-12-05 16:36:23', 'approved', NULL, 12, '2025-12-05 17:12:48'),
(13, 10, 'address_proof', 'http://localhost/mindinfotech/public/storage/uploads/kyc/10/6932bcaf23828_ResumeBapanDutta.pdf', 'ResumeBapanDutta.pdf', 30, '2025-12-05 16:36:23', 'approved', NULL, 12, '2025-12-05 17:12:48'),
(14, 10, 'other', 'http://localhost/mindinfotech/public/storage/uploads/kyc/10/6932bcaf252be_Invoice #1 - Job Portal2.pdf', 'Invoice #1 - Job Portal2.pdf', 30, '2025-12-05 16:36:23', 'approved', NULL, 12, '2025-12-05 17:12:48'),
(15, 11, 'business_license', 'http://localhost:8000/storage/uploads/kyc/11/694534aa06862_ResumeSIDDHESHDALAVI (1).pdf', 'ResumeSIDDHESHDALAVI (1).pdf', 36, '2025-12-19 16:49:06', 'approved', '', 12, '2025-12-20 07:40:22'),
(16, 11, 'tax_id', 'http://localhost:8000/storage/uploads/kyc/11/694534aa090e3_ResumePRASANNAtomar.pdf', 'ResumePRASANNAtomar.pdf', 36, '2025-12-19 16:49:06', 'approved', '', 12, '2025-12-19 11:40:38'),
(17, 11, 'address_proof', 'http://localhost:8000/storage/uploads/kyc/11/694534aa0aac2_ResumePrincePrasad.pdf', 'ResumePrincePrasad.pdf', 36, '2025-12-19 16:49:06', 'approved', '', 12, '2025-12-20 07:54:19'),
(18, 11, 'director_id', 'http://localhost:8000/storage/uploads/kyc/11/694534aa0e3ed_ResumePoojaKashyap.pdf', 'ResumePoojaKashyap.pdf', 36, '2025-12-19 16:49:06', 'approved', '', 12, '2025-12-20 07:54:17'),
(19, 11, 'other', 'http://localhost:8000/storage/uploads/kyc/11/694534aa0fc9a_HarshSainiResume.pdf', 'HarshSainiResume.pdf', 36, '2025-12-19 16:49:06', 'approved', '', 12, '2025-12-20 07:54:15');

-- --------------------------------------------------------

--
-- Table structure for table `employer_payments`
--

CREATE TABLE `employer_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(8) DEFAULT 'INR',
  `gateway` varchar(64) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` enum('pending','success','failed','refunded') DEFAULT 'pending',
  `refund_status` enum('none','partial','full') DEFAULT 'none',
  `error_message` text DEFAULT NULL,
  `txn_id` varchar(255) DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employer_risk_scores`
--

CREATE TABLE `employer_risk_scores` (
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `risk_level` enum('low','medium','high','blocked') NOT NULL DEFAULT 'medium',
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `flagged` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employer_settings`
--

CREATE TABLE `employer_settings` (
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `billing_plan` varchar(128) DEFAULT 'free',
  `credits` int(11) DEFAULT 0,
  `timezone` varchar(64) DEFAULT 'Asia/Kolkata',
  `notification_pref` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_pref`)),
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employer_settings`
--

INSERT INTO `employer_settings` (`employer_id`, `billing_plan`, `credits`, `timezone`, `notification_pref`, `created_at`, `updated_at`) VALUES
(2, 'free', 0, 'Asia/Kolkata', '{\"email_new_application\":true,\"email_shortlisted\":true,\"email_interview_scheduled\":true,\"email_messages\":true,\"push_notifications\":true}', '2026-01-01 17:12:17', '2026-01-01 17:12:17'),
(11, 'free', 0, 'Asia/Kolkata', '{\"email_new_application\":true,\"email_shortlisted\":true,\"email_interview_scheduled\":true,\"email_messages\":true,\"push_notifications\":true}', '2026-01-07 12:25:13', '2026-01-07 12:25:13');

-- --------------------------------------------------------

--
-- Table structure for table `employer_subscriptions`
--

CREATE TABLE `employer_subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('active','expired','cancelled','trial','suspended') DEFAULT 'trial',
  `billing_cycle` enum('monthly','quarterly','annual') DEFAULT 'monthly',
  `started_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `trial_ends_at` datetime DEFAULT NULL,
  `grace_period_ends_at` datetime DEFAULT NULL,
  `auto_renew` tinyint(1) DEFAULT 0,
  `next_billing_date` datetime DEFAULT NULL,
  `contacts_used_this_month` int(11) DEFAULT 0,
  `resume_downloads_used_this_month` int(11) DEFAULT 0,
  `chat_messages_used_this_month` int(11) DEFAULT 0,
  `job_posts_used` int(11) DEFAULT 0,
  `last_usage_reset_at` datetime DEFAULT NULL,
  `referral_code` varchar(32) DEFAULT NULL,
  `discount_code` varchar(32) DEFAULT NULL,
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `cancelled_at` datetime DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employer_subscriptions`
--

INSERT INTO `employer_subscriptions` (`id`, `employer_id`, `plan_id`, `status`, `billing_cycle`, `started_at`, `expires_at`, `trial_ends_at`, `grace_period_ends_at`, `auto_renew`, `next_billing_date`, `contacts_used_this_month`, `resume_downloads_used_this_month`, `chat_messages_used_this_month`, `job_posts_used`, `last_usage_reset_at`, `referral_code`, `discount_code`, `discount_percentage`, `cancelled_at`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
(2, 2, 2, 'active', 'monthly', '2025-11-27 10:13:46', '2025-12-27 10:13:46', NULL, NULL, 0, NULL, 0, 0, 0, 5, '2025-11-27 10:13:46', NULL, NULL, 0.00, NULL, NULL, '2025-11-27 15:43:46', '2025-12-09 18:35:55'),
(4, 10, 1, 'active', 'monthly', '2025-12-05 12:21:04', '2026-01-04 12:21:04', NULL, NULL, 0, NULL, 0, 0, 0, 1, '2025-12-05 12:21:04', NULL, NULL, 0.00, NULL, NULL, '2025-12-05 17:51:04', '2025-12-05 18:20:29'),
(5, 2, 3, 'active', 'monthly', '2025-12-13 06:37:32', '2026-01-12 06:37:32', NULL, NULL, 0, NULL, 0, 0, 0, 0, '2025-12-13 06:37:32', NULL, NULL, 0.00, NULL, NULL, '2025-12-13 12:07:32', '2025-12-13 12:07:32'),
(6, 2, 4, 'active', 'monthly', '2025-12-13 06:39:22', '2026-01-12 06:39:22', NULL, NULL, 0, NULL, 0, 0, 0, 0, '2025-12-13 06:39:22', NULL, NULL, 0.00, NULL, NULL, '2025-12-13 12:09:22', '2025-12-13 12:09:22'),
(7, 2, 3, 'active', 'monthly', '2025-12-13 07:40:38', '2026-01-12 07:40:38', NULL, NULL, 0, NULL, 0, 0, 0, 0, '2025-12-13 07:40:38', NULL, NULL, 0.00, NULL, NULL, '2025-12-13 13:10:38', '2025-12-13 13:10:38'),
(8, 2, 4, 'active', 'monthly', '2025-12-13 07:46:44', '2026-01-12 07:46:44', NULL, NULL, 0, NULL, 0, 0, 0, 3, '2025-12-13 07:46:44', NULL, NULL, 0.00, NULL, NULL, '2025-12-13 13:16:44', '2025-12-24 15:19:38'),
(9, 10, 3, 'active', 'monthly', '2025-12-17 11:13:25', '2026-01-16 11:13:25', NULL, NULL, 0, NULL, 0, 0, 0, 0, '2025-12-17 11:13:25', NULL, NULL, 0.00, NULL, NULL, '2025-12-17 16:43:25', '2025-12-17 16:43:25'),
(10, 10, 4, 'active', 'monthly', '2025-12-17 11:13:40', '2026-01-16 11:13:40', NULL, NULL, 0, NULL, 0, 0, 0, 2, '2025-12-17 11:13:40', NULL, NULL, 0.00, NULL, NULL, '2025-12-17 16:43:40', '2025-12-17 17:07:49'),
(11, 2, 2, 'active', 'annual', '2025-12-25 07:01:26', '2026-12-25 07:01:26', NULL, NULL, 0, NULL, 0, 0, 0, 0, '2025-12-25 07:01:26', NULL, NULL, 0.00, NULL, NULL, '2025-12-25 12:31:26', '2025-12-25 12:31:26'),
(12, 2, 3, 'active', 'monthly', '2025-12-25 07:01:35', '2026-01-24 07:01:35', NULL, NULL, 0, NULL, 0, 0, 0, 0, '2025-12-25 07:01:35', NULL, NULL, 0.00, NULL, NULL, '2025-12-25 12:31:35', '2025-12-25 12:31:35'),
(13, 2, 4, 'active', 'annual', '2025-12-25 08:12:20', '2026-12-25 08:12:20', NULL, NULL, 0, NULL, 0, 0, 0, 2, '2025-12-25 08:12:20', NULL, NULL, 0.00, NULL, NULL, '2025-12-25 13:42:20', '2026-01-03 16:36:01'),
(14, 2, 2, 'active', 'annual', '2026-01-03 12:59:48', '2027-01-03 12:59:48', NULL, NULL, 0, NULL, 0, 0, 0, 0, '2026-01-03 12:59:48', NULL, NULL, 0.00, NULL, NULL, '2026-01-03 18:29:48', '2026-01-03 18:29:48'),
(15, 2, 4, 'active', 'monthly', '2026-01-05 06:49:19', '2026-02-04 06:49:19', NULL, NULL, 0, NULL, 0, 0, 0, 0, '2026-01-05 06:49:19', NULL, NULL, 0.00, NULL, NULL, '2026-01-05 12:19:19', '2026-01-05 12:19:19'),
(16, 2, 2, 'active', 'monthly', '2026-01-06 06:41:54', '2026-02-05 06:41:54', NULL, NULL, 0, NULL, 0, 0, 0, 0, '2026-01-06 06:41:54', NULL, NULL, 0.00, NULL, NULL, '2026-01-06 12:11:54', '2026-01-06 12:11:54'),
(17, 2, 4, 'active', 'monthly', '2026-01-06 06:42:12', '2026-02-05 06:42:12', NULL, NULL, 0, NULL, 0, 0, 0, 0, '2026-01-06 06:42:12', NULL, NULL, 0.00, NULL, NULL, '2026-01-06 12:12:12', '2026-01-06 12:12:12'),
(18, 2, 3, 'active', 'monthly', '2026-01-06 06:42:29', '2026-02-05 06:42:29', NULL, NULL, 0, NULL, 0, 0, 0, 0, '2026-01-06 06:42:29', NULL, NULL, 0.00, NULL, NULL, '2026-01-06 12:12:29', '2026-01-06 12:12:29'),
(19, 2, 2, 'active', 'monthly', '2026-01-06 07:55:37', '2026-02-05 07:55:37', NULL, NULL, 0, NULL, 0, 0, 0, 0, '2026-01-06 07:55:37', NULL, NULL, 0.00, NULL, NULL, '2026-01-06 13:25:37', '2026-01-06 13:25:37'),
(20, 11, 2, 'active', 'monthly', '2026-01-06 09:17:01', '2026-02-05 09:17:01', NULL, NULL, 0, NULL, 0, 0, 1, 0, '2026-01-06 09:17:01', NULL, NULL, 0.00, NULL, NULL, '2026-01-06 14:47:01', '2026-01-06 16:10:38'),
(21, 2, 4, 'active', 'monthly', '2026-01-06 13:10:34', '2026-02-05 13:10:34', NULL, NULL, 0, NULL, 0, 0, 0, 0, '2026-01-06 13:10:34', NULL, NULL, 0.00, NULL, NULL, '2026-01-06 18:40:34', '2026-01-06 18:40:34');

-- --------------------------------------------------------

--
-- Table structure for table `employer_verification_logs`
--

CREATE TABLE `employer_verification_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `rule_name` varchar(100) NOT NULL,
  `result` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`result`)),
  `risk_score_change` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hiring_funnel_events`
--

CREATE TABLE `hiring_funnel_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `stage` enum('applied','shortlisted','interviewed','offered','hired','rejected') NOT NULL,
  `entered_at` datetime DEFAULT current_timestamp(),
  `exited_at` datetime DEFAULT NULL,
  `days_in_stage` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interviews`
--

CREATE TABLE `interviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `scheduled_by` bigint(20) UNSIGNED NOT NULL,
  `interview_type` enum('phone','video','onsite') DEFAULT 'phone',
  `scheduled_start` datetime NOT NULL,
  `scheduled_end` datetime NOT NULL,
  `timezone` varchar(64) DEFAULT 'Asia/Kolkata',
  `location` varchar(512) DEFAULT NULL,
  `meeting_link` varchar(1024) DEFAULT NULL,
  `status` enum('scheduled','rescheduled','completed','cancelled') DEFAULT 'scheduled',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interviews`
--

INSERT INTO `interviews` (`id`, `application_id`, `employer_id`, `scheduled_by`, `interview_type`, `scheduled_start`, `scheduled_end`, `timezone`, `location`, `meeting_link`, `status`, `created_at`, `updated_at`) VALUES
(4, 13, 2, 3, 'phone', '2025-12-21 12:14:00', '2025-12-21 13:14:00', 'Asia/Kolkata', 'Dwarka Sector-12 , New Delhi, India 110078', '', 'completed', '2025-12-20 12:14:43', '2025-12-20 13:42:48'),
(5, 12, 2, 3, 'phone', '2025-12-20 13:45:00', '2025-12-20 13:50:00', 'Asia/Kolkata', 'Dwarka Sector-12, New Delhi, India 110078', '', 'completed', '2025-12-20 13:46:03', '2025-12-20 19:20:18'),
(6, 13, 2, 3, 'phone', '2026-01-06 17:04:00', '2026-01-06 18:04:00', 'Asia/Kolkata', '', '', 'completed', '2026-01-05 16:03:58', '2026-01-07 11:59:08'),
(7, 13, 2, 3, 'phone', '2026-01-06 17:04:00', '2026-01-06 18:04:00', 'Asia/Kolkata', '', '', 'cancelled', '2026-01-05 16:04:04', '2026-01-05 16:17:01'),
(8, 13, 2, 3, 'onsite', '2026-01-07 16:28:00', '2026-01-07 17:28:00', 'Asia/Kolkata', 'Dwarka Sector 12, New Delhi', '', 'completed', '2026-01-05 16:29:15', '2026-01-07 11:59:08'),
(9, 13, 2, 3, 'onsite', '2026-01-07 16:28:00', '2026-01-07 17:28:00', 'Asia/Kolkata', 'Dwarka Sector 12, New Delhi', '', 'completed', '2026-01-05 16:29:19', '2026-01-07 11:59:08'),
(10, 13, 2, 3, 'onsite', '2026-01-07 16:28:00', '2026-01-07 17:28:00', 'Asia/Kolkata', 'Dwarka Sector 12, New Delhi', '', 'completed', '2026-01-05 16:29:26', '2026-01-07 11:59:08'),
(11, 13, 2, 3, 'onsite', '2026-01-07 16:28:00', '2026-01-07 17:28:00', 'Asia/Kolkata', 'Dwarka Sector 12, New Delhi', '', 'completed', '2026-01-05 16:29:40', '2026-01-07 11:59:08');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `employer_id` int(11) DEFAULT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `subscription_id` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `tax` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `status` enum('paid','unpaid','refunded') DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `issued_at` datetime DEFAULT NULL,
  `due_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ip_whitelist`
--

CREATE TABLE `ip_whitelist` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `short_description` varchar(1000) DEFAULT NULL,
  `employment_type` enum('full_time','part_time','contract','internship','freelance') DEFAULT 'full_time',
  `seniority` enum('entry','mid','senior','lead','manager') DEFAULT 'mid',
  `salary_min` int(11) DEFAULT NULL,
  `salary_max` int(11) DEFAULT NULL,
  `currency` varchar(8) DEFAULT 'INR',
  `pay_type` varchar(50) DEFAULT NULL,
  `pay_frequency` varchar(50) DEFAULT NULL,
  `pay_fixed_amount` int(11) DEFAULT NULL,
  `hours_per_week` int(11) DEFAULT NULL,
  `shift` varchar(100) DEFAULT NULL,
  `contract_length` int(11) DEFAULT NULL,
  `contract_period` varchar(20) DEFAULT NULL,
  `commission_percent` decimal(5,2) DEFAULT NULL,
  `incentive_rules` text DEFAULT NULL,
  `stipend` int(11) DEFAULT NULL,
  `internship_length` int(11) DEFAULT NULL,
  `season_duration` varchar(50) DEFAULT NULL,
  `flexible_hours` tinyint(1) DEFAULT 0,
  `remote_policy` varchar(255) DEFAULT NULL,
  `remote_tools` varchar(255) DEFAULT NULL,
  `is_remote` tinyint(1) DEFAULT 0,
  `locations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`locations`)),
  `status` enum('draft','published','paused','closed','archived') DEFAULT 'draft',
  `visibility` enum('public','private','internal') DEFAULT 'public',
  `publish_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `vacancies` int(11) DEFAULT 1,
  `views` bigint(20) UNSIGNED DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `experience_type` enum('any','fresher','experienced') DEFAULT 'any' COMMENT 'Experience requirement type',
  `min_experience` int(11) DEFAULT NULL COMMENT 'Minimum years of experience',
  `max_experience` int(11) DEFAULT NULL COMMENT 'Maximum years of experience',
  `offers_bonus` enum('yes','no') DEFAULT 'no' COMMENT 'Whether job offers bonus',
  `call_availability` enum('everyday','weekdays','weekdays_saturday','custom') DEFAULT 'everyday' COMMENT 'When candidates can call',
  `company_name` varchar(255) DEFAULT NULL COMMENT 'Company name (can override employer default)',
  `contact_person` varchar(255) DEFAULT NULL COMMENT 'Contact person name',
  `phone` varchar(32) DEFAULT NULL COMMENT 'Contact phone number',
  `email` varchar(255) DEFAULT NULL COMMENT 'Contact email',
  `contact_profile` enum('owner','hr','recruiter') DEFAULT NULL COMMENT 'Contact person profile',
  `company_size` enum('1-10','11-50','51-200','201-500','501-1000','1001+') DEFAULT NULL COMMENT 'Company size (can override employer default)',
  `hiring_urgency` enum('immediate','can_wait') DEFAULT 'immediate' COMMENT 'How soon position needs to be filled',
  `job_timings` varchar(255) DEFAULT NULL COMMENT 'Job working hours/timings',
  `interview_timings` varchar(255) DEFAULT NULL COMMENT 'Interview schedule timings',
  `job_address` text DEFAULT NULL COMMENT 'Complete job address',
  `language` varchar(50) DEFAULT 'English',
  `category` varchar(128) DEFAULT NULL COMMENT 'Job category/industry (e.g., IT/Software, Manufacturing, Sales & Marketing)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `employer_id`, `title`, `slug`, `description`, `short_description`, `employment_type`, `seniority`, `salary_min`, `salary_max`, `currency`, `pay_type`, `pay_frequency`, `pay_fixed_amount`, `hours_per_week`, `shift`, `contract_length`, `contract_period`, `commission_percent`, `incentive_rules`, `stipend`, `internship_length`, `season_duration`, `flexible_hours`, `remote_policy`, `remote_tools`, `is_remote`, `locations`, `status`, `visibility`, `publish_at`, `expires_at`, `vacancies`, `views`, `created_at`, `updated_at`, `experience_type`, `min_experience`, `max_experience`, `offers_bonus`, `call_availability`, `company_name`, `contact_person`, `phone`, `email`, `contact_profile`, `company_size`, `hiring_urgency`, `job_timings`, `interview_timings`, `job_address`, `language`, `category`) VALUES
(30, 10, 'Web Application Developer', 'web-application-developer', '\n                    Web Application Developer\n                    We are hiring a full time Web Application Developer. Responsibilities include delivering results, collaborating with cross-functional teams, and maintaining high standards.\n                    Responsibilities\n                    Plan and execute assigned tasksCommunicate with stakeholdersMaintain documentation\n                    Requirements\n                    Relevant experienceStrong communicationAbility to work monthly\n                    Benefits\n                    Competitive payGrowth opportunitiesHealthy work culture', '', '', 'mid', 1000, 20000, 'RUB', 'range', 'monthly', NULL, NULL, '', NULL, 'months', NULL, '', NULL, NULL, NULL, 0, NULL, NULL, 1, NULL, 'published', 'public', NULL, NULL, 1, 0, '2025-12-11 10:55:19', '2025-12-13 11:58:29', 'experienced', NULL, 3, 'no', 'everyday', 'Barcode Vault', 'varsha mam', '+73XX 8978787987', 'hr.1@gmail.com', 'hr', '11-50', 'can_wait', '10:00 AM - 07:00 PM | Monday to Saturday', '02:00 AM - 05:00 PM | Monday to Saturday', 'Moscow, Moscow, Russia', 'Russian', 'Manufacturing'),
(31, 2, '3 Wheeler Driver', '3-wheeler-driver-1', 'About the RoleWe are seeking a reliable and experienced 3 Wheeler Driver to join our team. This is a full-time, on-site position based in Anandnagar, Uttar Pradesh, India. The ideal candidate should have strong driving skills, knowledge of local routes, and a commitment to safety and professionalism.Job SummaryAs a 3 Wheeler Driver, you will be responsible for transporting goods safely and efficiently. You will play an important role in daily operations, ensuring timely deliveries and maintaining the assigned vehicle. We offer a competitive salary, job stability, and a supportive work environment.Key ResponsibilitiesSafely operate a 3-wheeler for daily delivery or transportation tasksLoad and unload goods as requiredEnsure timely delivery to assigned locationsFollow assigned routes and schedulesMaintain vehicle cleanliness and basic upkeepReport any mechanical issues to the supervisorFollow all traffic rules and company safety policiesMaintain delivery logs and customer receiptsProvide good customer service during deliveriesRequired QualificationsMinimum 10th pass (preferred, not mandatory)Valid 3-wheeler or commercial driving licenseProven experience as a 3-wheeler driverBasic understanding of local routes and areasPhysically fit to load/unload goodsAbility to work flexible hours when neededGood communication and disciplineTechnical SkillsAbility to operate and maintain 3-wheeler vehiclesBasic mechanical knowledge for minor repairsUsing GPS or route maps (if provided)Handling invoices and delivery documentationWhat We OfferCompetitive monthly salaryPerformance incentives (if applicable)Fuel allowance or company-provided vehicleStable, long-term employment opportunitySupportive team and safe working environmentOvertime pay (as per company policy)', '', 'full_time', 'mid', 0, 0, 'INR', 'range', 'monthly', NULL, NULL, '', NULL, 'months', NULL, '', NULL, NULL, NULL, 0, NULL, NULL, 0, NULL, 'published', 'public', NULL, NULL, 1, 0, '2025-12-13 13:09:00', '2025-12-13 16:20:52', 'any', NULL, NULL, 'no', 'everyday', 'Indian Barcode Corporation', 'Varsha Mam', '+91 9910112688', 'testhr@gmail.com', 'hr', '1-10', 'immediate', '09:30 AM - 06:30 PM | Monday to Saturday | IST', '11:00 AM - 04:00 PM | Monday to Saturday | IST', 'Anandnagar, Uttar Pradesh, India ', 'Hindi', 'Driver'),
(32, 2, 'Web And Software Developer', 'web-and-software-developer', 'About the Role\nWe are seeking a talented and motivated Web And Software Developer to join our team. This is a part_time position based in Sunan, Pyongyang, North Korea.\n\nJob Summary\nAs a Web And Software Developer, you will play a key role in our organization, contributing to our success through your expertise and dedication. We offer Competitive salary and a supportive work environment.\n\nKey Responsibilities\n\nRequired Qualifications\n\nTechnical Skills\nRelevant skills and experience\n\nWhat We Offer\n\nHow to Apply\nIf you are interested in this position and meet the requirements, please submit your application through our portal. We look forward to hearing from you!', '', 'internship', 'mid', 7500, 7500, 'INR', 'fixed', 'monthly', NULL, NULL, '', NULL, 'months', NULL, '', 7500, 6, NULL, 0, NULL, NULL, 0, NULL, 'published', 'public', NULL, NULL, 5, 0, '2025-12-13 16:04:52', '2025-12-13 16:47:21', 'any', NULL, NULL, 'no', 'weekdays', 'Indian Barcode Corporation', 'HERA ', '+91 8800839490', 'sales@indianbarcode.com', '', '11-50', 'immediate', '09:30 AM - 06:30 PM | Monday to Saturday | IST', '11:00 AM - 04:00 PM | Monday to Saturday | IST', 'Shimla, Himachal Pradesh, India', 'Hindi', 'IT / Software'),
(33, 2, 'Clinical Pharmacist', 'clinical-pharmacist-1', '<p>About the Role\nWe are seeking a talented and motivated Clinical Pharmacist to join our team. This is a full_time position based in Bāli, Rajasthan, India.\n\nJob Summary\nAs a Clinical Pharmacist, you will play a key role in our organization, contributing to our success through your expertise and dedication. We offer Competitive salary and a supportive work environment.\n\nKey Responsibilities\n\n\nRequired Qualifications\n\n\nTechnical Skills\nRelevant skills and experience\n\nWhat We Offer\n\n\nHow to Apply\nIf you are interested in this position and meet the requirements, please submit your application through our portal. We look forward to hearing from you!</p>', '<p>About the Role\nWe are seeking a talented and motivated Clinical Pharmacist to join our team. This is a full_time position based in Bāli, Rajasthan, India.\n\nJob Summary\nAs a Clinical Pharmacist, you will play a key role in our organization, contributing to our success through your expertise and dedication. We offer Competitive salary and a supportive work environment.\n\nKey Responsibilities\n\n\nRequired Qualifications\n\n\nTechnical Skills\nRelevant skills and experience\n\nWhat We Offer\n\n\nHow to Apply\nIf you are interested in this position and meet the requirements, please submit your application through our portal. We look forward to hearing from you!</p>', 'full_time', 'mid', 25000, 35000, 'AMD', 'range', 'monthly', NULL, NULL, '', NULL, 'months', NULL, '', NULL, NULL, NULL, 0, NULL, NULL, 0, NULL, 'published', 'public', NULL, NULL, 1, 0, '2025-12-17 16:22:54', '2025-12-27 10:25:47', 'any', NULL, NULL, 'no', 'everyday', 'Indian Barcode Corporation', 'Vinita ', '+91 8527522688', 'hr@gmail.com', 'hr', '11-50', 'immediate', '09:30 AM - 06:30 PM | Monday to Saturday | IST', '11:00 AM - 04:00 PM | Monday to Saturday | IST', 'Vaishali Nagar, Jaipur, Rajasthan, India', 'Hindi', 'Pharmaceuticals & Biotechnology'),
(34, 10, 'Banking Executive', 'banking-executive', 'About the Role\nWe are seeking a talented and motivated Banking Executive to join our team. This is a contract position based in Cochin, Kerala, India.\n\nJob Summary\nAs a Banking Executive, you will play a key role in our organization, contributing to our success through your expertise and dedication. We offer Competitive salary and a supportive work environment.\n\nKey Responsibilities\n\n\nRequired Qualifications\n\n\nTechnical Skills\nRelevant skills and experience\n\nWhat We Offer\n\n\nHow to Apply\nIf you are interested in this position and meet the requirements, please submit your application through our portal. We look forward to hearing from you!', '', 'contract', 'mid', 18000, 27000, 'INR', 'range', 'monthly', NULL, NULL, '', 1, 'years', NULL, '', NULL, NULL, NULL, 0, NULL, NULL, 0, NULL, 'published', 'public', NULL, NULL, 1, 0, '2025-12-17 16:57:48', '2025-12-17 16:58:07', 'fresher', NULL, NULL, 'no', 'everyday', 'Barcode Vault', '', '+91 9910112688', 'hr@gmail.com', 'recruiter', '51-200', 'immediate', '09:30 AM - 06:30 PM | Monday to Saturday | IST', '11:00 AM - 04:00 PM | Monday to Saturday | IST', 'Cochin, Kerala, India', 'Hindi', 'Banking & Financial Services'),
(35, 10, 'Delivery Boy', 'delivery-boy', 'About the Role\nWe are seeking a talented and motivated Delivery Boy to join our team. This is a full_time position based in Gorakhpur, Uttar Pradesh, India.\n\nJob Summary\nAs a Delivery Boy, you will play a key role in our organization, contributing to our success through your expertise and dedication. We offer Competitive salary and a supportive work environment.\n\nKey Responsibilities\n\n\nRequired Qualifications\n\n\nTechnical Skills\nRelevant skills and experience\n\nWhat We Offer\n\n\nHow to Apply\nIf you are interested in this position and meet the requirements, please submit your application through our portal. We look forward to hearing from you!', '', 'full_time', 'mid', 9000, 22000, 'INR', 'range', 'monthly', NULL, NULL, '', NULL, 'months', NULL, '', NULL, NULL, NULL, 0, NULL, NULL, 0, NULL, 'published', 'public', NULL, NULL, 5, 0, '2025-12-17 17:07:49', '2025-12-17 17:11:29', 'any', NULL, NULL, 'no', 'everyday', 'Barcode Vault', 'Vinitha ', '+91 9910112688', 'hr@gmail.com', 'hr', '201-500', 'immediate', '09:30 AM - 06:30 PM | Monday, Tuesday, Wednesday, Friday, Saturday, Sunday | IST', '11:00 AM - 04:00 PM | Monday to Saturday | IST', 'Gorakhnath, Gorakhpur, Uttar Pradesh, India', 'Hindi', 'Delivery Boy / Partner'),
(39, 11, 'Web And Software Developer', 'web-and-software-developer-1', 'About the Role\nWe are seeking a talented and motivated Web And Software Developer to join our team. This is a full_time position based in Kumbalam, Kerala, India.\n\nJob Summary\nAs a Web And Software Developer, you will play a key role in our organization, contributing to our success through your expertise and dedication. We offer INR 45000 - 55000 per monthly and a supportive work environment.\n\nKey Responsibilities\n\n\nRequired Qualifications\n\n\nTechnical Skills\nRelevant skills and experience\n\nWhat We Offer\n\n\nHow to Apply\nIf you are interested in this position and meet the requirements, please submit your application through our portal. We look forward to hearing from you!', 'About the Role\nWe are seeking a talented and motivated Web And Software Developer to join our team. This is a full_time position based in Kumbalam, Kerala, India.\n\nJob Summary\nAs a Web And Software Developer, you will play a key role in our organization, contributing to our success through your expertise and dedication. We offer INR 45000 - 55000 per monthly and a supportive work environment.\n\nKey Responsibilities\n\n\nRequired Qualifications\n\n\nTechnical Skills\nRelevant skills and experience\n\nWhat We Offer\n\n\nHow to Apply\nIf you are interested in this position and meet the requirements, please submit your application through our portal. We look forward to hearing from you!', 'full_time', 'mid', 45000, 55000, 'INR', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0, NULL, 'published', 'public', NULL, NULL, 1, 0, '2026-01-06 14:01:59', '2026-01-06 14:03:44', 'any', NULL, NULL, 'no', 'everyday', 'PKR Techvision', '', '+919876543210', 'info.myndsglobal@gmail.com', '', '11-50', 'immediate', '', '', '', 'English', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `job_benefits`
--

CREATE TABLE `job_benefits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_id` bigint(20) UNSIGNED NOT NULL,
  `benefit_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_benefits`
--

INSERT INTO `job_benefits` (`id`, `job_id`, `benefit_id`) VALUES
(25, 30, 3),
(27, 30, 16),
(26, 30, 26),
(24, 30, 30),
(33, 31, 17),
(34, 31, 28),
(35, 31, 32),
(38, 32, 29),
(39, 32, 30),
(55, 33, 3),
(56, 33, 28),
(57, 33, 30),
(58, 33, 31),
(59, 33, 32),
(47, 34, 3),
(46, 34, 5),
(48, 34, 16),
(45, 34, 29),
(49, 34, 40),
(54, 35, 7),
(53, 35, 9),
(51, 35, 28),
(52, 35, 29),
(50, 35, 30),
(66, 39, 11),
(67, 39, 16),
(64, 39, 17),
(65, 39, 29);

-- --------------------------------------------------------

--
-- Table structure for table `job_bookmarks`
--

CREATE TABLE `job_bookmarks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `candidate_id` bigint(20) UNSIGNED NOT NULL,
  `job_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_bookmarks`
--

INSERT INTO `job_bookmarks` (`id`, `candidate_id`, `job_id`, `created_at`) VALUES
(28, 12, 32, '2025-12-23 15:00:49');

-- --------------------------------------------------------

--
-- Table structure for table `job_categories`
--

CREATE TABLE `job_categories` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(128) NOT NULL,
  `slug` varchar(128) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL COMMENT 'Image/icon URL for the category',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_categories`
--

INSERT INTO `job_categories` (`id`, `name`, `slug`, `description`, `image`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'IT / Software', 'it-software', '', 'http://localhost:8000/storage/uploads/job-categories/693d42cddd8f2_Information_Technology.avif', 1, 1, '2025-12-13 13:00:58', '2025-12-13 16:11:17'),
(2, 'Manufacturing', 'manufacturing', '', 'http://localhost:8000/storage/uploads/job-categories/693d2a0c27dcd_manufacturing.webp', 1, 2, '2025-12-13 13:00:58', '2025-12-13 14:25:40'),
(3, 'Sales & Marketing', 'sales-marketing', '', 'http://localhost:8000/storage/uploads/job-categories/693d3048a00d0_telesales_telemarketing.webp', 1, 3, '2025-12-13 13:00:58', '2025-12-13 14:52:16'),
(4, 'Finance & Accounting', 'finance-accounting', NULL, NULL, 1, 4, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(5, 'Healthcare & Medical', 'healthcare-medical', NULL, NULL, 1, 5, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(6, 'Education & Training', 'education-training', NULL, NULL, 1, 6, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(7, 'Retail & E-commerce', 'retail-ecommerce', NULL, NULL, 1, 7, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(8, 'Hospitality & Tourism', 'hospitality-tourism', NULL, NULL, 1, 8, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(9, 'Construction & Real Estate', 'construction-real-estate', NULL, NULL, 1, 9, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(10, 'Logistics & Supply Chain', 'logistics-supply-chain', NULL, NULL, 1, 10, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(11, 'Banking & Financial Services', 'banking-financial-services', '', 'http://localhost:8000/storage/uploads/job-categories/694291a484238_banking-service-online-app.webp', 1, 11, '2025-12-13 13:00:58', '2025-12-17 16:49:00'),
(12, 'Telecommunications', 'telecommunications', NULL, NULL, 1, 12, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(13, 'Automotive', 'automotive', NULL, NULL, 1, 13, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(14, 'Pharmaceutical', 'pharmaceutical', '', 'http://localhost:8000/storage/uploads/job-categories/69428cbdc2548_role-of-biotechnology-in-pharmaceutical-industry.jpg', 1, 14, '2025-12-13 13:00:58', '2025-12-30 12:51:25'),
(15, 'Food & Beverage', 'food-beverage', '', NULL, 1, 15, '2025-12-13 13:00:58', '2025-12-17 17:10:01'),
(16, 'Textiles & Apparel', 'textiles-apparel', NULL, NULL, 1, 16, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(17, 'Energy & Power', 'energy-power', NULL, NULL, 1, 17, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(18, 'Media & Entertainment', 'media-entertainment', NULL, NULL, 1, 18, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(19, 'Aviation & Aerospace', 'aviation-aerospace', NULL, NULL, 1, 19, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(20, 'Shipping & Maritime', 'shipping-maritime', NULL, NULL, 1, 20, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(21, 'Agriculture & Farming', 'agriculture-farming', NULL, NULL, 1, 21, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(22, 'Legal Services', 'legal-services', NULL, NULL, 1, 22, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(23, 'Consulting', 'consulting', NULL, NULL, 1, 23, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(24, 'Human Resources', 'human-resources', NULL, NULL, 1, 24, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(25, 'Customer Service', 'customer-service', NULL, NULL, 1, 25, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(26, 'Administrative & Clerical', 'administrative-clerical', NULL, NULL, 1, 26, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(27, 'Engineering', 'engineering', NULL, NULL, 1, 27, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(28, 'Design & Creative', 'design-creative', NULL, NULL, 1, 28, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(29, 'Research & Development', 'research-development', NULL, NULL, 1, 29, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(30, 'Quality Assurance', 'quality-assurance', NULL, NULL, 1, 30, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(31, 'Project Management', 'project-management', NULL, NULL, 1, 31, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(32, 'Operations', 'operations', NULL, NULL, 1, 32, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(33, 'Procurement & Purchasing', 'procurement-purchasing', NULL, NULL, 1, 33, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(34, 'Warehouse & Distribution', 'warehouse-distribution', NULL, NULL, 1, 34, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(35, 'Security & Safety', 'security-safety', NULL, NULL, 1, 35, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(36, 'Maintenance & Repair', 'maintenance-repair', NULL, NULL, 1, 36, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(37, 'Beauty & Wellness', 'beauty-wellness', NULL, NULL, 1, 37, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(38, 'Fitness & Sports', 'fitness-sports', NULL, NULL, 1, 38, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(39, 'Event Management', 'event-management', NULL, NULL, 1, 39, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(40, 'Non-Profit & NGO', 'non-profit-ngo', NULL, NULL, 1, 40, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(41, 'Government & Public Sector', 'government-public-sector', NULL, NULL, 1, 41, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(42, 'Insurance', 'insurance', NULL, NULL, 1, 42, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(43, 'Real Estate', 'real-estate', NULL, NULL, 1, 43, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(44, 'Travel & Tourism', 'travel-tourism', NULL, NULL, 1, 44, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(45, 'Fashion & Apparel', 'fashion-apparel', NULL, NULL, 1, 45, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(46, 'Gaming & Animation', 'gaming-animation', NULL, NULL, 1, 46, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(47, 'Digital Marketing', 'digital-marketing', NULL, NULL, 1, 47, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(48, 'Content Writing', 'content-writing', NULL, NULL, 1, 48, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(49, 'Data Science & Analytics', 'data-science-analytics', NULL, NULL, 1, 49, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(50, 'Cybersecurity', 'cybersecurity', NULL, NULL, 1, 50, '2025-12-13 13:00:58', '2025-12-13 13:00:58'),
(51, 'Electrician', 'electrician', NULL, NULL, 1, 200, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(52, 'Plumber', 'plumber', NULL, NULL, 1, 201, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(53, 'AC Technician', 'ac-technician', NULL, NULL, 1, 202, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(54, 'Refrigeration Technician', 'refrigeration-technician', NULL, NULL, 1, 203, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(55, 'Mobile Repair Technician', 'mobile-repair-technician', NULL, NULL, 1, 204, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(56, 'Laptop / Computer Technician', 'computer-technician', NULL, NULL, 1, 205, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(57, 'CCTV Technician', 'cctv-technician', NULL, NULL, 1, 206, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(58, 'Driver', 'driver', '', 'http://localhost:8000/storage/uploads/job-categories/693d2cea48e71_driver.webp', 1, 207, '2025-12-13 13:02:18', '2025-12-13 14:37:54'),
(59, 'Cab Driver (Uber/Ola)', 'cab-driver', NULL, NULL, 1, 208, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(60, 'Truck Driver', 'truck-driver', NULL, NULL, 1, 209, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(61, 'Delivery Boy / Partner', 'delivery-boy-partner', '', 'http://localhost:8000/storage/uploads/job-categories/6942978eb7c04_delivery.webp', 1, 210, '2025-12-13 13:02:18', '2025-12-17 17:14:14'),
(62, 'Courier Delivery', 'courier-delivery', NULL, NULL, 1, 211, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(63, 'Warehouse Worker', 'warehouse-worker', NULL, NULL, 1, 212, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(64, 'Picker / Packer', 'picker-packer', NULL, NULL, 1, 214, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(65, 'Loading / Unloading', 'loading-unloading', NULL, NULL, 1, 215, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(66, 'Manufacturing Worker', 'manufacturing-worker', NULL, NULL, 1, 216, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(67, 'Machine Operator', 'machine-operator', NULL, NULL, 1, 217, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(68, 'Factory Worker', 'factory-worker', NULL, NULL, 1, 218, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(69, 'Production Worker', 'production-worker', NULL, NULL, 1, 219, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(70, 'Peon / Office Boy', 'peon-office-boy', NULL, NULL, 1, 220, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(71, 'Housekeeping Staff', 'housekeeping', NULL, NULL, 1, 221, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(72, 'Security Guard', 'security-guard', NULL, NULL, 1, 222, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(73, 'Cleaner / Sweeper', 'cleaner-sweeper', NULL, NULL, 1, 223, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(74, 'Labour / Helper', 'labour-helper', NULL, NULL, 1, 224, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(75, 'Construction Labour', 'construction-labour', NULL, NULL, 1, 225, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(76, 'Painter', 'painter', NULL, NULL, 1, 226, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(77, 'Carpenter', 'carpenter', NULL, NULL, 1, 227, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(78, 'Welder', 'welder', NULL, NULL, 1, 228, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(79, 'Mechanic (Automobile)', 'mechanic-automobile', NULL, NULL, 1, 229, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(80, 'Bike Mechanic', 'bike-mechanic', NULL, NULL, 1, 230, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(81, 'Diesel Mechanic', 'diesel-mechanic', NULL, NULL, 1, 231, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(82, 'Technician (General)', 'technician-general', NULL, NULL, 1, 232, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(83, 'Field Technician', 'field-technician', NULL, NULL, 1, 233, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(84, 'Beautician', 'beautician', NULL, NULL, 1, 234, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(85, 'Hair Stylist', 'hair-stylist', NULL, NULL, 1, 235, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(86, 'Makeup Artist', 'makeup-artist', NULL, NULL, 1, 236, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(87, 'Spa Therapist', 'spa-therapist', NULL, NULL, 1, 237, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(88, 'Tailor / Stitching', 'tailor', NULL, NULL, 1, 238, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(89, 'Cook / Chef', 'cook-chef', NULL, NULL, 1, 239, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(90, 'Kitchen Helper', 'kitchen-helper', NULL, NULL, 1, 240, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(91, 'Waiter / Restaurant Staff', 'waiter-restaurant', NULL, NULL, 1, 241, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(92, 'Event Staff / Setup Crew', 'event-staff', NULL, NULL, 1, 245, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(93, 'Store Keeper', 'store-keeper', NULL, NULL, 1, 247, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(94, 'Packing Staff', 'packing-staff', NULL, NULL, 1, 248, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(95, 'Gardener / Mali', 'gardener', NULL, NULL, 1, 249, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(96, 'House Maid / Domestic Helper', 'house-maid', NULL, NULL, 1, 250, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(97, 'Nanny / Babysitter', 'nanny-babysitter', NULL, NULL, 1, 251, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(98, 'Ward Boy / Hospital Support', 'ward-boy', NULL, NULL, 1, 252, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(99, 'Pharmacy Assistant', 'pharmacy-assistant', NULL, NULL, 1, 253, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(100, 'Driving Instructor', 'driving-instructor', NULL, NULL, 1, 254, '2025-12-13 13:02:18', '2025-12-13 13:02:18'),
(101, 'Parking Attendant', 'parking-attendant', NULL, NULL, 1, 255, '2025-12-13 13:02:18', '2025-12-13 13:02:18');

-- --------------------------------------------------------

--
-- Table structure for table `job_engagement`
--

CREATE TABLE `job_engagement` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_id` bigint(20) UNSIGNED NOT NULL,
  `views_count` int(10) UNSIGNED DEFAULT 0,
  `saves_count` int(10) UNSIGNED DEFAULT 0,
  `shares_count` int(10) UNSIGNED DEFAULT 0,
  `applications_count` int(10) UNSIGNED DEFAULT 0,
  `engagement_score` decimal(5,2) DEFAULT 0.00,
  `last_viewed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_locations`
--

CREATE TABLE `job_locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_id` bigint(20) UNSIGNED NOT NULL,
  `country_id` bigint(20) UNSIGNED DEFAULT NULL,
  `state_id` bigint(20) UNSIGNED DEFAULT NULL,
  `city_id` bigint(20) UNSIGNED DEFAULT NULL,
  `custom_label` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_locations`
--

INSERT INTO `job_locations` (`id`, `job_id`, `country_id`, `state_id`, `city_id`, `custom_label`, `latitude`, `longitude`, `created_at`) VALUES
(1, 30, 2, 4, 5, NULL, NULL, NULL, '2025-12-30 13:33:53'),
(2, 31, 1, 2, 3, NULL, NULL, NULL, '2025-12-30 13:33:53'),
(3, 32, 1, 1, 6, NULL, NULL, NULL, '2025-12-30 13:33:53'),
(4, 34, 1, 5, 7, NULL, NULL, NULL, '2025-12-30 13:33:53'),
(5, 35, 1, 1, 8, NULL, NULL, NULL, '2025-12-30 13:33:53'),
(7, 33, 3, 6, 10, NULL, NULL, NULL, '2025-12-30 13:33:53'),
(10, 39, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-06 14:01:59');

-- --------------------------------------------------------

--
-- Table structure for table `job_review_queue`
--

CREATE TABLE `job_review_queue` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `review_reason` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_review_queue`
--

INSERT INTO `job_review_queue` (`id`, `job_id`, `employer_id`, `review_reason`, `status`, `reviewer_id`, `reviewed_at`, `comments`, `created_at`) VALUES
(1, 19, 10, 'No trust score available', 'pending', NULL, NULL, NULL, '2025-12-05 18:20:25'),
(2, 20, 10, 'No trust score available', 'pending', NULL, NULL, NULL, '2025-12-05 18:20:29'),
(3, 21, 2, 'No trust score available', 'pending', NULL, NULL, NULL, '2025-12-09 17:21:44'),
(4, 22, 2, 'No trust score available', 'pending', NULL, NULL, NULL, '2025-12-09 17:22:12'),
(5, 23, 2, 'No trust score available', 'pending', NULL, NULL, NULL, '2025-12-09 17:22:36'),
(6, 24, 2, 'No trust score available', 'pending', NULL, NULL, NULL, '2025-12-09 18:00:23'),
(7, 25, 2, 'No trust score available', 'pending', NULL, NULL, NULL, '2025-12-09 18:35:55'),
(8, 26, 10, 'No trust score available', 'pending', NULL, NULL, NULL, '2025-12-09 18:54:32'),
(9, 27, 10, 'No trust score available', 'pending', NULL, NULL, NULL, '2025-12-09 19:05:20'),
(10, 28, 10, 'No trust score available', 'pending', NULL, NULL, NULL, '2025-12-10 10:51:34'),
(11, 29, 10, 'No trust score available', 'pending', NULL, NULL, NULL, '2025-12-10 17:01:53'),
(12, 30, 10, 'No trust score available', 'pending', NULL, NULL, NULL, '2025-12-11 10:55:19'),
(13, 31, 2, 'No trust score available', 'pending', NULL, NULL, NULL, '2025-12-13 13:09:00'),
(14, 32, 2, 'No trust score available', 'pending', NULL, NULL, NULL, '2025-12-13 16:04:52'),
(15, 33, 2, 'No trust score available', 'pending', NULL, NULL, NULL, '2025-12-17 16:22:54'),
(16, 34, 10, 'No trust score available', 'pending', NULL, NULL, NULL, '2025-12-17 16:57:48'),
(17, 35, 10, 'No trust score available', 'pending', NULL, NULL, NULL, '2025-12-17 17:07:49'),
(18, 36, 2, 'No trust score available', 'pending', NULL, NULL, NULL, '2025-12-24 15:19:38'),
(19, 37, 2, 'No trust score available', 'pending', NULL, NULL, NULL, '2026-01-01 10:56:16'),
(20, 38, 2, 'No trust score available', 'pending', NULL, NULL, NULL, '2026-01-03 16:36:01'),
(21, 39, 11, 'No trust score available', 'pending', NULL, NULL, NULL, '2026-01-06 14:01:59');

-- --------------------------------------------------------

--
-- Table structure for table `job_saves_log`
--

CREATE TABLE `job_saves_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `saved_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_shares_log`
--

CREATE TABLE `job_shares_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `share_platform` varchar(50) DEFAULT NULL,
  `shared_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_skills`
--

CREATE TABLE `job_skills` (
  `job_id` bigint(20) UNSIGNED NOT NULL,
  `skill_id` bigint(20) UNSIGNED NOT NULL,
  `importance` tinyint(3) UNSIGNED DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_skills`
--

INSERT INTO `job_skills` (`job_id`, `skill_id`, `importance`) VALUES
(30, 50, 5),
(30, 51, 5),
(30, 52, 5),
(30, 53, 5),
(30, 54, 5),
(30, 55, 5),
(30, 56, 5),
(30, 57, 5),
(31, 58, 5),
(31, 59, 5),
(31, 60, 5),
(31, 61, 5),
(32, 1, 5),
(32, 22, 5),
(32, 27, 5),
(32, 29, 5),
(32, 48, 5),
(33, 19, 5),
(33, 62, 5),
(33, 63, 5),
(33, 64, 5),
(34, 19, 5),
(34, 67, 5),
(34, 68, 5),
(34, 69, 5),
(34, 70, 5),
(34, 71, 5),
(34, 72, 5),
(34, 73, 5);

-- --------------------------------------------------------

--
-- Table structure for table `job_titles`
--

CREATE TABLE `job_titles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `usage_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_titles`
--

INSERT INTO `job_titles` (`id`, `title`, `slug`, `category`, `is_active`, `usage_count`, `created_at`, `updated_at`) VALUES
(1, '2 Wheeler Mechanic', '2-wheeler-mechanic', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(2, '2 Wheeler Washer', '2-wheeler-washer', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(3, '2D/3D Architect', '2d-3d-architect', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(4, '2D/3D Interior Designer', '2d-3d-interior-designer', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(5, '2D Animator', '2d-animator', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(6, '3 Wheeler Driver', '3-wheeler-driver', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(7, '3D Animator', '3d-animator', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(8, '3D Artist', '3d-artist', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(9, '3D Designer', '3d-designer', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(10, '3D Graphic Designer', '3d-graphic-designer', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(11, '3D Modeling Designer', '3d-modeling-designer', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(12, '3D Visualizer', '3d-visualizer', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(13, '4 Wheeler Mechanic', '4-wheeler-mechanic', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(14, '4 Wheeler Washer', '4-wheeler-washer', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(15, 'AC-HCAV Technician', 'ac-hcav-technician', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(16, 'AC-HVAC Technician', 'ac-hvac-technician', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(17, 'AC Mechanic', 'ac-mechanic', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(18, 'AC Technician', 'ac-technician', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(19, 'AC Technician Helper', 'ac-technician-helper', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(20, 'Academic Advisor', 'academic-advisor', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(21, 'Academic Content Writer', 'academic-content-writer', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(22, 'Academic Coordinator', 'academic-coordinator', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(23, 'Academic Counsellor', 'academic-counsellor', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(24, 'Academic Counselor', 'academic-counselor', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(25, 'Academic Mentor', 'academic-mentor', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(26, 'Academic Registrar', 'academic-registrar', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(27, 'Account / Relationship Management - Non Voice', 'account-relationship-management---non-voice', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(28, 'Account / Relationship Management - Voice / Blended', 'account-relationship-management---voice-blended', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(29, 'Account Admin', 'account-admin', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(30, 'Account Assistant', 'account-assistant', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(31, 'Account Clerk', 'account-clerk', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(32, 'Account Client Relationship Associate - (Non-Voice)', 'account-client-relationship-associate---non-voice', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(33, 'Account Client Services Representative - (Non-Voice)', 'account-client-services-representative---non-voice', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(34, 'Account Consultant', 'account-consultant', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(35, 'Account Coordinator', 'account-coordinator', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(36, 'Account Executive', 'account-executive', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(37, 'Account Head', 'account-head', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(38, 'Account Manager', 'account-manager', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(39, 'Account Manager - (Non-Voice)', 'account-manager---non-voice', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(40, 'Account Relationship Consultant', 'account-relationship-consultant', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(41, 'Account Relationship Executive - (Non-Voice)', 'account-relationship-executive---non-voice', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(42, 'Account Specialist', 'account-specialist', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(43, 'Account Supervisor', 'account-supervisor', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(44, 'Account Trainee', 'account-trainee', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(45, 'Accountant', 'accountant', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(46, 'Accountant (Articleship)', 'accountant-articleship', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(47, 'Accountant (Part -Time)', 'accountant-part--time', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(48, 'Accountant/ Accounts Executive', 'accountant-accounts-executive', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(49, 'Accountant Cum Office Assistant', 'accountant-cum-office-assistant', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(50, 'Accounting Assistant', 'accounting-assistant', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(51, 'Accounting Associate', 'accounting-associate', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(52, 'Accounting Clerk', 'accounting-clerk', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(53, 'Accounting Intern', 'accounting-intern', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(54, 'Accounts & Audit Assistant', 'accounts-audit-assistant', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(55, 'Accounts Admin', 'accounts-admin', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(56, 'Accounts Administrator', 'accounts-administrator', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(57, 'Accounts And Admin Executive', 'accounts-and-admin-executive', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(58, 'Accounts And Finance Executive', 'accounts-and-finance-executive', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(59, 'Accounts Assistant', 'accounts-assistant', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(60, 'Accounts Associate', 'accounts-associate', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(61, 'Accounts Executive', 'accounts-executive', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(62, 'Accounts Head', 'accounts-head', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(63, 'Accounts Manager', 'accounts-manager', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(64, 'Accounts Officer', 'accounts-officer', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(65, 'Accounts Payable', 'accounts-payable', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(66, 'Accounts Receivable Officer', 'accounts-receivable-officer', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(67, 'Accounts Supervisor', 'accounts-supervisor', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(68, 'Accounts Teacher', 'accounts-teacher', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(69, 'Accounts Trainee', 'accounts-trainee', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(70, 'Acquisition Manager', 'acquisition-manager', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(71, 'Activities Coordinator', 'activities-coordinator', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(72, 'Admin Executive', 'admin-executive', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(73, 'Admin Incharge', 'admin-incharge', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(74, 'Admin Manager', 'admin-manager', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(75, 'Administration Manager', 'administration-manager', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(76, 'Administrative Assistant', 'administrative-assistant', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(77, 'Administrative Clerk', 'administrative-clerk', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(78, 'Administrative Coordinator', 'administrative-coordinator', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(79, 'Administrative Manager', 'administrative-manager', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(80, 'Administrative Officer', 'administrative-officer', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(81, 'Administrative Operations Manager', 'administrative-operations-manager', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(82, 'Administrative Receptionist', 'administrative-receptionist', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(83, 'Administrative Secretary', 'administrative-secretary', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(84, 'Administrative Supervisor', 'administrative-supervisor', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(85, 'Administrator', 'administrator', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(86, 'Admission Counsellor', 'admission-counsellor', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(87, 'Advertising Sales Executive', 'advertising-sales-executive', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(88, 'Advisor', 'advisor', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(89, 'Advocate', 'advocate', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(90, 'Affiliate Marketing', 'affiliate-marketing', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(91, 'Agency Development Manager', 'agency-development-manager', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(92, 'Agency Manager', 'agency-manager', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(93, 'Agency Partner', 'agency-partner', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(94, 'Agriculture Helper', 'agriculture-helper', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(95, 'Agriculture Worker', 'agriculture-worker', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(96, 'AI / ML Engineer', 'ai-ml-engineer', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(97, 'AI Business Analyst', 'ai-business-analyst', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(98, 'AI Consultant', 'ai-consultant', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(99, 'AI Content Generator', 'ai-content-generator', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(100, 'AI Engineer', 'ai-engineer', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(101, 'AI Ethics Consultant', 'ai-ethics-consultant', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(102, 'AI for IoT Developer', 'ai-for-iot-developer', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(103, 'AI Governance Lead', 'ai-governance-lead', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(104, 'AI GTM Specialist', 'ai-gtm-specialist', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(105, 'AI Infrastructure/Platform Architect', 'ai-infrastructure-platform-architect', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(106, 'AI Infrastructure/Platform Engineer', 'ai-infrastructure-platform-engineer', NULL, 1, 0, '2025-11-26 17:37:40', '2025-11-26 17:37:40'),
(107, 'AI Innovation Lead', 'ai-innovation-lead', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(108, 'AI Interaction Designer', 'ai-interaction-designer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(109, 'AI Policy Analyst', 'ai-policy-analyst', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(110, 'AI Product Manager', 'ai-product-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(111, 'AI Researcher', 'ai-researcher', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(112, 'AI Safety Engineer', 'ai-safety-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(113, 'AI Solutions Architect', 'ai-solutions-architect', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(114, 'AI Systems Engineer', 'ai-systems-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(115, 'AI Technical Program Manager', 'ai-technical-program-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(116, 'AI Trust & Risk Officer', 'ai-trust-risk-officer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(117, 'Air Hostess', 'air-hostess', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(118, 'Album Designer', 'album-designer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(119, 'Allied Health Manager', 'allied-health-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(120, 'Aluminium Fabricator Fitter', 'aluminium-fabricator-fitter', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(121, 'Aluminium Glass Fitter', 'aluminium-glass-fitter', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(122, 'Ambulance Driver', 'ambulance-driver', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(123, 'Ambulance Operator', 'ambulance-operator', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(124, 'Analyst', 'analyst', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(125, 'Analytical Chemist', 'analytical-chemist', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(126, 'Anchor', 'anchor', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(127, 'Android App Engineer', 'android-app-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(128, 'Android Application Developer', 'android-application-developer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(129, 'Android Developer', 'android-developer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(130, 'Android Developer Trainee', 'android-developer-trainee', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(131, 'Android Software Developer', 'android-software-developer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(132, 'Android Software Engineer', 'android-software-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(133, 'Angular Developer', 'angular-developer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(134, 'Animator', 'animator', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(135, 'App Developer', 'app-developer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(136, 'Apparel Sales Retail', 'apparel-sales-retail', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(137, 'Appliance Repair Specialist', 'appliance-repair-specialist', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(138, 'Application Analyst', 'application-analyst', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(139, 'Application Developer', 'application-developer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(140, 'Application Development Analyst', 'application-development-analyst', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(141, 'Application Development Associate', 'application-development-associate', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(142, 'Application Development Intern', 'application-development-intern', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(143, 'Application Engineer', 'application-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(144, 'Application Support Engineer', 'application-support-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(145, 'Apprentice', 'apprentice', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(146, 'Aptitude Coach', 'aptitude-coach', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(147, 'Aptitude Consultant', 'aptitude-consultant', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(148, 'Aptitude Educator', 'aptitude-educator', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(149, 'Aptitude Instructor', 'aptitude-instructor', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(150, 'Aptitude Specialist', 'aptitude-specialist', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(151, 'Aptitude Trainer', 'aptitude-trainer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(152, 'Aptitude Tutor', 'aptitude-tutor', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(153, 'Aquaguard Technician', 'aquaguard-technician', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(154, 'Arabic Teacher', 'arabic-teacher', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(155, 'Arc Welder', 'arc-welder', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(156, 'Architect', 'architect', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(157, 'Architecture', 'architecture', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(158, 'Area / Territory Manager', 'area-territory-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(159, 'Area Credit Manager', 'area-credit-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(160, 'Area Head', 'area-head', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(161, 'Area Manager', 'area-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(162, 'Area Sales Executive', 'area-sales-executive', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(163, 'Area Sales Manager', 'area-sales-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(164, 'Area Sales Manager (ASM)', 'area-sales-manager-asm', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(165, 'Area Sales Manager (B2B)', 'area-sales-manager-b2b', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(166, 'Area Sales Manager (B2C)', 'area-sales-manager-b2c', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(167, 'Area Sales Officer', 'area-sales-officer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(168, 'Area Service Manager', 'area-service-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(169, 'Area Supervisor', 'area-supervisor', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(170, 'Argon Welder', 'argon-welder', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(171, 'Art & Craft Teacher', 'art-craft-teacher', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(172, 'Article Assistant', 'article-assistant', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(173, 'Article Trainee', 'article-trainee', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(174, 'Articled Assistant', 'articled-assistant', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(175, 'Artist', 'artist', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(176, 'Arts Teacher', 'arts-teacher', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(177, 'Asp.net Developer', 'asp-net-developer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(178, 'Assembler', 'assembler', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(179, 'Assembly Fitter', 'assembly-fitter', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(180, 'Assembly Technician', 'assembly-technician', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(181, 'Asset Analyst', 'asset-analyst', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(182, 'Asset Operations Administrator', 'asset-operations-administrator', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(183, 'Asset Operations Director', 'asset-operations-director', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(184, 'Asset Operations Lead', 'asset-operations-lead', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(185, 'Asset Operations Manager', 'asset-operations-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(186, 'Asset Operations Specialist', 'asset-operations-specialist', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(187, 'Asset Operations Supervisor', 'asset-operations-supervisor', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(188, 'Assistant', 'assistant', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(189, 'Assistant Account Executive', 'assistant-account-executive', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(190, 'Assistant Account Manager', 'assistant-account-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(191, 'Assistant Accountant', 'assistant-accountant', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(192, 'Assistant Architect', 'assistant-architect', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(193, 'Assistant Audio Engineer', 'assistant-audio-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(194, 'Assistant Bell Services Manager', 'assistant-bell-services-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(195, 'Assistant Branch Manager', 'assistant-branch-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(196, 'Assistant Brand Manager', 'assistant-brand-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(197, 'Assistant Business Manager', 'assistant-business-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(198, 'Assistant Civil Engineer', 'assistant-civil-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(199, 'Assistant Clerk', 'assistant-clerk', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(200, 'Assistant Collection Manager', 'assistant-collection-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(201, 'Assistant Cook', 'assistant-cook', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(202, 'Assistant Department Manager', 'assistant-department-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(203, 'Assistant Design Engineer', 'assistant-design-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(204, 'Assistant Designer', 'assistant-designer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(205, 'Assistant Director', 'assistant-director', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(206, 'Assistant Doctor', 'assistant-doctor', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(207, 'Assistant E Commerce Manager', 'assistant-e-commerce-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(208, 'Assistant Electrical Engineer', 'assistant-electrical-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(209, 'Assistant Engineer', 'assistant-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(210, 'Assistant Facility Manager', 'assistant-facility-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(211, 'Assistant Fashion Designer', 'assistant-fashion-designer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(212, 'Assistant Finance Manager', 'assistant-finance-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(213, 'Assistant General Manager', 'assistant-general-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(214, 'Assistant General Manager (AGM)', 'assistant-general-manager-agm', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(215, 'Assistant Head Cashier', 'assistant-head-cashier', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(216, 'Assistant HR Manager', 'assistant-hr-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(217, 'Assistant Inspector', 'assistant-inspector', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(218, 'Assistant Inspector General', 'assistant-inspector-general', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(219, 'Assistant Manager', 'assistant-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(220, 'Assistant Manager - HR', 'assistant-manager---hr', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(221, 'Assistant Manager Business Development', 'assistant-manager-business-development', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(222, 'Assistant Manager Digital Marketing', 'assistant-manager-digital-marketing', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(223, 'Assistant Manager MIS', 'assistant-manager-mis', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(224, 'Assistant Manager Purchase', 'assistant-manager-purchase', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(225, 'Assistant Manager Sales And Marketing', 'assistant-manager-sales-and-marketing', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(226, 'Assistant Marketing Manager', 'assistant-marketing-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(227, 'Assistant Mechanical Engineer', 'assistant-mechanical-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(228, 'Assistant Merchandiser', 'assistant-merchandiser', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(229, 'Assistant Operations Manager', 'assistant-operations-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(230, 'Assistant Production Engineer', 'assistant-production-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(231, 'Assistant Production Manager', 'assistant-production-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(232, 'Assistant Professor', 'assistant-professor', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(233, 'Assistant Project Coordinator', 'assistant-project-coordinator', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(234, 'Assistant Project Manager', 'assistant-project-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(235, 'Assistant Receptionist', 'assistant-receptionist', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(236, 'Assistant Relationship Manager', 'assistant-relationship-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(237, 'Assistant Restaurant Manager', 'assistant-restaurant-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(238, 'Assistant Sales Manager', 'assistant-sales-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(239, 'Assistant Site Engineer', 'assistant-site-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(240, 'Assistant Software Engineer', 'assistant-software-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(241, 'Assistant Steward', 'assistant-steward', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(242, 'Assistant Store Incharge', 'assistant-store-incharge', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(243, 'Assistant Store Keeper', 'assistant-store-keeper', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(244, 'Assistant Store Manager', 'assistant-store-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(245, 'Assistant Store Manager (ASM)', 'assistant-store-manager-asm', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(246, 'Assistant Store Supervisor', 'assistant-store-supervisor', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(247, 'Assistant Surveyor', 'assistant-surveyor', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(248, 'Assistant System Engineer', 'assistant-system-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(249, 'Assistant Teacher', 'assistant-teacher', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(250, 'Assistant Technician', 'assistant-technician', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(251, 'Assistant Video Editor', 'assistant-video-editor', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(252, 'Assistant Warehouse Manager', 'assistant-warehouse-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(253, 'Associate', 'associate', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(254, 'Associate / Consultant', 'associate-consultant', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(255, 'Associate Account Manager', 'associate-account-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(256, 'Associate Advocate', 'associate-advocate', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(257, 'Associate Analyst', 'associate-analyst', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(258, 'Associate Application Developer', 'associate-application-developer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(259, 'Associate Bpo Executive', 'associate-bpo-executive', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(260, 'Associate Business Analyst', 'associate-business-analyst', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(261, 'Associate Coordinator', 'associate-coordinator', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(262, 'Associate Director', 'associate-director', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(263, 'Associate Engineer', 'associate-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(264, 'Associate Graphic Designer', 'associate-graphic-designer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(265, 'Associate Hardware', 'associate-hardware', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(266, 'Associate Hardware Engineer', 'associate-hardware-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(267, 'Associate Hotel Manager', 'associate-hotel-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(268, 'Associate Java Developer', 'associate-java-developer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(269, 'Associate Lawyer', 'associate-lawyer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(270, 'Associate Manager', 'associate-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(271, 'Associate Manager – Digital, GenAI, AI & ML', 'associate-manager-digital-genai-ai-ml', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(272, 'Associate Network Engineer', 'associate-network-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(273, 'Associate Project Manager', 'associate-project-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(274, 'Associate Software Developer', 'associate-software-developer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(275, 'Associate Software Engineer', 'associate-software-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(276, 'Associate Teacher', 'associate-teacher', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(277, 'Associate Technician', 'associate-technician', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(278, 'Associate Test Engineer', 'associate-test-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(279, 'Associate Trainer', 'associate-trainer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(280, 'ATM Operations Manager', 'atm-operations-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(281, 'Audiologist', 'audiologist', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(282, 'Audit / Account Assistant', 'audit-account-assistant', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(283, 'Audit Assistant', 'audit-assistant', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(284, 'Audit Assistant Manager', 'audit-assistant-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(285, 'Audit Associate', 'audit-associate', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(286, 'Audit Executive', 'audit-executive', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(287, 'Audit Intern', 'audit-intern', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(288, 'Audit Manager', 'audit-manager', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(289, 'Audit Officer', 'audit-officer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(290, 'Audit Senior', 'audit-senior', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(291, 'Audit Trainee', 'audit-trainee', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(292, 'Auditor', 'auditor', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(293, 'Auto Cleaning Specialist', 'auto-cleaning-specialist', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(294, 'Auto Detailing Specialist', 'auto-detailing-specialist', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(295, 'Auto Driver', 'auto-driver', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(296, 'Auto Mechanic', 'auto-mechanic', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(297, 'Auto Service Consultant', 'auto-service-consultant', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(298, 'Auto Service Technician', 'auto-service-technician', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(299, 'AutoCAD Designer', 'autocad-designer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(300, 'AutoCAD Operator', 'autocad-operator', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(301, 'Automation Developer', 'automation-developer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(302, 'Automation Engineer', 'automation-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(303, 'Automation Integration Engineer', 'automation-integration-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(304, 'Automation Process Engineer', 'automation-process-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(305, 'Automation Systems Engineer', 'automation-systems-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(306, 'Automation Test Engineer', 'automation-test-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(307, 'Automobile Cleaner', 'automobile-cleaner', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(308, 'Automobile Engineer', 'automobile-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(309, 'Automobile Fitter', 'automobile-fitter', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(310, 'Automobile Mechanic', 'automobile-mechanic', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(311, 'Automobile Painter', 'automobile-painter', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(312, 'Automobile Sales Agent', 'automobile-sales-agent', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(313, 'Automobile Sales Executive', 'automobile-sales-executive', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(314, 'Automotive Sales Representative', 'automotive-sales-representative', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(315, 'Automotive Service Engineer', 'automotive-service-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(316, 'Automotive Service Representative', 'automotive-service-representative', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(317, 'Automotive Service Specialist', 'automotive-service-specialist', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(318, 'Automotive Service Technician', 'automotive-service-technician', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(319, 'Automotive Technician', 'automotive-technician', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(320, 'Autonomous Vehicle Engineer', 'autonomous-vehicle-engineer', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(321, 'Auxiliary Nurse Midwife (ANM)', 'auxiliary-nurse-midwife-anm', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(322, 'Ayurveda Physician', 'ayurveda-physician', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(323, 'Ayurvedic Consultant', 'ayurvedic-consultant', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(324, 'Ayurvedic Doctor', 'ayurvedic-doctor', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(325, 'Ayurvedic Specialist', 'ayurvedic-specialist', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(326, 'Ayurvedic Therapist', 'ayurvedic-therapist', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(327, 'Baby Care', 'baby-care', NULL, 1, 0, '2025-11-26 17:37:41', '2025-11-26 17:37:41'),
(328, 'Baby Sitter', 'baby-sitter', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(329, 'Back End Developer', 'back-end-developer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(330, 'Back-End Executive', 'back-end-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(331, 'Back Office', 'back-office', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(332, 'Back Office Assistant', 'back-office-assistant', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(333, 'Back Office Coordinator', 'back-office-coordinator', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(334, 'Back Office Employee', 'back-office-employee', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(335, 'Back Office Executive', 'back-office-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(336, 'Back Office Manager', 'back-office-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(337, 'Back Office Marketing Executive', 'back-office-marketing-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(338, 'Back Office Operations', 'back-office-operations', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(339, 'Back Office Operations Executive', 'back-office-operations-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(340, 'Back Office Sales Assistant', 'back-office-sales-assistant', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(341, 'Back Office Staff', 'back-office-staff', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(342, 'Back Office Trainee', 'back-office-trainee', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(343, 'Backend Architect', 'backend-architect', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(344, 'Backend Developer', 'backend-developer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(345, 'Backend Developer Intern', 'backend-developer-intern', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(346, 'Backend Engineer', 'backend-engineer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(347, 'Backend Software Developer', 'backend-software-developer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(348, 'Backend Web Developer', 'backend-web-developer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(349, 'Baker', 'baker', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(350, 'Bakery Assistant', 'bakery-assistant', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(351, 'Bakery Chef', 'bakery-chef', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(352, 'Bakery Helper', 'bakery-helper', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(353, 'Bakery Manager', 'bakery-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(354, 'Bancassurance Manager', 'bancassurance-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(355, 'Bank Branch Manager', 'bank-branch-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(356, 'Bank Financial Advisor', 'bank-financial-advisor', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(357, 'Bank Teller', 'bank-teller', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(358, 'Bank Teller/Clerk Branch Manager', 'bank-teller-clerk-branch-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(359, 'Banker', 'banker', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(360, 'Banking Assistant', 'banking-assistant', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(361, 'Banking Executive', 'banking-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(362, 'Banquet Manager', 'banquet-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(363, 'Bar Tender', 'bar-tender', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(364, 'Barber', 'barber', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(365, 'Barista', 'barista', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(366, 'Bartender', 'bartender', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(367, 'Beautician', 'beautician', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(368, 'Beauty Advisor', 'beauty-advisor', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(369, 'Beauty Consultant', 'beauty-consultant', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(370, 'Beauty Director', 'beauty-director', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(371, 'Beauty Expert', 'beauty-expert', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(372, 'Beauty Salon Manager', 'beauty-salon-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(373, 'Beauty Salon Receptionist', 'beauty-salon-receptionist', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(374, 'Beauty Spa Manager', 'beauty-spa-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(375, 'Beauty Specialist', 'beauty-specialist', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(376, 'Beauty Studio Manager', 'beauty-studio-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(377, 'Beauty Technician', 'beauty-technician', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(378, 'Beauty Therapist', 'beauty-therapist', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(379, 'Bell Boy / Porter', 'bell-boy-porter', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(380, 'Bell Captain', 'bell-captain', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(381, 'Bell Services Supervisor', 'bell-services-supervisor', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(382, 'Bengali Cook', 'bengali-cook', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(383, 'Bengali Teacher', 'bengali-teacher', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(384, 'Beverage Manager', 'beverage-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(385, 'Beverage Specialist', 'beverage-specialist', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(386, 'BI Developer', 'bi-developer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(387, 'Bias Mitigation Engineer', 'bias-mitigation-engineer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(388, 'Bicycle Mechanic', 'bicycle-mechanic', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(389, 'Bidding / Auction / Proposal Manager', 'bidding-auction-proposal-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(390, 'Bike Mechanic', 'bike-mechanic', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(391, 'Bike Mechanic Helper', 'bike-mechanic-helper', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(392, 'Bike Rider', 'bike-rider', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(393, 'Biker', 'biker', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(394, 'Bilingual/Multilingual Support - Voice / Blended', 'bilingual-multilingual-support---voice-blended', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(395, 'Bilingual/Multilingual Support Executive - Non Voice', 'bilingual-multilingual-support-executive---non-voice', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(396, 'Billing / Records', 'billing-records', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(397, 'Billing Assistant', 'billing-assistant', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(398, 'Billing Clerk', 'billing-clerk', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(399, 'Billing Coordinator', 'billing-coordinator', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(400, 'Billing Executive', 'billing-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(401, 'Billing Manager', 'billing-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(402, 'Billing Officer', 'billing-officer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(403, 'Billing Specialist', 'billing-specialist', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(404, 'Billing Supervisor', 'billing-supervisor', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(405, 'Biology Lecturer', 'biology-lecturer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(406, 'Biology Teacher', 'biology-teacher', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(407, 'Biology Tutor', 'biology-tutor', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(408, 'Biomedical Engineer', 'biomedical-engineer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(409, 'Biomedical Technician', 'biomedical-technician', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(410, 'Biryani Cook', 'biryani-cook', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(411, 'Blood Collection Job', 'blood-collection-job', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(412, 'Booking Coordinator', 'booking-coordinator', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(413, 'Bouncer', 'bouncer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(414, 'Boutique Helper', 'boutique-helper', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(415, 'Boutique Tailor', 'boutique-tailor', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(416, 'BPO Executive', 'bpo-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(417, 'BPO Manager', 'bpo-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(418, 'BPO Staff', 'bpo-staff', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(419, 'BPO Tele calling', 'bpo-tele-calling', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(420, 'BPO Telecaller', 'bpo-telecaller', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(421, 'Branch Accountant', 'branch-accountant', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(422, 'Branch Client Relations Specialist', 'branch-client-relations-specialist', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(423, 'Branch Executive', 'branch-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(424, 'Branch Head', 'branch-head', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(425, 'Branch Manager', 'branch-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(426, 'Branch Operation Executive', 'branch-operation-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(427, 'Branch Operations Manager', 'branch-operations-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(428, 'Branch Relation Executive', 'branch-relation-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(429, 'Branch Relations Officer', 'branch-relations-officer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(430, 'Branch Relationship Associate', 'branch-relationship-associate', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(431, 'Branch Relationship Executive', 'branch-relationship-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(432, 'Branch Relationship Manager', 'branch-relationship-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(433, 'Branch Relationship Officer', 'branch-relationship-officer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(434, 'Branch Sales Executive', 'branch-sales-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(435, 'Branch Sales Manager', 'branch-sales-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(436, 'Branch Sales Manager (B2B)', 'branch-sales-manager-b2b', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(437, 'Branch Sales Manager (B2C)', 'branch-sales-manager-b2c', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(438, 'Brand Ambassador', 'brand-ambassador', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(439, 'Brand Assistant', 'brand-assistant', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(440, 'Brand Associate', 'brand-associate', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(441, 'Brand Coordinator', 'brand-coordinator', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(442, 'Brand Development Associate', 'brand-development-associate', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(443, 'Brand Development Manager', 'brand-development-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(444, 'Brand Executive', 'brand-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(445, 'Brand Management', 'brand-management', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(446, 'Brand Manager', 'brand-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(447, 'Brand Marketing Manager', 'brand-marketing-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(448, 'Brand Promoter', 'brand-promoter', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(449, 'Brand Representative', 'brand-representative', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(450, 'Brand Supervisor', 'brand-supervisor', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42');
INSERT INTO `job_titles` (`id`, `title`, `slug`, `category`, `is_active`, `usage_count`, `created_at`, `updated_at`) VALUES
(451, 'Bricklayer', 'bricklayer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(452, 'Bridge Design Engineer', 'bridge-design-engineer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(453, 'Broadcast Journalist', 'broadcast-journalist', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(454, 'Building Maintenance Manager', 'building-maintenance-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(455, 'Building Manager', 'building-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(456, 'Building Operations Manager', 'building-operations-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(457, 'Building Operator', 'building-operator', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(458, 'Building Services Manager', 'building-services-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(459, 'Burger Maker', 'burger-maker', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(460, 'Bus Driver', 'bus-driver', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(461, 'Business Analyst', 'business-analyst', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(462, 'Business Analyst Intern', 'business-analyst-intern', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(463, 'Business Associate', 'business-associate', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(464, 'Business Consultant', 'business-consultant', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(465, 'Business Correspondent Card Operations Executive', 'business-correspondent-card-operations-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(466, 'Business Developer', 'business-developer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(467, 'Business Developer (Internship)', 'business-developer-internship', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(468, 'Business Development', 'business-development', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(469, 'Business Development & Sales Executive', 'business-development-sales-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(470, 'Business Development- Sales Intern', 'business-development--sales-intern', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(471, 'Business Development Associate', 'business-development-associate', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(472, 'Business Development Associate (BDA)', 'business-development-associate-bda', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(473, 'Business Development Consultant', 'business-development-consultant', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(474, 'Business Development Coordinator', 'business-development-coordinator', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(475, 'Business Development Director', 'business-development-director', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(476, 'Business Development Executive', 'business-development-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(477, 'Business Development Executive (Bde)', 'business-development-executive-bde', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(478, 'Business Development Intern', 'business-development-intern', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(479, 'Business Development Manager', 'business-development-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(480, 'Business Development Manager (Bdm)', 'business-development-manager-bdm', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(481, 'Business Development Officer', 'business-development-officer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(482, 'Business Development Officer (BDO)', 'business-development-officer-bdo', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(483, 'Business Development Representative', 'business-development-representative', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(484, 'Business Development Specialist', 'business-development-specialist', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(485, 'Business Executive', 'business-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(486, 'Business Head', 'business-head', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(487, 'Business Initiatives Executive', 'business-initiatives-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(488, 'Business Initiatives Manager', 'business-initiatives-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(489, 'Business Intelligence Analyst', 'business-intelligence-analyst', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(490, 'Business Intelligence And Analytics Manager', 'business-intelligence-and-analytics-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(491, 'Business Intelligence Developer', 'business-intelligence-developer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(492, 'Business Intelligence Engineer', 'business-intelligence-engineer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(493, 'Business Intelligence Manager', 'business-intelligence-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(494, 'Business Journalist', 'business-journalist', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(495, 'Business Manager', 'business-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(496, 'Business News Journalist', 'business-news-journalist', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(497, 'Business Operations Analyst', 'business-operations-analyst', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(498, 'Business Operations Associate', 'business-operations-associate', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(499, 'Business Operations Manager', 'business-operations-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(500, 'Business Process Lead', 'business-process-lead', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(501, 'Business Promoter', 'business-promoter', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(502, 'Butcher', 'butcher', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(503, 'Butcher/Kasai', 'butcher-kasai', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(504, 'Butler', 'butler', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(505, 'CA Articleship', 'ca-articleship', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(506, 'Cab Driver', 'cab-driver', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(507, 'Cable Technician', 'cable-technician', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(508, 'CAD Engineer', 'cad-engineer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(509, 'Cafe Manager', 'cafe-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(510, 'Cafe Staff', 'cafe-staff', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(511, 'Calibration Engineer', 'calibration-engineer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(512, 'Call Center Agent', 'call-center-agent', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(513, 'Call Center Executive', 'call-center-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(514, 'Call Center Representative', 'call-center-representative', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(515, 'Call Centre Bpo Executive', 'call-centre-bpo-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(516, 'Call Quality Analyst', 'call-quality-analyst', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(517, 'Camera Operator', 'camera-operator', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(518, 'Camera Technician', 'camera-technician', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(519, 'Campus Ambassador', 'campus-ambassador', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(520, 'Car Alignment Technician', 'car-alignment-technician', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(521, 'Car Cleaner', 'car-cleaner', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(522, 'Car Detailer', 'car-detailer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(523, 'Car Driver', 'car-driver', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(524, 'Car Dry Cleaner', 'car-dry-cleaner', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(525, 'Car Electrician', 'car-electrician', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(526, 'Car Loan Sales Advisor', 'car-loan-sales-advisor', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(527, 'Car Loan Sales Executive', 'car-loan-sales-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(528, 'Car Mechanic', 'car-mechanic', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(529, 'Car Painter', 'car-painter', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(530, 'Car Painting Specialist', 'car-painting-specialist', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(531, 'Car Polisher', 'car-polisher', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(532, 'Car Sales Executive', 'car-sales-executive', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(533, 'Car Sales Retail', 'car-sales-retail', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(534, 'Car Technician', 'car-technician', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(535, 'Car Wash Attendant', 'car-wash-attendant', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(536, 'Car Washer', 'car-washer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(537, 'Care Taker', 'care-taker', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(538, 'Career Consultant', 'career-consultant', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(539, 'Career Counsellor', 'career-counsellor', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(540, 'Career Counselor', 'career-counselor', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(541, 'Caretaker', 'caretaker', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(542, 'Carpenter', 'carpenter', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(543, 'Carpenter Assistant', 'carpenter-assistant', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(544, 'Carpenter Foreman', 'carpenter-foreman', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(545, 'Carpenter Helper', 'carpenter-helper', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(546, 'Cart Puller', 'cart-puller', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(547, 'Casa Sales Officer', 'casa-sales-officer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(548, 'Cash Collection', 'cash-collection', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(549, 'Cash Officer', 'cash-officer', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(550, 'Cashier', 'cashier', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(551, 'Cashier Assistant', 'cashier-assistant', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(552, 'Cashier Lead', 'cashier-lead', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(553, 'Cashier Manager', 'cashier-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(554, 'Cashier Receptionist', 'cashier-receptionist', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(555, 'Cashier Supervisor', 'cashier-supervisor', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(556, 'Casino General Manager', 'casino-general-manager', NULL, 1, 0, '2025-11-26 17:37:42', '2025-11-26 17:37:42'),
(557, 'Casual Labor Supervisor', 'casual-labor-supervisor', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(558, 'Casual Worker', 'casual-worker', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(559, 'Catalog Administrator', 'catalog-administrator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(560, 'Catalog Coordinator', 'catalog-coordinator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(561, 'Catalog Executive', 'catalog-executive', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(562, 'Catalog Manager', 'catalog-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(563, 'Catalog Operations Manager', 'catalog-operations-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(564, 'Category Associate', 'category-associate', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(565, 'Category Consultant', 'category-consultant', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(566, 'Category Coordinator', 'category-coordinator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(567, 'Category Executive', 'category-executive', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(568, 'Category Head', 'category-head', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(569, 'Category Lead', 'category-lead', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(570, 'Category Leader', 'category-leader', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(571, 'Category Manager', 'category-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(572, 'Category Officer', 'category-officer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(573, 'Category Operations Executive', 'category-operations-executive', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(574, 'Category Planner', 'category-planner', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(575, 'Category Specialist', 'category-specialist', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(576, 'Catering', 'catering', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(577, 'Catering Assistant', 'catering-assistant', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(578, 'Catering Associate', 'catering-associate', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(579, 'Catering Coordinator', 'catering-coordinator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(580, 'Catering Executive', 'catering-executive', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(581, 'Catering Manager', 'catering-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(582, 'Catering Manager / Supervisor', 'catering-manager-supervisor', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(583, 'Catering Operations Manager', 'catering-operations-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(584, 'Catering Operations Supervisor', 'catering-operations-supervisor', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(585, 'Catering Service Manager', 'catering-service-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(586, 'Catering Supervisor', 'catering-supervisor', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(587, 'CCTV Operator', 'cctv-operator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(588, 'CCTV Technician', 'cctv-technician', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(589, 'Center Coordinator', 'center-coordinator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(590, 'Center Incharge', 'center-incharge', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(591, 'Center Manager', 'center-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(592, 'Center Officer', 'center-officer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(593, 'Center Operations Manager', 'center-operations-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(594, 'Center Supervisor', 'center-supervisor', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(595, 'Chaat Maker', 'chaat-maker', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(596, 'Channel Development Executive', 'channel-development-executive', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(597, 'Channel Development Manager', 'channel-development-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(598, 'Channel Development Specialist', 'channel-development-specialist', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(599, 'Channel Management Associate', 'channel-management-associate', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(600, 'Channel Management Executive', 'channel-management-executive', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(601, 'Channel Manager', 'channel-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(602, 'Channel Marketing Executive', 'channel-marketing-executive', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(603, 'Channel Operations Executive', 'channel-operations-executive', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(604, 'Channel Partner', 'channel-partner', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(605, 'Channel Relationship Executive', 'channel-relationship-executive', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(606, 'Channel Sales Executive', 'channel-sales-executive', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(607, 'Channel Sales Executive (Software)', 'channel-sales-executive-software', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(608, 'Channel Sales Manager', 'channel-sales-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(609, 'Channel Sales Representative', 'channel-sales-representative', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(610, 'Chartered Accountant', 'chartered-accountant', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(611, 'Chat Process Executive', 'chat-process-executive', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(612, 'Chat Support Executive', 'chat-support-executive', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(613, 'Chat Support Representative', 'chat-support-representative', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(614, 'Chatbot Developer', 'chatbot-developer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(615, 'Chauffeur', 'chauffeur', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(616, 'Chef', 'chef', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(617, 'Chef De Partie', 'chef-de-partie', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(618, 'Chemical Analyst', 'chemical-analyst', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(619, 'Chemical Engineer', 'chemical-engineer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(620, 'Chemical Process Engineer', 'chemical-process-engineer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(621, 'Chemist', 'chemist', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(622, 'Chemist - Production', 'chemist---production', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(623, 'Chemist - Quality Control', 'chemist---quality-control', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(624, 'Chemistry Educator', 'chemistry-educator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(625, 'Chemistry Instructor', 'chemistry-instructor', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(626, 'Chemistry Lecturer', 'chemistry-lecturer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(627, 'Chemistry Teacher', 'chemistry-teacher', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(628, 'Chief Accountant', 'chief-accountant', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(629, 'Chief Cook', 'chief-cook', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(630, 'Chief Engineer', 'chief-engineer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(631, 'Chief Marketing Officer', 'chief-marketing-officer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(632, 'Chief Officer', 'chief-officer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(633, 'Chief Risk Management Officer', 'chief-risk-management-officer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(634, 'Chief Risk Officer (Cro)', 'chief-risk-officer-cro', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(635, 'Chinese Chef', 'chinese-chef', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(636, 'Chinese Cook', 'chinese-cook', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(637, 'Chole Bhature Cook', 'chole-bhature-cook', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(638, 'Cinematographer', 'cinematographer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(639, 'Civil And Structural Draughtsman', 'civil-and-structural-draughtsman', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(640, 'Civil Design Engineer', 'civil-design-engineer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(641, 'Civil Drafter', 'civil-drafter', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(642, 'Civil Draughtsman', 'civil-draughtsman', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(643, 'Civil Engineer', 'civil-engineer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(644, 'Civil Engineering Assistant', 'civil-engineering-assistant', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(645, 'Civil Mason/Mason', 'civil-mason-mason', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(646, 'Civil Project Manager', 'civil-project-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(647, 'Civil Site Engineer', 'civil-site-engineer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(648, 'Civil Site Supervisor', 'civil-site-supervisor', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(649, 'Claims Analyst', 'claims-analyst', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(650, 'Claims Assistant', 'claims-assistant', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(651, 'Claims Examiner', 'claims-examiner', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(652, 'Claims Processor', 'claims-processor', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(653, 'Claims Representative', 'claims-representative', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(654, 'Cleaner', 'cleaner', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(655, 'Cleaning staff', 'cleaning-staff', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(656, 'Clerk', 'clerk', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(657, 'Client Relations Manager', 'client-relations-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(658, 'Client Relations Team Leader', 'client-relations-team-leader', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(659, 'Client Relationship Executive', 'client-relationship-executive', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(660, 'Client Relationship Manager', 'client-relationship-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(661, 'Client Relationship Officer', 'client-relationship-officer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(662, 'Client Relationship Partner', 'client-relationship-partner', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(663, 'Client Retention Associate', 'client-retention-associate', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(664, 'Client Retention Executive', 'client-retention-executive', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(665, 'Client Retention Specialist', 'client-retention-specialist', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(666, 'Client Service Executive', 'client-service-executive', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(667, 'Client Services Manager', 'client-services-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(668, 'Client Servicing', 'client-servicing', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(669, 'Client Servicing Executive', 'client-servicing-executive', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(670, 'Client Servicing Manager', 'client-servicing-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(671, 'Client Success Associate', 'client-success-associate', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(672, 'Client Success Manager', 'client-success-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(673, 'Client Support Manager', 'client-support-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(674, 'Clinic Assistant', 'clinic-assistant', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(675, 'Clinic Assistant Veterinary', 'clinic-assistant-veterinary', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(676, 'Clinic Helper', 'clinic-helper', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(677, 'Clinic Treatment Assistant', 'clinic-treatment-assistant', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(678, 'Clinical Assistant', 'clinical-assistant', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(679, 'Clinical Informatics Executive', 'clinical-informatics-executive', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(680, 'Clinical Nurse Specialist', 'clinical-nurse-specialist', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(681, 'Clinical Nutrition Specialist', 'clinical-nutrition-specialist', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(682, 'Clinical Pharmacist', 'clinical-pharmacist', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(683, 'Clinical Services Manager', 'clinical-services-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(684, 'Clothes Sales Retail', 'clothes-sales-retail', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(685, 'Clothing Designer', 'clothing-designer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(686, 'Cloud AI Engineer', 'cloud-ai-engineer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(687, 'Cloud Automation Engineer', 'cloud-automation-engineer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(688, 'Cloud Devops Engineer', 'cloud-devops-engineer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(689, 'Cloud Engineer', 'cloud-engineer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(690, 'Cloud Operations Engineer', 'cloud-operations-engineer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(691, 'Cloud Architect', 'cloud-architect', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(692, 'Cloud Administrator', 'cloud-administrator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(693, 'Cloud Developer', 'cloud-developer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(694, 'Cloud Consultant', 'cloud-consultant', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(695, 'Cloud Solutions Architect', 'cloud-solutions-architect', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(696, 'Cloud Data Engineer', 'cloud-data-engineer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(697, 'Cloud Security Architect', 'cloud-security-architect', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(698, 'Cloud Support Engineer', 'cloud-support-engineer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(699, 'CNC Laser Machine Operator', 'cnc-laser-machine-operator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(700, 'CNC Lathe Machine Operator', 'cnc-lathe-machine-operator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(701, 'CNC Lathe Operator', 'cnc-lathe-operator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(702, 'CNC Machine Operator', 'cnc-machine-operator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(703, 'CNC Machinist', 'cnc-machinist', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(704, 'CNC Mill Operator', 'cnc-mill-operator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(705, 'CNC Milling Operator', 'cnc-milling-operator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(706, 'CNC Operator', 'cnc-operator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(707, 'CNC Production Operator', 'cnc-production-operator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(708, 'CNC Programmer', 'cnc-programmer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(709, 'CNC Router Operator', 'cnc-router-operator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(710, 'CNC Turning Programmer', 'cnc-turning-programmer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(711, 'CO2 Welder', 'co2-welder', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(712, 'Coach', 'coach', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(713, 'Code Generation AI Engineer', 'code-generation-ai-engineer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(714, 'Coding Teacher', 'coding-teacher', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(715, 'Collection', 'collection', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(716, 'Collection Associate', 'collection-associate', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(717, 'Collection Coordinator', 'collection-coordinator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(718, 'Collection Executive / Officer', 'collection-executive-officer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(719, 'Collection Head', 'collection-head', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(720, 'Collection Manager', 'collection-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(721, 'Collection Officer', 'collection-officer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(722, 'Collection/Recovery Manager', 'collection-recovery-manager', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(723, 'Collection Specialist', 'collection-specialist', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(724, 'Collection Team Leader', 'collection-team-leader', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(725, 'Collection Telecaller', 'collection-telecaller', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(726, 'Collections Coordinator', 'collections-coordinator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(727, 'Collections Executive', 'collections-executive', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(728, 'Collections Officer', 'collections-officer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(729, 'Collections Operations Lead', 'collections-operations-lead', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(730, 'Collections Representative', 'collections-representative', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(731, 'Collections Specialist', 'collections-specialist', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(732, 'Collections Supervisor', 'collections-supervisor', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(733, 'Collections Team Leader', 'collections-team-leader', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(734, 'College Registrar', 'college-registrar', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(735, 'College Teacher', 'college-teacher', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(736, 'Commerce Teacher', 'commerce-teacher', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(737, 'Commercial Driver', 'commercial-driver', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(738, 'Commercial Loan Officer', 'commercial-loan-officer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(739, 'Commis (Commi 1 / 2 / 3)', 'commis-commi-1-2-3', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(740, 'Commis Chef', 'commis-chef', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(741, 'Communication Skills Trainer', 'communication-skills-trainer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(742, 'Company Driver', 'company-driver', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(743, 'Company Secretary', 'company-secretary', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(744, 'Compliance Engineer – AI Systems', 'compliance-engineer-ai-systems', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(745, 'Computer Accountant', 'computer-accountant', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(746, 'Computer Assistant', 'computer-assistant', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(747, 'Computer Designer (Printing Press)', 'computer-designer-printing-press', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(748, 'Computer Engineer', 'computer-engineer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(749, 'Computer Hardware Engineer', 'computer-hardware-engineer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(750, 'Computer Hardware Engineering Technician', 'computer-hardware-engineering-technician', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(751, 'Computer Hardware Technician', 'computer-hardware-technician', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(752, 'Computer Instructor', 'computer-instructor', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(753, 'Computer Operator', 'computer-operator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(754, 'Computer Operator Cum Office Assistant', 'computer-operator-cum-office-assistant', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(755, 'Computer Programmer', 'computer-programmer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(756, 'Computer Science Educator', 'computer-science-educator', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(757, 'Computer Science Teacher', 'computer-science-teacher', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(758, 'Computer Skills Trainer', 'computer-skills-trainer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(759, 'Computer Support Specialist', 'computer-support-specialist', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(760, 'Computer Teacher', 'computer-teacher', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(761, 'Computer Technician', 'computer-technician', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(762, 'Computer Trainer', 'computer-trainer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(763, 'Computer Tutor', 'computer-tutor', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(764, 'Computer Typist', 'computer-typist', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(765, 'Computer Vision Engineer', 'computer-vision-engineer', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(766, 'Concierge', 'concierge', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(767, 'Construction / Site Supervisor', 'construction-site-supervisor', NULL, 1, 0, '2025-11-26 17:37:43', '2025-11-26 17:37:43'),
(768, 'Construction Carpenter', 'construction-carpenter', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(769, 'Construction Helper', 'construction-helper', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(770, 'Construction Labour', 'construction-labour', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(771, 'Construction Worker', 'construction-worker', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(772, 'Consultant', 'consultant', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(773, 'Content Analyst', 'content-analyst', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(774, 'Content Creator', 'content-creator', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(775, 'Content Developer', 'content-developer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(776, 'Content Editor', 'content-editor', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(777, 'Content Engineer', 'content-engineer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(778, 'Content Manager', 'content-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(779, 'Content Marketing', 'content-marketing', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(780, 'Content Marketing / Writing', 'content-marketing-writing', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(781, 'Content Marketing Executive', 'content-marketing-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(782, 'Content Moderator', 'content-moderator', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(783, 'Content Reviewer', 'content-reviewer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(784, 'Content Writer', 'content-writer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(785, 'Content Writer & Editor', 'content-writer-editor', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(786, 'Content Writer Intern', 'content-writer-intern', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(787, 'Content Writing Intern', 'content-writing-intern', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(788, 'Continental Cook', 'continental-cook', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(789, 'Contract Labor Supervisor', 'contract-labor-supervisor', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(790, 'Contractor', 'contractor', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(791, 'Controller', 'controller', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(792, 'Cook', 'cook', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(793, 'Cook / Chef', 'cook-chef', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(794, 'Coordinator', 'coordinator', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(795, 'Copy Writer', 'copy-writer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(796, 'Copywriter', 'copywriter', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(797, 'Core PHP Developer', 'core-php-developer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(798, 'Corel Draw Designer', 'corel-draw-designer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(799, 'Corporate Account Executive', 'corporate-account-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(800, 'Corporate Controller', 'corporate-controller', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(801, 'Corporate Event Coordinator', 'corporate-event-coordinator', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(802, 'Corporate Event Manager', 'corporate-event-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(803, 'Corporate Journalist', 'corporate-journalist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(804, 'Corporate Lawyer', 'corporate-lawyer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(805, 'Corporate Product Sales', 'corporate-product-sales', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(806, 'Corporate Sales Executive', 'corporate-sales-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(807, 'Corporate Sales Manager', 'corporate-sales-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(808, 'Corporate Tie Up Executive', 'corporate-tie-up-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(809, 'Corporate Tie Ups', 'corporate-tie-ups', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(810, 'Corporate Trainer', 'corporate-trainer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(811, 'Cosmetologist', 'cosmetologist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(812, 'Cost Accountant', 'cost-accountant', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(813, 'Councillor', 'councillor', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(814, 'Counsellor', 'counsellor', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(815, 'Counselor', 'counselor', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(816, 'Counter Sales', 'counter-sales', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(817, 'Counter Sales Executive', 'counter-sales-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(818, 'Counter Sales Guy', 'counter-sales-guy', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(819, 'Counter Sales Manager', 'counter-sales-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(820, 'Counter Sales Representative', 'counter-sales-representative', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(821, 'Counter Salesman', 'counter-salesman', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(822, 'Counter Salesperson', 'counter-salesperson', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(823, 'Counter Service Specialist', 'counter-service-specialist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(824, 'Counter Staff', 'counter-staff', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(825, 'Courier Boy', 'courier-boy', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(826, 'Courier Delivery', 'courier-delivery', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(827, 'Crane Driver', 'crane-driver', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(828, 'Crane Operator', 'crane-operator', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(829, 'Creative Manager', 'creative-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(830, 'Credid Card Sales Representative', 'credid-card-sales-representative', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(831, 'Credit Analyst', 'credit-analyst', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(832, 'Credit Card Account Executive', 'credit-card-account-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(833, 'Credit Card Acquisition Executive', 'credit-card-acquisition-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(834, 'Credit Card Business Development Executive', 'credit-card-business-development-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(835, 'Credit Card Operations', 'credit-card-operations', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(836, 'Credit Card Sales', 'credit-card-sales', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(837, 'Credit Card Sales Associate', 'credit-card-sales-associate', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(838, 'Credit Card Sales Consultant', 'credit-card-sales-consultant', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(839, 'Credit Card Sales Executive', 'credit-card-sales-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(840, 'Credit Card Sales Officer', 'credit-card-sales-officer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(841, 'Credit Card Sales Specialist', 'credit-card-sales-specialist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(842, 'Credit Controller', 'credit-controller', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(843, 'Credit Manager', 'credit-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(844, 'Credit Officer', 'credit-officer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(845, 'Credit Operations Manager', 'credit-operations-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(846, 'Crew Member', 'crew-member', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(847, 'CRM Executive', 'crm-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(848, 'CRM Implementation Coordinator', 'crm-implementation-coordinator', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(849, 'CRM Implementation Executive', 'crm-implementation-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(850, 'CRM Manager', 'crm-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(851, 'CRM Technical Consultant', 'crm-technical-consultant', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(852, 'CS Articleship', 'cs-articleship', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(853, 'CS Management Trainee', 'cs-management-trainee', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(854, 'CSR Manager', 'csr-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(855, 'Curriculum Designer', 'curriculum-designer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(856, 'Customer Advocacy Manager', 'customer-advocacy-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(857, 'Customer Care Associate', 'customer-care-associate', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(858, 'Customer Care Associate (CCA)', 'customer-care-associate-cca', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(859, 'Customer Care Coordinator', 'customer-care-coordinator', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(860, 'Customer Care Executive', 'customer-care-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(861, 'Customer Care Executive (CCE)', 'customer-care-executive-cce', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(862, 'Customer Care Manager', 'customer-care-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(863, 'Customer Care Officer', 'customer-care-officer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(864, 'Customer Care Representative', 'customer-care-representative', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(865, 'Customer Care Specialist', 'customer-care-specialist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(866, 'Customer Care Supervisor', 'customer-care-supervisor', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(867, 'Customer Engagement Executive', 'customer-engagement-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(868, 'Customer Onboarding Executive - Non Voice', 'customer-onboarding-executive---non-voice', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(869, 'Customer Onboarding Executive - Voice / Blended', 'customer-onboarding-executive---voice-blended', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(870, 'Customer Relations Executive', 'customer-relations-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(871, 'Customer Relations Manager', 'customer-relations-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(872, 'Customer Relations Officer', 'customer-relations-officer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(873, 'Customer Relations Specialist', 'customer-relations-specialist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(874, 'Customer Relationship Executive', 'customer-relationship-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(875, 'Customer Relationship Executive (CRE)', 'customer-relationship-executive-cre', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(876, 'Customer Relationship Management (CRM)', 'customer-relationship-management-crm', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(877, 'Customer Relationship Management Consultant', 'customer-relationship-management-consultant', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(878, 'Customer Relationship Manager', 'customer-relationship-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(879, 'Customer Relationship Officer', 'customer-relationship-officer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(880, 'Customer Relationship Officer (CRO)', 'customer-relationship-officer-cro', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(881, 'Customer Representative Officer (CRO)', 'customer-representative-officer-cro', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(882, 'Customer Retention Agent', 'customer-retention-agent', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(883, 'Customer Retention Associate', 'customer-retention-associate', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(884, 'Customer Retention Executive - Non Voice', 'customer-retention-executive---non-voice', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(885, 'Customer Retention Executive - Voice / Blended', 'customer-retention-executive---voice-blended', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(886, 'Customer Retention Representative', 'customer-retention-representative', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(887, 'Customer Retention Specialist', 'customer-retention-specialist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(888, 'Customer Sales Executive', 'customer-sales-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(889, 'Customer Sales Representative', 'customer-sales-representative', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(890, 'Customer Sales Specialist', 'customer-sales-specialist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(891, 'Customer Service', 'customer-service', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(892, 'Customer Service Advisor', 'customer-service-advisor', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(893, 'Customer Service Agent', 'customer-service-agent', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44');
INSERT INTO `job_titles` (`id`, `title`, `slug`, `category`, `is_active`, `usage_count`, `created_at`, `updated_at`) VALUES
(894, 'Customer Service Assistant', 'customer-service-assistant', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(895, 'Customer Service Associate', 'customer-service-associate', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(896, 'Customer Service Associate (CSA)', 'customer-service-associate-csa', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(897, 'Customer Service Coordinator', 'customer-service-coordinator', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(898, 'Customer Service Executive', 'customer-service-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(899, 'Customer Service Manager', 'customer-service-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(900, 'Customer Service Officer', 'customer-service-officer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(901, 'Customer Service Operations Lead', 'customer-service-operations-lead', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(902, 'Customer Service Representative', 'customer-service-representative', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(903, 'Customer Service Specialist', 'customer-service-specialist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(904, 'Customer Service Supervisor', 'customer-service-supervisor', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(905, 'Customer Service Team Lead', 'customer-service-team-lead', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(906, 'Customer Service Team Supervisor', 'customer-service-team-supervisor', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(907, 'Customer Success Associate', 'customer-success-associate', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(908, 'Customer Success Executive', 'customer-success-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(909, 'Customer Success Executive (CSE)', 'customer-success-executive-cse', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(910, 'Customer Success Manager', 'customer-success-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(911, 'Customer Success Specialist', 'customer-success-specialist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(912, 'Customer Support Coordinator', 'customer-support-coordinator', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(913, 'Customer Support Engineer', 'customer-support-engineer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(914, 'Customer Support Executive', 'customer-support-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(915, 'Customer Support Leader', 'customer-support-leader', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(916, 'Customer Support Manager', 'customer-support-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(917, 'Customer Support Officer', 'customer-support-officer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(918, 'Customer Support Representative (CSR)', 'customer-support-representative-csr', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(919, 'Customer Support Specialist', 'customer-support-specialist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(920, 'Customer Support Team Leader', 'customer-support-team-leader', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(921, 'Customer Verification Executive', 'customer-verification-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(922, 'Customer Verification Job', 'customer-verification-job', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(923, 'Cutting Master', 'cutting-master', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(924, 'Cyber Coding Educator', 'cyber-coding-educator', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(925, 'Cyber Security Consultant', 'cyber-security-consultant', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(926, 'Dairy Farm Manager', 'dairy-farm-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(927, 'Dance Teacher', 'dance-teacher', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(928, 'Dance Trainer', 'dance-trainer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(929, 'Data Analyst', 'data-analyst', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(930, 'Data Analyst Intern', 'data-analyst-intern', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(931, 'Data Annotation Engineer', 'data-annotation-engineer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(932, 'Data Annotator', 'data-annotator', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(933, 'Data Engineer', 'data-engineer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(934, 'Data Entry / Mis', 'data-entry-mis', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(935, 'Data Entry Executive', 'data-entry-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(936, 'Data Entry Manager', 'data-entry-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(937, 'Data Entry Operator', 'data-entry-operator', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(938, 'Data Entry Operator (DEO)', 'data-entry-operator-deo', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(939, 'Data Entry Specialist', 'data-entry-specialist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(940, 'Data Entry Supervisor', 'data-entry-supervisor', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(941, 'Data Operator', 'data-operator', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(942, 'Data Processing Officer', 'data-processing-officer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(943, 'Data Science Intern', 'data-science-intern', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(944, 'Data Scientist', 'data-scientist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(945, 'Database Administrator', 'database-administrator', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(946, 'Database Analyst', 'database-analyst', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(947, 'Database Developer', 'database-developer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(948, 'Database Engineer', 'database-engineer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(949, 'Database Manager', 'database-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(950, 'Database Specialist', 'database-specialist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(951, 'Debt Collection Specialist', 'debt-collection-specialist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(952, 'Debt Collector', 'debt-collector', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(953, 'Debt Recovery Agent', 'debt-recovery-agent', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(954, 'Debt Recovery Officer', 'debt-recovery-officer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(955, 'Debt Recovery Specialist', 'debt-recovery-specialist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(956, 'Deep Learning Researcher', 'deep-learning-researcher', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(957, 'Delivery Associate', 'delivery-associate', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(958, 'Delivery Boy', 'delivery-boy', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(959, 'Delivery Boy Biker', 'delivery-boy-biker', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(960, 'Delivery Driver', 'delivery-driver', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(961, 'Delivery Executive', 'delivery-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(962, 'Delivery Girl', 'delivery-girl', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(963, 'Delivery Head', 'delivery-head', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(964, 'Delivery Lead', 'delivery-lead', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(965, 'Delivery Manager', 'delivery-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(966, 'Delivery Partner', 'delivery-partner', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(967, 'Delivery Person', 'delivery-person', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(968, 'Delivery Supervisor', 'delivery-supervisor', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(969, 'Dental Assistant', 'dental-assistant', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(970, 'Dental Assistant/Receptionist', 'dental-assistant-receptionist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(971, 'Dental Chairside Assistant', 'dental-chairside-assistant', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(972, 'Dental Clinic Assistant', 'dental-clinic-assistant', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(973, 'Dental Consultant', 'dental-consultant', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(974, 'Dental Hygienist', 'dental-hygienist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(975, 'Dental Nurse', 'dental-nurse', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(976, 'Dental Officer', 'dental-officer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(977, 'Dental Physician', 'dental-physician', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(978, 'Dental Surgeon', 'dental-surgeon', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(979, 'Dental Surgical Assistant', 'dental-surgical-assistant', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(980, 'Dentist', 'dentist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(981, 'Department / Floor Manager', 'department-floor-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(982, 'Department Manager', 'department-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(983, 'Deputy Branch Manager', 'deputy-branch-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(984, 'Deputy Inspector General', 'deputy-inspector-general', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(985, 'Deputy Manager', 'deputy-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(986, 'Deputy Manager Finance', 'deputy-manager-finance', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(987, 'Deputy Manager Marketing', 'deputy-manager-marketing', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(988, 'Deputy Project Manager', 'deputy-project-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(989, 'Deputy Sales Manager', 'deputy-sales-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(990, 'Deputy Supervisor', 'deputy-supervisor', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(991, 'Dermatologist', 'dermatologist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(992, 'Design And Development Engineer', 'design-and-development-engineer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(993, 'Design Engineer', 'design-engineer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(994, 'Design Manager', 'design-manager', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(995, 'Design Specialist', 'design-specialist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(996, 'Designer', 'designer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(997, 'Designer Tailor', 'designer-tailor', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(998, 'Desktop Engineer', 'desktop-engineer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(999, 'Desktop Publisher', 'desktop-publisher', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(1000, 'Desktop Publishing Operator', 'desktop-publishing-operator', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(1001, 'Desktop Support Engineer', 'desktop-support-engineer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(1002, 'Desktop Support Executive', 'desktop-support-executive', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(1003, 'Desktop Support Specialist', 'desktop-support-specialist', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(1004, 'Desktop Support Technician', 'desktop-support-technician', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(1005, 'Desktop Technician', 'desktop-technician', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(1006, 'Developer', 'developer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(1007, 'Electrical Engineer', 'electrical-engineer', NULL, 1, 0, '2025-11-26 17:37:44', '2025-11-26 17:37:44'),
(1008, 'Electrician', 'electrician', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1009, 'Electrical Design Engineer', 'electrical-design-engineer', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1010, 'Electrical Technician', 'electrical-technician', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1011, 'Electrical and Maintenance Engineer', 'electrical-and-maintenance-engineer', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1012, 'Electrical Designer', 'electrical-designer', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1013, 'Electrical Supervisor', 'electrical-supervisor', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1014, 'Electrical Site Engineer', 'electrical-site-engineer', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1015, 'Electrical Draughtsman', 'electrical-draughtsman', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1016, 'Electrical Manager', 'electrical-manager', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1017, 'Hr Fresher', 'hr-fresher', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1018, 'Hr Assistant', 'hr-assistant', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1019, 'Hris Analyst', 'hris-analyst', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1020, 'Hr Abap Consultant', 'hr-abap-consultant', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1021, 'HRM Functional Consultant', 'hrm-functional-consultant', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1022, 'HRM Consultant', 'hrm-consultant', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1023, 'Hr Data Analyst', 'hr-data-analyst', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1024, 'Hr Functional Consultant', 'hr-functional-consultant', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1025, 'HR and Accountant', 'hr-and-accountant', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1026, 'Hr Business Analyst', 'hr-business-analyst', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1027, 'Plumber', 'plumber', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1028, 'Plumber Technician', 'plumber-technician', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1029, 'Plumber Training', 'plumber-training', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1030, 'Plumber Lead', 'plumber-lead', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1031, 'Plumber Contractor', 'plumber-contractor', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1032, 'Plumber Instructor', 'plumber-instructor', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1033, 'Plumber Fitter', 'plumber-fitter', NULL, 1, 0, '2025-11-26 17:37:45', '2025-11-26 17:37:45'),
(1034, 'Web Developer', 'web-developer', NULL, 1, 0, '2025-11-26 17:42:46', '2025-11-26 17:42:46'),
(1035, 'Web Designer', 'web-designer', NULL, 1, 0, '2025-11-26 17:42:46', '2025-11-26 17:42:46'),
(1036, 'Web Application Developer', 'web-application-developer', NULL, 1, 0, '2025-11-26 17:42:46', '2025-11-26 17:42:46'),
(1037, 'Web Driver', 'web-driver', NULL, 1, 0, '2025-11-26 17:42:46', '2025-11-26 17:42:46'),
(1038, 'Web And Software Developer', 'web-and-software-developer', NULL, 1, 0, '2025-11-26 17:42:46', '2025-11-26 17:42:46'),
(1039, 'Webmethods Developer', 'webmethods-developer', NULL, 1, 0, '2025-11-26 17:42:46', '2025-11-26 17:42:46'),
(1040, 'Web Content Writer', 'web-content-writer', NULL, 1, 0, '2025-11-26 17:42:46', '2025-11-26 17:42:46'),
(1041, 'Websphere Administrator', 'websphere-administrator', NULL, 1, 0, '2025-11-26 17:42:46', '2025-11-26 17:42:46'),
(1042, 'Weblogic Administrator', 'weblogic-administrator', NULL, 1, 0, '2025-11-26 17:42:46', '2025-11-26 17:42:46'),
(1043, 'Websphere Application Server Administrator', 'websphere-application-server-administrator', NULL, 1, 0, '2025-11-26 17:42:46', '2025-11-26 17:42:46');

-- --------------------------------------------------------

--
-- Table structure for table `job_views`
--

CREATE TABLE `job_views` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `candidate_id` bigint(20) UNSIGNED NOT NULL,
  `job_id` bigint(20) UNSIGNED NOT NULL,
  `viewed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_views`
--

INSERT INTO `job_views` (`id`, `candidate_id`, `job_id`, `viewed_at`) VALUES
(22, 5, 30, '2025-12-11 10:59:55'),
(25, 12, 31, '2025-12-13 13:52:24'),
(26, 12, 30, '2025-12-13 14:21:59'),
(27, 12, 32, '2025-12-13 16:07:55'),
(28, 12, 30, '2025-12-15 13:28:08'),
(29, 12, 32, '2025-12-15 13:30:05'),
(30, 12, 31, '2025-12-15 17:34:47'),
(31, 12, 32, '2025-12-16 10:56:43'),
(32, 12, 31, '2025-12-16 10:57:28'),
(33, 12, 30, '2025-12-16 13:52:13'),
(34, 12, 32, '2025-12-17 10:55:33'),
(35, 12, 31, '2025-12-17 11:13:00'),
(36, 12, 30, '2025-12-17 12:00:11'),
(37, 4, 33, '2025-12-17 16:55:51'),
(38, 12, 32, '2025-12-18 12:52:02'),
(39, 12, 35, '2025-12-18 14:22:52'),
(40, 12, 33, '2025-12-18 14:23:18'),
(41, 12, 31, '2025-12-18 14:23:22'),
(42, 12, 30, '2025-12-18 15:40:36'),
(43, 12, 32, '2025-12-19 12:30:36'),
(44, 12, 33, '2025-12-20 11:59:54'),
(45, 12, 32, '2025-12-20 12:17:59'),
(46, 12, 31, '2025-12-20 13:51:35'),
(47, 12, 32, '2025-12-22 15:34:41'),
(48, 12, 31, '2025-12-22 18:31:01'),
(49, 12, 32, '2025-12-23 10:36:10'),
(50, 12, 33, '2025-12-23 10:51:46'),
(51, 12, 35, '2025-12-23 10:59:01'),
(52, 12, 31, '2025-12-23 11:39:08'),
(53, 12, 32, '2025-12-24 16:19:14'),
(54, 12, 33, '2025-12-24 18:19:17'),
(55, 12, 32, '2025-12-25 11:02:34'),
(56, 12, 33, '2025-12-25 14:16:11'),
(57, 12, 31, '2025-12-25 14:33:33'),
(58, 12, 32, '2025-12-26 12:11:40'),
(59, 12, 32, '2025-12-27 12:05:20'),
(60, 12, 32, '2025-12-29 16:50:24'),
(61, 12, 34, '2025-12-29 17:43:53'),
(62, 12, 32, '2025-12-31 11:05:20'),
(63, 12, 33, '2025-12-31 11:05:31'),
(64, 12, 31, '2025-12-31 15:31:23'),
(65, 12, 34, '2026-01-01 10:41:36'),
(66, 12, 32, '2026-01-01 10:43:15'),
(67, 12, 31, '2026-01-01 13:26:36'),
(68, 12, 32, '2026-01-02 11:59:39'),
(69, 12, 33, '2026-01-02 11:59:45'),
(70, 12, 31, '2026-01-02 16:58:07'),
(71, 12, 32, '2026-01-03 12:01:09'),
(72, 12, 31, '2026-01-03 13:14:06'),
(73, 12, 33, '2026-01-03 14:02:19'),
(74, 12, 32, '2026-01-05 16:46:45'),
(75, 14, 39, '2026-01-06 14:18:27');

-- --------------------------------------------------------

--
-- Table structure for table `job_views_log`
--

CREATE TABLE `job_views_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `viewed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `user_type` enum('employer','candidate','admin') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_successful` tinyint(1) DEFAULT 1,
  `failure_reason` varchar(255) DEFAULT NULL,
  `logged_in_at` datetime DEFAULT current_timestamp(),
  `logged_out_at` datetime DEFAULT NULL,
  `session_duration_seconds` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `conversation_id` bigint(20) UNSIGNED NOT NULL,
  `sender_user_id` bigint(20) UNSIGNED NOT NULL,
  `body` text DEFAULT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_user_id`, `body`, `attachments`, `is_read`, `created_at`) VALUES
(60, 2, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 1, '2025-12-04 15:08:37'),
(61, 2, 10, 'Hello, I am interested in this position and would like to know more.', NULL, 1, '2025-12-04 15:08:51'),
(62, 2, 10, 'hello ibc', NULL, 1, '2025-12-04 15:09:00'),
(63, 2, 3, 'who are u tell me what happned', NULL, 1, '2025-12-04 15:09:18'),
(66, 5, 10, 'Hello, I am interested in this position and would like to know more.', NULL, 1, '2025-12-06 17:21:19'),
(68, 2, 3, '', '[{\"name\":\"Mindware-infotech.png\",\"url\":\"http:\\/\\/localhost\\/mindinfotech\\/public\\/storage\\/uploads\\/messages\\/2\\/693419bed2823_Mindware-infotech.png\",\"size\":194823,\"type\":\"image\\/png\"}]', 1, '2025-12-06 17:25:43'),
(69, 2, 10, 'Hello, I am interested in this position and would like to know more.', NULL, 1, '2025-12-06 17:25:52'),
(70, 2, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 0, '2025-12-06 17:48:59'),
(71, 2, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 0, '2025-12-06 19:07:41'),
(72, 2, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 0, '2025-12-09 18:06:05'),
(73, 5, 30, 'Hello, I saw your application and would like to discuss further.', NULL, 1, '2025-12-11 11:01:32'),
(74, 5, 30, 'hello tags india', NULL, 1, '2025-12-11 11:01:50'),
(75, 5, 10, 'Hello, I am interested in this position and would like to know more.', NULL, 1, '2025-12-11 11:01:56'),
(76, 6, 35, 'Hello, I am interested in this position and would like to know more.', NULL, 1, '2025-12-13 14:21:32'),
(77, 6, 35, 'Hello, I am interested in this position and would like to know more.', NULL, 1, '2025-12-13 16:43:35'),
(78, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 1, '2025-12-13 17:18:37'),
(79, 6, 35, 'Name: TEST\nEmail: hr@gmail.com\nPhone: 9989856595\n\nMessage:\ntest', NULL, 1, '2025-12-13 17:33:35'),
(80, 6, 3, 'hii', NULL, 1, '2025-12-13 17:34:08'),
(81, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 1, '2025-12-13 17:57:03'),
(82, 6, 3, 'hii', NULL, 1, '2025-12-13 18:14:10'),
(83, 6, 35, 'Regarding application #13', NULL, 1, '2025-12-13 18:28:35'),
(84, 6, 35, 'Regarding application #13', NULL, 1, '2025-12-13 18:58:38'),
(85, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 1, '2025-12-13 18:58:58'),
(86, 6, 3, 'hii', NULL, 1, '2025-12-13 18:59:01'),
(87, 6, 35, 'Regarding application #13', NULL, 1, '2025-12-15 17:32:45'),
(88, 6, 35, 'Regarding application #12', NULL, 1, '2025-12-15 17:34:43'),
(89, 6, 35, 'Regarding application #12', NULL, 1, '2025-12-16 10:57:22'),
(90, 6, 35, 'Regarding application #13', NULL, 1, '2025-12-17 11:24:37'),
(91, 6, 35, 'Regarding application #13', NULL, 1, '2025-12-17 11:41:30'),
(92, 6, 35, 'Regarding application #13', NULL, 1, '2025-12-17 11:47:32'),
(93, 6, 35, 'Regarding application #12', NULL, 1, '2025-12-17 12:10:30'),
(94, 6, 35, 'jkdfghdfh', NULL, 1, '2025-12-17 12:20:35'),
(95, 6, 35, 'Regarding application #13', NULL, 1, '2025-12-18 12:42:19'),
(96, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 1, '2025-12-18 13:30:34'),
(97, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 1, '2025-12-18 14:10:32'),
(98, 6, 35, 'hii', NULL, 1, '2025-12-18 14:58:40'),
(99, 6, 35, 'hii', NULL, 1, '2025-12-18 17:37:39'),
(100, 6, 35, 'Name: Raj \nEmail: hr@gmail.com\nPhone: 654646464646\n\nMessage:\ndgfdg dfg fdh', NULL, 1, '2025-12-18 17:40:27'),
(101, 6, 35, 'Regarding application #12', NULL, 1, '2025-12-18 18:01:13'),
(102, 6, 35, 'Regarding application #13', NULL, 1, '2025-12-20 11:24:41'),
(103, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 1, '2025-12-20 12:16:44'),
(104, 6, 3, 'hii', NULL, 1, '2025-12-20 12:16:49'),
(105, 6, 35, 'Regarding application #13', NULL, 1, '2025-12-20 12:17:45'),
(106, 6, 35, 'Regarding application #13', NULL, 1, '2025-12-20 13:43:15'),
(107, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 1, '2025-12-20 14:08:36'),
(108, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 1, '2025-12-20 14:12:30'),
(109, 6, 3, 'Hii', NULL, 1, '2025-12-20 16:58:51'),
(110, 6, 3, 'how are you hope u are doing well', NULL, 1, '2025-12-20 16:59:06'),
(111, 6, 35, 'hii', NULL, 1, '2025-12-20 17:00:25'),
(112, 2, 3, '', '[{\"name\":\"WhatsApp Image 2025-12-19 at 5.24.57 PM.jpeg\",\"url\":\"http:\\/\\/localhost:8000\\/storage\\/uploads\\/messages\\/2\\/694688e3e437d_WhatsApp Image 2025-12-19 at 5.24.57 PM.jpeg\",\"size\":125472,\"type\":\"image\\/jpeg\"}]', 0, '2025-12-20 17:00:43'),
(113, 6, 35, 'Regarding application #13', NULL, 1, '2025-12-22 11:07:30'),
(114, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 1, '2025-12-22 14:08:02'),
(115, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 1, '2025-12-24 11:19:11'),
(116, 6, 35, 'hii', NULL, 1, '2025-12-26 17:12:59'),
(117, 7, 35, 'Regarding application #14', NULL, 0, '2025-12-29 17:44:21'),
(118, 7, 35, 'hii', NULL, 0, '2025-12-29 17:44:28'),
(119, 7, 35, 'Regarding application #14', NULL, 0, '2025-12-31 15:33:44'),
(120, 6, 35, 'Regarding application #12', NULL, 1, '2026-01-02 16:53:05'),
(121, 6, 35, 'Regarding application #12', NULL, 1, '2026-01-03 12:57:29'),
(122, 6, 35, '[Attachment: 5_6251108935487462564.pdf]', '[{\"name\":\"5_6251108935487462564.pdf\",\"url\":\"http:\\/\\/localhost:8000\\/storage\\/uploads\\/messages\\/6\\/6958c7e4e33e9_5_6251108935487462564.pdf\",\"size\":972513,\"type\":\"application\\/pdf\"}]', 1, '2026-01-03 13:10:20'),
(123, 6, 35, 'hello pkr', NULL, 1, '2026-01-03 13:12:21'),
(124, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 1, '2026-01-03 14:23:06'),
(125, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 1, '2026-01-03 16:39:03'),
(126, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 1, '2026-01-03 17:13:49'),
(127, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 1, '2026-01-05 16:17:12'),
(128, 6, 35, '[Attachment: AmanResume.pdf] hlo indian barcode', '[{\"name\":\"AmanResume.pdf\",\"url\":\"http:\\/\\/localhost:8000\\/storage\\/uploads\\/messages\\/6\\/695b9dc962e18_AmanResume.pdf\",\"size\":81858,\"type\":\"application\\/pdf\"}]', 1, '2026-01-05 16:47:29'),
(129, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 0, '2026-01-05 17:43:06'),
(130, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 0, '2026-01-05 18:28:05'),
(131, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 0, '2026-01-06 10:38:59'),
(132, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 0, '2026-01-06 14:31:52'),
(133, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 0, '2026-01-06 14:35:05'),
(134, 8, 36, 'Hello, I saw your application and would like to discuss further.', NULL, 0, '2026-01-06 16:10:05'),
(135, 9, 36, 'Hello, I saw your application and would like to discuss further.', NULL, 0, '2026-01-06 16:10:38'),
(136, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 0, '2026-01-06 16:16:00'),
(137, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 0, '2026-01-06 18:10:48'),
(138, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 0, '2026-01-06 18:40:08'),
(139, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 0, '2026-01-06 18:44:52'),
(140, 6, 3, 'vvgjgh', NULL, 0, '2026-01-07 11:00:17'),
(141, 6, 3, 'Hello, I saw your application and would like to discuss further.', NULL, 0, '2026-01-07 11:36:26'),
(142, 6, 3, 'Hi Candidate,\n\nYour application status for the Web And Software Developer position at Indian Barcode Corporation has been updated.\n\nNew Status: Hired\n\nView your application dashboard for more details.', NULL, 0, '2026-01-07 11:59:10'),
(143, 8, 36, 'Hello, I saw your application and would like to discuss further.', NULL, 0, '2026-01-07 12:24:09'),
(144, 8, 36, 'Hi Candidate,\n\nYour application status for the Web And Software Developer position at PKR Techvision has been updated.\n\nNew Status: Applied\n\nView your application dashboard for more details.', NULL, 0, '2026-01-07 12:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('job_match','application_update','interview_scheduled','message','profile_view','system') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(512) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(45, 10, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/2', 0, '2025-12-04 15:08:37'),
(46, 3, 'message', 'New message from candidate', 'hello ibc', '/employer/messages?conversation=2', 0, '2025-12-04 15:09:00'),
(47, 10, 'message', 'New message from employer', 'who are u tell me what happned', '/candidate/chat/2', 0, '2025-12-04 15:09:18'),
(50, 30, 'message', 'New message from candidate', 'Hello, I am interested in this position and would like to know more.', '/employer/messages?conversation=5', 0, '2025-12-06 17:21:20'),
(52, 10, 'message', 'New message from employer', '', '/candidate/chat/2', 0, '2025-12-06 17:25:43'),
(53, 10, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/2', 0, '2025-12-06 17:48:59'),
(54, 10, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/2', 0, '2025-12-06 19:07:41'),
(55, 10, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/2', 0, '2025-12-09 18:06:05'),
(56, 10, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/5', 0, '2025-12-11 11:01:32'),
(57, 10, 'message', 'New message from employer', 'hello tags india', '/candidate/chat/5', 0, '2025-12-11 11:01:50'),
(58, 3, 'message', 'New message from candidate', 'Hello, I am interested in this position and would like to know more.', '/employer/messages?conversation=6', 0, '2025-12-13 14:21:32'),
(59, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 1, '2025-12-13 17:18:37'),
(60, 35, 'message', 'New message from employer', 'hii', '/candidate/chat/6', 1, '2025-12-13 17:34:08'),
(61, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 1, '2025-12-13 17:57:03'),
(62, 35, 'message', 'New message from employer', 'hii', '/candidate/chat/6', 1, '2025-12-13 18:14:10'),
(63, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 1, '2025-12-13 18:58:58'),
(64, 35, 'message', 'New message from employer', 'hii', '/candidate/chat/6', 1, '2025-12-13 18:59:01'),
(65, 3, 'message', 'New message from candidate', 'jkdfghdfh', '/employer/messages?conversation=6', 0, '2025-12-17 12:20:35'),
(66, 30, '', 'Job Published Successfully!', 'Your job posting \'Delivery Boy\' has been approved and published. It\'s now live and visible to candidates.', '/employer/jobs/delivery-boy', 0, '2025-12-17 17:11:29'),
(67, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 1, '2025-12-18 13:30:34'),
(68, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 1, '2025-12-18 14:10:32'),
(69, 3, 'message', 'New message from candidate', 'hii', '/employer/messages?conversation=6', 0, '2025-12-18 14:58:40'),
(70, 3, 'message', 'New message from candidate', 'hii', '/employer/messages?conversation=6', 0, '2025-12-18 17:37:39'),
(71, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 1, '2025-12-20 12:16:44'),
(72, 35, 'message', 'New message from employer', 'hii', '/candidate/chat/6', 1, '2025-12-20 12:16:49'),
(73, 35, 'application_update', 'Application Update', 'Your application for \'Web And Software Developer\' has been hired.', '/candidate/applications', 1, '2025-12-20 13:42:49'),
(74, 35, 'application_update', 'Application Update', 'Your application for \'Web And Software Developer\' has been rejected.', '/candidate/applications', 1, '2025-12-20 13:43:51'),
(75, 35, 'interview_scheduled', 'Interview Scheduled', 'Your interview for \'3 Wheeler Driver\' is scheduled for Dec 20, 2025 01:45 PM.', '/candidate/applications', 1, '2025-12-20 13:46:05'),
(76, 35, 'application_update', 'Application Update', 'Your application for \'3 Wheeler Driver\' has been shortlisted.', '/candidate/applications', 1, '2025-12-20 13:51:59'),
(77, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 1, '2025-12-20 14:08:36'),
(78, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 1, '2025-12-20 14:12:30'),
(79, 35, 'application_update', 'Application Update', 'Your application for \'3 Wheeler Driver\' has been shortlisted.', '/candidate/applications', 1, '2025-12-20 14:13:02'),
(80, 35, 'application_update', 'Application Update', 'Your application for \'Web And Software Developer\' has been shortlisted.', '/candidate/applications', 1, '2025-12-20 14:13:30'),
(81, 35, 'application_update', 'Application Update', 'Your application for \'3 Wheeler Driver\' has been shortlisted.', '/candidate/applications', 1, '2025-12-20 14:13:31'),
(82, 35, 'application_update', 'Application Update', 'Your application for \'Web And Software Developer\' has been shortlisted.', '/candidate/applications', 1, '2025-12-20 14:13:32'),
(83, 35, 'application_update', 'Application Update', 'Your application for \'Web And Software Developer\' has been shortlisted.', '/candidate/applications', 1, '2025-12-20 14:13:34'),
(84, 35, 'application_update', 'Application Update', 'Your application for \'3 Wheeler Driver\' has been shortlisted.', '/candidate/applications', 1, '2025-12-20 14:13:35'),
(85, 35, 'application_update', 'Application Update', 'Your application for \'Web And Software Developer\' has been rejected.', '/candidate/applications', 1, '2025-12-20 14:13:52'),
(86, 35, 'application_update', 'Application Update', 'Your application for \'Web And Software Developer\' has been shortlisted.', '/candidate/applications', 1, '2025-12-20 14:13:59'),
(87, 35, 'application_update', 'Application Update', 'Your application for \'3 Wheeler Driver\' has been shortlisted.', '/candidate/applications', 1, '2025-12-20 14:14:01'),
(88, 35, 'message', 'New message from employer', 'Hii', '/candidate/chat/6', 1, '2025-12-20 16:58:51'),
(89, 35, 'message', 'New message from employer', 'how are you hope u are doing well', '/candidate/chat/6', 1, '2025-12-20 16:59:06'),
(90, 3, 'message', 'New message from candidate', 'hii', '/employer/messages?conversation=6', 0, '2025-12-20 17:00:25'),
(91, 10, 'message', 'New message from employer', '', '/candidate/chat/2', 0, '2025-12-20 17:00:43'),
(92, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 1, '2025-12-22 14:08:02'),
(93, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 1, '2025-12-24 11:19:11'),
(94, 3, 'message', 'New message from candidate', 'hii', '/employer/messages?conversation=6', 0, '2025-12-26 17:12:59'),
(95, 30, 'message', 'New message from candidate', 'Regarding application #14', '/employer/messages?conversation=7', 0, '2025-12-29 17:44:21'),
(96, 30, 'message', 'New message from candidate', 'hii', '/employer/messages?conversation=7', 0, '2025-12-29 17:44:28'),
(97, 3, 'message', 'New message from candidate', '[Attachment: 5_6251108935487462564.pdf]', '/employer/messages?conversation=6', 0, '2026-01-03 13:10:20'),
(98, 3, 'message', 'New message from candidate', 'hello pkr', '/employer/messages?conversation=6', 0, '2026-01-03 13:12:21'),
(99, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 1, '2026-01-03 14:23:06'),
(100, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 1, '2026-01-03 16:39:03'),
(101, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 1, '2026-01-03 17:13:49'),
(102, 35, 'application_update', 'Application Update', 'Your application for \'Web And Software Developer\' has been shortlisted.', '/candidate/applications', 1, '2026-01-05 11:09:30'),
(103, 35, 'application_update', 'Application Update', 'Your application for \'Clinical Pharmacist\' has been shortlisted.', '/candidate/applications', 1, '2026-01-05 11:09:34'),
(104, 35, 'application_update', 'Application Update', 'Your application for \'3 Wheeler Driver\' has been shortlisted.', '/candidate/applications', 1, '2026-01-05 11:09:37'),
(105, 35, 'application_update', 'Application Update', 'Your application for \'Clinical Pharmacist\' has been shortlisted.', '/candidate/applications', 1, '2026-01-05 11:38:35'),
(106, 35, 'application_update', 'Application Update', 'Your application for \'Clinical Pharmacist\' has been shortlisted.', '/candidate/applications', 1, '2026-01-05 11:38:36'),
(107, 35, 'application_update', 'Application Update', 'Your application for \'Clinical Pharmacist\' has been shortlisted.', '/candidate/applications', 1, '2026-01-05 11:38:38'),
(108, 35, 'application_update', 'Application Update', 'Your application for \'Clinical Pharmacist\' has been rejected.', '/candidate/applications', 1, '2026-01-05 11:38:45'),
(109, 35, 'application_update', 'Application Update', 'Your application for \'Clinical Pharmacist\' has been rejected.', '/candidate/applications', 1, '2026-01-05 13:13:06'),
(110, 35, 'application_update', 'Application Update', 'Your application for \'Clinical Pharmacist\' has been rejected.', '/candidate/applications', 1, '2026-01-05 13:13:07'),
(111, 35, 'application_update', 'Application Update', 'Your application for \'Clinical Pharmacist\' has been rejected.', '/candidate/applications', 1, '2026-01-05 13:13:08'),
(112, 35, 'application_update', 'Application Update', 'Your application for \'Clinical Pharmacist\' has been rejected.', '/candidate/applications', 1, '2026-01-05 13:13:10'),
(113, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 1, '2026-01-05 16:17:12'),
(114, 35, 'application_update', 'Application Update', 'Your application for \'3 Wheeler Driver\' has been rejected.', '/candidate/applications', 1, '2026-01-05 16:25:25'),
(115, 35, 'application_update', 'Application Update', 'Your application for \'Web And Software Developer\' has been rejected.', '/candidate/applications', 1, '2026-01-05 16:25:48'),
(116, 35, 'application_update', 'Application Update', 'Your application for \'Web And Software Developer\' has been rejected.', '/candidate/applications', 1, '2026-01-05 16:25:53'),
(117, 3, 'message', 'New message from candidate', '[Attachment: AmanResume.pdf] hlo indian barcode', '/employer/messages?conversation=6', 0, '2026-01-05 16:47:29'),
(118, 35, 'application_update', 'Application Update', 'Your application for \'3 Wheeler Driver\' has been shortlisted.', '/candidate/applications', 1, '2026-01-05 17:04:39'),
(119, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 1, '2026-01-05 17:43:06'),
(120, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 0, '2026-01-05 18:28:05'),
(121, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 0, '2026-01-06 10:38:59'),
(122, 35, 'application_update', 'Application Update', 'Your application for \'Clinical Pharmacist\' has been shortlisted.', '/candidate/applications', 0, '2026-01-06 14:07:26'),
(123, 35, 'application_update', 'Application Update', 'Your application for \'Clinical Pharmacist\' has been shortlisted.', '/candidate/applications', 0, '2026-01-06 14:07:27'),
(124, 35, 'application_update', 'Application Update', 'Your application for \'Clinical Pharmacist\' has been shortlisted.', '/candidate/applications', 0, '2026-01-06 14:07:28'),
(125, 35, 'application_update', 'Application Update', 'Your application for \'Clinical Pharmacist\' has been shortlisted.', '/candidate/applications', 0, '2026-01-06 14:07:29'),
(126, 40, 'application_update', 'Application Update', 'Your application for \'Web And Software Developer\' has been screening.', '/candidate/applications', 0, '2026-01-06 14:28:54'),
(127, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 0, '2026-01-06 14:31:52'),
(128, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 0, '2026-01-06 14:35:05'),
(129, 35, 'application_update', 'Application Update', 'Your application for \'3 Wheeler Driver\' has been shortlisted.', '/candidate/applications', 0, '2026-01-06 14:36:08'),
(130, 40, 'application_update', 'Application Update', 'Your application for \'Web And Software Developer\' has been applied.', '/candidate/applications', 0, '2026-01-06 14:37:20'),
(131, 40, 'application_update', 'Application Update', 'Your application for \'Web And Software Developer\' has been interview scheduled.', '/candidate/applications', 0, '2026-01-06 16:09:54'),
(132, 40, 'application_update', 'Application Update', 'Your application for \'Web And Software Developer\' has been interview scheduled.', '/candidate/applications', 0, '2026-01-06 16:09:55'),
(133, 40, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/8', 0, '2026-01-06 16:10:06'),
(134, 9, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/9', 0, '2026-01-06 16:10:38'),
(135, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 0, '2026-01-06 16:16:00'),
(136, 35, 'application_update', 'Application Update', 'Your application for \'Clinical Pharmacist\' has been shortlisted.', '/candidate/applications', 0, '2026-01-06 17:42:11'),
(137, 35, 'application_update', 'Application Update', 'Your application for \'Clinical Pharmacist\' has been shortlisted.', '/candidate/applications', 0, '2026-01-06 17:42:12'),
(138, 35, 'application_update', 'Application Update', 'Your application for \'Web And Software Developer\' has been rejected.', '/candidate/applications', 0, '2026-01-06 17:47:52'),
(139, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 0, '2026-01-06 18:10:48'),
(140, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 0, '2026-01-06 18:40:08'),
(141, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 0, '2026-01-06 18:44:52'),
(142, 35, 'message', 'New message from employer', 'vvgjgh', '/candidate/chat/6', 0, '2026-01-07 11:00:17'),
(143, 35, 'application_update', 'Application Update', 'Your application for \'Web And Software Developer\' has been interview scheduled.', '/candidate/applications', 0, '2026-01-07 11:01:14'),
(144, 35, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/6', 0, '2026-01-07 11:36:26'),
(145, 35, 'application_update', 'Application Update', 'Your application for \'Web And Software Developer\' has been hired.', '/candidate/applications', 0, '2026-01-07 11:59:10'),
(146, 40, 'message', 'New message from employer', 'Hello, I saw your application and would like to discuss further.', '/candidate/chat/8', 0, '2026-01-07 12:24:09'),
(147, 40, 'application_update', 'Application Update', 'Your application for \'Web And Software Developer\' has been applied.', '/candidate/applications', 0, '2026-01-07 12:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `notification_logs`
--

CREATE TABLE `notification_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `candidate_id` bigint(20) UNSIGNED DEFAULT NULL,
  `channel` enum('email','sms','whatsapp','push','in_app') NOT NULL,
  `template_key` varchar(100) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `status` enum('sent','delivered','opened','failed','bounced') DEFAULT 'sent',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `error_message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `delivered_at` datetime DEFAULT NULL,
  `opened_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_logs`
--

INSERT INTO `notification_logs` (`id`, `employer_id`, `candidate_id`, `channel`, `template_key`, `subject`, `content`, `status`, `metadata`, `error_message`, `created_at`, `delivered_at`, `opened_at`) VALUES
(1, NULL, 9, 'email', 'marketing_broadcast', 'Upgrade Reminder', '<h2>Get More Visibility on Your Profile</h2>\r\n\r\n<p>Hi,</p>\r\n\r\n<p>We noticed that your profile is currently on a free plan. While free profiles are visible, upgrading your account can significantly improve your chances of getting noticed by employers.</p>\r\n\r\n<p><strong>With an upgraded profile, you get:</strong></p>\r\n\r\n<ul>\r\n  <li>Higher visibility in employer searches</li>\r\n  <li>Priority access to relevant job opportunities</li>\r\n  <li>Improved profile reach across industries</li>\r\n  <li>Better chances of interview calls</li>\r\n</ul>\r\n\r\n<p>Thousands of candidates have already upgraded and are getting more responses from employers.</p>\r\n\r\n<p>\r\n  <a href=\"https://yourwebsite.com/upgrade\" \r\n     style=\"display:inline-block;padding:12px 20px;background:#28a745;color:#ffffff;text-decoration:none;border-radius:4px;\">\r\n     Upgrade Your Profile Now\r\n  </a>\r\n</p>\r\n\r\n<p>If you have any questions, feel free to contact our support team.</p>\r\n\r\n<p>Best regards,<br>\r\n<strong>Team Mindware Infotech</strong></p>\r\n', 'sent', '{\"subject\":\"Upgrade Reminder\",\"body_html\":\"<h2>Get More Visibility on Your Profile<\\/h2>\\r\\n\\r\\n<p>Hi,<\\/p>\\r\\n\\r\\n<p>We noticed that your profile is currently on a free plan. While free profiles are visible, upgrading your account can significantly improve your chances of getting noticed by employers.<\\/p>\\r\\n\\r\\n<p><strong>With an upgraded profile, you get:<\\/strong><\\/p>\\r\\n\\r\\n<ul>\\r\\n  <li>Higher visibility in employer searches<\\/li>\\r\\n  <li>Priority access to relevant job opportunities<\\/li>\\r\\n  <li>Improved profile reach across industries<\\/li>\\r\\n  <li>Better chances of interview calls<\\/li>\\r\\n<\\/ul>\\r\\n\\r\\n<p>Thousands of candidates have already upgraded and are getting more responses from employers.<\\/p>\\r\\n\\r\\n<p>\\r\\n  <a href=\\\"https:\\/\\/yourwebsite.com\\/upgrade\\\" \\r\\n     style=\\\"display:inline-block;padding:12px 20px;background:#28a745;color:#ffffff;text-decoration:none;border-radius:4px;\\\">\\r\\n     Upgrade Your Profile Now\\r\\n  <\\/a>\\r\\n<\\/p>\\r\\n\\r\\n<p>If you have any questions, feel free to contact our support team.<\\/p>\\r\\n\\r\\n<p>Best regards,<br>\\r\\n<strong>Team Mindware Infotech<\\/strong><\\/p>\\r\\n\",\"candidate_user_id\":9}', NULL, '2025-12-20 13:19:29', NULL, NULL),
(2, NULL, 10, 'email', 'marketing_broadcast', 'Upgrade Reminder', '<h2>Get More Visibility on Your Profile</h2>\r\n\r\n<p>Hi,</p>\r\n\r\n<p>We noticed that your profile is currently on a free plan. While free profiles are visible, upgrading your account can significantly improve your chances of getting noticed by employers.</p>\r\n\r\n<p><strong>With an upgraded profile, you get:</strong></p>\r\n\r\n<ul>\r\n  <li>Higher visibility in employer searches</li>\r\n  <li>Priority access to relevant job opportunities</li>\r\n  <li>Improved profile reach across industries</li>\r\n  <li>Better chances of interview calls</li>\r\n</ul>\r\n\r\n<p>Thousands of candidates have already upgraded and are getting more responses from employers.</p>\r\n\r\n<p>\r\n  <a href=\"https://yourwebsite.com/upgrade\" \r\n     style=\"display:inline-block;padding:12px 20px;background:#28a745;color:#ffffff;text-decoration:none;border-radius:4px;\">\r\n     Upgrade Your Profile Now\r\n  </a>\r\n</p>\r\n\r\n<p>If you have any questions, feel free to contact our support team.</p>\r\n\r\n<p>Best regards,<br>\r\n<strong>Team Mindware Infotech</strong></p>\r\n', 'sent', '{\"subject\":\"Upgrade Reminder\",\"body_html\":\"<h2>Get More Visibility on Your Profile<\\/h2>\\r\\n\\r\\n<p>Hi,<\\/p>\\r\\n\\r\\n<p>We noticed that your profile is currently on a free plan. While free profiles are visible, upgrading your account can significantly improve your chances of getting noticed by employers.<\\/p>\\r\\n\\r\\n<p><strong>With an upgraded profile, you get:<\\/strong><\\/p>\\r\\n\\r\\n<ul>\\r\\n  <li>Higher visibility in employer searches<\\/li>\\r\\n  <li>Priority access to relevant job opportunities<\\/li>\\r\\n  <li>Improved profile reach across industries<\\/li>\\r\\n  <li>Better chances of interview calls<\\/li>\\r\\n<\\/ul>\\r\\n\\r\\n<p>Thousands of candidates have already upgraded and are getting more responses from employers.<\\/p>\\r\\n\\r\\n<p>\\r\\n  <a href=\\\"https:\\/\\/yourwebsite.com\\/upgrade\\\" \\r\\n     style=\\\"display:inline-block;padding:12px 20px;background:#28a745;color:#ffffff;text-decoration:none;border-radius:4px;\\\">\\r\\n     Upgrade Your Profile Now\\r\\n  <\\/a>\\r\\n<\\/p>\\r\\n\\r\\n<p>If you have any questions, feel free to contact our support team.<\\/p>\\r\\n\\r\\n<p>Best regards,<br>\\r\\n<strong>Team Mindware Infotech<\\/strong><\\/p>\\r\\n\",\"candidate_user_id\":10}', NULL, '2025-12-20 13:19:30', NULL, NULL),
(4, NULL, 9, 'email', 'marketing_broadcast', 'test to upgrade premium profile', '<h2>Take the Next Step in Your Career</h2>\r\n\r\n<p>Hi there,</p>\r\n\r\n<p>Your profile is live, and that’s a great start. To get more visibility and better job opportunities, we recommend upgrading your account.</p>\r\n\r\n<p>An upgraded profile helps employers find you faster and increases your chances of getting shortlisted.</p>\r\n\r\n<p>\r\n  <a href=\"https://yourwebsite.com/upgrade\"\r\n     style=\"display:inline-block;padding:12px 22px;background:#ff6b00;color:#ffffff;text-decoration:none;border-radius:4px;\">\r\n     Upgrade My Profile\r\n  </a>\r\n</p>\r\n\r\n<p>Wishing you success,<br>\r\n<strong>Mindware Infotech Team</strong></p>\r\n', 'sent', '{\"subject\":\"test to upgrade premium profile\",\"body_html\":\"<h2>Take the Next Step in Your Career<\\/h2>\\r\\n\\r\\n<p>Hi there,<\\/p>\\r\\n\\r\\n<p>Your profile is live, and that’s a great start. To get more visibility and better job opportunities, we recommend upgrading your account.<\\/p>\\r\\n\\r\\n<p>An upgraded profile helps employers find you faster and increases your chances of getting shortlisted.<\\/p>\\r\\n\\r\\n<p>\\r\\n  <a href=\\\"https:\\/\\/yourwebsite.com\\/upgrade\\\"\\r\\n     style=\\\"display:inline-block;padding:12px 22px;background:#ff6b00;color:#ffffff;text-decoration:none;border-radius:4px;\\\">\\r\\n     Upgrade My Profile\\r\\n  <\\/a>\\r\\n<\\/p>\\r\\n\\r\\n<p>Wishing you success,<br>\\r\\n<strong>Mindware Infotech Team<\\/strong><\\/p>\\r\\n\",\"candidate_user_id\":9}', NULL, '2025-12-20 13:41:04', NULL, NULL),
(5, NULL, 10, 'email', 'marketing_broadcast', 'test to upgrade premium profile', '<h2>Take the Next Step in Your Career</h2>\r\n\r\n<p>Hi there,</p>\r\n\r\n<p>Your profile is live, and that’s a great start. To get more visibility and better job opportunities, we recommend upgrading your account.</p>\r\n\r\n<p>An upgraded profile helps employers find you faster and increases your chances of getting shortlisted.</p>\r\n\r\n<p>\r\n  <a href=\"https://yourwebsite.com/upgrade\"\r\n     style=\"display:inline-block;padding:12px 22px;background:#ff6b00;color:#ffffff;text-decoration:none;border-radius:4px;\">\r\n     Upgrade My Profile\r\n  </a>\r\n</p>\r\n\r\n<p>Wishing you success,<br>\r\n<strong>Mindware Infotech Team</strong></p>\r\n', 'sent', '{\"subject\":\"test to upgrade premium profile\",\"body_html\":\"<h2>Take the Next Step in Your Career<\\/h2>\\r\\n\\r\\n<p>Hi there,<\\/p>\\r\\n\\r\\n<p>Your profile is live, and that’s a great start. To get more visibility and better job opportunities, we recommend upgrading your account.<\\/p>\\r\\n\\r\\n<p>An upgraded profile helps employers find you faster and increases your chances of getting shortlisted.<\\/p>\\r\\n\\r\\n<p>\\r\\n  <a href=\\\"https:\\/\\/yourwebsite.com\\/upgrade\\\"\\r\\n     style=\\\"display:inline-block;padding:12px 22px;background:#ff6b00;color:#ffffff;text-decoration:none;border-radius:4px;\\\">\\r\\n     Upgrade My Profile\\r\\n  <\\/a>\\r\\n<\\/p>\\r\\n\\r\\n<p>Wishing you success,<br>\\r\\n<strong>Mindware Infotech Team<\\/strong><\\/p>\\r\\n\",\"candidate_user_id\":10}', NULL, '2025-12-20 13:41:05', NULL, NULL),
(6, 2, 35, 'email', 'application_status', 'Application hired for Web And Software Developer', '<div style=\'font-family:Arial,sans-serif\'><h2>Application Update</h2><p>Your application for <strong>Web And Software Developer</strong> has been hired.</p></div>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"status\":\"hired\",\"employer_id\":2,\"candidate_user_id\":35}', NULL, '2025-12-20 13:42:49', NULL, NULL),
(7, 2, 35, 'email', 'application_status', 'Application rejected for Web And Software Developer', '<div style=\'font-family:Arial,sans-serif\'><h2>Application Update</h2><p>Your application for <strong>Web And Software Developer</strong> has been rejected.</p></div>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"status\":\"rejected\",\"employer_id\":2,\"candidate_user_id\":35}', NULL, '2025-12-20 13:43:51', NULL, NULL),
(8, 2, 35, 'email', 'interview_scheduled', 'Interview scheduled for 3 Wheeler Driver', '<div style=\'font-family:Arial,sans-serif\'><h2 style=\'color:#059669\'>Interview Scheduled</h2><p>Job: <strong>3 Wheeler Driver</strong></p><p>Time: Dec 20, 2025 01:45 PM</p><p><a href=\'http://localhost:8000/candidate/applications\' style=\'background:#059669;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none\'>View Details</a></p></div>', 'sent', '{\"job_title\":\"3 Wheeler Driver\",\"scheduled_time\":\"Dec 20, 2025 01:45 PM\",\"employer_id\":2,\"candidate_user_id\":35,\"interview_id\":5}', NULL, '2025-12-20 13:46:05', NULL, NULL),
(9, 2, 35, 'email', 'application_status', 'Application shortlisted for 3 Wheeler Driver', '<div style=\'font-family:Arial,sans-serif\'><h2>Application Update</h2><p>Your application for <strong>3 Wheeler Driver</strong> has been shortlisted.</p></div>', 'sent', '{\"job_title\":\"3 Wheeler Driver\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35}', NULL, '2025-12-20 13:51:59', NULL, NULL),
(10, 2, 35, 'email', 'application_status', 'Application shortlisted for 3 Wheeler Driver', '<div style=\'font-family:Arial,sans-serif\'><h2>Application Update</h2><p>Your application for <strong>3 Wheeler Driver</strong> has been shortlisted.</p></div>', 'sent', '{\"job_title\":\"3 Wheeler Driver\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35}', NULL, '2025-12-20 14:13:02', NULL, NULL),
(11, 2, 35, 'email', 'application_status', 'Application shortlisted for Web And Software Developer', '<div style=\'font-family:Arial,sans-serif\'><h2>Application Update</h2><p>Your application for <strong>Web And Software Developer</strong> has been shortlisted.</p></div>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35}', NULL, '2025-12-20 14:13:30', NULL, NULL),
(12, 2, 35, 'email', 'application_status', 'Application shortlisted for 3 Wheeler Driver', '<div style=\'font-family:Arial,sans-serif\'><h2>Application Update</h2><p>Your application for <strong>3 Wheeler Driver</strong> has been shortlisted.</p></div>', 'sent', '{\"job_title\":\"3 Wheeler Driver\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35}', NULL, '2025-12-20 14:13:31', NULL, NULL),
(13, 2, 35, 'email', 'application_status', 'Application shortlisted for Web And Software Developer', '<div style=\'font-family:Arial,sans-serif\'><h2>Application Update</h2><p>Your application for <strong>Web And Software Developer</strong> has been shortlisted.</p></div>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35}', NULL, '2025-12-20 14:13:32', NULL, NULL),
(14, 2, 35, 'email', 'application_status', 'Application shortlisted for Web And Software Developer', '<div style=\'font-family:Arial,sans-serif\'><h2>Application Update</h2><p>Your application for <strong>Web And Software Developer</strong> has been shortlisted.</p></div>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35}', NULL, '2025-12-20 14:13:34', NULL, NULL),
(15, 2, 35, 'email', 'application_status', 'Application shortlisted for 3 Wheeler Driver', '<div style=\'font-family:Arial,sans-serif\'><h2>Application Update</h2><p>Your application for <strong>3 Wheeler Driver</strong> has been shortlisted.</p></div>', 'sent', '{\"job_title\":\"3 Wheeler Driver\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35}', NULL, '2025-12-20 14:13:35', NULL, NULL),
(16, 2, 35, 'email', 'application_status', 'Application rejected for Web And Software Developer', '<div style=\'font-family:Arial,sans-serif\'><h2>Application Update</h2><p>Your application for <strong>Web And Software Developer</strong> has been rejected.</p></div>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"status\":\"rejected\",\"employer_id\":2,\"candidate_user_id\":35}', NULL, '2025-12-20 14:13:52', NULL, NULL),
(17, 2, 35, 'email', 'application_status', 'Application shortlisted for Web And Software Developer', '<div style=\'font-family:Arial,sans-serif\'><h2>Application Update</h2><p>Your application for <strong>Web And Software Developer</strong> has been shortlisted.</p></div>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35}', NULL, '2025-12-20 14:13:59', NULL, NULL),
(18, 2, 35, 'email', 'application_status', 'Application shortlisted for 3 Wheeler Driver', '<div style=\'font-family:Arial,sans-serif\'><h2>Application Update</h2><p>Your application for <strong>3 Wheeler Driver</strong> has been shortlisted.</p></div>', 'sent', '{\"job_title\":\"3 Wheeler Driver\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35}', NULL, '2025-12-20 14:14:01', NULL, NULL),
(19, 2, 35, 'email', 'application_status', 'Application Update: Web And Software Developer at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Web And Software Developer</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#059669; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            shortlisted\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-05 11:09:30', NULL, NULL),
(20, 2, 35, 'email', 'application_status', 'Application Update: Clinical Pharmacist at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Clinical Pharmacist</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#059669; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            shortlisted\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Clinical Pharmacist\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-05 11:09:34', NULL, NULL),
(21, 2, 35, 'email', 'application_status', 'Application Update: 3 Wheeler Driver at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>3 Wheeler Driver</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#059669; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            shortlisted\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"3 Wheeler Driver\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-05 11:09:37', NULL, NULL),
(22, 2, 35, 'email', 'application_status', 'Application Update: Clinical Pharmacist at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/storage/uploads/employers/2/694e8975dbafa_mindware-logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Clinical Pharmacist</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#059669; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            shortlisted\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Clinical Pharmacist\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"http:\\/\\/localhost:8000\\/storage\\/uploads\\/employers\\/2\\/694e8975dbafa_mindware-logo.png\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-05 11:38:35', NULL, NULL),
(23, 2, 35, 'email', 'application_status', 'Application Update: Clinical Pharmacist at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/storage/uploads/employers/2/694e8975dbafa_mindware-logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Clinical Pharmacist</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#059669; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            shortlisted\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Clinical Pharmacist\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"http:\\/\\/localhost:8000\\/storage\\/uploads\\/employers\\/2\\/694e8975dbafa_mindware-logo.png\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-05 11:38:36', NULL, NULL),
(24, 2, 35, 'email', 'application_status', 'Application Update: Clinical Pharmacist at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/storage/uploads/employers/2/694e8975dbafa_mindware-logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Clinical Pharmacist</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#059669; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            shortlisted\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Clinical Pharmacist\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"http:\\/\\/localhost:8000\\/storage\\/uploads\\/employers\\/2\\/694e8975dbafa_mindware-logo.png\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-05 11:38:38', NULL, NULL),
(25, 2, 35, 'email', 'application_status', 'Application Update: Clinical Pharmacist at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/storage/uploads/employers/2/694e8975dbafa_mindware-logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Clinical Pharmacist</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#dc2626; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            rejected\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Clinical Pharmacist\",\"status\":\"rejected\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"http:\\/\\/localhost:8000\\/storage\\/uploads\\/employers\\/2\\/694e8975dbafa_mindware-logo.png\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-05 11:38:45', NULL, NULL),
(26, 2, 35, 'email', 'application_status', 'Application Update: Clinical Pharmacist at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/storage/uploads/employers/2/694e8975dbafa_mindware-logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Clinical Pharmacist</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#dc2626; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            rejected\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Clinical Pharmacist\",\"status\":\"rejected\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"http:\\/\\/localhost:8000\\/storage\\/uploads\\/employers\\/2\\/694e8975dbafa_mindware-logo.png\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-05 13:13:06', NULL, NULL),
(27, 2, 35, 'email', 'application_status', 'Application Update: Clinical Pharmacist at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/storage/uploads/employers/2/694e8975dbafa_mindware-logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Clinical Pharmacist</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#dc2626; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            rejected\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Clinical Pharmacist\",\"status\":\"rejected\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"http:\\/\\/localhost:8000\\/storage\\/uploads\\/employers\\/2\\/694e8975dbafa_mindware-logo.png\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-05 13:13:07', NULL, NULL);
INSERT INTO `notification_logs` (`id`, `employer_id`, `candidate_id`, `channel`, `template_key`, `subject`, `content`, `status`, `metadata`, `error_message`, `created_at`, `delivered_at`, `opened_at`) VALUES
(28, 2, 35, 'email', 'application_status', 'Application Update: Clinical Pharmacist at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/storage/uploads/employers/2/694e8975dbafa_mindware-logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Clinical Pharmacist</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#dc2626; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            rejected\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Clinical Pharmacist\",\"status\":\"rejected\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"http:\\/\\/localhost:8000\\/storage\\/uploads\\/employers\\/2\\/694e8975dbafa_mindware-logo.png\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-05 13:13:08', NULL, NULL),
(29, 2, 35, 'email', 'application_status', 'Application Update: Clinical Pharmacist at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/storage/uploads/employers/2/694e8975dbafa_mindware-logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Clinical Pharmacist</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#dc2626; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            rejected\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Clinical Pharmacist\",\"status\":\"rejected\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"http:\\/\\/localhost:8000\\/storage\\/uploads\\/employers\\/2\\/694e8975dbafa_mindware-logo.png\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-05 13:13:10', NULL, NULL),
(30, 2, 35, 'email', 'interview_cancelled', 'Notification', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Mindware Infotech\">\r\n        </div>\r\n        <div class=\"content\">\r\n            You have a new notification.\r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"employer_id\":2,\"candidate_user_id\":35,\"interview_id\":7}', NULL, '2026-01-05 16:17:03', NULL, NULL),
(31, 2, 35, 'email', 'application_status', 'Application Update: 3 Wheeler Driver at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>3 Wheeler Driver</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#dc2626; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            rejected\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"3 Wheeler Driver\",\"status\":\"rejected\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-05 16:25:25', NULL, NULL),
(32, 2, 35, 'email', 'application_status', 'Application Update: Web And Software Developer at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Web And Software Developer</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#dc2626; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            rejected\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"status\":\"rejected\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-05 16:25:48', NULL, NULL),
(33, 2, 35, 'email', 'application_status', 'Application Update: Web And Software Developer at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Web And Software Developer</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#dc2626; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            rejected\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"status\":\"rejected\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-05 16:25:53', NULL, NULL),
(34, 2, 35, 'email', 'interview_rescheduled', 'Notification', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Mindware Infotech\">\r\n        </div>\r\n        <div class=\"content\">\r\n            You have a new notification.\r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"scheduled_time\":\"Jan 06, 2026 05:04 PM\",\"employer_id\":2,\"candidate_user_id\":35,\"interview_id\":6}', NULL, '2026-01-05 16:28:29', NULL, NULL),
(35, 2, 35, 'email', 'application_status', 'Application Update: 3 Wheeler Driver at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>3 Wheeler Driver</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#059669; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            shortlisted\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"3 Wheeler Driver\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-05 17:04:39', NULL, NULL),
(36, 2, 35, 'email', 'application_status', 'Application Update: Clinical Pharmacist at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Clinical Pharmacist</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#059669; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            shortlisted\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Clinical Pharmacist\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-06 14:07:26', NULL, NULL),
(37, 2, 35, 'email', 'application_status', 'Application Update: Clinical Pharmacist at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Clinical Pharmacist</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#059669; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            shortlisted\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Clinical Pharmacist\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-06 14:07:27', NULL, NULL),
(38, 2, 35, 'email', 'application_status', 'Application Update: Clinical Pharmacist at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Clinical Pharmacist</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#059669; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            shortlisted\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Clinical Pharmacist\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-06 14:07:28', NULL, NULL),
(39, 2, 35, 'email', 'application_status', 'Application Update: Clinical Pharmacist at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Clinical Pharmacist</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#059669; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            shortlisted\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Clinical Pharmacist\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-06 14:07:29', NULL, NULL),
(40, NULL, 40, 'email', 'candidate_welcome', 'Welcome to Mindware Infotech', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Mindware Infotech\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Welcome, Candidate!</h2>\r\n                    <p>Thanks for joining Mindware Infotech. We\'re excited to help you find your next career opportunity.</p>\r\n                    <p>Complete your profile to get matched with top employers.</p>\r\n                    <center><a href=\'http://localhost:8000/login\' class=\'btn\'>Login to Your Account</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"candidate_user_id\":40}', NULL, '2026-01-06 14:17:38', NULL, NULL),
(41, 11, 40, 'email', 'application_status', 'Application Update: Web And Software Developer at PKR Techvision', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"PKR Techvision\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Web And Software Developer</strong> at <strong>PKR Techvision</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#2563eb; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            screening\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"status\":\"screening\",\"employer_id\":11,\"candidate_user_id\":40,\"company_name\":\"PKR Techvision\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/pkrtechvision.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-06 14:28:54', NULL, NULL);
INSERT INTO `notification_logs` (`id`, `employer_id`, `candidate_id`, `channel`, `template_key`, `subject`, `content`, `status`, `metadata`, `error_message`, `created_at`, `delivered_at`, `opened_at`) VALUES
(42, 2, 35, 'email', 'application_status', 'Application Update: 3 Wheeler Driver at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>3 Wheeler Driver</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#059669; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            shortlisted\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"3 Wheeler Driver\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-06 14:36:08', NULL, NULL),
(43, 11, 40, 'email', 'application_status', 'Application Update: Web And Software Developer at PKR Techvision', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"PKR Techvision\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Web And Software Developer</strong> at <strong>PKR Techvision</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#2563eb; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            applied\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"status\":\"applied\",\"employer_id\":11,\"candidate_user_id\":40,\"company_name\":\"PKR Techvision\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/pkrtechvision.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-06 14:37:20', NULL, NULL),
(44, 11, 40, 'email', 'application_status', 'Application Update: Web And Software Developer at PKR Techvision', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"PKR Techvision\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Web And Software Developer</strong> at <strong>PKR Techvision</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#2563eb; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            interview\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"status\":\"interview\",\"employer_id\":11,\"candidate_user_id\":40,\"company_name\":\"PKR Techvision\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/pkrtechvision.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-06 16:09:54', NULL, NULL),
(45, 11, 40, 'email', 'application_status', 'Application Update: Web And Software Developer at PKR Techvision', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"PKR Techvision\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Web And Software Developer</strong> at <strong>PKR Techvision</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#2563eb; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            interview\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"status\":\"interview\",\"employer_id\":11,\"candidate_user_id\":40,\"company_name\":\"PKR Techvision\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/pkrtechvision.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-06 16:09:55', NULL, NULL),
(46, 2, 35, 'email', 'application_status', 'Application Update: Clinical Pharmacist at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Clinical Pharmacist</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#059669; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            shortlisted\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Clinical Pharmacist\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-06 17:42:11', NULL, NULL),
(47, 2, 35, 'email', 'application_status', 'Application Update: Clinical Pharmacist at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Clinical Pharmacist</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#059669; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            shortlisted\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Clinical Pharmacist\",\"status\":\"shortlisted\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-06 17:42:12', NULL, NULL),
(48, 2, 35, 'email', 'application_status', 'Application Update: Web And Software Developer at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Web And Software Developer</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#dc2626; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            rejected\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"status\":\"rejected\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-06 17:47:52', NULL, NULL),
(49, 2, 35, 'email', 'application_status', 'Application Update: Web And Software Developer at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Web And Software Developer</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#2563eb; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            interview\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"status\":\"interview\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-07 11:01:14', NULL, NULL),
(50, 2, 35, 'email', 'application_status', 'Application Update: Web And Software Developer at Indian Barcode Corporation', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"Indian Barcode Corporation\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Web And Software Developer</strong> at <strong>Indian Barcode Corporation</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#7c3aed; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            hired\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"status\":\"hired\",\"employer_id\":2,\"candidate_user_id\":35,\"company_name\":\"Indian Barcode Corporation\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/www.mindwaretechnologies.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-07 11:59:10', NULL, NULL),
(51, 11, 40, 'email', 'application_status', 'Application Update: Web And Software Developer at PKR Techvision', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <meta charset=\"utf-8\">\r\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <style>\r\n        body { font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }\r\n        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; margin-bottom: 20px; }\r\n        .header { background: #ffffff; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; text-align: center; }\r\n        .header img { max-height: 50px; object-fit: contain; }\r\n        .content { padding: 30px; }\r\n        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }\r\n        .btn { display: inline-block; padding: 12px 24px; background-color: #2563eb; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }\r\n        .btn:hover { background-color: #1d4ed8; }\r\n        .info-box { background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb; }\r\n        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold; text-transform: uppercase; }\r\n        .label-tag { display: inline-block; background-color: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-top: 5px; }\r\n    </style>\r\n</head>\r\n<body>\r\n    <div class=\"container\">\r\n        <div class=\"header\">\r\n            <img src=\"http://localhost:8000/assets/images/logo.png\" alt=\"PKR Techvision\">\r\n        </div>\r\n        <div class=\"content\">\r\n            \r\n                    <h2 style=\'color:#111827; margin-top:0;\'>Application Status Update</h2>\r\n                    <p>Hi Candidate,</p>\r\n                    <p>The status of your application for <strong>Web And Software Developer</strong> at <strong>PKR Techvision</strong> has been updated.</p>\r\n                    \r\n                    <div style=\'text-align:center; margin:30px 0;\'>\r\n                        <span style=\'background-color:#2563eb; color:white; padding:8px 20px; border-radius:99px; font-weight:bold; text-transform:uppercase;\'>\r\n                            applied\r\n                        </span>\r\n                    </div>\r\n                    \r\n                    <center><a href=\'http://localhost:8000/candidate/applications\' class=\'btn\'>View Details</a></center>\r\n                \r\n        </div>\r\n        <div class=\"footer\">\r\n            <p>Mindware Infotech - Empowering Careers<br />\nContact us: support@mindinfotech.com | +91 123 456 7890<br />\nUnsubscribe options available in your profile.</p>\r\n            <p><small>You are receiving this email because you are registered on Mindware Infotech.</small></p>\r\n        </div>\r\n    </div>\r\n</body>\r\n</html>', 'sent', '{\"job_title\":\"Web And Software Developer\",\"status\":\"applied\",\"employer_id\":11,\"candidate_user_id\":40,\"company_name\":\"PKR Techvision\",\"company_logo\":\"\",\"company_website\":\"https:\\/\\/pkrtechvision.com\",\"candidate_name\":\"Candidate\"}', NULL, '2026-01-07 12:45:28', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `employer_id` int(11) DEFAULT NULL,
  `gateway` varchar(30) DEFAULT NULL,
  `token` varchar(150) DEFAULT NULL,
  `method_type` enum('card','upi') DEFAULT NULL,
  `last4` varchar(4) DEFAULT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `exp_month` int(11) DEFAULT NULL,
  `exp_year` int(11) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `slug` varchar(150) DEFAULT NULL,
  `module` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `module`, `created_at`) VALUES
(1, 'View Verification Queue', 'verification.view', 'verification', '2025-12-04 16:54:11'),
(2, 'Assign Verification', 'verification.assign', 'verification', '2025-12-04 16:54:11'),
(3, 'Approve / Reject Verification', 'verification.review', 'verification', '2025-12-04 16:54:11'),
(4, 'Override Verification', 'verification.override', 'verification', '2025-12-04 16:54:11'),
(5, 'View Leads', 'sales.view', 'sales', '2025-12-04 16:54:11'),
(6, 'Assign Leads', 'sales.assign', 'sales', '2025-12-04 16:54:11'),
(7, 'Close Leads', 'sales.close', 'sales', '2025-12-04 16:54:11'),
(8, 'Manage Employers', 'employer.manage', 'employer', '2025-12-04 16:54:11'),
(9, 'Manage Candidates', 'candidate.manage', 'candidate', '2025-12-04 16:54:11'),
(10, 'View Payments', 'payments.view', 'billing', '2025-12-04 16:54:11'),
(11, 'Refund Payments', 'payments.refund', 'billing', '2025-12-04 16:54:11'),
(12, 'Manage Plans', 'plans.manage', 'billing', '2025-12-04 16:54:11'),
(13, 'Support Tickets', 'support.handle', 'support', '2025-12-04 16:54:11'),
(14, 'System Settings', 'settings.manage', 'system', '2025-12-04 16:54:11'),
(15, 'Audit view', 'audit.view', 'audit', '2025-12-05 15:58:16'),
(16, 'Sales leads view', 'sales.leads.view', 'sales', '2025-12-05 16:02:07'),
(17, 'Sales leads update', 'sales.leads.update', 'sales', '2025-12-05 16:02:07'),
(18, 'Sales reports view', 'sales.reports.view', 'sales', '2025-12-05 16:02:07'),
(19, 'Support tickets view', 'support.tickets.view', 'support', '2025-12-05 16:02:07'),
(20, 'Support tickets assign', 'support.tickets.assign', 'support', '2025-12-05 16:02:07'),
(21, 'Support tickets reply', 'support.tickets.reply', 'support', '2025-12-05 16:02:07'),
(22, 'Support tickets close', 'support.tickets.close', 'support', '2025-12-05 16:02:07'),
(23, 'Support escalate', 'support.escalate', 'support', '2025-12-05 16:02:07'),
(24, 'Payments approve', 'payments.approve', 'payments', '2025-12-05 16:02:07'),
(25, 'Invoices view', 'invoices.view', 'invoices', '2025-12-05 16:02:07'),
(26, 'System manage', 'system.manage', 'system', '2025-12-05 16:02:07'),
(27, 'Impersonate user', 'impersonate.user', 'impersonate', '2025-12-05 16:02:07'),
(28, 'Ip whitelist manage', 'ip_whitelist.manage', 'ip_whitelist', '2025-12-05 16:02:07'),
(29, 'Cron manage', 'cron.manage', 'cron', '2025-12-05 16:02:07'),
(30, 'Api manage', 'api.manage', 'api', '2025-12-05 16:02:07');

-- --------------------------------------------------------

--
-- Table structure for table `permission_role`
--

CREATE TABLE `permission_role` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resumes`
--

CREATE TABLE `resumes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `candidate_id` bigint(20) UNSIGNED NOT NULL,
  `template_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'My Resume',
  `job_category` varchar(100) DEFAULT NULL,
  `status` enum('draft','active','hidden','archived') DEFAULT 'draft',
  `strength_score` int(11) DEFAULT 0 COMMENT 'Percentage 0-100',
  `ats_score` int(11) DEFAULT 0 COMMENT 'ATS optimization score 0-100',
  `is_primary` tinyint(1) DEFAULT 0 COMMENT 'Default resume for applications',
  `pdf_url` varchar(512) DEFAULT NULL COMMENT 'Generated PDF path',
  `preview_image` varchar(512) DEFAULT NULL COMMENT 'Thumbnail for template selection',
  `version` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `resumes`
--

INSERT INTO `resumes` (`id`, `candidate_id`, `template_id`, `title`, `job_category`, `status`, `strength_score`, `ats_score`, `is_primary`, `pdf_url`, `preview_image`, `version`, `created_at`, `updated_at`) VALUES
(21, 12, 5, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2025-12-31 12:50:30', '2025-12-31 12:50:30'),
(22, 12, 5, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2025-12-31 13:51:49', '2025-12-31 13:51:49'),
(23, 12, 2, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2025-12-31 13:53:10', '2025-12-31 13:53:10'),
(24, 12, 1, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2025-12-31 13:54:12', '2025-12-31 13:54:12'),
(25, 12, 3, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2025-12-31 13:54:18', '2025-12-31 13:54:18'),
(26, 12, 4, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2025-12-31 13:57:37', '2025-12-31 13:57:37'),
(27, 12, 2, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2025-12-31 14:22:03', '2025-12-31 14:22:03'),
(28, 12, 2, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2025-12-31 14:33:07', '2025-12-31 14:33:07'),
(29, 12, 2, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2025-12-31 14:33:23', '2025-12-31 14:33:23'),
(30, 12, 5, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2025-12-31 14:33:49', '2025-12-31 14:33:49'),
(31, 12, 1, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2025-12-31 15:08:40', '2025-12-31 15:08:41'),
(32, 12, 2, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2025-12-31 15:19:18', '2025-12-31 15:19:18'),
(33, 12, 4, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2025-12-31 15:47:23', '2025-12-31 15:47:24'),
(34, 12, 2, 'Prabhat Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2025-12-31 16:00:31', '2025-12-31 16:00:31'),
(35, 12, 5, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2025-12-31 17:24:07', '2025-12-31 17:24:07'),
(36, 12, 5, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2026-01-01 10:42:17', '2026-01-01 10:42:17'),
(37, 12, 5, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2026-01-02 11:59:55', '2026-01-02 11:59:55'),
(38, 12, 5, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2026-01-03 11:59:04', '2026-01-03 11:59:04'),
(39, 12, 5, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2026-01-03 13:45:21', '2026-01-03 13:45:21'),
(40, 12, 5, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2026-01-03 13:46:25', '2026-01-03 13:46:25'),
(41, 12, 2, 'My Resume', NULL, 'draft', 100, 0, 0, NULL, NULL, 1, '2026-01-05 16:45:48', '2026-01-05 16:45:48');

-- --------------------------------------------------------

--
-- Table structure for table `resume_analytics`
--

CREATE TABLE `resume_analytics` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `resume_id` bigint(20) UNSIGNED NOT NULL,
  `event_type` enum('view','download','share','shortlist','application') NOT NULL,
  `employer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON: additional event data' CHECK (json_valid(`metadata`)),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resume_sections`
--

CREATE TABLE `resume_sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `resume_id` bigint(20) UNSIGNED NOT NULL,
  `section_type` enum('header','summary','experience','education','skills','languages','certifications','projects','achievements','references') NOT NULL,
  `section_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'JSON: content, styling, layout, position' CHECK (json_valid(`section_data`)),
  `sort_order` int(11) DEFAULT 0,
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `resume_sections`
--

INSERT INTO `resume_sections` (`id`, `resume_id`, `section_type`, `section_data`, `sort_order`, `is_visible`, `created_at`, `updated_at`) VALUES
(242, 21, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"+91-9910112688\",\"location\":\"Dwarka, India\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\",\"first_name\":\"Prabhat\",\"last_name\":\"Paswan\",\"city\":\"Dwarka\",\"country\":\"India\",\"pin_code\":\"110078\"}}', 0, 1, '2025-12-31 12:50:30', '2025-12-31 13:03:37'),
(243, 21, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2025-12-31 12:50:30', '2025-12-31 12:50:30'),
(244, 21, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer \",\"company_name\":\"Mindware\",\"start_date\":\"2023-04\",\"end_date\":\"\",\"is_current\":true,\"description\":\"\",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2025-12-31 12:50:30', '2025-12-31 13:09:26'),
(245, 21, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2025-12-31 12:50:30', '2025-12-31 12:50:30'),
(246, 21, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2025-12-31 12:50:30', '2025-12-31 12:50:30'),
(247, 21, 'certifications', '{\"content\":[]}', 5, 1, '2025-12-31 12:50:30', '2025-12-31 12:50:30'),
(248, 21, 'achievements', '{\"content\":[]}', 6, 1, '2025-12-31 12:50:30', '2025-12-31 12:50:30'),
(249, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:50:41', '2025-12-31 12:50:41'),
(250, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:50:41', '2025-12-31 12:50:41'),
(251, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:50:51', '2025-12-31 12:50:51'),
(252, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:50:51', '2025-12-31 12:50:51'),
(253, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:50:57', '2025-12-31 12:50:57'),
(254, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:50:57', '2025-12-31 12:50:57'),
(255, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:51:07', '2025-12-31 12:51:07'),
(256, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:51:07', '2025-12-31 12:51:07'),
(257, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:51:12', '2025-12-31 12:51:12'),
(258, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:51:12', '2025-12-31 12:51:12'),
(259, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:51:14', '2025-12-31 12:51:14'),
(260, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:51:14', '2025-12-31 12:51:14'),
(261, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:51:27', '2025-12-31 12:51:27'),
(262, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:51:27', '2025-12-31 12:51:27'),
(263, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:51:28', '2025-12-31 12:51:28'),
(264, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:51:28', '2025-12-31 12:51:28'),
(265, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:51:32', '2025-12-31 12:51:32'),
(266, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:51:32', '2025-12-31 12:51:32'),
(267, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:52:04', '2025-12-31 12:52:04'),
(268, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:52:04', '2025-12-31 12:52:04'),
(269, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:52:34', '2025-12-31 12:52:34'),
(270, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:52:34', '2025-12-31 12:52:34'),
(271, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:53:03', '2025-12-31 12:53:03'),
(272, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:53:03', '2025-12-31 12:53:03'),
(273, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:53:33', '2025-12-31 12:53:33'),
(274, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:53:33', '2025-12-31 12:53:33'),
(275, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:54:03', '2025-12-31 12:54:03'),
(276, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:54:03', '2025-12-31 12:54:03'),
(277, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:54:11', '2025-12-31 12:54:11'),
(278, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:54:11', '2025-12-31 12:54:11'),
(279, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:54:42', '2025-12-31 12:54:42'),
(280, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:54:42', '2025-12-31 12:54:42'),
(281, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:55:12', '2025-12-31 12:55:12'),
(282, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:55:12', '2025-12-31 12:55:12'),
(283, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:55:41', '2025-12-31 12:55:41'),
(284, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:55:41', '2025-12-31 12:55:41'),
(285, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:56:11', '2025-12-31 12:56:11'),
(286, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:56:11', '2025-12-31 12:56:11'),
(287, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:56:41', '2025-12-31 12:56:41'),
(288, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:56:41', '2025-12-31 12:56:41'),
(289, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:57:11', '2025-12-31 12:57:11'),
(290, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:57:11', '2025-12-31 12:57:11'),
(291, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:57:41', '2025-12-31 12:57:41'),
(292, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:57:41', '2025-12-31 12:57:41'),
(293, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:58:11', '2025-12-31 12:58:11'),
(294, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:58:11', '2025-12-31 12:58:11'),
(295, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:58:41', '2025-12-31 12:58:41'),
(296, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:58:41', '2025-12-31 12:58:41'),
(297, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:59:11', '2025-12-31 12:59:11'),
(298, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:59:11', '2025-12-31 12:59:11'),
(299, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:59:42', '2025-12-31 12:59:42'),
(300, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 12:59:42', '2025-12-31 12:59:42'),
(301, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:00:12', '2025-12-31 13:00:12'),
(302, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:00:12', '2025-12-31 13:00:12'),
(303, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:01:12', '2025-12-31 13:01:12'),
(304, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:01:12', '2025-12-31 13:01:12'),
(305, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:02:11', '2025-12-31 13:02:11'),
(306, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:02:11', '2025-12-31 13:02:11'),
(307, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:02:59', '2025-12-31 13:02:59'),
(308, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:02:59', '2025-12-31 13:02:59'),
(309, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:03:24', '2025-12-31 13:03:24'),
(310, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:03:24', '2025-12-31 13:03:24'),
(311, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:03:27', '2025-12-31 13:03:27'),
(312, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:03:27', '2025-12-31 13:03:27'),
(313, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:03:29', '2025-12-31 13:03:29'),
(314, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:03:29', '2025-12-31 13:03:29'),
(315, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:03:34', '2025-12-31 13:03:34'),
(316, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:03:34', '2025-12-31 13:03:34'),
(317, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:03:37', '2025-12-31 13:03:37'),
(318, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:03:37', '2025-12-31 13:03:37'),
(319, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:03:37', '2025-12-31 13:03:37'),
(320, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:03:37', '2025-12-31 13:03:37'),
(321, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:03:55', '2025-12-31 13:03:55'),
(322, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:03:55', '2025-12-31 13:03:55'),
(323, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:04:08', '2025-12-31 13:04:08'),
(324, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:04:08', '2025-12-31 13:04:08'),
(325, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:04:11', '2025-12-31 13:04:11'),
(326, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:04:11', '2025-12-31 13:04:11'),
(327, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:04:12', '2025-12-31 13:04:12'),
(328, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:04:12', '2025-12-31 13:04:12'),
(329, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:04:23', '2025-12-31 13:04:23'),
(330, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:04:23', '2025-12-31 13:04:23'),
(331, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:04:46', '2025-12-31 13:04:46'),
(332, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:04:46', '2025-12-31 13:04:46'),
(333, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:05:16', '2025-12-31 13:05:16'),
(334, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:05:16', '2025-12-31 13:05:16'),
(335, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:05:46', '2025-12-31 13:05:46'),
(336, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:05:46', '2025-12-31 13:05:46'),
(337, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:06:16', '2025-12-31 13:06:16'),
(338, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:06:16', '2025-12-31 13:06:16'),
(339, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:06:46', '2025-12-31 13:06:46'),
(340, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:06:46', '2025-12-31 13:06:46'),
(341, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:07:16', '2025-12-31 13:07:16'),
(342, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:07:16', '2025-12-31 13:07:16'),
(343, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:07:46', '2025-12-31 13:07:46'),
(344, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:07:46', '2025-12-31 13:07:46'),
(345, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:08:47', '2025-12-31 13:08:47'),
(346, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:08:47', '2025-12-31 13:08:47'),
(347, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:09:17', '2025-12-31 13:09:17'),
(348, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:09:17', '2025-12-31 13:09:17'),
(349, 21, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 13:09:26', '2025-12-31 13:09:26'),
(350, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:09:26', '2025-12-31 13:09:26'),
(351, 21, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 13:09:28', '2025-12-31 13:09:28'),
(352, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:09:28', '2025-12-31 13:09:28'),
(353, 21, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 13:09:48', '2025-12-31 13:09:48'),
(354, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:09:48', '2025-12-31 13:09:48'),
(355, 21, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 13:09:59', '2025-12-31 13:09:59'),
(356, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:09:59', '2025-12-31 13:09:59'),
(357, 21, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 13:10:05', '2025-12-31 13:10:05'),
(358, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:10:05', '2025-12-31 13:10:05'),
(359, 21, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 13:10:06', '2025-12-31 13:10:06'),
(360, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:10:06', '2025-12-31 13:10:06'),
(361, 21, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 13:11:35', '2025-12-31 13:11:35'),
(362, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:11:35', '2025-12-31 13:11:35'),
(363, 21, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 13:12:05', '2025-12-31 13:12:05'),
(364, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:12:05', '2025-12-31 13:12:05'),
(365, 21, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 13:12:35', '2025-12-31 13:12:35'),
(366, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:12:35', '2025-12-31 13:12:35'),
(367, 21, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 13:13:05', '2025-12-31 13:13:05'),
(368, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:13:05', '2025-12-31 13:13:05'),
(369, 21, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 13:13:35', '2025-12-31 13:13:35'),
(370, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:13:35', '2025-12-31 13:13:35'),
(371, 21, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 13:14:06', '2025-12-31 13:14:06'),
(372, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:14:06', '2025-12-31 13:14:06'),
(373, 21, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 13:14:36', '2025-12-31 13:14:36'),
(374, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:14:36', '2025-12-31 13:14:36'),
(375, 21, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 13:15:12', '2025-12-31 13:15:12'),
(376, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:15:12', '2025-12-31 13:15:12'),
(377, 21, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 13:16:07', '2025-12-31 13:16:07'),
(378, 21, '', '{\"content\":[]}', 0, 1, '2025-12-31 13:16:07', '2025-12-31 13:16:07'),
(379, 22, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Dwarka , Delhi\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\"}}', 0, 1, '2025-12-31 13:51:49', '2025-12-31 13:51:49'),
(380, 22, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2025-12-31 13:51:49', '2025-12-31 13:51:49'),
(381, 22, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2025-12-31 13:51:49', '2025-12-31 13:51:49'),
(382, 22, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2025-12-31 13:51:49', '2025-12-31 13:51:49'),
(383, 22, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2025-12-31 13:51:49', '2025-12-31 13:51:49'),
(384, 22, 'certifications', '{\"content\":[]}', 5, 1, '2025-12-31 13:51:49', '2025-12-31 13:51:49'),
(385, 22, 'achievements', '{\"content\":[]}', 6, 1, '2025-12-31 13:51:49', '2025-12-31 13:51:49'),
(386, 23, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Dwarka , Delhi\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\"}}', 0, 1, '2025-12-31 13:53:10', '2025-12-31 13:53:10'),
(387, 23, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2025-12-31 13:53:10', '2025-12-31 13:53:10'),
(388, 23, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2025-12-31 13:53:10', '2025-12-31 13:53:10'),
(389, 23, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2025-12-31 13:53:10', '2025-12-31 13:53:10'),
(390, 23, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2025-12-31 13:53:10', '2025-12-31 13:53:10'),
(391, 23, 'languages', '{\"content\":{\"items\":[]}}', 5, 1, '2025-12-31 13:53:10', '2025-12-31 13:53:10'),
(392, 24, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Dwarka , Delhi\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\"}}', 0, 1, '2025-12-31 13:54:12', '2025-12-31 13:54:12'),
(393, 24, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2025-12-31 13:54:12', '2025-12-31 13:54:12'),
(394, 24, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2025-12-31 13:54:12', '2025-12-31 13:54:12'),
(395, 24, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2025-12-31 13:54:12', '2025-12-31 13:54:12'),
(396, 24, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2025-12-31 13:54:12', '2025-12-31 13:54:12'),
(397, 25, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Dwarka , Delhi\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\"}}', 0, 1, '2025-12-31 13:54:18', '2025-12-31 13:54:18'),
(398, 25, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2025-12-31 13:54:18', '2025-12-31 13:54:18'),
(399, 25, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2025-12-31 13:54:18', '2025-12-31 13:54:18'),
(400, 25, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2025-12-31 13:54:18', '2025-12-31 13:54:18'),
(401, 25, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2025-12-31 13:54:18', '2025-12-31 13:54:18'),
(402, 25, 'certifications', '{\"content\":[]}', 5, 1, '2025-12-31 13:54:18', '2025-12-31 13:54:18'),
(403, 26, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Dwarka , Delhi\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\"}}', 0, 1, '2025-12-31 13:57:37', '2025-12-31 13:57:37'),
(404, 26, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2025-12-31 13:57:37', '2025-12-31 13:57:37'),
(405, 26, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2025-12-31 13:57:37', '2025-12-31 13:57:37'),
(406, 26, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2025-12-31 13:57:37', '2025-12-31 13:57:37'),
(407, 26, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2025-12-31 13:57:37', '2025-12-31 13:57:37'),
(408, 26, 'projects', '{\"content\":[]}', 5, 1, '2025-12-31 13:57:37', '2025-12-31 13:57:37'),
(409, 26, 'achievements', '{\"content\":[]}', 6, 1, '2025-12-31 13:57:37', '2025-12-31 13:57:37'),
(410, 27, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Dwarka , Delhi\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\",\"first_name\":\"Prabhat\",\"last_name\":\"Paswan\"}}', 0, 1, '2025-12-31 14:22:03', '2025-12-31 14:22:08'),
(411, 27, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2025-12-31 14:22:03', '2025-12-31 14:22:03'),
(412, 27, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2025-12-31 14:22:03', '2025-12-31 14:22:03'),
(413, 27, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2025-12-31 14:22:03', '2025-12-31 14:22:03'),
(414, 27, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2025-12-31 14:22:03', '2025-12-31 14:22:03'),
(415, 27, 'languages', '{\"content\":{\"items\":[]}}', 5, 1, '2025-12-31 14:22:03', '2025-12-31 14:22:03'),
(416, 27, '', '{\"content\":{\"primary_color\":\"#ef4444\"}}', 999, 0, '2025-12-31 14:22:03', '2025-12-31 14:22:03'),
(417, 27, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:22:08', '2025-12-31 14:22:08'),
(418, 27, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:22:08', '2025-12-31 14:22:08'),
(419, 27, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:22:10', '2025-12-31 14:22:10'),
(420, 27, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:22:10', '2025-12-31 14:22:10'),
(421, 27, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:22:11', '2025-12-31 14:22:11'),
(422, 27, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:22:11', '2025-12-31 14:22:11'),
(423, 27, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:22:12', '2025-12-31 14:22:12'),
(424, 27, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:22:12', '2025-12-31 14:22:12'),
(425, 27, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:22:13', '2025-12-31 14:22:13'),
(426, 27, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:22:13', '2025-12-31 14:22:13'),
(427, 27, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:22:15', '2025-12-31 14:22:15'),
(428, 27, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:22:15', '2025-12-31 14:22:15'),
(429, 27, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:22:46', '2025-12-31 14:22:46'),
(430, 27, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:22:46', '2025-12-31 14:22:46'),
(431, 27, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:23:34', '2025-12-31 14:23:34'),
(432, 27, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:23:34', '2025-12-31 14:23:34'),
(433, 27, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:24:10', '2025-12-31 14:24:10'),
(434, 27, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:24:10', '2025-12-31 14:24:10'),
(435, 28, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Dwarka , Delhi\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\"}}', 0, 1, '2025-12-31 14:33:07', '2025-12-31 14:33:07'),
(436, 28, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2025-12-31 14:33:07', '2025-12-31 14:33:07'),
(437, 28, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2025-12-31 14:33:07', '2025-12-31 14:33:07'),
(438, 28, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2025-12-31 14:33:07', '2025-12-31 14:33:07'),
(439, 28, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2025-12-31 14:33:07', '2025-12-31 14:33:07'),
(440, 28, 'languages', '{\"content\":{\"items\":[]}}', 5, 1, '2025-12-31 14:33:07', '2025-12-31 14:33:07'),
(441, 28, '', '{\"content\":{\"primary_color\":\"#2563eb\"}}', 999, 0, '2025-12-31 14:33:07', '2025-12-31 14:33:07'),
(442, 29, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Dwarka , Delhi\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\"}}', 0, 1, '2025-12-31 14:33:23', '2025-12-31 14:33:23'),
(443, 29, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2025-12-31 14:33:23', '2025-12-31 14:33:23'),
(444, 29, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2025-12-31 14:33:23', '2025-12-31 14:33:23'),
(445, 29, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2025-12-31 14:33:23', '2025-12-31 14:33:23'),
(446, 29, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2025-12-31 14:33:23', '2025-12-31 14:33:23'),
(447, 29, 'languages', '{\"content\":{\"items\":[]}}', 5, 1, '2025-12-31 14:33:23', '2025-12-31 14:33:23'),
(448, 29, '', '{\"content\":{\"primary_color\":\"#60a5fa\"}}', 999, 0, '2025-12-31 14:33:23', '2025-12-31 14:33:23'),
(449, 30, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Dwarka , Delhi\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\",\"first_name\":\"Prabhat\",\"last_name\":\"Paswan\"}}', 0, 1, '2025-12-31 14:33:49', '2025-12-31 14:33:52'),
(450, 30, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2025-12-31 14:33:49', '2025-12-31 14:33:49'),
(451, 30, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"},{\"job_title\":\"\",\"company_name\":\"\",\"location\":\"\",\"start_date\":\"\",\"end_date\":\"\",\"is_current\":false,\"description\":\"\"},{\"job_title\":\"\",\"company_name\":\"\",\"location\":\"\",\"start_date\":\"\",\"end_date\":\"\",\"is_current\":false,\"description\":\"\"},{\"job_title\":\"\",\"company_name\":\"\",\"location\":\"\",\"start_date\":\"\",\"end_date\":\"\",\"is_current\":false,\"description\":\"\"},{\"job_title\":\"\",\"company_name\":\"\",\"location\":\"\",\"start_date\":\"\",\"end_date\":\"\",\"is_current\":false,\"description\":\"\"}]}}', 2, 1, '2025-12-31 14:33:49', '2025-12-31 14:34:10'),
(452, 30, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2025-12-31 14:33:49', '2025-12-31 14:33:49'),
(453, 30, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2025-12-31 14:33:49', '2025-12-31 14:33:49'),
(454, 30, 'certifications', '{\"content\":[]}', 5, 1, '2025-12-31 14:33:49', '2025-12-31 14:33:49'),
(455, 30, 'achievements', '{\"content\":[]}', 6, 1, '2025-12-31 14:33:49', '2025-12-31 14:33:49'),
(456, 30, '', '{\"content\":{\"primary_color\":\"#2563eb\"}}', 999, 0, '2025-12-31 14:33:49', '2025-12-31 14:33:49'),
(457, 30, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:33:52', '2025-12-31 14:33:52'),
(458, 30, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:33:52', '2025-12-31 14:33:52'),
(459, 30, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:33:53', '2025-12-31 14:33:53'),
(460, 30, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:33:53', '2025-12-31 14:33:53'),
(461, 30, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:33:53', '2025-12-31 14:33:53'),
(462, 30, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:33:53', '2025-12-31 14:33:53'),
(463, 30, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:33:55', '2025-12-31 14:33:55'),
(464, 30, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:33:55', '2025-12-31 14:33:55'),
(465, 30, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:33:55', '2025-12-31 14:33:55'),
(466, 30, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:33:55', '2025-12-31 14:33:55'),
(467, 30, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:33:57', '2025-12-31 14:33:57'),
(468, 30, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:33:57', '2025-12-31 14:33:57'),
(469, 30, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:33:57', '2025-12-31 14:33:57'),
(470, 30, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:33:57', '2025-12-31 14:33:57'),
(471, 30, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:34:02', '2025-12-31 14:34:02'),
(472, 30, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:34:02', '2025-12-31 14:34:02'),
(473, 30, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:34:10', '2025-12-31 14:34:10'),
(474, 30, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:34:10', '2025-12-31 14:34:10'),
(475, 30, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:34:11', '2025-12-31 14:34:11'),
(476, 30, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:34:11', '2025-12-31 14:34:11'),
(477, 30, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:34:13', '2025-12-31 14:34:13'),
(478, 30, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:34:13', '2025-12-31 14:34:13'),
(479, 30, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:34:14', '2025-12-31 14:34:14'),
(480, 30, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:34:14', '2025-12-31 14:34:14'),
(481, 30, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:34:15', '2025-12-31 14:34:15'),
(482, 30, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:34:15', '2025-12-31 14:34:15'),
(483, 30, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 14:34:17', '2025-12-31 14:34:17'),
(484, 30, '', '{\"content\":[]}', 0, 1, '2025-12-31 14:34:17', '2025-12-31 14:34:17'),
(485, 31, 'header', '{\"content\":{\"full_name\":\"anuj Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Dwarka , Delhi\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\",\"first_name\":\"anuj\",\"last_name\":\"Paswan\"}}', 0, 1, '2025-12-31 15:08:41', '2025-12-31 15:08:58'),
(486, 31, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2025-12-31 15:08:41', '2025-12-31 15:08:41'),
(487, 31, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2025-12-31 15:08:41', '2025-12-31 15:08:41'),
(488, 31, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2025-12-31 15:08:41', '2025-12-31 15:08:41'),
(489, 31, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2025-12-31 15:08:41', '2025-12-31 15:08:41'),
(490, 31, '', '{\"content\":{\"primary_color\":\"#2563eb\"}}', 999, 0, '2025-12-31 15:08:41', '2025-12-31 15:08:41'),
(491, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:08:45', '2025-12-31 15:08:45'),
(492, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:08:45', '2025-12-31 15:08:45'),
(493, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:08:58', '2025-12-31 15:08:58'),
(494, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:08:58', '2025-12-31 15:08:58'),
(495, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:09:00', '2025-12-31 15:09:00'),
(496, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:09:00', '2025-12-31 15:09:00'),
(497, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:09:00', '2025-12-31 15:09:00'),
(498, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:09:00', '2025-12-31 15:09:00'),
(499, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:09:10', '2025-12-31 15:09:10'),
(500, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:09:10', '2025-12-31 15:09:10'),
(501, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:09:11', '2025-12-31 15:09:11'),
(502, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:09:11', '2025-12-31 15:09:11'),
(503, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:09:12', '2025-12-31 15:09:12'),
(504, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:09:12', '2025-12-31 15:09:12'),
(505, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:09:14', '2025-12-31 15:09:14'),
(506, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:09:14', '2025-12-31 15:09:14'),
(507, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:09:15', '2025-12-31 15:09:15'),
(508, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:09:15', '2025-12-31 15:09:15'),
(509, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:09:16', '2025-12-31 15:09:16'),
(510, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:09:16', '2025-12-31 15:09:16'),
(511, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:09:45', '2025-12-31 15:09:45'),
(512, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:09:45', '2025-12-31 15:09:45'),
(513, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:10:15', '2025-12-31 15:10:15'),
(514, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:10:15', '2025-12-31 15:10:15'),
(515, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:10:45', '2025-12-31 15:10:45'),
(516, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:10:45', '2025-12-31 15:10:45'),
(517, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:11:16', '2025-12-31 15:11:16'),
(518, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:11:16', '2025-12-31 15:11:16'),
(519, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:11:45', '2025-12-31 15:11:45'),
(520, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:11:45', '2025-12-31 15:11:45'),
(521, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:12:15', '2025-12-31 15:12:15'),
(522, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:12:15', '2025-12-31 15:12:15'),
(523, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:12:45', '2025-12-31 15:12:45'),
(524, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:12:45', '2025-12-31 15:12:45'),
(525, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:13:15', '2025-12-31 15:13:15'),
(526, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:13:15', '2025-12-31 15:13:15'),
(527, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:13:45', '2025-12-31 15:13:45'),
(528, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:13:45', '2025-12-31 15:13:45'),
(529, 31, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:14:15', '2025-12-31 15:14:15'),
(530, 31, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:14:15', '2025-12-31 15:14:15'),
(531, 32, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"India\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\",\"first_name\":\"Prabhat\",\"last_name\":\"Paswan\",\"city\":\"\",\"country\":\"India\",\"pin_code\":\"110078\"}}', 0, 1, '2025-12-31 15:19:18', '2025-12-31 15:19:55'),
(532, 32, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2025-12-31 15:19:18', '2025-12-31 15:19:18'),
(533, 32, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2025-12-31 15:19:18', '2025-12-31 15:19:18'),
(534, 32, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2025-12-31 15:19:18', '2025-12-31 15:19:18'),
(535, 32, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2025-12-31 15:19:18', '2025-12-31 15:19:18'),
(536, 32, 'languages', '{\"content\":{\"items\":[]}}', 5, 1, '2025-12-31 15:19:18', '2025-12-31 15:19:18'),
(537, 32, '', '{\"content\":{\"primary_color\":\"#2563eb\"}}', 999, 0, '2025-12-31 15:19:18', '2025-12-31 15:19:18'),
(538, 32, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:19:29', '2025-12-31 15:19:29'),
(539, 32, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:19:29', '2025-12-31 15:19:29'),
(540, 32, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:19:49', '2025-12-31 15:19:49'),
(541, 32, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:19:49', '2025-12-31 15:19:49'),
(542, 32, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:19:55', '2025-12-31 15:19:55'),
(543, 32, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:19:56', '2025-12-31 15:19:56'),
(544, 32, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:20:29', '2025-12-31 15:20:29'),
(545, 32, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:20:29', '2025-12-31 15:20:29'),
(546, 32, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:20:59', '2025-12-31 15:20:59'),
(547, 32, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:20:59', '2025-12-31 15:20:59'),
(548, 32, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:21:30', '2025-12-31 15:21:30'),
(549, 32, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:21:30', '2025-12-31 15:21:30'),
(550, 32, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:21:59', '2025-12-31 15:21:59'),
(551, 32, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:21:59', '2025-12-31 15:21:59'),
(552, 32, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:22:31', '2025-12-31 15:22:31'),
(553, 32, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:22:31', '2025-12-31 15:22:31'),
(554, 32, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:22:59', '2025-12-31 15:22:59'),
(555, 32, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:22:59', '2025-12-31 15:22:59'),
(556, 32, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:23:31', '2025-12-31 15:23:31'),
(557, 32, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:23:31', '2025-12-31 15:23:31'),
(558, 32, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:24:14', '2025-12-31 15:24:14'),
(559, 32, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:24:14', '2025-12-31 15:24:14'),
(560, 32, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:25:14', '2025-12-31 15:25:14'),
(561, 32, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:25:14', '2025-12-31 15:25:14'),
(562, 32, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:25:33', '2025-12-31 15:25:33'),
(563, 32, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:25:33', '2025-12-31 15:25:33'),
(564, 33, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Dwarka , Delhi\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\",\"first_name\":\"Prabhat\",\"last_name\":\"Paswan\",\"pin_code\":\"110078\"}}', 0, 1, '2025-12-31 15:47:23', '2025-12-31 15:47:37'),
(565, 33, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2025-12-31 15:47:23', '2025-12-31 15:47:23'),
(566, 33, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2025-12-31 15:47:23', '2025-12-31 15:47:23'),
(567, 33, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2025-12-31 15:47:23', '2025-12-31 15:47:23'),
(568, 33, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2025-12-31 15:47:23', '2025-12-31 15:47:23'),
(569, 33, 'projects', '{\"content\":[]}', 5, 1, '2025-12-31 15:47:23', '2025-12-31 15:47:23'),
(570, 33, 'achievements', '{\"content\":[]}', 6, 1, '2025-12-31 15:47:24', '2025-12-31 15:47:24'),
(571, 33, '', '{\"content\":{\"primary_color\":\"#2563eb\"}}', 999, 0, '2025-12-31 15:47:24', '2025-12-31 15:47:24'),
(572, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:47:31', '2025-12-31 15:47:31'),
(573, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:47:31', '2025-12-31 15:47:31'),
(574, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:47:37', '2025-12-31 15:47:37'),
(575, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:47:37', '2025-12-31 15:47:37'),
(576, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:47:42', '2025-12-31 15:47:42'),
(577, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:47:42', '2025-12-31 15:47:42'),
(578, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:48:08', '2025-12-31 15:48:08'),
(579, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:48:08', '2025-12-31 15:48:08'),
(580, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:48:39', '2025-12-31 15:48:39'),
(581, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:48:39', '2025-12-31 15:48:39'),
(582, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:49:09', '2025-12-31 15:49:09'),
(583, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:49:09', '2025-12-31 15:49:09'),
(584, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:49:39', '2025-12-31 15:49:39'),
(585, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:49:39', '2025-12-31 15:49:39'),
(586, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:50:18', '2025-12-31 15:50:18'),
(587, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:50:18', '2025-12-31 15:50:18'),
(588, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:50:28', '2025-12-31 15:50:28'),
(589, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:50:28', '2025-12-31 15:50:28'),
(590, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:50:38', '2025-12-31 15:50:38'),
(591, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:50:38', '2025-12-31 15:50:38'),
(592, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:51:27', '2025-12-31 15:51:27'),
(593, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:51:27', '2025-12-31 15:51:27'),
(594, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:51:38', '2025-12-31 15:51:38'),
(595, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:51:38', '2025-12-31 15:51:38'),
(596, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:53:46', '2025-12-31 15:53:46'),
(597, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:53:46', '2025-12-31 15:53:46'),
(598, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:54:48', '2025-12-31 15:54:48'),
(599, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:54:49', '2025-12-31 15:54:49'),
(600, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:55:09', '2025-12-31 15:55:09'),
(601, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:55:09', '2025-12-31 15:55:09'),
(602, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:57:05', '2025-12-31 15:57:05'),
(603, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:57:05', '2025-12-31 15:57:05'),
(604, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:57:08', '2025-12-31 15:57:08'),
(605, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:57:08', '2025-12-31 15:57:08'),
(606, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:57:48', '2025-12-31 15:57:48'),
(607, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:57:48', '2025-12-31 15:57:48'),
(608, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 15:57:51', '2025-12-31 15:57:51'),
(609, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 15:57:52', '2025-12-31 15:57:52');
INSERT INTO `resume_sections` (`id`, `resume_id`, `section_type`, `section_data`, `sort_order`, `is_visible`, `created_at`, `updated_at`) VALUES
(610, 34, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Sector-12 Dwarka, India\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\",\"first_name\":\"Prabhat\",\"last_name\":\"Paswan\",\"country\":\"India\",\"city\":\"Sector-12 Dwarka\",\"pin_code\":\"110078\"}}', 0, 1, '2025-12-31 16:00:31', '2025-12-31 16:01:39'),
(611, 34, 'summary', '{\"content\":{\"text\":\"We are seeking a skilled Software Developer to join our development team. The candidate will work on designing, developing, and maintaining web-based applications using PHP, Laravel, JavaScript, and React.js. You will collaborate with designers, backend developers, and project managers to deliver high-quality, performance-driven solutions.\\n\\n🔧 Key Responsibilities\\n\\nDevelop and maintain web applications using PHP and Laravel\\n\\nBuild dynamic and responsive user interfaces using React.js, JavaScript, jQuery, and Tailwind CSS\\n\\nFollow OOP principles and MVC architecture for clean and maintainable code\\n\\nIntegrate frontend interfaces with backend APIs\\n\\nOptimize applications for speed, performance, and scalability\\n\\nDebug, test, and troubleshoot application issues\\n\\nWrite clean, reusable, and well-documented code\\n\\nCollaborate with cross-functional teams to understand requirements\\n\\nEnsure application security and data protection best practices\\n\\nParticipate in code reviews and continuous improvement\\n\\n🧠 Required Skills & Technologies\\nBackend Skills\\n\\nStrong knowledge of PHP\\n\\nExperience with Laravel Framework\\n\\nUnderstanding of OOP (Object-Oriented Programming)\\n\\nKnowledge of MVC architecture\\n\\nREST API development and integration\\n\\nMySQL \\/ Database concepts\\n\\nFrontend Skills\\n\\nProficiency in JavaScript\\n\\nExperience with React.js\\n\\nKnowledge of jQuery\\n\\nStrong understanding of HTML5 and CSS3\\n\\nExperience using Tailwind CSS for responsive UI design\\n\\nAdditional Skills\\n\\nGit \\/ Version control\\n\\nBasic knowledge of server deployment (Linux preferred)\\n\\nUnderstanding of web security practices\\n\\nAbility to write optimized and scalable code\"}}', 1, 1, '2025-12-31 16:00:31', '2025-12-31 16:09:06'),
(612, 34, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04\",\"end_date\":\"\",\"is_current\":true,\"description\":\"We are seeking a skilled Software Developer to join our development team. The candidate will work on designing, developing, and maintaining web-based applications using PHP, Laravel, JavaScript, and React.js. You will collaborate with designers, backend developers, and project managers to deliver high-quality, performance-driven solutions.\",\"location\":\"Dwarka Delhi\"},{\"job_title\":\"Full Stack Junior Php Developer\",\"company_name\":\"Mindware\",\"location\":\"\",\"start_date\":\"2023-04\",\"end_date\":\"2024-12\",\"is_current\":true,\"description\":\"We are seeking a skilled Software Developer to join our development team. The candidate will work on designing, developing, and maintaining web-based applications using PHP, Laravel, JavaScript, and React.js. You will collaborate with designers, backend developers, and project managers to deliver high-quality, performance-driven solutions.\"},{\"job_title\":\"Full Stack Senior Php Developer\",\"company_name\":\"Mindware\",\"location\":\"Dwarka Sector-12\",\"start_date\":\"2024-12\",\"end_date\":\"\",\"is_current\":true,\"description\":\"We are seeking a skilled Software Developer to join our development team. The candidate will work on designing, developing, and maintaining web-based applications using PHP, Laravel, JavaScript, and React.js. You will collaborate with designers, backend developers, and project managers to deliver high-quality, performance-driven solutions.\"}]}}', 2, 1, '2025-12-31 16:00:31', '2025-12-31 16:04:58'),
(613, 34, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2025-12-31 16:00:31', '2025-12-31 16:00:31'),
(614, 34, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2025-12-31 16:00:31', '2025-12-31 16:00:31'),
(615, 34, 'languages', '{\"content\":{\"items\":[]}}', 5, 1, '2025-12-31 16:00:31', '2025-12-31 16:00:31'),
(616, 34, '', '{\"content\":{\"primary_color\":\"#2563eb\"}}', 999, 0, '2025-12-31 16:00:31', '2025-12-31 16:00:31'),
(617, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:00:45', '2025-12-31 16:00:45'),
(618, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:00:45', '2025-12-31 16:00:45'),
(619, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:00:51', '2025-12-31 16:00:51'),
(620, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:00:51', '2025-12-31 16:00:51'),
(621, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:00:51', '2025-12-31 16:00:51'),
(622, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:00:51', '2025-12-31 16:00:51'),
(623, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:01:27', '2025-12-31 16:01:27'),
(624, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:01:27', '2025-12-31 16:01:27'),
(625, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:01:35', '2025-12-31 16:01:35'),
(626, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:01:35', '2025-12-31 16:01:35'),
(627, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:01:39', '2025-12-31 16:01:39'),
(628, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:01:39', '2025-12-31 16:01:39'),
(629, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:01:39', '2025-12-31 16:01:39'),
(630, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:01:39', '2025-12-31 16:01:39'),
(631, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:02:03', '2025-12-31 16:02:03'),
(632, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:02:03', '2025-12-31 16:02:03'),
(633, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:02:09', '2025-12-31 16:02:09'),
(634, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:02:09', '2025-12-31 16:02:09'),
(635, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:02:12', '2025-12-31 16:02:12'),
(636, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:02:12', '2025-12-31 16:02:12'),
(637, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:02:27', '2025-12-31 16:02:27'),
(638, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:02:27', '2025-12-31 16:02:27'),
(639, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:02:50', '2025-12-31 16:02:50'),
(640, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:02:50', '2025-12-31 16:02:50'),
(641, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:03:10', '2025-12-31 16:03:10'),
(642, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:03:10', '2025-12-31 16:03:10'),
(643, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:03:18', '2025-12-31 16:03:18'),
(644, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:03:18', '2025-12-31 16:03:18'),
(645, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:03:19', '2025-12-31 16:03:19'),
(646, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:03:19', '2025-12-31 16:03:19'),
(647, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:03:27', '2025-12-31 16:03:27'),
(648, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:03:27', '2025-12-31 16:03:27'),
(649, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:03:27', '2025-12-31 16:03:27'),
(650, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:03:27', '2025-12-31 16:03:27'),
(651, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:03:38', '2025-12-31 16:03:38'),
(652, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:03:38', '2025-12-31 16:03:38'),
(653, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:03:44', '2025-12-31 16:03:44'),
(654, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:03:44', '2025-12-31 16:03:44'),
(655, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:03:49', '2025-12-31 16:03:49'),
(656, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:03:49', '2025-12-31 16:03:49'),
(657, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:03:55', '2025-12-31 16:03:55'),
(658, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:03:55', '2025-12-31 16:03:55'),
(659, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:03:56', '2025-12-31 16:03:56'),
(660, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:03:56', '2025-12-31 16:03:56'),
(661, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:04:02', '2025-12-31 16:04:02'),
(662, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:04:02', '2025-12-31 16:04:02'),
(663, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:04:15', '2025-12-31 16:04:15'),
(664, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:04:15', '2025-12-31 16:04:15'),
(665, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:04:19', '2025-12-31 16:04:19'),
(666, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:04:19', '2025-12-31 16:04:19'),
(667, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:04:31', '2025-12-31 16:04:31'),
(668, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:04:31', '2025-12-31 16:04:31'),
(669, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:04:31', '2025-12-31 16:04:31'),
(670, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:04:31', '2025-12-31 16:04:31'),
(671, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:04:36', '2025-12-31 16:04:36'),
(672, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:04:36', '2025-12-31 16:04:36'),
(673, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:04:46', '2025-12-31 16:04:46'),
(674, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:04:46', '2025-12-31 16:04:46'),
(675, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:04:49', '2025-12-31 16:04:49'),
(676, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:04:49', '2025-12-31 16:04:49'),
(677, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:04:49', '2025-12-31 16:04:49'),
(678, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:04:49', '2025-12-31 16:04:49'),
(679, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:04:58', '2025-12-31 16:04:58'),
(680, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:04:58', '2025-12-31 16:04:58'),
(681, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:05:29', '2025-12-31 16:05:29'),
(682, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:05:29', '2025-12-31 16:05:29'),
(683, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:05:38', '2025-12-31 16:05:38'),
(684, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:05:38', '2025-12-31 16:05:38'),
(685, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:06:06', '2025-12-31 16:06:06'),
(686, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:06:06', '2025-12-31 16:06:06'),
(687, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:06:24', '2025-12-31 16:06:24'),
(688, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:06:24', '2025-12-31 16:06:24'),
(689, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:06:24', '2025-12-31 16:06:24'),
(690, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:06:24', '2025-12-31 16:06:24'),
(691, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:06:29', '2025-12-31 16:06:29'),
(692, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:06:29', '2025-12-31 16:06:29'),
(693, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:06:42', '2025-12-31 16:06:42'),
(694, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:06:42', '2025-12-31 16:06:42'),
(695, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:07:08', '2025-12-31 16:07:08'),
(696, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:07:08', '2025-12-31 16:07:08'),
(697, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:07:13', '2025-12-31 16:07:13'),
(698, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:07:13', '2025-12-31 16:07:13'),
(699, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:07:14', '2025-12-31 16:07:14'),
(700, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:07:14', '2025-12-31 16:07:14'),
(701, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:07:18', '2025-12-31 16:07:18'),
(702, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:07:18', '2025-12-31 16:07:18'),
(703, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:07:39', '2025-12-31 16:07:39'),
(704, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:07:39', '2025-12-31 16:07:39'),
(705, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:07:59', '2025-12-31 16:07:59'),
(706, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:07:59', '2025-12-31 16:07:59'),
(707, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:08:09', '2025-12-31 16:08:09'),
(708, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:08:09', '2025-12-31 16:08:09'),
(709, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:08:11', '2025-12-31 16:08:11'),
(710, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:08:11', '2025-12-31 16:08:11'),
(711, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:09:06', '2025-12-31 16:09:06'),
(712, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:09:06', '2025-12-31 16:09:06'),
(713, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:09:09', '2025-12-31 16:09:09'),
(714, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:09:09', '2025-12-31 16:09:09'),
(715, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:09:35', '2025-12-31 16:09:35'),
(716, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:09:35', '2025-12-31 16:09:35'),
(717, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:10:17', '2025-12-31 16:10:17'),
(718, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:10:17', '2025-12-31 16:10:17'),
(719, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:10:17', '2025-12-31 16:10:17'),
(720, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:10:17', '2025-12-31 16:10:17'),
(721, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:10:36', '2025-12-31 16:10:36'),
(722, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:10:36', '2025-12-31 16:10:36'),
(723, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:10:45', '2025-12-31 16:10:45'),
(724, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:10:45', '2025-12-31 16:10:45'),
(725, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:10:45', '2025-12-31 16:10:45'),
(726, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:10:45', '2025-12-31 16:10:45'),
(727, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[]}}', 0, 1, '2025-12-31 16:10:48', '2025-12-31 16:10:48'),
(728, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:10:48', '2025-12-31 16:10:48'),
(729, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[]}}', 0, 1, '2025-12-31 16:10:55', '2025-12-31 16:10:55'),
(730, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:10:55', '2025-12-31 16:10:55'),
(731, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:11:13', '2025-12-31 16:11:13'),
(732, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:11:13', '2025-12-31 16:11:13'),
(733, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:11:16', '2025-12-31 16:11:16'),
(734, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:11:16', '2025-12-31 16:11:16'),
(735, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:11:46', '2025-12-31 16:11:46'),
(736, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:11:46', '2025-12-31 16:11:46'),
(737, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:12:12', '2025-12-31 16:12:12'),
(738, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:12:12', '2025-12-31 16:12:12'),
(739, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:12:16', '2025-12-31 16:12:16'),
(740, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:12:16', '2025-12-31 16:12:16'),
(741, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:12:46', '2025-12-31 16:12:46'),
(742, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:12:46', '2025-12-31 16:12:46'),
(743, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:13:14', '2025-12-31 16:13:14'),
(744, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:13:14', '2025-12-31 16:13:14'),
(745, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:13:16', '2025-12-31 16:13:16'),
(746, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:13:16', '2025-12-31 16:13:16'),
(747, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:13:46', '2025-12-31 16:13:46'),
(748, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:13:46', '2025-12-31 16:13:46'),
(749, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:14:13', '2025-12-31 16:14:13'),
(750, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:14:13', '2025-12-31 16:14:13'),
(751, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:14:16', '2025-12-31 16:14:16'),
(752, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:14:16', '2025-12-31 16:14:16'),
(753, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:14:46', '2025-12-31 16:14:46'),
(754, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:14:46', '2025-12-31 16:14:46'),
(755, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:15:16', '2025-12-31 16:15:16'),
(756, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:15:16', '2025-12-31 16:15:16'),
(757, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:15:46', '2025-12-31 16:15:46'),
(758, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:15:46', '2025-12-31 16:15:46'),
(759, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:16:17', '2025-12-31 16:16:17'),
(760, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:16:17', '2025-12-31 16:16:17'),
(761, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:16:46', '2025-12-31 16:16:46'),
(762, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:16:46', '2025-12-31 16:16:46'),
(763, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:17:17', '2025-12-31 16:17:17'),
(764, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:17:17', '2025-12-31 16:17:17'),
(765, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:17:49', '2025-12-31 16:17:49'),
(766, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:17:49', '2025-12-31 16:17:49'),
(767, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:18:16', '2025-12-31 16:18:16'),
(768, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:18:16', '2025-12-31 16:18:16'),
(769, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:18:46', '2025-12-31 16:18:46'),
(770, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:18:46', '2025-12-31 16:18:46'),
(771, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:19:16', '2025-12-31 16:19:16'),
(772, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:19:16', '2025-12-31 16:19:16'),
(773, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:19:47', '2025-12-31 16:19:47'),
(774, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:19:47', '2025-12-31 16:19:47'),
(775, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:20:16', '2025-12-31 16:20:16'),
(776, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:20:16', '2025-12-31 16:20:16'),
(777, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:20:46', '2025-12-31 16:20:46'),
(778, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:20:46', '2025-12-31 16:20:46'),
(779, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:21:26', '2025-12-31 16:21:26'),
(780, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:21:26', '2025-12-31 16:21:26'),
(781, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:23:21', '2025-12-31 16:23:21'),
(782, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:23:21', '2025-12-31 16:23:21'),
(783, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:23:22', '2025-12-31 16:23:22'),
(784, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:23:22', '2025-12-31 16:23:22'),
(785, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:23:29', '2025-12-31 16:23:29'),
(786, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:23:29', '2025-12-31 16:23:29'),
(787, 34, '', '{\"content\":{\"projects\":[[]],\"certifications\":[[]]}}', 0, 1, '2025-12-31 16:23:37', '2025-12-31 16:23:37'),
(788, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:23:37', '2025-12-31 16:23:37'),
(789, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:23:38', '2025-12-31 16:23:38'),
(790, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:23:38', '2025-12-31 16:23:38'),
(791, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Shop \"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:23:46', '2025-12-31 16:23:46'),
(792, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:23:46', '2025-12-31 16:23:46'),
(793, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:24:08', '2025-12-31 16:24:08'),
(794, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:24:08', '2025-12-31 16:24:08'),
(795, 34, '', '{\"content\":{\"projects\":[{\"title\":\"\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:24:25', '2025-12-31 16:24:25'),
(796, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:24:25', '2025-12-31 16:24:25'),
(797, 34, '', '{\"content\":{\"projects\":[{\"title\":\"\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:24:46', '2025-12-31 16:24:46'),
(798, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:24:46', '2025-12-31 16:24:46'),
(799, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:25:10', '2025-12-31 16:25:10'),
(800, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:25:10', '2025-12-31 16:25:10'),
(801, 34, '', '{\"content\":{\"projects\":[{\"title\":\"\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:25:16', '2025-12-31 16:25:16'),
(802, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:25:16', '2025-12-31 16:25:16'),
(803, 34, '', '{\"content\":{\"projects\":[{\"title\":\"\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:25:46', '2025-12-31 16:25:46'),
(804, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:25:46', '2025-12-31 16:25:46'),
(805, 34, '', '{\"content\":{\"projects\":[{\"title\":\"\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:26:20', '2025-12-31 16:26:20'),
(806, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:26:20', '2025-12-31 16:26:20'),
(807, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware em\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:26:46', '2025-12-31 16:26:46'),
(808, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:26:46', '2025-12-31 16:26:46'),
(809, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerece\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:26:59', '2025-12-31 16:26:59'),
(810, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:26:59', '2025-12-31 16:26:59'),
(811, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:27:05', '2025-12-31 16:27:05'),
(812, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:27:05', '2025-12-31 16:27:05'),
(813, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Deve\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:27:17', '2025-12-31 16:27:17'),
(814, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:27:17', '2025-12-31 16:27:17'),
(815, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Developer\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:27:22', '2025-12-31 16:27:22'),
(816, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:27:22', '2025-12-31 16:27:22'),
(817, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Developer\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:27:47', '2025-12-31 16:27:47'),
(818, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:27:47', '2025-12-31 16:27:47'),
(819, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Developer\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:27:56', '2025-12-31 16:27:56'),
(820, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:27:56', '2025-12-31 16:27:56'),
(821, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Developer\",\"url\":\"https:\\/\\/shop.tscprintersindia.com\\/\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:28:01', '2025-12-31 16:28:01'),
(822, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:28:01', '2025-12-31 16:28:01'),
(823, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Developer\",\"url\":\"https:\\/\\/shop.tscprintersindia.com\\/\",\"start_date\":\"2025-02\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:28:09', '2025-12-31 16:28:09'),
(824, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:28:09', '2025-12-31 16:28:09'),
(825, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Developer\",\"url\":\"https:\\/\\/shop.tscprintersindia.com\\/\",\"start_date\":\"2025-02\",\"end_date\":\"2025-02\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:28:13', '2025-12-31 16:28:13'),
(826, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:28:13', '2025-12-31 16:28:13'),
(827, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Developer\",\"url\":\"https:\\/\\/shop.tscprintersindia.com\\/\",\"start_date\":\"2025-02\",\"end_date\":\"2025-02\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:28:16', '2025-12-31 16:28:16'),
(828, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:28:16', '2025-12-31 16:28:16'),
(829, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Developer\",\"url\":\"https:\\/\\/shop.tscprintersindia.com\\/\",\"start_date\":\"2025-02\",\"end_date\":\"2025-02\",\"description\":\"We sell \"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:28:48', '2025-12-31 16:28:48'),
(830, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:28:48', '2025-12-31 16:28:48'),
(831, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Developer\",\"url\":\"https:\\/\\/shop.tscprintersindia.com\\/\",\"start_date\":\"2025-02\",\"end_date\":\"2025-02\",\"description\":\"We sell all kind of hardware likes \"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:29:18', '2025-12-31 16:29:18'),
(832, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:29:18', '2025-12-31 16:29:18'),
(833, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Developer\",\"url\":\"https:\\/\\/shop.tscprintersindia.com\\/\",\"start_date\":\"2025-02\",\"end_date\":\"2025-02\",\"description\":\"We sell all kind of hardware likes Barcode printers, barcode scanner, \"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:29:46', '2025-12-31 16:29:46'),
(834, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:29:46', '2025-12-31 16:29:46'),
(835, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Developer\",\"url\":\"https:\\/\\/shop.tscprintersindia.com\\/\",\"start_date\":\"2025-02\",\"end_date\":\"2025-02\",\"description\":\"We sell all kind of hardware likes Barcode printers, barcode scanner, barcode \"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:30:16', '2025-12-31 16:30:16'),
(836, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:30:16', '2025-12-31 16:30:16'),
(837, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Developer\",\"url\":\"https:\\/\\/shop.tscprintersindia.com\\/\",\"start_date\":\"2025-02\",\"end_date\":\"2025-02\",\"description\":\"We sell all kind of hardware likes Barcode printers, barcode scanner, barcode \"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:30:54', '2025-12-31 16:30:54'),
(838, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:30:54', '2025-12-31 16:30:54'),
(839, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Developer\",\"url\":\"https:\\/\\/shop.tscprintersindia.com\\/\",\"start_date\":\"2025-02\",\"end_date\":\"2025-02\",\"description\":\"We sell all kind of hardware likes Barcode printers, barcode scanner, barcode labels\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:31:16', '2025-12-31 16:31:16'),
(840, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:31:16', '2025-12-31 16:31:16'),
(841, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Developer\",\"url\":\"https:\\/\\/shop.tscprintersindia.com\\/\",\"start_date\":\"2025-02\",\"end_date\":\"2025-02\",\"description\":\"We sell all kind of hardware likes Barcode printers, barcode scanner, barcode labels and more. User can register login and \"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:31:46', '2025-12-31 16:31:46'),
(842, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:31:46', '2025-12-31 16:31:46'),
(843, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Developer\",\"url\":\"https:\\/\\/shop.tscprintersindia.com\\/\",\"start_date\":\"2025-02\",\"end_date\":\"2025-02\",\"description\":\"We sell all kind of hardware likes Barcode printers, barcode scanner, barcode labels and more. User can register login and user can order any kind items and view invoice and recipt \"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:32:16', '2025-12-31 16:32:16'),
(844, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:32:16', '2025-12-31 16:32:16'),
(845, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Developer\",\"url\":\"https:\\/\\/shop.tscprintersindia.com\\/\",\"start_date\":\"2025-02\",\"end_date\":\"2025-02\",\"description\":\"We sell all kind of hardware likes Barcode printers, barcode scanner, barcode labels and more. User can register login and user can order any kind items and view invoice and recipt of product\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:32:22', '2025-12-31 16:32:22'),
(846, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:32:22', '2025-12-31 16:32:22'),
(847, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Developer\",\"url\":\"https:\\/\\/shop.tscprintersindia.com\\/\",\"start_date\":\"2025-02\",\"end_date\":\"2025-02\",\"description\":\"We sell all kind of hardware likes Barcode printers, barcode scanner, barcode labels and more. User can register login and user can order any kind items and view invoice and receipt of product.\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:32:26', '2025-12-31 16:32:26'),
(848, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:32:26', '2025-12-31 16:32:26'),
(849, 34, '', '{\"content\":{\"projects\":[{\"title\":\"Hardware Ecommerce \",\"role\":\"Full Stack Developer\",\"url\":\"https:\\/\\/shop.tscprintersindia.com\\/\",\"start_date\":\"2025-02\",\"end_date\":\"2025-02\",\"description\":\"We sell all kind of hardware likes Barcode printers, barcode scanner, barcode labels and more. User can register login and user can order any kind items and view invoice and receipt of product.\"}],\"certifications\":[]}}', 0, 1, '2025-12-31 16:32:26', '2025-12-31 16:32:26'),
(850, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:32:26', '2025-12-31 16:32:26'),
(851, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:32:31', '2025-12-31 16:32:31'),
(852, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:32:31', '2025-12-31 16:32:31'),
(853, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:32:58', '2025-12-31 16:32:58'),
(854, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:32:58', '2025-12-31 16:32:58'),
(855, 34, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:33:28', '2025-12-31 16:33:28'),
(856, 34, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:33:28', '2025-12-31 16:33:28'),
(857, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:35:52', '2025-12-31 16:35:52'),
(858, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:35:52', '2025-12-31 16:35:52'),
(859, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:36:09', '2025-12-31 16:36:09'),
(860, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:36:09', '2025-12-31 16:36:09'),
(861, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:36:38', '2025-12-31 16:36:38'),
(862, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:36:38', '2025-12-31 16:36:38'),
(863, 33, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 16:37:08', '2025-12-31 16:37:08'),
(864, 33, '', '{\"content\":[]}', 0, 1, '2025-12-31 16:37:08', '2025-12-31 16:37:08'),
(865, 35, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Dwarka , Delhi\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\",\"first_name\":\"Prabhat\",\"last_name\":\"Paswan\"}}', 0, 1, '2025-12-31 17:24:07', '2025-12-31 17:24:40'),
(866, 35, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2025-12-31 17:24:07', '2025-12-31 17:24:07'),
(867, 35, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2025-12-31 17:24:07', '2025-12-31 17:24:07'),
(868, 35, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2025-12-31 17:24:07', '2025-12-31 17:24:07'),
(869, 35, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2025-12-31 17:24:07', '2025-12-31 17:24:07'),
(870, 35, 'certifications', '{\"content\":[]}', 5, 1, '2025-12-31 17:24:07', '2025-12-31 17:24:07'),
(871, 35, 'achievements', '{\"content\":[]}', 6, 1, '2025-12-31 17:24:07', '2025-12-31 17:24:07'),
(872, 35, '', '{\"content\":{\"primary_color\":\"#2563eb\"}}', 999, 0, '2025-12-31 17:24:07', '2025-12-31 17:24:07'),
(873, 35, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 17:24:40', '2025-12-31 17:24:40'),
(874, 35, '', '{\"content\":[]}', 0, 1, '2025-12-31 17:24:40', '2025-12-31 17:24:40'),
(875, 35, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2025-12-31 17:24:46', '2025-12-31 17:24:46'),
(876, 35, '', '{\"content\":[]}', 0, 1, '2025-12-31 17:24:46', '2025-12-31 17:24:46'),
(877, 36, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Dwarka , Delhi\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\"}}', 0, 1, '2026-01-01 10:42:17', '2026-01-01 10:42:17'),
(878, 36, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2026-01-01 10:42:17', '2026-01-01 10:42:17'),
(879, 36, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2026-01-01 10:42:17', '2026-01-01 10:42:17'),
(880, 36, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2026-01-01 10:42:17', '2026-01-01 10:42:17'),
(881, 36, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2026-01-01 10:42:17', '2026-01-01 10:42:17'),
(882, 36, 'certifications', '{\"content\":[]}', 5, 1, '2026-01-01 10:42:17', '2026-01-01 10:42:17'),
(883, 36, 'achievements', '{\"content\":[]}', 6, 1, '2026-01-01 10:42:17', '2026-01-01 10:42:17'),
(884, 36, '', '{\"content\":{\"primary_color\":\"#2563eb\"}}', 999, 0, '2026-01-01 10:42:17', '2026-01-01 10:42:17'),
(885, 37, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Dwarka , Delhi\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\",\"first_name\":\"Prabhat\",\"last_name\":\"Paswan\"}}', 0, 1, '2026-01-02 11:59:55', '2026-01-02 11:59:57'),
(886, 37, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2026-01-02 11:59:55', '2026-01-02 11:59:55'),
(887, 37, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2026-01\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2026-01-02 11:59:55', '2026-01-02 12:00:07'),
(888, 37, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2026-01-02 11:59:55', '2026-01-02 11:59:55'),
(889, 37, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2026-01-02 11:59:55', '2026-01-02 11:59:55'),
(890, 37, 'certifications', '{\"content\":[]}', 5, 1, '2026-01-02 11:59:55', '2026-01-02 11:59:55'),
(891, 37, 'achievements', '{\"content\":[]}', 6, 1, '2026-01-02 11:59:55', '2026-01-02 11:59:55'),
(892, 37, '', '{\"content\":{\"primary_color\":\"#2563eb\"}}', 999, 0, '2026-01-02 11:59:55', '2026-01-02 11:59:55'),
(893, 37, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-02 11:59:58', '2026-01-02 11:59:58'),
(894, 37, '', '{\"content\":[]}', 0, 1, '2026-01-02 11:59:58', '2026-01-02 11:59:58'),
(895, 37, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-02 11:59:58', '2026-01-02 11:59:58'),
(896, 37, '', '{\"content\":[]}', 0, 1, '2026-01-02 11:59:58', '2026-01-02 11:59:58'),
(897, 37, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-02 11:59:58', '2026-01-02 11:59:58'),
(898, 37, '', '{\"content\":[]}', 0, 1, '2026-01-02 11:59:58', '2026-01-02 11:59:58'),
(899, 37, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-02 11:59:59', '2026-01-02 11:59:59'),
(900, 37, '', '{\"content\":[]}', 0, 1, '2026-01-02 11:59:59', '2026-01-02 11:59:59'),
(901, 37, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-02 12:00:00', '2026-01-02 12:00:00'),
(902, 37, '', '{\"content\":[]}', 0, 1, '2026-01-02 12:00:00', '2026-01-02 12:00:00'),
(903, 37, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-02 12:00:01', '2026-01-02 12:00:01'),
(904, 37, '', '{\"content\":[]}', 0, 1, '2026-01-02 12:00:01', '2026-01-02 12:00:01'),
(905, 37, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-02 12:00:01', '2026-01-02 12:00:01'),
(906, 37, '', '{\"content\":[]}', 0, 1, '2026-01-02 12:00:01', '2026-01-02 12:00:01'),
(907, 37, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-02 12:00:07', '2026-01-02 12:00:07'),
(908, 37, '', '{\"content\":[]}', 0, 1, '2026-01-02 12:00:07', '2026-01-02 12:00:07'),
(909, 37, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-02 12:00:32', '2026-01-02 12:00:32'),
(910, 37, '', '{\"content\":[]}', 0, 1, '2026-01-02 12:00:32', '2026-01-02 12:00:32'),
(911, 37, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-02 12:01:02', '2026-01-02 12:01:02'),
(912, 37, '', '{\"content\":[]}', 0, 1, '2026-01-02 12:01:02', '2026-01-02 12:01:02'),
(913, 37, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-02 12:01:32', '2026-01-02 12:01:32'),
(914, 37, '', '{\"content\":[]}', 0, 1, '2026-01-02 12:01:32', '2026-01-02 12:01:32'),
(915, 37, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-02 12:02:02', '2026-01-02 12:02:02'),
(916, 37, '', '{\"content\":[]}', 0, 1, '2026-01-02 12:02:02', '2026-01-02 12:02:02'),
(917, 37, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-02 12:02:32', '2026-01-02 12:02:32'),
(918, 37, '', '{\"content\":[]}', 0, 1, '2026-01-02 12:02:32', '2026-01-02 12:02:32'),
(919, 37, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-02 12:03:02', '2026-01-02 12:03:02'),
(920, 37, '', '{\"content\":[]}', 0, 1, '2026-01-02 12:03:02', '2026-01-02 12:03:02'),
(921, 37, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-02 12:03:08', '2026-01-02 12:03:08'),
(922, 37, '', '{\"content\":[]}', 0, 1, '2026-01-02 12:03:09', '2026-01-02 12:03:09'),
(923, 38, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Dwarka , Delhi\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\",\"first_name\":\"Prabhat\",\"last_name\":\"Paswan\"}}', 0, 1, '2026-01-03 11:59:04', '2026-01-03 11:59:09'),
(924, 38, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2026-01-03 11:59:04', '2026-01-03 11:59:04'),
(925, 38, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2026-01-03 11:59:04', '2026-01-03 11:59:04'),
(926, 38, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2026-01-03 11:59:04', '2026-01-03 11:59:04'),
(927, 38, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2026-01-03 11:59:04', '2026-01-03 11:59:04'),
(928, 38, 'certifications', '{\"content\":[]}', 5, 1, '2026-01-03 11:59:04', '2026-01-03 11:59:04'),
(929, 38, 'achievements', '{\"content\":[]}', 6, 1, '2026-01-03 11:59:04', '2026-01-03 11:59:04'),
(930, 38, '', '{\"content\":{\"primary_color\":\"#2563eb\"}}', 999, 0, '2026-01-03 11:59:04', '2026-01-03 11:59:04'),
(931, 38, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 11:59:09', '2026-01-03 11:59:09'),
(932, 38, '', '{\"content\":[]}', 0, 1, '2026-01-03 11:59:09', '2026-01-03 11:59:09'),
(933, 38, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 11:59:10', '2026-01-03 11:59:10'),
(934, 38, '', '{\"content\":[]}', 0, 1, '2026-01-03 11:59:10', '2026-01-03 11:59:10'),
(935, 38, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 11:59:11', '2026-01-03 11:59:11'),
(936, 38, '', '{\"content\":[]}', 0, 1, '2026-01-03 11:59:11', '2026-01-03 11:59:11'),
(937, 38, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 11:59:12', '2026-01-03 11:59:12'),
(938, 38, '', '{\"content\":[]}', 0, 1, '2026-01-03 11:59:12', '2026-01-03 11:59:12'),
(939, 38, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 11:59:13', '2026-01-03 11:59:13'),
(940, 38, '', '{\"content\":[]}', 0, 1, '2026-01-03 11:59:13', '2026-01-03 11:59:13'),
(941, 38, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 11:59:19', '2026-01-03 11:59:19'),
(942, 38, '', '{\"content\":[]}', 0, 1, '2026-01-03 11:59:19', '2026-01-03 11:59:19'),
(943, 39, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Dwarka , Delhi\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\",\"first_name\":\"Prabhat\",\"last_name\":\"Paswan\"}}', 0, 1, '2026-01-03 13:45:21', '2026-01-03 13:45:24'),
(944, 39, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2026-01-03 13:45:21', '2026-01-03 13:45:21'),
(945, 39, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2026-01-03 13:45:21', '2026-01-03 13:45:21'),
(946, 39, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2026-01-03 13:45:21', '2026-01-03 13:45:21'),
(947, 39, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2026-01-03 13:45:21', '2026-01-03 13:45:21'),
(948, 39, 'certifications', '{\"content\":[]}', 5, 1, '2026-01-03 13:45:21', '2026-01-03 13:45:21'),
(949, 39, 'achievements', '{\"content\":[]}', 6, 1, '2026-01-03 13:45:21', '2026-01-03 13:45:21'),
(950, 39, '', '{\"content\":{\"primary_color\":\"#2563eb\"}}', 999, 0, '2026-01-03 13:45:21', '2026-01-03 13:45:21'),
(951, 39, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:45:24', '2026-01-03 13:45:24'),
(952, 39, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:45:24', '2026-01-03 13:45:24'),
(953, 39, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:45:24', '2026-01-03 13:45:24'),
(954, 39, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:45:24', '2026-01-03 13:45:24'),
(955, 39, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:45:25', '2026-01-03 13:45:25'),
(956, 39, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:45:25', '2026-01-03 13:45:25');
INSERT INTO `resume_sections` (`id`, `resume_id`, `section_type`, `section_data`, `sort_order`, `is_visible`, `created_at`, `updated_at`) VALUES
(957, 39, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:45:25', '2026-01-03 13:45:25'),
(958, 39, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:45:25', '2026-01-03 13:45:25'),
(959, 39, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:45:27', '2026-01-03 13:45:27'),
(960, 39, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:45:27', '2026-01-03 13:45:27'),
(961, 39, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:45:28', '2026-01-03 13:45:28'),
(962, 39, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:45:28', '2026-01-03 13:45:28'),
(963, 39, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:45:28', '2026-01-03 13:45:28'),
(964, 39, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:45:28', '2026-01-03 13:45:28'),
(965, 39, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:45:30', '2026-01-03 13:45:30'),
(966, 39, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:45:30', '2026-01-03 13:45:30'),
(967, 39, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:45:36', '2026-01-03 13:45:36'),
(968, 39, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:45:36', '2026-01-03 13:45:36'),
(969, 39, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:45:38', '2026-01-03 13:45:38'),
(970, 39, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:45:38', '2026-01-03 13:45:38'),
(971, 39, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:45:39', '2026-01-03 13:45:39'),
(972, 39, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:45:39', '2026-01-03 13:45:39'),
(973, 39, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:45:41', '2026-01-03 13:45:41'),
(974, 39, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:45:41', '2026-01-03 13:45:41'),
(975, 39, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:45:43', '2026-01-03 13:45:43'),
(976, 39, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:45:43', '2026-01-03 13:45:43'),
(977, 40, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Dwarka , Delhi\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\",\"first_name\":\"Prabhat\",\"last_name\":\"Paswan\"}}', 0, 1, '2026-01-03 13:46:25', '2026-01-03 13:46:39'),
(978, 40, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2026-01-03 13:46:25', '2026-01-03 13:46:25'),
(979, 40, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2026-01-03 13:46:25', '2026-01-03 13:46:25'),
(980, 40, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2026-01-03 13:46:25', '2026-01-03 13:46:25'),
(981, 40, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2026-01-03 13:46:25', '2026-01-03 13:46:25'),
(982, 40, 'certifications', '{\"content\":[]}', 5, 1, '2026-01-03 13:46:25', '2026-01-03 13:46:25'),
(983, 40, 'achievements', '{\"content\":[]}', 6, 1, '2026-01-03 13:46:25', '2026-01-03 13:46:25'),
(984, 40, '', '{\"content\":{\"primary_color\":\"#2563eb\"}}', 999, 0, '2026-01-03 13:46:25', '2026-01-03 13:46:25'),
(985, 40, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:46:39', '2026-01-03 13:46:39'),
(986, 40, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:46:39', '2026-01-03 13:46:39'),
(987, 40, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:46:41', '2026-01-03 13:46:41'),
(988, 40, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:46:41', '2026-01-03 13:46:41'),
(989, 40, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:46:42', '2026-01-03 13:46:42'),
(990, 40, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:46:42', '2026-01-03 13:46:42'),
(991, 40, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:46:42', '2026-01-03 13:46:42'),
(992, 40, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:46:42', '2026-01-03 13:46:42'),
(993, 40, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:46:43', '2026-01-03 13:46:43'),
(994, 40, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:46:43', '2026-01-03 13:46:43'),
(995, 40, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:46:45', '2026-01-03 13:46:45'),
(996, 40, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:46:45', '2026-01-03 13:46:45'),
(997, 40, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-03 13:47:06', '2026-01-03 13:47:06'),
(998, 40, '', '{\"content\":[]}', 0, 1, '2026-01-03 13:47:06', '2026-01-03 13:47:06'),
(999, 41, 'header', '{\"content\":{\"full_name\":\"Prabhat Paswan\",\"email\":\"indianrfid@gmail.com\",\"phone\":\"9910112688\",\"location\":\"Dwarka , Delhi\",\"linkedin\":\"https:\\/\\/linkedin.com\",\"website\":\"www.pkrtechvision.com\",\"first_name\":\"Prabhat\",\"last_name\":\"Paswan\"}}', 0, 1, '2026-01-05 16:45:48', '2026-01-05 16:45:49'),
(1000, 41, 'summary', '{\"content\":{\"text\":\"\"}}', 1, 1, '2026-01-05 16:45:48', '2026-01-05 16:45:48'),
(1001, 41, 'experience', '{\"content\":{\"items\":[{\"job_title\":\"Software developer\",\"company_name\":\"Mindware\",\"start_date\":\"2023-04-16\",\"end_date\":\"\",\"is_current\":true,\"description\":\"Full stack developer \",\"location\":\"Dwarka Delhi\"}]}}', 2, 1, '2026-01-05 16:45:48', '2026-01-05 16:45:48'),
(1002, 41, 'education', '{\"content\":{\"items\":[{\"degree\":\"B.tech\",\"field_of_study\":\"Computer science\",\"institution\":\"Dr. APJ Abdul Kalam Technical University\",\"start_date\":\"2018-06-06\",\"end_date\":\"2022-06-28\",\"is_current\":false,\"grade\":\"7.8\",\"description\":\"Graduate\"}]}}', 3, 1, '2026-01-05 16:45:48', '2026-01-05 16:45:48'),
(1003, 41, 'skills', '{\"content\":{\"items\":[{\"skill_id\":1,\"name\":\"html\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":29,\"name\":\"CSS\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null},{\"skill_id\":26,\"name\":\"Node.js\",\"proficiency_level\":\"intermediate\",\"years_of_experience\":null}]}}', 4, 1, '2026-01-05 16:45:48', '2026-01-05 16:45:48'),
(1004, 41, 'languages', '{\"content\":{\"items\":[]}}', 5, 1, '2026-01-05 16:45:48', '2026-01-05 16:45:48'),
(1005, 41, '', '{\"content\":{\"primary_color\":\"#2563eb\"}}', 999, 0, '2026-01-05 16:45:48', '2026-01-05 16:45:48'),
(1006, 41, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-05 16:45:49', '2026-01-05 16:45:49'),
(1007, 41, '', '{\"content\":[]}', 0, 1, '2026-01-05 16:45:49', '2026-01-05 16:45:49'),
(1008, 41, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-05 16:45:52', '2026-01-05 16:45:52'),
(1009, 41, '', '{\"content\":[]}', 0, 1, '2026-01-05 16:45:52', '2026-01-05 16:45:52'),
(1010, 41, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-05 16:45:53', '2026-01-05 16:45:53'),
(1011, 41, '', '{\"content\":[]}', 0, 1, '2026-01-05 16:45:53', '2026-01-05 16:45:53'),
(1012, 41, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-05 16:45:54', '2026-01-05 16:45:54'),
(1013, 41, '', '{\"content\":[]}', 0, 1, '2026-01-05 16:45:54', '2026-01-05 16:45:54'),
(1014, 41, '', '{\"content\":{\"projects\":[],\"certifications\":[]}}', 0, 1, '2026-01-05 16:45:55', '2026-01-05 16:45:55'),
(1015, 41, '', '{\"content\":[]}', 0, 1, '2026-01-05 16:45:55', '2026-01-05 16:45:55');

-- --------------------------------------------------------

--
-- Table structure for table `resume_share_links`
--

CREATE TABLE `resume_share_links` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `resume_id` bigint(20) UNSIGNED NOT NULL,
  `token` varchar(64) NOT NULL COMMENT 'UUID v4 for sharing',
  `password` varchar(255) DEFAULT NULL COMMENT 'Optional password protection',
  `expires_at` datetime DEFAULT NULL,
  `max_views` int(11) DEFAULT NULL,
  `view_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resume_templates`
--

CREATE TABLE `resume_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL COMMENT 'Professional, Creative, Modern, Classic',
  `job_category` varchar(100) DEFAULT NULL COMMENT 'IT, Marketing, Finance, etc.',
  `is_premium` tinyint(1) DEFAULT 0,
  `preview_image` varchar(512) DEFAULT NULL,
  `template_schema` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON: default sections, layout, colors, fonts' CHECK (json_valid(`template_schema`)),
  `css_styles` text DEFAULT NULL COMMENT 'Custom CSS for template',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `has_photo` tinyint(1) DEFAULT 0 COMMENT 'Template supports photo/headshot',
  `layout_type` varchar(50) DEFAULT 'single-column' COMMENT 'single-column, two-column, three-column',
  `color_scheme` varchar(50) DEFAULT NULL COMMENT 'blue, green, purple, red, black, custom',
  `tags` text DEFAULT NULL COMMENT 'JSON array of tags for filtering'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `resume_templates`
--

INSERT INTO `resume_templates` (`id`, `name`, `slug`, `description`, `category`, `job_category`, `is_premium`, `preview_image`, `template_schema`, `css_styles`, `is_active`, `sort_order`, `created_at`, `updated_at`, `has_photo`, `layout_type`, `color_scheme`, `tags`) VALUES
(1, 'Professional', 'professional', 'Clean and professional design perfect for corporate roles', 'Professional', NULL, 0, NULL, '{\r\n  \"sections\": [\"header\", \"summary\", \"experience\", \"education\", \"skills\"],\r\n  \"colors\": {\r\n    \"primary\": \"#2563eb\",\r\n    \"secondary\": \"#64748b\",\r\n    \"background\": \"#ffffff\",\r\n    \"text\": \"#1e293b\"\r\n  },\r\n  \"fonts\": {\r\n    \"heading\": \"Arial, sans-serif\",\r\n    \"body\": \"Arial, sans-serif\"\r\n  },\r\n  \"layout\": \"single-column\",\r\n  \"header_style\": \"centered\"\r\n}', NULL, 1, 1, '2025-12-27 13:45:37', '2025-12-27 13:45:37', 0, 'single-column', NULL, NULL),
(2, 'Modern', 'modern', 'Contemporary design with bold typography', 'Modern', NULL, 0, NULL, '{\r\n  \"sections\": [\"header\", \"summary\", \"experience\", \"education\", \"skills\", \"languages\"],\r\n  \"colors\": {\r\n    \"primary\": \"#0ea57a\",\r\n    \"secondary\": \"#64748b\",\r\n    \"background\": \"#ffffff\",\r\n    \"text\": \"#1e293b\"\r\n  },\r\n  \"fonts\": {\r\n    \"heading\": \"Georgia, serif\",\r\n    \"body\": \"Arial, sans-serif\"\r\n  },\r\n  \"layout\": \"two-column\",\r\n  \"header_style\": \"left-aligned\"\r\n}', NULL, 1, 2, '2025-12-27 13:45:37', '2025-12-27 13:45:37', 0, 'single-column', NULL, NULL),
(3, 'Classic', 'classic', 'Traditional format with clear sections', 'Classic', NULL, 0, NULL, '{\r\n  \"sections\": [\"header\", \"summary\", \"experience\", \"education\", \"skills\", \"certifications\"],\r\n  \"colors\": {\r\n    \"primary\": \"#1e293b\",\r\n    \"secondary\": \"#475569\",\r\n    \"background\": \"#ffffff\",\r\n    \"text\": \"#0f172a\"\r\n  },\r\n  \"fonts\": {\r\n    \"heading\": \"Times New Roman, serif\",\r\n    \"body\": \"Times New Roman, serif\"\r\n  },\r\n  \"layout\": \"single-column\",\r\n  \"header_style\": \"centered\"\r\n}', NULL, 1, 3, '2025-12-27 13:45:37', '2025-12-27 13:45:37', 0, 'single-column', NULL, NULL),
(4, 'Creative', 'creative', 'Eye-catching design for creative professionals', 'Creative', NULL, 1, NULL, '{\r\n  \"sections\": [\"header\", \"summary\", \"experience\", \"education\", \"skills\", \"projects\", \"achievements\"],\r\n  \"colors\": {\r\n    \"primary\": \"#8b5cf6\",\r\n    \"secondary\": \"#a78bfa\",\r\n    \"background\": \"#faf5ff\",\r\n    \"text\": \"#1e293b\"\r\n  },\r\n  \"fonts\": {\r\n    \"heading\": \"Georgia, serif\",\r\n    \"body\": \"Arial, sans-serif\"\r\n  },\r\n  \"layout\": \"two-column\",\r\n  \"header_style\": \"centered\"\r\n}', NULL, 1, 4, '2025-12-27 13:45:37', '2025-12-27 13:45:37', 0, 'single-column', NULL, NULL),
(5, 'Executive', 'executive', 'Sophisticated design for senior roles', 'Professional', NULL, 1, NULL, '{\r\n  \"sections\": [\"header\", \"summary\", \"experience\", \"education\", \"skills\", \"certifications\", \"achievements\"],\r\n  \"colors\": {\r\n    \"primary\": \"#1e40af\",\r\n    \"secondary\": \"#3b82f6\",\r\n    \"background\": \"#ffffff\",\r\n    \"text\": \"#0f172a\"\r\n  },\r\n  \"fonts\": {\r\n    \"heading\": \"Georgia, serif\",\r\n    \"body\": \"Calibri, sans-serif\"\r\n  },\r\n  \"layout\": \"single-column\",\r\n  \"header_style\": \"left-aligned\"\r\n}', NULL, 1, 5, '2025-12-27 13:45:37', '2025-12-27 13:45:37', 0, 'single-column', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `candidate_id` int(11) DEFAULT NULL,
  `reviewer_name` varchar(255) NOT NULL,
  `rating` tinyint(1) NOT NULL DEFAULT 5 COMMENT 'Rating from 1 to 5',
  `title` varchar(255) DEFAULT NULL,
  `review_text` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `company_id`, `user_id`, `candidate_id`, `reviewer_name`, `rating`, `title`, `review_text`, `status`, `created_at`, `updated_at`) VALUES
(1, 0, 35, 12, 'Prabhat Paswan', 3, 'Review for Mindware', 'Do you approve of the company\'s leadership?: Yes\n\nWould you recommend this company as a good place to work?: Yes\n\nDo you feel satisfied with your current role?: Yes', 'approved', '2026-01-02 11:22:01', '2026-01-02 11:22:01');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(3, 'Sales Manager', 'sales_manager', NULL, '2025-12-04 16:53:53'),
(10, 'Sales Manager', 'sales-manager', 'Manage all sales team', '2025-12-20 17:05:54'),
(11, 'Sales Executive', 'sales-executive', 'Manage all sales funnel', '2025-12-22 12:49:57');

-- --------------------------------------------------------

--
-- Table structure for table `role_user`
--

CREATE TABLE `role_user` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_user`
--

INSERT INTO `role_user` (`user_id`, `role_id`) VALUES
(9, 3),
(10, 3),
(10, 10),
(39, 11);

-- --------------------------------------------------------

--
-- Table structure for table `sales_communications`
--

CREATE TABLE `sales_communications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `channel` enum('call','email','whatsapp','sms','meeting','other') NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `direction` enum('outbound','inbound') NOT NULL DEFAULT 'outbound',
  `status` enum('completed','failed','scheduled') NOT NULL DEFAULT 'completed',
  `logged_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_followups`
--

CREATE TABLE `sales_followups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `follow_up_at` datetime NOT NULL,
  `status` enum('pending','done','skipped','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_leads`
--

CREATE TABLE `sales_leads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(64) DEFAULT NULL,
  `stage` enum('new','contacted','demo_done','follow_up','payment_pending','converted','lost') DEFAULT 'new',
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `source` enum('import','form','referral','cold_call') DEFAULT 'import',
  `notes` text DEFAULT NULL,
  `next_followup_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_leads`
--

INSERT INTO `sales_leads` (`id`, `employer_id`, `company_name`, `contact_name`, `contact_email`, `contact_phone`, `stage`, `assigned_to`, `source`, `notes`, `next_followup_at`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Acme Corp', 'Rahul Verma', 'rahul@acme.com', '+91-9876543210', 'lost', 0, 'referral', NULL, NULL, '2025-12-05 18:04:31', '2025-12-05 18:44:35'),
(2, NULL, 'Globex', 'Anita Sharma', 'anita@globex.com', '+91-9988776655', 'contacted', 0, 'cold_call', NULL, NULL, '2025-12-05 18:04:31', '2025-12-05 18:49:33'),
(3, NULL, 'Initech', 'Sanjay Patel', 'sanjay@initech.com', '+91-8877665544', 'demo_done', 0, 'form', NULL, NULL, '2025-12-05 18:04:31', '2025-12-05 18:49:34');

-- --------------------------------------------------------

--
-- Table structure for table `sales_lead_activities`
--

CREATE TABLE `sales_lead_activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('status_change','note','call','email','meeting','task','system') NOT NULL,
  `old_stage_id` int(10) UNSIGNED DEFAULT NULL,
  `new_stage_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_lead_assignments`
--

CREATE TABLE `sales_lead_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_to_id` int(10) UNSIGNED NOT NULL,
  `assigned_by_id` int(10) UNSIGNED DEFAULT NULL,
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_lead_notes`
--

CREATE TABLE `sales_lead_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `note_text` text NOT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_notifications`
--

CREATE TABLE `sales_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('new_lead','followup_due','payment_pending','payment_success','reassigned','system') NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_payments`
--

CREATE TABLE `sales_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `gateway` varchar(50) DEFAULT NULL,
  `gateway_txn_id` varchar(150) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `status` enum('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_link` varchar(255) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_stages`
--

CREATE TABLE `sales_stages` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_users`
--

CREATE TABLE `sales_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `role` enum('manager','executive') NOT NULL DEFAULT 'executive',
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_user_metrics`
--

CREATE TABLE `sales_user_metrics` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sales_user_id` bigint(20) UNSIGNED NOT NULL,
  `period_date` date NOT NULL,
  `leads_created` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `leads_contacted` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `demos_done` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `payments_success` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `revenue_closed` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seo_page_logs`
--

CREATE TABLE `seo_page_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `page_type` varchar(50) NOT NULL,
  `country_id` int(10) UNSIGNED DEFAULT NULL,
  `state_id` int(10) UNSIGNED DEFAULT NULL,
  `city_id` int(10) UNSIGNED DEFAULT NULL,
  `url` varchar(255) NOT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `impressions` int(10) UNSIGNED DEFAULT 0,
  `clicks` int(10) UNSIGNED DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_code` int(11) DEFAULT 200
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seo_page_logs`
--

INSERT INTO `seo_page_logs` (`id`, `page_type`, `country_id`, `state_id`, `city_id`, `url`, `meta_title`, `meta_description`, `impressions`, `clicks`, `created_at`, `status_code`) VALUES
(1, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2025-12-29 12:43:01', 200),
(2, 'job_detail', NULL, NULL, NULL, '/job/delivery-boy', 'Delivery Boy at Barcode Vault - Apply Now', 'Hiring: Delivery Boy at Barcode Vault in . Salary: INR 9000. Apply today!', 0, 0, '2025-12-29 12:43:48', 200),
(3, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-29 12:43:52', 200),
(4, 'job_detail', NULL, NULL, NULL, '/job/delivery-boy', 'Delivery Boy at Barcode Vault - Apply Now', 'Hiring: Delivery Boy at Barcode Vault in . Salary: INR 9000. Apply today!', 0, 0, '2025-12-29 12:44:12', 200),
(5, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2025-12-29 12:44:13', 200),
(6, 'job_detail', NULL, NULL, NULL, '/job/delivery-boy', 'Delivery Boy at Barcode Vault - Apply Now', 'Hiring: Delivery Boy at Barcode Vault in . Salary: INR 9000. Apply today!', 0, 0, '2025-12-29 12:45:06', 200),
(7, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-29 12:45:09', 200),
(8, 'job_detail', NULL, NULL, NULL, '/job/delivery-boy', 'Delivery Boy at Barcode Vault - Apply Now', 'Hiring: Delivery Boy at Barcode Vault in . Salary: INR 9000. Apply today!', 0, 0, '2025-12-29 12:45:14', 200),
(9, 'city_jobs', NULL, NULL, NULL, '/', 'Jobs in Mumbai, Maharashtra | Apply Now', 'Looking for jobs in Mumbai? Browse 150 openings in Mumbai, Maharashtra.', 0, 0, '2025-12-29 12:52:06', 200),
(10, 'skill_city_jobs', NULL, NULL, NULL, '/', 'Python Jobs in Bangalore | Top Opportunities', 'Find the best Python jobs in Bangalore. Apply to top companies hiring for Python roles now.', 0, 0, '2025-12-29 12:52:06', 200),
(11, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2025-12-29 13:14:39', 200),
(12, 'job_detail', NULL, NULL, NULL, '/job/banking-executive', 'Banking Executive at Barcode Vault - Apply Now', 'Hiring: Banking Executive at Barcode Vault in . Salary: INR 18000. Apply today!', 0, 0, '2025-12-29 13:16:54', 200),
(13, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-29 13:16:59', 200),
(14, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault/why', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-29 13:17:04', 200),
(15, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault/reviews', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-29 13:17:06', 200),
(16, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault/jobs', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-29 13:17:09', 200),
(17, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault/blogs', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-29 13:17:13', 200),
(18, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault/blogs', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-29 13:17:17', 200),
(19, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault/blogs', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-29 13:17:22', 200),
(20, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault/blogs', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-29 13:19:49', 200),
(21, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault/jobs', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-29 13:19:51', 200),
(22, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2025-12-29 13:19:55', 200),
(23, 'job_detail', NULL, NULL, NULL, '/job/delivery-boy', 'Delivery Boy at Barcode Vault - Apply Now', 'Hiring: Delivery Boy at Barcode Vault in Gorakhpur. Salary: INR 9000. Apply today!', 0, 0, '2025-12-29 13:20:39', 200),
(24, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2025-12-30 04:50:24', 200),
(25, 'job_detail', NULL, NULL, NULL, '/job/delivery-boy', 'Delivery Boy at Barcode Vault - Apply Now', 'Hiring: Delivery Boy at Barcode Vault in Gorakhpur. Salary: INR 9000. Apply today!', 0, 0, '2025-12-30 05:11:26', 200),
(26, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2025-12-30 05:11:32', 200),
(27, 'job_detail', NULL, NULL, NULL, '/job/delivery-boy', 'Delivery Boy at Barcode Vault - Apply Now', 'Hiring: Delivery Boy at Barcode Vault in Gorakhpur. Salary: INR 9000. Apply today!', 0, 0, '2025-12-30 05:11:35', 200),
(28, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2025-12-30 05:11:37', 200),
(29, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2025-12-30 05:22:54', 200),
(30, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2025-12-30 05:22:59', 200),
(31, 'job_detail', NULL, NULL, NULL, '/job/banking-executive', 'Banking Executive at Barcode Vault - Apply Now', 'Hiring: Banking Executive at Barcode Vault in Cochin. Salary: INR 18000. Apply today!', 0, 0, '2025-12-30 06:04:10', 200),
(32, 'job_detail', NULL, NULL, NULL, '/job/banking-executive', 'Banking Executive at Barcode Vault - Apply Now', 'Hiring: Banking Executive at Barcode Vault in Cochin. Salary: INR 18000. Apply today!', 0, 0, '2025-12-30 06:04:12', 200),
(33, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2025-12-30 06:07:29', 200),
(34, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2025-12-30 06:07:31', 200),
(35, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2025-12-30 06:11:52', 200),
(36, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2025-12-30 06:11:54', 200),
(37, 'job_detail', NULL, NULL, NULL, '/job/3-wheeler-driver-1', '3 Wheeler Driver at Indian Barcode Corporation - Apply Now', 'Hiring: 3 Wheeler Driver at Indian Barcode Corporation in New Delhi. Salary: Negotiable. Apply today!', 0, 0, '2025-12-30 06:12:54', 200),
(38, 'job_detail', NULL, NULL, NULL, '/job/3-wheeler-driver-1', '3 Wheeler Driver at Indian Barcode Corporation - Apply Now', 'Hiring: 3 Wheeler Driver at Indian Barcode Corporation in New Delhi. Salary: Negotiable. Apply today!', 0, 0, '2025-12-30 06:12:56', 200),
(39, 'job_detail', NULL, NULL, NULL, '/job/delivery-boy', 'Delivery Boy at Barcode Vault - Apply Now', 'Hiring: Delivery Boy at Barcode Vault in Gorakhpur. Salary: INR 9000. Apply today!', 0, 0, '2025-12-30 06:25:01', 200),
(40, 'job_detail', NULL, NULL, NULL, '/job/delivery-boy', 'Delivery Boy at Barcode Vault - Apply Now', 'Hiring: Delivery Boy at Barcode Vault in Gorakhpur. Salary: INR 9000. Apply today!', 0, 0, '2025-12-30 06:25:03', 200),
(41, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2025-12-30 06:25:05', 200),
(42, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2025-12-30 06:35:23', 200),
(43, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2025-12-30 06:35:26', 200),
(44, 'job_detail', NULL, NULL, NULL, '/job/web-and-software-developer', 'Web And Software Developer at Indian Barcode Corporation - Apply Now', 'Hiring: Web And Software Developer at Indian Barcode Corporation in Agra. Salary: INR 7500. Apply today!', 0, 0, '2025-12-30 07:02:52', 200),
(45, 'city_jobs', NULL, NULL, NULL, '/jobs-in-new-york?page=2', 'Jobs in New York,  | Apply Now', 'Looking for jobs in New York? Browse 100 openings in New York, .', 0, 0, '2025-12-30 07:07:18', 200),
(46, 'job_detail', NULL, NULL, NULL, '/job/clinical-pharmacist-1', 'Clinical Pharmacist at Indian Barcode Corporation - Apply Now', 'Hiring: Clinical Pharmacist at Indian Barcode Corporation in Dalar. Salary: AMD 25000. Apply today!', 0, 0, '2025-12-30 07:21:38', 200),
(47, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-30 08:10:29', 200),
(48, 'job_detail', NULL, NULL, NULL, '/job/web-application-developer', 'Web Application Developer at Barcode Vault - Apply Now', 'Hiring: Web Application Developer at Barcode Vault in . Salary: RUB 1000. Apply today!', 0, 0, '2025-12-30 09:17:28', 200),
(49, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault/jobs', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-30 10:45:46', 200),
(50, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault/reviews', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-30 11:22:31', 200),
(51, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault/why', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-30 11:42:46', 200),
(52, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault/blogs', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-30 12:43:49', 200),
(53, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2025-12-31 04:51:09', 200),
(54, 'job_detail', NULL, NULL, NULL, '/job/delivery-boy', 'Delivery Boy at Barcode Vault - Apply Now', 'Hiring: Delivery Boy at Barcode Vault in Gorakhpur. Salary: INR 9000. Apply today!', 0, 0, '2025-12-31 05:21:41', 200),
(55, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-31 05:24:22', 200),
(56, 'job_detail', NULL, NULL, NULL, '/job/banking-executive', 'Banking Executive at Barcode Vault - Apply Now', 'Hiring: Banking Executive at Barcode Vault in Cochin. Salary: INR 18000. Apply today!', 0, 0, '2025-12-31 06:39:41', 200),
(57, 'job_detail', NULL, NULL, NULL, '/job/web-and-software-developer', 'Web And Software Developer at Indian Barcode Corporation - Apply Now', 'Hiring: Web And Software Developer at Indian Barcode Corporation in Agra. Salary: INR 7500. Apply today!', 0, 0, '2025-12-31 09:47:39', 200),
(58, 'company_detail', NULL, NULL, NULL, '/company/indian-barcode-corporation', 'Careers at Indian Barcode Corporation | Job Openings & Reviews', 'Learn about working at Indian Barcode Corporation. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-31 09:47:41', 200),
(59, 'job_detail', NULL, NULL, NULL, '/job/3-wheeler-driver-1', '3 Wheeler Driver at Indian Barcode Corporation - Apply Now', 'Hiring: 3 Wheeler Driver at Indian Barcode Corporation in New Delhi. Salary: Negotiable. Apply today!', 0, 0, '2025-12-31 10:03:14', 200),
(60, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault/reviews', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2025-12-31 10:10:49', 200),
(61, 'home', NULL, NULL, NULL, '/?ide_webview_request_time=1767244081569', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2026-01-01 05:08:02', 200),
(62, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2026-01-01 05:08:37', 200),
(63, 'job_detail', NULL, NULL, NULL, '/job/delivery-boy', 'Delivery Boy at Barcode Vault - Apply Now', 'Hiring: Delivery Boy at Barcode Vault in Gorakhpur. Salary: INR 9000. Apply today!', 0, 0, '2026-01-01 05:09:59', 200),
(64, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-01 05:10:02', 200),
(65, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault/blogs', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-01 05:10:05', 200),
(66, 'job_detail', NULL, NULL, NULL, '/job/clinical-pharmacist-1', 'Clinical Pharmacist at Indian Barcode Corporation - Apply Now', 'Hiring: Clinical Pharmacist at Indian Barcode Corporation in Dalar. Salary: AMD 25000. Apply today!', 0, 0, '2026-01-01 07:34:18', 200),
(67, 'company_detail', NULL, NULL, NULL, '/company/indian-barcode-corporation', 'Careers at Indian Barcode Corporation | Job Openings & Reviews', 'Learn about working at Indian Barcode Corporation. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-01 07:34:26', 200),
(68, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault/jobs', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-01 08:36:31', 200),
(69, 'company_detail', NULL, NULL, NULL, '/company/pkr-techvision', 'Careers at PKR Techvision | Job Openings & Reviews', 'Learn about working at PKR Techvision. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-01 08:48:17', 200),
(70, 'company_detail', NULL, NULL, NULL, '/company/pkr-techvision/why', 'Careers at PKR Techvision | Job Openings & Reviews', 'Learn about working at PKR Techvision. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-01 08:48:22', 200),
(71, 'company_detail', NULL, NULL, NULL, '/company/pkr-techvision/jobs', 'Careers at PKR Techvision | Job Openings & Reviews', 'Learn about working at PKR Techvision. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-01 08:48:24', 200),
(72, 'company_detail', NULL, NULL, NULL, '/company/pkr-techvision/blogs', 'Careers at PKR Techvision | Job Openings & Reviews', 'Learn about working at PKR Techvision. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-01 08:48:26', 200),
(73, 'company_detail', NULL, NULL, NULL, '/company/pkr-techvision/snapshot', 'Careers at PKR Techvision | Job Openings & Reviews', 'Learn about working at PKR Techvision. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-01 08:48:41', 200),
(74, 'company_detail', NULL, NULL, NULL, '/company/pkr-techvision/reviews', 'Careers at PKR Techvision | Job Openings & Reviews', 'Learn about working at PKR Techvision. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-01 08:54:29', 200),
(75, 'company_detail', NULL, NULL, NULL, '/company/mindware', 'Careers at Mindware Infotech | Job Openings & Reviews', 'Learn about working at Mindware Infotech. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-01 08:56:39', 200),
(76, 'company_detail', NULL, NULL, NULL, '/company/tsc-printers', 'Careers at TSC Printers India | Job Openings & Reviews', 'Learn about working at TSC Printers India. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-01 09:23:22', 200),
(77, 'company_detail', NULL, NULL, NULL, '/company/tsc-printers/reviews', 'Careers at TSC Printers India | Job Openings & Reviews', 'Learn about working at TSC Printers India. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-01 10:37:37', 200),
(78, 'job_detail', NULL, NULL, NULL, '/job/web-and-software-developer', 'Web And Software Developer at Indian Barcode Corporation - Apply Now', 'Hiring: Web And Software Developer at Indian Barcode Corporation in Agra. Salary: INR 7500. Apply today!', 0, 0, '2026-01-01 11:59:05', 200),
(79, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2026-01-02 04:59:17', 200),
(80, 'job_detail', NULL, NULL, NULL, '/job/3-wheeler-driver-1', '3 Wheeler Driver at Indian Barcode Corporation - Apply Now', 'Hiring: 3 Wheeler Driver at Indian Barcode Corporation in New Delhi. Salary: Negotiable. Apply today!', 0, 0, '2026-01-02 06:09:47', 200),
(81, 'company_detail', NULL, NULL, NULL, '/company/pkr-techvision', 'Careers at PKR Techvision | Job Openings & Reviews', 'Learn about working at PKR Techvision. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-02 06:29:12', 200),
(82, 'company_detail', NULL, NULL, NULL, '/company/indian-barcode-corporation', 'Careers at Indian Barcode Corporation | Job Openings & Reviews', 'Learn about working at Indian Barcode Corporation. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-02 06:49:28', 200),
(83, 'job_detail', NULL, NULL, NULL, '/job/delivery-boy', 'Delivery Boy at Barcode Vault - Apply Now', 'Hiring: Delivery Boy at Barcode Vault in Gorakhpur. Salary: INR 9000. Apply today!', 0, 0, '2026-01-02 07:16:09', 200),
(84, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-02 07:16:29', 200),
(85, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault/jobs', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-02 07:16:32', 200),
(86, 'job_detail', NULL, NULL, NULL, '/job/web-and-software-developer', 'Web And Software Developer at Indian Barcode Corporation - Apply Now', 'Hiring: Web And Software Developer at Indian Barcode Corporation in Agra. Salary: INR 7500. Apply today!', 0, 0, '2026-01-02 08:19:16', 200),
(87, 'company_detail', NULL, NULL, NULL, '/company/mindware', 'Careers at Mindware Infotech | Job Openings & Reviews', 'Learn about working at Mindware Infotech. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-02 11:28:17', 200),
(88, 'company_detail', NULL, NULL, NULL, '/company/mindware-india', 'Careers at Mindware India | Job Openings & Reviews', 'Learn about working at Mindware India. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-02 11:32:03', 200),
(89, 'company_detail', NULL, NULL, NULL, '/company/pkr-techvision/why', 'Careers at PKR Techvision | Job Openings & Reviews', 'Learn about working at PKR Techvision. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-02 11:32:20', 200),
(90, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2026-01-03 05:13:46', 200),
(91, 'job_detail', NULL, NULL, NULL, '/job/delivery-boy', 'Delivery Boy at Barcode Vault - Apply Now', 'Hiring: Delivery Boy at Barcode Vault in Gorakhpur. Salary: INR 9000. Apply today!', 0, 0, '2026-01-03 05:38:49', 200),
(92, 'job_detail', NULL, NULL, NULL, '/job/banking-executive', 'Banking Executive at Barcode Vault - Apply Now', 'Hiring: Banking Executive at Barcode Vault in Cochin. Salary: INR 18000. Apply today!', 0, 0, '2026-01-03 05:39:44', 200),
(93, 'job_detail', NULL, NULL, NULL, '/job/web-and-software-developer', 'Web And Software Developer at Indian Barcode Corporation - Apply Now', 'Hiring: Web And Software Developer at Indian Barcode Corporation in Agra. Salary: INR 7500. Apply today!', 0, 0, '2026-01-03 06:25:17', 200),
(94, 'company_detail', NULL, NULL, NULL, '/company/indian-barcode-corporation', 'Careers at Indian Barcode Corporation | Job Openings & Reviews', 'Learn about working at Indian Barcode Corporation. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-03 07:34:39', 200),
(95, 'company_detail', NULL, NULL, NULL, '/company/mindware', 'Careers at Mindware Infotech | Job Openings & Reviews', 'Learn about working at Mindware Infotech. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-03 07:46:22', 200),
(96, 'company_detail', NULL, NULL, NULL, '/company/indian-barcode-corporation/jobs', 'Careers at Indian Barcode Corporation | Job Openings & Reviews', 'Learn about working at Indian Barcode Corporation. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-03 08:26:39', 200),
(97, 'company_detail', NULL, NULL, NULL, '/company/indian-barcode-corporation/blogs', 'Careers at Indian Barcode Corporation | Job Openings & Reviews', 'Learn about working at Indian Barcode Corporation. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-03 08:26:41', 200),
(98, 'company_detail', NULL, NULL, NULL, '/company/indian-barcode-corporation/why', 'Careers at Indian Barcode Corporation | Job Openings & Reviews', 'Learn about working at Indian Barcode Corporation. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-03 08:26:43', 200),
(99, 'company_detail', NULL, NULL, NULL, '/company/indian-barcode-corporation/reviews', 'Careers at Indian Barcode Corporation | Job Openings & Reviews', 'Learn about working at Indian Barcode Corporation. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-03 08:26:46', 200),
(100, 'job_detail', NULL, NULL, NULL, '/job/clinical-pharmacist-1', 'Clinical Pharmacist at Indian Barcode Corporation - Apply Now', 'Hiring: Clinical Pharmacist at Indian Barcode Corporation in Dalar. Salary: AMD 25000. Apply today!', 0, 0, '2026-01-03 08:32:18', 200),
(101, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2026-01-05 05:08:40', 200),
(102, 'job_detail', NULL, NULL, NULL, '/job/3-wheeler-driver-1', '3 Wheeler Driver at Indian Barcode Corporation - Apply Now', 'Hiring: 3 Wheeler Driver at Indian Barcode Corporation in New Delhi. Salary: Negotiable. Apply today!', 0, 0, '2026-01-05 05:53:30', 200),
(103, 'job_detail', NULL, NULL, NULL, '/job/delivery-boy', 'Delivery Boy at Barcode Vault - Apply Now', 'Hiring: Delivery Boy at Barcode Vault in Gorakhpur. Salary: INR 9000. Apply today!', 0, 0, '2026-01-05 05:54:21', 200),
(104, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-05 11:14:32', 200),
(105, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault/blogs', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-05 11:14:36', 200),
(106, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault/jobs', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-05 11:14:38', 200),
(107, 'job_detail', NULL, NULL, NULL, '/job/web-and-software-developer', 'Web And Software Developer at Indian Barcode Corporation - Apply Now', 'Hiring: Web And Software Developer at Indian Barcode Corporation in Agra. Salary: INR 7500. Apply today!', 0, 0, '2026-01-05 11:15:02', 200),
(108, 'company_detail', NULL, NULL, NULL, '/company/indian-barcode-corporation', 'Careers at Indian Barcode Corporation | Job Openings & Reviews', 'Learn about working at Indian Barcode Corporation. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-05 11:16:35', 200),
(109, 'company_detail', NULL, NULL, NULL, '/company/tsc-printers', 'Careers at TSC Printers India | Job Openings & Reviews', 'Learn about working at TSC Printers India. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-05 11:51:34', 200),
(110, 'company_detail', NULL, NULL, NULL, '/company/tsc-printers/reviews', 'Careers at TSC Printers India | Job Openings & Reviews', 'Learn about working at TSC Printers India. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-05 11:51:39', 200),
(111, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 6+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2026-01-06 05:01:44', 200),
(112, 'company_detail', NULL, NULL, NULL, '/company/mindware', 'Careers at Mindware Infotech | Job Openings & Reviews', 'Learn about working at Mindware Infotech. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-06 08:45:45', 200),
(113, 'job_detail', NULL, NULL, NULL, '/job/web-and-software-developer-1', 'Web And Software Developer at PKR Techvision - Apply Now', 'Hiring: Web And Software Developer at PKR Techvision in . Salary: INR 45000. Apply today!', 0, 0, '2026-01-06 08:45:57', 200),
(114, 'company_detail', NULL, NULL, NULL, '/company/pkr-techvision', 'Careers at PKR Techvision | Job Openings & Reviews', 'Learn about working at PKR Techvision. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-06 08:56:41', 200),
(115, 'home', NULL, NULL, NULL, '/', 'Find Your Dream Job | 7+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in India and more.', 0, 0, '2026-01-07 05:26:26', 200),
(116, 'company_detail', NULL, NULL, NULL, '/company/barcode-vault', 'Careers at Barcode Vault | Job Openings & Reviews', 'Learn about working at Barcode Vault. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-07 05:27:21', 200),
(117, 'company_detail', NULL, NULL, NULL, '/company/indian-barcode-corporation', 'Careers at Indian Barcode Corporation | Job Openings & Reviews', 'Learn about working at Indian Barcode Corporation. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-07 05:27:28', 200),
(118, 'company_detail', NULL, NULL, NULL, '/company/indian-barcode-corporation/blogs', 'Careers at Indian Barcode Corporation | Job Openings & Reviews', 'Learn about working at Indian Barcode Corporation. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-07 05:27:31', 200),
(119, 'company_detail', NULL, NULL, NULL, '/company/indian-barcode-corporation/jobs', 'Careers at Indian Barcode Corporation | Job Openings & Reviews', 'Learn about working at Indian Barcode Corporation. View open jobs, salaries, and employee reviews.', 0, 0, '2026-01-07 05:27:35', 200);

-- --------------------------------------------------------

--
-- Table structure for table `seo_rules`
--

CREATE TABLE `seo_rules` (
  `id` int(10) UNSIGNED NOT NULL,
  `page_type` varchar(50) NOT NULL,
  `meta_title_template` varchar(255) NOT NULL,
  `meta_description_template` text NOT NULL,
  `meta_keywords_template` text DEFAULT NULL,
  `h1_template` varchar(255) NOT NULL,
  `canonical_rule` enum('dynamic','static') DEFAULT 'dynamic',
  `indexable` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seo_rules`
--

INSERT INTO `seo_rules` (`id`, `page_type`, `meta_title_template`, `meta_description_template`, `meta_keywords_template`, `h1_template`, `canonical_rule`, `indexable`, `created_at`, `updated_at`) VALUES
(1, 'home', 'Find Your Dream Job | {job_count}+ Openings', 'Browse thousands of jobs in your area. Apply now for top roles in {city} and more.', NULL, 'Find Your Next Job', 'dynamic', 1, '2025-12-29 09:05:49', NULL),
(2, 'job_detail', '{job_title} at {company} - Apply Now', 'Hiring: {job_title} at {company} in {city}. Salary: {salary}. Apply today!', NULL, '{job_title}', 'dynamic', 1, '2025-12-29 09:05:49', NULL),
(3, 'city_jobs', 'Jobs in {city}, {state} | Apply Now', 'Looking for jobs in {city}? Browse {job_count} openings in {city}, {state}.', NULL, 'Jobs in {city}', 'dynamic', 1, '2025-12-29 09:05:49', NULL),
(4, 'skill_city_jobs', '{skill} Jobs in {city} | Top Opportunities', 'Find the best {skill} jobs in {city}. Apply to top companies hiring for {skill} roles now.', NULL, '{skill} Jobs in {city}', 'dynamic', 1, '2025-12-29 09:05:49', NULL),
(5, 'company_detail', 'Careers at {company} | Job Openings & Reviews', 'Learn about working at {company}. View open jobs, salaries, and employee reviews.', NULL, 'Careers at {company}', 'dynamic', 1, '2025-12-29 09:05:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(128) NOT NULL,
  `slug` varchar(128) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'html', '', '2025-11-24 16:54:51'),
(18, 'sales', 'sales', '2025-11-27 19:00:05'),
(19, 'good communication', 'good-communication', '2025-11-27 19:00:05'),
(20, 'customer handing', 'customer-handing', '2025-11-27 19:00:05'),
(21, 'customer coordination', 'customer-coordination', '2025-11-28 18:28:05'),
(22, 'php', 'php', '2025-11-29 11:48:36'),
(23, 'JavaScript', 'javascript', '2025-11-29 11:48:36'),
(24, 'MySQL', 'mysql', '2025-11-29 11:48:36'),
(25, 'React', 'react', '2025-11-29 17:07:47'),
(26, 'Node.js', 'node-js', '2025-11-29 17:07:47'),
(27, 'Tailwind css', 'tailwind-css', '2025-12-01 17:02:48'),
(28, 'Apline js', 'apline-js', '2025-12-01 17:02:48'),
(29, 'CSS', 'css', '2025-12-01 17:02:48'),
(30, 'OPPS', 'opps', '2025-12-01 17:02:48'),
(31, 'React js', 'react-js', '2025-12-01 17:02:48'),
(32, 'Laravel', 'laravel', '2025-12-01 17:04:55'),
(33, 'driving', 'driving', '2025-12-05 18:20:25'),
(34, 'cars', 'cars', '2025-12-05 18:20:25'),
(35, 'honesty', 'honesty', '2025-12-05 18:20:25'),
(36, 'retional language known', 'retional-language-known', '2025-12-09 17:21:44'),
(37, 'time punctual', 'time-punctual', '2025-12-09 18:35:55'),
(38, 'honest', 'honest', '2025-12-09 18:35:55'),
(39, 'android dev', 'android-dev', '2025-12-09 18:54:32'),
(40, 'kotlin', 'kotlin', '2025-12-09 18:54:32'),
(41, 'jss', 'jss', '2025-12-09 18:54:32'),
(42, 'play console', 'play-console', '2025-12-09 18:54:32'),
(43, 'GOOD PROGRAMMING', 'good-programming', '2025-12-09 19:05:21'),
(44, 'xml', 'xml', '2025-12-09 19:05:21'),
(45, 'jetpack compose', 'jetpack-compose', '2025-12-09 19:05:21'),
(46, 'java programming', 'java-programming', '2025-12-09 19:05:21'),
(47, 'Good thinking ability', 'good-thinking-ability', '2025-12-10 17:01:53'),
(48, 'js', 'js', '2025-12-10 17:01:53'),
(49, 'tailwind', 'tailwind', '2025-12-10 17:01:53'),
(50, 'dgfgfd', 'dgfgfd', '2025-12-11 10:55:19'),
(51, 'fdhfdh', 'fdhfdh', '2025-12-11 10:55:19'),
(52, 'hfdghgfh', 'hfdghgfh', '2025-12-11 10:55:19'),
(53, 'ghgf', 'ghgf', '2025-12-11 10:55:19'),
(54, 'hgfdh', 'hgfdh', '2025-12-11 10:55:19'),
(55, 'gfj', 'gfj', '2025-12-11 10:55:19'),
(56, 'fj', 'fj', '2025-12-11 10:55:19'),
(57, 'hfg', 'hfg', '2025-12-11 10:55:19'),
(58, 'Ability to operate and maintain 3-wheeler vehicles', 'ability-to-operate-and-maintain-3-wheeler-vehicles', '2025-12-13 13:09:00'),
(59, 'Basic mechanical knowledge for minor repairs and troubleshooting', 'basic-mechanical-knowledge-for-minor-repairs-and-troubleshooting', '2025-12-13 13:09:00'),
(60, 'Ability to use GPS, route maps, and navigation tools', 'ability-to-use-gps-route-maps-and-navigation-tools', '2025-12-13 13:09:00'),
(61, 'Handling invoices, delivery slips, tags, and documentation', 'handling-invoices-delivery-slips-tags-and-documentation', '2025-12-13 13:09:00'),
(62, 'Medical care', 'medical-care', '2025-12-17 16:22:54'),
(63, 'medicine knowledge', 'medicine-knowledge', '2025-12-17 16:22:54'),
(64, 'patient care', 'patient-care', '2025-12-17 16:22:54'),
(67, 'finance', 'finance', '2025-12-17 16:57:48'),
(68, 'Banking', 'banking', '2025-12-17 16:57:48'),
(69, 'Bank policy', 'bank-policy', '2025-12-17 16:57:49'),
(70, 'Problem Solving', 'problem-solving', '2025-12-17 16:57:49'),
(71, 'Customer handling', 'customer-handling', '2025-12-17 16:57:49'),
(72, 'English Speaking', 'english-speaking', '2025-12-17 16:57:49'),
(73, 'Time Punctaul', 'time-punctaul', '2025-12-17 16:57:49');

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `country_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `country_id`, `name`, `slug`, `created_at`) VALUES
(1, 1, 'Uttar Pradesh', 'uttar-pradesh', '2025-12-30 13:20:33'),
(2, 1, 'Delhi', 'delhi', '2025-12-30 13:20:33'),
(3, 1, 'Karnataka', 'karnataka', '2025-12-30 13:20:33'),
(4, 2, 'Moscow', 'moscow', '2025-12-30 13:33:53'),
(5, 1, 'Kerala', 'kerala', '2025-12-30 13:33:53'),
(6, 3, 'Ararat Province', 'ararat-province', '2025-12-30 13:33:53');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_notifications`
--

CREATE TABLE `subscription_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('expiry_reminder','payment_due','payment_failed','trial_ending','discount_offer','feature_locked','renewal_success') NOT NULL,
  `channel` enum('email','sms','in_app','push') DEFAULT 'email',
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('pending','sent','failed','read') DEFAULT 'pending',
  `sent_at` datetime DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_payments`
--

CREATE TABLE `subscription_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(8) DEFAULT 'INR',
  `billing_cycle` enum('monthly','quarterly','annual') NOT NULL,
  `gateway` varchar(32) DEFAULT 'razorpay',
  `gateway_payment_id` varchar(255) DEFAULT NULL,
  `gateway_order_id` varchar(255) DEFAULT NULL,
  `gateway_signature` varchar(512) DEFAULT NULL,
  `status` enum('pending','processing','completed','failed','refunded','cancelled') DEFAULT 'pending',
  `failure_reason` text DEFAULT NULL,
  `invoice_number` varchar(64) DEFAULT NULL,
  `invoice_url` varchar(512) DEFAULT NULL,
  `invoice_generated_at` datetime DEFAULT NULL,
  `refund_amount` decimal(10,2) DEFAULT 0.00,
  `refund_reason` text DEFAULT NULL,
  `refunded_at` datetime DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription_payments`
--

INSERT INTO `subscription_payments` (`id`, `subscription_id`, `employer_id`, `amount`, `currency`, `billing_cycle`, `gateway`, `gateway_payment_id`, `gateway_order_id`, `gateway_signature`, `status`, `failure_reason`, `invoice_number`, `invoice_url`, `invoice_generated_at`, `refund_amount`, `refund_reason`, `refunded_at`, `metadata`, `paid_at`, `created_at`, `updated_at`) VALUES
(18, 21, 2, 1650.00, 'INR', 'monthly', 'razorpay', NULL, NULL, NULL, 'completed', NULL, NULL, NULL, NULL, 0.00, NULL, NULL, NULL, NULL, '2026-01-06 18:40:34', '2026-01-06 18:43:10');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(128) NOT NULL,
  `slug` varchar(128) NOT NULL,
  `tier` enum('free','basic','premium','enterprise') NOT NULL DEFAULT 'free',
  `description` text DEFAULT NULL,
  `price_monthly` decimal(10,2) DEFAULT 0.00,
  `price_quarterly` decimal(10,2) DEFAULT 0.00,
  `price_annual` decimal(10,2) DEFAULT 0.00,
  `currency` varchar(8) DEFAULT 'INR',
  `max_job_posts` int(11) DEFAULT 1,
  `max_contacts_per_month` int(11) DEFAULT 50,
  `max_resume_downloads` int(11) DEFAULT 10,
  `max_chat_messages` int(11) DEFAULT 100,
  `job_post_boost` tinyint(1) DEFAULT 0,
  `priority_support` tinyint(1) DEFAULT 0,
  `advanced_filters` tinyint(1) DEFAULT 0,
  `candidate_mobile_visible` tinyint(1) DEFAULT 0,
  `resume_download_enabled` tinyint(1) DEFAULT 0,
  `chat_enabled` tinyint(1) DEFAULT 0,
  `ai_matching` tinyint(1) DEFAULT 0,
  `analytics_dashboard` tinyint(1) DEFAULT 0,
  `custom_branding` tinyint(1) DEFAULT 0,
  `api_access` tinyint(1) DEFAULT 0,
  `trial_days` int(11) DEFAULT 0,
  `trial_enabled` tinyint(1) DEFAULT 0,
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `discount_valid_until` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription_plans`
--

INSERT INTO `subscription_plans` (`id`, `name`, `slug`, `tier`, `description`, `price_monthly`, `price_quarterly`, `price_annual`, `currency`, `max_job_posts`, `max_contacts_per_month`, `max_resume_downloads`, `max_chat_messages`, `job_post_boost`, `priority_support`, `advanced_filters`, `candidate_mobile_visible`, `resume_download_enabled`, `chat_enabled`, `ai_matching`, `analytics_dashboard`, `custom_branding`, `api_access`, `trial_days`, `trial_enabled`, `discount_percentage`, `discount_valid_until`, `is_active`, `is_featured`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Free', 'free', 'free', 'Perfect for startups and small businesses', 0.00, 0.00, 0.00, 'INR', 1, 50, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0.00, NULL, 1, 0, 1, '2025-11-26 15:01:20', '2025-11-26 15:01:20'),
(2, 'Basic', 'basic', 'basic', 'Essential features for growing businesses', 400.00, 1100.00, 4000.00, 'INR', 5, 200, 10, 100, 0, 0, 1, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0.00, NULL, 1, 0, 2, '2025-11-26 15:01:20', '2025-11-26 15:01:20'),
(3, 'Premium', 'premium', 'premium', 'Advanced features for established companies', 850.00, 2300.00, 8500.00, 'INR', -1, -1, -1, -1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0.00, NULL, 1, 1, 3, '2025-11-26 15:01:20', '2025-11-26 15:01:20'),
(4, 'Enterprise', 'enterprise', 'enterprise', 'Custom solutions for large organizations', 1650.00, 4500.00, 16500.00, 'INR', -1, -1, -1, -1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0.00, NULL, 1, 0, 4, '2025-11-26 15:01:20', '2025-11-26 15:01:20');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_usage_logs`
--

CREATE TABLE `subscription_usage_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subscription_id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `action_type` enum('contact_view','resume_download','chat_message','job_post','filter_used','mobile_view') NOT NULL,
  `candidate_id` bigint(20) UNSIGNED DEFAULT NULL,
  `job_id` bigint(20) UNSIGNED DEFAULT NULL,
  `application_id` bigint(20) UNSIGNED DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription_usage_logs`
--

INSERT INTO `subscription_usage_logs` (`id`, `subscription_id`, `employer_id`, `action_type`, `candidate_id`, `job_id`, `application_id`, `metadata`, `created_at`) VALUES
(1, 20, 11, 'chat_message', NULL, 39, NULL, '{\"source\":\"applications_list\"}', '2026-01-06 16:10:38');

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('open','assigned','pending','closed','escalated') DEFAULT 'open',
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `category` enum('payment','job_visibility','kyc','general') DEFAULT 'general',
  `employer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `candidate_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_ticket_messages`
--

CREATE TABLE `support_ticket_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `sender_user_id` bigint(20) UNSIGNED NOT NULL,
  `body` text NOT NULL,
  `attachments` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `created_at`, `updated_at`) VALUES
(1, 'email_footer', 'Mindware Infotech - Empowering Careers\nContact us: support@mindinfotech.com | +91 123 456 7890\nUnsubscribe options available in your profile.', 'email', '2025-12-20 08:56:48', '2025-12-20 08:56:48');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `testimonial_type` varchar(50) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `video_url` varchar(512) DEFAULT NULL,
  `image` varchar(512) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `testimonial_type`, `title`, `name`, `designation`, `company`, `message`, `video_url`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'client', 'We hired multiple shop-floor workers and technicians using this platform.', 'Anjali Kumari', 'Web Dev Intern', 'Mindware Infotech', 'We hired multiple shop-floor workers and technicians using this platform. The process is straightforward, and the support team is responsive. It has become our go-to hiring solution.', NULL, 'http://localhost:8000/storage/uploads/testimonials/695628e194f38_istockphoto-1135381120-612x612.jpg', 1, '2025-12-15 13:51:49', '2025-12-15 13:51:49'),
(2, 'client', 'Best Platform for any employer who wants to hiring fast and skilled work force', 'Prabhat Paswan', 'Senior Soft. Developer', 'Mindware', '“Hiring drivers and warehouse staff was always challenging for us. After using this platform, we were able to connect with verified candidates quickly. The job posting and response rate are excellent.”', NULL, NULL, 1, '2025-12-15 14:22:40', '2025-12-15 14:22:40'),
(4, 'candidate', NULL, 'Himanshu Choudhary', 'Web Developer Intern', 'Mindware Infotech', '“This job portal has completely simplified our hiring process. We were able to find skilled and reliable candidates much faster than expected. The candidate profiles are detailed, and the resume quality is impressive. It has saved our HR team a lot of time and effort.', NULL, NULL, 1, '2025-12-15 14:26:38', '2025-12-15 14:26:38'),
(5, 'candidate', 'My Kids’ Learning Environment is Safe with Quick Heal', 'Varsha Thakur', 'HR Manager', 'Mindware Infotech', '“This job portal has completely simplified our hiring process. We were able to find skilled and reliable candidates much faster than expected. The candidate profiles are detailed, and the resume quality is impressive. It has saved our HR team a lot of time and effort.', NULL, NULL, 1, '2025-12-15 14:29:39', '2025-12-15 14:29:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `google_email` varchar(255) DEFAULT NULL,
  `google_name` varchar(255) DEFAULT NULL,
  `google_picture` text DEFAULT NULL,
  `apple_id` varchar(255) DEFAULT NULL,
  `apple_email` varchar(255) DEFAULT NULL,
  `apple_name` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','employer','candidate','sales_manager','sales_executive') NOT NULL DEFAULT 'candidate',
  `status` enum('pending','active','suspended','deleted') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `is_email_verified` tinyint(1) DEFAULT 0,
  `is_phone_verified` tinyint(1) DEFAULT 0,
  `twofa_secret` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `google_id`, `google_email`, `google_name`, `google_picture`, `apple_id`, `apple_email`, `apple_name`, `password_hash`, `role`, `status`, `created_at`, `updated_at`, `last_login`, `phone`, `is_email_verified`, `is_phone_verified`, `twofa_secret`) VALUES
(2, 'hr@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$PKIHkb.1rdm79ic4RhenaOSdvzKUSWOlAZIosSDBn0VqCu8tojHhy', 'employer', 'active', '2025-11-24 16:31:07', '2025-11-24 16:36:12', '2025-11-25 07:46:04', '+919910112688', 0, 0, NULL),
(3, 'sales@indianbarcode.com', '113315662264609378735', 'sales@indianbarcode.com', 'MINDWARE', 'https://lh3.googleusercontent.com/a/ACg8ocKe01CcmOTuav3K_RkbHDOWxmWdEPS4jPq18vHQJQrZjjbxMbgcBw=s96-c', NULL, NULL, NULL, '$2y$10$RL/O/ph2y2E34N8wozAILeHKPUWTNkG/PgqhVm90ncWR4W7Q0bQ3S', 'employer', 'active', '2025-11-24 16:41:03', '2026-01-05 18:44:49', '2026-01-05 13:14:49', '+919717122688', 1, 0, NULL),
(9, 'infotechmindware@gmail.com', '112724768084460695874', 'infotechmindware@gmail.com', 'Mynds Global India', 'https://lh3.googleusercontent.com/a/ACg8ocKKrySUOjmxRFhh7Ttwm5m_Z823lRsYOwRfz2WWyvrc9v1-mA=s96-c', NULL, NULL, NULL, '$2y$10$TLTD3n4zO2x8nDe08XdXI.AHx3ujGQilWShw.B3b7pEqDPS4bL.ua', 'admin', 'active', '2025-11-27 11:54:40', '2025-12-20 15:13:28', '2025-12-17 11:04:47', NULL, 1, 0, NULL),
(10, 'tagsindia1997@gmail.com', '113651223290918877980', 'tagsindia1997@gmail.com', 'Prabhat Kumar', 'https://lh3.googleusercontent.com/a/ACg8ocKPnqmzJS7Gz7IhYC9DWVhbHicbjm4Emww-WQw83TxDv9UKFw=s96-c', NULL, NULL, NULL, '$2y$10$lrpP/2oGRE5yLw6EqRtiU.cD2MHqomkxVT0eE1S.H6YT4TJTsfLwO', 'sales_manager', 'active', '2025-11-27 11:57:43', '2025-12-22 12:12:55', '2025-12-22 06:42:55', NULL, 1, 0, NULL),
(12, 'gm@indianbarcode.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$bkVi0pdlWoJU/7Daxq/m2OTWqlaqwVHORvBsReWWqoyWTUU8EdoCS', 'super_admin', 'active', '2025-12-02 12:59:16', '2026-01-02 11:03:49', '2026-01-02 05:33:49', '+919810822688', 1, 0, NULL),
(30, 'barcodevault@gmail.com', '106585191140102836360', 'barcodevault@gmail.com', 'Barcode Vault', 'https://lh3.googleusercontent.com/a/ACg8ocL6Y-iMv5lcXlJi9HYv9Btt_FW1a5gTcFYLZSGK-vJ9WNsuR_k=s96-c', NULL, NULL, NULL, '$2y$10$Io4bd9AhP.hIom0bxt/AoOIaXei6tnzXiLjX/.DQjGIlPCoELflcS', 'employer', 'active', '2025-12-05 16:36:23', '2025-12-19 12:51:18', '2025-12-19 07:21:18', '+918800122588', 1, 0, NULL),
(31, 'hr1@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$9CiHZJ/wn8/5paiRD1vgcemlZoL0mEtnLp0aISmwLtWNrgdWmC6HW', 'candidate', 'active', '2025-12-12 18:50:44', '2025-12-12 18:51:27', '2025-12-12 13:21:27', NULL, 0, 0, NULL),
(35, 'indianrfid@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$wGspeMjmsdXVDqG0rvYRuupjWaZN6S5Ms0Ml0Z.KozClfA5fhzL1u', 'candidate', 'active', '2025-12-13 11:32:22', '2026-01-06 17:00:03', '2026-01-06 11:30:03', NULL, 0, 0, NULL),
(36, 'info.myndsglobal@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$mkpQt1HHwF7k2IWUB8Z1A.UM3IzMGNZ0uzuAag48RALx.slPKcrTy', 'employer', 'active', '2025-12-19 16:49:05', '2026-01-07 12:14:17', '2026-01-07 06:44:17', '+919876543210', 0, 0, NULL),
(39, 'semiconductorsindia@gmail.com', NULL, NULL, 'Semiconductors India', NULL, NULL, NULL, NULL, '$2y$10$wIUWy3y.Jd5knB7sdDuvYODGG1.rWg/7gIDAXSgOOJ5pO29WYoBaS', '', 'active', '2025-12-22 13:05:01', '2025-12-22 13:05:01', NULL, NULL, 0, 0, NULL),
(40, 'pkrr@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$DoHe.jvQ9RkD.Ua8pQ4K2.G6VMkA5c0XvQo1y3V476PyspG4CKVvu', 'candidate', 'active', '2026-01-06 14:17:36', '2026-01-06 14:18:10', '2026-01-06 08:48:10', NULL, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `verifications`
--

CREATE TABLE `verifications` (
  `id` int(11) NOT NULL,
  `user_type` enum('employer','candidate') NOT NULL,
  `user_id` int(11) NOT NULL,
  `document_type` varchar(100) DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','assigned','approved','rejected') DEFAULT 'pending',
  `assigned_to` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verification_logs`
--

CREATE TABLE `verification_logs` (
  `id` int(11) NOT NULL,
  `verification_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `video_resumes`
--

CREATE TABLE `video_resumes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `resume_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(512) NOT NULL,
  `thumbnail_path` varchar(512) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'Duration in seconds',
  `file_size` bigint(20) DEFAULT NULL COMMENT 'File size in bytes',
  `mime_type` varchar(100) DEFAULT NULL,
  `transcription` text DEFAULT NULL COMMENT 'AI-generated transcript',
  `is_premium` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `webhooks`
--

CREATE TABLE `webhooks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employer_id` bigint(20) UNSIGNED NOT NULL,
  `url` varchar(1024) NOT NULL,
  `events` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`events`)),
  `secret` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `last_delivery_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `gateway` varchar(50) DEFAULT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `processed` tinyint(1) DEFAULT 0,
  `received_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `actor_type` (`actor_type`,`actor_id`),
  ADD KEY `action` (`action`),
  ADD KEY `target_type` (`target_type`,`target_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `ai_resume_suggestions`
--
ALTER TABLE `ai_resume_suggestions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resume_id` (`resume_id`),
  ADD KEY `idx_suggestion_type` (`suggestion_type`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `candidate_user_id` (`candidate_user_id`),
  ADD KEY `status` (`status`),
  ADD KEY `applied_at` (`applied_at`),
  ADD KEY `idx_resume_id` (`resume_id`);

--
-- Indexes for table `application_events`
--
ALTER TABLE `application_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `entity_type` (`entity_type`,`entity_id`),
  ADD KEY `performed_by` (`performed_by`);

--
-- Indexes for table `benefits`
--
ALTER TABLE `benefits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `billing_profiles`
--
ALTER TABLE `billing_profiles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_blogs_slug` (`slug`),
  ADD KEY `idx_blogs_published` (`published_at`),
  ADD KEY `idx_blogs_author` (`author_id`),
  ADD KEY `idx_blogs_status` (`status_id`);

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_blog_categories_slug` (`slug`),
  ADD KEY `idx_blog_categories_active` (`is_active`);

--
-- Indexes for table `blog_category_map`
--
ALTER TABLE `blog_category_map`
  ADD PRIMARY KEY (`blog_id`,`category_id`);

--
-- Indexes for table `blog_tags`
--
ALTER TABLE `blog_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_blog_tags_slug` (`slug`);

--
-- Indexes for table `blog_tag_map`
--
ALTER TABLE `blog_tag_map`
  ADD PRIMARY KEY (`blog_id`,`tag_id`);

--
-- Indexes for table `call_logs`
--
ALTER TABLE `call_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `candidate_user_id` (`candidate_user_id`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `user_id_2` (`user_id`),
  ADD KEY `is_profile_complete` (`is_profile_complete`),
  ADD KEY `is_premium` (`is_premium`),
  ADD KEY `profile_strength` (`profile_strength`);

--
-- Indexes for table `candidate_education`
--
ALTER TABLE `candidate_education`
  ADD PRIMARY KEY (`id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- Indexes for table `candidate_experience`
--
ALTER TABLE `candidate_experience`
  ADD PRIMARY KEY (`id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- Indexes for table `candidate_interest`
--
ALTER TABLE `candidate_interest`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_interest` (`candidate_id`,`employer_id`,`job_id`),
  ADD KEY `candidate_id` (`candidate_id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `interest_level` (`interest_level`);

--
-- Indexes for table `candidate_job_scores`
--
ALTER TABLE `candidate_job_scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_candidate_job` (`candidate_id`,`job_id`),
  ADD KEY `idx_job_score` (`job_id`,`overall_match_score`),
  ADD KEY `idx_candidate_score` (`candidate_id`,`overall_match_score`),
  ADD KEY `idx_recommendation` (`recommendation`,`overall_match_score`),
  ADD KEY `idx_ai_parsed` (`ai_parsed_at`);

--
-- Indexes for table `candidate_languages`
--
ALTER TABLE `candidate_languages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_candidate_language` (`candidate_id`,`language`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- Indexes for table `candidate_premium_purchases`
--
ALTER TABLE `candidate_premium_purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `candidate_id` (`candidate_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `candidate_quality_scores`
--
ALTER TABLE `candidate_quality_scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_application` (`application_id`),
  ADD KEY `overall_score` (`overall_score`),
  ADD KEY `calculated_at` (`calculated_at`);

--
-- Indexes for table `candidate_skills`
--
ALTER TABLE `candidate_skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_candidate_skill` (`candidate_id`,`skill_id`),
  ADD KEY `candidate_id` (`candidate_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `state_id` (`state_id`);

--
-- Indexes for table `communication_logs`
--
ALTER TABLE `communication_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `candidate_id` (`candidate_id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `communication_type` (`communication_type`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `company_blogs`
--
ALTER TABLE `company_blogs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `company_followers`
--
ALTER TABLE `company_followers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_follow` (`candidate_id`,`company_id`);

--
-- Indexes for table `company_stats`
--
ALTER TABLE `company_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `company_id` (`company_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `candidate_user_id` (`candidate_user_id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `data_export_logs`
--
ALTER TABLE `data_export_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `export_type` (`export_type`),
  ADD KEY `exported_at` (`exported_at`);

--
-- Indexes for table `discount_codes`
--
ALTER TABLE `discount_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `code_2` (`code`),
  ADD KEY `is_active` (`is_active`),
  ADD KEY `valid_until` (`valid_until`);

--
-- Indexes for table `document_ocr_results`
--
ALTER TABLE `document_ocr_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_id` (`document_id`),
  ADD KEY `employer_id` (`employer_id`);

--
-- Indexes for table `employers`
--
ALTER TABLE `employers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `company_slug` (`company_slug`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `company_name` (`company_name`),
  ADD KEY `kyc_status` (`kyc_status`),
  ADD KEY `idx_employers_kyc_assigned_to` (`kyc_assigned_to`);

--
-- Indexes for table `employer_api_keys`
--
ALTER TABLE `employer_api_keys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `revoked` (`revoked`);

--
-- Indexes for table `employer_blacklist`
--
ALTER TABLE `employer_blacklist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_value_unique` (`type`,`value`);

--
-- Indexes for table `employer_kyc_documents`
--
ALTER TABLE `employer_kyc_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `review_status` (`review_status`);

--
-- Indexes for table `employer_payments`
--
ALTER TABLE `employer_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `employer_risk_scores`
--
ALTER TABLE `employer_risk_scores`
  ADD PRIMARY KEY (`employer_id`);

--
-- Indexes for table `employer_settings`
--
ALTER TABLE `employer_settings`
  ADD PRIMARY KEY (`employer_id`);

--
-- Indexes for table `employer_subscriptions`
--
ALTER TABLE `employer_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `status` (`status`),
  ADD KEY `expires_at` (`expires_at`),
  ADD KEY `next_billing_date` (`next_billing_date`);

--
-- Indexes for table `employer_verification_logs`
--
ALTER TABLE `employer_verification_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `rule_name` (`rule_name`);

--
-- Indexes for table `hiring_funnel_events`
--
ALTER TABLE `hiring_funnel_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `stage` (`stage`),
  ADD KEY `entered_at` (`entered_at`);

--
-- Indexes for table `interviews`
--
ALTER TABLE `interviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `scheduled_start` (`scheduled_start`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`);

--
-- Indexes for table `ip_whitelist`
--
ALTER TABLE `ip_whitelist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_ip` (`ip_address`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `status` (`status`),
  ADD KEY `publish_at` (`publish_at`),
  ADD KEY `expires_at` (`expires_at`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indexes for table `job_benefits`
--
ALTER TABLE `job_benefits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `job_benefit_unique` (`job_id`,`benefit_id`),
  ADD KEY `benefit_id` (`benefit_id`);

--
-- Indexes for table `job_bookmarks`
--
ALTER TABLE `job_bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_bookmark` (`candidate_id`,`job_id`),
  ADD KEY `candidate_id` (`candidate_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `job_categories`
--
ALTER TABLE `job_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_slug` (`slug`),
  ADD UNIQUE KEY `unique_name` (`name`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_sort_order` (`sort_order`);

--
-- Indexes for table `job_engagement`
--
ALTER TABLE `job_engagement`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_job` (`job_id`),
  ADD KEY `engagement_score` (`engagement_score`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `job_locations`
--
ALTER TABLE `job_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_locations_job_id_foreign` (`job_id`),
  ADD KEY `job_locations_city_id_foreign` (`city_id`),
  ADD KEY `country_id` (`country_id`),
  ADD KEY `state_id` (`state_id`);

--
-- Indexes for table `job_review_queue`
--
ALTER TABLE `job_review_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `job_saves_log`
--
ALTER TABLE `job_saves_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_job_user` (`job_id`,`user_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `job_shares_log`
--
ALTER TABLE `job_shares_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `shared_at` (`shared_at`),
  ADD KEY `share_platform` (`share_platform`);

--
-- Indexes for table `job_skills`
--
ALTER TABLE `job_skills`
  ADD PRIMARY KEY (`job_id`,`skill_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `job_titles`
--
ALTER TABLE `job_titles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_title` (`title`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_active` (`is_active`);
ALTER TABLE `job_titles` ADD FULLTEXT KEY `idx_title_fulltext` (`title`);

--
-- Indexes for table `job_views`
--
ALTER TABLE `job_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `candidate_id` (`candidate_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `viewed_at` (`viewed_at`);

--
-- Indexes for table `job_views_log`
--
ALTER TABLE `job_views_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `viewed_at` (`viewed_at`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`user_type`),
  ADD KEY `login_successful` (`login_successful`),
  ADD KEY `logged_in_at` (`logged_in_at`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `sender_user_id` (`sender_user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `candidate_id` (`candidate_id`),
  ADD KEY `channel` (`channel`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `resumes`
--
ALTER TABLE `resumes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_candidate_id` (`candidate_id`),
  ADD KEY `idx_template_id` (`template_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_is_primary` (`candidate_id`,`is_primary`);

--
-- Indexes for table `resume_analytics`
--
ALTER TABLE `resume_analytics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resume_id` (`resume_id`),
  ADD KEY `idx_event_type` (`event_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `resume_sections`
--
ALTER TABLE `resume_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resume_id` (`resume_id`),
  ADD KEY `idx_sort_order` (`resume_id`,`sort_order`);

--
-- Indexes for table `resume_share_links`
--
ALTER TABLE `resume_share_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_resume_id` (`resume_id`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `resume_templates`
--
ALTER TABLE `resume_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_is_premium` (`is_premium`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_candidate_id` (`candidate_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `sales_communications`
--
ALTER TABLE `sales_communications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sales_comm_lead` (`lead_id`);

--
-- Indexes for table `sales_followups`
--
ALTER TABLE `sales_followups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sales_follow_lead` (`lead_id`);

--
-- Indexes for table `sales_leads`
--
ALTER TABLE `sales_leads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `stage` (`stage`);

--
-- Indexes for table `sales_lead_activities`
--
ALTER TABLE `sales_lead_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sales_activities_lead` (`lead_id`);

--
-- Indexes for table `sales_lead_assignments`
--
ALTER TABLE `sales_lead_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sales_assign_lead` (`lead_id`),
  ADD KEY `fk_sales_assign_user` (`assigned_to_id`);

--
-- Indexes for table `sales_lead_notes`
--
ALTER TABLE `sales_lead_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sales_notes_lead` (`lead_id`);

--
-- Indexes for table `sales_notifications`
--
ALTER TABLE `sales_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`,`is_read`,`created_at`);

--
-- Indexes for table `sales_payments`
--
ALTER TABLE `sales_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sales_pay_lead` (`lead_id`);

--
-- Indexes for table `sales_stages`
--
ALTER TABLE `sales_stages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `sales_users`
--
ALTER TABLE `sales_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales_user_metrics`
--
ALTER TABLE `sales_user_metrics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_period` (`sales_user_id`,`period_date`);

--
-- Indexes for table `seo_page_logs`
--
ALTER TABLE `seo_page_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_page` (`page_type`,`country_id`,`state_id`,`city_id`);

--
-- Indexes for table `seo_rules`
--
ALTER TABLE `seo_rules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `country_id` (`country_id`);

--
-- Indexes for table `subscription_notifications`
--
ALTER TABLE `subscription_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_id` (`subscription_id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `type` (`type`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `subscription_payments`
--
ALTER TABLE `subscription_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `subscription_id` (`subscription_id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `status` (`status`),
  ADD KEY `gateway_payment_id` (`gateway_payment_id`),
  ADD KEY `invoice_number_2` (`invoice_number`);

--
-- Indexes for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `tier` (`tier`),
  ADD KEY `is_active` (`is_active`),
  ADD KEY `slug_2` (`slug`);

--
-- Indexes for table `subscription_usage_logs`
--
ALTER TABLE `subscription_usage_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subscription_id` (`subscription_id`),
  ADD KEY `employer_id` (`employer_id`),
  ADD KEY `action_type` (`action_type`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `priority` (`priority`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `support_ticket_messages`
--
ALTER TABLE `support_ticket_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_testimonials_type` (`testimonial_type`),
  ADD KEY `idx_testimonials_active` (`is_active`),
  ADD KEY `idx_testimonials_created` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD UNIQUE KEY `apple_id` (`apple_id`),
  ADD KEY `role` (`role`),
  ADD KEY `status` (`status`),
  ADD KEY `idx_google_id` (`google_id`),
  ADD KEY `idx_apple_id` (`apple_id`);

--
-- Indexes for table `verifications`
--
ALTER TABLE `verifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `verification_logs`
--
ALTER TABLE `verification_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `video_resumes`
--
ALTER TABLE `video_resumes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_resume_id` (`resume_id`);

--
-- Indexes for table `webhooks`
--
ALTER TABLE `webhooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employer_id` (`employer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_resume_suggestions`
--
ALTER TABLE `ai_resume_suggestions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `application_events`
--
ALTER TABLE `application_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `benefits`
--
ALTER TABLE `benefits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `billing_profiles`
--
ALTER TABLE `billing_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `blog_tags`
--
ALTER TABLE `blog_tags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `call_logs`
--
ALTER TABLE `call_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `candidate_education`
--
ALTER TABLE `candidate_education`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidate_experience`
--
ALTER TABLE `candidate_experience`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidate_interest`
--
ALTER TABLE `candidate_interest`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `candidate_job_scores`
--
ALTER TABLE `candidate_job_scores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `candidate_languages`
--
ALTER TABLE `candidate_languages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidate_premium_purchases`
--
ALTER TABLE `candidate_premium_purchases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `candidate_quality_scores`
--
ALTER TABLE `candidate_quality_scores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidate_skills`
--
ALTER TABLE `candidate_skills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `communication_logs`
--
ALTER TABLE `communication_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `company_blogs`
--
ALTER TABLE `company_blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `company_followers`
--
ALTER TABLE `company_followers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_stats`
--
ALTER TABLE `company_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `data_export_logs`
--
ALTER TABLE `data_export_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discount_codes`
--
ALTER TABLE `discount_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_ocr_results`
--
ALTER TABLE `document_ocr_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employers`
--
ALTER TABLE `employers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `employer_api_keys`
--
ALTER TABLE `employer_api_keys`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employer_blacklist`
--
ALTER TABLE `employer_blacklist`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employer_kyc_documents`
--
ALTER TABLE `employer_kyc_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `employer_payments`
--
ALTER TABLE `employer_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `employer_subscriptions`
--
ALTER TABLE `employer_subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `employer_verification_logs`
--
ALTER TABLE `employer_verification_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hiring_funnel_events`
--
ALTER TABLE `hiring_funnel_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ip_whitelist`
--
ALTER TABLE `ip_whitelist`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `job_benefits`
--
ALTER TABLE `job_benefits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `job_bookmarks`
--
ALTER TABLE `job_bookmarks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `job_categories`
--
ALTER TABLE `job_categories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `job_engagement`
--
ALTER TABLE `job_engagement`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_locations`
--
ALTER TABLE `job_locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `job_review_queue`
--
ALTER TABLE `job_review_queue`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `job_saves_log`
--
ALTER TABLE `job_saves_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_shares_log`
--
ALTER TABLE `job_shares_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_titles`
--
ALTER TABLE `job_titles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1044;

--
-- AUTO_INCREMENT for table `job_views`
--
ALTER TABLE `job_views`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `job_views_log`
--
ALTER TABLE `job_views_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `notification_logs`
--
ALTER TABLE `notification_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `resumes`
--
ALTER TABLE `resumes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `resume_analytics`
--
ALTER TABLE `resume_analytics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resume_sections`
--
ALTER TABLE `resume_sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1016;

--
-- AUTO_INCREMENT for table `resume_share_links`
--
ALTER TABLE `resume_share_links`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resume_templates`
--
ALTER TABLE `resume_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `sales_communications`
--
ALTER TABLE `sales_communications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_followups`
--
ALTER TABLE `sales_followups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_leads`
--
ALTER TABLE `sales_leads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sales_lead_activities`
--
ALTER TABLE `sales_lead_activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_lead_assignments`
--
ALTER TABLE `sales_lead_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_lead_notes`
--
ALTER TABLE `sales_lead_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_notifications`
--
ALTER TABLE `sales_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_payments`
--
ALTER TABLE `sales_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_stages`
--
ALTER TABLE `sales_stages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_users`
--
ALTER TABLE `sales_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_user_metrics`
--
ALTER TABLE `sales_user_metrics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seo_page_logs`
--
ALTER TABLE `seo_page_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `seo_rules`
--
ALTER TABLE `seo_rules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `subscription_notifications`
--
ALTER TABLE `subscription_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_payments`
--
ALTER TABLE `subscription_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `subscription_usage_logs`
--
ALTER TABLE `subscription_usage_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_ticket_messages`
--
ALTER TABLE `support_ticket_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `verifications`
--
ALTER TABLE `verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `verification_logs`
--
ALTER TABLE `verification_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `video_resumes`
--
ALTER TABLE `video_resumes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `webhooks`
--
ALTER TABLE `webhooks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_resume_suggestions`
--
ALTER TABLE `ai_resume_suggestions`
  ADD CONSTRAINT `fk_ai_suggestions_resume` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`candidate_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_applications_resume` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `application_events`
--
ALTER TABLE `application_events`
  ADD CONSTRAINT `application_events_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `candidate_education`
--
ALTER TABLE `candidate_education`
  ADD CONSTRAINT `candidate_education_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `candidate_experience`
--
ALTER TABLE `candidate_experience`
  ADD CONSTRAINT `candidate_experience_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `candidate_interest`
--
ALTER TABLE `candidate_interest`
  ADD CONSTRAINT `candidate_interest_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `candidate_interest_ibfk_2` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `candidate_interest_ibfk_3` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `candidate_job_scores`
--
ALTER TABLE `candidate_job_scores`
  ADD CONSTRAINT `fk_cjs_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cjs_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `candidate_languages`
--
ALTER TABLE `candidate_languages`
  ADD CONSTRAINT `candidate_languages_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `candidate_premium_purchases`
--
ALTER TABLE `candidate_premium_purchases`
  ADD CONSTRAINT `candidate_premium_purchases_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `candidate_quality_scores`
--
ALTER TABLE `candidate_quality_scores`
  ADD CONSTRAINT `candidate_quality_scores_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `candidate_skills`
--
ALTER TABLE `candidate_skills`
  ADD CONSTRAINT `candidate_skills_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `candidate_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `cities_ibfk_1` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `communication_logs`
--
ALTER TABLE `communication_logs`
  ADD CONSTRAINT `communication_logs_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `communication_logs_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `communication_logs_ibfk_3` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_2` FOREIGN KEY (`candidate_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `data_export_logs`
--
ALTER TABLE `data_export_logs`
  ADD CONSTRAINT `data_export_logs_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employers`
--
ALTER TABLE `employers`
  ADD CONSTRAINT `employers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employer_api_keys`
--
ALTER TABLE `employer_api_keys`
  ADD CONSTRAINT `employer_api_keys_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employer_kyc_documents`
--
ALTER TABLE `employer_kyc_documents`
  ADD CONSTRAINT `employer_kyc_documents_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employer_payments`
--
ALTER TABLE `employer_payments`
  ADD CONSTRAINT `employer_payments_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employer_settings`
--
ALTER TABLE `employer_settings`
  ADD CONSTRAINT `employer_settings_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employer_subscriptions`
--
ALTER TABLE `employer_subscriptions`
  ADD CONSTRAINT `employer_subscriptions_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employer_subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`);

--
-- Constraints for table `hiring_funnel_events`
--
ALTER TABLE `hiring_funnel_events`
  ADD CONSTRAINT `hiring_funnel_events_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `interviews`
--
ALTER TABLE `interviews`
  ADD CONSTRAINT `interviews_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interviews_ibfk_2` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_benefits`
--
ALTER TABLE `job_benefits`
  ADD CONSTRAINT `job_benefits_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_benefits_ibfk_2` FOREIGN KEY (`benefit_id`) REFERENCES `benefits` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_bookmarks`
--
ALTER TABLE `job_bookmarks`
  ADD CONSTRAINT `job_bookmarks_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_bookmarks_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_engagement`
--
ALTER TABLE `job_engagement`
  ADD CONSTRAINT `job_engagement_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_locations`
--
ALTER TABLE `job_locations`
  ADD CONSTRAINT `job_locations_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `job_locations_ibfk_2` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `job_locations_ibfk_3` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `job_locations_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_saves_log`
--
ALTER TABLE `job_saves_log`
  ADD CONSTRAINT `job_saves_log_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_saves_log_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_shares_log`
--
ALTER TABLE `job_shares_log`
  ADD CONSTRAINT `job_shares_log_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_skills`
--
ALTER TABLE `job_skills`
  ADD CONSTRAINT `job_skills_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`);

--
-- Constraints for table `job_views`
--
ALTER TABLE `job_views`
  ADD CONSTRAINT `job_views_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_views_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_views_log`
--
ALTER TABLE `job_views_log`
  ADD CONSTRAINT `job_views_log_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_history`
--
ALTER TABLE `login_history`
  ADD CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD CONSTRAINT `notification_logs_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notification_logs_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_role_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resumes`
--
ALTER TABLE `resumes`
  ADD CONSTRAINT `fk_resumes_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resume_analytics`
--
ALTER TABLE `resume_analytics`
  ADD CONSTRAINT `fk_resume_analytics_resume` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resume_sections`
--
ALTER TABLE `resume_sections`
  ADD CONSTRAINT `fk_resume_sections_resume` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resume_share_links`
--
ALTER TABLE `resume_share_links`
  ADD CONSTRAINT `fk_resume_share_links_resume` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_user_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_communications`
--
ALTER TABLE `sales_communications`
  ADD CONSTRAINT `fk_sales_comm_lead` FOREIGN KEY (`lead_id`) REFERENCES `sales_leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_followups`
--
ALTER TABLE `sales_followups`
  ADD CONSTRAINT `fk_sales_follow_lead` FOREIGN KEY (`lead_id`) REFERENCES `sales_leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_lead_activities`
--
ALTER TABLE `sales_lead_activities`
  ADD CONSTRAINT `fk_sales_activities_lead` FOREIGN KEY (`lead_id`) REFERENCES `sales_leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_lead_assignments`
--
ALTER TABLE `sales_lead_assignments`
  ADD CONSTRAINT `fk_sales_assign_lead` FOREIGN KEY (`lead_id`) REFERENCES `sales_leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sales_assign_user` FOREIGN KEY (`assigned_to_id`) REFERENCES `sales_users` (`id`);

--
-- Constraints for table `sales_lead_notes`
--
ALTER TABLE `sales_lead_notes`
  ADD CONSTRAINT `fk_sales_notes_lead` FOREIGN KEY (`lead_id`) REFERENCES `sales_leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_payments`
--
ALTER TABLE `sales_payments`
  ADD CONSTRAINT `fk_sales_pay_lead` FOREIGN KEY (`lead_id`) REFERENCES `sales_leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `states`
--
ALTER TABLE `states`
  ADD CONSTRAINT `states_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscription_notifications`
--
ALTER TABLE `subscription_notifications`
  ADD CONSTRAINT `subscription_notifications_ibfk_1` FOREIGN KEY (`subscription_id`) REFERENCES `employer_subscriptions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscription_notifications_ibfk_2` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscription_payments`
--
ALTER TABLE `subscription_payments`
  ADD CONSTRAINT `subscription_payments_ibfk_1` FOREIGN KEY (`subscription_id`) REFERENCES `employer_subscriptions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscription_payments_ibfk_2` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscription_usage_logs`
--
ALTER TABLE `subscription_usage_logs`
  ADD CONSTRAINT `subscription_usage_logs_ibfk_1` FOREIGN KEY (`subscription_id`) REFERENCES `employer_subscriptions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscription_usage_logs_ibfk_2` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_ticket_messages`
--
ALTER TABLE `support_ticket_messages`
  ADD CONSTRAINT `support_ticket_messages_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `video_resumes`
--
ALTER TABLE `video_resumes`
  ADD CONSTRAINT `fk_video_resumes_resume` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `webhooks`
--
ALTER TABLE `webhooks`
  ADD CONSTRAINT `webhooks_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `employers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
