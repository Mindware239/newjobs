<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\ExternalJob;
use App\Services\SeoService;

/**
 * SEO landing pages for every state / UT, district and major town of India (and India itself):
 *   /internship-in-{place}   /skill-development-in-{place}   /jobs-in-{place} (fallback when the
 *   jobs DB has no such city). Places come from resources/data/india_places.php.
 * Villages are reached through their district page and the PIN-code search in Near Me.
 */
class LocationPagesController extends BaseController
{
    public const KINDS = [
        'internship' => ['path' => 'internship-in-', 'form' => '/apply/internship', 'hi' => 'इंटर्नशिप', 'en' => 'Internship'],
        'skill' => ['path' => 'skill-development-in-', 'form' => '/apply/skill-development', 'hi' => 'कौशल विकास', 'en' => 'Skill Development'],
        'jobs' => ['path' => 'jobs-in-', 'form' => '/apply/full-time-job', 'hi' => 'नौकरियाँ', 'en' => 'Jobs'],
        // Provider directories per place: 'provider_type' lists verified providers; form = seeker form, offer = free provider form.
        'mentors' => ['path' => 'mentors-in-', 'form' => '/apply/skill-development', 'offer' => '/apply/skill-provider', 'list' => '/skill-mentors', 'provider_type' => 'provider',
            'hi' => 'मेंटर और ट्रेनिंग संस्थान', 'en' => 'Mentors & Training Institutes'],
        'interncos' => ['path' => 'internship-providers-in-', 'form' => '/apply/internship', 'offer' => '/apply/internship-provider', 'list' => '/internship-providers', 'provider_type' => 'internpro',
            'hi' => 'इंटर्नशिप देने वाली कंपनियाँ', 'en' => 'Internship Providers'],
        'hiring' => ['path' => 'companies-hiring-in-', 'form' => '/apply/full-time-job', 'offer' => '/apply/job-provider', 'list' => '/hiring-companies', 'provider_type' => 'jobpro',
            'hi' => 'भर्ती करने वाली कंपनियाँ', 'en' => 'Companies Hiring'],
        'services' => ['path' => 'services-near-me-in-', 'form' => '/apply/near-me-seeker', 'offer' => '/apply/near-me-provider', 'list' => '/near-me', 'provider_type' => 'nearpro',
            'hi' => 'पास की सेवाएँ (प्लंबर, इलेक्ट्रीशियन…)', 'en' => 'Services Near Me (Plumber, Electrician…)'],
    ];

    /** Countries Jobsence does not serve (no pages, no sitemap entries, old links redirect). */
    public const BLOCKED = ['pakistan'];

    public static function isBlocked(string $slug): bool
    {
        foreach (self::BLOCKED as $b) {
            if ($slug === $b || str_starts_with($slug, $b . '-') || str_ends_with($slug, '-' . $b)) {
                return true;
            }
        }
        return false;
    }

    /** Kinds that list providers (no abroad pages: providers are India-only). */
    public static function isProviderKind(string $kind): bool
    {
        return isset(self::KINDS[$kind]['provider_type']);
    }

    public function internship(Request $request, Response $response, array $params = []): void
    {
        $this->render('internship', (string)($params['location'] ?? $request->param('location')), $response);
    }

    public function skill(Request $request, Response $response, array $params = []): void
    {
        $this->render('skill', (string)($params['location'] ?? $request->param('location')), $response);
    }

    public function mentors(Request $request, Response $response, array $params = []): void
    {
        $this->render('mentors', (string)($params['location'] ?? $request->param('location')), $response);
    }

    public function internshipProviders(Request $request, Response $response, array $params = []): void
    {
        $this->render('interncos', (string)($params['location'] ?? $request->param('location')), $response);
    }

    public function hiring(Request $request, Response $response, array $params = []): void
    {
        $this->render('hiring', (string)($params['location'] ?? $request->param('location')), $response);
    }

    public function services(Request $request, Response $response, array $params = []): void
    {
        $this->render('services', (string)($params['location'] ?? $request->param('location')), $response);
    }

    /** slug => ['name', 'state', 'level'] – India (country), its states / districts / towns, and every other country (abroad). */
    public static function places(): array
    {
        static $map = null;
        if ($map !== null) {
            return $map;
        }
        $map = ['india' => ['name' => 'India', 'state' => '', 'level' => 'country']];
        foreach (NearMeController::places() as $state => $p) {
            $map[self::slug($state)] = ['name' => $state, 'state' => $state, 'level' => 'state'];
        }
        foreach (NearMeController::places() as $state => $p) {
            foreach (['district' => $p['districts'] ?? [], 'town' => $p['towns'] ?? []] as $level => $names) {
                foreach ($names as $name) {
                    $slug = self::slug($name);
                    // Same name in two states (e.g. Aurangabad): the later one gets "-state".
                    if (isset($map[$slug])) {
                        if ($map[$slug]['state'] === $state) {
                            continue;
                        }
                        $slug .= '-' . self::slug($state);
                    }
                    $map[$slug] = ['name' => $name, 'state' => $state, 'level' => $level];
                }
            }
        }
        foreach ((is_file($f = dirname(__DIR__, 3) . '/resources/data/countries.php') ? require $f : []) as $country) {
            $map[self::slug($country)] ??= ['name' => $country, 'state' => '', 'level' => 'abroad', 'country' => $country];
        }
        // Searched short names: /jobs-in-usa, /jobs-in-england, /jobs-in-uae, /jobs-in-saudi …
        foreach (JobsAbroadController::ALIASES as $alias => [$display, $country]) {
            $map[$alias] ??= ['name' => $display, 'state' => '', 'level' => 'abroad', 'country' => $country];
        }
        return $map;
    }

