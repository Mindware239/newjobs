<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Services\Registration\SkillTaxonomy;
use App\Services\SeoService;

/**
 * Category → subcategory → skill / role catalogue with "want" and "offer" calls to action.
 *   /categories/{mode}                     skills | internships | jobs | near-me
 *   /categories/{mode}/{category}
 *   /categories/{mode}/{category}/{sub}
 */
class CatalogueController extends BaseController
{
    /** mode => labels, calls to action (URL gets ?cat=… / ?q=…) and the live-count captions. */
    public const MODES = [
        'skills' => [
            'title' => ['कौशल विकास – सभी स्किल', 'Skill Development – All Skills'],
            'want' => ['क्या आप इसमें स्किल सीखना चाहते हैं?', 'Looking for skill development in this?'], 'want_url' => '/apply/skill-development?cat=',
            'offer' => ['क्या आप यह स्किल सिखा सकते हैं?', 'Can you provide skill in this?'], 'offer_url' => '/apply/skill-provider?cat=',
            'want_n' => ['सीखने वाले', 'learners'], 'offer_n' => ['मेंटर', 'mentors'], 'unit' => ['स्किल', 'skills'],
        ],
        'internships' => [
            'title' => ['इंटर्नशिप – सभी डोमेन', 'Internships – All Domains'],
            'want' => ['क्या आप इसमें इंटर्नशिप चाहते हैं?', 'Looking for an internship in this?'], 'want_url' => '/apply/internship?cat=',
            'offer' => ['क्या आप इसमें इंटर्नशिप दे सकते हैं?', 'Can you offer internships in this?'], 'offer_url' => '/apply/internship-provider?cat=',
            'want_n' => ['इंटर्नशिप चाहने वाले', 'seekers'], 'offer_n' => ['प्रदाता', 'providers'], 'unit' => ['डोमेन', 'domains'],
        ],
        'jobs' => [
            'title' => ['नौकरियाँ – सभी कैटेगरी', 'Jobs – All Categories'],
            'want' => ['क्या आप इसमें नौकरी ढूँढ रहे हैं?', 'Looking for a job in this?'], 'want_url' => '/apply/full-time-job?cat=',
            'offer' => ['क्या आप इसके लिए भर्ती कर रहे हैं?', 'Hiring for this?'], 'offer_url' => '/apply/job-provider?cat=',
            'want_n' => ['नौकरी चाहने वाले', 'job seekers'], 'offer_n' => ['खुली नौकरियाँ', 'open jobs'], 'unit' => ['जॉब रोल', 'job roles'],
        ],
        'near-me' => [
            'title' => ['मेरे पास सेवा – सभी सेवाएँ', 'Near Me – All Services'],
            'want' => ['क्या आपको यह सेवा पास में चाहिए?', 'Need this service near you?'], 'want_url' => '/near-me?q=',
            'offer' => ['क्या आप यह सेवा देते हैं?', 'Do you provide this service?'], 'offer_url' => '/apply/near-me-provider?cat=',
            'want_n' => ['', ''], 'offer_n' => ['सत्यापित प्रदाता', 'verified providers'], 'unit' => ['सेवाएँ', 'services'],
        ],
    ];

    public function index(Request $request, Response $response, array $params = []): void
    {
        $this->render($response, (string)($params['mode'] ?? $request->param('mode')), null, null);
    }

    public function category(Request $request, Response $response, array $params = []): void
    {
        $this->render($response, (string)($params['mode'] ?? $request->param('mode')), (string)($params['category'] ?? $request->param('category')), null);
    }

    public function sub(Request $request, Response $response, array $params = []): void
    {
        $this->render($response, (string)($params['mode'] ?? $request->param('mode')), (string)($params['category'] ?? $request->param('category')), (string)($params['sub'] ?? $request->param('sub')));
    }

    public static function tree(string $mode): array
    {
        return $mode === 'near-me' ? SkillTaxonomy::nearMeTree() : SkillTaxonomy::tree();
    }

    private function render(Response $response, string $mode, ?string $catSlug, ?string $subSlug): void
    {
        if (!isset(self::MODES[$mode])) {
            $response->redirect('/categories/skills');
            return;
        }
        $m = self::MODES[$mode];
        $tree = self::tree($mode);
        $sector = $sub = null;
        if ($catSlug !== null) {
            foreach ($tree as $s) {
                if ($s['slug'] === $catSlug) {
                    $sector = $s;
                }
            }
            if (!$sector) {
                $response->redirect('/categories/' . $mode);
                return;
            }
            if ($subSlug !== null) {
                foreach ($sector['subs'] as $x) {
                    if ($x['slug'] === $subSlug) {
                        $sub = $x;
                    }
                }
                if (!$sub) {
                    $response->redirect('/categories/' . $mode . '/' . $sector['slug']);
                    return;
                }
            }
        }

        $path = '/categories/' . $mode . ($sector ? '/' . $sector['slug'] : '') . ($sub ? '/' . $sub['slug'] : '');
        $name = $sub['name'] ?? ($sector['short'] ?? null);
        $total = array_sum(array_column($tree, 'count'));
        $title = $name !== null ? "{$name} – {$m['title'][1]}" : $m['title'][1] . ' (' . number_format($total) . ' ' . $m['unit'][1] . ')';
        SeoService::getInstance()->setMeta([
            'title' => $title . ' | Jobsence',
            'h1' => $title,
            'description' => $name !== null
                ? "{$name}: " . number_format($sub ? count($sub['skills']) : $sector['count']) . " {$m['unit'][1]} on Jobsence. {$m['want'][1]} {$m['offer'][1]} Register in minutes."
                : number_format($total) . " {$m['unit'][1]} in " . count($tree) . " categories across India on Jobsence. {$m['want'][1]} {$m['offer'][1]}",
            'keywords' => strtolower(($name ?? '') . " {$m['unit'][1]}, {$m['title'][1]}, jobsence, india"),
            'canonical' => rtrim((string)($_ENV['APP_URL'] ?? ''), '/') . $path,
            'robots' => 'index, follow',
        ]);

        $response->view('front/catalogue/index', [
            'mode' => $mode, 'm' => $m, 'tree' => $tree, 'sector' => $sector, 'sub' => $sub, 'total' => $total,
            'live' => SkillTaxonomy::live($mode),
        ], 200, 'layout');
    }
}
