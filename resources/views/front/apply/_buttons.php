<?php
/**
 * "Jobsence – भारत का Job Portal" headline + big apply buttons.
 * Used on the homepage (above the existing hero) and on /apply.
 * $sdButtonsHeadingTag: 'h1' on /apply, 'h2' on the homepage (which has its own h1).
 */
use App\Helpers\Lang;
use App\Services\Registration\FormRegistry;

require __DIR__ . '/_partials.php';
require dirname(__DIR__, 2) . '/include/_young_india.php';

$sdButtonsHeadingTag = $sdButtonsHeadingTag ?? 'h2';
$sdForms = FormRegistry::all();
$sdCandidateOrder = ['skill-development', 'internship', 'full-time-job', 'part-time-job', 'work-from-home', 'healthcare-jobs', 'restaurant-chef-jobs', 'senior-citizen-jobs', 'honorary-learner', 'international-job'];
$sdProviders = [
    ['/apply/skill-provider', '👩‍🏫', ['स्किल मेंटर / ग्रुप मेंटर / संस्थान – मुफ़्त रजिस्टर करें', 'Skill Mentors / Group Mentors / Institutes – Register Free']],
    ['/apply/internship-provider', '🏢', $sdForms['internship-provider']['button']],
    ['/apply/job-provider', '🏭', $sdForms['job-provider']['button']],
    ['/register-employer?offering=jobs', '🏭', ['नौकरी देने वाली कंपनियाँ – यहाँ रजिस्टर करें', 'For Companies offering Jobs – Register Here']],
    ['/apply/hospital-hiring', '🏥', $sdForms['hospital-hiring']['button']],
    ['/apply/hire-part-time', '🧑‍💼', $sdForms['hire-part-time']['button']],
    ['/apply/senior-citizen-hiring', '🤝', $sdForms['senior-citizen-hiring']['button']],
    ['/apply/honorary-mentor', '🎗️', $sdForms['honorary-mentor']['button']],
    ['/apply/ngo-registration', '🤝', $sdForms['ngo-registration']['button']],
];
?>
<section class="sd sd-section" style="background:linear-gradient(135deg,#fff1ed 0%,#fff 65%);border-bottom:1px solid #e5e7eb">
    <div class="sd-wrap">
        <div style="display:flex;justify-content:flex-end;margin-bottom:10px"><?php $sdLangSwitcher(); ?></div>
        <div style="text-align:center;margin-bottom:22px">
            <<?= $sdButtonsHeadingTag ?> style="font-size:clamp(1.7rem,4.5vw,2.7rem);font-weight:900;margin:0 0 6px">Jobsence – <?= Lang::t('भारत का Job Portal', 'India’s Job Portal') ?></<?= $sdButtonsHeadingTag ?>>
            <p style="font-size:1.15rem;font-weight:800;color:#f05537;margin:0 0 6px">भारत को कुशल बनाने की Jobsence पहल</p>
            <div style="margin:14px 0"><?php $youngIndiaBanner(); ?></div>
            <?php $clStyle = 'text-align:center;margin:0 0 10px'; require dirname(__DIR__, 2) . '/include/_country_links.php'; ?>
            <nav aria-label="All categories" style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin:0 0 12px">
                <?php foreach ([
                    ['/categories/skills', '🎓', ['सभी स्किल', 'All skills']],
                    ['/categories/internships', '🧑‍💻', ['सभी इंटर्नशिप डोमेन', 'All internship domains']],
                    ['/categories/jobs', '💼', ['सभी जॉब कैटेगरी', 'All job categories']],
                    ['/categories/near-me', '📍', ['सभी Near Me सेवाएँ', 'All Near Me services']],
                ] as [$cu, $ci, $cl]): ?>
                    <a href="<?= $h($cu) ?>" style="padding:8px 14px;border-radius:999px;background:#fff;border:2px solid #0b3d91;color:#0b3d91;font-weight:800;text-decoration:none;font-size:.9rem"><?= $ci ?> <?= Lang::t($cl[0], $cl[1]) ?></a>
                <?php endforeach; ?>
            </nav>
            <p style="margin:0;color:#4b5563"><?= Lang::t('कौशल विकास → इंटर्नशिप → नौकरी। नौकरी ढूँढने वालों का रजिस्ट्रेशन मुफ़्त है; नियोक्ता / सेवा देने वाले अपने प्लान का शुल्क देते हैं।', 'Skill Development → Internship → Job. Registration is free for job seekers; employers / providers pay for their plans.') ?></p>
        </div>

        <style>
            .sd-near { display: grid; grid-template-columns: 1.3fr 1fr; gap: 18px; align-items: center; background: linear-gradient(120deg, #065f46 0%, #059669 60%, #10b981 100%); color: #fff; border-radius: 22px; padding: 22px 24px; margin: 0 0 24px; box-shadow: 0 14px 34px rgba(5, 150, 105, .25); }
            .sd-near h2 { color: #fff; font-size: clamp(1.4rem, 3.6vw, 2rem); font-weight: 900; margin: 0 0 6px; }
            .sd-near p { margin: 0 0 10px; color: #d1fae5; }
            .sd-near .chips { display: flex; flex-wrap: wrap; gap: 6px; }
            .sd-near .chips a { background: rgba(255, 255, 255, .14); color: #fff; border: 1px solid rgba(255, 255, 255, .35); border-radius: 999px; padding: 4px 11px; font-size: .85rem; font-weight: 700; text-decoration: none; }
            .sd-near .chips a:hover { background: #fff; color: #065f46; }
            .sd-near form { display: flex; gap: 8px; background: #fff; border-radius: 14px; padding: 6px; }
            .sd-near input { flex: 1; min-width: 0; border: 0; padding: 10px; font: inherit; border-radius: 10px; }
            .sd-near button { border: 0; background: #f05537; color: #fff; font-weight: 900; border-radius: 10px; padding: 10px 16px; cursor: pointer; }
            .sd-near .act { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
            .sd-near .act a { color: #fff; font-weight: 800; font-size: .9rem; text-decoration: underline; }
            .sd-near .usp { display: inline-block; background: #fbbf24; color: #78350f; font-weight: 900; font-size: .75rem; border-radius: 999px; padding: 3px 10px; margin-bottom: 8px; letter-spacing: .3px; }
            @media (max-width: 800px) { .sd-near { grid-template-columns: 1fr; } }
        </style>
        <div class="sd-near" role="region" aria-label="Near Me services">
            <div>
                <span class="usp">★ <?= Lang::t('सिर्फ़ Jobsence पर', 'ONLY ON JOBSENCE') ?></span>
                <h2>📍 <?= Lang::t('Near Me – पास में सेवा और काम', 'Near Me – services & work near you') ?></h2>
                <p><?= Lang::t('पूरे भारत में, पिन कोड से: सत्यापित प्लंबर, इलेक्ट्रीशियन, कारपेंटर, वेल्डर, हेयर ड्रेसर, सैलून – फोटो, समय और स्टार रेटिंग के साथ।', 'All over India, by PIN code: verified plumbers, electricians, carpenters, welders, hair dressers, salons – with photo, timings and star ratings.') ?></p>
                <div class="chips">
                    <?php foreach (['Plumber', 'Electrician', 'Carpenter', 'Welder', 'Hair Dresser', 'Salon', 'AC Mechanic', 'Driver on Call'] as $nm): ?>
                        <a href="/near-me?q=<?= rawurlencode($nm) ?>"><?= htmlspecialchars($nm, ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div>
                <form action="/near-me" method="GET" role="search">
                    <input name="q" placeholder="Plumber, Salon…" aria-label="Service">
                    <input name="where" placeholder="<?= htmlspecialchars(Lang::plain('पिन कोड या शहर', 'PIN or city'), ENT_QUOTES, 'UTF-8') ?>" aria-label="PIN code or city" autocomplete="postal-code" style="max-width:150px">
                    <button type="submit"><?= Lang::t('खोजें', 'Search') ?></button>
                </form>
                <div class="act">
                    <a href="/apply/near-me-provider">🛠️ <?= Lang::t('दुकान / सेवा देते हैं? ₹590 में 6 महीने', 'Shop or service? Enrol – ₹590 for 6 months') ?></a>
                    <a href="/apply/near-me-job-giver">🏪 <?= Lang::t('दुकान पर काम देना है? ₹590 / 6 महीने', 'Hiring for your shop? ₹590 / 6 months') ?></a>
                    <a href="/apply/near-me-job-seeker">🧑‍🔧 <?= Lang::t('घर के पास काम चाहिए? ₹295 / 3 महीने', 'Work near home? ₹295 / 3 months') ?></a>
                    <a href="/apply/near-me-seeker">🔎 <?= Lang::t('सेवा चाहिए? ₹295 में 3 महीने', 'Need a service? ₹295 for 3 months') ?></a>
                </div>
            </div>
        </div>

        <div class="sd-apply-grid">
            <?php foreach ($sdCandidateOrder as $slug): $f = $sdForms[$slug]; ?>
                <a class="sd-apply" href="/apply/<?= $h($slug) ?>">
                    <span class="ico" aria-hidden="true"><?= $f['icon'] ?></span>
                    <?= Lang::b($f['button'][0], $f['button'][1]) ?>
                    <span class="go" aria-hidden="true">→</span>
                </a>
            <?php endforeach; ?>
        </div>

        <h3 style="margin:26px 0 12px;text-align:center"><?= Lang::t('प्रोवाइडर / कंपनियों के लिए', 'For Providers / Companies') ?></h3>
        <div class="sd-apply-grid">
            <?php foreach ($sdProviders as [$url, $icon, $label]): ?>
                <a class="sd-apply provider" href="<?= $h($url) ?>">
                    <span class="ico" aria-hidden="true"><?= $icon ?></span>
                    <?= Lang::b($label[0], $label[1]) ?>
                    <span class="go" aria-hidden="true">→</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
