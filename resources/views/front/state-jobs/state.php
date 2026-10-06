<?php
require dirname(__DIR__) . '/apply/_partials.php';
require __DIR__ . '/_style.php';
use App\Services\JobBoard\StateJobBoard;
?>
<div class="sd">
    <section class="sd-section sj-hero">
        <div class="sd-wrap">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px">
                <a href="/jobs-by-state" class="ij-more">← <?= $tb('सभी राज्य (A–Z)', 'All states (A–Z)') ?></a>
                <?php $sdLangSwitcher(); ?>
            </div>
            <h1>📍 <?= $h($state === StateJobBoard::ALL_INDIA ? 'All India / Central Govt' : $state) ?> – <?= $t('शहर अनुसार नौकरियाँ', 'jobs city-wise') ?></h1>
            <p style="margin:0 0 12px;color:#374151"><b><?= (int)$count ?></b> <?= $t('नौकरियाँ, शहर A–Z, नई पोस्ट पहले।', 'jobs, cities A–Z, newest first.') ?></p>
            <a class="sj-post-cta" href="/post-job-free">➕ <?= $t('इस राज्य में मुफ़्त नौकरी पोस्ट करें', 'Post a job FREE in this state') ?></a>
            <?php if ($cities): ?>
                <nav class="sj-az" aria-label="Cities">
                    <?php foreach (array_keys($cities) as $i => $city): ?><a href="#city-<?= $i ?>" style="min-width:0"><?= $h($city) ?> (<?= count($cities[$city]) ?>)</a><?php endforeach; ?>
                </nav>
            <?php endif; ?>
        </div>
    </section>
    <section class="sd-section" style="padding-top:10px">
        <div class="sd-wrap">
            <?php if (!$cities): ?>
                <div class="sd-card" style="text-align:center"><b><?= $t('इस राज्य में अभी कोई खुली नौकरी नहीं।', 'No open jobs in this state right now.') ?></b>
                    <p style="margin:6px 0 0"><?= $t('क्या आप भर्ती कर रहे हैं? पहली नौकरी मुफ़्त पोस्ट करें।', 'Hiring? Post the first job free.') ?></p></div>
            <?php endif; ?>
            <?php foreach (array_keys($cities) as $i => $city): ?>
                <h2 class="sj-city" id="city-<?= $i ?>">🏙️ <?= $h($city) ?> <span>(<?= count($cities[$city]) ?>)</span></h2>
                <div class="sj-list">
                    <?php foreach ($cities[$city] as $j): $showPlace = false; require __DIR__ . '/_job.php'; endforeach; ?>
                </div>
            <?php endforeach; ?>
            <p style="margin-top:24px"><b><?= $t('दूसरे राज्य', 'Other states') ?>:</b>
                <?php foreach ($allStates as $s): if ($s === $state) { continue; } ?>
                    <a href="/jobs-by-state/<?= $h(StateJobBoard::slug($s)) ?>" style="margin-right:10px;white-space:nowrap"><?= $h($s) ?></a>
                <?php endforeach; ?>
            </p>
        </div>
    </section>
</div>
