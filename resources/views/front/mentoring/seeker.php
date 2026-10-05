<?php
require __DIR__ . "/_shared.php";
use App\Services\Registration\FormRegistry;
use App\Services\Registration\Mentoring;

$form = FormRegistry::byType((string)$seeker['type']);
$d = $seeker['details'] ?? [];
// Never shown before the agreement: contact, identity numbers, exact address.
$hidden = '/^(mobile|whatsapp|email|aadhaar|address_line|landmark|resume|video|photo|selfie)$/';
$value = static function (array $field) use ($seeker, $d) {
    $key = $field['key'];
    $v = $seeker[$key] ?? ($d[$key] ?? null);
    if ($v === null || $v === '' || $v === []) {
        return null;
    }
    if (isset($field['options'])) {
        $labels = array_map(static fn($x) => $field['options'][$x][1] ?? (string)$x, (array)$v);
        return implode(', ', $labels);
    }
    if ($key === 'dob') {
        return date('d M Y', strtotime((string)$v));
    }
    return is_array($v) ? implode(', ', $v) : (string)$v;
};
$signed = $agreement && $agreement['status'] === 'accepted';
?>
<div class="sd">
    <section class="sd-section mt-hero">
        <div class="sd-wrap" style="max-width:900px">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px">
                <a href="<?= $h((string)(Mentoring::KINDS[Mentoring::kindOf((string)$seeker['type'])]['seekers_page'] ?? '/mentoring')) ?>">← <?= $t('सूची पर वापस', 'Back to list') ?></a>
                <?php $sdLangSwitcher(); ?>
            </div>
            <?php $mtFlash($flash); ?>
            <div class="mt-box">
                <span class="mt-tag g"><?= $h($seeker['reg_no']) ?></span>
                <?php if ($state === 'matched'): ?><span class="mt-tag w"><?= $t('मेंटर मिल चुका', 'Already matched') ?></span><?php elseif ($state === 'closed'): ?><span class="mt-tag w"><?= $t('रजिस्ट्रेशन बंद', 'Registration closed') ?></span><?php endif; ?>
                <h1 style="margin:6px 0"><?= $h($seeker['full_name']) ?></h1>
                <div class="mt-meta">📍 <?= $h(implode(', ', array_filter([$d['village'] ?? '', $seeker['city'], $seeker['district'], $seeker['state'], $seeker['pincode']]))) ?></div>
                <div class="mt-skills" style="margin-top:8px"><?php foreach (array_filter(array_map('trim', explode(',', (string)$seeker['categories']))) as $i => $c): ?><span><?= ($i + 1) . '. ' . $h($c) ?></span><?php endforeach; ?></div>

                <div class="mt-act">
                    <?php if (!empty($seeker['resume_path'])): ?><a class="sd-btn ghost" href="/mentoring/resume/<?= (int)$seeker['id'] ?>">📄 <?= $t('रिज़्यूमे डाउनलोड करें', 'Download resume') ?></a><?php endif; ?>
                    <?php if ($agreement): ?>
                        <a class="sd-btn mt-green" href="<?= $h($agreement['link']) ?>"><?= $signed ? $t('समझौता और संपर्क देखें', 'See agreement & contact') : $t('समझौता खोलें', 'Open agreement') ?></a>
                    <?php elseif ($state === 'open'): ?>
                        <form method="POST" action="/mentoring/request/<?= (int)$seeker['id'] ?>"><?= $mtCsrf() ?><input type="hidden" name="back" value="/mentoring/seeker/<?= (int)$seeker['id'] ?>">
                            <button class="sd-btn mt-green" type="submit"><?= $t('त्रिपक्षीय समझौता भेजें', 'Send tripartite agreement') ?></button></form>
                    <?php endif; ?>
                </div>
                <?php if (!$signed): ?><p class="mt-lock" style="margin:8px 0 0">🔒 <?= $t('फ़ोन, ईमेल और पूरा पता समझौते पर दोनों के साइन के बाद दिखेंगे।', 'Phone, email and full address appear after both have signed the agreement.') ?></p><?php endif; ?>
            </div>

            <div class="mt-box">
                <h2 style="font-size:1.15rem"><?= $t('पूरा फॉर्म', 'Full form') ?></h2>
                <?php foreach ($form['sections'] as $sec): $rows = []; foreach ($sec['fields'] as $field) {
                    if (preg_match($hidden, $field['key']) || $field['type'] === 'categories') { continue; }
                    $v = $value($field);
                    if ($v !== null) { $rows[] = [$field['label'], $v]; }
                } if (!$rows) continue; ?>
                    <h3 style="font-size:1rem;margin:14px 0 4px;color:#138808"><?= $tp($sec['title']) ?></h3>
                    <table class="mt-table"><tbody>
                        <?php foreach ($rows as [$label, $v]): ?><tr><td style="width:40%;color:#4b5563"><?= $tp($label) ?></td><td><?= nl2br($h($v)) ?></td></tr><?php endforeach; ?>
                    </tbody></table>
                <?php endforeach; ?>
                <p class="mt-meta" style="margin-top:10px"><?= $t('रजिस्ट्रेशन की तारीख: ', 'Registered on: ') ?><?= $h(date('d M Y', strtotime((string)$seeker['paid_at']))) ?></p>
            </div>
        </div>
    </section>
</div>
