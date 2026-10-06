<?php
$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$csrf = $h($_SESSION['csrf_token'] ?? '');
$input = 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm';
$kinds = ['job' => 'Job', 'internship' => 'Internship', 'skill' => 'Skill development', 'apprenticeship' => 'Apprenticeship'];
?>
<div>
    <div class="mb-6">
        <a href="/admin/india-jobs" class="text-sm font-semibold text-gray-500 hover:text-gray-900">&larr; Jobs in India</a>
        <h1 class="mt-2 text-3xl font-bold text-gray-900">Official sources &amp; feeds</h1>
        <p class="mt-2 text-sm text-gray-600">Add the RSS / Atom / JSON feed that an organisation itself publishes, enable it, and new notifications are imported every hour (cron <code>india_jobs_fetch</code>). Robots.txt is respected and only public http(s) feeds are allowed. Sources without a feed stay as a directory – add their jobs manually. Check every website before enabling. To look for feeds on all sources run <code>php scripts/discover_india_job_feeds.php</code> – found feeds appear below as suggestions.</p>
    </div>

    <?php if ($flash): ?>
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800"><?= $h($flash) ?></div>
    <?php endif; ?>

    <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-6 gap-3 rounded-lg bg-white p-4 shadow">
        <input type="text" name="q" value="<?= $h($filters['q']) ?>" placeholder="Search name or website" class="md:col-span-2 rounded-lg border border-gray-300 px-3 py-2 text-sm">
        <select name="type" class="rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="">All types</option><?php foreach ($types as $k => $l): ?><option value="<?= $h($k) ?>" <?= $filters['type'] === $k ? 'selected' : '' ?>><?= $h($l[1]) ?></option><?php endforeach; ?></select>
        <select name="feed" class="rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="">All sources</option><option value="yes" <?= $filters['feed'] === 'yes' ? 'selected' : '' ?>>With feed URL</option></select>
        <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-bold text-white">Filter</button>
    </form>

    <details class="mb-6 rounded-lg bg-white p-4 shadow">
        <summary class="cursor-pointer text-sm font-bold text-gray-900">+ Add a new source</summary>
        <form method="POST" action="/admin/india-jobs/sources" class="mt-4 grid grid-cols-1 md:grid-cols-6 gap-3">
            <input type="hidden" name="_token" value="<?= $csrf ?>">
            <input class="<?= $input ?> md:col-span-2" name="name" placeholder="Organisation name" required>
            <select class="<?= $input ?>" name="org_type"><?php foreach ($types as $k => $l): ?><option value="<?= $h($k) ?>"><?= $h($l[1]) ?></option><?php endforeach; ?></select>
            <select class="<?= $input ?>" name="state"><option value="">All India</option><?php foreach ($states as $st): ?><option value="<?= $h($st) ?>"><?= $h($st) ?></option><?php endforeach; ?></select>
            <input class="<?= $input ?> md:col-span-2" name="website" placeholder="https://official-website">
            <input class="<?= $input ?> md:col-span-4" name="feed_url" placeholder="Job page or feed URL – https://…/careers, https://…/rss.xml">
            <select class="<?= $input ?>" name="feed_type"><option value="rss">RSS / Atom</option><option value="json">JSON</option><option value="employmentnews">Employment News table</option><option value="upsc">UPSC website</option><option value="icsil">ICSIL jobs table</option><option value="govtlist">State board list (built-in profile)</option><option value="htmllinks" selected>Any job page (links)</option></select>
            <label class="text-xs font-semibold text-gray-600">Re-read every <input class="<?= $input ?>" type="number" name="crawl_every_days" value="<?= \App\Models\ExternalJob::ADDED_SITE_EVERY_DAYS ?>" min="0" max="365" style="width:5rem"> days (0 = hourly)</label>
            <label class="text-xs font-semibold text-gray-600">Keep reading until <input class="<?= $input ?>" type="date" name="crawl_until" value="<?= date('Y-m-d', strtotime('+' . \App\Models\ExternalJob::ADDED_SITE_YEARS . ' years')) ?>"></label>
            <select class="<?= $input ?>" name="kind"><?php foreach ($kinds as $k => $l): ?><option value="<?= $h($k) ?>"><?= $h($l) ?></option><?php endforeach; ?></select>
            <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white">Add source</button>
        </form>
    </details>

    <p class="mb-2 text-sm text-gray-600"><?= count($sources) ?> sources</p>
    <div class="space-y-3">
        <?php foreach ($sources as $s): ?>
            <form id="src-<?= (int)$s['id'] ?>" method="POST" action="/admin/india-jobs/sources/<?= (int)$s['id'] ?>" class="rounded-lg bg-white p-4 shadow grid grid-cols-1 md:grid-cols-6 gap-3 items-center">
                <input type="hidden" name="_token" value="<?= $csrf ?>">
                <div class="md:col-span-2">
                    <input class="<?= $input ?> font-semibold" name="name" value="<?= $h($s['name']) ?>" required>
                    <div class="mt-1 text-xs text-gray-500"><?= (int)$s['jobs'] ?> live jobs<?= $s['last_fetched_at'] ? ' · fetched ' . $h(date('d M H:i', strtotime((string)$s['last_fetched_at']))) . ' – ' . $h($s['last_status']) : '' ?></div>
                </div>
                <select class="<?= $input ?>" name="org_type"><?php foreach ($types as $k => $l): ?><option value="<?= $h($k) ?>" <?= $s['org_type'] === $k ? 'selected' : '' ?>><?= $h($l[1]) ?></option><?php endforeach; ?></select>
                <select class="<?= $input ?>" name="state"><option value="">All India</option><?php foreach ($states as $st): ?><option value="<?= $h($st) ?>" <?= $s['state'] === $st ? 'selected' : '' ?>><?= $h($st) ?></option><?php endforeach; ?></select>
                <div class="md:col-span-2 text-sm"><input class="<?= $input ?>" name="website" value="<?= $h($s['website']) ?>" placeholder="Website"><?php if ($s['website']): ?> <a class="text-xs text-primary hover:underline" href="<?= $h($s['website']) ?>" target="_blank" rel="noopener noreferrer">open ↗</a><?php endif; ?></div>
                <div class="md:col-span-3">
                    <input class="<?= $input ?>" name="feed_url" value="<?= $h($s['feed_url']) ?>" placeholder="Feed URL (RSS / Atom / JSON)" id="feed-<?= (int)$s['id'] ?>">
                    <?php if (!empty($s['suggested_feed']) && empty($s['feed_url'])): ?>
                        <div class="mt-1 text-xs text-amber-800">Suggested: <span class="break-all"><?= $h($s['suggested_feed']) ?></span>
                            <button type="button" class="font-bold text-primary hover:underline" onclick="document.getElementById('feed-<?= (int)$s['id'] ?>').value = <?= $h(json_encode($s['suggested_feed'])) ?>">Use this feed</button></div>
                    <?php elseif (!empty($s['checked_at']) && empty($s['feed_url'])): ?>
                        <div class="mt-1 text-xs text-gray-500">Checked <?= $h(date('d M', strtotime((string)$s['checked_at']))) ?>: <?= $h($s['last_status']) ?></div>
                    <?php endif; ?>
                </div>
                <select class="<?= $input ?>" name="kind"><?php foreach ($kinds as $k => $l): ?><option value="<?= $h($k) ?>" <?= ($s['kind'] ?? 'job') === $k ? 'selected' : '' ?>><?= $h($l) ?></option><?php endforeach; ?></select>
                <select class="<?= $input ?>" name="feed_type"><option value="rss" <?= $s['feed_type'] === 'rss' ? 'selected' : '' ?>>RSS / Atom</option><option value="json" <?= $s['feed_type'] === 'json' ? 'selected' : '' ?>>JSON</option><option value="manual" <?= $s['feed_type'] === 'manual' ? 'selected' : '' ?>>Manual only</option><option value="employmentnews" <?= $s['feed_type'] === 'employmentnews' ? 'selected' : '' ?>>Employment News table</option><option value="upsc" <?= $s['feed_type'] === 'upsc' ? 'selected' : '' ?>>UPSC website</option><option value="icsil" <?= $s['feed_type'] === 'icsil' ? 'selected' : '' ?>>ICSIL jobs table</option><option value="govtlist" <?= $s['feed_type'] === 'govtlist' ? 'selected' : '' ?>>State board list (built-in profile)</option><option value="htmllinks" <?= $s['feed_type'] === 'htmllinks' ? 'selected' : '' ?>>Any job page (links)</option></select>
                <label class="text-xs font-semibold text-gray-600">Every <input class="<?= $input ?>" type="number" name="crawl_every_days" value="<?= (int)($s['crawl_every_days'] ?? 0) ?>" min="0" max="365" style="width:4.5rem"> days</label>
                <label class="text-xs font-semibold text-gray-600">Until <input class="<?= $input ?>" type="date" name="crawl_until" value="<?= $h($s['crawl_until'] ?? '') ?>"></label>
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700"><input type="checkbox" name="enabled" value="1" <?= $s['enabled'] ? 'checked' : '' ?>> Auto-fetch</label>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-bold text-white">Save</button>
                    <?php if ($s['feed_url']): ?><button type="submit" formaction="/admin/india-jobs/sources/<?= (int)$s['id'] ?>/fetch" class="rounded-lg bg-primary px-3 py-2 text-xs font-bold text-white">Fetch now</button><?php endif; ?>
                    <a href="/admin/india-jobs?source_id=<?= (int)$s['id'] ?>" class="rounded-lg border px-3 py-2 text-xs font-semibold">Jobs</a>
                </div>
            </form>
        <?php endforeach; ?>
    </div>
</div>
