<?php

declare(strict_types=1);

namespace App\Controllers\Candidate;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Candidate;
use App\Models\Job;
use App\Models\JobBookmark;
use App\Models\JobView;
use App\Models\Application;
use App\Services\JobMatchService;
use App\Services\NotificationService;

class JobController extends BaseController
{

    private function ensureCandidate(Request $request, Response $response): ?Candidate
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            // Get current URI for redirect after login
            $currentUri = $_SERVER['REQUEST_URI'] ?? $request->getPath();
            $redirectUrl = '/login?redirect=' . urlencode($currentUri);
            $response->redirect($redirectUrl);
            return null;
        }

        /** @var \App\Models\User|null $user */
        $user = User::find($userId);
        if (!$user || !$user->isCandidate()) {
            $response->redirect('/');
            return null;
        }

        $candidate = Candidate::findByUserId($userId);
        if (!$candidate) {
            $candidate = Candidate::createForUser($userId);
        }

        return $candidate;
    }

    /**
     * Helper to remove unused parameters to avoid PDO errors
     */
    private function cleanParams(string $sql, array $params): array
    {
        $matches = [];
        // Match named parameters like :name, :id_1, etc.
        preg_match_all('/:[a-zA-Z0-9_]+/', $sql, $matches);
        
        // Get unique parameter names including the colon
        $usedParams = array_unique($matches[0]);
        
        $cleaned = [];
        foreach ($usedParams as $param) {
            // Remove the leading colon to get the key
            $key = substr($param, 1);
            if (array_key_exists($key, $params)) {
                $cleaned[$key] = $params[$key];
            }
        }
        
        return $cleaned;
    }

    /**
     * Job listing with search and filters (public - no login required)
     */
    public function index(Request $request, Response $response): void
    {
        // Optional: Get candidate if logged in (for personalized features)
        $candidate = null;
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            /** @var \App\Models\User|null $user */
            $user = User::find((int)$userId);
            if ($user && $user->isCandidate()) {
                $candidate = Candidate::findByUserId((int)$userId);
                if (!$candidate) {
                    $candidate = Candidate::createForUser((int)$userId);
                }
            }
        }

        $keyword = $request->get('keyword') ?? '';
        $location = $request->get('location') ?? '';
        $salaryMin = $request->get('salary_min') ?? '';
        $salaryMax = $request->get('salary_max') ?? '';
        $experience = $request->get('experience') ?? '';
        $jobType = $request->get('job_type') ?? '';
        $jobTypeArray = $request->get('job_type') ?? [];
        if (!is_array($jobTypeArray)) {
            $jobTypeArray = $jobType ? [$jobType] : [];
        }
        $workModeArray = $request->get('work_mode') ?? [];
        if (!is_array($workModeArray)) {
            $workModeArray = [];
        }
        $locationFilterArray = $request->get('location_filter') ?? [];
        if (!is_array($locationFilterArray)) {
            $locationFilterArray = [];
        }
        $industryFilterArray = $request->get('industry_filter') ?? [];
        if (!is_array($industryFilterArray)) {
            $industryFilterArray = [];
        }
        $companyFilterArray = $request->get('company_filter') ?? [];
        if (!is_array($companyFilterArray)) {
            $companyFilterArray = [];
        }
        $salaryRange = $request->get('salary_range') ?? '';
        $industry = $request->get('industry') ?? ''; // Legacy support
        $isRemote = $request->get('is_remote') ?? '';
        $sortBy = $request->get('sort') ?? 'date';
        $datePostedArray = $request->get('date_posted') ?? [];
        if (!is_array($datePostedArray)) {
            $datePostedArray = $datePostedArray ? [$datePostedArray] : [];
        }
        $page = (int)($request->get('page') ?? 1);
        $perPage = 20;

        // Build query using raw SQL - show ALL jobs by default, filter only when user searches
        $db = \App\Core\Database::getInstance();
        $params = [];
        // Show ALL jobs from database (no status filter to show everything)
        $whereConditions = ["j.status = 'published'"];
        
        // Keyword filter - only if user searches
        if ($keyword) {
            $whereConditions[] = "(j.title LIKE :keyword OR j.description LIKE :keyword_desc OR j.short_description LIKE :keyword_short)";
            $params['keyword'] = "%{$keyword}%";
            $params['keyword_desc'] = "%{$keyword}%";
            $params['keyword_short'] = "%{$keyword}%";
        }
        
        // Location filter - from search bar
        if ($location) {
            $parts = array_values(array_filter(array_map('trim', explode(',', $location)), fn($p) => $p !== ''));
            $partConditions = [];
            foreach ($parts as $idx => $part) {
                $slugPart = strtolower(str_replace(' ', '-', $part));
                $locEntity = $db->fetchOne(
                    "SELECT id, 'city' as type FROM cities WHERE name = :name1 OR slug = :slug1
                     UNION
                     SELECT id, 'state' as type FROM states WHERE name = :name2 OR slug = :slug2
                     UNION
                     SELECT id, 'country' as type FROM countries WHERE name = :name3 OR slug = :slug3",
                    [
                        'name1' => $part, 'slug1' => $slugPart,
                        'name2' => $part, 'slug2' => $slugPart,
                        'name3' => $part, 'slug3' => $slugPart
                    ]
                );
                if ($locEntity) {
                    $paramId = "loc_id_{$idx}";
                    if ($locEntity['type'] === 'city') {
                        $partConditions[] = "EXISTS (SELECT 1 FROM job_locations jl WHERE jl.job_id = j.id AND jl.city_id = :{$paramId})";
                    } elseif ($locEntity['type'] === 'state') {
                        $partConditions[] = "EXISTS (SELECT 1 FROM job_locations jl WHERE jl.job_id = j.id AND jl.state_id = :{$paramId})";
                    } else {
                        $partConditions[] = "EXISTS (SELECT 1 FROM job_locations jl WHERE jl.job_id = j.id AND jl.country_id = :{$paramId})";
                    }
                    $params[$paramId] = $locEntity['id'];
                } else {
                    $pCity = "loc_part_city_{$idx}";
                    $pState = "loc_part_state_{$idx}";
                    $pCountry = "loc_part_country_{$idx}";
                    $pJson = "loc_part_json_{$idx}";
                    $partConditions[] = "(EXISTS (
                        SELECT 1 FROM job_locations jl 
                        LEFT JOIN cities c ON jl.city_id = c.id
                        LEFT JOIN states s ON jl.state_id = s.id
                        LEFT JOIN countries co ON jl.country_id = co.id
                        WHERE jl.job_id = j.id 
                        AND (c.name LIKE :{$pCity} OR s.name LIKE :{$pState} OR co.name LIKE :{$pCountry})
                    ) OR j.locations LIKE :{$pJson})";
                    $likeVal = "%{$part}%";
                    $params[$pCity] = $likeVal;
                    $params[$pState] = $likeVal;
                    $params[$pCountry] = $likeVal;
                    $params[$pJson] = $likeVal;
                }
            }
            if (!empty($partConditions)) {
                $whereConditions[] = '(' . implode(' OR ', $partConditions) . ')';
            } else {
                $whereConditions[] = "j.locations LIKE :location_search";
                $params['location_search'] = "%{$location}%";
            }
        }
        
        // Location filter - from filter checkboxes
        if (!empty($locationFilterArray)) {
            $locationOrConditions = [];
            foreach ($locationFilterArray as $index => $loc) {
                $locParam = 'loc_filter_city_' . $index;
                $locParamState = 'loc_filter_state_' . $index;
                $locParamJson = 'loc_filter_json_' . $index;
                
                // Build conditions for each location
                $locationOrConditions[] = "(EXISTS (
                    SELECT 1 FROM job_locations jl2 
                    LEFT JOIN cities c2 ON jl2.city_id = c2.id
                    LEFT JOIN states s2 ON jl2.state_id = s2.id
                    WHERE jl2.job_id = j.id 
                    AND (c2.name LIKE :{$locParam} OR s2.name LIKE :{$locParamState})
                ) OR j.locations LIKE :{$locParamJson})";
                
                $params[$locParam] = "%{$loc}%";
                $params[$locParamState] = "%{$loc}%";
                $params[$locParamJson] = "%{$loc}%";
            }
            if (!empty($locationOrConditions)) {
                $whereConditions[] = "(" . implode(' OR ', $locationOrConditions) . ")";
            }
        }
        
        // Salary filters - from salary_min/salary_max or salary_range
        if ($salaryRange) {
            // Handle salary range (e.g., "0-3", "3-6", "15+")
            if (strpos($salaryRange, '-') !== false) {
                list($minLakhs, $maxLakhs) = explode('-', $salaryRange);
                $salaryMin = (int)$minLakhs * 100000;
                $salaryMax = (int)$maxLakhs * 100000;
            } elseif ($salaryRange === '15+') {
                $salaryMin = 1500000;
                $salaryMax = 999999999; // Very high number
            }
        }
        
        if ($salaryMin) {
            $whereConditions[] = "(j.salary_max >= :salary_min OR j.salary_min >= :salary_min)";
            $params['salary_min'] = $salaryMin;
        }
        
        if ($salaryMax && $salaryMax < 999999999) {
            $whereConditions[] = "(j.salary_min <= :salary_max OR j.salary_max <= :salary_max)";
            $params['salary_max'] = $salaryMax;
        }
        
        if ($experience && $experience !== 'Experience') {
            $expMin = 0;
            $expMax = 0;
            $exp = trim($experience);
            // Support "2 - 3 years", "2 – 3 years", "2 to 3 years", "2-3 yrs"
            if (preg_match('/^(\d+)\s*[-–]\s*(\d+)\s*(years|yrs)?$/i', $exp, $m)) {
                $expMin = (int)$m[1];
                $expMax = (int)$m[2];
            } elseif (preg_match('/^(\d+)\s*to\s*(\d+)\s*(years|yrs)?$/i', $exp, $m)) {
                $expMin = (int)$m[1];
                $expMax = (int)$m[2];
            } elseif (preg_match('/^(\d+)\s*\+\s*(years|yrs)?$/i', $exp, $m)) {
                $expMin = (int)$m[1];
                $expMax = 99;
            }
            if ($expMax > 0) {
                $whereConditions[] = "((j.min_experience IS NULL OR j.min_experience <= :exp_max) AND (j.max_experience IS NULL OR j.max_experience >= :exp_min))";
                $params['exp_min'] = $expMin;
                $params['exp_max'] = $expMax;
            }
        }
        
        // Work mode filter
        if (!empty($workModeArray)) {
            $workModeConditions = [];
            foreach ($workModeArray as $index => $mode) {
                if ($mode === 'remote') {
                    $workModeConditions[] = "j.is_remote = 1";
                } elseif ($mode === 'office') {
                    $workModeConditions[] = "j.is_remote = 0";
                } elseif ($mode === 'hybrid') {
                    // Hybrid can be either remote or office, so we don't filter it out
                    // Or you can add a hybrid field to jobs table
                }
            }
            if (!empty($workModeConditions)) {
                $whereConditions[] = "(" . implode(' OR ', $workModeConditions) . ")";
            }
        }
        
        // Remote filter (legacy support)
        if ($isRemote === '1') {
            $whereConditions[] = "j.is_remote = 1";
        }

        // Date Posted filter (created_at thresholds)
        if (!empty($datePostedArray)) {
            $dateConditions = [];
            $now = time();
            foreach ($datePostedArray as $index => $range) {
                $param = 'date_posted_' . $index;
                switch ($range) {
                    case 'last_hour':
                        $params[$param] = date('Y-m-d H:i:s', $now - 60 * 60);
                        $dateConditions[] = "j.created_at >= :{$param}";
                        break;
                    case 'last_24_hours':
                        $params[$param] = date('Y-m-d H:i:s', $now - 24 * 60 * 60);
                        $dateConditions[] = "j.created_at >= :{$param}";
                        break;
                    case 'last_3_days':
                        $params[$param] = date('Y-m-d H:i:s', $now - 3 * 24 * 60 * 60);
                        $dateConditions[] = "j.created_at >= :{$param}";
                        break;
                    case 'last_7_days':
                        $params[$param] = date('Y-m-d H:i:s', $now - 7 * 24 * 60 * 60);
                        $dateConditions[] = "j.created_at >= :{$param}";
                        break;
                    case 'last_30_days':
                        $params[$param] = date('Y-m-d H:i:s', $now - 30 * 24 * 60 * 60);
                        $dateConditions[] = "j.created_at >= :{$param}";
                        break;
                    case 'last_3_months':
                        $params[$param] = date('Y-m-d H:i:s', $now - 90 * 24 * 60 * 60);
                        $dateConditions[] = "j.created_at >= :{$param}";
                        break;
                    case 'all':
                    default:
                        // No condition for 'all'
                        break;
                }
            }
            if (!empty($dateConditions)) {
                $whereConditions[] = '(' . implode(' OR ', $dateConditions) . ')';
            }
        }

        // Job type filter - handle array
        if (!empty($jobTypeArray)) {
            $jobTypeConditions = [];
            foreach ($jobTypeArray as $index => $type) {
                $typeParam = 'job_type_' . $index;
                $jobTypeConditions[] = "j.employment_type = :{$typeParam}";
                $params[$typeParam] = $type;
            }
            if (!empty($jobTypeConditions)) {
                $whereConditions[] = "(" . implode(' OR ', $jobTypeConditions) . ")";
            }
        } elseif ($jobType) {
            // Legacy support for single job_type
            $whereConditions[] = "j.employment_type = :job_type";
            $params['job_type'] = $jobType;
        }

        // Company filter - filter by employer's company name (array support)
        if (!empty($companyFilterArray)) {
            $companyConditions = [];
            foreach ($companyFilterArray as $index => $comp) {
                $compParam = 'company_filter_' . $index;
                $companyConditions[] = "EXISTS (
                    SELECT 1 FROM employers e 
                    WHERE e.id = j.employer_id 
                    AND e.company_name = :{$compParam}
                )";
                $params[$compParam] = $comp;
            }
            if (!empty($companyConditions)) {
                $whereConditions[] = "(" . implode(' OR ', $companyConditions) . ")";
            }
        }

        // Industry filter - filter by employer's industry (array support)
        if (!empty($industryFilterArray)) {
            $industryConditions = [];
            foreach ($industryFilterArray as $index => $ind) {
                $indParam = 'industry_filter_' . $index;
                $industryConditions[] = "EXISTS (
                    SELECT 1 FROM employers e 
                    WHERE e.id = j.employer_id 
                    AND (e.industry LIKE :{$indParam} OR e.industry = :{$indParam}_exact)
                )";
                $params[$indParam] = "%{$ind}%";
                $params[$indParam . '_exact'] = $ind;
            }
            if (!empty($industryConditions)) {
                $whereConditions[] = "(" . implode(' OR ', $industryConditions) . ")";
            }
        } elseif ($industry) {
            $categoryName = null;
            try {
                $catRow = $db->fetchOne(
                    "SELECT name FROM job_categories WHERE slug = :slug OR name = :name",
                    ['slug' => $industry, 'name' => $industry]
                );
                if ($catRow && !empty($catRow['name'])) {
                    $categoryName = $catRow['name'];
                }
            } catch (\Exception $e) {}
            
            $whereConditions[] = "("
                . "EXISTS (SELECT 1 FROM employers e WHERE e.id = j.employer_id AND (e.industry LIKE :industry OR e.industry = :industry_exact))"
                . " OR "
                . "(j.category LIKE :industry_cat OR j.category = :industry_cat_exact)"
            . ")";
            
            $params['industry'] = "%{$industry}%";
            $params['industry_exact'] = $industry;
            $catLike = $categoryName ? "%{$categoryName}%" : "%{$industry}%";
            $params['industry_cat'] = $catLike;
            $params['industry_cat_exact'] = $categoryName ?: $industry;
        }

        // Company filter
        // Note: Duplicate company filter block removed here to avoid parameter collision

        $candidateSkills = [];
        if (!empty($candidate->attributes['skills_data'])) {
            $candidateSkills = json_decode($candidate->attributes['skills_data'], true) ?? [];
        }
        $candidateSkillNames = array_values(array_filter(array_map(function($s){
            return strtolower(trim($s['name'] ?? ''));
        }, $candidateSkills)));

        $noUserFilters = !$keyword && !$location && !$salaryMin && !$salaryMax && empty($salaryRange) && empty($workModeArray) && empty($jobTypeArray) && empty($locationFilterArray) && empty($industryFilterArray) && !$industry && !$isRemote;

        // Only use candidate skills for matching if candidate is logged in
        if ($noUserFilters && !empty($candidateSkillNames) && $candidate) {
            $skillConditions = [];
            foreach ($candidateSkillNames as $index => $name) {
                $param = 'cand_skill_' . $index;
                $skillConditions[] = "EXISTS (SELECT 1 FROM job_skills js INNER JOIN skills s ON s.id = js.skill_id WHERE js.job_id = j.id AND LOWER(s.name) = :{$param})";
                $params[$param] = $name;
            }
            if (!empty($skillConditions)) {
                $whereConditions[] = '(' . implode(' OR ', $skillConditions) . ')';
            }
        }

        $whereClause = implode(' AND ', $whereConditions);
        
        // Get total count - include join for industry filter
        $countJoin = $industry ? "LEFT JOIN employers e ON e.id = j.employer_id" : "";
        $countSql = "SELECT COUNT(DISTINCT j.id) as total FROM jobs j {$countJoin} WHERE {$whereClause}";
        // Ensure only used params are passed to avoid PDO errors
        $countParams = $this->cleanParams($countSql, $params);
        try {
            $totalResult = $db->fetchOne($countSql, $countParams);
        } catch (\Exception $e) {
            error_log("Job Count Query Error: " . $e->getMessage());
            error_log("SQL: " . $countSql);
            error_log("Params: " . json_encode($countParams));
            // Fallback
            $totalResult = ['total' => 0];
        }
        $totalJobs = (int)($totalResult['total'] ?? 0);

        // Get jobs with pagination and sorting
        $offset = ($page - 1) * $perPage;
        
        // Determine sort order
        $orderBy = "j.created_at DESC"; // Default: newest first
        switch ($sortBy) {
            case 'date':
                $orderBy = "j.created_at DESC";
                break;
            case 'salary_high':
                $orderBy = "j.salary_max DESC, j.salary_min DESC";
                break;
            case 'salary_low':
                $orderBy = "j.salary_min ASC, j.salary_max ASC";
                break;
            case 'relevance':
            default:
                // "World Class" Search Logic
                $sortParts = [];
                
                // 1. Keyword Relevance: Title starts with > Title contains > Description contains
                if ($keyword) {
                    // Note: :keyword is already set to %keyword% in params
                    $params['keyword_starts'] = "{$keyword}%";
                    $sortParts[] = "(CASE 
                        WHEN j.title LIKE :keyword_starts THEN 0 
                        WHEN j.title LIKE :keyword THEN 1 
                        ELSE 2 
                    END) ASC";
                }
                
                // 2. Location Relevance (Smart Suggestion)
                // If user is logged in, hasn't searched for location, and has a profile location, prioritize local jobs
                if (empty($location) && empty($locationFilterArray) && $candidate) {
                    $candLoc = $candidate->attributes['preferred_job_location'] ?? $candidate->attributes['city'] ?? '';
                    if ($candLoc) {
                        // Simple check if job location contains candidate location string
                        $params['cand_loc_like'] = "%{$candLoc}%";
                        $sortParts[] = "(CASE 
                            WHEN j.locations LIKE :cand_loc_like THEN 0 
                            ELSE 1 
                        END) ASC";
                    }
                }
                
                // Default fallback: Newest first
                $sortParts[] = "j.created_at DESC";
                
                $orderBy = implode(', ', $sortParts);
                break;
        }
        
        // Build SQL - include company logo for display
        $sql = "SELECT DISTINCT j.*, e.company_name, e.logo_url as company_logo, j.slug
                FROM jobs j
                LEFT JOIN employers e ON j.employer_id = e.id
                WHERE {$whereClause}
                ORDER BY {$orderBy}
                LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
        
        // $params['limit'] = $perPage;
        // $params['offset'] = $offset;
        
        try {
            // Ensure only used params are passed to avoid PDO errors
            $queryParams = $this->cleanParams($sql, $params);
            $results = $db->fetchAll($sql, $queryParams);
        } catch (\Exception $e) {
            error_log("Job Query Error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . json_encode($queryParams ?? []));
            $results = [];
        }
        
        // Convert to Job models and enrich with company_name from JOIN
        $enrichedJobs = [];
        $candidateId = $candidate->attributes['id'] ?? null;
        foreach ($results as $row) {
            if (empty($row)) continue;
            
            try {
                $job = new Job($row);
                $jobData = $job->attributes;
                
                // Ensure job ID and slug are set from row data (critical for links)
                $jobData['id'] = (int)($row['id'] ?? $jobData['id'] ?? 0);
                
                // Generate slug if missing
                if ((empty($row['slug']) || $row['slug'] === null) && !empty($jobData['title'])) {
                    $generatedSlug = $job->generateSlug($jobData['title']);
                    // Update slug in database
                    if (!empty($jobData['id'])) {
                        \App\Core\Database::getInstance()->query(
                            "UPDATE jobs SET slug = :slug WHERE id = :id",
                            ['slug' => $generatedSlug, 'id' => $jobData['id']]
                        );
                    }
                    $jobData['slug'] = $generatedSlug;
                } else {
                    $jobData['slug'] = $row['slug'] ?? $jobData['slug'] ?? '';
                }
                
                if ($jobData['id'] === 0) {
                    error_log("Warning: Job with missing ID skipped: " . json_encode($row));
                    continue;
                }
                
                // Add company_name and logo from SQL JOIN - check both row and attributes
                $jobData['company_name'] = $row['company_name'] ?? $jobData['company_name'] ?? null;
                $jobData['company_logo'] = $row['company_logo'] ?? null;
                
                // If company_name still not found, get it from employer relationship
                if (empty($jobData['company_name'])) {
                    $employer = $job->employer();
                    if ($employer && isset($employer->attributes['company_name'])) {
                        $jobData['company_name'] = $employer->attributes['company_name'];
                        $jobData['company_logo'] = $employer->attributes['logo_url'] ?? null;
                    } else {
                        $jobData['company_name'] = 'Company Name Not Available';
                    }
                }
                
                // Ensure title is set (should always be there, but just in case)
                if (empty($jobData['title'])) {
                    $jobData['title'] = $row['title'] ?? 'Job Title Not Available';
                }
                
                // Explicitly extract salary from row data to ensure it's included
                if (isset($row['salary_min'])) {
                    $jobData['salary_min'] = $row['salary_min'];
                }
                if (isset($row['salary_max'])) {
                    $jobData['salary_max'] = $row['salary_max'];
                }
                
                // Get job locations directly from database
                $locationStrings = [];
                try {
                    $jobId = $jobData['id'] ?? $row['id'] ?? null;
                    if ($jobId) {
                        $db = \App\Core\Database::getInstance();
                        $locationRows = $db->fetchAll(
                            "SELECT 
                                COALESCE(c.name, jl.city) as city, 
                                COALESCE(s.name, jl.state) as state, 
                                COALESCE(co.name, jl.country) as country
                             FROM job_locations jl
                             LEFT JOIN cities c ON jl.city_id = c.id
                             LEFT JOIN states s ON jl.state_id = s.id
                             LEFT JOIN countries co ON jl.country_id = co.id
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
                    error_log("Error getting job locations for job ID {$jobId}: " . $e->getMessage());
                }
                
                // Fallback to jobs.locations if JSON
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
                    } elseif (is_string($jobData['locations'])) {
                        $locationStrings[] = $jobData['locations'];
                    }
                }
                $jobData['location_display'] = !empty($locationStrings) 
                    ? implode(' | ', $locationStrings) 
                    : 'Location not specified';
                
                // Format employment type for display
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
                
                // Calculate match score (only if candidate is logged in)
                if ($candidate) {
                    $jobData['match_score'] = $this->calculateMatchScore($candidate, $job);
                } else {
                    $jobData['match_score'] = 0;
                }
                
                // Check if bookmarked (only if candidate is logged in)
                $jobId = $job->attributes['id'] ?? null;
                $jobData['is_bookmarked'] = $candidateId && $jobId 
                    ? $this->isBookmarked($candidateId, $jobId) 
                    : false;
                
                // Format salary - preserve null values, only convert to int if not null
                if (isset($jobData['salary_min']) && $jobData['salary_min'] !== null) {
                    $jobData['salary_min'] = (int)$jobData['salary_min'];
                } else {
                    $jobData['salary_min'] = null;
                }
                if (isset($jobData['salary_max']) && $jobData['salary_max'] !== null) {
                    $jobData['salary_max'] = (int)$jobData['salary_max'];
                } else {
                    $jobData['salary_max'] = null;
                }
                
                // Format created date for display
                if (!empty($jobData['created_at'])) {
                    $jobData['created_at_formatted'] = date('M d, Y', strtotime($jobData['created_at']));
                } else {
                    $jobData['created_at_formatted'] = 'Recently';
                }
                
                // Ensure all fields have default values to prevent "undefined"
                $jobData['id'] = $jobData['id'] ?? 0;
                $jobData['title'] = $jobData['title'] ?? 'Job Title Not Available';
                $jobData['company_name'] = $jobData['company_name'] ?? 'Company Name Not Available';
                // Keep salary as null if not set (don't override with 0)
                if (!isset($jobData['salary_min'])) $jobData['salary_min'] = null;
                if (!isset($jobData['salary_max'])) $jobData['salary_max'] = null;
                $jobData['is_remote'] = (int)($jobData['is_remote'] ?? 0);
                $jobData['vacancies'] = (int)($jobData['vacancies'] ?? 1);
                $jobData['match_score'] = (int)($jobData['match_score'] ?? 0);
                $jobData['is_bookmarked'] = (bool)($jobData['is_bookmarked'] ?? false);
                $jobData['employment_type_display'] = $jobData['employment_type_display'] ?? 'Full-time';
                $jobData['location_display'] = $jobData['location_display'] ?? 'Location not specified';
                $jobData['job_timings'] = $jobData['job_timings'] ?? '';
                $jobData['interview_timings'] = $jobData['interview_timings'] ?? '';
                
                $enrichedJobs[] = $jobData;
            } catch (\Exception $e) {
                error_log("Error enriching job data: " . $e->getMessage());
                continue;
            }
        }

        if ($sortBy === 'relevance') {
            usort($enrichedJobs, function($a, $b) {
                $am = (int)($a['match_score'] ?? 0);
                $bm = (int)($b['match_score'] ?? 0);
                return $bm <=> $am;
            });
        }

        // Fetch top companies (employers with most jobs)
        $topCompanies = [];
        try {
            $topCompaniesSql = "SELECT 
                e.id,
                e.company_name,
                e.logo_url as company_logo,
                e.company_slug,
                COUNT(DISTINCT j.id) as job_count
            FROM employers e
            INNER JOIN jobs j ON j.employer_id = e.id
            WHERE j.status = 'published'
                AND e.company_name IS NOT NULL 
                AND e.company_name != ''
            GROUP BY e.id, e.company_name, e.logo_url, e.company_slug
            HAVING job_count > 0
            ORDER BY job_count DESC
            LIMIT 8";
            $topCompanies = $db->fetchAll($topCompaniesSql);
        } catch (\Exception $e) {
            error_log("Error fetching top companies: " . $e->getMessage());
        }

        // Featured companies (admin-curated, ordered)
        $featuredCompanies = [];
        try {
            $db->query("SET SESSION sql_mode=''");
        } catch (\Exception $e) {}
        try {
            $featuredCompanies = $db->fetchAll(
                "SELECT c.id, c.name as company_name, c.slug, c.logo_url as company_logo, c.featured_order
                 FROM companies c
                 WHERE c.is_featured = 1
                 ORDER BY c.featured_order ASC, c.name ASC
                 LIMIT 12"
            );
        } catch (\Exception $e) {
            error_log('Error fetching featured companies: ' . $e->getMessage());
        }

        // Fetch companies for filter (companies with active jobs)
        $filterCompanies = [];
        try {
            $filterCompanies = $db->fetchAll(
                "SELECT DISTINCT e.id, e.company_name 
                 FROM employers e 
                 INNER JOIN jobs j ON j.employer_id = e.id 
                 WHERE j.status = 'published' 
                   AND e.company_name IS NOT NULL 
                   AND e.company_name != ''
                 ORDER BY e.company_name ASC"
            );
        } catch (\Exception $e) {
            error_log("Error fetching filter companies: " . $e->getMessage());
        }

        $response->view('candidate/jobs/index', [
            'title' => 'Browse Jobs',
            'candidate' => $candidate,
            'jobs' => $enrichedJobs,
            'topCompanies' => $topCompanies,
            'featuredCompanies' => $featuredCompanies,
            'filterCompanies' => $filterCompanies,
            'isLoggedIn' => $candidate !== null,
            'filters' => [
                'keyword' => $keyword,
                'location' => $location,
                'salary_min' => $salaryMin,
                'salary_max' => $salaryMax,
                'salary_range' => $salaryRange,
                'experience' => $experience,
                'job_type' => $jobTypeArray,
                'work_mode' => $workModeArray,
                'location_filter' => $locationFilterArray,
                'industry_filter' => $industryFilterArray,
                'company_filter' => $companyFilterArray,
                'industry' => $industry,
            'is_remote' => $isRemote,
            'date_posted' => $datePostedArray
        ],
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $totalJobs,
                'total_pages' => ceil($totalJobs / $perPage)
            ]
        ]);
    }

    /**
     * Job search (AJAX)
     */
    public function search(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        // Similar to index but return JSON
        $this->index($request, $response);
    }

    /**
     * View single job
     */
    public function show(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $slug = $request->param('slug') ?? $request->get('slug') ?? '';
        
        // Use SQL JOIN to get job with company_name directly (same approach as index method)
        $db = \App\Core\Database::getInstance();
        $sql = "SELECT j.*, e.company_name, e.description as company_description, 
                       e.logo_url as company_logo, e.website as company_website, 
                       e.company_slug
                FROM jobs j
                LEFT JOIN employers e ON j.employer_id = e.id
                WHERE j.slug = :slug";
        
        $row = $db->fetchOne($sql, ['slug' => $slug]);
        
        $jobId = (int)($row['id'] ?? 0);
        
        if (!$row || empty($row['id'])) {
            $response->redirect('/candidate/jobs');
            return;
        }
        
        // Create Job model from the row data
        $job = new Job($row);
        $jobData = $job->attributes;
        
        // Ensure job ID, slug, and title are explicitly set from row data (critical for display)
        $jobData['id'] = (int)($row['id'] ?? $jobData['id'] ?? $jobId);
        
        // Generate slug if missing
        if ((empty($row['slug']) || $row['slug'] === null) && !empty($jobData['title'])) {
            $generatedSlug = $job->generateSlug($jobData['title']);
            // Update slug in database
            if (!empty($jobId)) {
                \App\Core\Database::getInstance()->query(
                    "UPDATE jobs SET slug = :slug WHERE id = :id",
                    ['slug' => $generatedSlug, 'id' => $jobId]
                );
            }
            $jobData['slug'] = $generatedSlug;
        } else {
            $jobData['slug'] = $row['slug'] ?? $jobData['slug'] ?? '';
        }
        
        $jobData['title'] = $row['title'] ?? $jobData['title'] ?? 'Job Title Not Available';
        
        // Track view
        $candidateId = $candidate->attributes['id'] ?? null;
        if ($candidateId) {
            $this->trackJobView($candidateId, $jobId);
        }

        // Get company data from SQL JOIN result
        $jobData['employer_id'] = (int)($row['employer_id'] ?? $job->attributes['employer_id'] ?? 0);
        $jobData['company_name'] = $row['company_name'] ?? null;
        $jobData['company_description'] = $row['company_description'] ?? null;
        $jobData['company_logo'] = $row['company_logo'] ?? null;
        $jobData['company_website'] = $row['company_website'] ?? null;
        $jobData['company_slug'] = $row['company_slug'] ?? null;
        
        // If company_name not in JOIN result, try employer relationship as fallback
        if (empty($jobData['company_name'])) {
            $employer = $job->employer();
            if ($employer && isset($employer->attributes)) {
                $jobData['company_name'] = $employer->attributes['company_name'] ?? 'Company Name Not Available';
                $jobData['company_description'] = $employer->attributes['description'] ?? '';
                $jobData['company_logo'] = $employer->attributes['logo_url'] ?? null;
                $jobData['company_website'] = $employer->attributes['website'] ?? null;
                $jobData['company_slug'] = $employer->attributes['company_slug'] ?? null;
            } else {
                $jobData['company_name'] = 'Company Name Not Available';
                $jobData['company_description'] = '';
                $jobData['company_logo'] = null;
                $jobData['company_website'] = null;
                $jobData['company_slug'] = null;
            }
        }
        
        $jobData['skills'] = $job->skills();
        
        // Format locations - query directly from database with latitude/longitude
        $locationStrings = [];
        $locationRows = [];
        try {
            $jobId = $job->attributes['id'] ?? $job->id ?? null;
            if ($jobId) {
                $db = \App\Core\Database::getInstance();
                $locationRows = $db->fetchAll(
                    "SELECT c.name as city, s.name as state, cnt.name as country, jl.latitude, jl.longitude 
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
            error_log("Error getting job locations for job ID {$jobId}: " . $e->getMessage());
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
            } elseif (is_string($jobData['locations'])) {
                $locationStrings[] = $jobData['locations'];
            }
        }
        $jobData['location_display'] = !empty($locationStrings) ? implode(' | ', $locationStrings) : 'Location not specified';
        
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
        
        $jobData['match_score'] = $this->calculateMatchScore($candidate, $job);
        
        // Get related jobs (same category or same employer, excluding current job)
        $relatedJobs = [];
        try {
            $category = $jobData['category'] ?? '';
            $employerId = $jobData['employer_id'] ?? 0;
            
            // Build query with proper parameter binding
            $relatedSql = "SELECT j.*, e.company_name, e.logo_url as company_logo
                          FROM jobs j
                          LEFT JOIN employers e ON j.employer_id = e.id
                          WHERE j.status = 'published'
                          AND j.id != :current_job_id
                          AND j.slug IS NOT NULL AND j.slug != ''";
            
            $params = ['current_job_id' => $jobId];
            
            // Add category condition if category exists
            if (!empty($category)) {
                $relatedSql .= " AND (j.category = :category OR j.employer_id = :employer_id)";
                $params['category'] = $category;
                $params['employer_id'] = $employerId;
            } else {
                $relatedSql .= " AND j.employer_id = :employer_id";
                $params['employer_id'] = $employerId;
            }
            
            $relatedSql .= " ORDER BY j.created_at DESC LIMIT 6";
            
            $relatedJobs = $db->fetchAll($relatedSql, $params);
            
                // Format related jobs
                foreach ($relatedJobs as &$relatedJob) {
                    // Format location with coordinates
                    $relatedLocations = $db->fetchAll(
                        "SELECT 
                            COALESCE(c.name, jl.city) as city, 
                            COALESCE(s.name, jl.state) as state, 
                            COALESCE(cnt.name, jl.country) as country, 
                            jl.latitude, 
                            jl.longitude 
                         FROM job_locations jl
                         LEFT JOIN cities c ON jl.city_id = c.id
                         LEFT JOIN states s ON jl.state_id = s.id
                         LEFT JOIN countries cnt ON jl.country_id = cnt.id
                         WHERE jl.job_id = :job_id",
                        ['job_id' => $relatedJob['id']]
                    );
                $relatedLocationStrings = [];
                foreach ($relatedLocations as $locRow) {
                    $locParts = array_filter([
                        trim($locRow['city'] ?? ''),
                        trim($locRow['state'] ?? ''),
                        trim($locRow['country'] ?? '')
                    ]);
                    if (!empty($locParts)) {
                        $relatedLocationStrings[] = implode(', ', $locParts);
                    }
                }
                $relatedJob['location_display'] = !empty($relatedLocationStrings) ? implode(' | ', $relatedLocationStrings) : 'Location not specified';
                
                // Format employment type
                $relatedEmpType = $relatedJob['employment_type'] ?? 'full_time';
                $relatedJob['employment_type_display'] = $employmentTypeMap[$relatedEmpType] ?? ucfirst(str_replace('_', ' ', $relatedEmpType));
                
                // Format salary
                $relatedJob['salary_display'] = '';
                if (($relatedJob['salary_min'] ?? 0) > 0 && ($relatedJob['salary_max'] ?? 0) > 0) {
                    $relatedJob['salary_display'] = ($relatedJob['currency'] ?? 'INR') . ' ' . number_format($relatedJob['salary_min']) . ' - ' . number_format($relatedJob['salary_max']);
                } elseif (($relatedJob['salary_min'] ?? 0) > 0) {
                    $relatedJob['salary_display'] = ($relatedJob['currency'] ?? 'INR') . ' ' . number_format($relatedJob['salary_min']);
                }
                
                // Check if bookmarked
                if ($candidateId) {
                    $relatedJob['is_bookmarked'] = $this->isBookmarked($candidateId, $relatedJob['id']);
                } else {
                    $relatedJob['is_bookmarked'] = false;
                }
                
                // Fetch skills for related jobs
                try {
                    $jobSkills = $db->fetchAll(
                        "SELECT s.name FROM job_skills js 
                         INNER JOIN skills s ON js.skill_id = s.id 
                         WHERE js.job_id = :job_id 
                         LIMIT 3",
                        ['job_id' => $relatedJob['id']]
                    );
                    $relatedJob['skills'] = array_map(fn($s) => ['name' => $s['name']], $jobSkills);
                } catch (\Exception $e) {
                    $relatedJob['skills'] = [];
                }
            }
        } catch (\Exception $e) {
            error_log("Error fetching related jobs: " . $e->getMessage());
        }
        
        // Get location data for map (first location) with geocoding if needed
        $mapLocation = null;
        if (!empty($locationRows) && isset($locationRows[0])) {
            $firstLoc = $locationRows[0];
            $address = trim(($firstLoc['city'] ?? '') . ', ' . ($firstLoc['state'] ?? '') . ', ' . ($firstLoc['country'] ?? 'India'), ', ');
            
            $latitude = $firstLoc['latitude'] ?? null;
            $longitude = $firstLoc['longitude'] ?? null;
            
            // If coordinates are missing, try to geocode the address
            if (empty($latitude) || empty($longitude)) {
                $coordinates = $this->geocodeAddress($address);
                if ($coordinates) {
                    $latitude = $coordinates['lat'];
                    $longitude = $coordinates['lon'];
                    
                    // Update database with coordinates for future use
                    try {
                        $db->query(
                            "UPDATE job_locations jl 
                             SET latitude = :lat, longitude = :lon 
                             WHERE jl.job_id = :job_id 
                               AND (
                                   jl.city_id = (SELECT id FROM cities WHERE name = :city LIMIT 1)
                                   OR jl.state_id = (SELECT id FROM states WHERE name = :state LIMIT 1)
                               )",
                            [
                                'lat' => $latitude,
                                'lon' => $longitude,
                                'job_id' => $jobId,
                                'city' => $firstLoc['city'] ?? '',
                                'state' => $firstLoc['state'] ?? ''
                            ]
                        );
                    } catch (\Exception $e) {
                        error_log("Error updating location coordinates: " . $e->getMessage());
                    }
                }
            }
            
            $mapLocation = [
                'city' => $firstLoc['city'] ?? '',
                'state' => $firstLoc['state'] ?? '',
                'country' => $firstLoc['country'] ?? 'India',
                'address' => $address,
                'latitude' => $latitude,
                'longitude' => $longitude
            ];
        }
        
        // Get candidate ID and user ID safely
        $candidateId = $candidate->attributes['id'] ?? null;
        $userId = $candidate->attributes['user_id'] ?? null;
        
        $jobData['is_bookmarked'] = ($candidateId && $jobId) 
            ? $this->isBookmarked($candidateId, $jobId) 
            : false;
        $jobData['has_applied'] = ($userId && $jobId) 
            ? $this->hasApplied((int)$userId, $jobId) 
            : false;

        // Get application count
        $applications = $job->applications();
        $jobData['application_count'] = count($applications);
        
        // Ensure all fields have default values
        $jobData['id'] = (int)($jobData['id'] ?? $jobId);
        $jobData['title'] = $jobData['title'] ?? 'Job Title Not Available';
        $jobData['job_timings'] = $jobData['job_timings'] ?? '';
        $jobData['interview_timings'] = $jobData['interview_timings'] ?? '';
        $jobData['salary_min'] = (int)($jobData['salary_min'] ?? 0);
        $jobData['salary_max'] = (int)($jobData['salary_max'] ?? 0);
        $jobData['vacancies'] = (int)($jobData['vacancies'] ?? 1);
        $jobData['is_remote'] = (int)($jobData['is_remote'] ?? 0);
        $jobData['description'] = $jobData['description'] ?? '';
        $jobData['created_at'] = $jobData['created_at'] ?? date('Y-m-d H:i:s');
        $jobData['created_at_formatted'] = !empty($jobData['created_at']) 
            ? date('M d, Y', strtotime($jobData['created_at'])) 
            : 'Recently';

        // Fetch interview blogs from blog_category_map (interview category)
        $interviewBlogs = [];
        try {
            $interviewBlogs = $db->fetchAll(
                "SELECT DISTINCT b.* FROM blogs b
                 INNER JOIN blog_category_map bcm ON bcm.blog_id = b.id
                 INNER JOIN blog_categories bc ON bc.id = bcm.category_id
                 WHERE (bc.slug = 'interview' OR bc.name LIKE '%interview%' OR bc.name LIKE '%Interview%')
                   AND b.published_at IS NOT NULL
                   AND b.status_id = 1
                 ORDER BY b.published_at DESC
                 LIMIT 10"
            );
            
            // If no blogs found with 'interview' category, try to get any published blogs
            if (empty($interviewBlogs)) {
                $interviewBlogs = $db->fetchAll(
                    "SELECT b.* FROM blogs b
                     WHERE b.published_at IS NOT NULL
                       AND b.status_id = 1
                     ORDER BY b.published_at DESC
                     LIMIT 10"
                );
            }
        } catch (\Exception $e) {
            error_log("Error fetching interview blogs: " . $e->getMessage());
            // Fallback: try to get any published blogs
            try {
                $interviewBlogs = $db->fetchAll(
                    "SELECT b.* FROM blogs b
                     WHERE b.published_at IS NOT NULL
                       AND b.status_id = 1
                     ORDER BY b.created_at DESC
                     LIMIT 10"
                );
            } catch (\Exception $e2) {
                error_log("Error fetching fallback blogs: " . $e2->getMessage());
            }
        }

        $response->view('candidate/jobs/show', [
            'relatedJobs' => $relatedJobs,
            'mapLocation' => $mapLocation,
            'interviewBlogs' => $interviewBlogs,
            'title' => $jobData['title'],
            'candidate' => $candidate,
            'job' => $jobData
        ]);
    }

    /**
     * Apply for job
     */
    public function apply(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        // Get user ID from session (more reliable than candidate attributes)
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->json(['error' => 'User ID not found. Please login again.'], 401);
            return;
        }
        $userId = (int)$userId;

        // Get job slug from route parameter
        $slug = $request->param('slug') ?? $request->get('slug') ?? '';
        if (empty($slug)) {
            $response->json(['error' => 'Invalid job slug'], 400);
            return;
        }
        
        $job = Job::findBySlug($slug);
        if (!$job) {
            $response->json(['error' => 'Job not found'], 404);
            return;
        }
        
        $jobId = $job->attributes['id'] ?? $job->id ?? null;
        if (!$jobId) {
            $response->json(['error' => 'Job ID not found'], 404);
            return;
        }

        // Check if already applied
        if ($this->hasApplied($userId, $jobId)) {
            $response->json(['error' => 'You have already applied for this job'], 400);
            return;
        }

        $data = $request->getJsonBody() ?? $request->all();

        // Create application
        $application = new Application();
        $application->fill([
            'job_id' => $jobId,
            'candidate_user_id' => $userId,
            'resume_url' => $data['resume_url'] ?? $candidate->attributes['resume_url'] ?? '',
            'cover_letter' => $data['cover_letter'] ?? '',
            'expected_salary' => $data['expected_salary'] ?? $candidate->attributes['expected_salary_min'] ?? null,
            'status' => 'applied',
            'score' => $this->calculateMatchScore($candidate, $job),
            'source' => 'portal'
        ]);

        if ($application->save()) {
            // Log application event
            try {
                $event = new \App\Models\ApplicationEvent();
                $event->fill([
                    'application_id' => $application->attributes['id'] ?? $application->id,
                    'actor_user_id' => $userId,
                    'from_status' => null,
                    'to_status' => 'applied',
                    'comment' => 'Application submitted'
                ]);
                $event->save();
            } catch (\Exception $e) {
                error_log("Failed to save application event: " . $e->getMessage());
            }

            // Send notification to employer
            $employer = $job->employer();
            if ($employer && $employerUser = $employer->user()) {
                try {
                    NotificationService::send(
                        (int)$employerUser->id,
                        'application_received',
                        'New Application Received',
                        "New application received for {$job->title} from " . ($candidate->full_name ?? 'a candidate'),
                        [
                            'job_id' => $jobId, 
                            'application_id' => $application->attributes['id'] ?? $application->id,
                            'email_template' => 'employer_application_received'
                        ],
                        "/employer/applications/" . ($application->attributes['id'] ?? $application->id)
                    );
                } catch (\Exception $e) {
                    error_log("Failed to send employer notification: " . $e->getMessage());
                }
            }

            // Send notification to candidate
            try {
                NotificationService::send(
                    (int)$userId,
                    'application_sent',
                    'Application Submitted',
                    "You have successfully applied for {$job->title} at " . ($job->company_name ?? 'the company'),
                    [
                        'job_id' => $jobId, 
                        'application_id' => $application->attributes['id'] ?? $application->id,
                        'email_template' => 'candidate_application_submitted'
                    ],
                    "/candidate/applications"
                );
            } catch (\Exception $e) {
                error_log("Failed to send candidate notification: " . $e->getMessage());
            }

            $response->json([
                'success' => true,
                'message' => 'Application submitted successfully!',
                'application_id' => $application->attributes['id'] ?? $application->id
            ]);
        } else {
            error_log("Failed to save application. Job ID: {$jobId}, User ID: {$userId}");
            $response->json(['error' => 'Failed to submit application'], 500);
        }
    }

    /**
     * Bookmark job
     */
    public function bookmark(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        // Get job slug from route parameter
        $slug = $request->param('slug') ?? $request->get('slug') ?? '';
        if (empty($slug)) {
            $response->json(['error' => 'Invalid job slug'], 400);
            return;
        }
        
        $job = Job::findBySlug($slug);
        if (!$job) {
            $response->json(['error' => 'Job not found'], 404);
            return;
        }
        
        $jobId = $job->attributes['id'];

        $candidateId = $candidate->attributes['id'] ?? null;
        if (!$candidateId) {
            $response->json(['error' => 'Candidate ID not found'], 400);
            return;
        }
        
        // Check if already bookmarked
        $existing = JobBookmark::where('candidate_id', '=', $candidateId)
            ->where('job_id', '=', $jobId)
            ->first();

        if ($existing) {
            // Remove bookmark
            $existing->delete();
            $response->json(['success' => true, 'bookmarked' => false]);
        } else {
            // Add bookmark
            $bookmark = new JobBookmark();
            $bookmark->fill([
                'candidate_id' => $candidateId,
                'job_id' => $jobId
            ]);
            if ($bookmark->save()) {
                $response->json(['success' => true, 'bookmarked' => true]);
            } else {
                $response->json(['error' => 'Failed to bookmark job'], 500);
            }
        }
    }

    /**
     * Saved Jobs (My Jobs) - Indeed-style page
     */
    public function savedJobs(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $candidateId = $candidate->attributes['id'] ?? null;
        if (!$candidateId) {
            $response->redirect('/candidate/dashboard');
            return;
        }

        // Get all bookmarked jobs
        $bookmarks = JobBookmark::where('candidate_id', '=', $candidateId)
            ->orderBy('created_at', 'DESC')
            ->get();

        $savedJobs = [];
        $db = \App\Core\Database::getInstance();
        
        foreach ($bookmarks as $bookmark) {
            /** @var \App\Models\Job|null $job */
            $job = Job::find($bookmark->attributes['job_id']);
            if ($job && ($job->attributes['status'] ?? '') === 'published') {
                $jobData = $job->attributes;
                $employer = $job->employer();
                $jobData['company_name'] = $employer ? $employer->attributes['company_name'] : '';
                $jobData['company_logo'] = $employer ? $employer->attributes['logo_url'] : null;
                
                // Get location - support both normalized (city_id/state_id) and denormalized (city/state) schemas
                $locationStrings = [];
                $locations = [];
                try {
                    $locations = $db->fetchAll(
                        "SELECT 
                            c.name as city, 
                            s.name as state, 
                            cnt.name as country 
                         FROM job_locations jl
                         LEFT JOIN cities c ON jl.city_id = c.id
                         LEFT JOIN states s ON jl.state_id = s.id
                         LEFT JOIN countries cnt ON jl.country_id = cnt.id
                         WHERE jl.job_id = :job_id",
                        ['job_id' => $job->attributes['id']]
                    );
                } catch (\Exception $e) {
                    try {
                        $locations = $db->fetchAll(
                            "SELECT city, state, country FROM job_locations WHERE job_id = :job_id",
                            ['job_id' => $job->attributes['id']]
                        );
                    } catch (\Exception $e2) {
                        $locations = [];
                    }
                }
                foreach ($locations as $loc) {
                    $locParts = array_filter([
                        trim($loc['city'] ?? ''),
                        trim($loc['state'] ?? ''),
                        trim($loc['country'] ?? '')
                    ]);
                    if (!empty($locParts)) {
                        $locationStrings[] = implode(', ', $locParts);
                    }
                }
                // Fallback to jobs.locations JSON if present
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
                    } elseif (is_string($jobData['locations'])) {
                        $locationStrings[] = $jobData['locations'];
                    }
                }
                $jobData['location_display'] = !empty($locationStrings) ? implode(' | ', $locationStrings) : 'Location not specified';
                
                // Format employment type
                $empType = $jobData['employment_type'] ?? 'full_time';
                $empTypeMap = [
                    'full_time' => 'Full-time',
                    'part_time' => 'Part-time',
                    'contract' => 'Contract',
                    'internship' => 'Internship',
                    'freelance' => 'Freelance'
                ];
                $jobData['employment_type_display'] = $empTypeMap[$empType] ?? ucfirst(str_replace('_', ' ', $empType));
                
                // Format salary
                $currency = $jobData['currency'] ?? 'INR';
                $symbol = $currency === 'USD' ? '$' : ($currency === 'EUR' ? '€' : ($currency === 'GBP' ? '£' : '₹'));
                if (($jobData['salary_min'] ?? 0) > 0 && ($jobData['salary_max'] ?? 0) > 0) {
                    $jobData['salary_display'] = $symbol . number_format($jobData['salary_min']) . '-' . $symbol . number_format($jobData['salary_max']);
                } elseif (($jobData['salary_min'] ?? 0) > 0) {
                    $jobData['salary_display'] = $symbol . number_format($jobData['salary_min']);
                } else {
                    $jobData['salary_display'] = '';
                }
                
                $jobData['saved_at'] = $bookmark->attributes['created_at'] ?? date('Y-m-d H:i:s');
                $savedJobs[] = $jobData;
            }
        }

        // Get applications for status
        $userId = $candidate->attributes['user_id'] ?? null;
        $applications = [];
        if ($userId) {
            $appList = Application::where('candidate_user_id', '=', $userId)->get();
            foreach ($appList as $app) {
                $applications[$app->attributes['job_id']] = $app->attributes['status'] ?? 'applied';
            }
        }

        // Get applied jobs
        $appliedJobs = [];
        foreach ($savedJobs as $job) {
            if (isset($applications[$job['id']])) {
                $appliedJobs[] = $job;
            }
        }

        // Get interviews (if any)
        $interviewJobs = [];
        foreach ($appliedJobs as $job) {
            if (($applications[$job['id']] ?? '') === 'interview') {
                $interviewJobs[] = $job;
            }
        }

        $response->view('candidate/jobs/saved', [
            'title' => 'My jobs',
            'candidate' => $candidate,
            'savedJobs' => $savedJobs,
            'appliedJobs' => $appliedJobs,
            'interviewJobs' => $interviewJobs,
            'applications' => $applications
        ]);
    }

    private function calculateMatchScore(Candidate $candidate, Job $job): int
    {
        try {
            $service = new JobMatchService();
            // Don't store results every time we just view/list, only when needed or background job
            // For now, let's not store to avoid writing to DB on every page load
            $match = $service->calculateMatch((int)$candidate->id, (int)$job->id, false);
            return $match['overall_match_score'] ?? 0;
        } catch (\Exception $e) {
            error_log("Error calculating match score: " . $e->getMessage());
            return 0;
        }
    }

    private function isBookmarked(int $candidateId, int $jobId): bool
    {
        $bookmark = JobBookmark::where('candidate_id', '=', $candidateId)
            ->where('job_id', '=', $jobId)
            ->first();
        return $bookmark !== null;
    }

    private function hasApplied(int $userId, int $jobId): bool
    {
        $application = Application::where('candidate_user_id', '=', $userId)
            ->where('job_id', '=', $jobId)
            ->first();
        return $application !== null;
    }

    private function trackJobView(int $candidateId, int $jobId): void
    {
        // Check if already viewed today
        $today = date('Y-m-d');
        $existing = JobView::where('candidate_id', '=', $candidateId)
            ->where('job_id', '=', $jobId)
            ->where('viewed_at', '>=', $today)
            ->first();

        if (!$existing) {
            $view = new JobView();
            $view->fill([
                'candidate_id' => $candidateId,
                'job_id' => $jobId
            ]);
            $view->save();
        }
    }
    
    /**
     * Geocode an address to get latitude and longitude
     * Uses OpenStreetMap Nominatim API (free, no API key required)
     */
    private function geocodeAddress(string $address): ?array
    {
        if (empty($address)) {
            return null;
        }
        
        try {
            // Use OpenStreetMap Nominatim API
            $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
                'addressdetails' => 1
            ]);
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: MindwareInfotech/1.0 (Job Portal)',
                        'Accept: application/json'
                    ],
                    'timeout' => 5
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response === false) {
                error_log("Geocoding failed for address: " . $address);
                return null;
            }
            
            $data = json_decode($response, true);
            
            if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
                return [
                    'lat' => (float)$data[0]['lat'],
                    'lon' => (float)$data[0]['lon']
                ];
            }
            
            return null;
        } catch (\Exception $e) {
            error_log("Geocoding error for address '{$address}': " . $e->getMessage());
            return null;
        }
    }
}
