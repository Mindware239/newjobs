<?php
/**
 * Homepage: one "Featured" block – featured companies (companies.is_featured, admin) next to featured Govt & PSU
 * jobs (external_jobs.is_featured, admin star; topped up with the newest open Govt / PSU jobs) – plus the
 * prominent links to Jobs by State & City (A–Z) and free job posting.
 */
use App\Helpers\Lang;
use App\Models\ExternalJob;

$ftH = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
try {
    $ftCompanies = (new \App\Models\Company())->getFeaturedCompanies(12);
} catch (\Throwable $e) {
    $ftCompanies = [];
}
try {
    $ftJobs = ExternalJob::featured(8);
} catch (\Throwable $e) {
    $ftJobs = [];
}
$ftTypes = ['psu' => 'PSU', 'central_govt' => 'Central Govt', 'state_govt' => 'State Govt', 'bank' => 'Bank', 'railways' => 'Railways', 'defence' => 'Defence', 'police' => 'Police'];
?>
<section class="ft" aria-labelledby="ft-title">
    <div class="ft-board">
        <a class="ft-board-main" href="/jobs-by-state">📍 <b><?= Lang::t('राज्य और शहर अनुसार नौकरियाँ (A–Z)', 'Jobs by State & City (A–Z)') ?></b>
            <span><?= Lang::t('हर राज्य, हर शहर – पोस्ट की तारीख और समय के साथ', 'Every state and city – with the date & time each job was posted') ?></span></a>
        <a class="ft-board-post" href="/post-job-free">➕ <b><?= Lang::t('मुफ़्त नौकरी पोस्ट करें', 'Post a job FREE') ?></b>
            <span><?= Lang::t('कंपनी / प्रोप्राइटरशिप – कोई शुल्क नहीं', 'Company / proprietorship – no fee') ?></span></a>
    </div>

    <h2 id="ft-title" class="ft-title">⭐ <?= Lang::t('फ़ीचर्ड – कंपनियाँ और सरकारी / PSU नौकरियाँ', 'Featured – Companies and Govt / PSU jobs') ?></h2>
    <div class="ft-grid">
        <div class="ft-box">
            <h3>🏢 <?= Lang::t('फ़ीचर्ड कंपनियाँ', 'Featured Companies') ?></h3>
            <?php if ($ftCompanies): ?>
                <div class="ft-cos">
                    <?php foreach ($ftCompanies as $c): ?>
                        <a class="ft-co" href="/company/<?= $ftH($c['slug']) ?>">
                            <?php if (!empty($c['logo_url'])): ?><img src="<?= $ftH($c['logo_url']) ?>" alt="" loading="lazy" width="44" height="44"><?php else: ?><span class="ft-ini" aria-hidden="true"><?= $ftH(mb_strtoupper(mb_substr((string)$c['name'], 0, 1))) ?></span><?php endif; ?>
                            <span class="ft-co-name"><?= $ftH($c['name']) ?></span>
                            <?php if (!empty($c['industry'])): ?><small><?= $ftH(mb_strimwidth((string)$c['industry'], 0, 30, '…')) ?></small><?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="ft-empty"><?= Lang::t('जल्द ही यहाँ फ़ीचर्ड कंपनियाँ दिखेंगी।', 'Featured companies will appear here soon.') ?></p>
            <?php endif; ?>
            <a class="ft-more" href="/company/featured"><?= Lang::t('सभी फ़ीचर्ड कंपनियाँ', 'All featured companies') ?> →</a>
        </div>
        <div class="ft-box">
            <h3>🏛️ <?= Lang::t('फ़ीचर्ड सरकारी और PSU नौकरियाँ', 'Featured Govt & PSU Jobs') ?></h3>
            <?php if ($ftJobs): ?>
                <ul class="ft-jobs">
                    <?php foreach ($ftJobs as $j): ?>
                        <li>
                            <span class="ft-tag"><?= $ftH($ftTypes[$j['org_type']] ?? 'Govt') ?></span>
                            <a href="<?= $ftH(ExternalJob::url($j)) ?>"><?= $ftH(mb_strimwidth((string)$j['title'], 0, 90, '…')) ?></a>
                            <small><?= $ftH(mb_strimwidth((string)$j['org_name'], 0, 60, '…')) ?><?= $j['last_date'] ? ' · ' . Lang::t('अंतिम तिथि', 'Last date') . ' ' . $ftH(date('d M', strtotime((string)$j['last_date']))) : '' ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="ft-empty"><?= Lang::t('नई सरकारी नौकरियाँ जल्द।', 'New Govt jobs coming soon.') ?></p>
            <?php endif; ?>
            <a class="ft-more" href="/india-jobs"><?= Lang::t('सभी सरकारी, PSU और कंपनी नौकरियाँ', 'All Govt, PSU & company jobs') ?> →</a>
        </div>
    </div>
</section>
<style>
    .ft { max-width: 1100px; width: calc(100% - 32px); margin: 16px auto; box-sizing: border-box; }
    .ft-board { display: grid; grid-template-columns: 2fr 1fr; gap: 12px; margin-bottom: 16px; }
    .ft-board a { display: flex; flex-direction: column; gap: 2px; padding: 16px 18px; border-radius: 16px; text-decoration: none; color: #fff !important; }
    .ft-board b { font-size: 1.25rem; font-weight: 900; }
    .ft-board span { font-size: .88rem; opacity: .95; }
    .ft-board-main { background: linear-gradient(90deg, #f05537, #f59e0b); box-shadow: 0 4px 14px rgba(240,85,55,.3); }
    .ft-board-post { background: #059669; box-shadow: 0 4px 14px rgba(5,150,105,.3); }
    .ft-board a:hover { filter: brightness(1.06); }
    .ft-title { text-align: center; font-size: 1.35rem; font-weight: 900; color: #111827; margin: 0 0 12px; }
    .ft-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .ft-box { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 16px; display: flex; flex-direction: column; gap: 10px; box-shadow: 0 2px 6px rgba(0,0,0,.05); }
    .ft-box h3 { margin: 0; font-size: 1.15rem; font-weight: 900; color: #111827; }
    .ft-cos { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 8px; }
    .ft-co { display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 10px 6px; border: 1px solid #f3f4f6; border-radius: 12px; text-decoration: none; color: #111827; text-align: center; }
    .ft-co:hover { border-color: #f05537; }
    .ft-co img { width: 44px; height: 44px; object-fit: contain; }
    .ft-ini { width: 44px; height: 44px; border-radius: 50%; background: #fff7ed; color: #f05537; display: grid; place-items: center; font-weight: 900; font-size: 1.2rem; }
    .ft-co-name { font-weight: 800; font-size: .85rem; line-height: 1.2; }
    .ft-co small { color: #6b7280; font-size: .72rem; }
    .ft-jobs { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
    .ft-jobs li { border-bottom: 1px dashed #e5e7eb; padding-bottom: 7px; }
    .ft-jobs a { font-weight: 800; color: #111827; text-decoration: none; }
    .ft-jobs a:hover { color: #f05537; text-decoration: underline; }
    .ft-jobs small { display: block; color: #6b7280; font-size: .8rem; }
    .ft-tag { display: inline-block; background: #dbeafe; color: #1e40af; font-size: .68rem; font-weight: 900; padding: 1px 7px; border-radius: 999px; margin-right: 6px; vertical-align: 1px; }
    .ft-empty { color: #6b7280; margin: 0; }
    .ft-more { margin-top: auto; font-weight: 800; color: #f05537; text-decoration: none; }
    @media (max-width: 860px) { .ft-board, .ft-grid { grid-template-columns: 1fr; } }
</style>
