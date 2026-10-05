<?php
require dirname(__DIR__) . '/front/apply/_partials.php';
?>
<div class="sd">
    <section class="sd-section">
        <div class="sd-wrap" style="max-width:520px">
            <div class="sd-card">
                <h1 style="font-size:1.45rem;margin-bottom:6px">📱 <?= $t('अपना मोबाइल नंबर जोड़ें', 'Add your mobile number') ?></h1>
                <p style="color:#4b5563;margin:0 0 14px"><?= $t('Jobsence पर हर खाते के लिए मोबाइल नंबर ज़रूरी है। अगली बार इसी डिवाइस पर आप सिर्फ़ मोबाइल नंबर से लॉगिन कर सकेंगे। OTP हमेशा ईमेल पर आएगा – SMS नहीं।', 'A mobile number is required for every Jobsence account. Next time on this device you can log in with just your mobile number. OTPs always come by email – never SMS.') ?></p>
                <?php if (!empty($error)): ?><div class="sd-alert err" role="alert"><?= $h($error) ?></div><?php endif; ?>
                <form method="POST" action="/account/mobile">
                    <input type="hidden" name="_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="next" value="<?= $h($next) ?>">
                    <label for="mobile" style="display:block;font-weight:800;font-size:.9rem;margin-bottom:6px"><?= $t('मोबाइल नंबर', 'Mobile number') ?></label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <span style="padding:12px;border:1px solid #d1d5db;border-radius:10px;background:#f9fafb;font-weight:700">+91</span>
                        <input id="mobile" name="mobile" value="<?= $h($phone) ?>" inputmode="numeric" autocomplete="tel-national" maxlength="10" pattern="[6-9][0-9]{9}" required autofocus
                               placeholder="98XXXXXXXX" style="flex:1;min-width:0;padding:12px;border:1px solid #d1d5db;border-radius:10px;font-size:1.05rem">
                    </div>
                    <button class="sd-btn" type="submit" style="width:100%;margin-top:14px"><?= $tb('सेव करें और आगे बढ़ें', 'Save and continue') ?></button>
                </form>
                <p style="font-size:.8rem;color:#6b7280;margin:12px 0 0"><?= $t('एक मोबाइल नंबर केवल एक खाते में। आपका नंबर किसी के साथ साझा नहीं होगा।', 'One mobile number per account. Your number is never shared without your agreement.') ?></p>
            </div>
        </div>
    </section>
</div>
