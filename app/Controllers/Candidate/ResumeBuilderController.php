<?php

declare(strict_types=1);

namespace App\Controllers\Candidate;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Candidate;
use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Models\ResumeSection;
use App\Services\ResumePDFService;
use App\Services\ResumeAIService;

class ResumeBuilderController extends BaseController
{
    /**
     * Ensure user is a candidate
     */
    private function ensureCandidate(Request $request, Response $response): ?Candidate
    {
        if (!$this->requireAuth($request, $response)) {
            return null;
        }

        $candidate = Candidate::findByUserId((int)$this->currentUser->id);
        if (!$candidate) {
            $candidate = Candidate::createForUser((int)$this->currentUser->id);
        }

        return $candidate;
    }

    /**
     * Show onboarding page
     * GET /candidate/resume/builder/onboarding
     */
    public function onboarding(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) {
            return;
        }

        $response->view('candidate/resume/builder/onboarding', [
            'title' => 'Resume Builder - Get Started',
            'candidate' => $candidate
        ], 200);
    }

    /**
     * Show template gallery (legacy - redirects to onboarding)
     * GET /candidate/resume/builder
     */
    public function index(Request $request, Response $response): void
    {
        // Show templates directly instead of redirecting
        $this->templates($request, $response);
    }

    /**
     * Show template gallery
     * GET /candidate/resume/builder/templates
     */
    public function templates(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) {
            return;
        }

        // Get filter parameters
        $filters = [
            'include_premium' => true, // Show all templates, filter client-side
        ];
        
        // Apply filters from query params
        $category = $request->get('category');
        if ($category !== null && $category !== '') {
            $filters['category'] = $category;
        }
        
        $jobCategory = $request->get('job_category');
        if ($jobCategory !== null && $jobCategory !== '') {
            $filters['job_category'] = $jobCategory;
        }
        
        $hasPhoto = $request->get('has_photo');
        if ($hasPhoto !== null && $hasPhoto !== '') {
            $filters['has_photo'] = (bool)$hasPhoto;
        }
        
        $layoutType = $request->get('layout_type');
        if ($layoutType !== null && $layoutType !== '') {
            $filters['layout_type'] = $layoutType;
        }
        
        $colorScheme = $request->get('color_scheme');
        if ($colorScheme !== null && $colorScheme !== '') {
            $filters['color_scheme'] = $colorScheme;
        }

        // Get all active templates
        $isPremium = (bool)($candidate->attributes['is_premium'] ?? false);
        $templates = ResumeTemplate::getActive($filters);

        // Separate free and premium templates
        $freeTemplates = array_filter($templates, fn($t) => !($t->attributes['is_premium'] ?? false));
        $premiumTemplates = array_filter($templates, fn($t) => (bool)($t->attributes['is_premium'] ?? false));

        // Get filter options for dropdowns
        try {
            $categories = ResumeTemplate::getCategories();
            $jobCategories = ResumeTemplate::getJobCategories();
            $layoutTypes = ResumeTemplate::getLayoutTypes();
            $colorSchemes = ResumeTemplate::getColorSchemes();
        } catch (\Exception $e) {
            // Fallback if methods don't exist yet
            $categories = [];
            $jobCategories = [];
            $layoutTypes = ['single-column', 'two-column', 'three-column'];
            $colorSchemes = ['blue', 'green', 'purple', 'red', 'black'];
        }

        // Get candidate's existing resumes
        $resumes = Resume::getByCandidateId((int)$candidate->attributes['id']);

        $response->view('candidate/resume/builder/index', [
            'title' => 'Resume Builder',
            'candidate' => $candidate,
            'freeTemplates' => array_values($freeTemplates),
            'premiumTemplates' => array_values($premiumTemplates),
            'isPremium' => $isPremium,
            'resumes' => $resumes,
            'canCreateMore' => Resume::canCreateMore((int)$candidate->attributes['id']),
            'categories' => $categories,
            'jobCategories' => $jobCategories,
            'layoutTypes' => $layoutTypes,
            'colorSchemes' => $colorSchemes,
            'activeFilters' => $filters
        ], 200, 'candidate/layout');
    }

    /**
     * Create new resume from template
     * POST /candidate/resume/builder/create
     */
    public function create(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) {
            return;
        }

        $candidateId = (int)$candidate->attributes['id'];

        // Check if candidate can create more resumes
        if (!Resume::canCreateMore($candidateId)) {
            $response->json([
                'error' => 'Free accounts are limited to 1 resume. Upgrade to Premium for unlimited resumes.'
            ], 403);
            return;
        }

        $data = $request->getJsonBody() ?? $request->all();
        $templateId = (int)($data['template_id'] ?? 0);
        $title = trim($data['title'] ?? 'My Resume');
        $primaryColor = trim($data['primary_color'] ?? '');

        if (!$templateId) {
            $response->json(['error' => 'Template ID is required'], 400);
            return;
        }

        /** @var ResumeTemplate|null $template */
        $template = ResumeTemplate::find($templateId);
        if (!$template) {
            $response->json(['error' => 'Template not found'], 404);
            return;
        }

        // Check premium access
        if (!$template->isAccessible($candidate)) {
            $response->json([
                'error' => 'This template requires a Premium subscription',
                'requires_premium' => true
            ], 403);
            return;
        }

        // Create resume
        $resume = new Resume();
        $resume->fill([
            'candidate_id' => $candidateId,
            'template_id' => $templateId,
            'title' => $title,
            'status' => 'draft'
        ]);

        if (!$resume->save()) {
            $response->json(['error' => 'Failed to create resume'], 500);
            return;
        }

        // Initialize sections from template schema
        $this->initializeSectionsFromTemplate($resume, $template, $candidate);

        // Apply selected color theme if provided
        if ($primaryColor !== '') {
            $themeSection = new ResumeSection();
            $themeSection->fill([
                'resume_id' => $resume->getId(),
                'section_type' => 'theme',
                'section_data' => json_encode(['content' => ['primary_color' => $primaryColor]], JSON_UNESCAPED_UNICODE),
                'sort_order' => 999,
                'is_visible' => 0
            ]);
            $themeSection->save();
        }

        // Calculate strength score
        $resume->setAttribute('strength_score', $resume->calculateStrengthScore());
        $resume->save();

        $response->json([
            'success' => true,
            'resume_id' => $resume->getId(),
            'redirect' => '/candidate/resume/builder/' . $resume->getId() . '/wizard'
        ]);
    }

    /**
     * Show resume builder editor
     * GET /candidate/resume/builder/{resumeId}/edit
     */
    public function edit(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) {
            return;
        }

        $resumeId = (int)$request->param('resumeId');
        /** @var Resume|null $resume */
        $resume = Resume::find($resumeId);

        if (!$resume) {
            $response->redirect('/candidate/resume/builder');
            return;
        }

        // Verify ownership
        if ((int)$resume->attributes['candidate_id'] !== (int)$candidate->attributes['id']) {
            $response->redirect('/candidate/resume/builder');
            return;
        }

        $template = $resume->template();
        $sections = $resume->getSectionsArray();

        $response->view('candidate/resume/builder/edit', [
            'title' => 'Edit Resume - ' . ($resume->attributes['title'] ?? 'My Resume'),
            'candidate' => $candidate,
            'resume' => $resume,
            'template' => $template,
            'sections' => $sections,
            'isPremium' => (bool)($candidate->attributes['is_premium'] ?? false)
        ], 200, 'candidate/layout');
    }

    /**
     * Resume builder wizard (step-by-step)
     * GET /candidate/resume/builder/{resumeId}/wizard
     */
    public function wizard(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) {
            return;
        }

        $resumeId = (int)$request->param('resumeId');
        $resume = Resume::find($resumeId);

        if (!$resume || (int)$resume->attributes['candidate_id'] !== (int)$candidate->attributes['id']) {
            $response->redirect('/candidate/resume/builder');
            return;
        }

        $template = $resume->template();
        $sections = $resume->getSectionsArray();
        
        // Convert sections array to associative array by section_type
        $sectionsByType = [];
        foreach ($sections as $section) {
            $sectionType = $section['section_type'] ?? '';
            if ($sectionType) {
                $sectionsByType[$sectionType] = [
                    'id' => $section['id'] ?? null,
                    'section_type' => $sectionType,
                    'section_data' => $section['section_data'] ?? ['content' => []],
                    'sort_order' => $section['sort_order'] ?? 0,
                    'is_visible' => $section['is_visible'] ?? true
                ];
            }
        }

        $response->view('candidate/resume/builder/wizard', [
            'title' => 'Resume Builder - ' . ($resume->attributes['title'] ?? 'My Resume'),
            'candidate' => $candidate,
            'resume' => $resume,
            'template' => $template,
            'sections' => $sections,
            'sectionsData' => $sectionsByType,
            'isPremium' => (bool)($candidate->attributes['is_premium'] ?? false)
        ], 200);
    }

    /**
     * Save resume sections
     * POST /candidate/resume/builder/{resumeId}/save
     */
    public function save(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) {
            return;
        }

        $resumeId = (int)$request->param('resumeId');
        $resume = Resume::find($resumeId);

        if (!$resume || (int)$resume->attributes['candidate_id'] !== (int)$candidate->attributes['id']) {
            $response->json(['error' => 'Resume not found'], 404);
            return;
        }

        $data = $request->getJsonBody() ?? [];
        $sections = $data['sections'] ?? [];
        $title = trim($data['title'] ?? $resume->attributes['title'] ?? 'My Resume');

        // Update resume title
        $resume->setAttribute('title', $title);

        // Save/update sections
        foreach ($sections as $sectionData) {
            $sectionId = isset($sectionData['id']) ? (int)$sectionData['id'] : null;
            
            if ($sectionId) {
                // Update existing section
                $section = ResumeSection::find($sectionId);
                if ($section && (int)$section->attributes['resume_id'] === $resumeId) {
                    $section->updateData($sectionData['section_data'] ?? []);
                    if (isset($sectionData['sort_order'])) {
                        $section->setAttribute('sort_order', (int)$sectionData['sort_order']);
                    }
                    if (isset($sectionData['is_visible'])) {
                        $section->setAttribute('is_visible', (bool)$sectionData['is_visible'] ? 1 : 0);
                    }
                    $section->save();
                }
            } else {
                // Create new section
                $section = new ResumeSection();
                $section->fill([
                    'resume_id' => $resumeId,
                    'section_type' => $sectionData['section_type'] ?? 'summary',
                    'section_data' => json_encode($sectionData['section_data'] ?? [], JSON_UNESCAPED_UNICODE),
                    'sort_order' => (int)($sectionData['sort_order'] ?? 0),
                    'is_visible' => (bool)($sectionData['is_visible'] ?? true) ? 1 : 0
                ]);
                $section->save();
            }
        }

        // Recalculate strength score
        $resume->setAttribute('strength_score', $resume->calculateStrengthScore());
        $resume->setAttribute('status', 'draft'); // Keep as draft until published
        $resume->save();

        $response->json([
            'success' => true,
            'strength_score' => $resume->attributes['strength_score']
        ]);
    }

    /**
     * AI: Generate professional summary
     * POST /candidate/resume/builder/{resumeId}/ai/generate-summary
     */
    public function aiGenerateSummary(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) {
            return;
        }

        $resumeId = (int)$request->param('resumeId');
        $resume = Resume::find($resumeId);

        if (!$resume || (int)$resume->attributes['candidate_id'] !== (int)$candidate->attributes['id']) {
            $response->json(['error' => 'Resume not found'], 404);
            return;
        }

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

        try {
            $aiService = new ResumeAIService();
            $summary = $aiService->generateSummary($experience, $education, $skills, $jobTitle);
            
            if (!$summary) {
                $summary = $aiService->generateBasicSummary($experience, $education, $skills);
            }

            $response->json([
                'success' => true,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            error_log("AI Summary Generation Error: " . $e->getMessage());
            $aiService = new ResumeAIService();
            $summary = $aiService->generateBasicSummary($experience, $education, $skills);
            
            $response->json([
                'success' => true,
                'summary' => $summary,
                'note' => 'Using basic summary (AI service unavailable)'
            ]);
        }
    }

    /**
     * AI: Enhance job description
     * POST /candidate/resume/builder/{resumeId}/ai/enhance-description
     */
    public function aiEnhanceDescription(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) {
            return;
        }

        $resumeId = (int)$request->param('resumeId');
        $resume = Resume::find($resumeId);

        if (!$resume || (int)$resume->attributes['candidate_id'] !== (int)$candidate->attributes['id']) {
            $response->json(['error' => 'Resume not found'], 404);
            return;
        }

        $body = $request->post();
        $jobTitle = $body['job_title'] ?? '';
        $company = $body['company'] ?? '';
        $description = $body['description'] ?? '';
        $skills = $body['skills'] ?? [];

        if (empty($description)) {
            $response->json(['error' => 'Description is required'], 400);
            return;
        }

        try {
            $aiService = new ResumeAIService();
            $enhanced = $aiService->enhanceJobDescription($jobTitle, $company, $description, $skills);

            if (!$enhanced) {
                // AI service unavailable - return original with note
                $response->json([
                    'success' => true,
                    'description' => $description,
                    'note' => 'AI enhancement unavailable. Using original content.'
                ]);
                return;
            }

            $response->json([
                'success' => true,
                'description' => $enhanced
            ]);
        } catch (\Exception $e) {
            error_log("AI Description Enhancement Error: " . $e->getMessage());
            // Always return success with original content so UI doesn't break
            $response->json([
                'success' => true,
                'description' => $description,
                'note' => 'AI enhancement unavailable. Using original content.'
            ]);
        }
    }

    /**
     * AI: Suggest skills based on job role
     * POST /candidate/resume/builder/{resumeId}/ai/suggest-skills
     */
    public function aiSuggestSkills(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) {
            return;
        }

        $resumeId = (int)$request->param('resumeId');
        $resume = Resume::find($resumeId);

        if (!$resume || (int)$resume->attributes['candidate_id'] !== (int)$candidate->attributes['id']) {
            $response->json(['error' => 'Resume not found'], 404);
            return;
        }

        $body = $request->post();
        $jobRole = $body['job_role'] ?? '';

        $sections = $resume->getSectionsArray();
        $experience = [];
        $education = [];
        $skills = [];

        foreach ($sections as $section) {
            $type = $section['section_type'] ?? '';
            $content = $section['section_data']['content'] ?? [];
            
            if ($type === 'experience') {
                $experience = $content['items'] ?? [];
                if (empty($jobRole) && !empty($experience)) {
                    $jobRole = $experience[0]['job_title'] ?? '';
                }
            } elseif ($type === 'education') {
                $education = $content['items'] ?? [];
            } elseif ($type === 'skills') {
                $skills = array_column($content['items'] ?? [], 'name');
            }
        }

        // Build candidate profile
        $candidateProfile = [
            'full_name' => $candidate->attributes['full_name'] ?? '',
            'experience' => $experience,
            'education' => $education,
            'skills' => $skills,
            'self_introduction' => $candidate->attributes['self_introduction'] ?? ''
        ];

        try {
            $aiService = new ResumeAIService();
            
            // Use job role-based suggestion if job role provided
            if (!empty($jobRole)) {
                $suggestedSkills = $aiService->suggestSkillsByJobRole($jobRole, $candidateProfile);
            } else {
                $suggestedSkills = $aiService->suggestSkills($experience, $education, $jobRole);
            }

            $response->json([
                'success' => true,
                'skills' => $suggestedSkills
            ]);
        } catch (\Exception $e) {
            error_log("AI Skills Suggestion Error: " . $e->getMessage());
            $response->json([
                'success' => true,
                'skills' => []
            ]);
        }
    }

    /**
     * AI: Generate job summary based on candidate profile
     * POST /candidate/resume/builder/{resumeId}/ai/generate-job-summary
     */
    public function aiGenerateJobSummary(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) {
            return;
        }

        $resumeId = (int)$request->param('resumeId');
        $resume = Resume::find($resumeId);

        if (!$resume || (int)$resume->attributes['candidate_id'] !== (int)$candidate->attributes['id']) {
            $response->json(['error' => 'Resume not found'], 404);
            return;
        }

        $body = $request->post();
        $targetJobRole = $body['target_job_role'] ?? '';

        $sections = $resume->getSectionsArray();
        $experience = [];
        $education = [];
        $skills = [];

        foreach ($sections as $section) {
            $type = $section['section_type'] ?? '';
            $content = $section['section_data']['content'] ?? [];
            
            if ($type === 'experience') {
                $experience = $content['items'] ?? [];
            } elseif ($type === 'education') {
                $education = $content['items'] ?? [];
            } elseif ($type === 'skills') {
                $skills = array_column($content['items'] ?? [], 'name');
            }
        }

        // Build candidate profile
        $candidateProfile = [
            'full_name' => $candidate->attributes['full_name'] ?? '',
            'experience' => $experience,
            'education' => $education,
            'skills' => $skills,
            'self_introduction' => $candidate->attributes['self_introduction'] ?? ''
        ];

        try {
            $aiService = new ResumeAIService();
            $summary = $aiService->generateJobSummary($candidateProfile, $targetJobRole);
            
            if (!$summary) {
                $summary = $aiService->generateBasicSummary($experience, $education, $skills);
            }

            $response->json([
                'success' => true,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            error_log("AI Job Summary Generation Error: " . $e->getMessage());
            $aiService = new ResumeAIService();
            $summary = $aiService->generateBasicSummary($experience, $education, $skills);
            
            $response->json([
                'success' => true,
                'summary' => $summary,
                'note' => 'Using basic summary (AI service unavailable)'
            ]);
        }
    }

    /**
     * AI: Generate experience description based on candidate profile
     * POST /candidate/resume/builder/{resumeId}/ai/generate-experience
     */
    public function aiGenerateExperience(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) {
            return;
        }

        $resumeId = (int)$request->param('resumeId');
        $resume = Resume::find($resumeId);

        if (!$resume || (int)$resume->attributes['candidate_id'] !== (int)$candidate->attributes['id']) {
            $response->json(['error' => 'Resume not found'], 404);
            return;
        }

        $body = $request->post();
        $jobTitle = $body['job_title'] ?? '';
        $company = $body['company'] ?? '';
        $targetJobRole = $body['target_job_role'] ?? '';

        if (empty($jobTitle)) {
            $response->json(['error' => 'Job title is required'], 400);
            return;
        }

        $sections = $resume->getSectionsArray();
        $experience = [];
        $education = [];
        $skills = [];

        foreach ($sections as $section) {
            $type = $section['section_type'] ?? '';
            $content = $section['section_data']['content'] ?? [];
            
            if ($type === 'experience') {
                $experience = $content['items'] ?? [];
            } elseif ($type === 'education') {
                $education = $content['items'] ?? [];
            } elseif ($type === 'skills') {
                $skills = array_column($content['items'] ?? [], 'name');
            }
        }

        // Build candidate profile
        $candidateProfile = [
            'full_name' => $candidate->attributes['full_name'] ?? '',
            'experience' => $experience,
            'education' => $education,
            'skills' => $skills,
            'self_introduction' => $candidate->attributes['self_introduction'] ?? ''
        ];

        try {
            $aiService = new ResumeAIService();
            $description = $aiService->generateExperienceDescription($candidateProfile, $jobTitle, $company, $targetJobRole);

            if (!$description) {
                // More detailed fallback
                $description = "• Developed and implemented {$jobTitle} solutions to improve efficiency\n• Collaborated with cross-functional teams to deliver high-quality results\n• Utilized technical skills to solve complex problems\n• Contributed to project success through effective communication and teamwork\n• Maintained code quality and followed best practices\n• Participated in code reviews and knowledge sharing sessions";
            }

            $response->json([
                'success' => true,
                'description' => $description
            ]);
        } catch (\Exception $e) {
            error_log("AI Experience Generation Error: " . $e->getMessage());
            // More detailed fallback
            $fallback = "• Developed and implemented {$jobTitle} solutions to improve efficiency\n• Collaborated with cross-functional teams to deliver high-quality results\n• Utilized technical skills to solve complex problems\n• Contributed to project success through effective communication and teamwork\n• Maintained code quality and followed best practices\n• Participated in code reviews and knowledge sharing sessions";
            $response->json([
                'success' => true,
                'description' => $fallback,
                'note' => 'Using detailed description (AI service unavailable - check OPENAI_API_KEY in .env)'
            ]);
        }
    }

    /**
     * AI: Generate section description based on candidate profile
     * POST /candidate/resume/builder/{resumeId}/ai/generate-section
     */
    public function aiGenerateSection(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) {
            return;
        }

        $resumeId = (int)$request->param('resumeId');
        $resume = Resume::find($resumeId);

        if (!$resume || (int)$resume->attributes['candidate_id'] !== (int)$candidate->attributes['id']) {
            $response->json(['error' => 'Resume not found'], 404);
            return;
        }

        $body = $request->post();
        $sectionType = $body['section_type'] ?? '';
        $sectionData = $body['section_data'] ?? [];

        if (empty($sectionType)) {
            $response->json(['error' => 'Section type is required'], 400);
            return;
        }

        $sections = $resume->getSectionsArray();
        $experience = [];
        $education = [];
        $skills = [];

        foreach ($sections as $section) {
            $type = $section['section_type'] ?? '';
            $content = $section['section_data']['content'] ?? [];
            
            if ($type === 'experience') {
                $experience = $content['items'] ?? [];
            } elseif ($type === 'education') {
                $education = $content['items'] ?? [];
            } elseif ($type === 'skills') {
                $skills = array_column($content['items'] ?? [], 'name');
            }
        }

        // Build candidate profile
        $candidateProfile = [
            'full_name' => $candidate->attributes['full_name'] ?? '',
            'experience' => $experience,
            'education' => $education,
            'skills' => $skills,
            'self_introduction' => $candidate->attributes['self_introduction'] ?? ''
        ];

        try {
            $aiService = new ResumeAIService();
            $content = $aiService->generateSectionDescription($sectionType, $candidateProfile, $sectionData);

            if (!$content) {
                $content = $this->getDefaultSectionContent($sectionType);
            }

            $response->json([
                'success' => true,
                'content' => $content
            ]);
        } catch (\Exception $e) {
            error_log("AI Section Generation Error: " . $e->getMessage());
            $response->json([
                'success' => true,
                'content' => $this->getDefaultSectionContent($sectionType),
                'note' => 'Using default content (AI service unavailable)'
            ]);
        }
    }

    /**
     * Get default section content when AI is unavailable
     */
    private function getDefaultSectionContent(string $sectionType): string
    {
        switch ($sectionType) {
            case 'summary':
                return 'Experienced professional seeking opportunities to leverage expertise and drive results.';
            case 'experience':
                return "• Responsible for key duties and responsibilities\n• Collaborated with team members\n• Achieved project goals and objectives";
            case 'education':
                return 'Relevant coursework and academic achievements.';
            case 'skills':
                return 'Technical and soft skills relevant to the role.';
            default:
                return '';
        }
    }

    /**
     * Export resume as PDF
     * POST /candidate/resume/builder/{resumeId}/export-pdf
     */
    public function exportPDF(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) {
            return;
        }

        $resumeId = (int)$request->param('resumeId');
        $resume = Resume::find($resumeId);

        if (!$resume || (int)$resume->attributes['candidate_id'] !== (int)$candidate->attributes['id']) {
            $response->json(['error' => 'Resume not found'], 404);
            return;
        }

        $isPremium = (bool)($candidate->attributes['is_premium'] ?? false);

        try {
            $pdfService = new ResumePDFService();
            $pdfUrl = $pdfService->generatePDF($resume->getId(), !$isPremium); // true = with watermark for free users
            if (!$pdfUrl) {
                $response->json([
                    'error' => 'Failed to generate PDF: no file produced'
                ], 500);
                return;
            }
            $response->json([
                'success' => true,
                'pdf_url' => $pdfUrl,
                'message' => 'PDF generated successfully'
            ]);
        } catch (\Exception $e) {
            error_log("Resume PDF Export Error: " . $e->getMessage());
            $response->json([
                'error' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initialize sections from template schema
     */
    private function initializeSectionsFromTemplate(Resume $resume, ResumeTemplate $template, Candidate $candidate): void
    {
        $schema = $template->getSchema();
        $sectionTypes = $schema['sections'] ?? ['header', 'summary', 'experience', 'education', 'skills'];

        $resumeId = $resume->getId();
        $sortOrder = 0;

        foreach ($sectionTypes as $sectionType) {
            $sectionData = $this->getDefaultSectionData($sectionType, $candidate);

            $section = new ResumeSection();
            $section->fill([
                'resume_id' => $resumeId,
                'section_type' => $sectionType,
                'section_data' => json_encode($sectionData, JSON_UNESCAPED_UNICODE),
                'sort_order' => $sortOrder++,
                'is_visible' => 1
            ]);
            $section->save();
        }
    }

    /**
     * Get default section data populated from candidate profile
     */
    private function getDefaultSectionData(string $sectionType, Candidate $candidate): array
    {
        $attrs = $candidate->attributes;

        switch ($sectionType) {
            case 'header':
                $user = $candidate->user();
                return [
                    'content' => [
                        'full_name' => $attrs['full_name'] ?? '',
                        'email' => $user ? ($user->attributes['email'] ?? '') : '',
                        'phone' => $attrs['mobile'] ?? '',
                        'location' => trim(($attrs['city'] ?? '') . ', ' . ($attrs['state'] ?? '')),
                        'linkedin' => $attrs['linkedin_url'] ?? '',
                        'website' => $attrs['website_url'] ?? ''
                    ]
                ];

            case 'summary':
                return [
                    'content' => [
                        'text' => $attrs['self_introduction'] ?? ''
                    ]
                ];

            case 'experience':
                $experienceData = json_decode($attrs['experience_data'] ?? '[]', true);
                return [
                    'content' => [
                        'items' => is_array($experienceData) ? $experienceData : []
                    ]
                ];

            case 'education':
                $educationData = json_decode($attrs['education_data'] ?? '[]', true);
                return [
                    'content' => [
                        'items' => is_array($educationData) ? $educationData : []
                    ]
                ];

            case 'skills':
                $skillsData = json_decode($attrs['skills_data'] ?? '[]', true);
                return [
                    'content' => [
                        'items' => is_array($skillsData) ? $skillsData : []
                    ]
                ];

            case 'languages':
                $languagesData = json_decode($attrs['languages_data'] ?? '[]', true);
                return [
                    'content' => [
                        'items' => is_array($languagesData) ? $languagesData : []
                    ]
                ];

            default:
                return ['content' => []];
        }
    }

}
