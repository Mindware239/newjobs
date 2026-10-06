<?php
require dirname(__DIR__) . '/apply/_partials.php';
use App\Models\HonoraryWorkshop;
?>
<div class="sd">
    <section class="sd-section" style="background:linear-gradient(135deg,#fef3c7,#fff7ed 55%,#ecfdf5)">
        <div class="sd-wrap">
            <div style="display:flex;justify-content:flex-end;margin-bottom:8px"><?php $sdLangSwitcher(); ?></div>
            <h1 style="font-size:clamp(1.6rem,3.4vw,2.3rem);font-weight:900;margin:0 0 6px">🎗️ <?= $tb('मानद मेंटरशिप – एक दिन, 4 घंटे की मुफ़्त वर्कशॉप', 'Honorary Mentorship – Free One-Day, 4-Hour Workshops') ?></h1>
            <p class="sd-lead"><?= $t(
                'कौशल देना पुण्य है। अनुभवी लोग मानद रूप से (बिना फ़ीस) एक दिन की 4 घंटे की वर्कशॉप में सिखाते हैं – ऑनलाइन या किसी स्थान पर। सीखने वालों के लिए रजिस्ट्रेशन और वर्कशॉप दोनों मुफ़्त।',
                'Giving skill is punya. Experienced people teach honorarily (no fee) in one-day, 4-hour workshops – online or at a venue. Registration and workshops are free for learners.'
            ) ?></p>
            <div class="sd-alert info" style="margin:0 0 12px">📍 <b><?= $t('केवल ' . \App\Services\Registration\FormRegistry::HONORARY_PLACE[0] . ' में', 'Only in ' . \App\Services\Registration\FormRegistry::HONORARY_PLACE[1]) ?></b> · <?= $t('अभी इन स्किल्स के लिए: ' . \App\Services\Registration\FormRegistry::honorarySkillList(0), 'For these skills for now: ' . \App\Services\Registration\FormRegistry::honorarySkillList(1)) ?></div>
            <div class="sd-cta-row">
                <a class="sd-btn" href="/apply/honorary-learner"><?= $tb('मुफ़्त सीखने के लिए रजिस्टर करें', 'Register free to learn') ?></a>
                <a class="sd-btn ghost" href="/apply/honorary-mentor"><?= $tb('मानद मेंटर बनें – ₹155 प्लेटफ़ॉर्म शुल्क / 6 महीने', 'Become an honorary mentor – ₹155 platform fee / 6 months') ?></a>
            </div>
            <?php if (!empty($flash)): ?><div class="sd-alert ok" role="status" style="margin-top:12px"><?= $h($flash) ?></div><?php endif; ?>
        </div>
    </section>
    <section class="sd-section">
        <div class="sd-wrap">
            <h2 style="font-size:1.25rem"><?= $tb('आने वाली वर्कशॉप', 'Upcoming workshops') ?></h2>
            <?php if (!$workshops): ?>
                <div class="sd-card" style="text-align:center"><b><?= $t('अभी कोई वर्कशॉप नहीं है – जल्द आ रही हैं।', 'No workshops yet – coming soon.') ?></b>
                    <p style="margin:6px 0 0"><?= $t('मुफ़्त रजिस्टर करें – नई वर्कशॉप की सूचना मिलेगी।', 'Register free – you will hear about new workshops.') ?></p></div>
            <?php endif; ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px">
                <?php foreach ($workshops as $w): $left = max(0, (int)$w['seats'] - (int)$w['taken']); ?>
                    <article class="sd-card" style="display:flex;flex-direction:column;gap:6px;border-top:5px solid #f59e0b">
                        <span style="font-size:.78rem;font-weight:900;color:#92400e"><?= $h($w['skill']) ?> · <?= $w['mode'] === 'online' ? '💻 ' . $t('ऑनलाइन', 'Online') : '📍 ' . $h($w['city']) ?></span>
                        <h3 style="margin:0;font-size:1.05rem"><?= $h($w['title']) ?></h3>
                        <div style="font-size:.9rem">🗓️ <b><?= $h(date('D, d M Y', strtotime((string)$w['workshop_date']))) ?></b> · ⏰ <?= $h(HonoraryWorkshop::timeRange((string)$w['start_time'])) ?> (4 <?= $t('घंटे', 'hours') ?>)</div>
                        <div style="font-size:.85rem;color:#4b5563">🎗️ <?= $t('मानद मेंटर', 'Honorary mentor') ?>: <?= $h($w['mentor_name']) ?><?= $w['language'] ? ' · ' . $h($w['language']) : '' ?></div>
                        <div style="font-size:.85rem;font-weight:700;color:<?= $left > 0 ? '#047857' : '#b91c1c' ?>"><?= $left > 0 ? $left . ' ' . $t('सीटें बाकी', 'seats left') : $t('भर गई', 'Full') ?></div>
                        <?php if ($left > 0): ?><a class="sd-btn" style="margin-top:auto" href="/honorary-mentorship/join/<?= (int)$w['id'] ?>"><?= $tb('मुफ़्त जुड़ें', 'Join free') ?></a><?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="ij-note" style="margin-top:20px"><?= $t(
                'मानद वर्कशॉप पूरी तरह मुफ़्त हैं। कोई मेंटर पैसे माँगे तो gm@jobsence.com पर बताएँ। यह भारत सरकार की योजना नहीं है।',
                'Honorary workshops are completely free. Report any mentor who asks for money to gm@jobsence.com. This is not a Government of India scheme.'
            ) ?></div>
        </div>
    </section>
</div>
