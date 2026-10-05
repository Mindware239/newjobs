<?php
require __DIR__ . '/_partials.php';
$amount = number_format((float)$reg['total_amount'], 0);
$isUsd = ($reg['currency'] ?? 'INR') === 'USD';
$money = \App\Models\PortalRegistration::money($reg);
?>
<div class="sd">
    <section class="sd-section">
        <div class="sd-wrap" style="max-width:640px">
            <div style="display:flex;justify-content:flex-end;margin-bottom:12px"><?php $sdLangSwitcher(); ?></div>
            <div class="sd-card">
                <h1 style="font-size:1.55rem"><?= $tb('प्रोसेसिंग शुल्क भुगतान', 'Pay Processing Fee') ?></h1>

                <?php if ($failed): ?>
                    <div class="sd-alert err"><?= $t('भुगतान पूरा नहीं हुआ या सत्यापित नहीं हो सका। कृपया दोबारा प्रयास करें।', 'Payment was not completed or could not be verified. Please try again.') ?></div>
                <?php endif; ?>
                <?php if ($orderId === '' || $razorpayKey === ''): ?>
                    <div class="sd-alert err"><?= $t('पेमेंट गेटवे अभी उपलब्ध नहीं है, कृपया कुछ देर बाद पुनः प्रयास करें।', 'Payment gateway is unavailable right now, please retry in a few minutes.') ?></div>
                <?php endif; ?>

                <table class="sd-table" style="margin:12px 0 18px">
                    <tr><td><?= $t('रजि. नं.', 'Reg. No.') ?></td><td class="amt"><?= $h($reg['reg_no']) ?></td></tr>
                    <tr><td><?= $t('फॉर्म', 'Form') ?></td><td class="amt"><?= $tp($form['title']) ?></td></tr>
                    <tr><td><?= $t('नाम', 'Name') ?></td><td class="amt"><?= $h($reg['full_name']) ?></td></tr>
                    <tr><td><?= $t('मोबाइल', 'Mobile') ?></td><td class="amt"><?= $h($reg['mobile']) ?></td></tr>
                    <tr><td><b><?= $isUsd ? $t('देय राशि', 'Amount payable') : $t('देय राशि (GST सहित)', 'Amount payable (incl. GST)') ?></b></td><td class="amt" style="font-size:1.25rem;color:#f05537"><?= $h($money) ?></td></tr>
                </table>

                <?php if ($orderId !== '' && $razorpayKey !== ''): ?>
                    <button id="sd-pay-btn" type="button" class="sd-btn" style="width:100%"><?= $isUsd ? $tb("{$money} भुगतान करें", "Pay {$money} – international card") : $tb("{$money} भुगतान करें", "Pay {$money} – UPI / Card / Net Banking") ?></button>
                <?php else: ?>
                    <a class="sd-btn" style="width:100%" href="/apply/pay/<?= $h($reg['token']) ?>"><?= $tb('दोबारा प्रयास करें', 'Retry') ?></a>
                <?php endif; ?>

                <p style="font-size:.85rem;color:#4b5563;margin-top:14px">
                    <?= $t('इस पेज का लिंक सेव कर लें – भुगतान बाद में भी कर सकते हैं (हम ईमेल पर लिंक भी भेजेंगे)।', 'Bookmark this page – you can pay later too (we will also email you the link).') ?>
                </p>
            </div>

            <form id="sd-verify-form" method="POST" action="/apply/verify" style="display:none">
                <input type="hidden" name="_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="token" value="<?= $h($reg['token']) ?>">
                <input type="hidden" name="razorpay_payment_id">
                <input type="hidden" name="razorpay_order_id">
                <input type="hidden" name="razorpay_signature">
            </form>

            <div style="margin-top:20px"><?php $sdNotice((float)$fee, $isUsd ? $money : ''); ?></div>
        </div>
    </section>
</div>

<?php if ($orderId !== '' && $razorpayKey !== ''): ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function () {
    var btn = document.getElementById('sd-pay-btn');
    var form = document.getElementById('sd-verify-form');
    var label = btn.innerHTML;
    var options = {
        key: <?= json_encode($razorpayKey) ?>,
        order_id: <?= json_encode($orderId) ?>,
        amount: <?= (int)round((float)$reg['total_amount'] * 100) ?>,
        currency: <?= json_encode($isUsd ? 'USD' : 'INR') ?>,
        name: 'Jobsence',
        description: <?= json_encode($form['title'][1] . ' – ' . $reg['reg_no']) ?>,
        prefill: {
            name: <?= json_encode((string)$reg['full_name']) ?>,
            email: <?= json_encode((string)($reg['email'] ?? '')) ?>,
            contact: <?= json_encode('+91' . $reg['mobile']) ?>
        },
        notes: { reg_no: <?= json_encode((string)$reg['reg_no']) ?> },
        theme: { color: '#f05537' },
        handler: function (res) {
            form.razorpay_payment_id.value = res.razorpay_payment_id;
            form.razorpay_order_id.value = res.razorpay_order_id;
            form.razorpay_signature.value = res.razorpay_signature;
            btn.disabled = true;
            btn.textContent = 'भुगतान सत्यापित हो रहा है… / Verifying payment…';
            form.submit();
        },
        modal: { ondismiss: function () { btn.disabled = false; btn.innerHTML = label; } }
    };
    btn.addEventListener('click', function () {
        btn.disabled = true;
        new Razorpay(options).open();
    });
})();
</script>
<?php endif; ?>
<?php $sdWhatsapp($whatsappNumber); ?>
