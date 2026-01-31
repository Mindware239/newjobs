<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Core\Request;
use App\Core\Response;
use App\Models\Job;
use App\Models\Company;
use App\Models\CompanyBlog;
use App\Models\JobView;

class JobController
{
    /**
     * Public job detail page - no login required
     */
    public function show(Request $request, Response $response): void
    {
        $slug = $request->param('slug') ?? '';
        
        if (empty($slug)) {
            $response->redirect('/');
            return;
        }
        
        $db = \App\Core\Database::getInstance();
        
        // Get job with company information
        $sql = "SELECT j.*, 
                       e.company_name, e.description as company_description, 
                       e.logo_url as company_logo, e.website as company_website, 
                       e.company_slug, e.id as employer_id,
                       c.id as company_id, c.name as company_full_name, c.slug as company_slug_from_companies,
                       c.banner_url, c.logo_url as company_logo_from_companies,
                       c.description as company_about, c.ceo_name, c.ceo_photo,
                       c.headquarters, c.founded_year, c.company_size, c.revenue
                FROM jobs j
                LEFT JOIN employers e ON j.employer_id = e.id
                LEFT JOIN companies c ON c.employer_id = e.id
                WHERE j.slug = :slug AND j.status = 'published'";
        
        $row = $db->fetchOne($sql, ['slug' => $slug]);
        
        if (!$row || empty($row['id'])) {
            $response->redirect('/');
            return;
        }
        
        $jobId = (int)$row['id'];
        $employerId = (int)($row['employer_id'] ?? 0);
        $companyId = (int)($row['company_id'] ?? 0);
        
        // Get job locations
        $locationStrings = [];
        $locationRows = [];
        try {
            $locationRows = $db->fetchAll(
                "SELECT 
                    COALESCE(c.name, jl.city) as city, 
                    c.slug as city_slug,
                    COALESCE(s.name, jl.state) as state, 
                    s.slug as state_slug,
                    COALESCE(cnt.name, jl.country) as country, 
                    cnt.slug as country_slug,
                    jl.latitude, jl.longitude 
                 FROM job_locations jl 
                 LEFT JOIN cities c ON jl.city_id = c.id
                 LEFT JOIN states s ON jl.state_id = s.id
                 LEFT JOIN countries cnt ON jl.country_id = cnt.id
                 WHERE jl.job_id = :job_id",
                ['job_id' => $jobId]
            );
            
            foreach ($locationRows as $locRow) {
                $locParts = array_filter([
                    trim($locRow['city'] ?? ''),
                    trim($locRow['state'] ?? ''),
                    trim($locRow['country'] ?? '')
                ]);
                if (!empty($locParts)) {
                    $locationStrings[] = implode(', ', $locParts);
                }
            }
        } catch (\Exception $e) {
            error_log("Error getting job locations: " . $e->getMessage());
        }
        
        // Get job skills
        $skills = [];
        try {
            $skills = $db->fetchAll(
                "SELECT s.name FROM job_skills js 
                 INNER JOIN skills s ON js.skill_id = s.id 
                 WHERE js.job_id = :job_id",
                ['job_id' => $jobId]
            );
        } catch (\Exception $e) {
            error_log("Error getting job skills: " . $e->getMessage());
        }
        
        // Get company information (from companies table if available, else from employers)
        $company = [];
        if ($companyId > 0) {
            try {
                $companyModel = new Company();
                $companyObj = $companyModel->findBySlug($row['company_slug_from_companies'] ?? '');
                if ($companyObj) {
                    $company = is_array($companyObj) ? $companyObj : ($companyObj->attributes ?? []);
                } elseif ($companyId > 0) {
                    $company = $db->fetchOne("SELECT * FROM companies WHERE id = :id", ['id' => $companyId]);
                    if (!$company) {
                        $company = [];
                    }
                }
            } catch (\Exception $e) {
                error_log("Error fetching company: " . $e->getMessage());
                $company = [];
            }
        }
        
        // If no company from companies table, use employer data from SQL JOIN
        if (empty($company) || !is_array($company)) {
            $company = [
                'id' => $companyId,
                'name' => $row['company_full_name'] ?? $row['company_name'] ?? 'Company',
                'slug' => $row['company_slug_from_companies'] ?? $row['company_slug'] ?? '',
                'banner_url' => $row['banner_url'] ?? null,
                'logo_url' => $row['company_logo_from_companies'] ?? $row['company_logo'] ?? null,
                'description' => $row['company_about'] ?? $row['company_description'] ?? '',
                'ceo_name' => $row['ceo_name'] ?? null,
                'ceo_photo' => $row['ceo_photo'] ?? null,
                'headquarters' => $row['headquarters'] ?? null,
                'founded_year' => $row['founded_year'] ?? null,
                'company_size' => $row['company_size'] ?? null,
                'revenue' => $row['revenue'] ?? null,
                'website' => $row['company_website'] ?? null
            ];
        }
        
        // Get company stats (rating, reviews, followers)
        $companyStats = [
            'rating' => 0,
            'reviews_count' => 0,
            'followers_count' => 0
        ];
        if ($companyId > 0) {
            try {
                $companyModel = new Company();
                $stats = $companyModel->getStats($companyId);
                if ($stats && is_array($stats)) {
                    $companyStats = $stats;
                }
            } catch (\Exception $e) {
                error_log("Error fetching company stats: " . $e->getMessage());
            }
        }
        
        // Get company blogs (published only)
        $companyBlogs = [];
        if ($companyId > 0) {
            try {
                $blogModel = new CompanyBlog();
                $blogs = $blogModel->getByCompanyId($companyId);
                // Convert to array if it's a collection of objects
                if (is_array($blogs)) {
                    $companyBlogs = array_map(function($blog) {
                        return is_array($blog) ? $blog : ($blog->attributes ?? []);
                    }, $blogs);
                }
            } catch (\Exception $e) {
                error_log("Error fetching company blogs: " . $e->getMessage());
            }
        }
        
        // Get other jobs from same company
        $otherJobs = [];
        if ($employerId > 0) {
            try {
                $otherJobs = $db->fetchAll(
                    "SELECT j.*, 
                     GROUP_CONCAT(DISTINCT CONCAT(COALESCE(c.name, ''), ', ', COALESCE(s.name, ''), ', ', COALESCE(cnt.name, '')) SEPARATOR ' | ') as location_display
                     FROM jobs j
                     LEFT JOIN job_locations jl ON jl.job_id = j.id
                     LEFT JOIN cities c ON jl.city_id = c.id
                     LEFT JOIN states s ON jl.state_id = s.id
                     LEFT JOIN countries cnt ON jl.country_id = cnt.id
                     WHERE j.employer_id = :employer_id 
                     AND j.status = 'published'
                     AND j.id != :current_job_id
                     GROUP BY j.id
                     ORDER BY j.created_at DESC
                     LIMIT 5",
                    ['employer_id' => $employerId, 'current_job_id' => $jobId]
                );
            } catch (\Exception $e) {
                error_log("Error fetching other jobs: " . $e->getMessage());
            }
        }
        
        // Check if user is logged in (optional - for bookmark/apply buttons)
        $userId = $_SESSION['user_id'] ?? null;
        $candidateId = null;
        $isBookmarked = false;
        $hasApplied = false;
        $isFollowing = false;
        
        if ($userId) {
            try {
                $candidate = \App\Models\Candidate::where('user_id', '=', (int)$userId)->first();
                if ($candidate) {
                    $candidateId = $candidate->attributes['id'] ?? $candidate->id ?? null;
                    
                    // Check bookmark
                    if ($candidateId) {
                        $isBookmarked = \App\Models\JobBookmark::where('candidate_id', '=', $candidateId)
                            ->where('job_id', '=', $jobId)
                            ->first() !== null;
                    }
                    
                    // Check application
                    $hasApplied = \App\Models\Application::where('candidate_user_id', '=', (int)$userId)
                        ->where('job_id', '=', $jobId)
                        ->first() !== null;
                    
                    // Check if following company
                    if ($companyId > 0 && $candidateId) {
                        try {
                            $isFollowing = \App\Models\CompanyFollower::isFollowing($candidateId, $companyId);
                        } catch (\Exception $e) {
                            error_log("Error checking follow status: " . $e->getMessage());
                            $isFollowing = false;
                        }
                    }
                }
            } catch (\Exception $e) {
                error_log("Error checking user status: " . $e->getMessage());
            }
        }
        
        if ($candidateId) {
            $today = date('Y-m-d');
            $existing = JobView::where('candidate_id', '=', (int)$candidateId)
                ->where('job_id', '=', $jobId)
                ->where('viewed_at', '>=', $today)
                ->first();
            if (!$existing) {
                $view = new JobView();
                $view->fill([
                    'candidate_id' => (int)$candidateId,
                    'job_id' => $jobId
                ]);
                $view->save();
            }
        }
        
        // Format job data
        $jobData = $row;
        $jobData['location_display'] = !empty($locationStrings) ? implode(' | ', $locationStrings) : 'Location not specified';
        $jobData['skills'] = $skills;
        $jobData['is_bookmarked'] = $isBookmarked;
        $jobData['has_applied'] = $hasApplied;
        
        // Format employment type
        $employmentType = $jobData['employment_type'] ?? 'full_time';
        $employmentTypeMap = [
            'full_time' => 'Full-time',
            'part_time' => 'Part-time',
            'contract' => 'Contract',
            'internship' => 'Internship',
            'freelance' => 'Freelance',
            'temporary' => 'Temporary'
        ];
        $jobData['employment_type_display'] = $employmentTypeMap[$employmentType] ?? ucfirst(str_replace('_', ' ', $employmentType));
        
        // Format salary
        $currency = $jobData['currency'] ?? 'INR';
        $symbol = $currency === 'USD' ? '$' : ($currency === 'EUR' ? '€' : ($currency === 'GBP' ? '£' : '₹'));
        $jobData['currency_symbol'] = $symbol;
        
        // Initialize SEO
        $seoService = \App\Services\SeoService::getInstance();
        $skillNames = array_map(function($s) {
            return is_array($s) ? ($s['name'] ?? '') : (is_string($s) ? $s : '');
        }, $skills);
        $seoService->resolve('job_detail', [
            'job_title' => $jobData['title'] ?? 'Job',
            'company' => $company['name'] ?? ($jobData['company_name'] ?? 'Confidential'),
            'city' => $locationRows[0]['city'] ?? '', // First location
            'state' => $locationRows[0]['state'] ?? '',
            'country' => $locationRows[0]['country'] ?? '',
            'salary' => ($jobData['salary_min'] ? ($jobData['currency'] . ' ' . $jobData['salary_min']) : 'Negotiable'),
            'job' => $jobData, // For JSON-LD
            'company_logo' => $company['logo_url'] ?? ($jobData['company_logo'] ?? null),
            'canonical_path' => '/job/' . ($jobData['slug'] ?? ''),
            'skills' => array_values(array_filter($skillNames))
        ]);

        $response->view('front/job/show', [
            'title' => ($jobData['title'] ?? 'Job') . ' - ' . ($company['name'] ?? 'Company'),
            'job' => $jobData,
            'locationRows' => $locationRows,
            'company' => $company,
            'companyStats' => $companyStats,
            'companyBlogs' => $companyBlogs,
            'otherJobs' => $otherJobs,
            'isLoggedIn' => $userId !== null,
            'isFollowing' => $isFollowing,
            'userId' => $userId,
            'candidateId' => $candidateId
        ]);
    }
}

