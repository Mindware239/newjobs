<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Job;
use App\Models\Employer;
use App\Core\Database;

class SEOController
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
    }

    /**
     * Sitemap Index
     */
    public function index(Request $request, Response $response): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        $sitemaps = [
            'sitemap-main.xml',
            'sitemap-jobs.xml',
            'sitemap-countries.xml',
            'sitemap-states.xml',
            'sitemap-cities.xml',
            'sitemap-categories.xml',
            'sitemap-skills.xml',
            'sitemap-companies.xml'
        ];

        foreach ($sitemaps as $map) {
            $xml .= "  <sitemap>\n";
            $xml .= "    <loc>" . $this->baseUrl . '/' . $map . "</loc>\n";
            $xml .= "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
            $xml .= "  </sitemap>\n";
        }
        
        $xml .= '</sitemapindex>';
        $this->renderXml($response, $xml);
    }

    public function main(Request $request, Response $response): void
    {
        $xml = $this->startUrlSet();
        $xml .= $this->urlElement($this->baseUrl . '/', '1.0', 'daily');
        $xml .= $this->urlElement($this->baseUrl . '/jobs', '0.9', 'hourly');
        $xml .= $this->urlElement($this->baseUrl . '/login', '0.8', 'monthly');
        $xml .= $this->urlElement($this->baseUrl . '/register-candidate', '0.8', 'monthly');
        $xml .= $this->urlElement($this->baseUrl . '/register-employer', '0.8', 'monthly');
        $xml .= $this->urlElement($this->baseUrl . '/about', '0.7', 'monthly');
        $xml .= $this->urlElement($this->baseUrl . '/contact', '0.7', 'monthly');
        $xml .= $this->urlElement($this->baseUrl . '/blog', '0.8', 'weekly');
        $xml .= '</urlset>';
        $this->renderXml($response, $xml);
    }

    public function jobs(Request $request, Response $response): void
    {
        $xml = $this->startUrlSet();
        // Limit to 5000 for scalability
        $jobs = Job::where('status', '=', 'published')
            ->orderBy('created_at', 'DESC')
            ->limit(5000)
            ->get();
        
        foreach ($jobs as $job) {
            // Check for slug
            $slug = $job->slug ?? $job->attributes['slug'] ?? null;
            if (!$slug) continue;
            
            $url = $this->baseUrl . '/job/' . $slug;
            $updated = $job->updated_at ?? $job->attributes['updated_at'] ?? $job->created_at ?? null;
            $xml .= $this->urlElement($url, '0.8', 'daily', $updated);
        }
        $xml .= '</urlset>';
        $this->renderXml($response, $xml);
    }

    public function countries(Request $request, Response $response): void
    {
        $xml = $this->startUrlSet();
        $db = Database::getInstance();
        try {
            $countries = $db->fetchAll("
                SELECT DISTINCT c.slug
                FROM countries c
                JOIN job_locations jl ON jl.country_id = c.id
                JOIN jobs j ON j.id = jl.job_id
                WHERE j.status = 'published'
            ");
            foreach ($countries as $country) {
                if (empty($country['slug'])) continue;
                $url = $this->baseUrl . '/jobs-in-' . $country['slug'];
                $xml .= $this->urlElement($url, '0.9', 'daily');
            }
        } catch (\Exception $e) {}
        $xml .= '</urlset>';
        $this->renderXml($response, $xml);
    }


    public function cities(Request $request, Response $response): void
    {
        $xml = $this->startUrlSet();
        $db = Database::getInstance();
        try {
            // Only cities with active jobs
            $cities = $db->fetchAll("
                SELECT DISTINCT c.slug, MAX(j.updated_at) as last_mod
                FROM cities c 
                JOIN job_locations jl ON jl.city_id = c.id
                JOIN jobs j ON j.id = jl.job_id
                WHERE j.status = 'published'
                GROUP BY c.slug
            ");
            
            foreach ($cities as $city) {
                 if (empty($city['slug'])) continue;
                 $url = $this->baseUrl . '/jobs-in-' . $city['slug'];
                 $xml .= $this->urlElement($url, '0.8', 'daily', $city['last_mod'] ?? null);
            }
        } catch (\Exception $e) {}
        $xml .= '</urlset>';
        $this->renderXml($response, $xml);
    }

    public function states(Request $request, Response $response): void
    {
        $xml = $this->startUrlSet();
        $db = Database::getInstance();
        try {
            $states = $db->fetchAll("
                SELECT DISTINCT s.slug, MAX(j.updated_at) as last_mod
                FROM states s
                JOIN job_locations jl ON jl.state_id = s.id
                JOIN jobs j ON j.id = jl.job_id
                WHERE j.status = 'published'
                GROUP BY s.slug
            ");
            foreach ($states as $state) {
                if (empty($state['slug'])) continue;
                $url = $this->baseUrl . '/jobs-in-' . $state['slug'];
                $xml .= $this->urlElement($url, '0.8', 'daily', $state['last_mod'] ?? null);
            }
        } catch (\Exception $e) {}
        $xml .= '</urlset>';
        $this->renderXml($response, $xml);
    }
    
    public function categories(Request $request, Response $response): void
    {
        $xml = $this->startUrlSet();
        $db = Database::getInstance();
        try {
            $cats = $db->fetchAll("
                SELECT jc.slug, jc.name, COUNT(DISTINCT j.id) as job_count
                FROM job_categories jc
                LEFT JOIN jobs j ON j.category = jc.name AND j.status = 'published'
                WHERE jc.is_active = 1
                GROUP BY jc.slug, jc.name
                HAVING job_count > 0
                ORDER BY job_count DESC, jc.name ASC
            ");
            foreach ($cats as $c) {
                if (empty($c['slug'])) continue;
                $url = $this->baseUrl . '/jobs-in-category/' . $c['slug'];
                $xml .= $this->urlElement($url, '0.8', 'daily');
            }
        } catch (\Exception $e) {}
        $xml .= '</urlset>';
        $this->renderXml($response, $xml);
    }
    public function skills(Request $request, Response $response): void
    {
        $xml = $this->startUrlSet();
        $db = Database::getInstance();
        try {
             // Top 2000 skill-city combinations
            $combinations = $db->fetchAll(
                "SELECT DISTINCT s.name as skill_name, c.slug as city_slug 
                 FROM job_skills js
                 JOIN jobs j ON j.id = js.job_id
                 JOIN job_locations jl ON jl.job_id = j.id
                 JOIN cities c ON c.id = jl.city_id
                 JOIN skills s ON s.id = js.skill_id
                 WHERE j.status = 'published'
                 LIMIT 2000"
            );

            foreach ($combinations as $combo) {
                $skillSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $combo['skill_name'])));
                $citySlug = $combo['city_slug'];
                if (!$skillSlug || !$citySlug) continue;

                $url = $this->baseUrl . '/' . $skillSlug . '-jobs-in-' . $citySlug;
                $xml .= $this->urlElement($url, '0.7', 'weekly');
            }
        } catch (\Exception $e) {}
        $xml .= '</urlset>';
        $this->renderXml($response, $xml);
    }

    public function companies(Request $request, Response $response): void
    {
        $xml = $this->startUrlSet();
        $db = Database::getInstance();
        
        // Fetch from Companies table (generated/synced)
        try {
            $companies = $db->fetchAll("SELECT slug, updated_at FROM companies WHERE slug IS NOT NULL");
            foreach ($companies as $co) {
                $url = $this->baseUrl . '/company/' . $co['slug'];
                $xml .= $this->urlElement($url, '0.8', 'weekly', $co['updated_at']);
            }
        } catch (\Exception $e) {}

        // Also employers who might not be in companies table yet (fallback)
        // Note: Ideally we should only use companies table if it's the source of truth for company pages.
        // But since CompanyController uses both, we might want both.
        // To avoid complexity, let's rely on companies table if populated. 
        // If empty, maybe query employers.
        // For now, let's include employers with company_slug if not in companies list (hard to check in loop).
        // Let's just add employers that have company_slug.
        
        $employers = Employer::where('kyc_status', '=', 'approved')
            ->limit(500)
            ->get();
        
        foreach ($employers as $employer) {
            $slug = $employer->company_slug ?? $employer->attributes['company_slug'] ?? null;
            if ($slug) {
                 $url = $this->baseUrl . '/company/' . $slug;
                 // Allow duplicate lines in sitemap, search engines handle it.
                 $xml .= $this->urlElement($url, '0.8', 'weekly', $employer->updated_at ?? null);
            }
        }

        $xml .= '</urlset>';
        $this->renderXml($response, $xml);
    }

    public function robots(Request $request, Response $response): void
    {
        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /candidate/\n";
        $content .= "Disallow: /employer/\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /api/\n";
        $content .= "\n";
        $content .= "Sitemap: {$this->baseUrl}/sitemap.xml\n";
        
        $response->setHeader('Content-Type', 'text/plain');
        $response->setBody($content);
    }

    private function startUrlSet(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . 
               '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    }

    private function renderXml(Response $response, string $xml): void
    {
        $response->setHeader('Content-Type', 'application/xml');
        $response->setBody($xml);
    }

    private function urlElement(string $url, string $priority, string $changefreq, ?string $lastmod = null): string
    {
        $xml = "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($url) . "</loc>\n";
        $xml .= "    <priority>" . $priority . "</priority>\n";
        $xml .= "    <changefreq>" . $changefreq . "</changefreq>\n";
        if ($lastmod) {
            $xml .= "    <lastmod>" . date('Y-m-d', strtotime($lastmod)) . "</lastmod>\n";
        }
        $xml .= "  </url>\n";
        return $xml;
    }
}
