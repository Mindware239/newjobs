<?php

/**
 * Jobs in India – check every official source's homepage for an RSS / Atom feed it advertises.
 * Found feeds are saved as suggestions (Admin → Jobs in India → Sources) – nothing is enabled
 * automatically. Re-checks each source at most once a week.
 *
 * Usage: php scripts/discover_india_job_feeds.php [--all]
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
\Dotenv\Dotenv::createUnsafeMutable(__DIR__ . '/../')->safeLoad();
if (is_file(__DIR__ . '/../.env.local')) {
    \Dotenv\Dotenv::createUnsafeMutable(__DIR__ . '/../', '.env.local')->safeLoad();
}

use App\Core\Database;
use App\Models\ExternalJob;
use App\Services\IndiaJobs\FeedFetcher;

ExternalJob::ensureSchema();
$all = in_array('--all', $argv, true);
$rows = Database::getInstance()->fetchAll(
    "SELECT * FROM external_job_sources WHERE website IS NOT NULL AND website <> ''
       AND (feed_url IS NULL OR feed_url = '')" . ($all ? '' : ' AND (checked_at IS NULL OR checked_at < NOW() - INTERVAL 7 DAY)') . '
     ORDER BY id'
);

$found = 0;
foreach ($rows as $i => $s) {
    $line = FeedFetcher::discover($s);
    $found += str_contains($line, 'feed found') ? 1 : 0;
    printf("[%d/%d] %s – %s\n", $i + 1, count($rows), $s['name'], $line);
    usleep(300000); // be polite
}
echo "Checked " . count($rows) . " sources, feeds found: {$found}\n";
