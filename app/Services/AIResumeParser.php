<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * AI Resume Parser Service
 * 
 * Uses OpenAI API to parse resume text and extract structured data.
 * Stores results in candidates table JSON columns.
 */
class AIResumeParser
{
    private ResumeTextExtractor $extractor;
    private string $apiKey;
    private string $apiUrl;
    private string $model;
    private int $maxRetries;

    public function __construct(
        ResumeTextExtractor $extractor,
        string $apiKey = null,
        string $model = 'gpt-4o-mini'
    ) {
        $this->extractor = $extractor;
        $this->apiKey = $apiKey ?? $_ENV['OPENAI_API_KEY'] ?? '';
        $this->model = $model;
        $this->apiUrl = 'https://api.openai.com/v1/chat/completions';
        $this->maxRetries = 3;

        if (empty($this->apiKey)) {
            throw new \RuntimeException("OpenAI API key not configured. Set OPENAI_API_KEY in .env");
        }
    }

    /**
     * Parse resume file and return structured data
     * 
     * @param string $resumePath Full path to resume file
     * @return array Parsed data with keys: skills, education, experience, languages, summary_profile
     * @throws \RuntimeException If parsing fails
     */
    public function parseResume(string $resumePath): array
    {
        // Extract text from resume
        $resumeText = $this->extractor->extractResumeText($resumePath);

        // Build prompt
        $prompt = $this->buildParsingPrompt($resumeText);

        // Call OpenAI API
        $response = $this->callOpenAI($prompt);

        // Validate and return parsed data
        return $this->validateParsedData($response);
    }

    /**
     * Build prompt for resume parsing
     * 
     * @param string $resumeText Extracted resume text
     * @return string Complete prompt
     */
    private function buildParsingPrompt(string $resumeText): string
    {
        return <<<PROMPT
You are an expert resume parser. Extract structured information from the following resume text.

RESUME TEXT:
{$resumeText}

INSTRUCTIONS:
1. Extract all skills mentioned (programming languages, tools, frameworks, soft skills).
2. Extract education history (degree, field, institution, years).
3. Extract work experience (job title, company, location, dates, responsibilities).
4. Extract languages and proficiency levels.
5. Create a brief professional summary (max 3 lines).

CRITICAL: Return ONLY valid JSON. No markdown, no code blocks, no explanations. Just the raw JSON object.

REQUIRED JSON FORMAT:
{
  "skills": ["skill1", "skill2", "skill3"],
  "education": [
    {
      "degree": "Bachelor of Science",
      "field_of_study": "Computer Science",
      "institution": "University Name",
      "start_year": 2015,
      "end_year": 2019,
      "is_current": false
    }
  ],
  "experience": [
    {
      "job_title": "Software Developer",
      "company_name": "Company Name",
      "location": "City, State",
      "start_date": "2020-01-01",
      "end_date": "2022-12-31",
      "is_current": false,
      "summary": "Brief description of role and achievements"
    }
  ],
  "languages": [
    {
      "name": "English",
      "proficiency": "fluent"
    }
  ],
  "summary_profile": "Professional summary in 2-3 lines"
}

Return the JSON now:
PROMPT;
    }

    /**
     * Call OpenAI API with retry logic
     * 
     * @param string $prompt User prompt
     * @param float $temperature Temperature (0.0-2.0)
     * @return array Decoded JSON response
     * @throws \RuntimeException If API call fails
     */
    public function callOpenAI(string $prompt, float $temperature = 0.1): array
    {
        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a precise data extraction assistant. Always return valid JSON only, no markdown, no explanations.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => $temperature,
            'max_tokens' => 2000,
            'response_format' => ['type' => 'json_object'] // Force JSON mode
        ];

        $attempt = 0;
        $lastError = null;

        while ($attempt < $this->maxRetries) {
            try {
                $response = $this->makeApiRequest($payload);
                
                // Extract JSON from response
                $content = $response['choices'][0]['message']['content'] ?? '';
                
                if (empty($content)) {
                    throw new \RuntimeException("Empty response from OpenAI API");
                }

                // Clean JSON (remove markdown code blocks if any)
                $content = preg_replace('/^```json\s*/', '', $content);
                $content = preg_replace('/\s*```$/', '', $content);
                $content = trim($content);

                $decoded = json_decode($content, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("OpenAI JSON decode error: " . json_last_error_msg());
                    error_log("Response content: " . substr($content, 0, 500));
                    throw new \RuntimeException("Invalid JSON response from OpenAI: " . json_last_error_msg());
                }

                return $decoded;

            } catch (\Exception $e) {
                $lastError = $e;
                $attempt++;
                
                if ($attempt < $this->maxRetries) {
                    $delay = pow(2, $attempt); // Exponential backoff: 2s, 4s, 8s
                    error_log("OpenAI API attempt {$attempt} failed, retrying in {$delay}s: " . $e->getMessage());
                    sleep($delay);
                }
            }
        }

