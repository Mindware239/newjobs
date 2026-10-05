<?php
require __DIR__ . "/_shared.php";
use App\Services\Registration\ContactPass;
use App\Services\Registration\FormRegistry;
use App\Services\Registration\Mentoring;

$isSkill = $kind === 'skill';
$words = [
    'skill' => ['h1' => ['सत्यापित मेंटर, ग्रुप मेंटर और ट्रेनिंग संस्थान', 'Verified mentors, group mentors & training institutes'],
        'pay' => ['मेंटर ढूँढ रहे हैं? एक बार का फॉर्म शुल्क ₹155 भरें – फिर सभी मेंटर के पूरे नाम और उनसे समझौता।', 'Looking for a mentor? Pay the one-time ₹155 form fee – then see every mentor’s full name and sign with them.'],
        'choose' => ['इन्हें मेंटर चुनें – समझौता', 'Choose as mentor – agreement']],
    'internship' => ['h1' => ['इंटर्नशिप देने वाली सत्यापित कंपनियाँ और संस्थान', 'Verified companies & institutes offering internships'],
        'pay' => ['इंटर्नशिप ढूँढ रहे हैं? ₹155 का फॉर्म भरें – फिर सभी प्रदाताओं के नाम और समझौता।', 'Looking for an internship? Fill the ₹155 form – then see all providers and sign with them.'],
        'choose' => ['इंटर्नशिप के लिए समझौता', 'Agreement for internship']],
    'job' => ['h1' => ['भर्ती करने वाली सत्यापित कंपनियाँ, दुकानें और संस्थान', 'Verified companies, shops & institutes hiring now'],
        'pay' => ['नौकरी ढूँढ रहे हैं? ₹155 का फॉर्म भरें – फिर सभी कंपनियों के नाम और उनसे समझौता।', 'Looking for a job? Fill the ₹155 form – then see every company’s name and sign with them.'],
        'choose' => ['इस कंपनी से समझौता', 'Agreement with this company']],
][$kind];
$here = $_SERVER['REQUEST_URI'] ?? $k['providers_page'];
$paidSeeker = $isSeeker && in_array($seekerState, ['open', 'matched'], true);
$kindLabels = [
    'individual' => ['व्यक्तिगत मेंटर', 'Individual mentor'], 'group' => ['ग्रुप मेंटरिंग', 'Group mentoring'], 'institute' => ['ट्रेनिंग संस्थान', 'Training institute'],
    'psu' => ['PSU', 'PSU'], 'govt' => ['सरकारी संस्था', 'Govt body'], 'company' => ['कंपनी', 'Company'], 'startup' => ['स्टार्टअप', 'Startup'], 'ngo' => ['NGO', 'NGO'],
];
$modes = ['online' => ['ऑनलाइन', 'Online'], 'offline_ncr' => ['ऑफलाइन (दिल्ली-NCR)', 'Offline (Delhi-NCR)'], 'both' => ['ऑनलाइन + ऑफलाइन', 'Online + offline'],
    'onsite' => ['ऑफिस में', 'On-site'], 'wfh' => ['वर्क फ्रॉम होम', 'Work from home'], 'hybrid' => ['हाइब्रिड', 'Hybrid']];
