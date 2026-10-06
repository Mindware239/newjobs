<?php
require __DIR__ . '/_partials.php';
$amount = number_format((float)$reg['total_amount'], 0);
$isFree = (float)$reg['total_amount'] <= 0;
$mentoringTypes = ['skill', 'internship', 'provider', 'internpro', 'mentorplan', 'internplan', 'fulltime', 'parttime', 'wfh', 'jobpro', 'jobplan', 'nearpro', 'intljob', 'intlcountry'];
?>
<div class="sd">
    <section class="sd-section">
        <div class="sd-wrap" style="max-width:680px">
            <div style="display:flex;justify-content:flex-end;margin-bottom:12px"><?php $sdLangSwitcher(); ?></div>
            <div class="sd-card">
                <div class="sd-alert ok" style="font-size:1.05rem">
                    ✅ <b><?= $t('धन्यवाद! आपका रजिस्ट्रेशन सफल रहा।', 'Thank you! Your registration is successful.') ?></b>
                </div>

                <h1 style="font-size:1.45rem"><?= $tb('रसीद', 'Registration Receipt') ?></h1>
                <table class="sd-table" style="margin:12px 0 18px">
                    <tr><td><?= $t('रजि. नं.', 'Reg. No.') ?></td><td class="amt"><?= $h($reg['reg_no']) ?></td></tr>
                    <tr><td><?= $t('फॉर्म', 'Form') ?></td><td class="amt"><?= $tp($form['title']) ?></td></tr>
                    <tr><td><?= $t('नाम', 'Name') ?></td><td class="amt"><?= $h($reg['full_name']) ?></td></tr>
                    <tr><td><?= $t('मोबाइल', 'Mobile') ?></td><td class="amt"><?= $h($reg['mobile']) ?></td></tr>
                    <?php if ($isFree): ?>
                    <tr><td><?= $t('शुल्क', 'Fee') ?></td><td class="amt"><?= $t('मुफ़्त रजिस्ट्रेशन', 'Free registration') ?></td></tr>
                    <?php else: ?>
                    <tr><td><?= $t('राशि (GST सहित)', 'Amount paid (incl. GST)') ?></td><td class="amt"><?= $h(\App\Models\PortalRegistration::money($reg)) ?></td></tr>
                    <tr><td><?= $t('पेमेंट आईडी', 'Payment ID') ?></td><td class="amt" style="font-size:.85rem;word-break:break-all"><?= $h($reg['razorpay_payment_id']) ?></td></tr>
                    <?php endif; ?>
                    <tr><td><?= $t('तारीख', 'Date') ?></td><td class="amt"><?= $h(date('d/m/Y', strtotime((string)$reg['paid_at']))) ?></td></tr>
                    <?php if (!empty($reg['valid_until'])): ?>
                    <tr><td><?php $vl = \App\Models\PortalRegistration::validityLabel((string)$reg['type']) ?? ['3 महीने', '3 months']; ?><?= $t("रजिस्ट्रेशन वैधता ({$vl[0]})", "Registration valid till ({$vl[1]})") ?></td><td class="amt"><?= $h(date('d/m/Y', strtotime((string)$reg['valid_until']))) ?></td></tr>
                    <?php endif; ?>
                </table>

                <h2 style="font-size:1.15rem"><?= $tb('आगे क्या होगा?', 'What happens next?') ?></h2>
                <ul style="padding-left:18px">
                    <?php foreach ($form['next_steps'] as $s): ?><li><?= $tp($s) ?></li><?php endforeach; ?>
                    <?php if (!empty($reg['email'])): ?>
                        <li><?= $t('पुष्टि ईमेल ' . $reg['email'] . ' पर gm@jobsence.com से भेजा गया है।', 'A confirmation email has been sent to ' . $reg['email'] . ' from gm@jobsence.com.') ?></li>
                    <?php endif; ?>
                </ul>

                <?php if (in_array($reg['type'], ['senior', 'seniorhire'], true)) { require __DIR__ . '/_senior_matches.php'; } ?>
                <?php if (in_array($reg['type'], $mentoringTypes, true)): ?>
                    <a class="sd-btn" style="width:100%;margin:6px 0 14px" href="/mentoring/access/<?= $h($reg['token']) ?>"><?= $tb('मेरा मेंटरिंग डैशबोर्ड खोलें →', 'Open my mentoring dashboard →') ?></a>
                <?php elseif ($reg['type'] === 'jobpass'): ?>
                    <a class="sd-btn" style="width:100%;margin:6px 0 14px" href="/india-jobs"><?= $tb('अभी Jobs in India देखें →', 'See Jobs in India now →') ?></a>
                <?php elseif (!empty($passPage)): ?>
                    <a class="sd-btn" style="width:100%;margin:6px 0 14px" href="<?= $h($passPage . ($reg['type'] === 'nearseek' && $reg['pincode'] ? '?pin=' . $reg['pincode'] : '')) ?>"><?= $tb('अभी खोलें – नंबर और पता देखें →', 'Open now – choose providers →') ?></a>
                <?php elseif ($reg['type'] === 'nearpro'): ?>
                    <a class="sd-btn ghost" style="width:100%;margin:6px 0 14px" href="/near-me?pin=<?= $h($reg['pincode']) ?>"><?= $tb('Near Me पेज देखें', 'See the Near Me page') ?></a>
                <?php endif; ?>
                <div class="sd-cta-row">
                    <button type="button" class="sd-btn ghost" onclick="window.print()"><?= $tb('रसीद प्रिंट करें', 'Print receipt') ?></button>
                    <a class="sd-btn" href="/apply"><?= $tb('दूसरे फॉर्म देखें', 'See other forms') ?></a>
                </div>
            </div>
            <div style="margin-top:20px"><?php $sdNotice((float)$fee, ($reg['currency'] ?? 'INR') === 'USD' ? \App\Models\PortalRegistration::money($reg) : ''); ?></div>
        </div>
    </section>
</div>
<?php $sdWhatsapp($whatsappNumber); ?>
