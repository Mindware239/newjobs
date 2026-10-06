<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Services\Registration\FormRegistry;
use App\Services\Registration\SkillTaxonomy;
use App\Services\SeoService;

/**
 * Jobs abroad, for every country × every job category × every role:
 *   /jobs-abroad                         all countries (top destinations first)
 *   /jobs-abroad/{country}               job categories for that country
 *   /jobs-abroad/{country}/{category}    roles – each "Apply" opens the free form with country + role pre-filled,
 *                                        then unlocks that country (free for job seekers – FormRegistry::SEEKERS_FREE).
 */
class JobsAbroadController extends BaseController
{
    /** Most searched destinations for Indian job seekers, shown first. */
    public const TOP = ['United Arab Emirates', 'Saudi Arabia', 'Qatar', 'Kuwait', 'Oman', 'Bahrain', 'United States', 'Canada', 'United Kingdom',
        'Australia', 'Germany', 'Japan', 'China', 'Singapore', 'Malaysia', 'New Zealand', 'Ireland', 'Israel', 'Poland', 'Russia'];

    /** Short names people search for → [display name, country]. */
    public const ALIASES = [
        'usa' => ['USA', 'United States'], 'america' => ['America', 'United States'], 'us' => ['US', 'United States'],
        'uk' => ['UK', 'United Kingdom'], 'england' => ['England', 'United Kingdom'], 'london' => ['London', 'United Kingdom'], 'britain' => ['Britain', 'United Kingdom'],
        'uae' => ['UAE', 'United Arab Emirates'], 'dubai' => ['Dubai', 'United Arab Emirates'], 'abu-dhabi' => ['Abu Dhabi', 'United Arab Emirates'],
        'saudi' => ['Saudi', 'Saudi Arabia'], 'ksa' => ['KSA', 'Saudi Arabia'], 'riyadh' => ['Riyadh', 'Saudi Arabia'],
        'korea' => ['Korea', 'South Korea'], 'doha' => ['Doha', 'Qatar'], 'muscat' => ['Muscat', 'Oman'],
        'europe' => ['Europe', 'Germany'], 'gulf' => ['Gulf', 'United Arab Emirates'], 'singapore-city' => ['Singapore', 'Singapore'],
    ];

    /** Country for a slug or alias slug, or null. */
    public static function resolve(string $slug): ?string
    {
        return self::countries()[$slug] ?? (self::ALIASES[$slug][1] ?? null);
    }

    /** slug => country name (every country except India). */
    public static function countries(): array
    {
        static $map = null;
        if ($map === null) {
            $map = [];
            foreach ((is_file($f = dirname(__DIR__, 3) . '/resources/data/countries.php') ? require $f : []) as $c) {
                $map[LocationPagesController::slug($c)] = $c;
            }
        }
        return $map;
    }

    public function index(Request $request, Response $response): void
    {
        $all = self::countries();
        $top = array_filter($all, static fn($c) => in_array($c, self::TOP, true));
        uasort($top, static fn($a, $b) => array_search($a, self::TOP, true) <=> array_search($b, self::TOP, true));
        $this->meta('Jobs Abroad for Indians ' . date('Y') . ' – UAE, Saudi, USA, Japan, Canada & ' . count($all) . ' Countries', 'Register free for jobs abroad in ' . count($all) . ' countries – Gulf, Europe, USA, Japan, China and more. Unlocking each country is free too. Passport mandatory.', '/jobs-abroad');
        $response->view('front/jobs-abroad/index', ['level' => 'all', 'top' => $top, 'all' => $all, 'counts' => $this->counts()], 200, 'layout');
    }

    public function country(Request $request, Response $response, array $params = []): void
    {
        $slug = (string)($params['country'] ?? $request->param('country'));
        $country = self::resolve($slug);
        if (!$country) {
            $response->redirect('/jobs-abroad');
            return;
        }
        $this->meta("Jobs in {$country} for Indians " . date('Y') . ' – All Categories | Apply', "Jobs in {$country} for Indian job seekers in every category – construction, healthcare, IT, hospitality, drivers, factory and more. Register and unlock {$country} free.", '/jobs-abroad/' . $slug);
        $response->view('front/jobs-abroad/index', ['level' => 'country', 'slug' => $slug, 'country' => $country, 'tree' => SkillTaxonomy::tree(),
            'counts' => $this->counts()[mb_strtolower($country)] ?? ['seekers' => 0, 'companies' => 0]], 200, 'layout');
    }

    public function category(Request $request, Response $response, array $params = []): void
    {
        $slug = (string)($params['country'] ?? $request->param('country'));
        $country = self::resolve($slug);
        $sector = SkillTaxonomy::find((string)($params['category'] ?? $request->param('category')));
        if (!$country || !$sector) {
            $response->redirect($country ? '/jobs-abroad/' . $slug : '/jobs-abroad');
            return;
        }
        $this->meta("{$sector['short']} Jobs in {$country} for Indians " . date('Y'), "{$sector['short']} jobs in {$country}: " . number_format($sector['count']) . " roles. Register and unlock {$country} free, passport mandatory.", '/jobs-abroad/' . $slug . '/' . $sector['slug']);
        $response->view('front/jobs-abroad/index', ['level' => 'category', 'slug' => $slug, 'country' => $country, 'sector' => $sector], 200, 'layout');
    }

    /** lower-case country => ['seekers' => unlocked seekers, 'companies' => verified companies hiring there]. */
    private function counts(): array
    {
        $out = [];
        try {
            foreach (Database::getInstance()->fetchAll(
                "SELECT LOWER(JSON_UNQUOTE(JSON_EXTRACT(details, '$.country'))) AS c, COUNT(*) AS n FROM portal_registrations
                 WHERE type = 'intlcountry' AND payment_status = 'paid' GROUP BY c"
            ) as $r) {
                $out[$r['c']]['seekers'] = (int)$r['n'];
            }
            foreach (Database::getInstance()->fetchAll(
                "SELECT LOWER(JSON_UNQUOTE(JSON_EXTRACT(details, '$.country'))) AS c, COUNT(*) AS n FROM portal_registrations
                 WHERE type = 'jobpro' AND payment_status = 'paid' AND status = 'selected' AND JSON_UNQUOTE(JSON_EXTRACT(details, '$.based_in')) = 'abroad' GROUP BY c"
            ) as $r) {
                $out[$r['c']]['companies'] = (int)$r['n'];
            }
        } catch (\Throwable $e) {
            error_log('JobsAbroad counts: ' . $e->getMessage());
        }
        return $out;
    }

    private function meta(string $title, string $description, string $path): void
    {
        SeoService::getInstance()->setMeta([
            'title' => $title . ' | Jobsence',
            'h1' => $title,
            'description' => $description,
            'keywords' => strtolower('jobs abroad, jobs abroad for indians, gulf jobs, ' . $title),
            'canonical' => rtrim((string)($_ENV['APP_URL'] ?? ''), '/') . $path,
            'robots' => 'index, follow',
        ]);
    }
}