    public static function slug(string $s): string
    {
        return trim((string)preg_replace('/[^a-z0-9]+/', '-', strtolower($s)), '-');
    }

    public function render(string $kind, string $slug, Response $response): void
    {
        $places = self::places();
        $slug = strtolower($slug);
        $place = $places[$slug] ?? null;
        if (!$place) {
            $response->redirect($kind === 'jobs' ? '/jobs' : self::KINDS[$kind]['form']);
            return;
        }
        // State-level skill pages already exist at /skill-development/{state}.
        if ($kind === 'skill' && $place['level'] === 'state' && isset(SkillDevelopmentController::STATES[$slug])) {
            header('Location: /skill-development/' . $slug, true, 301);
            exit;
        }

        $k = self::KINDS[$kind];
        $name = $place['name'];
        $full = $place['level'] === 'district' || $place['level'] === 'town' ? $name . ', ' . $place['state'] : $name;
        $year = date('Y');
        $abroad = $place['level'] === 'abroad';
        $titles = [
            'internship' => "Internship in {$full} {$year} – Paid & Unpaid, WFH Internships",
            'skill' => "Skill Development in {$full} – Online & Offline Courses, Mentors",
            'jobs' => $abroad ? "Jobs in {$full} {$year} – for Indians & Remote Work" : "Jobs in {$full} {$year} – Full-time, Part-time & Govt Jobs",
            'mentors' => "Mentors & Training Institutes in {$full} – Online & Offline Skill Training",
            'interncos' => "Companies Offering Internships in {$full} {$year} – Paid & Unpaid",
            'hiring' => "Companies Hiring in {$full} {$year} – Shops, Factories, Offices",
            'services' => "Plumber, Electrician, Carpenter, Salon Near Me in {$full} – Verified Services",
        ];
        if ($abroad) {
            $titles['internship'] = "Online Internship in {$full} {$year} – Remote Internships with Indian Companies";
            $titles['skill'] = "Online Skill Development in {$full} – Learn from Indian Mentors";
        }
        $descriptions = [
            'internship' => "Find internships in {$full}: paid (₹8,000+ stipend) and unpaid, on-site and work from home, across 3000+ domains. Register free with Jobsence.",
            'skill' => "Skill development in {$full}: 3000+ skills with online video classes and mentors. Choose up to 5 skills. Register free with Jobsence – not a Govt scheme.",
            'jobs' => "Latest jobs in {$full}: full-time, part-time, work from home and Govt / PSU openings. Register with Jobsence and get matched with employers.",
            'mentors' => "Find verified mentors, group mentors and training institutes in {$full} for 3000+ skills. Mentors and institutes register free on Jobsence – a Jobsence initiative for young India.",
            'interncos' => "Verified companies, startups and institutes offering internships in {$full}. Providers register free; interns connect through a Jobsence agreement.",
            'hiring' => "Verified companies, shops, factories and offices hiring in {$full} – full-time, part-time, work from home. Employers register free on Jobsence.",
            'services' => "Find verified plumbers, electricians, carpenters, AC mechanics, salons and 70+ services in {$full} – photo and selfie verified. Search by your location or area.",
        ];
        SeoService::getInstance()->setMeta([
            'title' => $titles[$kind] . ' | Jobsence',
            'h1' => $titles[$kind],
            'description' => $descriptions[$kind],
            'keywords' => strtolower("{$k['en']} in {$name}, {$k['en']} {$name}, {$name} {$k['en']} {$year}, {$k['en']} near me {$name}" . ($place['state'] && $place['state'] !== $name ? ", {$k['en']} in {$place['state']}" : '')),
            'canonical' => rtrim((string)($_ENV['APP_URL'] ?? ''), '/') . '/' . $k['path'] . $slug,
            'robots' => 'index, follow',
        ]);

        $response->view('front/location/index', [
            'kind' => $kind,
            'k' => ($place['level'] === 'abroad' && in_array($kind, ['jobs', 'internship', 'skill'], true))
                ? ['form' => '/apply/international-job?country=' . rawurlencode((string)($place['country'] ?? $place['name']))] + $k
                : $k,
            'abroadSlug' => $place['level'] === 'abroad' ? self::slug((string)($place['country'] ?? $place['name'])) : null,
            'slug' => $slug,
            'place' => $place,
            'full' => $full,
            'openings' => $this->openings($kind, $place),
            'nearby' => $this->nearby($slug, $place),
        ], 200, 'layout');
    }

