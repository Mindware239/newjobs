<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Core\Request;
use App\Core\Response;
use App\Models\Job;
use App\Models\Company;
use App\Models\CompanyBlog;
use App\Models\JobView;
use App\Repositories\BlogRepository;
use App\Repositories\JobRepository;
use App\Services\JobService;

class JobController
{
    private JobService $jobService;

    public function __construct()
    {
        $this->jobService = new JobService();
    }

    public function categories(Request $request, Response $response): void
    {
        $grouped = $this->jobService->getCategoriesGrouped();
        $response->view('job-categories', [
            'groupedCategories' => $grouped,
            'pageTitle' => 'Browse Jobs by Category'
        ], 200, 'layout');
    }

    public function jobsByLocation(Request $request, Response $response, array $params): void
    {
        $slug = $params['location'] ?? '';
        $page = max(1, (int)$request->get('page', 1));
        
        $data = $this->jobService->getJobsByLocation($slug, $page);
        
        if (!$data) {
            // Not in the jobs DB: all-India landing page (any state / district / town), else /jobs.
            (new LocationPagesController())->render('jobs', (string)$slug, $response);
            return;
        }

        $response->view('candidate/jobs/index', $data, 200, 'layout');
    }

    public function jobsByRoleAndLocation(Request $request, Response $response, array $params): void
    {
        $roleSlug = $params['role'] ?? '';
        $locationSlug = $params['location'] ?? '';
        $page = max(1, (int)$request->get('page', 1));

        $data = $this->jobService->getJobsByRoleAndLocation($roleSlug, $locationSlug, $page);

        if (!$data) {
            $response->redirect('/jobs');
            return;
        }

        $response->view('candidate/jobs/index', $data, 200, 'layout');
    }

    public function jobsByCategory(Request $request, Response $response, array $params): void
    {
        $slug = $params['slug'] ?? '';
        $page = max(1, (int)$request->get('page', 1));

        $data = $this->jobService->getJobsByCategory($slug, $page);

        if (!$data) {
            $response->redirect('/jobs');
            return;
        }

        $response->view('candidate/jobs/index', $data, 200, 'layout');
    }

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
        
        $jobRepository = new JobRepository();
        
        $row = $jobRepository->getJobDetailRowBySlug($slug);
        
        if (!$row) {
            $response->redirect('/jobs');
            return;
        }

        // Only live jobs are public. The posting employer and admins may preview other statuses.
        $jobStatus = (string)($row['status'] ?? '');
        $isPreview = false;
        if ($jobStatus !== 'published') {
            $viewerId = (int)($_SESSION['user_id'] ?? 0);
            $viewerRole = (string)($_SESSION['user_role'] ?? '');
            $isOwner = false;
            if ($viewerId > 0 && $viewerRole === 'employer') {
                $owner = \App\Core\Database::getInstance()->fetchOne(
                    'SELECT e.id FROM employers e WHERE e.id = :eid AND e.user_id = :uid LIMIT 1',
                    ['eid' => (int)($row['employer_id'] ?? 0), 'uid' => $viewerId]
                );
                $isOwner = $owner !== null;
            }
            if (!$isOwner && !in_array($viewerRole, ['admin', 'super_admin', 'master_admin'], true)) {
                $response->redirect('/jobs');
                return;
            }
            $isPreview = true;
        }
        $row['is_preview'] = $isPreview;

        // Prepare location display if location_names is empty
        $locationDisplay = $row['location_names'] ?? '';
        if (empty($locationDisplay) && !empty($row['locations'])) {
            $locs = json_decode($row['locations'], true);
            if (is_array($locs) && !empty($locs)) {
                $parts = array_filter([
                    $locs[0]['city'] ?? '',
                    $locs[0]['state'] ?? '',
                    $locs[0]['country'] ?? ''
                ]);
                $locationDisplay = implode(', ', $parts);
            }
        }
        $row['location_display'] = $locationDisplay ?: 'Location not specified';

        // Check global hide external jobs setting
        if ($jobRepository->isHideExternalJobsEnabled() && ($row['job_type'] ?? 'internal') === 'external') {
            $response->redirect('/jobs');
            return;
        }
        
        $jobId = (int)$row['id'];
        $employerId = (int)($row['employer_id'] ?? 0);
        $companyId = (int)($row['company_id'] ?? 0);
        