?>
<div class="sd">
    <section class="sd-section mt-hero">
        <div class="sd-wrap">
            <div style="display:flex;justify-content:flex-end;margin-bottom:10px"><?php $sdLangSwitcher(); ?></div>
            <?php $youngIndiaBanner(); ?>
            <?php $mtTabs((string)$k['providers_page']); ?>
            <h1><?= $tp($words['h1']) ?></h1>
            <?php $mtFlash($flash); ?>
            <?php if ($paidSeeker): ?>
                <div class="mt-box" style="margin:0"><?= $seekerState === 'open'
                    ? $t('आप सभी के पूरे नाम और स्किल देख रहे हैं। किसी को चुनें और समझौता भेजें – फ़ोन / ईमेल दोनों के साइन के बाद दिखेंगे।', 'You can see full names and skills. Choose one and send an agreement – phone / email appear after both have signed.')
                    : $t('आपको मेंटर / प्रदाता मिल चुका है – संपर्क अपने डैशबोर्ड पर देखें।', 'You already have a mentor / provider – see the contact on your dashboard.') ?></div>
            <?php else: ?>
                <div class="mt-box" style="margin:0;display:flex;gap:10px;align-items:center;flex-wrap:wrap;justify-content:space-between">
                    <span><?= $tp($words['pay']) ?></span>
                    <a class="sd-btn" href="<?= $h($k['seeker_form']) ?>"><?= $tb('₹155 फॉर्म भरें', 'Apply – ₹155') ?></a>
                </div>
            <?php endif; ?>
            <?php $mtSearch($f, false); ?>
        </div>
    </section>

    <section class="sd-section" style="padding-top:16px">
        <div class="sd-wrap">
            <p style="margin:0 0 10px;color:#4b5563"><?= $t($total . ' सत्यापित प्रदाता', $total . ' verified provider' . ($total === 1 ? '' : 's')) ?> · <?= $t('फ़ोन नंबर कभी नहीं दिखाए जाते – केवल समझौते के बाद', 'phone numbers are never shown – only after the agreement') ?></p>
            <?php if (!$rows): ?>
                <div class="sd-card" style="text-align:center"><?= $t('अभी कोई सत्यापित प्रदाता नहीं मिला।', 'No verified provider found yet.') ?> <a href="<?= $h($k['provider_form']) ?>"><?= $t('मुफ़्त रजिस्टर करें', 'Register free') ?></a></div>
            <?php else: ?>
                <div class="mt-list">
                    <?php foreach ($rows as $r): $d = $r['details']; $org = ($d['business_name'] ?? '') ?: ($d['institute_name'] ?? ''); ?>
                        <article class="mt-card">
                            <div><span class="mt-tag">✔ <?= $t('सत्यापित', 'Verified') ?></span><?php if (!empty($d['provider_kind']) && isset($kindLabels[$d['provider_kind']])): ?><span class="mt-tag g"><?= $tp($kindLabels[$d['provider_kind']]) ?></span><?php endif; ?></div>
                            <h3><?= $h($org !== '' ? $org : ($paidSeeker ? $r['full_name'] : ContactPass::maskName((string)$r['full_name']))) ?></h3>
                            <?php if ($org !== '' && $paidSeeker): ?><div class="mt-meta">👤 <?= $h($r['full_name']) ?></div><?php endif; ?>
                            <div class="mt-meta">📍 <?= $h(implode(', ', array_filter([$r['city'] ?: $r['district'], $r['state']]))) ?></div>
                            <div class="mt-skills"><?php foreach (array_filter(array_map('trim', explode(',', (string)$r['categories']))) as $i => $c): if (!$paidSeeker && $i >= 6) break; ?><span><?= $h($c) ?></span><?php endforeach; ?></div>
                            <div class="mt-meta">
                                <?php $m = $d['training_mode'] ?? ($d['work_mode'] ?? ''); if (isset($modes[$m])): ?>💻 <?= $tp($modes[$m]) ?><?php endif; ?>
                                <?php if (!empty($d['years_experience'])): ?> · <?= (int)$d['years_experience'] ?> <?= $t('साल अनुभव', 'yrs experience') ?><?php endif; ?>
                                <?php if (!empty($d['languages'])): ?> · 🗣 <?= $h(implode(', ', array_map(static fn($l) => FormRegistry::LANGUAGES[$l][1] ?? $l, (array)$d['languages']))) ?><?php endif; ?>
                            </div>
                            <?php if ($kind === 'job'): ?>
                                <div class="mt-meta">💼 <?= $h(implode(', ', array_map(static fn($x) => ['fulltime' => 'Full-time', 'parttime' => 'Part-time', 'wfh' => 'Work from home', 'onetime' => 'One-time'][$x] ?? $x, (array)($d['job_types'] ?? [])))) ?><?= !empty($d['salary_offered']) ? ' · 💰 ' . $h($d['salary_offered']) : '' ?><?= !empty($d['positions']) ? ' · ' . (int)$d['positions'] . ' ' . $t('पद', 'positions') : '' ?></div>
                            <?php elseif (!$isSkill): ?>
                                <div class="mt-meta">💰 <?= ($d['internship_pay'] ?? '') === 'unpaid' ? $t('अनपेड', 'Unpaid') : (!empty($d['stipend_offered']) ? '₹' . $h(number_format((int)$d['stipend_offered'])) . '/' . $t('माह', 'month') : $t('पेड / अनपेड', 'Paid / unpaid')) ?><?= !empty($d['positions']) ? ' · ' . (int)$d['positions'] . ' ' . $t('पद', 'positions') : '' ?></div>
                            <?php endif; ?>
                            <?php $about = (string)($d['education_experience'] ?? ($d['about_internship'] ?? ($d['about_jobs'] ?? ''))); if ($about !== '' && $paidSeeker): ?><div class="mt-meta" style="background:#f9fafb;border-radius:8px;padding:6px 8px"><?= $h(mb_strimwidth($about, 0, 300, '…')) ?></div><?php endif; ?>
                            <div class="mt-act">
                                <?php if ($isSeeker && $seekerState === 'open'): ?>
                                    <form method="POST" action="/mentoring/request/<?= (int)$r['id'] ?>"><?= $mtCsrf() ?><input type="hidden" name="back" value="<?= $h($here) ?>"><button class="sd-btn mt-green" type="submit"><?= $tp($words['choose']) ?></button></form>
                                <?php else: ?><span class="mt-lock">🔒 <?= $t('फ़ोन / ईमेल छिपा है', 'Phone / email hidden') ?></span><?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <?php if ($total > $perPage): ?>
                    <div class="mt-pager">
                        <?php if ($page > 1): ?><a href="?<?= $h(http_build_query(array_filter($f) + ['page' => $page - 1])) ?>">← <?= $t('पिछला', 'Previous') ?></a><?php endif; ?>
                        <?php if ($page * $perPage < $total): ?><a href="?<?= $h(http_build_query(array_filter($f) + ['page' => $page + 1])) ?>"><?= $t('अगला', 'Next') ?> →</a><?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <div style="margin-top:22px;font-size:.85rem;color:#4b5563;background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px 14px"><?= $tp(FormRegistry::platformDisclaimer()) ?> <a href="/agreement-terms/<?= $h($kind) ?>"><?= $t('त्रिपक्षीय समझौते की शर्तें पढ़ें', 'Read the tripartite agreement terms') ?></a></div>
        </div>
    </section>
</div>
