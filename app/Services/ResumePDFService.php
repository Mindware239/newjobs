<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Resume;
use App\Models\Candidate;
use Dompdf\Dompdf;
use Dompdf\Options;

class ResumePDFService
{
    private string $storagePath;
    private string $publicPath;

    public function __construct()
    {
        // Set storage paths - PDFs stored in public/storage/resumes/
        $baseDir = dirname(__DIR__, 2);
        $this->storagePath = $baseDir . '/public/storage/resumes/';
        $this->publicPath = '/storage/resumes/';
        
        // Ensure directory exists
        if (!is_dir($this->storagePath)) {
            @mkdir($this->storagePath, 0755, true);
        }
    }

    /**
     * Generate PDF from resume
     * 
     * @param int $resumeId
     * @param bool $withWatermark If false, skip watermark (premium only)
     * @return string|null Path to generated PDF file or null on failure
     */
    public function generatePDF(int $resumeId, bool $withWatermark = true): ?string
    {
        $resume = Resume::find($resumeId);
        if (!$resume) {
            return null;
        }

        // Get candidate to check premium status
        $candidate = $resume->candidate();
        if (!$candidate) {
            return null;
        }

        $isPremium = (bool)($candidate->attributes['is_premium'] ?? false);
        
        // Generate HTML
        $html = $this->renderResumeHTML($resume, !$withWatermark || $isPremium);
        if (trim($html) === '') {
            $html = $this->renderFallbackHTML($resume);
        }

        // Configure DomPDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        
        $dompdf = new Dompdf($options);
        try {
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
        } catch (\Throwable $e) {
            // Fallback: render a minimal HTML to avoid blank PDFs
            $fallbackHtml = $this->renderFallbackHTML($resume);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($fallbackHtml);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
        }

        // Generate filename
        $filename = 'resume_' . $resumeId . '_' . time() . '.pdf';
        $filepath = $this->storagePath . $filename;

        // Save PDF
        if (!is_dir($this->storagePath)) {
            @mkdir($this->storagePath, 0755, true);
        }
        $pdfData = $dompdf->output();
        if (@file_put_contents($filepath, $pdfData) === false) {
            throw new \RuntimeException('Failed to write PDF file');
        }

        // Update resume with PDF URL
        $pdfUrl = $this->publicPath . $filename;
        $resume->attributes['pdf_url'] = $pdfUrl;
        $resume->save();

        return $pdfUrl;
    }

