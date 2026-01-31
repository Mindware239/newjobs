<?php

declare(strict_types=1);

namespace App\Services;

/**
 * AI-Powered Resume Content Generation Service
 * Uses OpenAI API or similar AI service to generate and enhance resume content
 */
class ResumeAIService
{
    private ?string $apiKey;
    private string $apiUrl = 'https://api.openai.com/v1/chat/completions';
    
    public function __construct()
    {
        // Load environment variables if not already loaded
        if (empty($_ENV['OPENAI_API_KEY'])) {
            $envFile = dirname(__DIR__, 2) . '/.env';
            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos(trim($line), '#') === 0) continue;
                    if (strpos($line, '=') !== false) {
                        list($key, $value) = explode('=', $line, 2);
                        $_ENV[trim($key)] = trim($value, '"\'');
                    }
                }
            }
        }
        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? null;
        if ($this->apiKey) {
            $this->apiKey = trim($this->apiKey);
        }
    }
    
    /**
     * Generate professional summary using AI
     */
    public function generateSummary(array $experience, array $education, array $skills, string $jobTitle = ''): ?string
    {
        if (!$this->apiKey) {
            error_log("OpenAI API Key not found. Please add OPENAI_API_KEY to .env file.");
            return null;
        }
        
        $prompt = $this->buildSummaryPrompt($experience, $education, $skills, $jobTitle);
        
        try {
            $response = $this->callAI($prompt, 250); // Increased tokens for more comprehensive content
            return $response ? trim($response) : null;
        } catch (\Exception $e) {
            error_log("AI Summary Generation Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Enhance job description using AI
     */
    public function enhanceJobDescription(string $jobTitle, string $company, string $description, array $skills = []): ?string
    {
        if (!$this->apiKey) {
            return null;
        }
        
        $prompt = "Rewrite and enhance the following job experience description to be more professional, impactful, and ATS-friendly. Use strong action verbs, quantify achievements with numbers/percentages/dollar amounts where possible, and highlight specific accomplishments.\n\n";
        $prompt .= "Job Title: {$jobTitle}\n";
        $prompt .= "Company: {$company}\n";
        $prompt .= "Current Description: {$description}\n";
        if (!empty($skills)) {
            $prompt .= "Relevant Skills: " . implode(', ', $skills) . "\n";
        }
        $prompt .= "\nProvide a comprehensive, detailed professional description with 5-7 bullet points. Each bullet should:\n";
        $prompt .= "- Start with a strong action verb (Developed, Implemented, Managed, Led, etc.)\n";
        $prompt .= "- Include specific metrics, numbers, or quantifiable results\n";
        $prompt .= "- Highlight technical skills and tools used\n";
        $prompt .= "- Show impact and value delivered\n";
        $prompt .= "- Be ATS-friendly and keyword-rich\n\n";
        $prompt .= "Enhanced description:";
        
        try {
            $response = $this->callAI($prompt, 400);
            return $response ? trim($response) : null;
        } catch (\Exception $e) {
            error_log("AI Job Description Enhancement Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate skills suggestions based on experience
     */
    public function suggestSkills(array $experience, array $education, string $jobTitle = ''): array
    {
        if (!$this->apiKey) {
            return [];
        }
        
        $prompt = "Based on the following professional experience and education, suggest 10-15 relevant technical and soft skills for a resume. Return only a comma-separated list of skills.\n\n";
        $prompt .= "Job Title: {$jobTitle}\n";
        $prompt .= "Experience: " . json_encode($experience) . "\n";
        $prompt .= "Education: " . json_encode($education) . "\n";
        $prompt .= "\nSkills (comma-separated):";
        
        try {
            $response = $this->callAI($prompt, 100);
            if ($response) {
                $skills = array_map('trim', explode(',', $response));
                return array_filter($skills);
            }
        } catch (\Exception $e) {
            error_log("AI Skills Suggestion Error: " . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * Generate job summary based on candidate profile
     */
    public function generateJobSummary(array $candidateProfile, string $targetJobRole = ''): ?string
    {
        $prompt = "Write a comprehensive, professional resume summary (3-4 sentences, 100-200 words) for a candidate based on their complete profile. Make it dynamic, detailed, and impactful.\n\n";
        $prompt .= "Candidate Profile:\n";
        
        if (!empty($candidateProfile['full_name'])) {
            $prompt .= "Name: {$candidateProfile['full_name']}\n";
        }
        if (!empty($candidateProfile['experience'])) {
            $prompt .= "Experience Details:\n";
            foreach ($candidateProfile['experience'] as $idx => $exp) {
                $prompt .= "  " . ($idx + 1) . ". {$exp['job_title']} at {$exp['company_name']}";
                if (!empty($exp['start_date'])) {
                    $start = date('Y', strtotime($exp['start_date']));
                    $end = !empty($exp['end_date']) ? date('Y', strtotime($exp['end_date'])) : 'Present';
                    $prompt .= " ({$start} - {$end})";
                }
                $prompt .= "\n";
                if (!empty($exp['description'])) {
                    $prompt .= "     Description: {$exp['description']}\n";
                }
            }
        }
        if (!empty($candidateProfile['education'])) {
            $prompt .= "Education:\n";
            foreach ($candidateProfile['education'] as $edu) {
                $prompt .= "  - {$edu['degree']} in {$edu['field_of_study']} from {$edu['institution']}\n";
            }
        }
        if (!empty($candidateProfile['skills'])) {
            $prompt .= "Key Skills: " . implode(', ', array_slice($candidateProfile['skills'], 0, 15)) . "\n";
        }
        if (!empty($targetJobRole)) {
            $prompt .= "Target Job Role: {$targetJobRole}\n";
        }
        
        $prompt .= "\nWrite a compelling, detailed professional summary that:\n";
        $prompt .= "- Highlights years of experience and key expertise areas\n";
        $prompt .= "- Mentions specific technical skills and technologies\n";
        $prompt .= "- Includes quantifiable achievements if available\n";
        $prompt .= "- Shows value proposition and career focus\n";
        $prompt .= "- Is tailored to the target job role\n";
        $prompt .= "- Uses industry-relevant keywords for ATS optimization\n";
        $prompt .= "- Is professional yet engaging\n\n";
        $prompt .= "Professional Summary:";
        
        try {
            $response = $this->callAI($prompt, 250);
            return $response ? trim($response) : null;
        } catch (\Exception $e) {
            error_log("AI Job Summary Generation Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate experience description based on candidate profile and job role
     */
    public function generateExperienceDescription(array $candidateProfile, string $jobTitle, string $company = '', string $targetJobRole = ''): ?string
    {
        $prompt = "Write a comprehensive, detailed job experience description (6-8 bullet points) for a resume based on the candidate's complete profile. Make it dynamic, specific, and impactful.\n\n";
        $prompt .= "Job Title: {$jobTitle}\n";
        if (!empty($company)) {
            $prompt .= "Company: {$company}\n";
        }
        if (!empty($targetJobRole)) {
            $prompt .= "Target Job Role: {$targetJobRole}\n";
        }
        
        $prompt .= "Candidate Profile:\n";
        if (!empty($candidateProfile['skills'])) {
            $prompt .= "Technical Skills: " . implode(', ', array_slice($candidateProfile['skills'], 0, 20)) . "\n";
        }
        if (!empty($candidateProfile['education'])) {
            $prompt .= "Education Background:\n";
            foreach ($candidateProfile['education'] as $edu) {
                $prompt .= "  - {$edu['degree']} in {$edu['field_of_study']} from {$edu['institution']}\n";
            }
        }
        if (!empty($candidateProfile['experience'])) {
            $prompt .= "Previous Experience:\n";
            foreach (array_slice($candidateProfile['experience'], 0, 3) as $exp) {
                if (!empty($exp['job_title'])) {
                    $prompt .= "  - {$exp['job_title']}";
                    if (!empty($exp['description'])) {
                        $prompt .= ": {$exp['description']}";
                    }
                    $prompt .= "\n";
                }
            }
        }
        
        $prompt .= "\nWrite comprehensive, detailed professional bullet points (6-8 points) describing responsibilities and achievements for this {$jobTitle} role. Each bullet should:\n";
        $prompt .= "- Start with a strong action verb (Developed, Implemented, Led, Managed, Optimized, etc.)\n";
        $prompt .= "- Include specific metrics, numbers, percentages, dollar amounts, or timeframes\n";
        $prompt .= "- Mention specific technologies, tools, or methodologies used\n";
        $prompt .= "- Highlight measurable impact and results achieved\n";
        $prompt .= "- Show progression and growth in responsibilities\n";
        $prompt .= "- Be tailored to the job title and industry\n";
        $prompt .= "- Use industry-standard terminology and keywords\n";
        $prompt .= "- Be ATS-friendly and keyword-rich\n\n";
        $prompt .= "Make the description comprehensive and dynamic, not generic. Include specific details that would make this candidate stand out:\n\n";
        $prompt .= "Professional Experience Description:";
        
        try {
            $response = $this->callAI($prompt, 500);
            return $response ? trim($response) : null;
        } catch (\Exception $e) {
            error_log("AI Experience Description Generation Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate section description based on candidate profile
     */
    public function generateSectionDescription(string $sectionType, array $candidateProfile, array $sectionData = []): ?string
    {
        $prompt = "Write comprehensive, detailed professional content for a resume {$sectionType} section based on the candidate's complete profile. Make it dynamic, specific, and impactful.\n\n";
        
        $prompt .= "Candidate Profile:\n";
        if (!empty($candidateProfile['full_name'])) {
            $prompt .= "Name: {$candidateProfile['full_name']}\n";
        }
        if (!empty($candidateProfile['experience'])) {
            $prompt .= "Professional Experience:\n";
            foreach ($candidateProfile['experience'] as $idx => $exp) {
                $prompt .= "  " . ($idx + 1) . ". {$exp['job_title']} at {$exp['company_name']}";
                if (!empty($exp['description'])) {
                    $prompt .= " - {$exp['description']}";
                }
                $prompt .= "\n";
            }
        }
        if (!empty($candidateProfile['education'])) {
            $prompt .= "Education:\n";
            foreach ($candidateProfile['education'] as $edu) {
                $prompt .= "  - {$edu['degree']} in {$edu['field_of_study']} from {$edu['institution']}\n";
            }
        }
        if (!empty($candidateProfile['skills'])) {
            $prompt .= "Skills: " . implode(', ', array_slice($candidateProfile['skills'], 0, 20)) . "\n";
        }
        
        if (!empty($sectionData)) {
            $prompt .= "\nAdditional Section Data: " . json_encode($sectionData) . "\n";
        }
        
        $sectionPrompts = [
            'summary' => "Write a comprehensive professional summary (3-4 sentences, 100-200 words) that:\n- Highlights years of experience and expertise\n- Mentions specific technical skills and technologies\n- Includes quantifiable achievements\n- Shows value proposition\n- Uses industry keywords for ATS optimization",
            'experience' => "Write detailed professional bullet points (6-8 points) with:\n- Strong action verbs\n- Specific metrics and numbers\n- Technologies and tools mentioned\n- Measurable impact and results\n- Industry-specific terminology",
            'education' => "Write a detailed description of educational achievements including:\n- Relevant coursework\n- Academic honors or achievements\n- Projects or research\n- GPA if notable\n- Relevant certifications",
            'skills' => "List and describe relevant skills comprehensively, including:\n- Technical skills with proficiency levels\n- Soft skills\n- Industry-specific tools\n- Certifications\n- Languages",
            'additional' => "Write comprehensive descriptions of:\n- Projects with specific details and outcomes\n- Certifications with dates and relevance\n- Awards and achievements\n- Publications or presentations\n- Volunteer work or leadership roles"
        ];
        
        $prompt .= "\n" . ($sectionPrompts[$sectionType] ?? "Write comprehensive, detailed professional content for this section.");
        $prompt .= "\n\nMake the content dynamic, specific, and not generic. Include concrete details that showcase the candidate's expertise:";
        
        try {
            $maxTokens = $sectionType === 'summary' ? 250 : ($sectionType === 'experience' ? 500 : 300);
            $response = $this->callAI($prompt, $maxTokens);
            return $response ? trim($response) : null;
        } catch (\Exception $e) {
            error_log("AI Section Description Generation Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Suggest skills based on job role and candidate profile
     */
    public function suggestSkillsByJobRole(string $jobRole, array $candidateProfile = []): array
    {
        $prompt = "Based on the job role and candidate profile, suggest 15-20 comprehensive, relevant technical and soft skills for a resume. Include industry-specific skills, tools, technologies, and methodologies.\n\n";
        $prompt .= "Job Role: {$jobRole}\n";
        
        if (!empty($candidateProfile['experience'])) {
            $prompt .= "Candidate Experience:\n";
            foreach (array_slice($candidateProfile['experience'], 0, 3) as $exp) {
                $prompt .= "  - {$exp['job_title']}";
                if (!empty($exp['description'])) {
                    $prompt .= ": {$exp['description']}";
                }
                $prompt .= "\n";
            }
        }
        if (!empty($candidateProfile['education'])) {
            $prompt .= "Education:\n";
            foreach ($candidateProfile['education'] as $edu) {
                $prompt .= "  - {$edu['degree']} in {$edu['field_of_study']}\n";
            }
        }
        if (!empty($candidateProfile['skills'])) {
            $prompt .= "Current Skills: " . implode(', ', array_slice($candidateProfile['skills'], 0, 10)) . "\n";
        }
        
        $prompt .= "\nSuggest comprehensive, relevant skills including:\n";
        $prompt .= "- Technical skills specific to this role\n";
        $prompt .= "- Programming languages, frameworks, tools\n";
        $prompt .= "- Software and platforms\n";
        $prompt .= "- Methodologies and practices\n";
        $prompt .= "- Soft skills relevant to the role\n";
        $prompt .= "- Industry-specific competencies\n\n";
        $prompt .= "Return only a comma-separated list of skills (15-20 skills):";
        
        try {
            $response = $this->callAI($prompt, 200);
            if ($response) {
                // Handle various formats (comma-separated, bullet points, etc.)
                $response = str_replace(['•', '-', '*', "\n"], ',', $response);
                $skills = array_map('trim', explode(',', $response));
                $skills = array_filter($skills, function($skill) {
                    return !empty($skill) && strlen($skill) > 1;
                });
                return array_slice($skills, 0, 20); // Limit to 20 skills
            }
        } catch (\Exception $e) {
            error_log("AI Skills Suggestion by Job Role Error: " . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * Summarize and optimize resume content
     */
    public function optimizeContent(string $content, string $sectionType, int $maxLength = 500): ?string
    {
        if (!$this->apiKey) {
            return null;
        }
        
        $prompt = "Optimize and summarize the following resume {$sectionType} content. Make it concise, professional, and impactful. Maximum {$maxLength} characters.\n\n";
        $prompt .= "Content: {$content}\n\n";
        $prompt .= "Optimized version:";
        
        try {
            $response = $this->callAI($prompt, $maxLength);
            return $response ? trim($response) : null;
        } catch (\Exception $e) {
            error_log("AI Content Optimization Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Build prompt for summary generation
     */
    private function buildSummaryPrompt(array $experience, array $education, array $skills, string $jobTitle): string
    {
        $prompt = "Write a comprehensive, detailed professional resume summary (3-4 sentences, 100-200 words) for a candidate with the following background. Make it dynamic, specific, and impactful.\n\n";
        
        if (!empty($jobTitle)) {
            $prompt .= "Target Role: {$jobTitle}\n";
        }
        
        if (!empty($experience)) {
            $prompt .= "Professional Experience:\n";
            foreach (array_slice($experience, 0, 3) as $idx => $exp) {
                $prompt .= "  " . ($idx + 1) . ". {$exp['job_title']} at {$exp['company_name']}";
                if (!empty($exp['start_date'])) {
                    $start = date('Y', strtotime($exp['start_date']));
                    $end = !empty($exp['end_date']) ? date('Y', strtotime($exp['end_date'])) : 'Present';
                    $prompt .= " ({$start} - {$end})";
                }
                $prompt .= "\n";
                if (!empty($exp['description'])) {
                    $prompt .= "     Key Responsibilities: {$exp['description']}\n";
                }
            }
        }
        
        if (!empty($education)) {
            $prompt .= "Education:\n";
            foreach (array_slice($education, 0, 2) as $edu) {
                $prompt .= "  - {$edu['degree']} in {$edu['field_of_study']} from {$edu['institution']}\n";
            }
        }
        
        if (!empty($skills)) {
            $skillList = array_slice($skills, 0, 15);
            $prompt .= "Key Skills: " . implode(', ', $skillList) . "\n";
        }
        
        $prompt .= "\nWrite a compelling, comprehensive professional summary that:\n";
        $prompt .= "- Highlights years of experience and key expertise areas\n";
        $prompt .= "- Mentions specific technical skills, technologies, and tools\n";
        $prompt .= "- Includes quantifiable achievements and impact\n";
        $prompt .= "- Shows unique value proposition\n";
        $prompt .= "- Is tailored to the target job role\n";
        $prompt .= "- Uses industry-relevant keywords for ATS optimization\n";
        $prompt .= "- Is professional yet engaging\n";
        $prompt .= "- Avoids generic phrases - be specific and detailed\n\n";
        $prompt .= "Professional Summary:";
        
        return $prompt;
    }
    
    /**
     * Call OpenAI API
     */
    private function callAI(string $prompt, int $maxTokens = 200): ?string
    {
        if (!$this->apiKey || empty(trim($this->apiKey))) {
            error_log("OpenAI API Error: API key not configured");
            return null;
        }
        
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert resume writing assistant specializing in creating comprehensive, detailed, and impactful resume content. Always provide thorough, dynamic content with specific details, metrics, and achievements. Never provide generic or placeholder content. Make every description detailed and professional.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => $maxTokens,
            'temperature' => 0.8 // Slightly higher for more creative/detailed content
        ];
        
        $ch = curl_init($this->apiUrl);
        if ($ch === false) {
            error_log("OpenAI API Error: Failed to initialize cURL");
            return null;
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Increased timeout for comprehensive content
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors (HTTP 0 usually means connection failed)
        if ($response === false || !empty($curlError)) {
            error_log("OpenAI API Error: cURL error - {$curlError}. Check API key in .env file: OPENAI_API_KEY");
            return null;
        }
        
        if ($httpCode === 0) {
            error_log("OpenAI API Error: HTTP 0 - Connection failed. Verify OPENAI_API_KEY in .env file and network connection.");
            return null;
        }
        
        if ($httpCode === 401) {
            error_log("OpenAI API Error: HTTP 401 - Invalid API key. Check OPENAI_API_KEY in .env file.");
            return null;
        }
        
        if ($httpCode !== 200) {
            $errorMsg = substr($response, 0, 500); // Limit error message length
            error_log("OpenAI API Error: HTTP {$httpCode} - {$errorMsg}");
            return null;
        }
        
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("OpenAI API Error: Invalid JSON response - " . json_last_error_msg());
            return null;
        }
        
        $content = $result['choices'][0]['message']['content'] ?? null;
        if ($content) {
            // Clean up the response - remove any markdown formatting if present
            $content = preg_replace('/^```[\w]*\n?/', '', $content);
            $content = preg_replace('/\n?```$/', '', $content);
            $content = trim($content);
        }
        
        return $content;
    }
    
    /**
     * Fallback: Generate basic summary without AI (if API key not available)
     */
    public function generateBasicSummary(array $experience, array $education, array $skills): string
    {
        $years = 0;
        if (!empty($experience)) {
            foreach ($experience as $exp) {
                if (!empty($exp['start_date'])) {
                    $start = strtotime($exp['start_date']);
                    $end = !empty($exp['end_date']) ? strtotime($exp['end_date']) : time();
                    $years += ($end - $start) / (365 * 24 * 3600);
                }
            }
        }
        
        $years = round($years);
        $topSkills = array_slice($skills, 0, 5);
        $latestJob = $experience[0] ?? null;
        
        $summary = "Experienced professional";
        if ($years > 0) {
            $summary .= " with {$years} years of experience";
        }
        if ($latestJob) {
            $summary .= " in " . ($latestJob['job_title'] ?? '');
        }
        if (!empty($topSkills)) {
            $summary .= ". Proficient in " . implode(', ', $topSkills);
        }
        $summary .= ". Seeking opportunities to leverage expertise and drive results.";
        
        return $summary;
    }
}
