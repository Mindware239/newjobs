<?php
/** Status page of a Near Me business (nearby job seekers) or a Near Me job seeker (nearby businesses hiring). */
use App\Services\Registration\FormRegistry;
use App\Services\Registration\NearJobMatch;

$njGiver = $reg['type'] === 'nearjobpro';
$njLive = $reg['valid_until'] === null || strtotime((string)$reg['valid_until']) > time();
$njList = $njLive ? ($njGiver ? NearJobMatch::seekersFor($reg) : NearJobMatch::giversFor($reg)) : [];
?>
<div class="sd-card" style="margin:10px 0 16px;border-left:5px solid #059669">
    <h2 style="font-size:1.15rem;margin:0 0 4px">📍 <?= $njGiver ? $t('आपके पास रहने वाले काम ढूँढने वाले', 'Job seekers living near you') : $t('आपके घर के पास काम', 'Work near your home') ?></h2>
    <p style="margin:0 0 10px;font-size:.88rem;color:#4b5563"><?= $t(
        'केवल पास वाले दिखाए जाते हैं – चुनी गई दूरी के भीतर, या उसी पिन कोड / इलाके में। नए रजिस्ट्रेशन अपने आप जुड़ते रहते हैं।',
        'Only nearby matches are shown – within the chosen distance, or the same PIN code / area. New registrations are added automatically.'
    ) ?></p>
    <?php if (!$njLive): ?>
        <p><b><?= $t('आपका रजिस्ट्रेशन समाप्त हो गया है – रिन्यू करें।', 'Your registration has ended – please renew.') ?></b> <a href="/apply/renew/<?= $h($reg['token']) ?>"><?= $t('रिन्यू', 'Renew') ?> →</a></p>
    <?php elseif (!$njList): ?>
        <p style="margin:0"><?= $njGiver
            ? $t('अभी आपके पास कोई काम ढूँढने वाला रजिस्टर नहीं है। यह पेज सेव कर लें।', 'No job seekers registered near you yet. Bookmark this page.')
            : $t('अभी आपके पास कोई काम नहीं है। यह पेज सेव कर लें।', 'No work near you yet. Bookmark this page.') ?></p>
    <?php else: ?>
        <div style="display:grid;gap:8px">
            <?php foreach ($njList as $m): $d = $m['details']; ?>
                <div style="border:1px solid #e5e7eb;border-radius:12px;padding:10px 12px">
                    <b><?= $h($njGiver ? $m['full_name'] : ($d['business_name'] ?? $m['full_name'])) ?></b>
                    · <span style="font-weight:800;color:#047857">📍 <?= $h($m['match']['label']) ?></span>
                    <?php if (!empty($d['work_type'])): ?> · <?= $tp(FormRegistry::NEAR_JOB_WORK_TYPES[$d['work_type']] ?? ['', $d['work_type']]) ?><?php endif; ?>
                    <div style="font-size:.88rem;color:#374151;margin-top:2px"><?= $h($m['categories']) ?><?= !empty($d['pay']) ? ' · ' . $h($d['pay']) : '' ?><?= !empty($d['timings']) ? ' · ' . $h($d['timings']) : '' ?></div>
                    <?php $about = (string)($d['about_me'] ?? $d['about_need'] ?? ''); if ($about !== ''): ?><div style="font-size:.85rem;color:#4b5563;margin-top:2px"><?= $h(mb_strimwidth($about, 0, 220, '…')) ?></div><?php endif; ?>
                    <div style="margin-top:6px"><a class="sd-btn" style="padding:5px 12px;font-size:.88rem" href="tel:<?= $h($m['mobile']) ?>">📞 <?= $h($m['mobile']) ?></a>
                        <a class="sd-btn ghost" style="padding:5px 12px;font-size:.88rem" href="https://wa.me/91<?= $h(substr((string)preg_replace('/\D/', '', (string)$m['mobile']), -10)) ?>" target="_blank" rel="noopener">WhatsApp</a></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <p style="margin:10px 0 0;font-size:.8rem;color:#6b7280"><?= $t('नौकरी के बदले कभी पैसा न दें / न माँगें। गड़बड़ लगे तो gm@jobsence.com पर बताएँ।', 'Never pay or ask for money for a job. Report anything suspicious to gm@jobsence.com.') ?></p>
</div>
