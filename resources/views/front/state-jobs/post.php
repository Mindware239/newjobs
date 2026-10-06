<?php
require dirname(__DIR__) . '/apply/_partials.php';
require __DIR__ . '/_style.php';
$o = static fn(string $k) => $h($old[$k] ?? '');
$err = static fn(string $k) => isset($errors[$k]) ? '<span class="err">' . $tp($errors[$k]) . '</span>' : '';
$csrf = '<input type="hidden" name="_token" value="' . $h($_SESSION['csrf_token'] ?? '') . '">';
?>
<div class="sd">
    <section class="sd-section sj-hero">
        <div class="sd-wrap" style="max-width:860px">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px">
                <a href="/jobs-by-state" class="ij-more">← <?= $tb('राज्य अनुसार नौकरियाँ', 'Jobs by state') ?></a>
                <?php $sdLangSwitcher(); ?>
            </div>
            <h1>➕ <?= $t('मुफ़्त नौकरी पोस्ट करें', 'Post a job – FREE') ?></h1>
            <p style="margin:0;color:#374151"><?= $t(
                'प्राइवेट लिमिटेड कंपनी, प्रोप्राइटरशिप, पार्टनरशिप, LLP या दुकान – अपनी ज़रूरत पूरी तरह मुफ़्त पोस्ट करें। नौकरी ' . \App\Models\FreeJobPost::LIVE_DAYS . ' दिन तक राज्य और शहर की सूची में दिखेगी।',
                'Private limited company, proprietorship, partnership, LLP or shop – post your requirement completely free. The job shows in the state & city list for ' . \App\Models\FreeJobPost::LIVE_DAYS . ' days.'
            ) ?></p>
        </div>
    </section>
    <section class="sd-section" style="padding-top:10px">
        <div class="sd-wrap" style="max-width:860px">
            <?php if (!$canPost): ?>
                <div class="sd-alert err" role="alert">
                    <b><?= $t('नौकरी पोस्ट करने के लिए एम्प्लॉयर (कंपनी) खाता चाहिए।', 'You need an employer (company) account to post jobs.') ?></b>
                    <div style="margin-top:6px"><?= $t('आप जॉब सीकर खाते से लॉगिन हैं। लॉग आउट करके एम्प्लॉयर खाते से लॉगिन करें, या मुफ़्त एम्प्लॉयर खाता बनाएँ।', 'You are logged in with a job seeker account. Log out and log in with an employer account, or create a free employer account.') ?></div>
                    <p style="margin:10px 0 0"><a class="sd-btn" href="/register-employer"><?= $tb('मुफ़्त एम्प्लॉयर खाता बनाएँ', 'Create a free employer account') ?></a> <a class="sd-btn ghost" href="/logout"><?= $tb('लॉग आउट', 'Logout') ?></a></p>
                </div>
            <?php else: ?>
                <?php if (isset($errors['general'])): ?><div class="sd-alert err" role="alert"><?= $tp($errors['general']) ?></div><?php endif; ?>
                <?php if ($errors && !isset($errors['general'])): ?><div class="sd-alert err" role="alert"><?= $t('कृपया लाल रंग में दिखाई गई गलतियाँ ठीक करें।', 'Please correct the errors shown in red.') ?></div><?php endif; ?>
                <form method="POST" action="/post-job-free" class="sd-card sj-form" novalidate>
                    <?= $csrf ?>
                    <h2 class="full" style="font-size:1.1rem;margin:0">🏢 <?= $t('कंपनी', 'Company') ?></h2>
                    <label><?= $t('कंपनी / फ़र्म का नाम', 'Company / firm name') ?> *<input name="company_name" value="<?= $o('company_name') ?>" maxlength="190" required><?= $err('company_name') ?></label>
                    <label><?= $t('कंपनी का प्रकार', 'Type of company') ?> *
                        <select name="company_type" required><option value=""><?= $h('— Select —') ?></option>
                            <?php foreach ($companyTypes as $k => $l): ?><option value="<?= $h($k) ?>" <?= ($old['company_type'] ?? '') === $k ? 'selected' : '' ?>><?= $h($l[1]) ?> / <?= $h($l[0]) ?></option><?php endforeach; ?>
                        </select><?= $err('company_type') ?></label>
                    <label><?= $t('संपर्क व्यक्ति', 'Contact person') ?> *<input name="contact_person" value="<?= $o('contact_person') ?>" maxlength="120" required><?= $err('contact_person') ?></label>
                    <label><?= $t('मोबाइल (आवेदकों के लिए)', 'Mobile (for applicants)') ?><input name="phone" value="<?= $o('phone') ?>" inputmode="numeric" maxlength="14" placeholder="98XXXXXXXX"><?= $err('phone') ?></label>
                    <label class="full"><?= $t('ईमेल (आवेदकों के लिए)', 'Email (for applicants)') ?><input type="email" name="email" value="<?= $o('email') ?>" maxlength="190"><?= $err('email') ?></label>

                    <h2 class="full" style="font-size:1.1rem;margin:8px 0 0">💼 <?= $t('नौकरी', 'Job') ?></h2>
                    <label class="full"><?= $t('पद का नाम', 'Job title') ?> *<input name="title" value="<?= $o('title') ?>" maxlength="190" placeholder="Driver, Accountant, Sales Executive…" required><?= $err('title') ?></label>
                    <label><?= $t('नौकरी का प्रकार', 'Job type') ?> *
                        <select name="job_type"><?php foreach ($jobTypes as $k => $l): ?><option value="<?= $h($k) ?>" <?= ($old['job_type'] ?? 'full_time') === $k ? 'selected' : '' ?>><?= $h($l[1]) ?> / <?= $h($l[0]) ?></option><?php endforeach; ?></select><?= $err('job_type') ?></label>
                    <label><?= $t('पदों की संख्या', 'Vacancies') ?><input type="number" name="vacancies" value="<?= $o('vacancies') ?>" min="1" max="100000"></label>
                    <label><?= $t('राज्य', 'State') ?> *
                        <select name="state" id="sj-state" required><option value=""><?= $h('— Select —') ?></option>
                            <?php foreach (array_keys($states) as $s): ?><option value="<?= $h($s) ?>" <?= ($old['state'] ?? '') === $s ? 'selected' : '' ?>><?= $h($s) ?></option><?php endforeach; ?>
                        </select><?= $err('state') ?></label>
                    <label><?= $t('शहर / ज़िला', 'City / district') ?> *<input name="city" id="sj-city" list="sj-cities" value="<?= $o('city') ?>" maxlength="120" required autocomplete="off"><datalist id="sj-cities"></datalist><?= $err('city') ?></label>
                    <label><?= $t('वेतन', 'Salary') ?><input name="salary" value="<?= $o('salary') ?>" maxlength="120" placeholder="₹15,000 – ₹20,000 / month"></label>
                    <label><?= $t('अनुभव', 'Experience') ?><input name="experience" value="<?= $o('experience') ?>" maxlength="80" placeholder="Fresher / 2+ years"></label>
                    <label class="full"><?= $t('योग्यता', 'Qualification') ?><input name="qualification" value="<?= $o('qualification') ?>" maxlength="190" placeholder="10th pass, Graduate, ITI…"></label>
                    <label class="full"><?= $t('काम का विवरण', 'Job description') ?> *<textarea name="description" rows="6" maxlength="5000" required><?= $o('description') ?></textarea><?= $err('description') ?></label>
                    <label class="full"><?= $t('आवेदन कैसे करें', 'How to apply') ?><input name="how_to_apply" value="<?= $o('how_to_apply') ?>" maxlength="500" placeholder="Call / WhatsApp between 10am–6pm, or walk in with resume at …"></label>
                    <p class="full" style="margin:0;font-size:.82rem;color:#4b5563"><?= $t(
                        'नौकरी के बदले उम्मीदवारों से कोई शुल्क या डिपॉज़िट माँगना मना है – ऐसी पोस्ट हटा दी जाएँगी। पोस्ट करके आप पुष्टि करते हैं कि जानकारी सही है।',
                        'Asking candidates for any fee or deposit is not allowed – such posts are removed. By posting you confirm the details are true.'
                    ) ?></p>
                    <div class="full"><button class="sd-btn" type="submit" style="font-size:1.05rem"><?= $tb('मुफ़्त पोस्ट करें', 'Post free') ?> ✓</button></div>
                </form>
                <script>
                (function () {
                    var cities = <?= json_encode($states, JSON_UNESCAPED_UNICODE) ?>, st = document.getElementById('sj-state'), dl = document.getElementById('sj-cities');
                    function fill() { dl.innerHTML = ''; (cities[st.value] || []).forEach(function (c) { var o = document.createElement('option'); o.value = c; dl.appendChild(o); }); }
                    st.addEventListener('change', fill); fill();
                })();
                </script>
            <?php endif; ?>

            <?php if ($myPosts): ?>
                <h2 style="font-size:1.15rem;margin:26px 0 8px"><?= $t('आपकी पोस्ट', 'Your posts') ?></h2>
                <div class="sj-list">
                    <?php foreach ($myPosts as $p): $live = $p['status'] === 'live' && strtotime((string)$p['expires_at']) > time(); ?>
                        <div class="sj-job">
                            <div><a class="t" href="<?= $h(\App\Models\FreeJobPost::url($p)) ?>"><?= $h($p['title']) ?></a> – <?= $h($p['city']) ?>, <?= $h($p['state']) ?></div>
                            <time><?= $h(date('d M Y, h:i A', strtotime((string)$p['published_at']))) ?></time>
                            <div class="m"><?= $live ? '🟢 ' . $t('लाइव', 'Live') . ' · ' . $t('तक', 'until') . ' ' . $h(date('d M Y', strtotime((string)$p['expires_at']))) : ($p['status'] === 'hidden' ? '⛔ ' . $t('एडमिन ने छिपाया', 'Hidden by admin') : '⚪ ' . $t('बंद', 'Closed')) ?> · <?= (int)$p['views'] ?> <?= $t('बार देखा गया', 'views') ?>
                                <?php if ($live): ?><form method="POST" action="/free-job/<?= (int)$p['id'] ?>/close" style="display:inline"><?= $csrf ?><button class="sd-btn ghost" type="submit" style="padding:2px 10px;font-size:.8rem;margin-left:8px"><?= $tb('बंद करें', 'Close') ?></button></form><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
