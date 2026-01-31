<?php

declare(strict_types=1);

namespace App\Controllers\Company;

use App\Core\Request;
use App\Core\Response;
use App\Models\Company;
use App\Models\CompanyBlog;

class CompanyController
{
    public function featured(Request $request, Response $response): void
    {
        $model = new Company();
        $model->ensureFeaturedSchema();
        $filters = [
            'q' => (string)$request->get('q', ''),
            'industry' => (string)$request->get('industry', ''),
            'year_from' => (string)$request->get('year_from', ''),
            'year_to' => (string)$request->get('year_to', ''),
            'location' => (string)$request->get('location', ''),
            'department' => (string)$request->get('department', ''),
            'experience' => (string)$request->get('experience', ''),
        ];
        $companies = $model->getFeaturedCompaniesFiltered($filters, 48);
        foreach ($companies as $idx => $co) {
            $stats = $model->getStats((int)($co['id'] ?? 0)) ?: [];
            $companies[$idx]['rating'] = (float)($stats['rating'] ?? 0);
            $companies[$idx]['reviews_count'] = (int)($stats['reviews_count'] ?? 0);
        }
        $chipCounts = $model->getFeaturedCountsByIndustries([
            'mnc' => 'MNC',
            'fintech' => 'Fintech',
            'fmcg' => 'FMCG',
            'startup' => 'Startup',
            'edtech' => 'Edtech'
        ]);

        $seoService = \App\Services\SeoService::getInstance();
        $seoService->resolve('company_featured', [
            'canonical_path' => '/company/featured',
            'title' => 'Featured Companies'
        ]);

        $response->view('company/featured', [
            'title' => 'Featured Companies',
            'companies' => $companies,
            'filters' => $filters,
            'chipCounts' => $chipCounts
        ], 200, 'layout');
    }

    public function show(Request $request, Response $response): void
    {
        $slug = $request->param('slug');
        $tab  = $request->param('tab') ?? 'snapshot';

        $companyModel = new Company();  
        $blogModel    = new CompanyBlog();      
        
        // FIND COMPANY - Try companies table first, then fallback to employers table
        $company = $companyModel->findBySlug($slug);
        
        // If not found in companies table, try to find by employer company_slug
        if (!$company) {
            $db = \App\Core\Database::getInstance();
            $employer = $db->fetchOne(
                "SELECT * FROM employers WHERE company_slug = ? LIMIT 1",
                [$slug]
            );
            
            if ($employer) {
                // Create company record if it doesn't exist
                $existingCompany = $companyModel->findByEmployerId((int)$employer['id']);
                if (!$existingCompany) {
                    // Auto-create company from employer data
                    $companySlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $employer['company_name'] ?? 'company')));
                    $companySlug = trim($companySlug, '-');
                    
                    $sql = "INSERT INTO companies (employer_id, short_name, name, slug, logo_url, website, description, industry, company_size, created_at, updated_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                    $db->execute($sql, [
                        (int)$employer['id'],
                        $employer['company_name'] ?? 'Company',
                        $employer['company_name'] ?? 'Company',
                        $companySlug,
                        $employer['logo_url'] ?? null,
                        $employer['website'] ?? null,
                        $employer['description'] ?? null,
                        $employer['industry'] ?? null,
                        $employer['size'] ?? null
                    ]);
                    
                    $company = $companyModel->findByEmployerId((int)$employer['id']);
                } else {
                    $company = $existingCompany;
                }
            }
        }

        if (!$company) {
            // Check if this is an AJAX/API request
            $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
            if (strpos($acceptHeader, 'application/json') !== false) {
                $response->json(['error' => 'Company not found'], 404);
            } else {
                $response->view('errors/404', ['message' => 'Company not found']);
            }
            return;
        }

        $companyId = (int) $company['id'];
        $employerId = (int) ($company['employer_id'] ?? 0);
        
        // Attach stats
        try {
            $stats = $companyModel->getStats($companyId) ?: [];
            $company = array_merge($company, $stats);
        } catch (\Exception $e) {
            error_log('Failed to fetch company stats: ' . $e->getMessage());
        }

        // FETCH JOBS - Get all published jobs for this company (by employer_id)
        $jobs = [];
        try {
            $db = \App\Core\Database::getInstance();
            if ($employerId > 0) {
                $jobRows = $db->fetchAll(
                    "SELECT j.*,
                     GROUP_CONCAT(
                        DISTINCT TRIM(CONCAT(
                            COALESCE(c.name, ''),
                            CASE WHEN s.name IS NOT NULL AND s.name <> '' THEN CONCAT(', ', s.name) ELSE '' END,
                            CASE WHEN cnt.name IS NOT NULL AND cnt.name <> '' THEN CONCAT(', ', cnt.name) ELSE '' END
                        ))
                        SEPARATOR ' | '
                     ) AS location_display
                     FROM jobs j
                     LEFT JOIN job_locations jl ON jl.job_id = j.id
                     LEFT JOIN cities c ON jl.city_id = c.id
                     LEFT JOIN states s ON jl.state_id = s.id
                     LEFT JOIN countries cnt ON jl.country_id = cnt.id
                     WHERE j.employer_id = :employer_id
                     AND j.status = 'published'
                     GROUP BY j.id
                     ORDER BY j.created_at DESC",
                    ['employer_id' => $employerId]
                );
                $jobs = $jobRows;
            }
        } catch (\Exception $e) {
            error_log('Failed to fetch jobs for company: ' . $e->getMessage());
        }
        
        // FETCH BLOGS - Get all published blogs for this company
        $blogs = [];
        try {
            $blogs = $blogModel->getByCompanyId($companyId);
        } catch (\Exception $e) {
            error_log('Failed to fetch blogs: ' . $e->getMessage());
        }

        // FETCH REVIEWS (latest 10) - tolerate missing table
        $reviews = [];
        try {
            $reviews = $db->fetchAll(
                "SELECT reviewer_name, rating, title, review_text, created_at
                 FROM reviews 
                 WHERE company_id = :cid 
                 AND (status = 'approved' OR status IS NULL)
                 ORDER BY created_at DESC 
                 LIMIT 10",
                ['cid' => $companyId]
            );
        } catch (\Throwable $e) {
            error_log('Company reviews fetch failed: ' . $e->getMessage());
            // Fallback to description reviews if table doesn't exist
            $desc = $company['description'] ?? '';
            $parsed = is_string($desc) ? json_decode($desc, true) : null;
            if (is_array($parsed) && !empty($parsed['reviews']) && is_array($parsed['reviews'])) {
                $reviews = $parsed['reviews'];
            }
        }

        // VALID TABS
        $validTabs = ['snapshot','why','reviews','jobs','blogs'];
        if (!in_array($tab, $validTabs)) {
            $tab = 'snapshot';
        }

        // Initialize SEO
        $seoService = \App\Services\SeoService::getInstance();
        $seoService->resolve('company_detail', [
            'company' => $company['name'] ?? 'Company',
            'company_logo' => $company['logo_url'] ?? null,
            'city' => $company['headquarters'] ?? 'India',
            'canonical_path' => '/company/' . ($company['slug'] ?? $slug)
        ]);

        // RENDER VIEW
        $response->view('company/details', [
            'company'   => $company,
            'jobs'      => $jobs,
            'blogs'     => $blogs,
            'reviews'   => is_array($reviews) ? $reviews : [],
            'activeTab' => $tab
        ]);
    }
}
