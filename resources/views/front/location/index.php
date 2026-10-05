<?php
require dirname(__DIR__) . '/apply/_partials.php';
use App\Controllers\Front\LocationPagesController as L;

$name = $place['name'];
$inState = $place['level'] === 'district' || $place['level'] === 'town';
$abroad = $place['level'] === 'abroad';
$stateSlug = $inState ? L::slug($place['state']) : null;
$points = [
    'internship' => [
        ['पेड इंटर्नशिप (₹8,000 से ₹5,00,000 प्रति माह स्टाइपेंड) और अनपेड – दोनों।', 'Paid internships (₹8,000 to ₹5,00,000 a month stipend) and unpaid – both.'],
        ["{$name} में ऑफिस से या वर्क फ्रॉम होम – 1, 2, 3 या 6 महीने।", "On-site in {$name} or work from home – 1, 2, 3 or 6 months."],
        ['3000+ डोमेन: IT, मार्केटिंग, अकाउंट्स, HR, डिज़ाइन, हेल्थकेयर और अधिक।', '3000+ domains: IT, marketing, accounts, HR, design, healthcare and more.'],
    ],
    'skill' => [
        ['3000+ स्किल में से अधिकतम 5 चुनें – आपको केवल उन्हीं में प्रशिक्षण मिलेगा।', 'Choose up to 5 of 3000+ skills – you are trained only in those.'],
        ["ऑनलाइन वीडियो क्लास ({$name} सहित पूरे भारत में) या ऑफलाइन (दिल्ली-NCR)।", "Online video classes (all of India, including {$name}) or offline (Delhi-NCR)."],
        ['मेंटर उपलब्ध होने पर ईमेल से सूचना; रजिस्ट्रेशन 3 महीने के लिए।', 'Email as soon as a mentor accepts; registration is valid for 3 months.'],
    ],
    'interncos' => [
        ["{$name} में इंटर्नशिप देने वाली सत्यापित कंपनियाँ, स्टार्टअप और संस्थान।", "Verified companies, startups and institutes offering internships in {$name}."],
        ['प्रदाता मुफ़्त रजिस्टर करते हैं; ₹155 प्लान से रोज़ 3 प्रोफ़ाइल, 10 दिन, अधिकतम 20।', 'Providers register free; the ₹155 plan opens 3 profiles a day for 10 days, up to 20.'],
        ['फ़ोन और ईमेल केवल त्रिपक्षीय समझौते के बाद।', 'Phone and email only after the tripartite agreement.'],
    ],
    'hiring' => [
        ["{$name} में भर्ती करने वाली सत्यापित कंपनियाँ, दुकानें, फ़ैक्टरियाँ और ऑफिस।", "Verified companies, shops, factories and offices hiring in {$name}."],
        ['कंपनियाँ मुफ़्त रजिस्टर करती हैं; ₹155 प्लान से रोज़ 3 उम्मीदवारों की पूरी प्रोफ़ाइल और रिज़्यूमे।', 'Companies register free; the ₹155 plan opens 3 full candidate profiles with resume a day.'],
        ['फ़ोन और ईमेल केवल त्रिपक्षीय समझौते के बाद।', 'Phone and email only after the tripartite agreement.'],
    ],
    'services' => [
        ["{$name} में प्लंबर, इलेक्ट्रीशियन, कारपेंटर, AC मैकेनिक, सैलून और 70+ सेवाएँ।", "Plumbers, electricians, carpenters, AC mechanics, salons and 70+ services in {$name}."],
        ['अपनी लोकेशन से या अपना इलाका / पिन कोड लिखकर खोजें।', 'Search by your current location, or type your area / PIN code.'],
        ['हर प्रदाता फोटो और लाइव सेल्फ़ी से सत्यापित; समझौते के बाद नंबर।', 'Every provider is photo and live-selfie verified; number after the agreement.'],
    ],
    'mentors' => [
        ["{$name} में सत्यापित मेंटर, ग्रुप मेंटर और ट्रेनिंग संस्थान – 3000+ स्किल।", "Verified mentors, group mentors and training institutes in {$name} – 3000+ skills."],
        ['मेंटर और संस्थान मुफ़्त रजिस्टर करते हैं; सीखने वाले ₹155 का फॉर्म भरते हैं।', 'Mentors and institutes register free; learners fill the ₹155 form.'],
        ['फ़ोन और ईमेल केवल त्रिपक्षीय समझौते (उम्मीदवार + मेंटर + Jobsence) के बाद।', 'Phone and email only after the tripartite agreement (candidate + mentor + Jobsence).'],
    ],
    'jobs' => [
        ["{$name} में फुल-टाइम, पार्ट-टाइम, वर्क फ्रॉम होम और एक दिन के काम।", "Full-time, part-time, work from home and one-time work in {$name}."],
        ['सरकारी, PSU, रेलवे, पुलिस और बड़ी कंपनियों की नौकरियाँ एक जगह।', 'Govt, PSU, railway, police and top company jobs in one place.'],
        ['30,000+ जॉब कैटेगरी – एक बार रजिस्टर करें, मिलान होने पर कॉल / ईमेल।', '30,000+ job categories – register once, get a call / email on a match.'],
    ],
][$kind];
?>
<style>
.sd .lc-hero { background: linear-gradient(135deg, #fff1ed 0%, #fff 70%); border-bottom: 1px solid #e5e7eb; }
.sd .lc-hero h1 { font-size: clamp(1.6rem, 4.2vw, 2.4rem); font-weight: 900; margin: 0 0 6px; line-height: 1.2; }
.sd .lc-crumbs { font-size: .85rem; color: #6b7280; margin-bottom: 8px; }
.sd .lc-crumbs a { color: #6b7280; }
.sd .lc-grid { display: grid; grid-template-columns: 1.4fr 1fr; gap: 18px; align-items: start; margin-top: 16px; }
@media (max-width: 860px) { .sd .lc-grid { grid-template-columns: 1fr; } }
.sd .lc-points { margin: 0; padding-left: 18px; }
.sd .lc-points li { margin: 4px 0; }
.sd .lc-cta { background: #fff; border: 2px solid #f05537; border-radius: 16px; padding: 16px; }
.sd .lc-cta .price { font-size: 1.6rem; font-weight: 900; }
.sd .lc-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px; }
.sd .lc-job { display: block; background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 12px 14px; text-decoration: none; color: #111827; min-width: 0; }
.sd .lc-job:hover { border-color: #f05537; }
.sd .lc-job b { display: block; overflow-wrap: anywhere; }
.sd .lc-job span { color: #4b5563; font-size: .86rem; }
.sd .lc-tag { display: inline-block; margin-top: 4px; padding: 2px 8px; border-radius: 999px; background: #ecfdf5; color: #047857; font-size: .72rem; font-weight: 800; }
.sd .lc-chips { display: flex; flex-wrap: wrap; gap: 6px; }
.sd .lc-chips a { padding: 5px 11px; border-radius: 999px; background: #fff; border: 1px solid #e5e7eb; color: #374151; font-size: .84rem; text-decoration: none; }
.sd .lc-chips a:hover { border-color: #f05537; color: #f05537; }
.sd .lc-faq details { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 10px 14px; margin-bottom: 8px; }
.sd .lc-faq summary { cursor: pointer; font-weight: 800; }
</style>
<div class="sd">
    <section class="sd-section lc-hero">
        <div class="sd-wrap">
            <div style="display:flex;justify-content:flex-end;margin-bottom:10px"><?php $sdLangSwitcher(); ?></div>
            <nav class="lc-crumbs" aria-label="Breadcrumb">
                <a href="/">Jobsence</a> › <a href="/<?= $h($k['path']) ?>india"><?= $h($k['en']) ?> in India</a>
                <?php if ($stateSlug): ?> › <a href="/<?= $h($k['path'] . $stateSlug) ?>"><?= $h($place['state']) ?></a><?php endif; ?>
                <?php if ($place['level'] !== 'country'): ?> › <?= $h($name) ?><?php endif; ?>
            </nav>
            <h1><?= $t("{$full} में {$k['hi']}", "{$k['en']} in {$full}") ?></h1>
            <div class="lc-grid">
                <div>
                    <ul class="lc-points"><?php foreach ($points as $p): ?><li><?= $tp($p) ?></li><?php endforeach; ?></ul>
                </div>
                <div class="lc-cta">
                    <div class="price">₹155 <small style="font-size:.9rem;font-weight:700;color:#6b7280"><?= $t('GST सहित, एक बार', 'incl. GST, one-time') ?></small></div>
                    <p style="margin:4px 0 12px;color:#4b5563"><?= $t("{$name} के लिए अभी रजिस्टर करें – CAPTCHA, OTP और सुरक्षित Razorpay भुगतान।", "Register now for {$name} – CAPTCHA, OTP and secure Razorpay payment.") ?></p>
                    <a class="sd-btn" style="width:100%;text-align:center" href="<?= $h($k['form']) ?>"><?= isset($k['offer']) ? [
                        'mentors' => $tb('मेंटर चाहिए – आवेदन करें', 'Find a mentor – apply'),
                        'interncos' => $tb('इंटर्नशिप चाहिए – आवेदन करें', 'Want an internship – apply'),
                        'hiring' => $tb('नौकरी चाहिए – आवेदन करें', 'Want a job – apply'),
                        'services' => $tb('सेवा चाहिए – Near Me पास', 'Need a service – Near Me pass'),
                    ][$kind] : $tb("{$k['hi']} के लिए आवेदन करें", "Apply for {$k['en']}") ?></a>
                    <?php if (isset($k['offer'])): ?><a class="sd-btn ghost" style="width:100%;text-align:center;margin-top:8px" href="<?= $h($k['offer']) ?>"><?= $kind === 'services' ? $tb('सेवा देते हैं? रजिस्टर करें', 'Provide a service? Enrol') : $tb('मुफ़्त रजिस्टर करें', 'Register free') ?></a><?php endif; ?>
                    <?php if ($abroad): ?><p style="margin:10px 0 0;font-size:.82rem;color:#7f1d1d;font-weight:600">⚠️ <?= $tp(\App\Services\Registration\FormRegistry::abroadDisclaimer()) ?></p><?php endif; ?>
                    <?php if ($abroad): ?><p style="margin:10px 0 0;font-size:.85rem;color:#6b7280"><?= $t('रजिस्ट्रेशन मुफ़्त · ' . $name . ' अनलॉक: ₹1,180 (₹1,000 + GST) या USD 10, एक बार · पासपोर्ट ज़रूरी', 'Registration free · unlock ' . $name . ': ₹1,180 (₹1,000 + GST) or USD 10, one-time · passport mandatory') ?></p><?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="sd-section" style="padding-top:24px">
        <div class="sd-wrap">
            <h2 style="font-size:1.3rem"><?= $t("{$name} में अभी के अवसर", "Current openings in {$name}") ?></h2>
            <?php if (!$openings): ?>
                <div class="sd-card"><?= $t("{$name} के नए अवसर रोज़ जोड़े जाते हैं। रजिस्टर करें – मिलान होते ही हम आपसे संपर्क करेंगे।", "New openings for {$name} are added every day. Register and we will contact you as soon as there is a match.") ?></div>
            <?php else: ?>
                <div class="lc-list">
                    <?php foreach ($openings as $o): ?>
                        <a class="lc-job" href="<?= $h($o['url']) ?>"><b><?= $h($o['title']) ?></b><span><?= $h($o['org']) ?></span><br><span class="lc-tag"><?= $h($o['tag']) ?></span></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($abroad && !empty($abroadSlug)): ?>
                <h2 style="font-size:1.1rem;margin-top:26px"><?= $t("{$name} में नौकरियाँ – 3000+ प्रकार, हर कैटेगरी", "Jobs in {$name} – 3000+ job types in every category") ?></h2>
                <div class="lc-chips">
                    <?php foreach (\App\Services\Registration\SkillTaxonomy::tree() as $sec): ?>
                        <a href="/jobs-abroad/<?= $h($abroadSlug . '/' . $sec['slug']) ?>"><?= $h($sec['short']) ?> (<?= number_format($sec['count']) ?>)</a>
                    <?php endforeach; ?>
                    <a href="/jobs-abroad/<?= $h($abroadSlug) ?>" style="font-weight:800">🌍 <?= $t('सभी देखें', 'See all') ?></a>
                </div>
            <?php endif; ?>
            <h2 style="font-size:1.1rem;margin-top:26px"><?= $t("{$name} में और देखें", "More in {$name}") ?></h2>
            <div class="lc-chips">
                <?php foreach (L::KINDS as $kk => $other): if ($kk === $kind) continue; ?>
                    <a href="/<?= $h($other['path'] . $slug) ?>"><?= $h($other['en'] . ' in ' . $name) ?></a>
                <?php endforeach; ?>
                <?php if (!$abroad): ?><a href="/near-me<?= $place['level'] === 'country' ? '' : '?' . $h(http_build_query(array_filter(['state' => $place['state'], 'city' => $inState ? $name : '']))) ?>">📍 <?= $h('Services near me in ' . $name) ?></a><?php endif; ?>
            </div>

            <?php if ($nearby): ?>
                <h2 style="font-size:1.1rem;margin-top:22px"><?= $place['level'] === 'country' ? $t("राज्य के अनुसार {$k['hi']}", "{$k['en']} by state") : ($abroad ? $t('दूसरे देश', 'Other countries') : $t("{$place['state']} के अन्य शहर", "Other places in {$place['state']}")) ?></h2>
                <div class="lc-chips">
                    <?php foreach ($nearby as $s => $n): ?><a href="/<?= $h($k['path'] . $s) ?>"><?= $h($k['en'] . ' in ' . $n) ?></a><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="lc-faq" style="margin-top:26px">
                <h2 style="font-size:1.1rem"><?= $t('अक्सर पूछे जाने वाले सवाल', 'Frequently asked questions') ?></h2>
                <details><summary><?= $t('रजिस्ट्रेशन शुल्क कितना है?', 'What is the registration fee?') ?></summary><p style="margin:6px 0 0"><?= $t('₹155 (GST सहित), एक बार, वापसी योग्य नहीं।', '₹155 (including GST), one-time, non-refundable.') ?></p></details>
                <details><summary><?= $t('क्या नौकरी या प्लेसमेंट की गारंटी है?', 'Is a job or placement guaranteed?') ?></summary><p style="margin:6px 0 0"><?= $t('नहीं। Jobsence आपका प्रोफ़ाइल उपयुक्त कंपनियों और मेंटर्स तक पहुँचाने की कोशिश करता है।', 'No. Jobsence tries to share your profile with suitable companies and mentors.') ?></p></details>
                <details><summary><?= $t('क्या यह सरकारी योजना है?', 'Is this a Government scheme?') ?></summary><p style="margin:6px 0 0"><?= $t('नहीं। यह भारत को कुशल बनाने की Jobsence पहल है – भारत सरकार की योजना नहीं।', 'No. This is Jobsence’s initiative to make India skilled – not a Government of India scheme.') ?></p></details>
            </div>
        </div>
    </section>
</div>
