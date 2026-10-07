-- Careers (/careers): every internship is UNPAID and needs prior knowledge of the skill (user, 2026-10-07).
-- Safe to run more than once (matches by title; inserts only what is missing). Run on local and live.

SET NAMES utf8mb4;

-- Existing internships → unpaid, prior knowledge required
UPDATE careers SET job_type = 'Internship (Unpaid)'
 WHERE job_type LIKE 'Internship%';

UPDATE careers SET
  title = 'UI/UX Design Intern (Figma) – Unpaid',
  requirements = CONCAT('Prior hands-on knowledge of UI/UX design and Figma is required (share your portfolio or Figma links)\n', requirements)
 WHERE title = 'UI/UX DESIGNER' AND requirements NOT LIKE 'Prior hands-on knowledge%';

UPDATE careers SET
  title = 'Barcode Printer Repair Intern – Unpaid',
  requirements = CONCAT('Prior knowledge of barcode printer repair, printheads, ribbons and label calibration is required\n', requirements)
 WHERE title = 'Barcode Printer Engineer Intern' AND requirements NOT LIKE 'Prior knowledge%';

UPDATE careers SET title = CONCAT(title, ' – Unpaid')
 WHERE job_type = 'Internship (Unpaid)' AND title NOT LIKE '%Unpaid%';

-- New unpaid internships
INSERT INTO careers (title, department, location, job_type, work_type, description, responsibilities, requirements, status)
SELECT * FROM (SELECT
  'Flutter Developer Intern – Unpaid' AS title, 'Mobile Application Development' AS department, 'Dwarka, New Delhi' AS location,
  'Internship (Unpaid)' AS job_type, 'On-site' AS work_type,
  'Jobsence is looking for a Flutter Developer Intern who already knows Flutter and Dart and wants real project experience on live Android and iOS apps.\n\nThis is an UNPAID internship. You get hands-on work on live products, an internship certificate and a letter of recommendation based on performance.' AS description,
  'Build and fix screens in Flutter (Dart) for Android and iOS\nConnect app screens to REST APIs\nTest the app on real devices and fix bugs\nWork with the UI/UX team to turn Figma designs into app screens\nUse Git for version control' AS responsibilities,
  'Prior knowledge of Flutter and Dart is required (share your GitHub or app links)\nUnderstanding of REST APIs and JSON\nBasic Git knowledge\nWilling to work on-site in Dwarka, New Delhi\nThis internship is unpaid' AS requirements,
  'active' AS status) t
WHERE NOT EXISTS (SELECT 1 FROM careers WHERE title = 'Flutter Developer Intern – Unpaid');

INSERT INTO careers (title, department, location, job_type, work_type, description, responsibilities, requirements, status)
SELECT * FROM (SELECT
  'AWS Cloud Intern – Unpaid', 'IT & Cloud', 'Dwarka, New Delhi', 'Internship (Unpaid)', 'On-site',
  'Jobsence is looking for an AWS Cloud Intern with prior knowledge of Amazon Web Services to help run and deploy our web and mobile apps.\n\nThis is an UNPAID internship. You get hands-on work on live servers, an internship certificate and a letter of recommendation based on performance.',
  'Help deploy and monitor web and mobile app backends on AWS\nWork with EC2, S3, RDS and CloudWatch\nSet up backups, SSL and basic security settings\nDocument server setups and deployment steps',
  'Prior knowledge of AWS (EC2, S3, RDS, IAM) is required\nBasic Linux command line\nAn AWS certification or practice projects are a plus\nWilling to work on-site in Dwarka, New Delhi\nThis internship is unpaid',
  'active') t
WHERE NOT EXISTS (SELECT 1 FROM careers WHERE title = 'AWS Cloud Intern – Unpaid');

INSERT INTO careers (title, department, location, job_type, work_type, description, responsibilities, requirements, status)
SELECT * FROM (SELECT
  'RFID Specialist Intern (RFID Printers & Scanners) – Unpaid', 'Technical Support & Barcode Solutions Department', 'Dwarka, New Delhi', 'Internship (Unpaid)', 'On-site',
  'Jobsence is looking for an RFID Specialist Intern with prior knowledge of RFID tags, RFID printers and RFID / barcode scanners to support installations and repairs.\n\nThis is an UNPAID internship. You get hands-on work with industrial RFID equipment, an internship certificate and a letter of recommendation based on performance.',
  'Set up, encode and calibrate RFID printers and RFID tags / labels\nInstall, configure and repair RFID readers and barcode scanners\nTest read ranges and troubleshoot tag and antenna issues\nSupport customers on-site and over the phone',
  'Prior knowledge of RFID (UHF / HF tags, readers, encoding) is required\nKnowledge of RFID printers and barcode / RFID scanners\nBasic computer and networking skills\nWilling to work on-site in Dwarka, New Delhi and travel to customer sites\nThis internship is unpaid',
  'active') t
WHERE NOT EXISTS (SELECT 1 FROM careers WHERE title = 'RFID Specialist Intern (RFID Printers & Scanners) – Unpaid');

INSERT INTO careers (title, department, location, job_type, work_type, description, responsibilities, requirements, status)
SELECT * FROM (SELECT
  'Marketing & Sales Intern – Unpaid', 'Sales & Marketing', 'Dwarka, New Delhi', 'Internship (Unpaid)', 'On-site',
  'Jobsence is looking for a Marketing & Sales Intern with prior sales or marketing experience to reach employers, institutes and customers.\n\nThis is an UNPAID internship. You get real sales and marketing experience, an internship certificate and a letter of recommendation based on performance.',
  'Call and email potential clients and follow up on leads\nExplain Jobsence services and products to customers\nRun social media and WhatsApp campaigns\nKeep lead and sales records up to date\nSupport events and promotions',
  'Prior knowledge or experience of marketing and sales is required\nGood spoken Hindi and English\nComfortable with phone calls and meeting people\nBasic MS Excel / Google Sheets\nWilling to work on-site in Dwarka, New Delhi\nThis internship is unpaid',
  'active') t
WHERE NOT EXISTS (SELECT 1 FROM careers WHERE title = 'Marketing & Sales Intern – Unpaid');

INSERT INTO careers (title, department, location, job_type, work_type, description, responsibilities, requirements, status)
SELECT * FROM (SELECT
  'Computer Hardware Intern – Unpaid', 'Technical Support & Barcode Solutions Department', 'Dwarka, New Delhi', 'Internship (Unpaid)', 'On-site',
  'Jobsence is looking for a Computer Hardware Intern with prior knowledge of assembling, repairing and maintaining computers and peripherals.\n\nThis is an UNPAID internship. You get hands-on hardware work, an internship certificate and a letter of recommendation based on performance.',
  'Assemble, upgrade and repair desktops and laptops\nInstall Windows, drivers and printer / scanner software\nTroubleshoot hardware, network and peripheral problems\nMaintain office computers, printers and networking equipment',
  'Prior knowledge of computer hardware and troubleshooting is required\nBasic networking (LAN, Wi-Fi, IP settings)\nA hardware / networking course (e.g. A+, N+, CCNA) is a plus\nWilling to work on-site in Dwarka, New Delhi\nThis internship is unpaid',
  'active') t
WHERE NOT EXISTS (SELECT 1 FROM careers WHERE title = 'Computer Hardware Intern – Unpaid');
