<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ResumeTemplate;

class ResumeTemplatePreviewService
{
    /**
     * Generate HTML preview for a template with sample data
     */
    public function generatePreviewHTML(ResumeTemplate $template): string
    {
        $schema = $template->getSchema();
        $sampleData = $this->getSampleData();
        
        $html = '<div class="resume-preview-template" style="font-family: Arial, sans-serif; max-width: 210mm; margin: 0 auto; background: white; padding: 20px;">';
        
        // Header
        if (in_array('header', $schema['sections'] ?? [])) {
            $html .= '<div style="border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px;">';
            $html .= '<h1 style="font-size: 28px; font-weight: bold; color: #1e293b; margin: 0 0 8px 0;">' . htmlspecialchars($sampleData['name']) . '</h1>';
            $html .= '<p style="font-size: 12px; color: #64748b; margin: 0;">';
            $html .= htmlspecialchars($sampleData['email']) . ' | ' . 
                     htmlspecialchars($sampleData['phone']) . ' | ' . 
                     htmlspecialchars($sampleData['location']);
            $html .= '</p></div>';
        }
        
        // Summary
        if (in_array('summary', $schema['sections'] ?? [])) {
            $html .= '<div style="margin-bottom: 20px;">';
            $html .= '<h2 style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 10px;">Professional Summary</h2>';
            $html .= '<p style="font-size: 12px; color: #475569; line-height: 1.6; margin: 0;">' . htmlspecialchars($sampleData['summary']) . '</p>';
            $html .= '</div>';
        }
        
        // Experience
        if (in_array('experience', $schema['sections'] ?? [])) {
            $html .= '<div style="margin-bottom: 20px;">';
            $html .= '<h2 style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 10px;">Work Experience</h2>';
            foreach ($sampleData['experience'] as $exp) {
                $html .= '<div style="margin-bottom: 15px;">';
                $html .= '<h3 style="font-size: 14px; font-weight: bold; margin: 0 0 5px 0;">' . htmlspecialchars($exp['title']) . '</h3>';
                $html .= '<p style="font-size: 11px; color: #64748b; margin: 0 0 5px 0;">' . 
                         htmlspecialchars($exp['company']) . ' | ' . htmlspecialchars($exp['period']) . '</p>';
                $html .= '<p style="font-size: 11px; color: #475569; margin: 0;">' . htmlspecialchars($exp['description']) . '</p>';
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        
        // Education
        if (in_array('education', $schema['sections'] ?? [])) {
            $html .= '<div style="margin-bottom: 20px;">';
            $html .= '<h2 style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 10px;">Education</h2>';
            foreach ($sampleData['education'] as $edu) {
                $html .= '<div style="margin-bottom: 10px;">';
                $html .= '<h3 style="font-size: 14px; font-weight: bold; margin: 0 0 3px 0;">' . htmlspecialchars($edu['degree']) . '</h3>';
                $html .= '<p style="font-size: 11px; color: #64748b; margin: 0;">' . 
                         htmlspecialchars($edu['institution']) . ' | ' . htmlspecialchars($edu['year']) . '</p>';
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        
        // Skills
        if (in_array('skills', $schema['sections'] ?? [])) {
            $html .= '<div style="margin-bottom: 20px;">';
            $html .= '<h2 style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 10px;">Skills</h2>';
            $html .= '<p style="font-size: 11px; color: #475569; margin: 0;">' . 
                     implode(' • ', array_map('htmlspecialchars', $sampleData['skills'])) . '</p>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Get sample data for template preview
     */
    private function getSampleData(): array
    {
        return [
            'name' => 'DIYA AGARWAL',
            'email' => 'd.agarwal@sample.in',
            'phone' => '+91 11 5555 3345',
            'location' => 'New Delhi, India 110034',
            'summary' => 'Customer-focused Retail Sales professional with 5+ years of experience in high-volume retail environments. Proven track record of driving sales, managing inventory, and delivering exceptional customer service.',
            'experience' => [
                [
                    'title' => 'Retail Sales Associate',
                    'company' => 'ZARA',
                    'period' => 'February 2017 - Current',
                    'description' => 'Assisted customers, managed inventory, processed transactions, and maintained store appearance.'
                ],
                [
                    'title' => 'Barista',
                    'company' => 'Dunkin\' Donuts',
                    'period' => 'March 2015 - January 2017',
                    'description' => 'Prepared beverages, handled cash transactions, and ensured customer satisfaction.'
                ]
            ],
            'education' => [
                [
                    'degree' => 'Diploma in Financial Accounting',
                    'institution' => 'Oxford Software Institute & Oxford School of English',
                    'year' => '2016'
                ]
            ],
            'skills' => [
                'Cash register operation',
                'POS system operation',
                'Sales expertise',
                'Teamwork',
                'Inventory management',
                'Customer service'
            ]
        ];
    }
}
