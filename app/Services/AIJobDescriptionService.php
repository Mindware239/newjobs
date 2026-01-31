<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\AIResumeParser;

class AIJobDescriptionService
{
    private AIResumeParser $aiParser;
    private string $apiKey;
    private string $apiUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? '';
        $this->model = 'gpt-4o-mini';
        $this->apiUrl = 'https://api.openai.com/v1/chat/completions';
        
        if (empty($this->apiKey)) {
            // Fallback to template-based generation if no API key
            $this->apiKey = '';
        }
    }

    /**
     * Generate professional job description using AI
     */
    public function generateJobDescription(array $jobData): string
    {
        if (empty($this->apiKey)) {
            // Fallback to enhanced template
            return $this->generateTemplateDescription($jobData);
        }

        try {
            $prompt = $this->buildJobDescriptionPrompt($jobData);
            $aiParser = new AIResumeParser(new \App\Services\ResumeTextExtractor());
            $response = $aiParser->callOpenAI($prompt);
            
            // Extract HTML content from response
            $description = $this->extractDescriptionFromResponse($response);
            
            if (!empty($description)) {
                return $description;
            }
        } catch (\Exception $e) {
            error_log("AI Job Description generation failed: " . $e->getMessage());
        }
        
        // Fallback to template
        return $this->generateTemplateDescription($jobData);
    }

    private function buildJobDescriptionPrompt(array $jobData): string
    {
        $title = $jobData['title'] ?? 'Professional';
        $employmentType = $jobData['employment_type'] ?? 'full-time';
        $location = $jobData['location'] ?? '';
        $salaryMin = $jobData['salary_min'] ?? '';
        $salaryMax = $jobData['salary_max'] ?? '';
        $currency = $jobData['currency'] ?? 'INR';
        $payFrequency = $jobData['pay_frequency'] ?? 'monthly';
        $skills = !empty($jobData['skills']) ? implode(', ', $jobData['skills']) : '';
        $minExp = $jobData['min_experience'] ?? '';
        $maxExp = $jobData['max_experience'] ?? '';
        $companyName = $jobData['company_name'] ?? 'Our Company';
        $benefits = !empty($jobData['benefits']) ? implode(', ', $jobData['benefits']) : '';

        return <<<PROMPT
You are an expert HR professional and job description writer. Create a professional, comprehensive, and engaging job description in HTML format.

JOB DETAILS:
- Title: {$title}
- Employment Type: {$employmentType}
- Location: {$location}
- Salary: {$currency} {$salaryMin} - {$salaryMax} per {$payFrequency}
- Required Skills: {$skills}
- Experience Required: {$minExp} - {$maxExp} years
- Company: {$companyName}
- Benefits: {$benefits}

REQUIREMENTS:
1. Write in professional, clear, and engaging language
2. Use proper HTML formatting (h2, h3, ul, li, p tags)
3. Include these sections:
   - Company Overview (brief)
   - Job Summary
   - Key Responsibilities (5-7 bullet points)
   - Required Qualifications (skills, experience, education)
   - Preferred Qualifications (nice-to-haves)
   - Benefits & Perks
   - How to Apply
4. Make it specific to the role, not generic
5. Use industry-standard terminology
6. Keep it between 400-600 words
7. Make it attractive to qualified candidates

CRITICAL: Return ONLY valid HTML. No markdown, no code blocks, no explanations. Just the HTML content.

Return the HTML job description now:
PROMPT;
    }

    private function extractDescriptionFromResponse(string $response): string
    {
        // Try to parse JSON response
        $json = json_decode($response, true);
        if (isset($json['choices'][0]['message']['content'])) {
            return $json['choices'][0]['message']['content'];
        }
        
        // If direct HTML, return as is
        if (strpos($response, '<') !== false) {
            return $response;
        }
        
        return '';
    }

    private function generateTemplateDescription(array $jobData): string
    {
        $title = $jobData['title'] ?? 'Professional';
        $employmentType = $jobData['employment_type'] ?? 'full-time';
        $location = $jobData['location'] ?? '';
        $salaryMin = $jobData['salary_min'] ?? '';
        $salaryMax = $jobData['salary_max'] ?? '';
        $currency = $jobData['currency'] ?? 'INR';
        $payFrequency = $jobData['pay_frequency'] ?? 'monthly';
        $skills = !empty($jobData['skills']) ? $jobData['skills'] : [];
        $minExp = $jobData['min_experience'] ?? '';
        $maxExp = $jobData['max_experience'] ?? '';
        $companyName = $jobData['company_name'] ?? 'Our Company';

        $skillsList = !empty($skills) ? '<ul><li>' . implode('</li><li>', array_slice($skills, 0, 8)) . '</li></ul>' : '<ul><li>Relevant skills and experience</li></ul>';
        $expText = $minExp || $maxExp ? "{$minExp}-{$maxExp} years" : "Relevant experience";
        $salaryText = $salaryMin && $salaryMax ? "{$currency} {$salaryMin} - {$salaryMax} per {$payFrequency}" : "Competitive salary";

        return <<<HTML
<h2>About the Role</h2>
<p>We are seeking a talented and motivated <strong>{$title}</strong> to join our team. This is a <strong>{$employmentType}</strong> position based in <strong>{$location}</strong>.</p>

<h3>Job Summary</h3>
<p>As a {$title}, you will play a key role in our organization, contributing to our success through your expertise and dedication. We offer {$salaryText} and a supportive work environment.</p>

<h3>Key Responsibilities</h3>
<ul>
    <li>Execute assigned tasks with high quality and attention to detail</li>
    <li>Collaborate effectively with cross-functional teams</li>
    <li>Maintain clear and professional communication with stakeholders</li>
    <li>Contribute to process improvements and best practices</li>
    <li>Meet project deadlines and deliver results</li>
    <li>Stay updated with industry trends and technologies</li>
    <li>Support team goals and company objectives</li>
</ul>

<h3>Required Qualifications</h3>
<ul>
    <li>{$expText} of relevant experience</li>
    <li>Strong technical skills and knowledge</li>
    <li>Excellent communication and interpersonal skills</li>
    <li>Ability to work independently and as part of a team</li>
    <li>Problem-solving mindset and attention to detail</li>
</ul>

<h3>Technical Skills</h3>
{$skillsList}

<h3>What We Offer</h3>
<ul>
    <li>Competitive compensation package</li>
    <li>Professional growth and development opportunities</li>
    <li>Positive and collaborative work culture</li>
    <li>Work-life balance initiatives</li>
    <li>Comprehensive benefits package</li>
</ul>

<h3>How to Apply</h3>
<p>If you are interested in this position and meet the requirements, please submit your application through our portal. We look forward to hearing from you!</p>
HTML;
    }
}

