<?php
require dirname(__DIR__) . '/apply/_partials.php';
require __DIR__ . '/_style.php';
use App\Services\JobBoard\StateJobBoard;

$byLetter = [];
foreach ($groups as $state => $g) {
    $letter = $state === StateJobBoard::ALL_INDIA ? '★' : strtoupper(mb_substr($state, 0, 1));
    $byLetter[$letter][$state] = $g;
}
?>
<div class="sd">
    <section class="sd-section sj-hero">
        <div class="sd-wrap">
            <div style="display:flex;justify-content:flex-end;margin-bottom:8px"><?php $sdLangSwitcher(); ?></div>
            <h1>📍 <?= $t('राज्य और शहर अनुसार नौकरियाँ (A–Z)', 'Jobs by State & City (A–Z)') ?></h1>
            <p style="margin:0 0 12px;color:#374151"><?= $t(
                'पूरे भारत की नौकरियाँ – राज्य और शहर के क्रम में, पोस्ट की तारीख और समय के साथ। कंपनियों की मुफ़्त पोस्ट, Jobsence पर कंपनियों की नौकरियाँ और सरकारी / PSU भर्तियाँ – सब एक जगह।',
                'Jobs across India arranged state by state and city by city, with the date and time each was posted – free posts by companies, jobs from employers on Jobsence and Govt / PSU recruitment, all in one place.'
            ) ?> <b><?= (int)$total ?></b> <?= $t('नौकरियाँ अभी खुली हैं।', 'jobs open now.') ?></p>
            <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center">
                <a class="sj-post-cta" href="/post-job-free">➕ <?= $t('मुफ़्त नौकरी पोस्ट करें', 'Post a job FREE') ?></a>
                <span style="font-size:.88rem;color:#374151"><?= $t('प्राइवेट लिमिटेड, प्रोप्राइटरशिप, पार्टनरशिप, LLP, दुकान – कोई शुल्क नहीं (एम्प्लॉयर खाता ज़रूरी)।', 'Private limited, proprietorship, partnership, LLP, shops – no fee (employer account needed).') ?></span>
            </div>
            <form method="GET" class="ij-filter" style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap">
                <input type="search" name="q" value="<?= $h($q) ?>" placeholder="Driver, Accountant, Ludhiana, Patna…" aria-label="Search jobs" style="flex:1;min-width:200px;padding:10px 12px;border:1px solid #d1d5db;border-radius:10px">
                <button class="sd-btn" type="submit"><?= $tb('खोजें', 'Search') ?></button>
            </form>
            <nav class="sj-az" aria-label="States A to Z">
                <?php foreach (array_merge(['★'], range('A', 'Z')) as $L): ?>
                    <?php if (isset($byLetter[$L])): ?><a href="#letter-<?= $L === '★' ? 'all' : $L ?>"><?= $L ?></a><?php else: ?><span><?= $L ?></span><?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </div>
    </section>

    <section class="sd-section" style="padding-top:10px">
        <div class="sd-wrap">
            <h2 style="font-size:1.25rem;margin:6px 0 10px"><?= $q !== '' ? $t('खोज परिणाम', 'Search results') . ': ' . $h($q) : $t('ताज़ा पोस्ट की गई नौकरियाँ', 'Latest posted jobs') ?></h2>
            <?php if (!$latest): ?><p style="color:#6b7280"><?= $t('कोई नौकरी नहीं मिली।', 'No jobs found.') ?></p><?php endif; ?>
            <div class="sj-list">
                <?php foreach ($latest as $j): $showPlace = true; require __DIR__ . '/_job.php'; endforeach; ?>
            </div>

            <h2 style="font-size:1.25rem;margin:30px 0 0"><?= $t('राज्य चुनें – A से Z', 'Choose a state – A to Z') ?></h2>
            <?php foreach ($byLetter as $L => $states): ?>
                <h3 class="sj-letter" id="letter-<?= $L === '★' ? 'all' : $h($L) ?>"><?= $L === '★' ? $t('पूरे भारत में', 'All India') : $h($L) ?></h3>
                <div class="sj-states">
                    <?php foreach ($states as $state => $g): ?>
                        <a class="sj-state" href="/jobs-by-state/<?= $h(StateJobBoard::slug($state)) ?>">
                            <span class="n"><?= (int)$g['count'] ?></span><b><?= $h($state) ?></b>
                            <small><?= $h(implode(' · ', array_slice(array_keys($g['cities']), 0, 6))) ?></small>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <div class="ij-note" style="margin-top:24px"><?= $t(
                'सरकारी / PSU नौकरियों के लिए केवल आधिकारिक वेबसाइट पर आवेदन करें। कोई भी नौकरी के बदले पैसे माँगे तो सावधान रहें – Jobsence पर पोस्ट करना और देखना मुफ़्त है।',
                'For Govt / PSU jobs apply only on the official website. Beware of anyone asking for money for a job – posting and viewing on Jobsence is free.'
            ) ?></div>
        </div>
    </section>
</div>