    /** Employer jobs + "Jobs in India" listings for this place (state-wide listings included). */
    private function openings(string $kind, array $place): array
    {
        $out = [];
        $db = Database::getInstance();
        try {
            $where = "j.status = 'published'";
            $params = [];
            if ($kind === 'internship') {
                $where .= " AND j.employment_type = 'internship'";
            }
            if ($place['level'] === 'state') {
                $where .= ' AND (s.name = ? OR jl.state = ?)';
                array_push($params, $place['name'], $place['name']);
            } elseif ($place['level'] !== 'country') {
                $where .= ' AND (c.name = ? OR jl.city = ?)';
                array_push($params, $place['name'], $place['name']);
            }
            if ($place['level'] === 'abroad') {
                $where = str_replace(['(c.name = ? OR jl.city = ?)'], ['(cnt.name = ? OR jl.country = ?)'], $where);
            }
            if (self::isProviderKind($kind)) {
                $k = self::KINDS[$kind];
                [$pw, $pp] = $place['level'] === 'state' ? ['state = ?', [$place['name']]]
                    : (in_array($place['level'], ['district', 'town'], true) ? ['(city = ? OR district = ?)', [$place['name'], $place['name']]] : ['1 = 1', []]);
                $valid = $k['provider_type'] === 'nearpro' ? ' AND valid_until > NOW()' : '';
                foreach ($db->fetchAll(
                    "SELECT id, full_name, categories, details, city, state, pincode FROM portal_registrations
                     WHERE type = ? AND payment_status = 'paid' AND status = 'selected'{$valid} AND $pw ORDER BY paid_at DESC LIMIT 30",
                    array_merge([$k['provider_type']], $pp)
                ) as $p) {
                    $d = json_decode((string)$p['details'], true) ?: [];
                    $org = ($d['business_name'] ?? '') ?: ($d['institute_name'] ?? '');
                    $url = $k['provider_type'] === 'nearpro'
                        ? '/near-me?pin=' . rawurlencode((string)$p['pincode'])
                        : $k['list'] . '?loc=' . rawurlencode((string)($p['city'] ?: $p['state']));
                    $out[] = ['title' => $org !== '' ? $org : \App\Services\Registration\ContactPass::maskName((string)$p['full_name']),
                        'org' => mb_strimwidth((string)$p['categories'], 0, 90, '…'), 'url' => $url, 'tag' => '✔ Verified · ' . ($p['city'] ?: $p['state'])];
                }
                return $out;
            }
            if ($kind !== 'skill') {
                foreach ($db->fetchAll(
                    "SELECT DISTINCT j.id, j.title, j.slug, j.employment_type, COALESCE(j.company_name, e.company_name) AS org, j.created_at
                     FROM jobs j JOIN job_locations jl ON jl.job_id = j.id
                     LEFT JOIN cities c ON c.id = jl.city_id LEFT JOIN states s ON s.id = jl.state_id LEFT JOIN countries cnt ON cnt.id = jl.country_id
                     LEFT JOIN employers e ON e.id = j.employer_id
                     WHERE $where ORDER BY j.created_at DESC LIMIT 20",
                    $params
                ) as $j) {
                    $out[] = ['title' => $j['title'], 'org' => $j['org'], 'url' => '/job/' . $j['slug'], 'tag' => ucwords(str_replace('_', ' ', (string)$j['employment_type']))];
                }
            }
        } catch (\Throwable $e) {
            error_log('Location openings (jobs): ' . $e->getMessage());
        }
        try {
            $f = ['kind' => ['internship' => 'internship', 'skill' => 'skill', 'jobs' => 'job'][$kind] ?? 'job'];
            if ($place['level'] === 'abroad') {
                return array_slice($out, 0, 30);
            }
            if ($place['level'] !== 'country') {
                $f['state'] = $place['state'];
            }
            foreach (ExternalJob::search($f, 1, 20)['rows'] as $x) {
                $out[] = ['title' => $x['title'], 'org' => $x['org_name'], 'url' => ExternalJob::url($x), 'tag' => $x['last_date'] ? 'Last date ' . date('d M', strtotime((string)$x['last_date'])) : 'Govt / PSU'];
            }
        } catch (\Throwable $e) {
            error_log('Location openings (external): ' . $e->getMessage());
        }
        return array_slice($out, 0, 30);
    }

    /** Other places in the same state (or all states for India) for internal links. */
    private function nearby(string $slug, array $place): array
    {
        $out = [];
        foreach (self::places() as $s => $p) {
            if ($s === $slug) {
                continue;
            }
            $near = match ($place['level']) {
                'country' => $p['level'] === 'state',
                'abroad' => $p['level'] === 'abroad',
                default => $p['state'] === $place['state'] && $p['level'] !== 'state' && $p['level'] !== 'abroad',
            };
            if ($near) {
                $out[$s] = $p['name'];
            }
        }
        return array_slice($out, 0, 60, true);
    }
}
