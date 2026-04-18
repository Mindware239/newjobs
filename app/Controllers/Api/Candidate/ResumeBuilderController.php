<?php

declare(strict_types=1);

namespace App\Controllers\Api\Candidate;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Candidate;
use App\Models\Resume;
use App\Services\ResumeAIService;

class ResumeBuilderController extends ApiController
{
    private function getCandidateAndResume(Request $request, Response $response, string $resumeId): ?array
    {
        $user = $this->user($request);
        if (!$user || $user->role !== 'candidate') {
            $this->error($response, 'Unauthorized', 401);
            return null;
        }

        $candidate = Candidate::findByUserId((int)$user->id);
        if (!$candidate) {
            $this->error($response, 'Candidate not found', 404);
            return null;
        }

        $resume = Resume::find((int)$resumeId);
        if (!$resume || (int)$resume->attributes['candidate_id'] !== (int)$candidate->attributes['id']) {
            $this->error($response, 'Resume not found', 404);
            return null;
        }

        return [$candidate, $resume];
    }

    private function extractProfileFromResume(Resume $resume, ?Candidate $candidate): array
    {
        $sections = $resume->getSectionsArray();
        $experience = [];
        $education = [];
        $skills = [];
        $jobTitle = '';

        foreach ($sections as $section) {
            $type = $section['section_type'] ?? '';
            $content = $section['section_data']['content'] ?? [];
            
            if ($type === 'experience') {
                $experience = $content['items'] ?? [];
                if (!empty($experience)) {
                    $jobTitle = $experience[0]['job_title'] ?? '';
                }
            } elseif ($type === 'education') {
                $education = $content['items'] ?? [];
            } elseif ($type === 'skills') {
                $skills = array_column($content['items'] ?? [], 'name');
            }
        }

        return [
            'experience' => $experience,
            'education' => $education,
            'skills' => $skills,
            'jobTitle' => $jobTitle,
            'candidateProfile' => [
                'full_name' => $candidate->attributes['full_name'] ?? '',
                'experience' => $experience,
                'education' => $education,
                'skills' => $skills,
                'self_introduction' => $candidate->attributes['self_introduction'] ?? ''
            ]
        ];
    }

    public function aiGenerateSummary(Request $request, Response $response, string $resumeId): void
    {
        $data = $this->getCandidateAndResume($request, $response, $resumeId);
        if (!$data) return;
        [$candidate, $resume] = $data;

        $profile = $this->extractProfileFromResume($resume, $candidate);

        try {
            $aiService = new ResumeAIService();
            $summary = $aiService->generateSummary($profile['experience'], $profile['education'], $profile['skills'], $profile['jobTitle']);
            
            if (!$summary) {
                $summary = $aiService->generateBasicSummary($profile['experience'], $profile['education'], $profile['skills']);
            }

            $this->success($response, ['summary' => $summary], 'Summary generated successfully');
        } catch (\Exception $e) {
            $aiService = new ResumeAIService();
            $summary = $aiService->generateBasicSummary($profile['experience'], $profile['education'], $profile['skills']);
            $this->success($response, ['summary' => $summary, 'note' => 'Using basic summary'], 'Generated successfully');
        }
    }

    public function aiGenerateJobSummary(Request $request, Response $response, string $resumeId): void
    {
        $data = $this->getCandidateAndResume($request, $response, $resumeId);
        if (!$data) return;
        [$candidate, $resume] = $data;

        $body = $request->getJsonBody();
        $targetJobRole = $body['target_job_role'] ?? '';
        $profile = $this->extractProfileFromResume($resume, $candidate);

        try {
            $aiService = new ResumeAIService();
            $summary = $aiService->generateJobSummary($profile['candidateProfile'], $targetJobRole);
            
            if (!$summary) {
                $summary = $aiService->generateBasicSummary($profile['experience'], $profile['education'], $profile['skills']);
            }

            $this->success($response, ['summary' => $summary], 'Job summary generated successfully');
        } catch (\Exception $e) {
            $aiService = new ResumeAIService();
            $summary = $aiService->generateBasicSummary($profile['experience'], $profile['education'], $profile['skills']);
            $this->success($response, ['summary' => $summary, 'note' => 'Using basic summary'], 'Generated successfully');
        }
    }

