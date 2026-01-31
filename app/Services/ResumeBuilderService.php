<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Models\Candidate;
use App\Models\ResumeSection;

class ResumeBuilderService
{
    /**
     * Initialize resume from template
     */
    public function initializeFromTemplate(int $templateId, int $candidateId): ?Resume
    {
        $template = ResumeTemplate::find($templateId);
        if (!$template) {
            return null;
        }

        $candidate = Candidate::find($candidateId);
        if (!$candidate) {
            return null;
        }

        // Create resume
        $resume = new Resume();
        $resume->fill([
            'candidate_id' => $candidateId,
            'template_id' => $templateId,
            'title' => 'My Resume',
            'status' => 'draft'
        ]);

        if (!$resume->save()) {
            return null;
        }

        // Populate sections from candidate profile
        $this->populateFromCandidateProfile($resume->getId(), $candidate);

        return $resume;
    }

    /**
     * Populate resume sections from candidate profile
     */
    public function populateFromCandidateProfile(int $resumeId, Candidate $candidate): void
    {
        $template = Resume::find($resumeId)->template();
        if (!$template) {
            return;
        }

        $schema = $template->getSchema();
        $sectionTypes = $schema['sections'] ?? ['header', 'summary', 'experience', 'education', 'skills'];

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
     * Get default section data from candidate profile
     */
    private function getDefaultSectionData(string $sectionType, Candidate $candidate): array
    {
        $attrs = $candidate->attributes;
        $user = $candidate->user();

        switch ($sectionType) {
            case 'header':
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

    /**
     * Merge section data
     */
    public function mergeSectionData(int $resumeId, array $sectionDataArray): bool
    {
        $resume = Resume::find($resumeId);
        if (!$resume) {
            return false;
        }

        foreach ($sectionDataArray as $sectionData) {
            $sectionId = $sectionData['id'] ?? null;
            
            if ($sectionId) {
                $section = ResumeSection::find($sectionId);
                if ($section) {
                    $section->updateData($sectionData['section_data'] ?? []);
                }
            } else {
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

        return true;
    }

    /**
     * Validate resume completeness
     */
    public function validateResume(int $resumeId): array
    {
        $resume = Resume::find($resumeId);
        if (!$resume) {
            return ['valid' => false, 'errors' => ['Resume not found']];
        }

        $errors = [];
        $sections = $resume->sections();

        // Check required sections
        $requiredSections = ['header', 'summary'];
        $presentTypes = array_map(function ($section) {
            return $section->attributes['section_type'] ?? null;
        }, $sections);

        foreach ($requiredSections as $reqSection) {
            if (!in_array($reqSection, $presentTypes)) {
                $errors[] = "Missing required section: {$reqSection}";
            }
        }

        // Check if sections have content
        foreach ($sections as $section) {
            $data = $section->getData();
            if (empty($data['content'])) {
                $sectionType = $section->attributes['section_type'] ?? 'unknown';
                $errors[] = "Section '{$sectionType}' is empty";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}

