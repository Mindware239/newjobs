<?php

use App\Core\Router;
use App\Controllers\Front\AuthController;
use App\Core\Request;
use App\Core\Response;
use App\Controllers\Front\ContactController;
use App\Controllers\Front\AboutController;
use App\Controllers\Front\BlogController;
use App\Controllers\Interview\InterviewRoomController;
use App\Controllers\SocialServiceController;
use App\Middleware\AuthMiddleware;
use App\Controllers\NotificationController;
use App\Services\NotificationService;

$router = \App\Core\Router::getInstance();

// Notification Tracking
$router->get('/notifications/track/open', [NotificationController::class, 'trackOpen']);
$router->get('/notifications/track/click', [NotificationController::class, 'trackClick']);

$router->get('/interviews/{id}/room', [InterviewRoomController::class, 'room'], []);
$router->get('/interviews/{id}/state', [InterviewRoomController::class, 'state'], []);
$router->post('/interviews/{id}/start', [InterviewRoomController::class, 'start'], []);
$router->post('/interviews/{id}/end', [InterviewRoomController::class, 'end'], []);
$router->post('/interviews/{id}/events', [InterviewRoomController::class, 'event'], []);
$router->get('/interviews/{id}/analytics', [InterviewRoomController::class, 'analytics'], []);
// Secure join via token (requires login; validates role + user)
$router->get('/interview/join', [InterviewRoomController::class, 'joinWithToken'], []);

// Auth Routes
$router->get('/verify-account', [AuthController::class, 'verifyAccount'], []);
$router->post('/verify-account', [AuthController::class, 'processVerification'], []);

// SEO Routes
use App\Controllers\SEOController;
$router->get('/sitemap.xml', [SEOController::class, 'index']);
$router->get('/sitemap-main.xml', [SEOController::class, 'main']);
$router->get('/sitemap-jobs.xml', [SEOController::class, 'jobs']);
$router->get('/sitemap-countries.xml', [SEOController::class, 'countries']);
$router->get('/sitemap-states.xml', [SEOController::class, 'states']);
$router->get('/sitemap-cities.xml', [SEOController::class, 'cities']);
$router->get('/sitemap-states.xml', [SEOController::class, 'states']);
$router->get('/sitemap-categories.xml', [SEOController::class, 'categories']);
$router->get('/sitemap-skills.xml', [SEOController::class, 'skills']);
$router->get('/sitemap-companies.xml', [SEOController::class, 'companies']);
$router->get('/robots.txt', [SEOController::class, 'robots']);

// Job Categories Page
$router->get('/job-categories', function(Request $request, Response $response) {
    $db = \App\Core\Database::getInstance();
    
    // Fetch all active categories with job counts
    $sql = "SELECT 
            jc.name,
            jc.slug,
            COUNT(DISTINCT j.id) as count
        FROM job_categories jc
        LEFT JOIN jobs j ON j.category = jc.name AND j.status = 'published'
        WHERE jc.is_active = 1
        GROUP BY jc.id, jc.name, jc.slug
        ORDER BY jc.name ASC";
        
    $categories = $db->fetchAll($sql);
    
    // Group by first letter
    $grouped = [];
    foreach ($categories as $cat) {
        $name = $cat['name'] ?? '';
        $firstLetter = strtoupper(substr($name, 0, 1));
        if (!ctype_alpha($firstLetter)) $firstLetter = '#';
        $grouped[$firstLetter][] = $cat;
    }
    
    $response->view('job-categories', [
        'groupedCategories' => $grouped,
        'pageTitle' => 'Browse Jobs by Category'
    ], 200, 'layout');
});

