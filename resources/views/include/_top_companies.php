<?php
/**
 * "Top Hiring Companies" strip – today's paid top places (TopBidding::topCompaniesToday: monthly place 1, then
 * the day's auction winners). Shown on the homepage and at the top of /jobs. Logos only with the paid logo right.
 */
use App\Helpers\Lang;

try {
    $tcList = \App\Services\TopPlaces\TopBidding::topCompaniesToday();
} catch (\Throwable $e) {
    $tcList = [];
}
$tcH = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<section class="tc" aria-labelledby="tc-title">
    <div class="tc-head">
        <h2 id="tc-title">🏆 <?= Lang::t('टॉप हायरिंग कंपनियाँ', 'Top Hiring Companies') ?></h2>
        <a href="/top-places"><?= Lang::t('अपनी कंपनी यहाँ दिखाएँ', 'Show your company here') ?> →</a>
    </div>
    <?php if ($tcList): ?>
        <div class="tc-row">
            <?php foreach ($tcList as $i => $c):
                $tcHref = '/jobs?company_filter[]=' . rawurlencode((string)$c['company_name']); ?>
                <a class="tc-card" href="<?= $tcH($tcHref) ?>">
                    <span class="tc-rank">#<?= $i + 1 ?></span>
                    <?php if ($c['show_logo']): ?><img src="<?= $tcH($c['logo_url']) ?>" alt="" width="48" height="48" loading="lazy">
                    <?php else: ?><span class="tc-ini" aria-hidden="true"><?= $tcH(mb_strtoupper(mb_substr((string)$c['company_name'], 0, 1))) ?></span><?php endif; ?>
                    <span class="tc-name"><?= $tcH($c['company_name']) ?></span>
                    <span class="tc-meta"><?= $c['open_jobs'] ?> <?= Lang::t('खुली नौकरियाँ', 'open jobs') ?><?= $c['city'] ? ' · ' . $tcH($c['city']) : '' ?></span>
                    <span class="tc-ad"><?= Lang::t('प्रायोजित', 'Sponsored') ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="tc-empty"><?= Lang::t('आज की टॉप जगहें खाली हैं – बोली ₹500 + GST से शुरू।', 'Today\'s top places are open – bids start at ₹500 + GST.') ?> <a href="/top-places"><?= Lang::t('बोली लगाएँ', 'Place a bid') ?> →</a></p>
    <?php endif; ?>
</section>
<style>
    .tc { max-width: 1200px; margin: 18px auto; padding: 0 16px; }
    .tc-head { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; flex-wrap: wrap; }
    .tc-head h2 { font-size: 1.25rem; font-weight: 900; margin: 0; }
    .tc-head a { font-size: .9rem; font-weight: 700; color: #f05537; }
    .tc-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 10px; margin-top: 10px; }
    .tc-card { position: relative; display: flex; flex-direction: column; align-items: center; gap: 4px; text-align: center; padding: 14px 10px 10px; border: 2px solid #fcd34d; border-radius: 14px; background: #fffbeb; text-decoration: none; color: #1f2937; }
    .tc-card:hover { border-color: #f05537; }
    .tc-card img, .tc-ini { width: 48px; height: 48px; border-radius: 10px; object-fit: contain; background: #fff; }
    .tc-ini { display: grid; place-items: center; font-weight: 900; font-size: 1.3rem; color: #f05537; border: 1px solid #fde68a; }
    .tc-rank { position: absolute; top: 6px; left: 8px; font-size: .75rem; font-weight: 900; color: #b45309; }
    .tc-name { font-weight: 800; font-size: .95rem; }
    .tc-meta { font-size: .8rem; color: #4b5563; }
    .tc-ad { font-size: .68rem; color: #9ca3af; text-transform: uppercase; letter-spacing: .04em; }
    .tc-empty { margin: 8px 0 0; font-size: .9rem; color: #4b5563; }
</style>