        throw new \RuntimeException(
            "OpenAI API call failed after {$this->maxRetries} attempts: " . 
            ($lastError ? $lastError->getMessage() : 'Unknown error')
        );
    }

    /**
     * Make HTTP request to OpenAI API
     * 
     * @param array $payload Request payload
     * @return array Decoded JSON response
     * @throws \RuntimeException If request fails
     */
    private function makeApiRequest(array $payload): array
    {
        $ch = curl_init($this->apiUrl);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ];
        $caPath = $this->getCaBundlePath();
        if (is_string($caPath) && $caPath !== '') {
            $opts[CURLOPT_CAINFO] = $caPath;
        }
        curl_setopt_array($ch, $opts);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("cURL error: {$error}");
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? "HTTP {$httpCode}";
            throw new \RuntimeException("OpenAI API error: {$errorMessage}");
        }

        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Failed to decode OpenAI response: " . json_last_error_msg());
        }

        return $decoded;
    }

    private function getCaBundlePath(): string|bool
    {
        $envPath = $_ENV['CA_BUNDLE_PATH'] ?? getenv('CA_BUNDLE_PATH') ?: null;
        $possiblePaths = [
            $envPath,
            __DIR__ . '/../../vendor/guzzlehttp/guzzle/src/cacert.pem',
            'E:/xampp/php/extras/ssl/cacert.pem',
            'C:/xampp/php/extras/ssl/cacert.pem',
            'E:/xampp/apache/bin/curl-ca-bundle.crt',
            'C:/xampp/apache/bin/curl-ca-bundle.crt',
            ini_get('curl.cainfo'),
            ini_get('openssl.cafile'),
        ];
        foreach ($possiblePaths as $path) {
            if ($path && file_exists((string)$path)) {
                return (string)$path;
            }
        }
        return true;
    }

    /**
     * Validate and normalize parsed data
     * 
     * @param array $data Raw parsed data
     * @return array Validated and normalized data
     */
    private function validateParsedData(array $data): array
    {
        $validated = [
            'skills' => [],
            'education' => [],
            'experience' => [],
            'languages' => [],
            'summary_profile' => ''
        ];

        // Validate skills
        if (isset($data['skills']) && is_array($data['skills'])) {
            $validated['skills'] = array_filter(
                array_map('trim', $data['skills']),
                fn($s) => !empty($s)
            );
        }

        // Validate education
        if (isset($data['education']) && is_array($data['education'])) {
            foreach ($data['education'] as $edu) {
                if (is_array($edu) && !empty($edu['degree'])) {
                    $validated['education'][] = [
                        'degree' => $edu['degree'] ?? '',
                        'field_of_study' => $edu['field_of_study'] ?? '',
                        'institution' => $edu['institution'] ?? '',
                        'start_year' => (int)($edu['start_year'] ?? 0),
                        'end_year' => (int)($edu['end_year'] ?? 0),
                        'is_current' => (bool)($edu['is_current'] ?? false)
                    ];
                }
            }
        }

        // Validate experience
        if (isset($data['experience']) && is_array($data['experience'])) {
            foreach ($data['experience'] as $exp) {
                if (is_array($exp) && !empty($exp['job_title'])) {
                    $validated['experience'][] = [
                        'job_title' => $exp['job_title'] ?? '',
                        'company_name' => $exp['company_name'] ?? '',
                        'location' => $exp['location'] ?? '',
                        'start_date' => $exp['start_date'] ?? '',
                        'end_date' => $exp['end_date'] ?? null,
                        'is_current' => (bool)($exp['is_current'] ?? false),
                        'summary' => $exp['summary'] ?? ''
                    ];
                }
            }
        }

        // Validate languages
        if (isset($data['languages']) && is_array($data['languages'])) {
            foreach ($data['languages'] as $lang) {
                if (is_array($lang) && !empty($lang['name'])) {
                    $validated['languages'][] = [
                        'name' => $lang['name'] ?? '',
                        'proficiency' => $this->normalizeProficiency($lang['proficiency'] ?? 'intermediate')
                    ];
                }
            }
        }

        // Validate summary
        if (isset($data['summary_profile']) && is_string($data['summary_profile'])) {
            $validated['summary_profile'] = trim($data['summary_profile']);
        }

        return $validated;
    }

    /**
     * Normalize language proficiency level
     * 
     * @param string $proficiency Raw proficiency string
     * @return string Normalized proficiency
     */
    private function normalizeProficiency(string $proficiency): string
    {
        $proficiency = strtolower(trim($proficiency));
        
        $mapping = [
            'basic' => 'basic',
            'beginner' => 'basic',
            'intermediate' => 'intermediate',
            'advanced' => 'fluent',
            'fluent' => 'fluent',
            'native' => 'native',
            'proficient' => 'fluent'
        ];

        return $mapping[$proficiency] ?? 'intermediate';
    }
}

