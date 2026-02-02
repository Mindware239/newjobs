-- Insert test data for candidate profile (user_id = 10 - Tags India)
-- This will test if JSON columns work correctly

-- First, check if candidate exists for user_id 10
-- If exists, update it. If not, we'll need to create it.

-- Update existing candidate with test JSON data
UPDATE candidates 
SET 
    full_name = 'Tags India',
    dob = '1997-01-01',
    gender = 'male',
    mobile = '9988774455',
    city = 'Dwarka',
    state = 'Delhi',
    country = 'India',
    self_introduction = 'Experienced software developer with expertise in PHP, JavaScript, and web development.',
    expected_salary_min = 50000,
    expected_salary_max = 80000,
    current_salary = 60000,
    notice_period = 30,
    preferred_job_location = 'Delhi, Noida, Gurgaon',
    portfolio_url = 'https://portfolio.example.com',
    linkedin_url = 'https://linkedin.com/in/tagsindia',
    github_url = 'https://github.com/tagsindia',
    website_url = 'https://tagsindia.com',
    -- JSON columns with test data
    education_data = JSON_ARRAY(
        JSON_OBJECT(
            'degree', 'Bachelor of Science',
            'field_of_study', 'Computer Science',
            'institution', 'Delhi University',
            'start_date', '2015-07-01',
            'end_date', '2019-06-30',
            'is_current', 0,
            'grade', 'A',
            'description', 'Graduated with honors in Computer Science'
        ),
        JSON_OBJECT(
            'degree', 'Master of Science',
            'field_of_study', 'Software Engineering',
            'institution', 'IIT Delhi',
            'start_date', '2019-07-01',
            'end_date', '2021-06-30',
            'is_current', 0,
            'grade', 'A+',
            'description', 'Specialized in web development and software architecture'
        )
    ),
    experience_data = JSON_ARRAY(
        JSON_OBJECT(
            'job_title', 'Senior Software Developer',
            'company_name', 'Tech Solutions Pvt Ltd',
            'start_date', '2021-07-01',
            'end_date', NULL,
            'is_current', 1,
            'description', 'Leading development team, building scalable web applications using PHP, JavaScript, and modern frameworks.',
            'location', 'Delhi'
        ),
        JSON_OBJECT(
            'job_title', 'Software Developer',
            'company_name', 'Web Innovations Inc',
            'start_date', '2019-08-01',
            'end_date', '2021-06-30',
            'is_current', 0,
            'description', 'Developed and maintained web applications, worked with REST APIs, and collaborated with cross-functional teams.',
            'location', 'Noida'
        )
    ),
    skills_data = JSON_ARRAY(
        JSON_OBJECT(
            'skill_id', NULL,
            'name', 'PHP',
            'proficiency_level', 'advanced',
            'years_of_experience', 5
        ),
        JSON_OBJECT(
            'skill_id', NULL,
            'name', 'JavaScript',
            'proficiency_level', 'advanced',
            'years_of_experience', 4
        ),
        JSON_OBJECT(
            'skill_id', NULL,
            'name', 'MySQL',
            'proficiency_level', 'intermediate',
            'years_of_experience', 4
        ),
        JSON_OBJECT(
            'skill_id', NULL,
            'name', 'Laravel',
            'proficiency_level', 'advanced',
            'years_of_experience', 3
        ),
        JSON_OBJECT(
            'skill_id', NULL,
            'name', 'React',
            'proficiency_level', 'intermediate',
            'years_of_experience', 2
        ),
        JSON_OBJECT(
            'skill_id', NULL,
            'name', 'Node.js',
            'proficiency_level', 'intermediate',
            'years_of_experience', 2
        )
    ),
    languages_data = JSON_ARRAY(
        JSON_OBJECT(
            'language', 'English',
            'proficiency', 'fluent'
        ),
        JSON_OBJECT(
            'language', 'Hindi',
            'proficiency', 'native'
        ),
        JSON_OBJECT(
            'language', 'Punjabi',
            'proficiency', 'conversational'
        )
    ),
    updated_at = NOW()
WHERE user_id = 10;

