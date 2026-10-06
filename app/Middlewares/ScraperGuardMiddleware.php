<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;

/**
 * Keeps scrapers off the public pages without hiding content from Google:
 *  - refuses scraping tools / AI crawlers by user agent (Googlebot, Bingbot and normal browsers pass);
 *  - limits page views per IP (PER_MINUTE, PER_HOUR) with small counter files – works without Redis,
 *    which shared hosting usually lacks (the Redis-based RateLimitMiddleware is then a no-op).
 * Contact details are additionally only revealed to logged-in users (see StateJobsController::contact).
 */
class ScraperGuardMiddleware implements MiddlewareInterface
{
    public const PER_MINUTE = 120;
    public const PER_HOUR = 1500;

    /** Tools and crawlers that only copy content. Case-insensitive substrings of the User-Agent. */
    private const BLOCKED_AGENTS = [
        'curl/', 'python-requests', 'python-urllib', 'aiohttp', 'httpx', 'scrapy', 'wget/', 'go-http-client', 'java/', 'okhttp',
        'libwww-perl', 'node-fetch', 'axios/', 'headlesschrome', 'phantomjs', 'puppeteer', 'playwright', 'selenium',
        'httrack', 'webcopier', 'sitesucker', 'offline explorer',
        'gptbot', 'chatgpt-user', 'ccbot', 'bytespider', 'claudebot', 'anthropic-ai', 'perplexitybot', 'amazonbot',
        'imagesiftbot', 'diffbot', 'omgili', 'petalbot', 'semrushbot', 'ahrefsbot', 'mj12bot', 'dotbot', 'dataforseobot',
        'blexbot', 'serpstatbot', 'megaindex', 'seekport',
    ];

    /** Search engines and link previews that must keep working. */
    private const ALLOWED_AGENTS = ['googlebot', 'google-inspectiontool', 'adsbot-google', 'bingbot', 'duckduckbot', 'yandexbot',
        'applebot', 'facebookexternalhit', 'whatsapp', 'twitterbot', 'linkedinbot', 'telegrambot'];

    public function handle(Request $request, Response $response, callable $next): void
    {
        if (PHP_SAPI === 'cli' || !$this->isPublicPage($request)) {
            $next($request, $response);
            return;
        }
        $ua = strtolower((string)($request->header('User-Agent') ?? ''));
        $friendly = $this->matches($ua, self::ALLOWED_AGENTS);
        if (!$friendly && ($ua === '' || $this->matches($ua, self::BLOCKED_AGENTS))) {
            $this->refuse($response, 403, 'Automated access to Jobsence is not allowed. For data partnerships write to gm@jobsence.com.');
            return;
        }
        if (!$friendly && !$this->withinLimits($request->ip())) {
            $response->setHeader('Retry-After', '60');
            $this->refuse($response, 429, 'Too many pages too fast – please wait a minute and try again.');
            return;
        }
        $next($request, $response);
    }

    /** Pages anyone can read (not static files, APIs with their own auth, or the admin area). */
    private function isPublicPage(Request $request): bool
    {
        $path = $request->getPath();
        return $request->getMethod() === 'GET'
            && !preg_match('#^/(api/|admin|assets/|css/|js/|images/|uploads/|robots\.txt|sitemap|favicon)#', $path);
    }

    private function matches(string $ua, array $needles): bool
    {
        foreach ($needles as $n) {
            if (str_contains($ua, $n)) {
                return true;
            }
        }
        return false;
    }

    /** Per-IP counters for the current minute and hour, kept in storage/cache/guard (old files expire by name). */
    private function withinLimits(string $ip): bool
    {
        $dir = dirname(__DIR__, 2) . '/storage/cache/guard';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            return true; // never block because of a storage problem
        }
        $key = substr(sha1($ip), 0, 16);
        $ok = true;
        foreach ([['m', date('YmdHi'), self::PER_MINUTE], ['h', date('YmdH'), self::PER_HOUR]] as [$kind, $slot, $max]) {
            $file = "$dir/{$kind}{$slot}_{$key}";
            $n = (int)@file_get_contents($file) + 1;
            @file_put_contents($file, (string)$n, LOCK_EX);
            if ($n > $max) {
                $ok = false;
            }
        }
        if (random_int(1, 200) === 1) {
            $this->cleanup($dir);
        }
        return $ok;
    }

    private function cleanup(string $dir): void
    {
        $keep = [date('YmdHi'), date('YmdH')];
        foreach (glob($dir . '/*') ?: [] as $f) {
            $base = basename($f);
            $slot = substr($base, 1, strpos($base, '_') - 1);
            if (!in_array($slot, $keep, true) && filemtime($f) < time() - 7200) {
                @unlink($f);
            }
        }
    }

    private function refuse(Response $response, int $code, string $message): void
    {
        $response->setStatusCode($code);
        $response->setHeader('Content-Type', 'text/plain; charset=utf-8');
        $response->setBody($message); // outputs and exits
    }
}
