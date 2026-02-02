-- Simple test data insertion for candidate (user_id = 10)
-- Use this if the complex JSON syntax doesn't work

UPDATE candidates 
SET 
    full_name = 'Tags India',
    dob = '1997-01-01',
    gender = 'male',
    mobile = '9988774455',
    city = 'Dwarka',
    state = 'Delhi',
    country = 'India',
    self_introduction = 'Experienced software developer.',
    expected_salary_min = 50000,
    expected_salary_max = 80000,
    current_salary = 60000,
    notice_period = 30,
    preferred_job_location = 'Delhi',
    education_data = '[{"degree":"Bachelor of Science","field_of_study":"Computer Science","institution":"Delhi University","start_date":"2015-07-01","end_date":"2019-06-30","is_current":0,"grade":"A","description":"Graduated with honors"}]',
    experience_data = '[{"job_title":"Senior Software Developer","company_name":"Tech Solutions","start_date":"2021-07-01","end_date":null,"is_current":1,"description":"Leading development team","location":"Delhi"}]',
    skills_data = '[{"skill_id":null,"name":"PHP","proficiency_level":"advanced","years_of_experience":5},{"skill_id":null,"name":"JavaScript","proficiency_level":"advanced","years_of_experience":4},{"skill_id":null,"name":"MySQL","proficiency_level":"intermediate","years_of_experience":4}]',
    languages_data = '[{"language":"English","proficiency":"fluent"},{"language":"Hindi","proficiency":"native"}]',
    updated_at = NOW()
WHERE user_id = 10;

-- Check the result
SELECT 
    id, user_id, full_name, city, state,
    education_data,
    experience_data,
    skills_data,
    languages_data
FROM candidates 
WHERE user_id = 10;

