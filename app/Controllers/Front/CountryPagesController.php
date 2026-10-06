<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Services\SeoService;

/** /jobsence-in/{country} – Jobsence for a neighbouring "open" country or a mentor country, in its language + English. */
class CountryPagesController extends BaseController
{
    public static function all(): array
    {
        static $pages = null;
        return $pages ??= require dirname(__DIR__, 3) . '/resources/data/country_pages.php';
    }

    public function show(Request $request, Response $response): void
    {
        $slug = (string)$request->param('country');
        $c = self::all()[$slug] ?? null;
        if (!$c) {
            $response->view('errors/404', [], 404);
            return;
        }
        $base = rtrim((string)($_ENV['APP_URL'] ?? 'https://jobsence.com'), '/');
        SeoService::getInstance()->setMeta([
            'title' => $c['t']['title'] . ' – ' . str_replace('{c}', $c['name'], $c['en']['title']),
            'description' => $c['t']['intro'] . ' ' . str_replace('{c}', $c['name'], $c['en']['mentor_p']),
            'canonical' => $base . '/jobsence-in/' . $slug,
            'robots' => 'index, follow',
        ]);
        $response->view('front/country/show', ['slug' => $slug, 'c' => $c, 'others' => self::all()], 200, 'layout');
    }
}
