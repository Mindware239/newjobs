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
                    <div class="sd-cta-row">
                        <?php if ($p['phone']): ?><a class="sd-btn" href="tel:+91<?= $h($p['phone']) ?>">📞 <?= $h($p['phone']) ?></a>
                            <a class="sd-btn ghost" href="https://wa.me/91<?= $h($p['phone']) ?>" target="_blank" rel="noopener nofollow">WhatsApp</a><?php endif; ?>
                        <?php if ($p['email']): ?><a class="sd-btn ghost" href="mailto:<?= $h($p['email']) ?>?subject=<?= rawurlencode('Application: ' . $p['title']) ?>">✉️ <?= $h($p['email']) ?></a><?php endif; ?>
                    </div>
                <?php endif; ?>
            </article>
            <div class="ij-note"><?= $t(
                'यह नौकरी कंपनी ने ख़ुद मुफ़्त पोस्ट की है – Jobsence ने इसकी पुष्टि नहीं की है। नौकरी के बदले कभी कोई पैसा न दें; गड़बड़ लगे तो gm@jobsence.com पर बताएँ।',
                'This job was posted free by the company itself – Jobsence has not verified it. Never pay anyone for a job; report anything suspicious to gm@jobsence.com.'
            ) ?> <?= $t('पोस्ट', 'Post') ?> #<?= (int)$p['id'] ?></div>
            <?php if ($mine && $live): ?>
                <form method="POST" action="/free-job/<?= (int)$p['id'] ?>/close" style="margin-top:10px"><input type="hidden" name="_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                    <button class="sd-btn ghost" type="submit"><?= $tb('यह नौकरी बंद करें', 'Close this job') ?></button></form>
            <?php endif; ?>
        </div>
    </section>
</div>
