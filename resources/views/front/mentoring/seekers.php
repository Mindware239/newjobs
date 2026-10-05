<?php
require __DIR__ . "/_shared.php";
use App\Services\Registration\ContactPass;
use App\Services\Registration\FormRegistry;
use App\Services\Registration\Mentoring;

$isSkill = $kind === 'skill';
$words = [
    'skill' => ['h1' => ['भारत में कौन कौन सा स्किल सीखना चाहता है', 'Who wants to learn which skill in India'], 'top' => ['सबसे ज़्यादा माँगे गए स्किल', 'Most wanted skills'],
        'join' => ['मेंटर, ग्रुप मेंटर या ट्रेनिंग संस्थान हैं? मुफ़्त रजिस्टर करें और पूरे नाम देखें।', 'Are you a mentor, group mentor or training institute? Register free and see full names.']],
    'internship' => ['h1' => ['भारत में कौन किस डोमेन में इंटर्नशिप चाहता है', 'Who wants an internship in which domain in India'], 'top' => ['सबसे ज़्यादा माँगे गए डोमेन', 'Most wanted domains'],
        'join' => ['इंटर्नशिप देते हैं? मुफ़्त रजिस्टर करें और पूरे नाम देखें।', 'Do you offer internships? Register free and see full names.']],
    'job' => ['h1' => ['भारत में कौन किस नौकरी के लिए तैयार है', 'Who is ready for which job in India'], 'top' => ['सबसे ज़्यादा चाहे गए जॉब रोल', 'Most wanted job roles'],
        'join' => ['भर्ती करते हैं? कंपनी / दुकान मुफ़्त रजिस्टर करें और पूरे नाम देखें।', 'Hiring? Register your company / shop free and see full names.']],
][$kind];
$here = $_SERVER['REQUEST_URI'] ?? $k['seekers_page'];
$canSeeNames = $isProvider;
?>
<div class="sd">
    <section class="sd-section mt-hero">
        <div class="sd-wrap">
            <div style="display:flex;justify-content:flex-end;margin-bottom:10px"><?php $sdLangSwitcher(); ?></div>
            <?php $youngIndiaBanner(); ?>
            <?php $mtTabs((string)$k['seekers_page']); ?>
            <h1><?= $tp($words['h1']) ?></h1>
            <?php $mtFlash($flash); ?>

            <div class="mt-stats">
                <div class="mt-stat"><div class="n"><?= (int)$stats['seekers'] ?></div><b><?= $t('सक्रिय ' . $k['seekers_label'][0], 'active ' . strtolower($k['seekers_label'][1])) ?></b><br>
                    <span class="mt-meta"><?= (int)$stats['providers'] ?> <?= $t('सत्यापित ' . $k['providers_label'][0], 'verified ' . strtolower($k['providers_label'][1])) ?></span></div>
                <div class="mt-stat"><b><?= $tp($words['top']) ?></b>
                    <ul class="mt-bars"><?php foreach (array_slice($stats['categories'], 0, 8, true) as $c => $n): ?><li><a href="?q=<?= rawurlencode($c) ?>"><?= $h($c) ?></a><b><?= (int)$n ?></b></li><?php endforeach; ?></ul></div>
                <div class="mt-stat"><b><?= $t('राज्य के अनुसार', 'By state') ?></b>
                    <ul class="mt-bars"><?php foreach (array_slice($stats['states'], 0, 8, true) as $c => $n): ?><li><a href="?loc=<?= rawurlencode($c) ?>"><?= $h($c) ?></a><b><?= (int)$n ?></b></li><?php endforeach; ?></ul></div>
                <div class="mt-stat"><b><?= $t('पसंदीदा समय', 'Preferred timing') ?></b>
                    <ul class="mt-bars"><?php foreach ($stats['timing'] as $c => $n): if (!isset(FormRegistry::TIMINGS[$c])) continue; ?><li><a href="?timing=<?= rawurlencode($c) ?>"><?= $tp(FormRegistry::TIMINGS[$c]) ?></a><b><?= (int)$n ?></b></li><?php endforeach; ?></ul></div>
            </div>

            <?php if ($isProvider): ?>
                <div class="mt-box" style="margin:0">
                    <?php if ($plan): ?>
                        ✅ <?= $t('प्लान सक्रिय · आज बची प्रोफ़ाइल: ', 'Plan active · profiles left today: ') ?><b><?= (int)$quota['today'] ?></b><?= $quota['total'] !== null ? ' · ' . $t('प्लान में बची: ', 'left in plan: ') . (int)$quota['total'] . '/20' : '' ?>
                    <?php else: ?>
                        <?= $t('आप नाम, स्थान और स्किल देख रहे हैं। पूरी प्रोफ़ाइल, रिज़्यूमे और समझौते के लिए ₹155 का प्लान लें।', 'You can see names, places and skills. Get the ₹155 plan for full profiles, resumes and agreements.') ?>
                        <?php if (Mentoring::planOptions($me)): ?>
                        <a class="sd-btn mt-green" style="padding:6px 12px;margin-left:6px" href="/mentoring#plan"><?= $t('भर्ती प्लान चुनें', 'Choose a hiring plan') ?></a>
                        <?php else: ?>
                        <form method="POST" action="/mentoring/plan" style="display:inline"><?= $mtCsrf() ?><button class="sd-btn mt-green" style="padding:6px 12px;margin-left:6px" type="submit"><?= $t('₹155 प्लान लें', 'Get ₹155 plan') ?></button></form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="mt-box" style="margin:0;display:flex;gap:10px;align-items:center;flex-wrap:wrap;justify-content:space-between">
                    <span><?= $tp($words['join']) ?></span>
                    <a class="sd-btn mt-green" href="<?= $h($k['provider_form']) ?>"><?= $tb('मुफ़्त रजिस्टर करें', 'Register free') ?></a>
                </div>
            <?php endif; ?>

            <?php $mtSearch($f, true); ?>
        </div>
    </section>

    <section class="sd-section" style="padding-top:16px">
        <div class="sd-wrap">
            <p style="margin:0 0 10px;color:#4b5563"><?= $t($total . ' परिणाम', $total . ' result' . ($total === 1 ? '' : 's')) ?> · <?= $t('फ़ोन और ईमेल केवल दोनों के शुल्क और समझौते के बाद', 'phone & email only after both have paid and signed') ?></p>
            <?php if (!$rows): ?>
                <div class="sd-card" style="text-align:center"><?= $t('कोई परिणाम नहीं। दूसरे कीवर्ड या स्थान से खोजें।', 'No results. Try another keyword or place.') ?></div>
            <?php else: ?>
                <div class="mt-list">
                    <?php foreach ($rows as $r): $d = $r['details']; $opened = $isProvider && Mentoring::hasOpened((int)$me['id'], (int)$r['id']); ?>
                        <article class="mt-card">
                            <h3><?= $h($canSeeNames ? $r['full_name'] : ContactPass::maskName((string)$r['full_name'])) ?></h3>
                            <div class="mt-meta">📍 <?= $h(implode(', ', array_filter([$r['city'] ?: $r['district'], $r['state']]))) ?><?= !empty($r['preferred_location']) ? ' · ' . $t('पसंद: ', 'prefers: ') . $h($r['preferred_location']) : '' ?></div>
                            <div class="mt-meta">🎓 <?= $h($mtQual($r['qualification'])) ?><?= !empty($d['current_course']) ? ' · ' . $h($d['current_course']) : '' ?></div>
                            <div class="mt-skills"><?php foreach (array_slice(array_filter(array_map('trim', explode(',', (string)$r['categories']))), 0, 5) as $c): ?><span><?= $h($c) ?></span><?php endforeach; ?></div>
                            <?php if (!empty($d['preferred_timing'])): ?><div class="mt-meta">🕘 <?= $h($mtTiming($d['preferred_timing'])) ?></div><?php endif; ?>
                            <?php if ($isSkill && !empty($d['training_mode'])): ?><div class="mt-meta">💻 <?= $d['training_mode'] === 'offline_ncr' ? $t('ऑफलाइन – दिल्ली-NCR', 'Offline – Delhi-NCR') : $t('ऑनलाइन / वीडियो', 'Online / video') ?></div><?php endif; ?>
                            <?php if ($isProvider): ?>
                                <div class="mt-act">
                                    <?php if ($opened && $plan): ?>
                                        <a class="sd-btn mt-green" href="/mentoring/seeker/<?= (int)$r['id'] ?>"><?= $t('पूरी प्रोफ़ाइल', 'Full profile') ?></a>
                                    <?php elseif ($plan): ?>
                                        <form method="POST" action="/mentoring/open/<?= (int)$r['id'] ?>"><?= $mtCsrf() ?><input type="hidden" name="back" value="<?= $h($here) ?>">
                                            <button class="sd-btn mt-green" type="submit" <?= $quota['can'] ? '' : 'disabled' ?>><?= $t('पूरी प्रोफ़ाइल खोलें (आज के 3 में से 1)', 'Open full profile (1 of today’s 3)') ?></button></form>
                                    <?php else: ?>
                                        <span class="mt-lock">🔒 <?= $t('पूरी प्रोफ़ाइल व रिज़्यूमे – ₹155 प्लान', 'Full profile & resume – ₹155 plan') ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="mt-lock">🔒 <?= $t('फ़ोन / ईमेल छिपा है', 'Phone / email hidden') ?></div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
                <?php if ($total > $perPage): $qs = $f; ?>
                    <div class="mt-pager">
                        <?php if ($page > 1): ?><a href="?<?= $h(http_build_query(array_filter($qs) + ['page' => $page - 1])) ?>">← <?= $t('पिछला', 'Previous') ?></a><?php endif; ?>
                        <?php if ($page * $perPage < $total): ?><a href="?<?= $h(http_build_query(array_filter($qs) + ['page' => $page + 1])) ?>"><?= $t('अगला', 'Next') ?> →</a><?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($isProvider && $unpaid): ?>
                <h2 style="font-size:1.15rem;margin-top:26px"><?= $t('फॉर्म भरा, शुल्क बाकी', 'Filled the form, fee pending') ?></h2>
                <p class="mt-meta" style="margin:0 0 10px"><?= $t('शुल्क दोनों पक्षों के लिए ज़रूरी है। “आमंत्रित करें” दबाने पर उम्मीदवार को शुल्क भरने का ईमेल जाएगा।', 'The fee is mandatory for both parties. “Invite” emails the candidate to pay the form fee.') ?></p>
                <div class="mt-list">
                    <?php foreach ($unpaid as $r): ?>
                        <article class="mt-card">
                            <h3><?= $h(ContactPass::maskName((string)$r['full_name'])) ?> <span class="mt-tag w"><?= $t('शुल्क बाकी', 'Fee pending') ?></span></h3>
                            <div class="mt-meta">📍 <?= $h(implode(', ', array_filter([$r['city'] ?: $r['district'], $r['state']]))) ?></div>
                            <div class="mt-skills"><?php foreach (array_slice(array_filter(array_map('trim', explode(',', (string)$r['categories']))), 0, 5) as $c): ?><span><?= $h($c) ?></span><?php endforeach; ?></div>
                            <div class="mt-act">
                                <?php if ($plan): ?>
                                    <form method="POST" action="/mentoring/invite/<?= (int)$r['id'] ?>"><?= $mtCsrf() ?><input type="hidden" name="back" value="<?= $h($here) ?>"><button class="sd-btn" type="submit"><?= $t('आमंत्रित करें', 'Invite to pay') ?></button></form>
                                <?php else: ?><span class="mt-lock">🔒 <?= $t('आमंत्रण के लिए ₹155 प्लान', '₹155 plan needed to invite') ?></span><?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div style="margin-top:22px;font-size:.85rem;color:#4b5563;background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px 14px"><?= $tp(FormRegistry::platformDisclaimer()) ?> <a href="/agreement-terms/<?= $h($kind) ?>"><?= $t('त्रिपक्षीय समझौते की शर्तें पढ़ें', 'Read the tripartite agreement terms') ?></a></div>
        </div>
    </section>
</div>
