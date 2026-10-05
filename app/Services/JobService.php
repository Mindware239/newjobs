<?php
namespace App\Services;

use App\Repositories\JobRepository;
use App\Helpers\FormatHelper;
use App\Services\SeoService;

class JobService
{
    private JobRepository $repo;

    public function __construct()
    {
        $this->repo = new JobRepository();
    }

    public function getCategoriesGrouped(): array
    {
        $categories = $this->repo->getAllCategoriesWithCounts();
        $grouped = [];
        foreach ($categories as $cat) {
            $name = $cat['name'] ?? '';
            $firstLetter = strtoupper(substr($name, 0, 1));
            if (!ctype_alpha($firstLetter)) $firstLetter = '#';
            $grouped[$firstLetter][] = $cat;
        }
        return $grouped;
    }

    public function getJobsByLocation(string $slug, int $page, int $perPage = 20): ?array
    {
        $synonyms = [
            'new-delhi' => 'delhi',
            'gurgaon' => 'gurugram',
            'bangalore' => 'bengaluru',
            'bombay' => 'mumbai',
            'madras' => 'chennai',
        ];
        $slugCanonical = $synonyms[$slug] ?? $slug;

        $location = $this->repo->getLocationBySlug($slug, $slugCanonical);
        if (!$location) return null;

        $locationType = $location['type'];
        $locationId = (int)$location['id'];
        $locationName = $location['name'];

        $jobCount = $this->repo->countJobsByLocation($locationType, $locationId);
        $topTitles = $this->repo->getTopTitlesByLocation($locationType, $locationId);
        
        // SEO logic moved here
        SeoService::getInstance()->resolve('location_jobs', [
            'location' => $locationName,
            'type' => $locationType,
            'job_count' => $jobCount,
            'top_titles' => $topTitles
        ]);

        $breadcrumbs = $this->buildLocationBreadcrumbs($locationType, $locationId, $locationName, $slug);
        
        $jobsRaw = $this->repo->getJobsByLocation($locationType, $locationId, $page, $perPage);
        $jobs = $this->formatJobsList($jobsRaw);

        return [
            'jobs' => $jobs,
            'filters' => ['location' => $locationName],
            'pageTitle' => "Jobs in $locationName",
            'breadcrumbs' => $breadcrumbs,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $jobCount,
                'total_pages' => max(1, (int)ceil($jobCount / $perPage))
            ]
        ];
    }

    public function getJobsByRoleAndLocation(string $roleSlug, string $locationSlug, int $page, int $perPage = 20): ?array
    {
        $synonyms = [
            'new-delhi' => 'delhi',
            'gurgaon' => 'gurugram',
            'bangalore' => 'bengaluru',
            'bombay' => 'mumbai',
            'madras' => 'chennai',
        ];
        $slugCanonical = $synonyms[$locationSlug] ?? $locationSlug;

        $location = $this->repo->getLocationBySlug($locationSlug, $slugCanonical);
        if (!$location) return null;

        $locationType = $location['type'];
        $locationId = (int)$location['id'];
        $locationName = $location['name'];

        $skill = $this->repo->getSkillBySlug($roleSlug);
        $roleName = $skill ? $skill['name'] : ucfirst(str_replace('-', ' ', $roleSlug));

        $breadcrumbs = $this->buildLocationBreadcrumbs($locationType, $locationId, $locationName, $locationSlug);
        $breadcrumbs[] = ['name' => "$roleName Jobs", 'url' => "/$roleSlug-jobs-in-$locationSlug"];

        SeoService::getInstance()->resolve('role_location_jobs', [
            'role' => $roleName,
            'location' => $locationName,
            'type' => $locationType,
            'breadcrumbs' => $breadcrumbs
        ]);

        $totalJobs = $this->repo->countJobsByRoleAndLocation($locationType, $locationId, $skill, $roleName);
        $jobsRaw = $this->repo->getJobsByRoleAndLocation($locationType, $locationId, $skill, $roleName, $page, $perPage);
        $jobs = $this->formatJobsList($jobsRaw);

        return [
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
        ];
    }

    public function getJobsByCategory(string $slug, int $page, int $perPage = 20): ?array
    {
        $category = $this->repo->getCategoryBySlugOrName($slug);
        if (!$category) return null;

        $categoryName = $category['name'];
        $totalJobs = $this->repo->countJobsByCategory($categoryName);

        SeoService::getInstance()->resolve('category_jobs', [
            'category' => $categoryName,
            'job_count' => $totalJobs
        ]);

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Jobs', 'url' => '/jobs'],
            ['name' => "Jobs in {$categoryName}", 'url' => '/jobs-in-category/' . ($category['slug'] ?? $slug)]
        ];

        $jobsRaw = $this->repo->getJobsByCategory($categoryName, $page, $perPage);
        $jobs = $this->formatJobsList($jobsRaw);

        return [
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
        ];
    }

    public function searchJobs(array $filters, ?int $userId = null): array
    {
        $keyword = trim((string)($filters['keyword'] ?? ''));
        $location = trim((string)($filters['location'] ?? ''));
        $page = max(1, (int)($filters['page'] ?? 1));
        $perPage = max(1, min(100, (int)($filters['per_page'] ?? 20)));

        if (!empty($location) && empty($keyword)) {
            $slug = strtolower(str_replace(' ', '-', $location));
            $result = $this->getJobsByLocation($slug, $page, $perPage);
            if ($this->hasJobs($result)) {
                $result['search'] = $this->buildSearchMeta($keyword, $location, 'exact');
                return $result;
            }
        }

        if (!empty($location) && !empty($keyword)) {
            $roleSlug = strtolower(str_replace(' ', '-', $keyword));
            $locSlug = strtolower(str_replace(' ', '-', $location));
            $result = $this->getJobsByRoleAndLocation($roleSlug, $locSlug, $page, $perPage);
            if ($this->hasJobs($result)) {
                $result['search'] = $this->buildSearchMeta($keyword, $location, 'exact');
                return $result;
            }
        }

        $jobsRaw = $this->repo->searchJobs($keyword, $location, $page, $perPage);
        $total = $this->repo->countSearchJobs($keyword, $location);
        $mode = 'exact';
        $message = null;

        if ($total === 0 && $keyword !== '' && $location !== '') {
            $jobsRaw = $this->repo->searchJobs($keyword, '', $page, $perPage);
            $total = $this->repo->countSearchJobs($keyword, '');
            if ($total > 0) {
                $mode = 'keyword_fallback';
                $message = "No exact jobs found in {$location}. Showing matching jobs from other locations.";
            }
        }

        return [
            'jobs' => $this->formatJobsList($jobsRaw),
            'filters' => [
                'keyword' => $keyword,
                'location' => $location
            ],
            'pageTitle' => $this->buildSearchTitle($keyword, $location),
            'message' => $message,
            'search' => $this->buildSearchMeta($keyword, $location, $mode, $message),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => max(1, (int)ceil($total / $perPage))
            ]
        ];
    }

    private function hasJobs(?array $result): bool
    {
        return $result !== null && (int)($result['pagination']['total'] ?? 0) > 0;
    }

    private function buildSearchTitle(string $keyword, string $location): string
    {
        if ($keyword !== '' && $location !== '') {
            return "{$keyword} Jobs in {$location}";
        }

        if ($keyword !== '') {
            return "{$keyword} Jobs";
        }

        if ($location !== '') {
            return "Jobs in {$location}";
        }

        return 'Latest Jobs';
    }

    private function buildSearchMeta(string $keyword, string $location, string $mode, ?string $message = null): array
    {
        return [
            'keyword' => $keyword,
            'location' => $location,
            'mode' => $mode,
            'is_exact_match' => $mode === 'exact',
            'message' => $message
        ];
    }

    public function getJobBySlug(string $slug, ?int $userId = null): ?array
    {
        $jobModel = \App\Models\Job::findBySlug($slug);
        if (!$jobModel) return null;

        $jobData = $jobModel->toArray();

        // Add related info
        $jobData['employer'] = $jobModel->employer() ? $jobModel->employer()->toArray() : null;
        $jobData['skills'] = $jobModel->skills();
        $jobData['benefits'] = $jobModel->benefits();
        $jobData['qualifications'] = $jobModel->qualifications();

        // Format locations
        $locations = $jobModel->locations();
        $jobData['locations'] = $locations; // Return structured locations
        
        $locStrings = [];
        foreach ($locations as $loc) {
            if (is_object($loc)) {
                $locStrings[] = implode(', ', array_filter([$loc->city ?? $loc->city_name ?? '', $loc->state ?? $loc->state_name ?? '', $loc->country ?? $loc->country_name ?? '']));
            } elseif (is_array($loc)) {
                 $locStrings[] = implode(', ', array_filter([$loc['city'] ?? '', $loc['state'] ?? '', $loc['country'] ?? '']));
            }
        }
        $jobData['location_display'] = !empty($locStrings) ? implode(' | ', $locStrings) : ($jobModel->is_remote == 1 ? 'Remote' : 'Location not specified');

        return $jobData;
    }

    public function applyForJob(int $jobId, int $candidateId, int $userId): array
    {
        $existing = \App\Models\Application::where('job_id', '=', $jobId)
            ->where('candidate_user_id', '=', $userId)
            ->first();

        if ($existing) {
            return ['success' => false, 'message' => 'You have already applied for this job', 'code' => 400];
        }

        $application = new \App\Models\Application();
        $application->fill([
            'job_id' => $jobId,
            'candidate_id' => $candidateId,
            'candidate_user_id' => $userId,
            'status' => 'applied',
            'applied_at' => date('Y-m-d H:i:s')
        ]);

        if ($application->save()) {
            return ['success' => true, 'application_id' => $application->id];
        }

        return ['success' => false, 'message' => 'Failed to submit application', 'code' => 500];
    }

    private function formatJobsList(array $jobsRaw): array
    {
        $jobs = [];
        foreach ($jobsRaw as $row) {
            $jobData = $row;
            $jobData['is_bookmarked'] = false;
            
            $salaryInfo = FormatHelper::formatSalary($jobData['salary_min'] ?? null, $jobData['salary_max'] ?? null, $jobData['currency'] ?? 'INR');
            $jobData['salary_min'] = $salaryInfo['min'];
            $jobData['salary_max'] = $salaryInfo['max'];
            $jobData['currency'] = $salaryInfo['currency'];
            
            $jobData['is_remote'] = (int)($jobData['is_remote'] ?? 0);
            
            if (!empty($row['location_names'])) {
                $jobData['location_display'] = $row['location_names'];
            } elseif (!empty($jobData['locations'])) {
                $locs = json_decode($jobData['locations'], true);
                $strings = [];
                if (is_array($locs)) {
                    foreach ($locs as $loc) {
                        if (is_string($loc)) {
                            $strings[] = $loc;
                        } elseif (is_array($loc)) {
                            $strings[] = implode(', ', array_filter([$loc['city'] ?? '', $loc['state'] ?? '', $loc['country'] ?? '']));
                        }
                    }
                }
                $jobData['location_display'] = !empty($strings) ? implode(' | ', $strings) : (!empty($jobData['location']) ? $jobData['location'] : ($jobData['is_remote'] == 1 ? 'Remote' : 'Location not specified'));
            } else {
                $jobData['location_display'] = !empty($jobData['location']) ? $jobData['location'] : ($jobData['is_remote'] == 1 ? 'Remote' : 'Location not specified');
            }

            $jobs[] = $jobData;
        }
        return $jobs;
    }

    private function buildLocationBreadcrumbs(string $locationType, int $locationId, string $locationName, string $slug): array
    {
        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Jobs', 'url' => '/jobs']
        ];

        if ($locationType === 'city') {
            $cityFull = $this->repo->getCityFullDetails($locationId);
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
            $stateFull = $this->repo->getStateFullDetails($locationId);
            if ($stateFull) {
                if (!empty($stateFull['country_name'])) {
                    $breadcrumbs[] = ['name' => $stateFull['country_name'], 'url' => '/jobs-in-' . $stateFull['country_slug']];
                }
                $breadcrumbs[] = ['name' => $stateFull['state_name'], 'url' => '/jobs-in-' . $stateFull['state_slug']];
            }
        } else {
            $breadcrumbs[] = ['name' => $locationName, 'url' => '/jobs-in-' . $slug];
        }

        return $breadcrumbs;
    }
}
