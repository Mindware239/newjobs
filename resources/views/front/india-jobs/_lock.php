<?php
/** Lock box shown instead of full job details when the visitor has no active Jobs Pass. */
$next = $next ?? '/india-jobs';
?>
<div class="ij-lock">
    <div class="ij-lock-ico" aria-hidden="true">🔒</div>
    <?php if (!$loggedIn): ?>
        <b><?= $t('पूरी जानकारी देखने के लिए पहले लॉगिन करें', 'Log in first to see full details') ?></b>
        <p><?= $t('लॉगिन के बाद ₹150 + GST (₹' . number_format((float)$passFee, 0) . ') का Jobs Pass लें – 1 महीने तक सभी नौकरियों की पूरी जानकारी और आधिकारिक आवेदन लिंक।', 'After login, get the Jobs Pass for ₹150 + GST (₹' . number_format((float)$passFee, 0) . ') – full details and official apply links of all jobs for 1 month.') ?></p>
        <div class="sd-cta-row" style="justify-content:center">
            <a class="sd-btn" href="/login?redirect=<?= $h(rawurlencode($next)) ?>"><?= $tb('लॉगिन करें', 'Log in') ?></a>
            <a class="sd-btn ghost" href="/register-candidate"><?= $tb('नया अकाउंट बनाएँ', 'Create account') ?></a>
        </div>
    <?php else: ?>
        <b><?= $t('पूरी जानकारी और आवेदन लिंक के लिए Jobs Pass लें', 'Get the Jobs Pass for full details and the apply link') ?></b>
        <p><?= $t('₹150 + GST = ₹' . number_format((float)$passFee, 0) . ' – 1 महीने तक सभी नौकरियाँ। एक ईमेल + एक मोबाइल, ईमेल OTP से सत्यापित।', '₹150 + GST = ₹' . number_format((float)$passFee, 0) . ' – all jobs for 1 month. One email + one mobile, verified by email OTP.') ?></p>
        <a class="sd-btn" href="/apply/jobs-pass"><?= $tb('₹' . number_format((float)$passFee, 0) . ' में Jobs Pass लें', 'Get Jobs Pass – ₹' . number_format((float)$passFee, 0)) ?></a>
    <?php endif; ?>
</div>
