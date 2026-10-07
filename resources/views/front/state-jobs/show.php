<?php
require dirname(__DIR__) . '/apply/_partials.php';
require __DIR__ . '/_style.php';
use App\Models\FreeJobPost;
use App\Services\JobBoard\StateJobBoard;
$p = $post;
$type = FreeJobPost::JOB_TYPES[$p['job_type']] ?? ['', ucfirst((string)$p['job_type'])];
$ctype = FreeJobPost::COMPANY_TYPES[$p['company_type']] ?? ['', ''];
$row = static function (array $label, $value) use ($t, $h): void {
    if ($value === null || $value === '') {
        return;
    } ?>
    <tr><td><?= $t($label[0], $label[1]) ?></td><td><?= $h($value) ?></td></tr>
<?php };
$posting = [
    '@context' => 'https://schema.org', '@type' => 'JobPosting',
    'title' => $p['title'], 'description' => nl2br(htmlspecialchars((string)$p['description'], ENT_QUOTES, 'UTF-8')),
    'datePosted' => date('c', strtotime((string)$p['published_at'])), 'validThrough' => date('c', strtotime((string)$p['expires_at'])),
    'employmentType' => ['full_time' => 'FULL_TIME', 'part_time' => 'PART_TIME', 'contract' => 'CONTRACTOR', 'internship' => 'INTERN', 'apprentice' => 'INTERN', 'wfh' => 'FULL_TIME'][$p['job_type']] ?? 'OTHER',
    'hiringOrganization' => ['@type' => 'Organization', 'name' => $p['company_name']],
    'jobLocation' => ['@type' => 'Place', 'address' => ['@type' => 'PostalAddress', 'addressLocality' => $p['city'], 'addressRegion' => $p['state'], 'addressCountry' => 'IN']],
];
?>
<?php if ($live): ?><script type="application/ld+json"><?= json_encode($posting, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script><?php endif; ?>
<div class="sd">
    <section class="sd-section">
        <div class="sd-wrap" style="max-width:820px">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:12px">
                <a href="/jobs-by-state/<?= $h(StateJobBoard::slug((string)$p['state'])) ?>" class="ij-more">← <?= $h($p['state']) ?> <?= $tb('की नौकरियाँ', 'jobs') ?></a>
                <?php $sdLangSwitcher(); ?>
            </div>
            <?php if ($flash === 'posted'): ?><div class="sd-alert ok" role="status">✅ <?= $t('आपकी नौकरी मुफ़्त पोस्ट हो गई और अब राज्य और शहर की सूची में दिख रही है।', 'Your job is posted free and is now live in the state & city list.') ?></div><?php endif; ?>
            <?php if (!$live): ?><div class="sd-alert err"><?= $t('यह पोस्ट अभी लाइव नहीं है (बंद, छिपाई गई या समय पूरा)।', 'This post is not live (closed, hidden or expired).') ?></div><?php endif; ?>
            <article class="sd-card">
                <span class="sj-badge free"><?= $t('मुफ़्त पोस्ट', 'Free post') ?></span>
                <h1 style="font-size:1.5rem;margin:10px 0 4px"><?= $h($p['title']) ?></h1>
                <div style="font-size:1rem;color:#374151"><b><?= $h($p['company_name']) ?></b><?= $ctype[1] !== '' ? ' · ' . $t($ctype[0], $ctype[1]) : '' ?> · 📍 <?= $h($p['city']) ?>, <?= $h($p['state']) ?></div>
                <p style="margin:6px 0 0;font-size:.9rem;color:#4b5563">🕒 <?= $t('पोस्ट किया गया', 'Posted') ?>: <b><?= $h(date('d M Y, h:i A', strtotime((string)$p['published_at']))) ?></b></p>
                <table class="sd-table" style="margin:16px 0">
                    <?php
                    $row(['नौकरी का प्रकार', 'Job type'], $type[1]);
                    $row(['पद', 'Vacancies'], $p['vacancies'] ? number_format((int)$p['vacancies']) : null);
                    $row(['वेतन', 'Salary'], $p['salary']);
                    $row(['योग्यता', 'Qualification'], $p['qualification']);
                    $row(['अनुभव', 'Experience'], $p['experience']);
                    $row(['संपर्क व्यक्ति', 'Contact person'], $p['contact_person']);
                    ?>
                </table>
                <h2 style="font-size:1.1rem"><?= $t('काम का विवरण', 'Job description') ?></h2>
                <div class="ij-details"><?= nl2br($h($p['description'])) ?></div>
                <?php if ($live): ?>
                    <h2 style="font-size:1.1rem;margin-top:16px"><?= $t('आवेदन कैसे करें', 'How to apply') ?></h2>
                    <?php if ($p['how_to_apply']): ?><p><?= $h($p['how_to_apply']) ?></p><?php endif; ?>
                    <?php // Phone / email are not in the page (scrapers): fetched after a click, for logged-in users. ?>
                    <div class="sd-cta-row" id="fj-contact" data-url="/free-job/<?= (int)$p['id'] ?>/contact" data-title="<?= $h($p['title']) ?>" data-token="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="button" class="sd-btn" id="fj-show">📞 <?= $tb('संपर्क देखें', 'Show contact') ?></button>
                    </div>
                    <p id="fj-msg" style="font-size:.85rem;color:#4b5563;margin:6px 0 0"><?= $mine ? '' : $t('लॉगिन करें – संपर्क ₹185 + GST में एक बार खुलता है, फिर इस नौकरी के लिए हमेशा खुला रहता है।', 'Log in – the contact unlocks once for ₹185 + GST and stays open for this job.') ?></p>
                    <script>
                    (function () {
                        var box = document.getElementById('fj-contact'), btn = document.getElementById('fj-show'), msg = document.getElementById('fj-msg');
                        var meta = document.querySelector('meta[name="csrf-token"]'), token = (meta && meta.content) || box.dataset.token;
                        function link(href, text, ghost) {
                            var a = document.createElement('a'); a.className = 'sd-btn' + (ghost ? ' ghost' : ''); a.href = href; a.textContent = text;
                            if (/^https?:/.test(href)) { a.target = '_blank'; a.rel = 'noopener nofollow'; }
                            return a;
                        }
                        btn.addEventListener('click', function () {
                            btn.disabled = true;
                            fetch(box.dataset.url, { method: 'POST', credentials: 'same-origin',
                                headers: { 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': token },
                                body: '_token=' + encodeURIComponent(token) })
                                .then(function (r) { return r.json(); })
                                .then(function (d) {
                                    if (d.login) { window.location.href = d.login; return; }
                                    if (d.pay) {
                                        msg.textContent = d.error || '';
                                        box.innerHTML = '';
                                        box.appendChild(link(d.pay, '🔓 ₹185 + GST देकर संपर्क देखें · Pay ₹185 + GST to see the contact'));
                                        return;
                                    }
                                    if (!d.success) { msg.textContent = d.error || 'Error'; btn.disabled = false; return; }
                                    box.innerHTML = '';
                                    if (d.phone) { box.appendChild(link('tel:+91' + d.phone, '📞 ' + d.phone)); box.appendChild(link('https://wa.me/91' + d.phone, 'WhatsApp', true)); }
                                    if (d.email) { box.appendChild(link('mailto:' + d.email + '?subject=' + encodeURIComponent('Application: ' + box.dataset.title), '✉️ ' + d.email, true)); }
                                    msg.textContent = '';
                                })
                                .catch(function () { msg.textContent = 'Network error – please retry'; btn.disabled = false; });
                        });
                    })();
                    </script>
                <?php endif; ?>
            </article>
            <div class="ij-note"><?= $t(
                'यह नौकरी कंपनी ने ख़ुद पोस्ट की है – Jobsence ने इसकी पुष्टि नहीं की है। संपर्क देखने का ₹185 + GST शुल्क केवल Jobsence को जाता है; नौकरी के बदले कंपनी या किसी व्यक्ति को कभी पैसा न दें; गड़बड़ लगे तो gm@jobsence.com पर बताएँ।',
                'This job was posted by the company itself – Jobsence has not verified it. The ₹185 + GST contact fee goes only to Jobsence; never pay the company or anyone else for a job, and report anything suspicious to gm@jobsence.com.'
            ) ?> <?= $t('पोस्ट', 'Post') ?> #<?= (int)$p['id'] ?></div>
            <?php if ($mine && $live): ?>
                <form method="POST" action="/free-job/<?= (int)$p['id'] ?>/close" style="margin-top:10px"><input type="hidden" name="_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                    <button class="sd-btn ghost" type="submit"><?= $tb('यह नौकरी बंद करें', 'Close this job') ?></button></form>
            <?php endif; ?>
        </div>
    </section>
</div>
