<?php

namespace App\Services;

use App\Models\SeoRule;
use App\Models\Job;
use App\Models\Company;
use App\Models\City;
use App\Core\Database;

class SeoService
{
    private static ?SeoService $instance = null;
    private array $currentMeta = [];
    private ?string $pageType = null;
    private array $data = [];
    private bool $resolved = false;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->setDefaultMeta();
        }
        return self::$instance;
    }

    /**
     * Resolve SEO metadata based on page type and parameters
     */
    public function resolve(string $pageType, array $data = []): self
    {
        $this->pageType = $pageType;
        $this->data = $data;
        $this->resolved = true;

        // Try cache first
        if ($cached = $this->getFromCache($pageType, $data)) {
            $this->currentMeta = $cached;
            $this->logSeoPage(); 
            return $this;
        }

        // Load rules from DB
        $rule = SeoRule::findByPageType($pageType);

        if (!$rule) {
            $this->applyFallbackSeo($pageType, $data);
            return $this;
        }

        // Replace placeholders
        $this->currentMeta = [
            'title' => $this->replacePlaceholders($rule->meta_title_template, $data),
            'description' => $this->replacePlaceholders($rule->meta_description_template, $data),
            'keywords' => $this->generateKeywords($pageType, $data, $this->replacePlaceholders($rule->meta_keywords_template ?? '', $data)),
            'h1' => $this->replacePlaceholders($rule->h1_template, $data),
            'canonical' => $this->generateCanonical($rule->canonical_rule, $data),
            'robots' => $rule->indexable ? 'index, follow' : 'noindex, nofollow',
            'og_type' => 'website', // default
            'twitter_card' => 'summary_large_image',
            'json_ld' => $this->generateJsonLd($pageType, $data)
        ];

        // Specific overrides
        if ($pageType === 'job_detail') {
            $this->currentMeta['og_type'] = 'article';
        }
        
        // Handle filters (noindex)
        // If there are query parameters other than 'page', treat as filter/duplicate
        $queryParams = $_GET ?? [];
        unset($queryParams['page']); // Allow pagination to be indexed (or handled separately)
        if (!empty($queryParams) && $this->currentMeta['robots'] === 'index, follow') {
            $this->currentMeta['robots'] = 'noindex, nofollow';
        }

        // Save to cache
        $this->saveToCache($pageType, $data, $this->currentMeta);

        $this->logSeoPage();

        return $this;
    }

    private function getFromCache(string $pageType, array $data): ?array
    {
        $cacheFile = $this->getCacheFilePath($pageType, $data);
        
        if (!file_exists($cacheFile)) {
            return null;
        }

        // Cache lifetime (e.g., 24 hours)
        if (time() - filemtime($cacheFile) > 86400) {
            unlink($cacheFile);
            return null;
        }

        $content = file_get_contents($cacheFile);
        return $content ? json_decode($content, true) : null;
    }

    private function saveToCache(string $pageType, array $data, array $meta): void
    {
        $cacheFile = $this->getCacheFilePath($pageType, $data);
        $dir = dirname($cacheFile);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($cacheFile, json_encode($meta));
    }

    private function getCacheFilePath(string $pageType, array $data): string
    {
        // Optimization: Create a unique key based on page type and ONLY scalar data
        // Filter data to remove objects and large arrays that might cause cache misses or bloat
        $cacheData = array_filter($data, function($value) {
            return is_scalar($value) || (is_array($value) && count($value) < 10);
        });
        
        ksort($cacheData);
        $key = $pageType . '_' . md5(json_encode($cacheData));
        
        $cacheDir = __DIR__ . '/../../storage/cache/seo';
        
        return $cacheDir . '/' . $key . '.json';
    }

    private function replacePlaceholders(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $template = str_replace('{' . $key . '}', (string)$value, $template);
            }
        }
        
        // Clear remaining placeholders
        return preg_replace('/\{[a-zA-Z0-9_]+\}/', '', $template);
    }

    private function generateCanonical(string $rule, array $data): string
    {
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $pathParts = explode('?', $path);
        $cleanPath = $pathParts[0];

        // Logic for specific pages to ensure clean URLs
        if (isset($data['canonical_path'])) {
             $url = rtrim($baseUrl, '/') . '/' . ltrim($data['canonical_path'], '/');
        } else {
             $url = rtrim($baseUrl, '/') . $cleanPath;
        }

        // Fix Pagination Canonical: Append page parameter if present
        if (isset($_GET['page']) && is_numeric($_GET['page']) && (int)$_GET['page'] > 1) {
            $url .= '?page=' . (int)$_GET['page'];
        }

        return $url;
    }

    private function generateJsonLd(string $pageType, array $data): ?string
    {
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
        $schemas = [];

        // 1. BreadcrumbList (Priority 7)
        $breadcrumbs = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => []
        ];

        if (isset($data['breadcrumbs']) && is_array($data['breadcrumbs'])) {
            foreach ($data['breadcrumbs'] as $index => $crumb) {
                $url = $crumb['url'] === '/' ? $baseUrl : rtrim($baseUrl, '/') . '/' . ltrim($crumb['url'], '/');
                $breadcrumbs['itemListElement'][] = [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $crumb['name'],
                    'item' => $url
                ];
            }
        } else {
            // Fallback for pages not passing breadcrumbs explicitly
            $breadcrumbs['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => $baseUrl
            ];

            if ($pageType === 'job_detail' && isset($data['job_title'])) {
                $breadcrumbs['itemListElement'][] = [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Jobs',
                    'item' => $baseUrl . '/jobs'
                ];
                $breadcrumbs['itemListElement'][] = [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $data['job_title'],
                    'item' => $this->currentMeta['canonical'] ?? ''
                ];
            } elseif ($pageType === 'city_jobs' && isset($data['city'])) {
                $breadcrumbs['itemListElement'][] = [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => "Jobs in " . $data['city'],
                    'item' => $this->currentMeta['canonical'] ?? ''
                ];
            }
        }

        $schemas[] = $breadcrumbs;

        // 2. JobPosting
        if ($pageType === 'job_detail' && isset($data['job'])) {
            $job = $data['job'];
            $get = fn($k) => is_object($job) ? ($job->$k ?? ($job->attributes[$k] ?? null)) : ($job[$k] ?? null);
            
            // Truncate description (Priority 5)
            $description = strip_tags($get('description') ?? '');
            if (strlen($description) > 5000) {
                $description = substr($description, 0, 4997) . '...';
            }

            $jobSchema = [
                '@context' => 'https://schema.org/',
                '@type' => 'JobPosting',
                'title' => $get('title'),
                'description' => $description,
                'datePosted' => $get('created_at'),
                'validThrough' => $get('expiry_date') ?? date('Y-m-d', strtotime('+30 days')), // Priority 4
                'employmentType' => $this->mapEmploymentType($get('employment_type')),
                'hiringOrganization' => [
                    '@type' => 'Organization',
                    'name' => $data['company_name'] ?? 'Confidential',
                    'logo' => $data['company_logo'] ?? ''
                ],
                'jobLocation' => [
                    '@type' => 'Place',
                    'address' => [
                        '@type' => 'PostalAddress',
                        'addressLocality' => $data['city'] ?? '',
                        'addressRegion' => $data['state'] ?? '',
                        'addressCountry' => $data['country'] ?? 'IN'
                    ]
                ]
            ];
            
            if ($min = $get('salary_min')) {
                $jobSchema['baseSalary'] = [
                    '@type' => 'MonetaryAmount',
                    'currency' => $get('currency') ?? 'INR',
                    'value' => [
                        '@type' => 'QuantitativeValue',
                        'minValue' => $min,
                        'maxValue' => $get('salary_max') ?? $min,
                        'unitText' => 'MONTH'
                    ]
                ];
            }
            $schemas[] = $jobSchema;
        }

        if ($pageType === 'company_detail') {
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => $data['company'] ?? 'Company',
                'url' => $this->currentMeta['canonical'] ?? '',
                'logo' => $data['company_logo'] ?? '',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $data['city'] ?? '',
                    'addressCountry' => 'IN'
                ]
            ];
        }

        // Return all schemas as a list or single
        if (count($schemas) === 1) {
            return json_encode($schemas[0], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }
        return json_encode($schemas, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    private function mapEmploymentType($type): string
    {
        $map = [
            'full_time' => 'FULL_TIME',
            'part_time' => 'PART_TIME',
            'contract' => 'CONTRACTOR',
            'internship' => 'INTERN',
            'freelance' => 'FREELANCE'
        ];
        return $map[$type] ?? 'FULL_TIME';
    }

   private function setDefaultMeta(): void
{
    $this->currentMeta = [
        'title' => 'Jobs & Internships in India | Latest Openings – Mindware Infotech',
        'description' => 'Search latest jobs and internships across India on Mindware Infotech. Explore IT, banking, healthcare, pharma, fresher and internship opportunities from verified employers.',
        'keywords' => 'jobs in india, internships in india, latest job openings, it jobs, banking jobs, healthcare jobs, pharma jobs, fresher jobs, internship opportunities, private jobs, government jobs',
        'h1' => 'Find Latest Jobs & Internships Across India',
        'canonical' => rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/'),
        'robots' => 'index, follow',
        'og_type' => 'website',
        'twitter_card' => 'summary_large_image',
        'json_ld' => null
    ];
}


    private function logSeoPage(): void
    {
        // Optimization: Only log unique URLs once per day to prevent table bloat
        try {
            $db = Database::getInstance();
            $url = $_SERVER['REQUEST_URI'] ?? '/';
            
            // Check if logged today
            $exists = $db->fetchOne(
                "SELECT id FROM seo_page_logs WHERE url = :url AND DATE(created_at) = CURDATE() LIMIT 1",
                ['url' => $url]
            );

            if (!$exists) {
                $db->execute(
                    "INSERT INTO seo_page_logs (url, page_type, meta_title, meta_description, status_code, created_at) VALUES (:url, :pt, :mt, :md, 200, NOW())",
                    [
                        'url' => $url,
                        'pt' => $this->pageType,
                        'mt' => $this->currentMeta['title'],
                        'md' => $this->currentMeta['description']
                    ]
                );
            }
        } catch (\Exception $e) {
            // Ignore logging errors
        }
    }

    public function render(): string
    {
        $meta = $this->currentMeta;
        $html = [];

        $html[] = "<title>" . htmlspecialchars($meta['title']) . "</title>";
        $html[] = "<meta name=\"description\" content=\"" . htmlspecialchars($meta['description']) . "\">";
        if ($meta['keywords']) {
            $html[] = "<meta name=\"keywords\" content=\"" . htmlspecialchars($meta['keywords']) . "\">";
        }
        $html[] = "<link rel=\"canonical\" href=\"" . htmlspecialchars($meta['canonical']) . "\">";
        $html[] = "<meta name=\"robots\" content=\"" . htmlspecialchars($meta['robots']) . "\">";

        // OG
        $html[] = "<meta property=\"og:title\" content=\"" . htmlspecialchars($meta['title']) . "\">";
        $html[] = "<meta property=\"og:description\" content=\"" . htmlspecialchars($meta['description']) . "\">";
        $html[] = "<meta property=\"og:type\" content=\"" . htmlspecialchars($meta['og_type']) . "\">";
        $html[] = "<meta property=\"og:url\" content=\"" . htmlspecialchars($meta['canonical']) . "\">";

        // Twitter
        $html[] = "<meta name=\"twitter:card\" content=\"" . htmlspecialchars($meta['twitter_card']) . "\">";
        $html[] = "<meta name=\"twitter:title\" content=\"" . htmlspecialchars($meta['title']) . "\">";
        $html[] = "<meta name=\"twitter:description\" content=\"" . htmlspecialchars($meta['description']) . "\">";

        if ($meta['json_ld']) {
            $html[] = "<script type=\"application/ld+json\">" . $meta['json_ld'] . "</script>";
        }

        return implode("\n    ", $html);
    }

    public function getH1(): string
    {
        return $this->currentMeta['h1'] ?? '';
    }

    private function applyFallbackSeo(string $pageType, array $data): void
    {
        // Start with defaults
        $this->setDefaultMeta();
        
        switch ($pageType) {
            case 'city_jobs':
                if (isset($data['city'])) {
                    $this->currentMeta['title'] = "Jobs in {$data['city']} - Mindware Infotech";
                    $this->currentMeta['description'] = "Find the best jobs in {$data['city']}. " . ($data['job_count'] ?? 'Thousands of') . " job openings available.";
                    $this->currentMeta['h1'] = "Jobs in {$data['city']}";
                }
                break;
                
            case 'skill_city_jobs':
                if (isset($data['skill']) && isset($data['city'])) {
                    $this->currentMeta['title'] = "{$data['skill']} Jobs in {$data['city']} - Mindware Infotech";
                    $this->currentMeta['description'] = "Apply to top {$data['skill']} jobs in {$data['city']}. Find your next career opportunity today.";
                    $this->currentMeta['h1'] = "{$data['skill']} Jobs in {$data['city']}";
                }
                break;

            case 'location_jobs':
                if (isset($data['location'])) {
                    $top = is_array($data['top_titles'] ?? null) ? $data['top_titles'] : [];
                    $lead = !empty($top) ? (strtolower($top[0]) . " jobs in {$data['location']} | Apply Now") : "Jobs in {$data['location']} | Apply Now";
                    $this->currentMeta['title'] = $lead . " - Mindware Infotech";
                    $this->currentMeta['description'] = "Find the best " . (!empty($top) ? strtolower($top[0]) . " jobs" : "jobs") . " in {$data['location']}. " . ($data['job_count'] ?? 'Thousands of') . " openings available. Apply today.";
                    $this->currentMeta['h1'] = "Jobs in {$data['location']}";
                }
                break;
                
            case 'role_location_jobs':
                if (isset($data['role']) && isset($data['location'])) {
                    $this->currentMeta['title'] = "{$data['role']} Jobs in {$data['location']} | Apply Now - Mindware Infotech";
                    $this->currentMeta['description'] = "Apply to top {$data['role']} jobs in {$data['location']}. Latest openings, verified employers. Submit your application today.";
                    $this->currentMeta['h1'] = "{$data['role']} Jobs in {$data['location']}";
                }
                break;
            
            case 'category_jobs':
                if (isset($data['category'])) {
                    $loc = $data['location'] ?? '';
                    $this->currentMeta['title'] = "Jobs in {$data['category']}" . ($loc ? " in {$loc}" : "") . " | Apply Now - Mindware Infotech";
                    $this->currentMeta['description'] = "Explore {$data['category']} jobs" . ($loc ? " in {$loc}" : "") . ". " . ($data['job_count'] ?? 'Many') . " openings across top companies. Apply online now.";
                    $this->currentMeta['h1'] = "Jobs in {$data['category']}" . ($loc ? " in {$loc}" : "");
                }
                break;
                
            case 'job_detail':
                if (isset($data['job_title'])) {
                    $company = $data['company'] ?? 'Mindware Infotech';
                    $location = !empty($data['city']) ? $data['city'] : '';
                    $this->currentMeta['title'] = "{$data['job_title']} at {$company} in {$location}";
                    $this->currentMeta['description'] = "Apply for {$data['job_title']} at {$company}. Location: {$location}.";
                    $this->currentMeta['h1'] = $data['job_title'];
                    
                    // Generate JSON-LD even in fallback
                    $this->currentMeta['json_ld'] = $this->generateJsonLd($pageType, $data);
                }
                break;
                
            case 'home':
                 $this->currentMeta['title'] = "Job Portal - Find Your Dream Job";
                 $this->currentMeta['description'] = "Search thousands of jobs. Connect with top employers and find your next career opportunity.";
                 $this->currentMeta['h1'] = "Find Your Dream Job";
                 break;
        }
        
        // Ensure canonical is set correctly if passed
        if (isset($data['canonical_path'])) {
             $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
             $this->currentMeta['canonical'] = rtrim($baseUrl, '/') . '/' . ltrim($data['canonical_path'], '/');
        }
        
        $this->currentMeta['keywords'] = $this->generateKeywords($pageType, $data, '');
    }
    
    private function generateKeywords(string $pageType, array $data, string $templateKeywords): string
    {
        $tk = trim($templateKeywords);
        if ($tk !== '') {
            return $this->normalizeKeywords($tk);
        }
        
        $keywords = [];
        $topTitles = is_array($data['top_titles'] ?? null) ? $data['top_titles'] : [];
        
        if ($pageType === 'job_detail') {
            $title = (string)($data['job_title'] ?? '');
            $company = (string)($data['company'] ?? ($data['company_name'] ?? ''));
            $city = (string)($data['city'] ?? '');
            $state = (string)($data['state'] ?? '');
            $country = (string)($data['country'] ?? '');
            $skills = is_array($data['skills'] ?? null) ? $data['skills'] : [];
            
            if ($title !== '') {
                $keywords[] = $title;
                $keywords[] = $title . ' jobs';
                $keywords[] = $title . ' hiring';
            }
            if ($company !== '') {
                $keywords[] = $company . ' jobs';
                $keywords[] = 'careers at ' . $company;
            }
            if ($city !== '') {
                $keywords[] = 'jobs in ' . $city;
                if ($title !== '') $keywords[] = $title . ' jobs in ' . $city;
            }
            if ($state !== '') {
                $keywords[] = 'jobs in ' . $state;
            }
            if ($country !== '') {
                $keywords[] = 'jobs in ' . $country;
            }
            foreach ($skills as $s) {
                if (is_string($s) && $s !== '') {
                    $keywords[] = strtolower($s) . ' jobs';
                    $keywords[] = strtolower($s) . ' hiring';
                }
            }
        } elseif ($pageType === 'location_jobs') {
            $loc = (string)($data['location'] ?? '');
            if ($loc !== '') {
                $keywords[] = 'jobs in ' . $loc;
                $keywords[] = 'latest jobs ' . $loc;
                $keywords[] = 'fresher jobs ' . $loc;
                $keywords[] = 'internships in ' . $loc;
                foreach ($topTitles as $tt) {
                    if (is_string($tt) && $tt !== '') {
                        $keywords[] = strtolower($tt) . ' jobs in ' . $loc;
                        $keywords[] = 'apply ' . strtolower($tt) . ' jobs ' . $loc;
                    }
                }
            }
        } elseif ($pageType === 'role_location_jobs') {
            $role = (string)($data['role'] ?? '');
            $loc = (string)($data['location'] ?? '');
            if ($role !== '' && $loc !== '') {
                $keywords[] = strtolower($role) . ' jobs in ' . $loc;
                $keywords[] = strtolower($role) . ' openings ' . $loc;
                $keywords[] = strtolower($role) . ' hiring ' . $loc;
            } elseif ($role !== '') {
                $keywords[] = strtolower($role) . ' jobs';
            } elseif ($loc !== '') {
                $keywords[] = 'jobs in ' . $loc;
            }
        } elseif ($pageType === 'city_jobs') {
            $city = (string)($data['city'] ?? '');
            if ($city !== '') {
                $keywords[] = 'jobs in ' . $city;
                $keywords[] = 'latest jobs ' . $city;
                $keywords[] = 'internships in ' . $city;
            }
        } elseif ($pageType === 'skill_city_jobs') {
            $skill = (string)($data['skill'] ?? '');
            $city = (string)($data['city'] ?? '');
            if ($skill !== '' && $city !== '') {
                $keywords[] = strtolower($skill) . ' jobs in ' . $city;
                $keywords[] = strtolower($skill) . ' openings ' . $city;
            }
        } elseif ($pageType === 'category_jobs') {
            $cat = (string)($data['category'] ?? '');
            $loc = (string)($data['location'] ?? '');
            if ($cat !== '') {
                if ($loc !== '') {
                    $keywords[] = strtolower($cat) . ' jobs in ' . $loc;
                }
                $keywords[] = strtolower($cat) . ' jobs';
                $keywords[] = 'apply ' . strtolower($cat) . ' jobs';
            }
        }
        
        $normalized = $this->normalizeKeywords(implode(', ', $keywords));
        return $normalized ?: $this->currentMeta['keywords'] ?? '';
    }
    
    private function normalizeKeywords(string $keywords): string
    {
        $parts = array_filter(array_map('trim', explode(',', strtolower($keywords))), fn($k) => $k !== '');
        $unique = [];
        foreach ($parts as $p) {
            if (!in_array($p, $unique, true)) {
                $unique[] = $p;
            }
        }
        return implode(', ', array_slice($unique, 0, 12));
    }
}
