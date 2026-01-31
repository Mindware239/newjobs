<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Resume;

class ResumeTXTExportService
{
    private string $storagePath;
    private string $publicPath;

    public function __construct()
    {
        $this->storagePath = $_SERVER['DOCUMENT_ROOT'] . '/storage/resumes/';
        $this->publicPath = '/storage/resumes/';

        // Create directory if it doesn't exist
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    /**
     * Export resume as plain text
     * 
     * @param int $resumeId Resume ID
     * @param bool $skipWatermark If false, skip watermark (premium only)
     * @return string|null Path to generated TXT file or null on failure
     */
    public function generateTXT(int $resumeId, bool $skipWatermark = true): ?string
    {
        $resume = Resume::find($resumeId);
        if (!$resume) {
            return null;
        }

        $sections = $resume->getSectionsArray();
        $content = [];

        // Render sections
        foreach ($sections as $sectionData) {
            if (!($sectionData['is_visible'] ?? true)) {
                continue;
            }

            $sectionType = $sectionData['section_type'] ?? '';
            $sectionContent = $sectionData['section_data']['content'] ?? [];

            $sectionText = $this->renderSection($sectionType, $sectionContent);
            if ($sectionText) {
                $content[] = $sectionText;
            }
        }

        // Add watermark if not premium
        if (!$skipWatermark) {
            $content[] = "\n\n---\nCreated with Mindware Infotech";
        }

        // Generate filename and save
        $filename = 'resume_' . $resumeId . '_' . time() . '.txt';
        $filepath = $this->storagePath . $filename;
        
        file_put_contents($filepath, implode("\n\n", $content));

        // Update resume with TXT URL
        $txtUrl = $this->publicPath . $filename;
        $resume->attributes['txt_url'] = $txtUrl;
        $resume->save();

        return $txtUrl;
    }

    /**
     * Render a section as text
     */
    private function renderSection(string $sectionType, array $content): string
    {
        switch ($sectionType) {
            case 'header':
                return $this->renderHeader($content);
            case 'summary':
                return $this->renderSummary($content);
            case 'experience':
                return $this->renderExperience($content);
            case 'education':
                return $this->renderEducation($content);
            case 'skills':
                return $this->renderSkills($content);
            default:
                return '';
        }
    }

    /**
     * Render header section
     */
    private function renderHeader(array $content): string
    {
        $lines = [];
        if (!empty($content['full_name'])) {
            $lines[] = strtoupper($content['full_name']);
        }

        $contactInfo = [];
        if (!empty($content['email'])) $contactInfo[] = $content['email'];
        if (!empty($content['phone'])) $contactInfo[] = $content['phone'];
        if (!empty($content['location'])) $contactInfo[] = $content['location'];

        if (!empty($contactInfo)) {
            $lines[] = implode(' | ', $contactInfo);
        }

        return implode("\n", $lines);
    }

    /**
     * Render summary section
     */
    private function renderSummary(array $content): string
    {
        if (empty($content['text'])) return '';
        return "PROFESSIONAL SUMMARY\n" . str_repeat('=', 50) . "\n" . $content['text'];
    }

    /**
     * Render experience section
     */
    private function renderExperience(array $content): string
    {
        $items = $content['items'] ?? [];
        if (empty($items)) return '';

        $lines = ["WORK EXPERIENCE", str_repeat('=', 50)];

        foreach ($items as $item) {
            $title = ($item['job_title'] ?? '');
            if (!empty($item['company_name'])) {
                $title .= ' - ' . $item['company_name'];
            }
            $lines[] = $title;

            $dates = $this->formatDateRange(
                $item['start_date'] ?? '',
                $item['end_date'] ?? '',
                $item['is_current'] ?? false
            );
            if ($dates) {
                $lines[] = $dates;
            }

            if (!empty($item['description'])) {
                $lines[] = $item['description'];
            }

            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * Render education section
     */
    private function renderEducation(array $content): string
    {
        $items = $content['items'] ?? [];
        if (empty($items)) return '';

        $lines = ["EDUCATION", str_repeat('=', 50)];

        foreach ($items as $item) {
            $degree = $item['degree'] ?? '';
            if (!empty($item['field_of_study'])) {
                $degree .= ' - ' . $item['field_of_study'];
            }
            $lines[] = $degree;

            if (!empty($item['institution'])) {
                $lines[] = $item['institution'];
            }

            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * Render skills section
     */
    private function renderSkills(array $content): string
    {
        $items = $content['items'] ?? [];
        if (empty($items)) return '';

        $skills = array_map(function($item) {
            return is_array($item) ? ($item['name'] ?? '') : $item;
        }, $items);

        return "SKILLS\n" . str_repeat('=', 50) . "\n" . implode(' • ', $skills);
    }

    /**
     * Format date range
     */
    private function formatDateRange(string $start, string $end, bool $isCurrent): string
    {
        if (!$start) return '';
        
        $startFormatted = date('M Y', strtotime($start));
        $endFormatted = $isCurrent ? 'Present' : ($end ? date('M Y', strtotime($end)) : '');
        
        return $startFormatted . ($endFormatted ? ' - ' . $endFormatted : '');
    }
}

