<?php
require dirname(__DIR__) . '/apply/_partials.php';
require __DIR__ . '/_styles.php';
$typeLabel = isset($types[$job['org_type']]) ? $tp($types[$job['org_type']]) : $h(ucwords(str_replace('_', ' ', (string)$job['org_type'])));
$next = \App\Models\ExternalJob::url($job);
$row = static function (string $hi, string $en, $value) use ($t, $h): void {
    if ($value === null || $value === '') {
        return;
    } ?>
    <tr><td><?= $t($hi, $en) ?></td><td><?= $h($value) ?></td></tr>
<?php };
?>
<div class="sd">
    <section class="sd-section">
        <div class="sd-wrap" style="max-width:820px">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:12px;flex-wrap:wrap">
                <a href="/india-jobs" class="ij-more">← <?= $tb('सभी नौकरियाँ', 'All jobs') ?></a>
                <?php $sdLangSwitcher(); ?>
            </div>
            <article class="sd-card">
                <span class="ij-badge t-<?= $h($job['org_type']) ?>"><?= $typeLabel ?></span>
                <h1 style="font-size:1.5rem;margin:10px 0 4px"><?= $h($job['title']) ?></h1>
                <div class="ij-org" style="font-size:1rem"><?= $h($job['org_name']) ?></div>

                <table class="sd-table" style="margin:16px 0">
                    <?php
                    $row('राज्य / स्थान', 'State / Location', trim(implode(', ', array_filter([(string)$job['location'], (string)$job['state']])), ', ') ?: 'All India');
                    $row('पद', 'Vacancies', $job['vacancies'] ? number_format((int)$job['vacancies']) : null);
                    $row('अंतिम तिथि', 'Last date', $job['last_date'] ? date('d M Y', strtotime((string)$job['last_date'])) : null);
                    if ($unlocked) {
                        $row('योग्यता', 'Qualification', $job['qualification']);
                        $row('वेतन', 'Salary / Pay', $job['salary']);
                        $row('प्रकाशित', 'Published', $job['published_at'] ? date('d M Y', strtotime((string)$job['published_at'])) : null);
                    }
                    ?>
                </table>

                <?php if ($unlocked): ?>
                    <?php if ($job['details'] || $job['summary']): ?>
                        <h2 style="font-size:1.1rem"><?= $tb('विवरण', 'Details') ?></h2>
                        <div class="ij-details"><?= nl2br($h($job['details'] ?: $job['summary'])) ?></div>
                    <?php endif; ?>
                    <div class="sd-cta-row" style="margin-top:18px">
                        <?php if ($job['apply_url']): ?>
                            <a class="sd-btn" href="<?= $h($job['apply_url']) ?>" target="_blank" rel="noopener nofollow"><?= $tb('आधिकारिक वेबसाइट पर आवेदन करें ↗', 'Apply on the official website ↗') ?></a>
                        <?php endif; ?>
                        <?php if ($job['source_url'] && $job['source_url'] !== $job['apply_url']): ?>
                            <a class="sd-btn ghost" href="<?= $h($job['source_url']) ?>" target="_blank" rel="noopener nofollow"><?= $tb('आधिकारिक सूचना ↗', 'Official notification ↗') ?></a>
                        <?php endif; ?>
                    </div>
                    <p style="font-size:.85rem;color:#6b7280;margin-top:12px"><?= $t('स्रोत', 'Source') ?>: <?= $h($job['source_name'] ?: $job['org_name']) ?><?= $job['source_website'] ? ' – ' . $h(parse_url((string)$job['source_website'], PHP_URL_HOST)) : '' ?></p>
                <?php else: ?>
                    <?php require __DIR__ . '/_lock.php'; ?>
                <?php endif; ?>
            </article>

            <div class="ij-note">
                <?= $t('Jobsence इस विभाग / कंपनी से संबद्ध नहीं है। आवेदन केवल आधिकारिक वेबसाइट पर करें; कोई भी नौकरी के बदले पैसे माँगे तो सावधान रहें। Jobs Pass शुल्क कोई सरकारी शुल्क नहीं है।', 'Jobsence is not affiliated with this department / company. Apply only on the official website and beware of anyone asking for money for a job. The Jobs Pass fee is not a government fee.') ?>
            </div>
        </div>
    </section>
</div>