// Home page
$router->get('/', function(Request $request, Response $response) {
    // Redirect logged-in users to their dashboard
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
        if ($_SESSION['user_role'] === 'candidate') {
            $response->redirect('/candidate/dashboard');
            return;
        } elseif ($_SESSION['user_role'] === 'employer') {
            $response->redirect('/employer/dashboard');
            return;
        } elseif ($_SESSION['user_role'] === 'admin') {
            $response->redirect('/admin/dashboard');
            return;
        }
    }

    $db = \App\Core\Database::getInstance();
    
    // Fetch recent published jobs (limit 6 for home page)
    $recentJobs = [];
    try {
        $sql = "SELECT DISTINCT j.*, e.company_name, e.logo_url as company_logo, e.industry, j.slug
                FROM jobs j
                LEFT JOIN employers e ON j.employer_id = e.id
                WHERE j.status = 'published'
                ORDER BY j.created_at DESC
                LIMIT 6";
        
        $results = $db->fetchAll($sql);
        
        // Enrich job data similar to JobController
        foreach ($results as $row) {
            if (empty($row)) continue;
            
            $jobData = $row;
            
            // Ensure job ID and slug
            $jobData['id'] = (int)($row['id'] ?? 0);
            if (empty($jobData['slug']) && !empty($jobData['title'])) {
                $jobData['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $jobData['title'])));
            }
            
            // Get job locations
            $locationStrings = [];
            try {
                $jobId = $jobData['id'];
                if ($jobId) {
                    $locationRows = $db->fetchAll(
                        "SELECT c.name as city, s.name as state, cnt.name as country 
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
                }
            } catch (\Exception $e) {
                error_log("Error getting job locations: " . $e->getMessage());
            }
            
            if (empty($locationStrings) && !empty($jobData['locations'])) {
                $locationsJson = json_decode($jobData['locations'], true);
                if (is_array($locationsJson)) {
                    foreach ($locationsJson as $loc) {
                        if (is_string($loc)) {
                            $locationStrings[] = $loc;
                        } elseif (is_array($loc)) {
                            $locParts = array_filter([
                                $loc['city'] ?? '',
                                $loc['state'] ?? '',
                                $loc['country'] ?? ''
                            ]);
                            if (!empty($locParts)) {
                                $locationStrings[] = implode(', ', $locParts);
                            }
                        }
                    }
                }
            }
            $jobData['location_display'] = !empty($locationStrings) 
                ? implode(' | ', $locationStrings) 
                : ($jobData['is_remote'] == 1 ? 'Remote' : 'Location not specified');
            
            // Format employment type
            $employmentType = $jobData['employment_type'] ?? 'full_time';
            $employmentTypeMap = [
                'full_time' => 'Full-time',
                'part_time' => 'Part-time',
                'contract' => 'Contract',
                'internship' => 'Internship',
                'freelance' => 'Freelance'
            ];
            $jobData['employment_type_display'] = $employmentTypeMap[$employmentType] ?? ucfirst(str_replace('_', ' ', $employmentType));
            
            // Format salary
            $jobData['salary_min'] = isset($jobData['salary_min']) && $jobData['salary_min'] !== null ? (int)$jobData['salary_min'] : null;
            $jobData['salary_max'] = isset($jobData['salary_max']) && $jobData['salary_max'] !== null ? (int)$jobData['salary_max'] : null;
            $jobData['currency'] = $jobData['currency'] ?? 'INR';
            
            // Format created date
            if (!empty($jobData['created_at'])) {
                $createdTime = strtotime($jobData['created_at']);
                $now = time();
                $diff = $now - $createdTime;
                $minutes = floor($diff / 60);
                $hours = floor($diff / 3600);
                $days = floor($diff / 86400);
                
                if ($minutes < 1) {
                    $jobData['time_ago'] = 'Just now';
                } elseif ($minutes < 60) {
                    $jobData['time_ago'] = $minutes . ' min ago';
                } elseif ($hours < 24) {
                    $jobData['time_ago'] = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
                } else {
                    $jobData['time_ago'] = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
                }
            } else {
                $jobData['time_ago'] = 'Recently';
            }
            
            // Ensure all fields have defaults
            $jobData['company_name'] = $jobData['company_name'] ?? 'Company Name Not Available';
            $jobData['company_logo'] = $jobData['company_logo'] ?? null;
            $jobData['industry'] = $jobData['industry'] ?? 'Industry';
            $jobData['is_remote'] = (int)($jobData['is_remote'] ?? 0);
            
            $recentJobs[] = $jobData;
        }
    } catch (\Exception $e) {
        error_log("Error fetching recent jobs for home page: " . $e->getMessage());
    }
    
    // Fetch categories for Browse by Category section
    $categories = [];
    try {
        $categoriesSql = "SELECT 
                jc.id,
                jc.name,
                jc.slug,
                jc.image,
                COUNT(DISTINCT j.id) as count
            FROM job_categories jc
            LEFT JOIN jobs j ON j.category = jc.name AND j.status = 'published'
            WHERE jc.is_active = 1
            GROUP BY jc.id, jc.name, jc.slug, jc.image
            HAVING count > 0
            ORDER BY jc.sort_order ASC, count DESC
            LIMIT 10";
        $categories = $db->fetchAll($categoriesSql);
    } catch (\Exception $e) {
        error_log("Error fetching categories: " . $e->getMessage());
    }
    
    // Fetch stats
    $stats = [];
    try {
        $stats['jobs'] = (int)($db->fetchOne("SELECT COUNT(*) as total FROM jobs WHERE status = 'published'")['total'] ?? 0);
        $stats['candidates'] = (int)($db->fetchOne("SELECT COUNT(*) as total FROM candidates")['total'] ?? 0);
        $stats['companies'] = (int)($db->fetchOne("SELECT COUNT(*) as total FROM employers WHERE verified = 1")['total'] ?? 0);
    } catch (\Exception $e) {
        error_log("Error fetching stats: " . $e->getMessage());
        $stats = ['jobs' => 25850, 'candidates' => 10250, 'companies' => 18400];
    }
    
    $clientTestimonials = [];
    $candidateTestimonials = [];
    $homeBlogs = [];
    $locations = [];
    $employerLogos = [];
    $typedRoles = [];
    try {
        $clientTestimonials = $db->fetchAll("SELECT * FROM testimonials WHERE testimonial_type = 'client' AND is_active = 1 ORDER BY created_at DESC LIMIT 12");
        $candidateTestimonials = $db->fetchAll("SELECT * FROM testimonials WHERE testimonial_type = 'candidate' AND is_active = 1 ORDER BY created_at DESC LIMIT 12");
        $homeBlogs = $db->fetchAll("
            SELECT b.*, bcj.category_name
            FROM blogs b
            LEFT JOIN (
                SELECT bcm.blog_id, MIN(bc.name) AS category_name
                FROM blog_category_map bcm
                INNER JOIN blog_categories bc ON bc.id = bcm.category_id
                GROUP BY bcm.blog_id
            ) bcj ON bcj.blog_id = b.id
            WHERE b.published_at IS NOT NULL
            ORDER BY b.is_featured DESC, b.published_at DESC
            LIMIT 8
        ");
        $locations = $db->fetchAll("
            SELECT DISTINCT
                TRIM(CONCAT_WS(', ',
                    NULLIF(c.name, ''),
                    NULLIF(s.name, ''),
                    NULLIF(cnt.name, '')
                )) AS display_name,
                COALESCE(c.name, '') AS city,
                COALESCE(s.name, '') AS state,
                COALESCE(cnt.name, '') AS country
            FROM job_locations jl
            LEFT JOIN cities c ON jl.city_id = c.id
            LEFT JOIN states s ON jl.state_id = s.id
            LEFT JOIN countries cnt ON jl.country_id = cnt.id
            WHERE (c.name IS NOT NULL AND c.name <> '')
               OR (s.name IS NOT NULL AND s.name <> '')
               OR (cnt.name IS NOT NULL AND cnt.name <> '')
            ORDER BY cnt.name, s.name, c.name
            LIMIT 50
        ");
        $employerLogos = $db->fetchAll("
            SELECT company_name, logo_url
            FROM employers
            WHERE (logo_url IS NOT NULL AND logo_url <> '')
            ORDER BY verified DESC, created_at DESC
            LIMIT 12
        ");
        $typedRoles = [];
        foreach ($recentJobs as $j) {
            $t = trim((string)($j['title'] ?? ''));
            if ($t !== '') $typedRoles[] = $t;
        }
        if (count($typedRoles) < 5) {
            $fallback = ['Blockchain Engineer','Data Scientist','Frontend Developer','Backend Developer','DevOps Engineer','Mobile Developer'];
            $typedRoles = array_values(array_unique(array_merge($typedRoles, $fallback)));
        } else {
            $typedRoles = array_values(array_unique($typedRoles));
        }
    } catch (\Throwable $t) {}
    
    // Initialize SEO
    $seo = \App\Services\SeoService::getInstance()->resolve('home', [
        'job_count' => $stats['jobs'] ?? 0,
        'city' => 'India' // Default context
    ]);

    $response->view('home', [
        'title' => 'Job Portal', // Will be overridden by SEO service
        'jobs' => $recentJobs,
        'categories' => $categories,
        'stats' => $stats,
        'testimonials_client' => $clientTestimonials,
        'testimonials_candidate' => $candidateTestimonials,
        'blogs' => $homeBlogs,
        'locations' => $locations,
        'employerLogos' => $employerLogos,
        'typedRoles' => $typedRoles,
        'seo' => $seo
    ], 200, 'layout');
});

// Dynamic SEO Routes

// Jobs in Location (Country/State/City): /jobs-in-{location}
$router->get('/jobs-in-{location}', function(Request $request, Response $response, array $params) {
    $slug = $params['location'];
    $synonyms = [
        'new-delhi' => 'delhi',
        'gurgaon' => 'gurugram',
        'bangalore' => 'bengaluru',
        'bombay' => 'mumbai',
        'madras' => 'chennai',
    ];
    $slugCanonical = $synonyms[$slug] ?? $slug;
    $db = \App\Core\Database::getInstance();
    
    try {
        // Try City
        $locationType = 'city';
        $location = $db->fetchOne("
            SELECT c.*, s.name as state_name 
            FROM cities c 
            LEFT JOIN states s ON c.state_id = s.id 
            WHERE c.slug = :slug OR c.slug = :slug_canonical OR c.name LIKE :name_like
        ", ['slug' => $slug, 'slug_canonical' => $slugCanonical, 'name_like' => str_replace('-', ' ', $slug)]);

        if (!$location) {
            // Try State
            $locationType = 'state';
            $location = $db->fetchOne("SELECT * FROM states WHERE slug = :slug OR name LIKE :name_like", ['slug' => $slug, 'name_like' => str_replace('-', ' ', $slug)]);
        }

        if (!$location) {
            // Try Country
            $locationType = 'country';
            $location = $db->fetchOne("SELECT * FROM countries WHERE slug = :slug OR name LIKE :name_like", ['slug' => $slug, 'name_like' => str_replace('-', ' ', $slug)]);
        }

        if (!$location) {
            $response->redirect('/jobs'); 
            return;
        }

        $locationName = $location['name'];
        $whereClause = '';
        $params = [];

        if ($locationType === 'city') {
            $whereClause = "jl.city_id = :loc_id";
            $params['loc_id'] = $location['id'];
        } elseif ($locationType === 'state') {
            $whereClause = "jl.state_id = :loc_id";
            $params['loc_id'] = $location['id'];
        } else {
            $whereClause = "jl.country_id = :loc_id";
            $params['loc_id'] = $location['id'];
        }

        // Count jobs
        $jobCount = $db->fetchOne(
            "SELECT COUNT(*) as cnt FROM job_locations jl 
             JOIN jobs j ON j.id = jl.job_id 
             WHERE $whereClause AND j.status = 'published'", 
            $params
        )['cnt'] ?? 0;

        // Top job titles in this location (for SEO keywords)
        $topTitlesRows = $db->fetchAll(
            "SELECT j.title, COUNT(*) as cnt 
             FROM jobs j 
             JOIN job_locations jl ON j.id = jl.job_id
             WHERE $whereClause AND j.status = 'published'
             GROUP BY j.title
             ORDER BY cnt DESC
             LIMIT 5",
            $params
        );
        $topTitles = array_values(array_filter(array_map(fn($r) => $r['title'] ?? '', $topTitlesRows)));

        // SEO
        \App\Services\SeoService::getInstance()->resolve('location_jobs', [
            'location' => $locationName,
            'type' => $locationType,
            'job_count' => $jobCount,
            'top_titles' => $topTitles
        ]);
        
        // Build Breadcrumbs
        $breadcrumbs = [];
        $breadcrumbs[] = ['name' => 'Home', 'url' => '/'];
        $breadcrumbs[] = ['name' => 'Jobs', 'url' => '/jobs'];

        if ($locationType === 'city') {
             $cityFull = $db->fetchOne("
                SELECT c.name as city_name, c.slug as city_slug,
                       s.name as state_name, s.slug as state_slug,
                       cnt.name as country_name, cnt.slug as country_slug
                FROM cities c
                LEFT JOIN states s ON c.state_id = s.id
                LEFT JOIN countries cnt ON s.country_id = cnt.id
                WHERE c.id = :id
             ", ['id' => $location['id']]);
             
             if ($cityFull) {
                 if (!empty($cityFull['country_name'])) {
                     $breadcrumbs[] = ['name' => $cityFull['country_name'], 'url' => '/jobs-in-' . $cityFull['country_slug']];
                 }
                 if (!empty($cityFull['state_name'])) {
                     $breadcrumbs[] = ['name' => $cityFull['state_name'], 'url' => '/jobs-in-' . $cityFull['state_slug']];
                 }
                 $breadcrumbs[] = ['name' => $cityFull['city_name'], 'url' => '/jobs-in-' . $cityFull['city_slug']];
             }
        } elseif ($locationType === 'state') {
             $stateFull = $db->fetchOne("
                SELECT s.name as state_name, s.slug as state_slug,
                       cnt.name as country_name, cnt.slug as country_slug
                FROM states s
                LEFT JOIN countries cnt ON s.country_id = cnt.id
                WHERE s.id = :id
             ", ['id' => $location['id']]);

             if ($stateFull) {
                 if (!empty($stateFull['country_name'])) {
                     $breadcrumbs[] = ['name' => $stateFull['country_name'], 'url' => '/jobs-in-' . $stateFull['country_slug']];
                 }
                 $breadcrumbs[] = ['name' => $stateFull['state_name'], 'url' => '/jobs-in-' . $stateFull['state_slug']];
             }
        } else {
             $breadcrumbs[] = ['name' => $location['name'], 'url' => '/jobs-in-' . $location['slug']];
        }

        // Pagination
        $page = max(1, (int)$request->get('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Fetch jobs (paginated) - inline numeric LIMIT/OFFSET to avoid binding issues
        $jobs = $db->fetchAll(
            "SELECT j.*, e.company_name, e.logo_url as company_logo FROM jobs j 
             JOIN job_locations jl ON j.id = jl.job_id 
             JOIN employers e ON j.employer_id = e.id
             WHERE $whereClause AND j.status = 'published'
             ORDER BY j.created_at DESC
             LIMIT " . (int)$perPage . " OFFSET " . (int)$offset,
            $params
        );

        // Add is_bookmarked field (default false for guests)
        foreach ($jobs as &$job) {
            $job['is_bookmarked'] = false;
            // Ensure numeric fields are numbers for JS
            $job['salary_min'] = (int)($job['salary_min'] ?? 0);
            $job['salary_max'] = (int)($job['salary_max'] ?? 0);
            $job['is_remote'] = (int)($job['is_remote'] ?? 0);
            $locRows = $db->fetchAll(
                "SELECT c.name as city, s.name as state, cnt.name as country
                 FROM job_locations jl
                 LEFT JOIN cities c ON jl.city_id = c.id
                 LEFT JOIN states s ON jl.state_id = s.id
                 LEFT JOIN countries cnt ON jl.country_id = cnt.id
                 WHERE jl.job_id = :jid",
                ['jid' => $job['id']]
            );
            $locStrings = [];
            foreach ($locRows as $lr) {
                $parts = array_filter([trim($lr['city'] ?? ''), trim($lr['state'] ?? ''), trim($lr['country'] ?? '')]);
                if (!empty($parts)) $locStrings[] = implode(', ', $parts);
            }
            $job['location_display'] = !empty($locStrings) ? implode(' | ', $locStrings) : ($job['is_remote'] == 1 ? 'Remote' : 'Location not specified');
        }

        $response->view('candidate/jobs/index', [
            'jobs' => $jobs,
            'filters' => ['location' => $locationName],
            'pageTitle' => "Jobs in $locationName",
            'breadcrumbs' => $breadcrumbs,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => (int)$jobCount,
                'total_pages' => max(1, (int)ceil($jobCount / $perPage))
            ]
        ], 200, 'layout');

    } catch (\Exception $e) {
        error_log("Error in location jobs route: " . $e->getMessage());
        $response->redirect('/jobs');
    }
});

// Role/Skill Jobs in Location: /{role}-jobs-in-{location}
$router->get('/{role}-jobs-in-{location}', function(Request $request, Response $response) {
    $params = $request->getParams();
    $roleSlug = $params['role'];
    $locationSlug = $params['location'];
    $synonyms = [
        'new-delhi' => 'delhi',
        'gurgaon' => 'gurugram',
        'bangalore' => 'bengaluru',
        'bombay' => 'mumbai',
        'madras' => 'chennai',
    ];
    $locationSlugCanonical = $synonyms[$locationSlug] ?? $locationSlug;
    
    $db = \App\Core\Database::getInstance();
    
    try {
        // Resolve Location (City -> State -> Country)
        $locationType = 'city';
        $location = $db->fetchOne("SELECT id, name FROM cities WHERE slug = :slug OR slug = :slug_canonical OR name LIKE :name_like", ['slug' => $locationSlug, 'slug_canonical' => $locationSlugCanonical, 'name_like' => str_replace('-', ' ', $locationSlug)]);
        if (!$location) {
            $locationType = 'state';
            $location = $db->fetchOne("SELECT id, name FROM states WHERE slug = :slug OR name LIKE :name_like", ['slug' => $locationSlug, 'name_like' => str_replace('-', ' ', $locationSlug)]);
        }
        if (!$location) {
            $locationType = 'country';
            $location = $db->fetchOne("SELECT id, name FROM countries WHERE slug = :slug OR name LIKE :name_like", ['slug' => $locationSlug, 'name_like' => str_replace('-', ' ', $locationSlug)]);
        }

        if (!$location) {
            $response->redirect('/jobs');
            return;
        }

        // Check if role is a Skill or Category or just a string
        $skill = $db->fetchOne("SELECT * FROM skills WHERE slug = :slug", ['slug' => $roleSlug]);
        $roleName = $skill ? $skill['name'] : ucfirst(str_replace('-', ' ', $roleSlug));
        
        $locationName = $location['name'];
        
        // Build Breadcrumbs
        $breadcrumbs = [];
        $breadcrumbs[] = ['name' => 'Home', 'url' => '/'];
        $breadcrumbs[] = ['name' => 'Jobs', 'url' => '/jobs'];

        if ($locationType === 'city') {
             $cityFull = $db->fetchOne("
                SELECT c.name as city_name, c.slug as city_slug,
                       s.name as state_name, s.slug as state_slug,
                       cnt.name as country_name, cnt.slug as country_slug
                FROM cities c
                LEFT JOIN states s ON c.state_id = s.id
                LEFT JOIN countries cnt ON s.country_id = cnt.id
                WHERE c.id = :id
             ", ['id' => $location['id']]);
             
             if ($cityFull) {
                 if (!empty($cityFull['country_name'])) {
                     $breadcrumbs[] = ['name' => $cityFull['country_name'], 'url' => '/jobs-in-' . $cityFull['country_slug']];
                 }
                 if (!empty($cityFull['state_name'])) {
                     $breadcrumbs[] = ['name' => $cityFull['state_name'], 'url' => '/jobs-in-' . $cityFull['state_slug']];
                 }
                 $breadcrumbs[] = ['name' => $cityFull['city_name'], 'url' => '/jobs-in-' . $cityFull['city_slug']];
             }
        } elseif ($locationType === 'state') {
             $stateFull = $db->fetchOne("
                SELECT s.name as state_name, s.slug as state_slug,
                       cnt.name as country_name, cnt.slug as country_slug
                FROM states s
                LEFT JOIN countries cnt ON s.country_id = cnt.id
                WHERE s.id = :id
             ", ['id' => $location['id']]);

             if ($stateFull) {
                 if (!empty($stateFull['country_name'])) {
                     $breadcrumbs[] = ['name' => $stateFull['country_name'], 'url' => '/jobs-in-' . $stateFull['country_slug']];
                 }
                 $breadcrumbs[] = ['name' => $stateFull['state_name'], 'url' => '/jobs-in-' . $stateFull['state_slug']];
             }
        } else {
             $breadcrumbs[] = ['name' => $location['name'], 'url' => '/jobs-in-' . $location['slug']];
        }
        
        // Add Role crumb
        $breadcrumbs[] = ['name' => "$roleName Jobs", 'url' => "/$roleSlug-jobs-in-$locationSlug"];

        // SEO
        \App\Services\SeoService::getInstance()->resolve('role_location_jobs', [
            'role' => $roleName,
            'location' => $locationName,
            'type' => $locationType,
            'breadcrumbs' => $breadcrumbs
        ]);

        // Build Query
        $sql = "SELECT j.*, e.company_name, e.logo_url FROM jobs j 
                JOIN job_locations jl ON j.id = jl.job_id 
                JOIN employers e ON j.employer_id = e.id ";
        
        $queryParams = [];
        $where = ["j.status = 'published'"];

        // Location Filter
        if ($locationType === 'city') {
            $where[] = "jl.city_id = :loc_id";
        } elseif ($locationType === 'state') {
            $where[] = "jl.state_id = :loc_id";
        } else {
            $where[] = "jl.country_id = :loc_id";
        }
        $queryParams['loc_id'] = $location['id'];

        // Role Filter (Skill or Title match)
        if ($skill) {
            $sql .= " JOIN job_skills js ON j.id = js.job_id ";
            $where[] = "js.skill_id = :skill_id";
            $queryParams['skill_id'] = $skill['id'];
        } else {
            $where[] = "(j.title LIKE :role OR j.description LIKE :role)";
            $queryParams['role'] = '%' . $roleName . '%';
        }

        // Pagination
        $page = max(1, (int)$request->get('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Total count
        $countSql = "SELECT COUNT(DISTINCT j.id) as cnt FROM jobs j 
                     JOIN job_locations jl ON j.id = jl.job_id ";
        $countParams = $queryParams;
        if (!empty($skill)) {
            $countSql .= " JOIN job_skills js ON j.id = js.job_id ";
        }
        $countSql .= " WHERE " . implode(' AND ', $where);
        $totalJobs = (int)($db->fetchOne($countSql, $countParams)['cnt'] ?? 0);

        // Fetch jobs (paginated) - inline numeric LIMIT/OFFSET to avoid binding issues
        $sql .= " WHERE " . implode(' AND ', $where) . " ORDER BY j.created_at DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
        
        $jobs = $db->fetchAll($sql, $queryParams);
        foreach ($jobs as &$job) {
            $job['is_bookmarked'] = false;
            $job['salary_min'] = (int)($job['salary_min'] ?? 0);
            $job['salary_max'] = (int)($job['salary_max'] ?? 0);
            $job['is_remote'] = (int)($job['is_remote'] ?? 0);
            $locRows = $db->fetchAll(
                "SELECT c.name as city, s.name as state, cnt.name as country
                 FROM job_locations jl
                 LEFT JOIN cities c ON jl.city_id = c.id
                 LEFT JOIN states s ON jl.state_id = s.id
                 LEFT JOIN countries cnt ON jl.country_id = cnt.id
                 WHERE jl.job_id = :jid",
                ['jid' => $job['id']]
            );
            $locStrings = [];
            foreach ($locRows as $lr) {
                $parts = array_filter([trim($lr['city'] ?? ''), trim($lr['state'] ?? ''), trim($lr['country'] ?? '')]);
                if (!empty($parts)) $locStrings[] = implode(', ', $parts);
            }
            $job['location_display'] = !empty($locStrings) ? implode(' | ', $locStrings) : ($job['is_remote'] == 1 ? 'Remote' : 'Location not specified');
        }

        $response->view('candidate/jobs/index', [
            'jobs' => $jobs,
            'filters' => [
                'location' => $locationName,
                'keyword' => $roleName
            ],
            'pageTitle' => "$roleName Jobs in $locationName",
            'breadcrumbs' => $breadcrumbs,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $totalJobs,
                'total_pages' => max(1, (int)ceil($totalJobs / $perPage))
            ]
        ], 200, 'layout');
    } catch (\Exception $e) {
        error_log("Error in role-location jobs route: " . $e->getMessage());
        $response->redirect('/jobs');
    }
});

// Jobs by Category: /jobs-in-category/{slug}
$router->get('/jobs-in-category/{slug}', function(Request $request, Response $response, array $params) {
    $slug = $params['slug'] ?? '';
    $db = \App\Core\Database::getInstance();
    try {
        $category = $db->fetchOne("SELECT name, slug FROM job_categories WHERE slug = :slug", ['slug' => $slug]);
        if (!$category) {
            // Try matching by name-like
            $category = $db->fetchOne("SELECT name, slug FROM job_categories WHERE name LIKE :name_like", ['name_like' => str_replace('-', ' ', $slug)]);
        }
        if (!$category) {
            $response->redirect('/jobs');
            return;
        }
        $categoryName = $category['name'];
        
        // Count jobs
        $countRow = $db->fetchOne("SELECT COUNT(DISTINCT j.id) as cnt FROM jobs j WHERE j.status = 'published' AND j.category = :cat", ['cat' => $categoryName]);
        $totalJobs = (int)($countRow['cnt'] ?? 0);
        
        // Pagination
        $page = max(1, (int)$request->get('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Fetch jobs
        $jobs = $db->fetchAll(
            "SELECT j.*, e.company_name, e.logo_url as company_logo 
             FROM jobs j
             LEFT JOIN employers e ON j.employer_id = e.id
             WHERE j.status = 'published' AND j.category = :cat
             ORDER BY j.created_at DESC
             LIMIT " . (int)$perPage . " OFFSET " . (int)$offset,
            ['cat' => $categoryName]
        );
        
        foreach ($jobs as &$job) {
            $job['is_bookmarked'] = false;
            $job['salary_min'] = (int)($job['salary_min'] ?? 0);
            $job['salary_max'] = (int)($job['salary_max'] ?? 0);
            $job['is_remote'] = (int)($job['is_remote'] ?? 0);
            $locRows = $db->fetchAll(
                "SELECT c.name as city, s.name as state, cnt.name as country
                 FROM job_locations jl
                 LEFT JOIN cities c ON jl.city_id = c.id
                 LEFT JOIN states s ON jl.state_id = s.id
                 LEFT JOIN countries cnt ON jl.country_id = cnt.id
                 WHERE jl.job_id = :jid",
                ['jid' => $job['id']]
            );
            $locStrings = [];
            foreach ($locRows as $lr) {
                $parts = array_filter([trim($lr['city'] ?? ''), trim($lr['state'] ?? ''), trim($lr['country'] ?? '')]);
                if (!empty($parts)) $locStrings[] = implode(', ', $parts);
            }
            $job['location_display'] = !empty($locStrings) ? implode(' | ', $locStrings) : ($job['is_remote'] == 1 ? 'Remote' : 'Location not specified');
        }
        
        // SEO
        \App\Services\SeoService::getInstance()->resolve('category_jobs', [
            'category' => $categoryName,
            'job_count' => $totalJobs
        ]);
        
        // Breadcrumbs
        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Jobs', 'url' => '/jobs'],
            ['name' => "Jobs in {$categoryName}", 'url' => '/jobs-in-category/' . ($category['slug'] ?? $slug)]
        ];
        
        $response->view('candidate/jobs/index', [
            'jobs' => $jobs,
            'filters' => ['category' => $categoryName],
            'pageTitle' => "Jobs in {$categoryName}",
            'breadcrumbs' => $breadcrumbs,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $totalJobs,
                'total_pages' => max(1, (int)ceil($totalJobs / $perPage))
            ]
        ], 200, 'layout');
        
    } catch (\Exception $e) {
        error_log("Error in category jobs route: " . $e->getMessage());
        $response->redirect('/jobs');
    }
});
// Job Detail: /job/{slug}
$router->get('/job/{slug}', [\App\Controllers\Front\JobController::class, 'show']);

// Swagger UI
$router->get('/swagger', function(Request $request, Response $response) {
    $swaggerPath = __DIR__ . '/../public/swagger-ui.html';
    if (file_exists($swaggerPath)) {
        $swaggerHtml = file_get_contents($swaggerPath);
        header('Content-Type: text/html; charset=utf-8');
        echo $swaggerHtml;
        exit;
    } else {
        $response->setStatusCode(404);
        $response->json(['error' => 'Swagger UI not found']);
    }
});

// Swagger JSON
$router->get('/swagger.json', function(Request $request, Response $response) {
    $swaggerPath = __DIR__ . '/../public/swagger.json';
    if (file_exists($swaggerPath)) {
        $swaggerJson = file_get_contents($swaggerPath);
        header('Content-Type: application/json; charset=utf-8');
        echo $swaggerJson;
        exit;
    } else {
        $response->setStatusCode(404);
        $response->json(['error' => 'Swagger JSON not found']);
    }
});

// Auth routes
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/auth/google', [AuthController::class, 'googleLogin']);
$router->get('/auth/google/callback', [AuthController::class, 'googleCallback']);
$router->get('/auth/apple', [AuthController::class, 'appleLogin']);
$router->post('/auth/apple/callback', [AuthController::class, 'appleCallback']);
$router->get('/auth/apple/callback', [AuthController::class, 'appleCallback']);
$router->get('/register-employer', [AuthController::class, 'registerEmployer']);
$router->post('/register-employer', [AuthController::class, 'registerEmployer']);
$router->get('/register-candidate', [AuthController::class, 'registerCandidate']);
$router->post('/register-candidate', [AuthController::class, 'registerCandidate']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->get('/reset-password', [AuthController::class, 'resetPassword']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);
$router->post('/contact', [ContactController::class, 'submitForm']);
$router->get('/contact', [ContactController::class, 'index']);
// About page
$router->get('/about', [AboutController::class, 'index']);

// Gateway webhooks
use App\Controllers\Gateway\RazorpayWebhookController;
$router->post('/webhook/razorpay', [RazorpayWebhookController::class, 'handle']);
// Sales Panels
$router->get('/sales-manager/dashboard', [\App\Controllers\SalesManager\DashboardController::class, 'index'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/sales-executive/dashboard', [\App\Controllers\SalesExecutive\DashboardController::class, 'index'], [new \App\Middlewares\SalesRoleMiddleware()]);
// Quick actions from dashboard table
$router->post('/sales-manager/leads/assign', [\App\Controllers\SalesManager\DashboardController::class, 'assign'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-manager/leads/stage', [\App\Controllers\SalesManager\DashboardController::class, 'stage'], [new \App\Middlewares\SalesRoleMiddleware()]);

// Sales Manager - Leads
$router->get('/sales-manager/leads', [\App\Controllers\SalesManager\LeadController::class, 'index'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/sales-manager/leads/create', [\App\Controllers\SalesManager\LeadController::class, 'create'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-manager/leads/store', [\App\Controllers\SalesManager\LeadController::class, 'store'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/sales-manager/leads/{id}', [\App\Controllers\SalesManager\LeadController::class, 'show'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-manager/leads/{id}/update', [\App\Controllers\SalesManager\LeadController::class, 'update'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-manager/leads/{id}/assign', [\App\Controllers\SalesManager\LeadController::class, 'assign'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-manager/leads/{id}/update-stage', [\App\Controllers\SalesManager\LeadController::class, 'updateStage'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-manager/leads/{id}/note', [\App\Controllers\SalesManager\LeadController::class, 'addNote'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-manager/leads/{id}/activity', [\App\Controllers\SalesManager\LeadController::class, 'addActivity'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-manager/leads/{id}/followup', [\App\Controllers\SalesManager\LeadController::class, 'scheduleFollowup'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-manager/leads/import-csv', [\App\Controllers\SalesManager\LeadController::class, 'importCsv'], [new \App\Middlewares\SalesRoleMiddleware()]);

// Sales Manager - Pipeline
$router->get('/sales-manager/pipeline', [\App\Controllers\SalesManager\PipelineController::class, 'index'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-manager/pipeline/update-stage', [\App\Controllers\SalesManager\PipelineController::class, 'updateStage'], [new \App\Middlewares\SalesRoleMiddleware()]);

// Sales Manager - Followups
$router->get('/sales-manager/followups', [\App\Controllers\SalesManager\FollowupController::class, 'index'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-manager/followups/{id}/update-status', [\App\Controllers\SalesManager\FollowupController::class, 'updateStatus'], [new \App\Middlewares\SalesRoleMiddleware()]);

// Sales Manager - Payments
$router->get('/sales-manager/payments', [\App\Controllers\SalesManager\PaymentController::class, 'index'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-manager/payments/{id}/update-status', [\App\Controllers\SalesManager\PaymentController::class, 'updateStatus'], [new \App\Middlewares\SalesRoleMiddleware()]);

// Sales Manager - Team & Notifications
$router->get('/sales-manager/team', [\App\Controllers\SalesManager\TeamController::class, 'index'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/sales-manager/notifications', [\App\Controllers\SalesManager\NotificationController::class, 'index'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-manager/notifications/mark-read', [\App\Controllers\SalesManager\NotificationController::class, 'markRead'], [new \App\Middlewares\SalesRoleMiddleware()]);

// Sales Executive - Leads
$router->get('/sales-executive/leads', [\App\Controllers\SalesExecutive\LeadController::class, 'index'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/sales-executive/leads/{id}', [\App\Controllers\SalesExecutive\LeadController::class, 'show'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-executive/leads/update', [\App\Controllers\SalesExecutive\LeadsController::class, 'update'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-executive/leads/{id}/update-stage', [\App\Controllers\SalesExecutive\LeadController::class, 'updateStage'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-executive/leads/{id}/note', [\App\Controllers\SalesExecutive\LeadController::class, 'addNote'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-executive/leads/{id}/activity', [\App\Controllers\SalesExecutive\LeadController::class, 'addActivity'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-executive/leads/{id}/followup', [\App\Controllers\SalesExecutive\LeadController::class, 'scheduleFollowup'], [new \App\Middlewares\SalesRoleMiddleware()]);

// Sales Executive - Pipeline & Followups
$router->get('/sales-executive/pipeline', [\App\Controllers\SalesExecutive\PipelineController::class, 'index'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/sales-executive/followups', [\App\Controllers\SalesExecutive\FollowupController::class, 'index'], [new \App\Middlewares\SalesRoleMiddleware()]);

// Sales Executive - Notifications
$router->get('/sales-executive/notifications', [\App\Controllers\SalesExecutive\NotificationController::class, 'index'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->post('/sales-executive/notifications/mark-read', [\App\Controllers\SalesExecutive\NotificationController::class, 'markRead'], [new \App\Middlewares\SalesRoleMiddleware()]);
$router->get('/support-exec/tickets', [\App\Controllers\SupportExecutive\TicketsController::class, 'index'], [new \App\Middlewares\RbacMiddleware('support.tickets.view')]);
$router->get('/support-exec/tickets/{id}', [\App\Controllers\SupportExecutive\TicketsController::class, 'show'], [new \App\Middlewares\RbacMiddleware('support.tickets.view')]);
$router->post('/support-exec/tickets/assign', [\App\Controllers\SupportExecutive\TicketsController::class, 'assign'], [new \App\Middlewares\RbacMiddleware('support.tickets.assign')]);
$router->post('/support-exec/tickets/reply', [\App\Controllers\SupportExecutive\TicketsController::class, 'reply'], [new \App\Middlewares\RbacMiddleware('support.tickets.reply')]);
$router->post('/support-exec/tickets/close', [\App\Controllers\SupportExecutive\TicketsController::class, 'close'], [new \App\Middlewares\RbacMiddleware('support.tickets.close')]);
$router->post('/support-exec/tickets/escalate', [\App\Controllers\SupportExecutive\TicketsController::class, 'escalate'], [new \App\Middlewares\RbacMiddleware('support.escalate')]);

$router->get('/finance/payments', [\App\Controllers\FinanceManager\PaymentsController::class, 'index'], [new \App\Middlewares\RbacMiddleware('payments.view')]);
$router->get('/finance/payments/{id}', [\App\Controllers\FinanceManager\PaymentsController::class, 'show'], [new \App\Middlewares\RbacMiddleware('payments.view')]);
$router->post('/finance/payments/approve', [\App\Controllers\FinanceManager\PaymentsController::class, 'approve'], [new \App\Middlewares\RbacMiddleware('payments.approve')]);
$router->post('/finance/payments/refund', [\App\Controllers\FinanceManager\PaymentsController::class, 'refund'], [new \App\Middlewares\RbacMiddleware('payments.refund')]);

// Blog routes
$router->get('/blog', [BlogController::class, 'index']);
$router->get('/blog/{slug}', [BlogController::class, 'detail']);
$router->get('/blog/category/{slug}', [BlogController::class, 'category']);
$router->get('/blog/tag/{slug}', [BlogController::class, 'tag']);

// Public job routes (no login required)
use App\Controllers\Front\JobController;
use App\Controllers\Candidate\JobController as CandidateJobController;
// Route /jobs to candidate job listing (now public, no login required)
$router->get('/jobs', [CandidateJobController::class, 'index']);
$router->get('/job/{slug}', [JobController::class, 'show']);

// Public company routes (no login required)
use App\Controllers\Company\CompanyController;
$router->get('/company/featured', [CompanyController::class, 'featured']);
$router->get('/company/{slug}', [CompanyController::class, 'show']);
$router->get('/company/{slug}/{tab}', [CompanyController::class, 'show']);

// Company follow routes
use App\Controllers\Company\CompanyFollowController;
$router->post('/company/follow', [CompanyFollowController::class, 'toggle']);
use App\Controllers\Company\CompanyReviewController;
$router->post('/company/{id}/review', [CompanyReviewController::class, 'store']);
///social
$router->get('/social-services', [SocialServiceController::class, 'index']);
$router->get('/find-a-job', [SocialServiceController::class, 'findjob']);
$router->get('/roles', [SocialServiceController::class, 'roles']);
$router->get('/createjob', [SocialServiceController::class, 'createjob']);

// Social Service routes
$router->get('/candidate', [SocialServiceController::class, 'candidate']);
$router->get('/listings', [SocialServiceController::class, 'listings']);
$router->get('/subscriptions', [SocialServiceController::class, 'subscriptions']);
$router->get('/newsubscriptions', [SocialServiceController::class, 'newsubscriptions']);
$router->get('/employers', [SocialServiceController::class, 'employers']);
$router->get('/pricing', [SocialServiceController::class, 'pricing']);
$router->get('/aboutus', [SocialServiceController::class, 'aboutus']);
$router->get('/supports', [SocialServiceController::class, 'supports']);
$router->get('/specials', [SocialServiceController::class, 'specials']);
use App\Controllers\Front\LegalController;
$router->get('/terms', [LegalController::class, 'terms']);
$router->get('/privacy', [LegalController::class, 'privacy']);
$router->get('/grievances', [LegalController::class, 'grievances']);
