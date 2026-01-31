<?php

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ResumeEnhancerController
{
    public function enhance(Request $request, Response $response)
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            return $response->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->getJsonBody();
        $candidateProfile = $data['profile'] ?? [];
        $enhancementType = $data['type'] ?? 'summary'; // 'summary', 'experience', 'skills'

        if (empty($candidateProfile)) {
            return $response->json(['error' => 'Candidate profile data is missing.'], 400);
        }

        $apiKey = $_ENV['OPENAI_API_KEY'] ?? null;
        if (!$apiKey) {
            error_log("OPENAI_API_KEY is not set in the .env file.");
            return $response->json(['error' => 'AI service is not configured.'], 500);
        }

        $client = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);

        $prompt = $this->buildPrompt($candidateProfile, $enhancementType);

        try {
            $apiResponse = $client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'temperature' => 0.5,
                ],
            ]);

            $body = $apiResponse->getBody()->getContents();
            $result = json_decode($body, true);

            if (isset($result['choices'][0]['message']['content'])) {
                $jsonResponse = json_decode($result['choices'][0]['message']['content'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $response->json($jsonResponse);
                }
            }
            
            return $response->json(['error' => 'Failed to parse AI response.'], 500);

        } catch (RequestException $e) {
            error_log('AI API Request Error: ' . $e->getMessage());
            return $response->json(['error' => 'An error occurred while communicating with the AI service.'], 500);
        }
    }

    private function buildPrompt(array $profile, string $type): string
    {
        $profileJson = json_encode($profile, JSON_PRETTY_PRINT);

        return <<<PROMPT
You are an advanced AI Resume Assistant for a job portal.

Your task is to ENHANCE (not replace) a candidate’s resume content to improve hiring chances.

STRICT RULES:
- DO NOT delete or overwrite existing content.
- DO NOT add fake experience or false claims.
- ONLY improve language, clarity, professionalism, and impact.
- Fix English grammar, spelling, and sentence structure.
- Use ATS-friendly keywords.
- Keep content concise and role-specific.

STEP 1: ANALYZE PROFILE
Analyze the candidate profile below and identify:
- Primary job role (e.g., Software Developer, Sales Executive, Marketing, HR, Accountant, etc.)
- Experience level (Fresher / Mid / Senior)
- Industry relevance

STEP 2: ENHANCE EXISTING CONTENT
Enhance the '{$type}' section while preserving original meaning:
- Job Description
- Professional Summary
- Skills

Improvements should include:
- Action verbs
- Measurable impact (if possible)
- Professional tone
- Industry keywords

STEP 3: IDENTIFY MISSING ELEMENTS
Based on the detected job role:
- Suggest MISSING skills separately (do not auto-add)
- Suggest MISSING responsibilities separately
- Suggest optional certifications/tools (if relevant)

STEP 4: OUTPUT FORMAT (IMPORTANT)
Return response in VALID JSON ONLY:

{
  "detected_role": "",
  "experience_level": "",
  "enhanced_summary": "",
  "enhanced_experience_description": "",
  "enhanced_skills": [],
  "missing_skills_suggestions": [],
  "missing_responsibilities_suggestions": [],
  "english_corrections_applied": true
}

TONE:
- Professional
- Clear
- Employer-attractive
- Simple English (India-friendly)

Candidate Data:
{$profileJson}
PROMPT;
    }
}
