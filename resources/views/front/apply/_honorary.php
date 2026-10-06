<?php
/** Status page: honorary mentors add / see their workshops and attendees; learners see the workshops they joined. */
use App\Models\HonoraryWorkshop;

$hwMentor = $reg['type'] === 'honormentor';
$hwLive = $reg['valid_until'] === null || strtotime((string)$reg['valid_until']) > time();
$hwFlash = $_SESSION['honor_flash'] ?? null;
unset($_SESSION['honor_flash']);
$hwCsrf = '<input type="hidden" name="_token" value="' . $h($_SESSION['csrf_token'] ?? '') . '"><input type="hidden" name="token" value="' . $h($reg['token']) . '">';
?>
<div class="sd-card" id="workshops" style="margin:10px 0 16px;border-left:5px solid #f59e0b">
    <h2 style="font-size:1.15rem;margin:0 0 6px">🎗️ <?= $hwMentor ? $t('मेरी मानद वर्कशॉप', 'My honorary workshops') : $t('मेरी वर्कशॉप', 'My workshops') ?></h2>
    <?php if ($hwFlash): ?><div class="sd-alert ok" role="status"><?= $h($hwFlash) ?></div><?php endif; ?>

    <?php if ($hwMentor): ?>
        <?php if ($hwLive): ?>
            <p style="margin:0 0 8px;font-size:.88rem;color:#4b5563"><?= $t('प्लेटफ़ॉर्म शुल्क वैध है ' . date('d M Y', strtotime((string)$reg['valid_until'])) . ' तक। हर वर्कशॉप एक दिन, 4 घंटे की।', 'Platform fee valid till ' . date('d M Y', strtotime((string)$reg['valid_until'])) . '. Each workshop is one day, 4 hours.') ?></p>
            <details <?= HonoraryWorkshop::byMentor((int)$reg['id']) ? '' : 'open' ?> style="margin-bottom:10px">
                <summary style="cursor:pointer;font-weight:800">➕ <?= $t('नई वर्कशॉप जोड़ें', 'Add a workshop') ?></summary>
                <form method="POST" action="/honorary-mentorship/workshop" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:8px;margin-top:8px"><?= $hwCsrf ?>
                    <label style="display:flex;flex-direction:column;font-size:.85rem;font-weight:700"><?= $t('वर्कशॉप का नाम', 'Workshop title') ?><input name="title" maxlength="190" required style="padding:8px;border:1px solid #d1d5db;border-radius:8px"></label>
                    <label style="display:flex;flex-direction:column;font-size:.85rem;font-weight:700"><?= $t('स्किल', 'Skill') ?><select name="skill" required style="padding:8px;border:1px solid #d1d5db;border-radius:8px"><?php foreach (\App\Services\Registration\FormRegistry::HONORARY_SKILLS as $sk => $sl): ?><option value="<?= $h($sk) ?>"><?= $h($sl[1]) ?> / <?= $h($sl[0]) ?></option><?php endforeach; ?></select></label>
                    <label style="display:flex;flex-direction:column;font-size:.85rem;font-weight:700"><?= $t('तारीख', 'Date') ?><input type="date" name="date" required min="<?= date('Y-m-d', strtotime('tomorrow')) ?>" max="<?= $h(date('Y-m-d', strtotime((string)$reg['valid_until']))) ?>" style="padding:8px;border:1px solid #d1d5db;border-radius:8px"></label>
                    <label style="display:flex;flex-direction:column;font-size:.85rem;font-weight:700"><?= $t('शुरू होने का समय (4 घंटे)', 'Start time (4 hours)') ?><input type="time" name="start" required value="10:00" min="06:00" max="18:00" style="padding:8px;border:1px solid #d1d5db;border-radius:8px"></label>
                    <label style="display:flex;flex-direction:column;font-size:.85rem;font-weight:700;grid-column:1/-1"><?= $t('स्थान – केवल द्वारका, नई दिल्ली (केवल जुड़ने वालों को दिखेगा)', 'Venue – Dwarka, New Delhi only (shown only to learners who join)') ?><input name="venue" maxlength="500" required value="<?= $h(\App\Services\Registration\Mentoring::legalAddress()) ?>" style="padding:8px;border:1px solid #d1d5db;border-radius:8px"></label>
                    <label style="display:flex;flex-direction:column;font-size:.85rem;font-weight:700"><?= $t('भाषा', 'Language') ?><input name="language" maxlength="60" placeholder="Hindi / English" style="padding:8px;border:1px solid #d1d5db;border-radius:8px"></label>
                    <label style="display:flex;flex-direction:column;font-size:.85rem;font-weight:700"><?= $t('सीटें', 'Seats') ?><input type="number" name="seats" value="30" min="1" max="500" style="padding:8px;border:1px solid #d1d5db;border-radius:8px"></label>
                    <div style="grid-column:1/-1"><button class="sd-btn" type="submit"><?= $tb('वर्कशॉप जोड़ें', 'Add workshop') ?></button></div>
                </form>
            </details>
        <?php else: ?>
            <p><b><?= $t('प्लेटफ़ॉर्म शुल्क की 6 महीने की अवधि पूरी – नई वर्कशॉप के लिए नवीनीकरण करें।', 'Your 6-month platform fee period has ended – renew to add workshops.') ?></b> <a href="/apply/renew/<?= $h($reg['token']) ?>"><?= $t('नवीनीकरण', 'Renew') ?> →</a></p>
        <?php endif; ?>
        <?php foreach (HonoraryWorkshop::byMentor((int)$reg['id']) as $w): $att = HonoraryWorkshop::attendees((int)$w['id']); ?>
            <div style="border:1px solid #e5e7eb;border-radius:12px;padding:10px 12px;margin-top:8px">
                <b><?= $h($w['title']) ?></b> · <?= $h(date('d M Y', strtotime((string)$w['workshop_date']))) ?>, <?= $h(HonoraryWorkshop::timeRange((string)$w['start_time'])) ?>
                · <?= $w['status'] === 'open' ? (int)$w['taken'] . '/' . (int)$w['seats'] . ' ' . $t('जुड़े', 'joined') : '<span style="color:#b91c1c">' . $t('रद्द', 'Cancelled') . '</span>' ?>
                <?php if ($att): ?>
                    <ul style="margin:6px 0 0;padding-left:18px;font-size:.88rem"><?php foreach ($att as $a): ?><li><?= $h($a['full_name']) ?> · <a href="tel:<?= $h($a['mobile']) ?>"><?= $h($a['mobile']) ?></a><?= $a['email'] ? ' · ' . $h($a['email']) : '' ?><?= $a['city'] ? ' · ' . $h($a['city']) : '' ?></li><?php endforeach; ?></ul>
                <?php endif; ?>
                <?php if ($w['status'] === 'open' && strtotime((string)$w['workshop_date']) >= strtotime('today')): ?>
                    <form method="POST" action="/honorary-mentorship/workshop/<?= (int)$w['id'] ?>/cancel" style="margin-top:6px"><?= $hwCsrf ?><button class="sd-btn ghost" type="submit" style="padding:3px 10px;font-size:.8rem"><?= $tb('रद्द करें', 'Cancel') ?></button></form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <?php $mine = HonoraryWorkshop::forLearner((int)$reg['id']); ?>
        <?php if (!$mine): ?><p style="margin:0"><?= $t('अभी आप किसी वर्कशॉप में नहीं जुड़े हैं।', 'You have not joined a workshop yet.') ?></p><?php endif; ?>
        <?php foreach ($mine as $w): ?>
            <div style="border:1px solid #e5e7eb;border-radius:12px;padding:10px 12px;margin-top:8px">
                <b><?= $h($w['title']) ?></b><?= $w['status'] !== 'open' ? ' · <span style="color:#b91c1c">' . $t('रद्द', 'Cancelled') . '</span>' : '' ?><br>
                🗓️ <?= $h(date('D, d M Y', strtotime((string)$w['workshop_date']))) ?> · ⏰ <?= $h(HonoraryWorkshop::timeRange((string)$w['start_time'])) ?> · <?= $w['mode'] === 'online' ? '💻 Online' : '📍 ' . $h($w['city']) ?>
                <?php if ($w['venue']): ?><div style="font-size:.88rem"><?= $t('स्थान / लिंक', 'Venue / link') ?>: <?= $h($w['venue']) ?></div><?php endif; ?>
                <div style="font-size:.88rem"><?= $t('मानद मेंटर', 'Honorary mentor') ?>: <?= $h($w['mentor_name']) ?> · <a href="tel:<?= $h($w['mentor_mobile']) ?>"><?= $h($w['mentor_mobile']) ?></a></div>
            </div>
        <?php endforeach; ?>
        <a class="sd-btn" style="margin-top:10px" href="/honorary-mentorship"><?= $tb('और वर्कशॉप देखें', 'See more workshops') ?></a>
    <?php endif; ?>
</div>
