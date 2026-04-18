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
                $jobData['location_display'] = !empty($strings) ? implode(' | ', $strings) : ($jobData['is_remote'] == 1 ? 'Remote' : 'Location not specified');
            } else {
                $jobData['location_display'] = $jobData['is_remote'] == 1 ? 'Remote' : 'Location not specified';
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