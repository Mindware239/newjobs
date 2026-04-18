<?php
namespace App\Services;

use App\Repositories\JobRepository;
use App\Core\Database;
use App\Helpers\FormatHelper;
use App\Models\JobLocation;

class HomeService
{
    private JobRepository $jobRepository;
    private Database $db;

    public function __construct()
    {
        $this->jobRepository = new JobRepository();
        $this->db = Database::getInstance(); // We use raw DB for non-job queries here temporarily to avoid making too many repos
    }

    /**
     * Gathers all data required for the Home Page
     */
    public function getHomeData(): array
    {
        $recentJobsRaw = $this->jobRepository->getRecentPublishedJobs(6);
        
        $recentJobs = [];
        $typedRoles = [];

        // Format Job Data
        foreach ($recentJobsRaw as $row) {
            if (empty($row)) continue;

            $jobData = $row;
            $jobData['id'] = (int)($row['id'] ?? 0);
            
            // Format slug
            if (empty($jobData['slug']) && !empty($jobData['title'])) {
                $jobData['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $jobData['title'])));
            }

            // Location formatting using the GROUP_CONCAT result or JSON fallback
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

            $jobData['employment_type_display'] = FormatHelper::formatEmploymentType($jobData['employment_type'] ?? null);
            
            $salaryInfo = FormatHelper::formatSalary($jobData['salary_min'] ?? null, $jobData['salary_max'] ?? null, $jobData['currency'] ?? 'INR');
            $jobData['salary_min'] = $salaryInfo['min'];
            $jobData['salary_max'] = $salaryInfo['max'];
            $jobData['currency'] = $salaryInfo['currency'];

            $jobData['time_ago'] = FormatHelper::timeAgo($jobData['created_at'] ?? null);

            $jobData['company_name'] = $jobData['company_name'] ?? 'Company Name Not Available';
            $jobData['company_logo'] = $jobData['company_logo'] ?? null;
            $jobData['industry'] = $jobData['industry'] ?? 'Industry';
            $jobData['is_remote'] = (int)($jobData['is_remote'] ?? 0);

            $recentJobs[] = $jobData;

            // Collect typed roles for hero section
            $t = trim((string)($jobData['title'] ?? ''));
            if ($t !== '') $typedRoles[] = $t;
        }

        // Ensure we have enough typed roles
        if (count($typedRoles) < 5) {
            $fallback = ['Blockchain Engineer', 'Data Scientist', 'Frontend Developer', 'Backend Developer', 'DevOps Engineer', 'Mobile Developer'];
            $typedRoles = array_values(array_unique(array_merge($typedRoles, $fallback)));
        } else {
            $typedRoles = array_values(array_unique($typedRoles));
        }

        // Other aggregated data
        $categories = $this->jobRepository->getCategoriesWithJobCount(10);
        $stats = $this->jobRepository->getJobStats();

        // Fetch Testimonials, Blogs, Logos (ideally in their own Repositories)
        $clientTestimonials = [];
        $candidateTestimonials = [];
        $homeBlogs = [];
        $employerLogos = [];
        $locations = [];
        
        try {
            $clientTestimonials = $this->db->fetchAll("SELECT * FROM testimonials WHERE testimonial_type = 'client' AND is_active = 1 ORDER BY created_at DESC LIMIT 12");
            $candidateTestimonials = $this->db->fetchAll("SELECT * FROM testimonials WHERE testimonial_type = 'candidate' AND is_active = 1 ORDER BY created_at DESC LIMIT 12");
            
            $homeBlogs = $this->db->fetchAll("
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

            $rawLocs = JobLocation::getDistinctRaw();
            foreach ($rawLocs as $r) {
                $parts = array_filter([trim($r['city'] ?? ''), trim($r['state'] ?? ''), trim($r['country'] ?? '')]);
                if (!empty($parts)) {
                    $locations[] = [
                        'city' => trim($r['city'] ?? ''),
                        'state' => trim($r['state'] ?? ''),
                        'country' => trim($r['country'] ?? ''),
                        'display_name' => implode(', ', $parts)
                    ];
                }
            }

            $employerLogos = $this->db->fetchAll("
                SELECT company_name, logo_url
                FROM employers
                WHERE (logo_url IS NOT NULL AND logo_url <> '')
                ORDER BY verified DESC, created_at DESC
                LIMIT 12
            ");
        } catch (\Throwable $t) {
            error_log("Error fetching extra home content: " . $t->getMessage());
        }

        return [
            'jobs' => $recentJobs,
            'categories' => $categories,
            'stats' => $stats,
            'testimonials_client' => $clientTestimonials,
            'testimonials_candidate' => $candidateTestimonials,
            'blogs' => $homeBlogs,
            'locations' => $locations,
            'employerLogos' => $employerLogos,
            'typedRoles' => $typedRoles,
        ];
    }
}