    public function aiGenerateExperience(Request $request, Response $response, string $resumeId): void
    {
        $data = $this->getCandidateAndResume($request, $response, $resumeId);
        if (!$data) return;
        [$candidate, $resume] = $data;

        $body = $request->getJsonBody();
        $jobTitle = $body['job_title'] ?? '';
        $company = $body['company'] ?? '';
        $targetJobRole = $body['target_job_role'] ?? '';

        if (empty($jobTitle)) {
            $this->error($response, 'Job title is required', 400);
            return;
        }

        $profile = $this->extractProfileFromResume($resume, $candidate);

        try {
            $aiService = new ResumeAIService();
            $description = $aiService->generateExperienceDescription($profile['candidateProfile'], $jobTitle, $company, $targetJobRole);

            if (!$description) {
                $description = "• Developed and implemented {$jobTitle} solutions\n• Collaborated with cross-functional teams";
            }

            $this->success($response, ['description' => $description], 'Experience generated successfully');
        } catch (\Exception $e) {
            $description = "• Developed and implemented {$jobTitle} solutions\n• Collaborated with cross-functional teams";
            $this->success($response, ['description' => $description], 'Experience generated successfully');
        }
    }

    public function aiGenerateSection(Request $request, Response $response, string $resumeId): void
    {
        $data = $this->getCandidateAndResume($request, $response, $resumeId);
        if (!$data) return;
        [$candidate, $resume] = $data;

        $body = $request->getJsonBody();
        $sectionType = $body['section_type'] ?? '';
        $sectionData = $body['section_data'] ?? [];

        if (empty($sectionType)) {
            $this->error($response, 'Section type is required', 400);
            return;
        }

        $profile = $this->extractProfileFromResume($resume, $candidate);

        try {
            $aiService = new ResumeAIService();
            $content = $aiService->generateSectionDescription($sectionType, $profile['candidateProfile'], $sectionData);
            
            $this->success($response, ['content' => $content ?: ''], 'Section generated successfully');
        } catch (\Exception $e) {
            $this->success($response, ['content' => ''], 'Generated successfully');
        }
    }

    public function aiEnhanceDescription(Request $request, Response $response, string $resumeId): void
    {
        $data = $this->getCandidateAndResume($request, $response, $resumeId);
        if (!$data) return;
        [$candidate, $resume] = $data;

        $body = $request->getJsonBody();
        $jobTitle = $body['job_title'] ?? '';
        $company = $body['company'] ?? '';
        $description = $body['text'] ?? '';
        $skills = $body['skills'] ?? [];

        if (empty($description)) {
            $this->error($response, 'Text description is required', 400);
            return;
        }

        try {
            $aiService = new ResumeAIService();
            $enhancedText = $aiService->enhanceJobDescription($jobTitle, $company, $description, $skills);

            $this->success($response, ['enhanced_text' => $enhancedText ?: $description], 'Description enhanced successfully');
        } catch (\Exception $e) {
            $this->success($response, ['enhanced_text' => $description], 'Description enhanced successfully');
        }
    }

    public function aiSuggestSkills(Request $request, Response $response, string $resumeId): void
    {
        $data = $this->getCandidateAndResume($request, $response, $resumeId);
        if (!$data) return;
        [$candidate, $resume] = $data;

        $body = $request->getJsonBody();
        $jobRole = $body['job_role'] ?? '';
        $profile = $this->extractProfileFromResume($resume, $candidate);

        try {
            $aiService = new ResumeAIService();
            
            if (!empty($jobRole)) {
                $suggestedSkills = $aiService->suggestSkillsByJobRole($jobRole, $profile['candidateProfile']);
            } else {
                $suggestedSkills = $aiService->suggestSkills($profile['experience'], $profile['education'], $jobRole);
            }

            $this->success($response, ['skills' => $suggestedSkills], 'Skills suggested successfully');
        } catch (\Exception $e) {
            $this->success($response, ['skills' => []], 'Skills suggested successfully');
        }
    }
}