    /**
     * Render resume as HTML
     */
    private function renderResumeHTML(Resume $resume, bool $skipWatermark = false): string
    {
        $template = $resume->template();
        $sections = $resume->getSectionsArray();
        $schema = $template ? $template->getSchema() : [];
        $colors = $schema['colors'] ?? [
            'primary' => '#2563eb',
            'secondary' => '#64748b',
            'background' => '#ffffff',
            'text' => '#1e293b'
        ];
        // Theme override
        foreach ($sections as $sec) {
            if (($sec['section_type'] ?? '') === 'theme') {
                $content = $sec['section_data']['content'] ?? [];
                if (!empty($content['primary_color'])) {
                    $colors['primary'] = $content['primary_color'];
                }
                break;
            }
        }

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page {
            size: A4;
            margin: 10mm;
        }
        body {
            font-family: Arial, sans-serif;
            color: ' . htmlspecialchars($colors['text']) . ';
            background: ' . htmlspecialchars($colors['background']) . ';
            padding: 0;
            line-height: 1.6;
            margin: 0;
        }
        .resume-container {
            width: 100%;
            margin: 0 auto;
            background: white;
            padding: 12mm;
        }
        /* Keep complex items together, allow sections to split across pages */
        .experience-item, .education-item {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .header {
            border-bottom: 3px solid ' . htmlspecialchars($colors['primary']) . ';
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 32px;
            color: ' . htmlspecialchars($colors['primary']) . ';
            margin-bottom: 10px;
        }
        .header p {
            color: ' . htmlspecialchars($colors['secondary']) . ';
            font-size: 14px;
        }
        .section {
            margin-bottom: 24px;
        }
        .section:last-child {
            margin-bottom: 0;
        }
        .section-title {
            font-size: 20px;
            font-weight: bold;
            color: ' . htmlspecialchars($colors['primary']) . ';
            border-bottom: 2px solid ' . htmlspecialchars($colors['primary']) . ';
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .section-content {
            font-size: 14px;
        }
        .experience-item, .education-item {
            margin-bottom: 20px;
        }
        .item-header {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .item-meta {
            color: ' . htmlspecialchars($colors['secondary']) . ';
            font-size: 12px;
            margin-bottom: 8px;
        }
        .item-description {
            font-size: 14px;
        }
        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 6px;
        }
        .skill-tag {
            background: ' . htmlspecialchars($colors['primary']) . ';
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
        }
        ' . ($skipWatermark ? '' : '
        .watermark {
            position: absolute;
            bottom: 8mm;
            right: 12mm;
            color: rgba(0,0,0,0.12);
            font-size: 11px;
        }') . '
    </style>
</head>
<body>
    <div class="resume-container">';

        // Render sections - filter out empty sections to prevent blank pages
        $hasContent = false;
        $candidate = $resume->candidate();
        $fallbackHeaderRendered = false;
        foreach ($sections as $section) {
            if (!($section['is_visible'] ?? true)) {
                continue;
            }

            $sectionType = $section['section_type'] ?? '';
            $sectionData = $section['section_data'] ?? [];
            $content = $sectionData['content'] ?? [];
            
            // Skip empty sections to prevent blank pages
            if (empty($content)) {
                continue;
            }
            
            // Check if section has actual content
            $hasSectionContent = false;
            if ($sectionType === 'header') {
                $hasSectionContent = !empty($content['full_name']);
            } elseif ($sectionType === 'summary') {
                $hasSectionContent = !empty($content['text']);
            } elseif (in_array($sectionType, ['experience', 'education', 'skills', 'languages'])) {
                $hasSectionContent = !empty($content['items']) && count($content['items']) > 0;
            } elseif ($sectionType === 'additional') {
                $hasSectionContent = (!empty($content['projects']) && count($content['projects']) > 0) ||
                                    (!empty($content['certifications']) && count($content['certifications']) > 0);
            } else {
                $hasSectionContent = !empty($content);
            }
            
            if ($hasSectionContent) {
                $html .= $this->renderSectionHTML($sectionType, $content);
                $hasContent = true;
            }
        }
        
        // If no content, render a minimal professional header from candidate profile
        if (!$hasContent) {
            $fullName = $candidate ? ($candidate->attributes['full_name'] ?? '') : '';
            $email = $candidate ? ($candidate->attributes['email'] ?? '') : '';
            $phone = $candidate ? ($candidate->attributes['contact'] ?? ($candidate->attributes['phone'] ?? '')) : '';
            $location = $candidate ? ($candidate->attributes['city'] ?? '') : '';
            if (!empty($fullName) || !empty($email) || !empty($phone)) {
                $html .= $this->renderSectionHTML('header', [
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'location' => $location,
                ]);
                $fallbackHeaderRendered = true;
                $hasContent = true;
            } else {
                $html .= '<div class="section"><p>Resume content will appear here.</p></div>';
            }
        }

        // Add watermark if not premium
        if (!$skipWatermark) {
            $html .= '<div class="watermark">Created with Mindware Infotech</div>';
        }

        $html .= '
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Render minimal fallback HTML using candidate profile
     */
    private function renderFallbackHTML(Resume $resume): string
    {
        $candidate = $resume->candidate();
        $fullName = $candidate ? ($candidate->attributes['full_name'] ?? '') : '';
        $email = $candidate ? ($candidate->attributes['email'] ?? '') : '';
        $phone = $candidate ? ($candidate->attributes['contact'] ?? ($candidate->attributes['phone'] ?? '')) : '';
        $location = $candidate ? ($candidate->attributes['city'] ?? '') : '';

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
            @page { size: A4; margin: 15mm; }
            body { font-family: Arial, sans-serif; color: #1e293b; }
            h1 { font-size: 28px; color: #2563eb; margin: 0 0 8px 0; }
            p { margin: 4px 0; font-size: 13px; color: #475569; }
            .section { margin-top: 16px; }
            .title { font-weight: bold; color: #2563eb; border-bottom: 1px solid #2563eb; padding-bottom: 4px; margin-bottom: 8px; }
        </style></head><body><div class="section">
            <h1>' . htmlspecialchars($fullName ?: 'Resume') . '</h1>
            <p>' . htmlspecialchars(implode(' | ', array_filter([$email, $phone, $location]))) . '</p>
        </div><div class="section"><div class="title">Summary</div>
        <p>Your resume data is preparing. Please update sections for richer content.</p></div></body></html>';
        return $html;
    }

    /**
     * Render individual section HTML
     */
    private function renderSectionHTML(string $sectionType, array $content): string
    {
        $html = '<div class="section">';

        switch ($sectionType) {
            case 'header':
                $html .= '<div class="header">';
                $html .= '<h1>' . htmlspecialchars($content['full_name'] ?? '') . '</h1>';
                $html .= '<p>';
                $parts = array_filter([
                    $content['email'] ?? '',
                    $content['phone'] ?? '',
                    $content['location'] ?? ''
                ]);
                $html .= htmlspecialchars(implode(' | ', $parts));
                $html .= '</p>';
                if (!empty($content['linkedin']) || !empty($content['website'])) {
                    $html .= '<p style="margin-top: 5px; font-size: 12px;">';
                    $links = array_filter([
                        !empty($content['linkedin']) ? 'LinkedIn: ' . htmlspecialchars($content['linkedin']) : '',
                        !empty($content['website']) ? 'Website: ' . htmlspecialchars($content['website']) : ''
                    ]);
                    $html .= htmlspecialchars(implode(' | ', $links));
                    $html .= '</p>';
                }
                $html .= '</div>';
                break;

            case 'summary':
                $html .= '<div class="section-title">Professional Summary</div>';
                $html .= '<div class="section-content">' . nl2br(htmlspecialchars($content['text'] ?? '')) . '</div>';
                break;

            case 'experience':
                $html .= '<div class="section-title">Work Experience</div>';
                $items = $content['items'] ?? [];
                foreach ($items as $item) {
                    $html .= '<div class="experience-item">';
                    $html .= '<div class="item-header">' . htmlspecialchars($item['job_title'] ?? '') . '</div>';
                    $html .= '<div class="item-meta">';
                    $meta = array_filter([
                        $item['company_name'] ?? '',
                        $item['location'] ?? '',
                        $this->formatDateRange(
                            $item['start_date'] ?? '',
                            $item['end_date'] ?? '',
                            (bool)($item['is_current'] ?? false)
                        )
                    ]);
                    $html .= htmlspecialchars(implode(' | ', $meta));
                    $html .= '</div>';
                    if (!empty($item['description'])) {
                        $html .= '<div class="item-description">' . nl2br(htmlspecialchars($item['description'])) . '</div>';
                    }
                    $html .= '</div>';
                }
                break;

            case 'education':
                $html .= '<div class="section-title">Education</div>';
                $items = $content['items'] ?? [];
                foreach ($items as $item) {
                    $html .= '<div class="education-item">';
                    $html .= '<div class="item-header">' . htmlspecialchars($item['degree'] ?? '') . ' - ' . htmlspecialchars($item['field_of_study'] ?? '') . '</div>';
                    $html .= '<div class="item-meta">';
                    $meta = array_filter([
                        $item['institution'] ?? '',
                        $this->formatDateRange(
                            $item['start_date'] ?? '',
                            $item['end_date'] ?? '',
                            (bool)($item['is_current'] ?? false)
                        )
                    ]);
                    $html .= htmlspecialchars(implode(' | ', $meta));
                    $html .= '</div>';
                    $html .= '</div>';
                }
                break;

            case 'skills':
                $html .= '<div class="section-title">Skills</div>';
                $items = $content['items'] ?? [];
                $html .= '<div class="skills-list">';
                foreach ($items as $item) {
                    $skillName = is_array($item) ? ($item['name'] ?? '') : $item;
                    if (!empty($skillName)) {
                        $html .= '<span class="skill-tag">' . htmlspecialchars($skillName) . '</span>';
                    }
                }
                $html .= '</div>';
                break;

            case 'languages':
                $html .= '<div class="section-title">Languages</div>';
                $items = $content['items'] ?? [];
                foreach ($items as $item) {
                    $langName = is_array($item) ? ($item['language'] ?? $item['name'] ?? '') : $item;
                    $proficiency = is_array($item) ? ($item['proficiency'] ?? '') : '';
                    if (!empty($langName)) {
                        $html .= '<div class="section-content">';
                        $html .= '<strong>' . htmlspecialchars($langName) . '</strong>';
                        if (!empty($proficiency)) {
                            $html .= ' - ' . htmlspecialchars($proficiency);
                        }
                        $html .= '</div>';
                    }
                }
                break;

                
            case 'additional':
                // Projects
                if (!empty($content['projects'])) {
                    $html .= '<div class="section-title">Projects</div>';
                    foreach ($content['projects'] as $project) {
                        $html .= '<div class="experience-item">';
                        $html .= '<div class="item-header">' . htmlspecialchars($project['title'] ?? '') . '</div>';
                        $html .= '<div class="item-meta">';
                        $meta = array_filter([
                            $project['role'] ?? '',
                            $project['url'] ?? '',
                            $this->formatDateRange($project['start_date'] ?? null, $project['end_date'] ?? null, false)
                        ]);
                        $html .= htmlspecialchars(implode(' | ', $meta));
                        $html .= '</div>';
                        if (!empty($project['description'])) {
                            $html .= '<div class="item-description">' . nl2br(htmlspecialchars($project['description'])) . '</div>';
                        }
                        $html .= '</div>';
                    }
                }
                
                // Certifications
                if (!empty($content['certifications'])) {
                    $html .= '<div class="section-title">Certifications</div>';
                    foreach ($content['certifications'] as $cert) {
                        $html .= '<div class="experience-item">';
                        $html .= '<div class="item-header">' . htmlspecialchars($cert['name'] ?? '') . '</div>';
                        $html .= '<div class="item-meta">';
                        $meta = array_filter([
                            $cert['issuer'] ?? '',
                            !empty($cert['date']) ? date('Y', strtotime($cert['date'])) : '',
                            $cert['url'] ?? ''
                        ]);
                        $html .= htmlspecialchars(implode(' | ', $meta));
                        $html .= '</div>';
                        $html .= '</div>';
                    }
                }
                break;
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Format date range for display
     */
    private function formatDateRange(?string $start, ?string $end, bool $isCurrent): string
    {
        $startFormatted = $start ? date('M Y', strtotime($start)) : '';
        $endFormatted = $isCurrent ? 'Present' : ($end ? date('M Y', strtotime($end)) : '');
        
        if (empty($startFormatted)) {
            return '';
        }
        
        return $startFormatted . ' - ' . $endFormatted;
    }

    /**
     * Get PDF download URL
     */
    public function getDownloadUrl(string $pdfUrl): string
    {
        return $pdfUrl;
    }
}
