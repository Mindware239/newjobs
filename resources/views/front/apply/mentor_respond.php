<?php
require __DIR__ . '/_partials.php';
$modeLabel = static fn(string $m) => $m === 'offline_ncr' ? $t('ऑफलाइन – दिल्ली-NCR', 'Offline – Delhi-NCR') : $t('ऑनलाइन / वीडियो क्लास', 'Online / Video classes');
$status = $assignment['status'] ?? null;
?>
<div class="sd">
    <section class="sd-section">
        <div class="sd-wrap" style="max-width:640px">
            <div style="display:flex;justify-content:flex-end;margin-bottom:12px"><?php $sdLangSwitcher(); ?></div>
            <div class="sd-card">
                <h1 style="font-size:1.5rem"><?= $tb('प्रशिक्षार्थी अनुरोध', 'Mentor Request') ?></h1>

                <?php if (!$assignment || !$candidate): ?>
                    <div class="sd-alert err"><?= $t('यह लिंक मान्य नहीं है।', 'This link is not valid.') ?></div>
                <?php else: ?>
                    <?php if ($result === 'accepted'): ?>
                        <div class="sd-alert ok">✅ <?= $t('धन्यवाद! आपने प्रशिक्षार्थी को स्वीकार कर लिया है। हमने उन्हें आपकी जानकारी भेज दी है और Jobsence टीम क्लास का समय तय करने के लिए संपर्क करेगी।', 'Thank you! You accepted this candidate. We have sent them your details and the Jobsence team will contact you to fix the class schedule.') ?></div>
                    <?php elseif ($result === 'declined'): ?>
                        <div class="sd-alert info"><?= $t('ठीक है, हमने प्रशिक्षार्थी को दूसरे मेंटर चुनने का विकल्प भेज दिया है।', 'Okay – we have offered the candidate other mentors.') ?></div>
                    <?php elseif ($status === 'accepted'): ?>
                        <div class="sd-alert ok"><?= $t('आप यह अनुरोध पहले ही स्वीकार कर चुके हैं।', 'You have already accepted this request.') ?></div>
                    <?php elseif ($status === 'declined'): ?>
                        <div class="sd-alert info"><?= $t('आप यह अनुरोध पहले ही मना कर चुके हैं।', 'You already declined this request.') ?></div>
                    <?php elseif ($status === 'expired'): ?>
                        <div class="sd-alert err"><?= $t('इस अनुरोध का समय समाप्त हो गया है।', 'This request has expired.') ?></div>
                    <?php endif; ?>

                    <table class="sd-table" style="margin:14px 0">
                        <tr><td><?= $t('स्किल', 'Skill') ?></td><td class="amt"><?= $h($assignment['skill']) ?></td></tr>
                        <tr><td><?= $t('तरीका', 'Mode') ?></td><td class="amt"><?= $modeLabel((string)$assignment['mode']) ?></td></tr>
                        <tr><td><?= $t('प्रशिक्षार्थी', 'Candidate') ?></td><td class="amt"><?= $h($candidate['full_name']) ?> (<?= $h($candidate['reg_no']) ?>)</td></tr>
                        <tr><td><?= $t('स्थान', 'Location') ?></td><td class="amt"><?= $h(trim(($candidate['district'] ?? '') . ', ' . ($candidate['state'] ?? ''), ', ')) ?></td></tr>
                    </table>

                    <?php if ($status === 'pending'): ?>
                        <form method="POST" action="/mentor/respond/<?= $h($assignment['token']) ?>" class="sd-cta-row" style="justify-content:center">
                            <input type="hidden" name="_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                            <button type="submit" name="action" value="accept" class="sd-btn" style="background:#16a34a;border-color:#16a34a<?= $action === 'decline' ? ';opacity:.85' : '' ?>"><?= $tb('✔ स्वीकार करें', 'Accept candidate') ?></button>
                            <button type="submit" name="action" value="decline" class="sd-btn ghost"><?= $tb('✖ मना करें', 'Decline') ?></button>
                        </form>
                        <p style="font-size:.85rem;color:#4b5563;text-align:center;margin-top:10px"><?= $t('मानदेय प्रति प्रशिक्षार्थी, सफल ट्रेनिंग के बाद दिया जाता है।', 'Emolument is paid per candidate after successful training.') ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>
