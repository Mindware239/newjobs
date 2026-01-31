<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Resume;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Shared\Html;

class ResumeWordExportService
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
     * Export resume as Word document
     * 
     * @param int $resumeId Resume ID
     * @param bool $skipWatermark If false, skip watermark (premium only)
     * @return string|null Path to generated Word file or null on failure
     */
    public function generateWord(int $resumeId, bool $skipWatermark = true): ?string
    {
        $resume = Resume::find($resumeId);
        if (!$resume) {
            return null;
        }

        $template = $resume->template();
        $sections = $resume->getSectionsArray();

        try {
            $phpWord = new PhpWord();
            $phpWord->setDefaultFontName('Arial');
            $phpWord->setDefaultFontSize(11);

            $section = $phpWord->addSection([
                'marginTop' => 1440, // 1 inch = 1440 twips
                'marginBottom' => 1440,
                'marginLeft' => 1440,
                'marginRight' => 1440,
            ]);

            // Render sections
            foreach ($sections as $sectionData) {
                if (!($sectionData['is_visible'] ?? true)) {
                    continue;
                }

                $sectionType = $sectionData['section_type'] ?? '';
                $content = $sectionData['section_data']['content'] ?? [];

                $this->renderSection($section, $sectionType, $content);
            }

            // Add watermark if not premium
            if (!$skipWatermark) {
                $footer = $section->addFooter();
                $footer->addText('Created with Mindware Infotech', [
                    'size' => 8,
                    'color' => 'CCCCCC'
                ], ['alignment' => 'right']);
            }

            // Save document
            $filename = 'resume_' . $resumeId . '_' . time() . '.docx';
            $filepath = $this->storagePath . $filename;
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($filepath);

            // Update resume with Word URL
            $wordUrl = $this->publicPath . $filename;
            $resume->attributes['word_url'] = $wordUrl;
            $resume->save();

            return $wordUrl;
        } catch (\Exception $e) {
            error_log("Resume Word Export Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Render a section in Word document
     */
    private function renderSection($section, string $sectionType, array $content): void
    {
        switch ($sectionType) {
            case 'header':
                $this->renderHeader($section, $content);
                break;
            case 'summary':
                $this->renderSummary($section, $content);
                break;
            case 'experience':
                $this->renderExperience($section, $content);
                break;
            case 'education':
                $this->renderEducation($section, $content);
                break;
            case 'skills':
                $this->renderSkills($section, $content);
                break;
        }
    }

    /**
     * Render header section
     */
    private function renderHeader($section, array $content): void
    {
        if (empty($content['full_name'])) return;

        $section->addText($content['full_name'] ?? '', [
            'bold' => true,
            'size' => 18
        ], ['alignment' => 'center']);

        $contactInfo = [];
        if (!empty($content['email'])) $contactInfo[] = $content['email'];
        if (!empty($content['phone'])) $contactInfo[] = $content['phone'];
        if (!empty($content['location'])) $contactInfo[] = $content['location'];

        if (!empty($contactInfo)) {
            $section->addText(implode(' | ', $contactInfo), [
                'size' => 10
            ], ['alignment' => 'center']);
        }

        $section->addTextBreak(1);
    }

    /**
     * Render summary section
     */
    private function renderSummary($section, array $content): void
    {
        if (empty($content['text'])) return;

        $section->addText('PROFESSIONAL SUMMARY', [
            'bold' => true,
            'size' => 12,
            'underline' => 'single'
        ]);

        $section->addText($content['text'] ?? '', ['size' => 11]);
        $section->addTextBreak(1);
    }

    /**
     * Render experience section
     */
    private function renderExperience($section, array $content): void
    {
        $items = $content['items'] ?? [];
        if (empty($items)) return;

        $section->addText('WORK EXPERIENCE', [
            'bold' => true,
            'size' => 12,
            'underline' => 'single'
        ]);

        foreach ($items as $item) {
            $title = ($item['job_title'] ?? '') . ($item['company_name'] ? ' - ' . $item['company_name'] : '');
            $section->addText($title, ['bold' => true, 'size' => 11]);

            $dates = $this->formatDateRange(
                $item['start_date'] ?? '',
                $item['end_date'] ?? '',
                $item['is_current'] ?? false
            );
            $section->addText($dates, ['italic' => true, 'size' => 10]);

            if (!empty($item['description'])) {
                $section->addText($item['description'], ['size' => 10]);
            }

            $section->addTextBreak(1);
        }
    }

    /**
     * Render education section
     */
    private function renderEducation($section, array $content): void
    {
        $items = $content['items'] ?? [];
        if (empty($items)) return;

        $section->addText('EDUCATION', [
            'bold' => true,
            'size' => 12,
            'underline' => 'single'
        ]);

        foreach ($items as $item) {
            $degree = ($item['degree'] ?? '') . ($item['field_of_study'] ? ' - ' . $item['field_of_study'] : '');
            $section->addText($degree, ['bold' => true, 'size' => 11]);
            $section->addText($item['institution'] ?? '', ['size' => 10]);
            $section->addTextBreak(1);
        }
    }

    /**
     * Render skills section
     */
    private function renderSkills($section, array $content): void
    {
        $items = $content['items'] ?? [];
        if (empty($items)) return;

        $section->addText('SKILLS', [
            'bold' => true,
            'size' => 12,
            'underline' => 'single'
        ]);

        $skills = array_map(function($item) {
            return is_array($item) ? ($item['name'] ?? '') : $item;
        }, $items);

        $section->addText(implode(' • ', $skills), ['size' => 11]);
        $section->addTextBreak(1);
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

