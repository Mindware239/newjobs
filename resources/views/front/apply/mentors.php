<?php
require __DIR__ . '/_partials.php';
$mask = static function (string $name): string {
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $first = $parts[0] ?? '';
    $last = count($parts) > 1 ? mb_substr((string)end($parts), 0, 1) . '.' : '';
    return trim($first . ' ' . $last);
};
$langNames = ['hindi' => 'हिंदी / Hindi', 'english' => 'English', 'regional' => 'Regional', 'mix' => 'Mix'];
$card = static function (array $m, string $mode) use ($h, $t, $mask, $langNames, $candidate): void {
    $d = $m['details'] ?? [];
    $langs = implode(', ', array_map(static fn($l) => $langNames[$l] ?? $l, (array)($d['languages'] ?? [])));
    ?>
    <div class="sd-card" style="display:flex;flex-direction:column;gap:6px">
        <div style="font-weight:800;font-size:1.05rem">👩‍🏫 <?= $h($mask((string)$m['full_name'])) ?></div>
        <div><span class="sd-chip"><?= $h($m['matched_skill']) ?></span></div>
        <div style="font-size:.9rem;color:#4b5563">
            <?= $t('अनुभव', 'Experience') ?>: <b><?= (int)($d['years_experience'] ?? 0) ?></b> <?= $t('वर्ष', 'years') ?>
            <?php if ($langs): ?> · <?= $t('भाषा', 'Languages') ?>: <?= $h($langs) ?><?php endif; ?>
            <?php if ($mode === 'offline_ncr' && !empty($m['district'])): ?> · <?= $h($m['district']) ?><?php endif; ?>
        </div>
        <form method="POST" action="/apply/mentors/<?= $h($candidate['token']) ?>" style="margin-top:6px">
            <input type="hidden" name="_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="mentor_id" value="<?= (int)$m['id'] ?>">
            <button type="submit" class="sd-btn small"><?= $t('इस मेंटर से सीखें', 'Request this mentor') ?></button>
        </form>
    </div>
<?php };
$modeLabel = static fn(string $m) => $m === 'offline_ncr' ? $t('ऑफलाइन – दिल्ली-NCR', 'Offline – Delhi-NCR') : $t('ऑनलाइन / वीडियो क्लास', 'Online / Video classes');
?>
<div class="sd">
    <section class="sd-section">
        <div class="sd-wrap" style="max-width:960px">
            <div style="display:flex;justify-content:flex-end;margin-bottom:12px"><?php $sdLangSwitcher(); ?></div>
            <h1 style="font-size:1.6rem"><?= $tb('अपना मेंटर चुनें', 'Choose Your Mentor') ?></h1>
            <p class="sd-lead"><?= $h($candidate['full_name']) ?> · <?= $h($candidate['reg_no']) ?></p>

            <div class="sd-card" style="margin-bottom:18px">
                <b><?= $t('आपके चुने हुए स्किल (पसंद के क्रम में)', 'Your skill choices (in order of preference)') ?>:</b>
                <div class="sd-chips" style="margin-top:8px">
                    <?php foreach ($skills as $i => $s): ?><span class="sd-chip"><?= $i + 1 ?>. <?= $h($s) ?></span><?php endforeach; ?>
                </div>
                <p style="margin:8px 0 0;font-size:.85rem;color:#4b5563"><?= $t('आपको केवल इन्हीं स्किल में ट्रेनिंग दी जाएगी।', 'You will be trained only in these skills.') ?></p>
            </div>

            <?php if ($flash !== ''): ?>
                <div class="sd-alert <?= $flashOk ? 'ok' : 'err' ?>"><?= $flashOk ? $t('अनुरोध मेंटर को भेज दिया गया है। जवाब आने पर आपको ईमेल मिलेगा।', 'Request sent to the mentor. You will get an email when they reply.') : $h($flash) ?></div>
            <?php endif; ?>

            <?php if ($current): ?>
                <div class="sd-alert <?= $current['status'] === 'accepted' ? 'ok' : 'info' ?>">
                    <?php if ($current['status'] === 'accepted'): ?>
                        ✅ <?= $t('आपका मेंटर तय है', 'Your mentor is confirmed') ?>: <b><?= $h($current['mentor_name']) ?></b> – <?= $h($current['skill']) ?> (<?= $modeLabel((string)$current['mode']) ?>)
                    <?php else: ?>
                        ⏳ <?= $t('मेंटर के जवाब का इंतज़ार है', 'Waiting for the mentor to reply') ?>: <b><?= $h($mask((string)$current['mentor_name'])) ?></b> – <?= $h($current['skill']) ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <h2 style="font-size:1.25rem;margin-top:10px">💻 <?= $tb('ऑनलाइन उपलब्ध मेंटर', 'Mentors available Online') ?></h2>
                <?php if ($online): ?>
                    <div class="sd-grid"><?php foreach ($online as $m) { $card($m, 'online'); } ?></div>
                <?php else: ?>
                    <p style="color:#4b5563"><?= $t('अभी कोई ऑनलाइन मेंटर उपलब्ध नहीं है।', 'No online mentor available right now.') ?></p>
                <?php endif; ?>

                <h2 style="font-size:1.25rem;margin-top:26px">🏫 <?= $tb('ऑफलाइन (दिल्ली-NCR) उपलब्ध मेंटर', 'Mentors available Offline (Delhi-NCR)') ?></h2>
                <?php if ($offline): ?>
                    <div class="sd-grid"><?php foreach ($offline as $m) { $card($m, 'offline_ncr'); } ?></div>
                <?php else: ?>
                    <p style="color:#4b5563"><?= $t('अभी कोई ऑफलाइन मेंटर उपलब्ध नहीं है।', 'No offline mentor available right now.') ?></p>
                <?php endif; ?>

                <?php if (!$online && !$offline): ?>
                    <div class="sd-alert info" style="margin-top:18px"><?= $t('जैसे ही आपके स्किल के लिए कोई मेंटर जुड़ेगा, हम आपको ईमेल करेंगे।', 'We will email you as soon as a mentor for your skills joins.') ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php $sdWhatsapp($whatsappNumber); ?>
