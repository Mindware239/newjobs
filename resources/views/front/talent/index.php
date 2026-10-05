<?php
require dirname(__DIR__) . '/apply/_partials.php';
use App\Services\Registration\ContactPass;
use App\Services\Registration\FormRegistry;

$unlocked = $pass !== null;
$isHospital = $mode === 'hospital';
$money = static fn($v) => '₹' . number_format((float)$v, 0);
$expLabels = ['fresher' => 'Fresher', '0_1' => '< 1 yr', '1_3' => '1–3 yrs', '3_5' => '3–5 yrs', '5_10' => '5–10 yrs', '10_plus' => '10+ yrs'];
$days = static fn($v) => implode(', ', array_map(static fn($x) => ucfirst((string)$x), (array)$v));
?>
<style>
.sd .tl-hero { background: linear-gradient(135deg, <?= $isHospital ? '#eff6ff' : '#fff7ed' ?> 0%, #fff 70%); border-bottom: 1px solid #e5e7eb; }
.sd .tl-hero h1 { font-size: clamp(1.6rem, 4.2vw, 2.3rem); font-weight: 900; margin: 0 0 6px; line-height: 1.15; }
.sd .tl-search { display: grid; grid-template-columns: 2fr 1fr auto; gap: 8px; align-items: end; background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 12px; box-shadow: 0 6px 18px rgba(0, 0, 0, .05); margin-top: 14px; }
.sd .tl-f { display: flex; flex-direction: column; gap: 4px; min-width: 0; font-size: .8rem; font-weight: 800; color: #374151; }
.sd .tl-f input { padding: 11px 12px; border: 1px solid #d1d5db; border-radius: 10px; font: inherit; font-size: 1rem; font-weight: 600; width: 100%; height: 46px; }
.sd .tl-search .sd-btn { height: 46px; padding-top: 0; padding-bottom: 0; }
@media (max-width: 700px) { .sd .tl-search { grid-template-columns: 1fr; } }
.sd .tl-fees { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin: 14px 0 0; }
.sd .tl-fees div { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 10px 12px; font-size: .9rem; }
.sd .tl-fees b { display: block; font-size: 1.05rem; }
.sd .tl-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 14px; }
@media (max-width: 400px) { .sd .tl-list { grid-template-columns: 1fr; } }
.sd .tl-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 14px 16px; min-width: 0; }
.sd .tl-card h2 { font-size: 1.05rem; margin: 0 0 2px; }
.sd .tl-meta { color: #4b5563; font-size: .87rem; margin-top: 3px; overflow-wrap: anywhere; }
.sd .tl-tag { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: .72rem; font-weight: 800; background: #ecfdf5; color: #047857; margin: 0 4px 3px 0; }
.sd .tl-tag.k { background: #fef3c7; color: #92400e; }
.sd .tl-contact { margin-top: 10px; display: flex; gap: 6px; flex-wrap: wrap; }
.sd .tl-contact a { padding: 6px 10px; border-radius: 8px; font-weight: 800; font-size: .85rem; text-decoration: none; }
.sd .tl-call { background: #f05537; color: #fff; }
.sd .tl-wa { background: #16a34a; color: #fff; }
.sd .tl-mail, .sd .tl-cv { background: #f3f4f6; color: #111827; border: 1px solid #e5e7eb; }
.sd .tl-locked { margin-top: 10px; font-size: .85rem; color: #6b7280; }
.sd .tl-brief { margin-top: 6px; font-size: .86rem; color: #374151; background: #f9fafb; border-radius: 8px; padding: 6px 8px; overflow-wrap: anywhere; }
</style>
<div class="sd">
    <section class="sd-section tl-hero">
        <div class="sd-wrap">
            <div style="display:flex;justify-content:flex-end;margin-bottom:10px"><?php $sdLangSwitcher(); ?></div>
            <?php if ($isHospital): ?>
                <h1>🏥 <?= $t('डॉक्टर और अस्पताल स्टाफ़ खोजें', 'Find Doctors & Hospital Staff') ?></h1>
                <p style="color:#4b5563;margin:0;max-width:780px"><?= $t('आपकी ज़रूरत के कीवर्ड उम्मीदवारों के विवरण और रिज़्यूमे से मिलाए जाते हैं। पास 15 दिन तक – नाम, मोबाइल, ईमेल और रिज़्यूमे।', 'Keywords from your requirement are matched with candidates’ briefs and resumes. The pass lasts 15 days – name, mobile, email and resume.') ?></p>
            <?php else: ?>
                <h1>🧑‍💼 <?= $t('पार्ट-टाइम लोग खोजें – टैलेंट सर्च', 'Talent Search – Hire Part-time People') ?></h1>
                <p style="color:#4b5563;margin:0;max-width:780px"><?= $t('एग्ज़िबिशन स्टाफ़, प्रमोटर, मॉडल, बाउंसर, ड्राइवर और 30,000+ काम – रिज़्यूमे कीवर्ड, शहर, घंटे और प्रति दिन भुगतान के साथ।', 'Exhibition staff, promoters, models, bouncers, drivers and 30,000+ roles – with resume keywords, city, hours and daily pay.') ?></p>
            <?php endif; ?>

            <?php if ($unlocked): ?>
                <div class="sd-alert ok" style="margin-top:14px">✅ <?= $t('आपका पास सक्रिय है – ' . date('d M Y, h:i A', strtotime((string)$pass['valid_until'])) . ' तक। पूरी जानकारी दिख रही है।', 'Your pass is active till ' . date('d M Y, h:i A', strtotime((string)$pass['valid_until'])) . '. Full details are visible.') ?>
                    <?php if ($isHospital && $role !== ''): ?><br><?= $t('भूमिका: ', 'Role: ') ?><b><?= $tp(FormRegistry::HEALTH_ROLES[$role]) ?></b><?php endif; ?>
                </div>
            <?php else: ?>
                <?php if ($isHospital): ?>
                    <div class="tl-fees">
                        <?php foreach (FormRegistry::HEALTH_ROLES as $k => $label): ?>
                            <div><?= $tp($label) ?><b><?= $money($fees[$k] / 1.18) ?> + GST</b><span style="color:#6b7280"><?= $t('कुल ' . $money($fees[$k]) . ' · 15 दिन', 'Total ' . $money($fees[$k]) . ' · 15 days') ?></span></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;border:2px dashed #f05537;border-radius:14px;padding:14px;margin-top:14px;background:#fff">
                    <span><b><?= $isHospital ? $t('नाम, नंबर, ईमेल और रिज़्यूमे देखने के लिए रजिस्टर करें', 'Register to see names, numbers, emails and resumes') : $t('नाम, नंबर और रिज़्यूमे देखने के लिए टैलेंट पास लें', 'Get the Talent Pass to see names, numbers and resumes') ?></b><br>
                        <?= $isHospital ? $t('भूमिका के अनुसार एक बार का शुल्क, 15 दिन', 'One-time fee by role, valid 15 days') : $t($money($fee) . ' (₹5,000 + GST) – 2 दिन (48 घंटे)', $money($fee) . ' (₹5,000 + GST) – 2 days (48 hours)') ?></span>
                    <a class="sd-btn" href="<?= $h($formUrl) ?>"><?= $isHospital ? $tb('अभी रजिस्टर करें', 'Register now') : $tb('पास लें', 'Get pass') ?></a>
                </div>
            <?php endif; ?>

            <form method="GET" class="tl-search">
                <label class="tl-f"><span><?= $t('कीवर्ड (स्किल, पद, विभाग)', 'Keywords (skill, post, department)') ?></span>
                    <input type="search" name="q" value="<?= $h($q) ?>" placeholder="<?= $isHospital ? 'Staff Nurse ICU, MD Medicine, Receptionist…' : 'Promoter, Hostess, Bouncer, Driver, Hindi…' ?>"></label>
                <label class="tl-f"><span><?= $t('शहर / राज्य', 'City / State') ?></span>
                    <input type="text" name="city" value="<?= $h($city) ?>" placeholder="Delhi, Noida, Pune…" autocomplete="address-level2"></label>
                <button class="sd-btn" type="submit"><?= $tb('खोजें', 'Search') ?></button>
            </form>
            <?php if ($auto): ?><p style="margin:8px 0 0;font-size:.85rem;color:#4b5563"><?= $t('आपकी ज़रूरत से मिलते उम्मीदवार: ', 'Candidates matching your requirement: ') ?><b><?= $h(implode(', ', array_slice($keywords, 0, 12))) ?></b></p><?php endif; ?>
        </div>
    </section>

    <section class="sd-section" style="padding-top:10px">
        <div class="sd-wrap">
            <?php if (!$results): ?>
                <div class="sd-card" style="text-align:center">
                    <b><?= $t('अभी कोई मिलता उम्मीदवार नहीं मिला। दूसरे कीवर्ड या शहर से खोजें।', 'No matching candidate yet. Try other keywords or a city.') ?></b>
                    <?php if ($unlocked && $auto): ?><p style="margin:6px 0 0"><a href="?all=1"><?= $t('सभी उम्मीदवार देखें', 'See all candidates') ?></a></p><?php endif; ?>
                </div>
            <?php else: ?>
                <p style="margin:0 0 10px;color:#4b5563"><?= $t(count($results) . ' उम्मीदवार – सबसे अच्छे मिलान पहले', count($results) . ' candidate' . (count($results) > 1 ? 's' : '') . ' – best match first') ?></p>
                <div class="tl-list">
                    <?php foreach ($results as $r): $d = $r['details']; ?>
                        <article class="tl-card">
                            <div>
                                <?php if ($isHospital && !empty($d['health_role']) && isset(FormRegistry::HEALTH_ROLES[$d['health_role']])): ?><span class="tl-tag"><?= $tp(FormRegistry::HEALTH_ROLES[$d['health_role']]) ?></span><?php endif; ?>
                                <?php if (!empty($d['experience'])): ?><span class="tl-tag"><?= $h($expLabels[$d['experience']] ?? $d['experience']) ?></span><?php endif; ?>
                                <?php foreach (array_slice($r['hits'], 0, 5) as $k): ?><span class="tl-tag k">✓ <?= $h($k) ?></span><?php endforeach; ?>
                            </div>
                            <h2><?= $h($unlocked ? $r['full_name'] : ContactPass::maskName((string)$r['full_name'])) ?></h2>
                            <div class="tl-meta"><b><?= $h($r['categories']) ?></b></div>
                            <div class="tl-meta">📍 <?= $h(implode(', ', array_filter([$r['city'], $r['district'] !== $r['city'] ? $r['district'] : '', $r['state']]))) ?><?= !empty($r['preferred_location']) ? ' · ' . $t('पसंद: ', 'Prefers: ') . $h($r['preferred_location']) : '' ?></div>
                            <?php if (!$isHospital): ?>
                                <div class="tl-meta">
                                    <?php if (!empty($d['min_pay_per_day'])): ?>💰 <?= $t('न्यूनतम', 'Min') ?> <?= $money($d['min_pay_per_day']) ?>/<?= $t('दिन', 'day') ?> · <?php endif; ?>
                                    <?php if (!empty($d['hours_per_day'])): ?>⏱ <?= (int)$d['hours_per_day'] ?> <?= $t('घंटे/दिन', 'hrs/day') ?><?php endif; ?>
                                </div>
                                <?php if (!empty($d['timing']) || !empty($d['available_days'])): ?><div class="tl-meta">🕘 <?= $h($d['timing'] ?? '') ?><?= !empty($d['available_days']) ? ' · ' . $h($days($d['available_days'])) : '' ?></div><?php endif; ?>
                                <?php if (!empty($d['available_from'])): ?><div class="tl-meta">📅 <?= $h(date('d M', strtotime((string)$d['available_from']))) ?><?= !empty($d['available_to']) ? ' – ' . $h(date('d M Y', strtotime((string)$d['available_to']))) : '' ?></div><?php endif; ?>
                            <?php else: ?>
                                <?php if (!empty($d['expected_salary'])): ?><div class="tl-meta">💰 <?= $t('अपेक्षित', 'Expects') ?> <?= $money($d['expected_salary']) ?>/<?= $t('माह', 'month') ?></div><?php endif; ?>
                            <?php endif; ?>
                            <?php $brief = (string)($d['about_me'] ?? $d['known_skills'] ?? ''); if ($brief !== ''): ?>
                                <div class="tl-brief"><?= $h(mb_strimwidth($brief, 0, $unlocked ? 400 : 120, '…')) ?></div>
                            <?php endif; ?>
                            <?php if ($unlocked): ?>
                                <div class="tl-contact">
                                    <a class="tl-call" href="tel:+91<?= $h($r['mobile']) ?>">📞 <?= $h($r['mobile']) ?></a>
                                    <a class="tl-wa" href="https://wa.me/91<?= $h($r['whatsapp'] ?: $r['mobile']) ?>" target="_blank" rel="noopener">WhatsApp</a>
                                    <?php if (!empty($r['email'])): ?><a class="tl-mail" href="mailto:<?= $h($r['email']) ?>">✉ <?= $h($r['email']) ?></a><?php endif; ?>
                                    <?php if (!empty($r['resume_path'])): ?><a class="tl-cv" href="/talent/resume/<?= (int)$r['id'] ?>">📄 <?= $t('रिज़्यूमे', 'Resume') ?></a><?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="tl-locked">🔒 <?= $h(ContactPass::maskMobile((string)$r['mobile'])) ?><?= !empty($r['resume_path']) ? ' · 📄 ' . $t('रिज़्यूमे उपलब्ध', 'Resume available') : '' ?> · <a href="<?= $h($formUrl) ?>"><?= $t('पूरी जानकारी देखें', 'See full details') ?></a></div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div style="margin-top:22px;font-size:.85rem;color:#4b5563;background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px 14px">
                <?= $tp(FormRegistry::platformDisclaimer()) ?>
                <?= $t(' काम देने से पहले पहचान और दस्तावेज़ ज़रूर जाँचें।', ' Always check identity and documents before hiring.') ?>
            </div>
        </div>
    </section>
</div>