        // Get job locations
        $locationStrings = [];
        $locationRows = [];
        try {
            $locationRows = $jobRepository->getJobLocations($jobId);
            
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
            $skills = $jobRepository->getJobSkillNames($jobId);
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
                    $company = $jobRepository->getCompanyById($companyId);
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

        // External jobs posted by admin should display the job's own company branding,
        // not the admin employer/company profile identity.
        $isExternalJob = (($row['job_type'] ?? 'internal') === 'external');
        $jobCompanyName = trim((string)($row['company_name'] ?? ''));
        $jobCompanyLogo = trim((string)($row['company_logo'] ?? ''));
        if ($isExternalJob) {
            if ($jobCompanyName !== '') {
                $company['name'] = $jobCompanyName;
            }
            if ($jobCompanyLogo !== '') {
                $company['logo_url'] = $jobCompanyLogo;
            }
            // External listings may not map to an internal public company profile.
            $company['id'] = 0;
            $company['slug'] = '';
            $companyStats = [
                'rating' => 0,
                'reviews_count' => 0,
                'followers_count' => 0
            ];
            $companyBlogs = [];
        }
        
        // Get company stats (rating, reviews, followers)
        $companyStats = [
            'rating' => 0,
            'reviews_count' => 0,
            'followers_count' => 0
        ];
        if (!$isExternalJob && $companyId > 0) {
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
        if (!$isExternalJob && $companyId > 0) {
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

        // Sidebar interview blogs from repository (shared source with blog pages).
        $interviewBlogs = [];
        try {
            $interviewBlogs = (new BlogRepository())->getInterviewSidebarBlogs(10);
        } catch (\Throwable $e) {
            error_log("Error fetching interview blogs for job sidebar: " . $e->getMessage());
        }
        
        // Get other jobs from same company
        $otherJobs = [];
        if (!$isExternalJob && $employerId > 0) {
            try {
                $otherJobs = $jobRepository->getOtherPublishedJobsByEmployer($employerId, $jobId, 5);
                
                // Enrichment loop to handle fallback and formatting
                foreach ($otherJobs as &$oj) {
                    if (empty($oj['location_display'])) {
                        $locData = json_decode($oj['locations'] ?? '', true);
                        if (is_array($locData)) {
                            $locStrings = [];
                            foreach ($locData as $loc) {
                                if (is_string($loc)) {
                                    $locStrings[] = $loc;
                                } elseif (is_array($loc)) {
                                    $locStrings[] = implode(', ', array_filter([$loc['city'] ?? '', $loc['state'] ?? '', $loc['country'] ?? '']));
                                }
                            }
                            $oj['location_display'] = !empty($locStrings) ? implode(' | ', $locStrings) : 'Location not specified';
                        } else {
                            $oj['location_display'] = !empty($oj['locations']) ? $oj['locations'] : 'Location not specified';
                        }
                    }
                }
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
                        ->where('status', '!=', 'withdrawn')
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

        // Track public job views (guest + logged-in) once per session/day
        try {
            $sessionKey = 'job_view_logged_' . $jobId . '_' . date('Ymd');
            if (empty($_SESSION[$sessionKey])) {
                $jobRepository->logPublicJobView(
                    $jobId,
                    isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null,
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    (string)($_SERVER['HTTP_USER_AGENT'] ?? '')
                );
                $_SESSION[$sessionKey] = 1;
            }
        } catch (\Throwable $e) {
            // Graceful fallback if analytics table is missing
            error_log("Job view log insert skipped: " . $e->getMessage());
        }

        // Dynamic sidebar metrics
        $viewsCount = 0;
        $applicationsCount = 0;
        $shortlistedCount = 0;
        try {
            $counts = $jobRepository->getApplicationMetrics($jobId);
            $applicationsCount = (int)($counts['applications_count'] ?? 0);
            $shortlistedCount = (int)($counts['shortlisted_count'] ?? 0);
        } catch (\Throwable $e) {
            error_log("Applications metrics fetch failed: " . $e->getMessage());
        }
        try {
            $viewsCount = $jobRepository->getJobViewsLogCount($jobId);
        } catch (\Throwable $e) {
            // Fallback to legacy table
            try {
                $viewsCount = (int) JobView::where('job_id', '=', $jobId)->count();
            } catch (\Throwable $ignored) {
                $viewsCount = 0;
            }
        }
        
        // Format job data
        $jobData = $row;
        $jobData['location_display'] = !empty($locationStrings) ? implode(' | ', $locationStrings) : (!empty($row['locations']) ? $row['locations'] : 'Location not specified');
        $jobData['skills'] = $skills;
        $jobData['is_bookmarked'] = $isBookmarked;
        $jobData['has_applied'] = $hasApplied;
        $jobData['views_count'] = $viewsCount;
        $jobData['applications_count'] = $applicationsCount;
        $jobData['shortlisted_count'] = $shortlistedCount;
        
        // Format employment type
        $employmentType = $jobData['employment_type'] ?? 'full_time';
        $employmentTypeMap = [
            'full_time' => 'Full-time',
            'part_time' => 'Part-time',
            'contract' => 'Contract',
            'internship' => 'Internship',
            'freelance' => 'Freelance',
            'one_time' => 'One-time Job',
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
            'interviewBlogs' => $interviewBlogs,
            'otherJobs' => $otherJobs,
            'isLoggedIn' => $userId !== null,
            'isFollowing' => $isFollowing,
            'userId' => $userId,
            'candidateId' => $candidateId,
            'isPreview' => $isPreview,
            'previewStatus' => $jobStatus,
        ]);
    }
}

