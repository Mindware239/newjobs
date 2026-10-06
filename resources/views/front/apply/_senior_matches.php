<?php
/** Status page of a senior citizen (nearby senior jobs) or an organisation (nearby seniors). Near-me only. */
use App\Services\Registration\SeniorMatch;

$smOrg = $reg['type'] === 'seniorhire';
$smLive = $reg['valid_until'] === null || strtotime((string)$reg['valid_until']) > time();
$smList = $smLive ? ($smOrg ? SeniorMatch::seniorsFor($reg) : SeniorMatch::jobsFor($reg)) : [];
$smWork = ['full_time' => ['फ़ुल-टाइम', 'Full-time'], 'part_time' => ['पार्ट-टाइम', 'Part-time'], 'community' => ['सामुदायिक सेवा', 'Community service']];
?>
<div class="sd-card" style="margin:10px 0 16px;border-left:5px solid #f59e0b">
    <h2 style="font-size:1.15rem;margin:0 0 4px">📍 <?= $smOrg ? $t('आपके पास के वरिष्ठ नागरिक', 'Senior citizens near you') : $t('आपके घर के पास वरिष्ठ नागरिकों के लिए काम', 'Senior jobs near your home') ?></h2>
    <p style="margin:0 0 10px;font-size:.88rem;color:#4b5563"><?= $t(
        'वरिष्ठ नागरिकों का काम हमेशा घर के पास होता है – केवल वही दिखाए जाते हैं जो चुनी गई दूरी के भीतर (या उसी पिन कोड / इलाके में) हैं।',
        'Senior jobs are always near home – only matches within the chosen distance (or the same PIN code / area) are shown.'
    ) ?></p>
    <?php if (!$smLive): ?>
        <p><b><?= $t('यह नौकरी 7 दिन पूरे कर चुकी है – नई नौकरी पोस्ट करें।', 'This job has completed its 7 days – post a new job.') ?></b> <a href="/apply/senior-citizen-hiring"><?= $t('नई नौकरी', 'New job') ?> →</a></p>
    <?php elseif (!$smList): ?>
        <p style="margin:0"><?= $smOrg
            ? $t('अभी पास में कोई वरिष्ठ नागरिक रजिस्टर नहीं है। नए रजिस्ट्रेशन आते ही यहाँ दिखेंगे – यह पेज 7 दिन तक खोलें।', 'No senior citizens registered near you yet. New registrations appear here – keep this page for the 7 days.')
            : $t('अभी आपके पास कोई नौकरी नहीं है। नई नौकरियाँ आते ही यहाँ दिखेंगी – यह पेज सेव कर लें।', 'No jobs near you yet. New ones appear here – bookmark this page.') ?></p>
    <?php else: ?>
        <div class="sj-list" style="display:grid;gap:8px">
            <?php foreach ($smList as $m): $d = $m['details']; ?>
                <div style="border:1px solid #e5e7eb;border-radius:12px;padding:10px 12px">
                    <b><?= $h($smOrg ? $m['full_name'] : ($d['business_name'] ?? $m['full_name'])) ?></b>
                    <?php if ($smOrg && !empty($m['dob'])): ?> · <?= (int)date_diff(date_create((string)$m['dob']), date_create('today'))->y ?> <?= $t('वर्ष', 'yrs') ?><?php endif; ?>
                    · <span style="font-weight:800;color:#b45309">📍 <?= $h($m['match']['label']) ?></span>
                    <?php if (!empty($d['work_type'])): ?> · <?= $tp($smWork[$d['work_type']] ?? ['', $d['work_type']]) ?><?php endif; ?>
                    <div style="font-size:.88rem;color:#374151;margin-top:2px"><?= $h($m['categories']) ?><?= !empty($d['pay']) ? ' · ' . $h($d['pay']) : '' ?><?= !empty($d['retired_from']) ? ' · ' . $t('रिटायर', 'Retired from') . ': ' . $h($d['retired_from']) : '' ?></div>
                    <?php $about = (string)($d['about_me'] ?? $d['about_need'] ?? ''); if ($about !== ''): ?><div style="font-size:.85rem;color:#4b5563;margin-top:2px"><?= $h(mb_strimwidth($about, 0, 220, '…')) ?></div><?php endif; ?>
                    <div style="margin-top:6px"><a class="sd-btn" style="padding:5px 12px;font-size:.88rem" href="tel:<?= $h($m['mobile']) ?>">📞 <?= $h($m['mobile']) ?></a>
                        <?php if (!empty($m['email']) && !str_ends_with((string)$m['email'], '@mobile.local')): ?><a class="sd-btn ghost" style="padding:5px 12px;font-size:.88rem" href="mailto:<?= $h($m['email']) ?>">✉️</a><?php endif; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