-- If no rows were updated, insert new candidate
-- (This will only run if the UPDATE above didn't affect any rows)
INSERT INTO candidates (
    user_id, full_name, dob, gender, mobile, city, state, country,
    self_introduction, expected_salary_min, expected_salary_max, current_salary,
    notice_period, preferred_job_location, portfolio_url, linkedin_url,
    github_url, website_url, education_data, experience_data, skills_data, languages_data
)
SELECT 
    10, 'Tags India', '1997-01-01', 'male', '9988774455', 'Dwarka', 'Delhi', 'India',
    'Experienced software developer with expertise in PHP, JavaScript, and web development.',
    50000, 80000, 60000, 30, 'Delhi, Noida, Gurgaon',
    'https://portfolio.example.com', 'https://linkedin.com/in/tagsindia',
    'https://github.com/tagsindia', 'https://tagsindia.com',
    JSON_ARRAY(
        JSON_OBJECT(
            'degree', 'Bachelor of Science',
            'field_of_study', 'Computer Science',
            'institution', 'Delhi University',
            'start_date', '2015-07-01',
            'end_date', '2019-06-30',
            'is_current', 0,
            'grade', 'A',
            'description', 'Graduated with honors in Computer Science'
        ),
        JSON_OBJECT(
            'degree', 'Master of Science',
            'field_of_study', 'Software Engineering',
            'institution', 'IIT Delhi',
            'start_date', '2019-07-01',
            'end_date', '2021-06-30',
            'is_current', 0,
            'grade', 'A+',
            'description', 'Specialized in web development and software architecture'
        )
    ),
    JSON_ARRAY(
        JSON_OBJECT(
            'job_title', 'Senior Software Developer',
            'company_name', 'Tech Solutions Pvt Ltd',
            'start_date', '2021-07-01',
            'end_date', NULL,
            'is_current', 1,
            'description', 'Leading development team, building scalable web applications using PHP, JavaScript, and modern frameworks.',
            'location', 'Delhi'
        ),
        JSON_OBJECT(
            'job_title', 'Software Developer',
            'company_name', 'Web Innovations Inc',
            'start_date', '2019-08-01',
            'end_date', '2021-06-30',
            'is_current', 0,
            'description', 'Developed and maintained web applications, worked with REST APIs, and collaborated with cross-functional teams.',
            'location', 'Noida'
        )
    ),
    JSON_ARRAY(
        JSON_OBJECT('skill_id', NULL, 'name', 'PHP', 'proficiency_level', 'advanced', 'years_of_experience', 5),
        JSON_OBJECT('skill_id', NULL, 'name', 'JavaScript', 'proficiency_level', 'advanced', 'years_of_experience', 4),
        JSON_OBJECT('skill_id', NULL, 'name', 'MySQL', 'proficiency_level', 'intermediate', 'years_of_experience', 4),
        JSON_OBJECT('skill_id', NULL, 'name', 'Laravel', 'proficiency_level', 'advanced', 'years_of_experience', 3),
        JSON_OBJECT('skill_id', NULL, 'name', 'React', 'proficiency_level', 'intermediate', 'years_of_experience', 2),
        JSON_OBJECT('skill_id', NULL, 'name', 'Node.js', 'proficiency_level', 'intermediate', 'years_of_experience', 2)
    ),
    JSON_ARRAY(
        JSON_OBJECT('language', 'English', 'proficiency', 'fluent'),
        JSON_OBJECT('language', 'Hindi', 'proficiency', 'native'),
        JSON_OBJECT('language', 'Punjabi', 'proficiency', 'conversational')
    )
WHERE NOT EXISTS (SELECT 1 FROM candidates WHERE user_id = 10);

-- Verify the data was inserted/updated
SELECT 
    id, user_id, full_name, city, state,
    JSON_LENGTH(education_data) as education_count,
    JSON_LENGTH(experience_data) as experience_count,
    JSON_LENGTH(skills_data) as skills_count,
    JSON_LENGTH(languages_data) as languages_count,
    education_data,
    experience_data,
    skills_data,
    languages_data
FROM candidates 
WHERE user_id = 10;

