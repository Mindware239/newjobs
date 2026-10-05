<?php

declare(strict_types=1);

namespace App\Controllers\Company;

use App\Core\Request;
use App\Core\Response;
use App\Models\Company;
use App\Models\CompanyBlog;
use App\Repositories\CompanyPortalRepository;

class CompanyController
{
    private CompanyPortalRepository $companyPortalRepository;

    public function __construct()
    {
        $this->companyPortalRepository = new CompanyPortalRepository();
    }

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
        // Fallback: if no featured match and user has applied any filter, search all companies
        $hasUserFilters = trim(implode('', [
            $filters['q'], $filters['industry'], $filters['year_from'], $filters['year_to'],
            $filters['location'], $filters['department'], $filters['experience']
        ])) !== '';
        if (empty($companies) && $hasUserFilters) {
            $companies = $model->searchCompanies($filters, 48);
        }
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
            $employer = $this->companyPortalRepository->findEmployerByCompanySlug((string)$slug);
            
            if ($employer) {
                // Create company record if it doesn't exist
                $existingCompany = $companyModel->findByEmployerId((int)$employer['id']);
                if (!$existingCompany) {
                    // Auto-create company from employer data
                    $companySlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $employer['company_name'] ?? 'company')));
                    $companySlug = trim($companySlug, '-');
                    $this->companyPortalRepository->createCompanyFromEmployer($employer, $companySlug);
                    
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
            if ($employerId > 0) {
                $jobs = $this->companyPortalRepository->getPublishedJobsByEmployerId($employerId);
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
            $reviews = $this->companyPortalRepository->getApprovedReviewsByCompanyId($companyId, 10);
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

    public function blogDetail(Request $request, Response $response): void
    {
        $companySlug = $request->param('company_slug');
        $blogSlug    = $request->param('blog_slug');

        $companyModel = new Company();
        $blogModel    = new CompanyBlog();

        $company = $companyModel->findBySlug($companySlug);
        if (!$company) {
            $response->view('errors/404', ['message' => 'Company not found']);
            return;
        }

        $blog = $blogModel->getBySlug($blogSlug);
        if (!$blog || (int)$blog['company_id'] !== (int)$company['id']) {
            $response->view('errors/404', ['message' => 'Blog post not found']);
            return;
        }

        // Initialize SEO
        $seoService = \App\Services\SeoService::getInstance();
        $seoService->resolve('blog_detail', [
            'title' => $blog['title'],
            'description' => $blog['excerpt'] ?? '',
            'image' => $blog['image'] ?? null,
            'canonical_path' => "/company/{$companySlug}/blog/{$blogSlug}"
        ]);

        // Adapt company blog data to match what blog/detail.php expects
        $post = $blog;
        $post['featured_image'] = $blog['image'];
        $post['author_name'] = $company['name'];

        $response->view('blog/detail', [
            'blog' => $post,
            'post' => $post,
            'content' => $blog['content'] ?? '',
            'company' => $company,
            'title' => $blog['title'],
            'categories' => [],
            'tags' => [],
            'related' => [],
            'latestArticles' => []
        ], 200, 'layout');
    }
}
