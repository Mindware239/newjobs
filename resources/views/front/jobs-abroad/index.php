<?php
require dirname(__DIR__) . '/apply/_partials.php';
use App\Services\Registration\FormRegistry;

$fee = '₹' . number_format(FormRegistry::INTL_COUNTRY_FEE_INR) . ' / USD ' . (int)FormRegistry::INTL_COUNTRY_FEE_USD;
$applyUrl = static fn(string $country, string $role = '') => '/apply/international-job?' . http_build_query(array_filter(['country' => $country, 'cat' => $role]));
?>
<style>
.sd .ja-hero { background: linear-gradient(135deg, #eef2ff 0%, #fff 60%, #fff7ed 100%); border-bottom: 1px solid #e5e7eb; }
.sd .ja-hero h1 { font-size: clamp(1.5rem, 4vw, 2.3rem); font-weight: 900; margin: 6px 0; line-height: 1.2; }
.sd .ja-crumbs { font-size: .85rem; color: #6b7280; } .sd .ja-crumbs a { color: #6b7280; }
.sd .ja-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px; }
.sd .ja-grid.wide { grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 14px; }
.sd .ja-c { display: block; background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 12px 14px; text-decoration: none; color: #111827; font-weight: 800; }
.sd .ja-c:hover { border-color: #4f46e5; color: #4f46e5; }
.sd .ja-c small { display: block; color: #6b7280; font-weight: 600; }
.sd .ja-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 14px 16px; }
.sd .ja-card h3 { font-size: 1.02rem; margin: 0 0 6px; }
.sd .ja-chips { display: flex; flex-wrap: wrap; gap: 5px; }
.sd .ja-chips a { padding: 4px 10px; border-radius: 999px; background: #f9fafb; border: 1px solid #e5e7eb; color: #374151; font-size: .82rem; text-decoration: none; }
.sd .ja-chips a:hover { border-color: #f05537; color: #f05537; }
.sd .ja-cta { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; justify-content: space-between; background: #fff; border: 2px solid #4f46e5; border-radius: 16px; padding: 14px 16px; margin-top: 14px; }
.sd .ja-note { font-size: .85rem; color: #4b5563; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px 14px; margin-top: 18px; }
</style>
<div class="sd">
    <section class="sd-section ja-hero">
        <div class="sd-wrap">
            <div style="display:flex;justify-content:flex-end;margin-bottom:10px"><?php $sdLangSwitcher(); ?></div>
            <nav class="ja-crumbs" aria-label="Breadcrumb">
                <a href="/jobs-abroad"><?= $t('विदेश में नौकरी', 'Jobs Abroad') ?></a>
                <?php if ($level !== 'all'): ?> › <a href="/jobs-abroad/<?= $h($slug) ?>"><?= $h($country) ?></a><?php endif; ?>
                <?php if ($level === 'category'): ?> › <?= $h($sector['short']) ?><?php endif; ?>
            </nav>
            <?php if ($level === 'all'): ?>
                <h1>🌍 <?= $t('विदेश में नौकरी – ' . count($all) . ' देश', 'Jobs Abroad – ' . count($all) . ' countries') ?></h1>
            <?php elseif ($level === 'country'): ?>
                <h1><?= $t("{$country} में नौकरी – सभी कैटेगरी", "Jobs in {$country} – all categories") ?></h1>
                <p style="margin:0;color:#4b5563"><?= $t((int)($counts['seekers'] ?? 0) . ' उम्मीदवार · ' . (int)($counts['companies'] ?? 0) . ' सत्यापित कंपनियाँ', (int)($counts['seekers'] ?? 0) . ' candidates · ' . (int)($counts['companies'] ?? 0) . ' verified companies hiring') ?></p>
            <?php else: ?>
                <h1><?= $t("{$country} में {$sector['short']} नौकरियाँ", "{$sector['short']} jobs in {$country}") ?></h1>
            <?php endif; ?>
            <div class="ja-cta">
                <span><b><?= $t('रजिस्ट्रेशन मुफ़्त', 'Registration is free') ?></b> · <?= $t("हर देश के लिए एक बार {$fee} (₹1,000 + GST)", "each country one-time {$fee} (₹1,000 + GST)") ?> · <?= $t('पासपोर्ट ज़रूरी', 'passport mandatory') ?></span>
                <span style="flex-basis:100%;font-size:.82rem;color:#7f1d1d">⚠️ <?= $t('हर नौकरी दूतावास / कॉन्सुलेट या उस देश के मंत्रालय से स्वयं पक्का करें – धोखाधड़ी के लिए Jobsence ज़िम्मेदार नहीं।', 'Verify every job yourself with the embassy / consulate or that country’s ministry – Jobsence is not liable for fraud.') ?></span>
                <a class="sd-btn" href="<?= $h($level === 'all' ? '/apply/international-job' : $applyUrl($country)) ?>"><?= $level === 'all' ? $tb('मुफ़्त रजिस्टर करें', 'Register free') : $tb("{$country} के लिए आवेदन करें", "Apply for {$country}") ?></a>
            </div>
        </div>
    </section>

    <section class="sd-section" style="padding-top:18px">
        <div class="sd-wrap">
            <?php $clStyle = 'margin:0 0 14px'; require dirname(__DIR__, 2) . '/include/_country_links.php'; ?>
            <?php if ($level === 'all'): ?>
                <h2 style="font-size:1.15rem"><?= $t('लोकप्रिय देश', 'Top destinations') ?></h2>
                <div class="ja-grid">
                    <?php foreach ($top as $s => $c): $n = $counts[mb_strtolower($c)] ?? []; ?>
                        <a class="ja-c" href="/jobs-abroad/<?= $h($s) ?>"><?= $h($c) ?><small><?= (int)($n['companies'] ?? 0) ?> <?= $t('कंपनियाँ', 'companies') ?> · <?= (int)($n['seekers'] ?? 0) ?> <?= $t('उम्मीदवार', 'candidates') ?></small></a>
                    <?php endforeach; ?>
                </div>
                <h2 style="font-size:1.15rem;margin-top:22px"><?= $t('सभी देश', 'All countries') ?></h2>
                <div class="ja-grid">
                    <?php foreach ($all as $s => $c): ?><a class="ja-c" style="font-weight:700" href="/jobs-abroad/<?= $h($s) ?>"><?= $h($c) ?></a><?php endforeach; ?>
                </div>
            <?php elseif ($level === 'country'): ?>
                <div class="ja-grid wide">
                    <?php foreach ($tree as $s): ?>
                        <article class="ja-card">
                            <h3><a href="/jobs-abroad/<?= $h($slug . '/' . $s['slug']) ?>" style="color:#111827;text-decoration:none"><?= $h($s['short']) ?></a></h3>
                            <div style="font-size:.85rem;color:#6b7280;margin-bottom:6px"><?= number_format($s['count']) ?> <?= $t('जॉब रोल', 'job roles') ?></div>
                            <div class="ja-chips">
                                <?php foreach (array_slice($s['subs'], 0, 6) as $x): ?><a href="<?= $h($applyUrl($country, $x['name'])) ?>"><?= $h($x['name']) ?></a><?php endforeach; ?>
                                <a href="/jobs-abroad/<?= $h($slug . '/' . $s['slug']) ?>"><?= $t('सभी देखें →', 'See all →') ?></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="ja-grid wide">
                    <?php foreach ($sector['subs'] as $x): ?>
                        <article class="ja-card">
                            <h3><?= $h($x['name']) ?> <small style="color:#6b7280;font-weight:600">(<?= number_format(count($x['skills'])) ?>)</small></h3>
                            <div class="ja-chips">
                                <?php foreach (array_slice($x['skills'], 0, 24) as $role): ?><a href="<?= $h($applyUrl($country, $role)) ?>"><?= $h($role) ?></a><?php endforeach; ?>
                            </div>
                            <a class="sd-btn" style="margin-top:10px;padding:8px 14px;font-size:.88rem" href="<?= $h($applyUrl($country, $x['name'])) ?>"><?= $t("{$country} में {$x['name']} – आवेदन", "Apply: {$x['name']} in {$country}") ?></a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="ja-note" style="border-color:#fecaca;background:#fef2f2;color:#7f1d1d;font-weight:600">⚠️ <?= $tp(FormRegistry::abroadDisclaimer()) ?></div>
            <div class="ja-note" style="border-color:#fecaca;background:#fef2f2;color:#7f1d1d;font-weight:600">🛂 <?= $tp(FormRegistry::visaDisclaimer()) ?></div>
            <div class="ja-note">
                <?= $t('Jobsence एक प्लेटफ़ॉर्म है, रिक्रूटिंग एजेंट नहीं। ECR पासपोर्ट वाले केवल eMigrate (emigrate.gov.in) पर पंजीकृत एजेंट के माध्यम से विदेश जाएँ। नौकरी या वीज़ा के लिए किसी को पैसे न दें।', 'Jobsence is a platform, not a recruiting agent. ECR passport holders must emigrate only through agents registered on eMigrate (emigrate.gov.in). Never pay anyone for a job or visa.') ?>
                <?= $tp(FormRegistry::platformDisclaimer()) ?>
                <a href="/hiring-companies"><?= $t('विदेश में भर्ती करने वाली कंपनियाँ', 'Companies hiring abroad') ?></a> · <a href="/apply/job-provider"><?= $t('विदेश के लिए भर्ती करते हैं? मुफ़्त रजिस्टर करें', 'Hiring for jobs abroad? Register free') ?></a>
            </div>
        </div>
    </section>
</div>
