<?php
require dirname(__DIR__) . '/apply/_partials.php';
$query = static fn(array $extra = []) => http_build_query(array_filter(array_merge($filters, $extra), static fn($v) => $v !== '' && $v !== null));
$pages = max(1, (int)ceil($total / $perPage));
$typeLabel = static fn(string $k) => isset($types[$k]) ? $tp($types[$k]) : $h(ucwords(str_replace('_', ' ', $k)));
require __DIR__ . '/_styles.php';
?>
<div class="sd">
    <section class="sd-section ij-hero">
        <div class="sd-wrap">
            <div style="display:flex;justify-content:flex-end;margin-bottom:10px"><?php $sdLangSwitcher(); ?></div>
            <h1><span class="ij-live" aria-hidden="true"></span><?= $t('भारत की नौकरियाँ देखें', 'See Jobs in India') ?></h1>
            <p class="ij-sub"><?= $t('भारतीय रेलवे, सेना, पुलिस, राज्य सरकारें, PSU (GAIL, BHEL, NTPC…), बैंक और Reliance, Tata जैसी बड़ी कंपनियों की नौकरियाँ – एक ही जगह, आधिकारिक लिंक के साथ।', 'Indian Railways, Army, Police, State Govts, PSUs (GAIL, BHEL, NTPC…), banks and big companies like Reliance and Tata – in one place, with official links.') ?></p>

            <?php if ($pass): ?>
                <div class="sd-alert ok">✅ <?= $t('आपका Jobs Pass सक्रिय है – ' . date('d M Y', strtotime((string)$pass['valid_until'])) . ' तक।', 'Your Jobs Pass is active till ' . date('d M Y', strtotime((string)$pass['valid_until'])) . '.') ?></div>
            <?php elseif (!$unlocked): ?>
                <div class="ij-pass">
                    <div>
                        <b><?= $t('Jobs Pass – ₹150 + GST (₹' . number_format((float)$passFee, 0) . ') / 1 महीना', 'Jobs Pass – ₹150 + GST (₹' . number_format((float)$passFee, 0) . ') / 1 month') ?></b>
                        <div><?= $t('लॉगिन → ईमेल OTP → भुगतान → सभी नौकरियों की पूरी जानकारी और आवेदन लिंक।', 'Log in → email OTP → pay → full details and apply links of every job.') ?></div>
                    </div>
                    <a class="sd-btn" href="<?= $loggedIn ? '/apply/jobs-pass' : '/login?redirect=' . rawurlencode('/apply/jobs-pass') ?>"><?= $loggedIn ? $tb('पास लें', 'Get pass') : $tb('लॉगिन करके पास लें', 'Log in & get pass') ?></a>
                </div>
            <?php endif; ?>

            <nav class="ij-tabs ij-kinds" aria-label="Listing kind">
                <a href="?<?= $h($query(['kind' => null, 'page' => null])) ?>" class="<?= $filters['kind'] === '' ? 'on' : '' ?>"><?= $t('सब कुछ', 'Everything') ?></a>
                <?php foreach ($kinds as $k => $label): ?>
                    <a href="?<?= $h($query(['kind' => $k, 'page' => null])) ?>" class="<?= $filters['kind'] === $k ? 'on' : '' ?>"><?= $tp($label) ?></a>
                <?php endforeach; ?>
            </nav>
            <nav class="ij-tabs" aria-label="Job type">
                <a href="?<?= $h($query(['type' => null, 'page' => null])) ?>" class="<?= $filters['type'] === '' ? 'on' : '' ?>"><?= $t('सभी', 'All') ?> <span><?= (int)array_sum($counts) ?></span></a>
                <?php foreach ($types as $k => $label): ?>
                    <a href="?<?= $h($query(['type' => $k, 'page' => null])) ?>" class="<?= $filters['type'] === $k ? 'on' : '' ?>"><?= $tp($label) ?> <span><?= (int)($counts[$k] ?? 0) ?></span></a>
                <?php endforeach; ?>
            </nav>

            <form method="GET" class="ij-filter">
                <?php if ($filters['type'] !== ''): ?><input type="hidden" name="type" value="<?= $h($filters['type']) ?>"><?php endif; ?>
                <?php if ($filters['kind'] !== ''): ?><input type="hidden" name="kind" value="<?= $h($filters['kind']) ?>"><?php endif; ?>
                <input type="search" name="q" value="<?= $h($filters['q']) ?>" placeholder="Railway, Constable, Engineer, GAIL, 10th pass…" aria-label="Search jobs">
                <select name="state" aria-label="State">
                    <option value=""><?= $h('All India / सभी राज्य') ?></option>
                    <?php foreach ($states as $st): ?><option value="<?= $h($st) ?>" <?= $filters['state'] === $st ? 'selected' : '' ?>><?= $h($st) ?></option><?php endforeach; ?>
                </select>
                <button class="sd-btn" type="submit"><?= $tb('खोजें', 'Search') ?></button>
            </form>
            <div class="ij-chips" aria-label="Popular searches">
                <?php foreach (['Agniveer' => 'Agniveer', 'CRPF' => 'CRPF', 'CISF' => 'CISF', 'Constable' => 'Constable', 'Peon / MTS' => 'MTS', 'Lecturer' => 'Lecturer', 'Professor' => 'Professor', 'School Teacher' => 'Teacher', 'Kindergarten Helper' => 'Kindergarten', 'Clerk' => 'Clerk', 'Driver' => 'Driver', 'Nurse' => 'Nurse', 'Engineer' => 'Engineer', '10th Pass' => '10th', '12th Pass' => '12th', 'Apprentice' => 'Apprentice'] as $label => $term): ?>
                    <a href="?<?= $h(http_build_query(['q' => $term])) ?>" class="<?= strcasecmp($filters['q'], $term) === 0 ? 'on' : '' ?>"><?= $h($label) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="sd-section" style="padding-top:8px">
        <div class="sd-wrap">
            <?php if (!$rows): ?>
                <div class="sd-card" style="text-align:center">
                    <b><?= $t('अभी इस खोज में कोई नौकरी नहीं है।', 'No jobs match this search right now.') ?></b>
                    <p style="margin:6px 0 0;color:#4b5563"><?= $t('नई सूचनाएँ रोज़ जोड़ी जाती हैं – बाद में दोबारा देखें।', 'New notifications are added every day – check back soon.') ?></p>
                </div>
            <?php endif; ?>

            <div class="ij-list">
                <?php foreach ($rows as $j): $url = \App\Models\ExternalJob::url($j); ?>
                    <article class="ij-job">
                        <div class="ij-job-top">
                            <span class="ij-badge t-<?= $h($j['org_type']) ?>"><?= $typeLabel((string)$j['org_type']) ?></span>
                            <?php if ($j['last_date']): $left = (int)floor((strtotime((string)$j['last_date']) - strtotime('today')) / 86400); ?>
                                <span class="ij-date <?= $left <= 3 ? 'soon' : '' ?>"><?= $t('अंतिम तिथि', 'Last date') ?>: <?= $h(date('d M Y', strtotime((string)$j['last_date']))) ?></span>
                            <?php endif; ?>
                        </div>
                        <h2><a href="<?= $h($url) ?>"><?= $h($j['title']) ?></a></h2>
                        <div class="ij-org"><?= $h($j['org_name']) ?><?= $j['state'] ? ' · ' . $h($j['state']) : '' ?><?= $j['vacancies'] ? ' · ' . $h(number_format((int)$j['vacancies'])) . ' ' . $t('पद', 'posts') : '' ?></div>
                        <?php if ($unlocked && $j['summary']): ?>
                            <p class="ij-sum"><?= $h(mb_substr((string)$j['summary'], 0, 220)) ?>…</p>
                        <?php endif; ?>
                        <a class="ij-more" href="<?= $h($url) ?>"><?= $unlocked ? $tb('पूरी जानकारी और आवेदन लिंक →', 'Full details & apply link →') : '🔒 ' . $tb('पूरी जानकारी देखें', 'See full details') ?></a>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($pages > 1): ?>
                <div class="ij-pager">
                    <?php if ($page > 1): ?><a class="sd-btn ghost" href="?<?= $h($query(['page' => $page - 1])) ?>">← <?= $tb('पिछला', 'Previous') ?></a><?php endif; ?>
                    <span><?= (int)$page ?> / <?= (int)$pages ?></span>
                    <?php if ($page < $pages): ?><a class="sd-btn ghost" href="?<?= $h($query(['page' => $page + 1])) ?>"><?= $tb('अगला', 'Next') ?> →</a><?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($notices)): ?>
                <h2 style="font-size:1.2rem;margin:28px 0 10px"><?= $tb('ताज़ा सरकारी सूचनाएँ – परिणाम, परीक्षा, साक्षात्कार', 'Latest Govt notices – results, tests, interviews') ?></h2>
                <div class="ij-list">
                    <?php foreach ($notices as $n): ?>
                        <article class="ij-job">
                            <div class="ij-job-top">
                                <span class="ij-badge t-central_govt"><?= $n['doc_type'] ? $h($n['doc_type']) : $t('सूचना', 'Notice') ?></span>
                                <?php if ($n['published_at']): ?><span class="ij-date"><?= $h(date('d M Y', strtotime((string)$n['published_at']))) ?></span><?php endif; ?>
                            </div>
                            <h2 style="font-size:1rem"><?= $h($n['title']) ?></h2>
                            <div class="ij-org"><?= $h($n['source_name'] ?: 'Govt of India') ?></div>
                            <?php if ($unlocked): ?>
                                <?php foreach (array_slice($n['documents'], 0, 3) as $d): ?>
                                    <a class="ij-more" href="<?= $h($d['local'] ? '/' . $d['local'] : $d['url']) ?>" target="_blank" rel="noopener nofollow">📄 <?= $h($d['label']) ?></a>
                                <?php endforeach; ?>
                                <?php if ($n['page_url']): ?><a class="ij-more" href="<?= $h($n['page_url']) ?>" target="_blank" rel="noopener nofollow"><?= $tb('आधिकारिक सूचना ↗', 'Official notice ↗') ?></a><?php endif; ?>
                            <?php else: ?>
                                <span class="ij-more">🔒 <?= $tb('PDF के लिए Jobs Pass', 'Jobs Pass for the PDF') ?></span>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($employerJobs): ?>
                <h2 style="font-size:1.2rem;margin:28px 0 10px"><?= $tb('Jobsence पर कंपनियों की ताज़ा नौकरियाँ (मुफ़्त)', 'Latest jobs posted by companies on Jobsence (free)') ?></h2>
                <div class="ij-list">
                    <?php foreach ($employerJobs as $ej): ?>
                        <article class="ij-job">
                            <span class="ij-badge t-jobsence">Jobsence</span>
                            <h2><a href="/job/<?= $h($ej['slug']) ?>"><?= $h($ej['title']) ?></a></h2>
                            <div class="ij-org"><?= $h($ej['company_name'] ?: 'Company') ?><?= $ej['employment_type'] ? ' · ' . $h(ucwords(str_replace('_', ' ', (string)$ej['employment_type']))) : '' ?></div>
                            <a class="ij-more" href="/job/<?= $h($ej['slug']) ?>"><?= $tb('देखें और आवेदन करें →', 'View & apply →') ?></a>
                        </article>
                    <?php endforeach; ?>
                </div>
                <p style="margin-top:10px"><a class="sd-btn ghost" href="/jobs"><?= $tb('Jobsence की सभी नौकरियाँ', 'All Jobsence jobs') ?></a></p>
            <?php endif; ?>

            <div class="ij-note">
                <b><?= $t('ज़रूरी सूचना', 'Important notice') ?>:</b>
                <?= $t('Jobsence इन सरकारी विभागों, PSU या कंपनियों से संबद्ध नहीं है। आवेदन हमेशा आधिकारिक वेबसाइट पर करें; जानकारी की पुष्टि आधिकारिक अधिसूचना से करें; नौकरी के बदले पैसे माँगने वाले किसी भी व्यक्ति से सावधान रहें। ', 'Jobsence is not affiliated with these government departments, PSUs or companies. Always apply on the official website and confirm details in the official notification; beware of anyone asking for money for a job. ') ?>
                <?= $tp(\App\Services\Registration\FormRegistry::platformDisclaimer()) ?>
            </div>
        </div>
    </section>
</div>
