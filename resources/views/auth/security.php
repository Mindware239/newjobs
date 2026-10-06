<?php
require dirname(__DIR__) . '/front/apply/_partials.php';
$csrf = '<input type="hidden" name="_token" value="' . $h($_SESSION['csrf_token'] ?? '') . '">';
?>
<div class="sd">
    <section class="sd-section">
        <div class="sd-wrap" style="max-width:720px">
            <div class="sd-card">
                <h1 style="font-size:1.45rem;margin-bottom:6px">🔐 <?= $t('लॉगिन और डिवाइस', 'Login & devices') ?></h1>
                <?php if (!empty($flash)): ?><div class="sd-alert ok" role="status"><?= $h($flash) ?></div><?php endif; ?>
                <table class="sd-table" style="margin:10px 0 16px">
                    <tr><td><?= $t('ईमेल (OTP यहीं आता है)', 'Email (OTPs come here)') ?></td><td class="amt"><?= $h($user->email ?? '') ?></td></tr>
                    <tr><td><?= $t('मोबाइल नंबर', 'Mobile number') ?></td><td class="amt"><?= $h($user->phone ?? '—') ?> · <a href="/account/mobile?change=1&next=/account/security"><?= $t('बदलें', 'Change') ?></a></td></tr>
                </table>

                <?php if ($pinAllowed): ?>
                    <h2 id="pin" style="font-size:1.1rem">🔒 <?= $t('लॉगिन PIN (वैकल्पिक)', 'Login PIN (optional)') ?></h2>
                    <p style="color:#4b5563;margin:0 0 10px;font-size:.92rem"><?= $t(
                        'PIN रखने पर नए डिवाइस पर मोबाइल नंबर / ईमेल + PIN से लॉगिन होता है – ईमेल OTP का इंतज़ार नहीं। PIN भूल जाएँ तो ईमेल OTP हमेशा काम करता है। ' . \App\Services\LoginPinService::MAX_TRIES . ' बार गलत PIN पर PIN ' . \App\Services\LoginPinService::LOCK_MINUTES . ' मिनट के लिए बंद हो जाता है।',
                        'With a PIN you log in on a new device with your mobile number / email + PIN – no waiting for an email OTP. If you forget it, the email OTP always works. ' . \App\Services\LoginPinService::MAX_TRIES . ' wrong PINs lock it for ' . \App\Services\LoginPinService::LOCK_MINUTES . ' minutes.'
                    ) ?></p>
                    <?php if (!empty($pinError)): ?><div class="sd-alert err" role="alert"><?= $h($pinError) ?></div><?php endif; ?>
                    <p style="margin:0 0 8px;font-weight:700"><?= $hasPin ? '✅ ' . $t('PIN सेट है', 'PIN is set') : $t('अभी कोई PIN नहीं – लॉगिन ईमेल OTP से होता है।', 'No PIN yet – you log in with the email OTP.') ?></p>
                    <form method="POST" action="/account/pin" style="display:flex;flex-wrap:wrap;gap:8px;align-items:flex-end;margin-bottom:10px"><?= $csrf ?>
                        <label style="display:flex;flex-direction:column;font-size:.85rem;font-weight:700"><?= $t($hasPin ? 'नया PIN' : 'PIN (4–6 अंक)', $hasPin ? 'New PIN' : 'PIN (4–6 digits)') ?>
                            <input type="password" name="pin" inputmode="numeric" pattern="\d{4,6}" minlength="4" maxlength="6" autocomplete="new-password" required style="width:140px;padding:8px;border:1px solid #d1d5db;border-radius:8px;letter-spacing:.3em"></label>
                        <label style="display:flex;flex-direction:column;font-size:.85rem;font-weight:700"><?= $t('PIN दोबारा', 'Repeat PIN') ?>
                            <input type="password" name="pin_confirm" inputmode="numeric" pattern="\d{4,6}" minlength="4" maxlength="6" autocomplete="new-password" required style="width:140px;padding:8px;border:1px solid #d1d5db;border-radius:8px;letter-spacing:.3em"></label>
                        <button class="sd-btn" type="submit"><?= $hasPin ? $tb('PIN बदलें', 'Change PIN') : $tb('PIN सेट करें', 'Set PIN') ?></button>
                    </form>
                    <?php if ($hasPin): ?>
                        <form method="POST" action="/account/pin/remove" style="margin-bottom:16px"><?= $csrf ?>
                            <button class="sd-btn ghost" type="submit" style="padding:6px 12px;font-size:.85rem"><?= $tb('PIN हटाएँ', 'Remove PIN') ?></button>
                        </form>
                    <?php endif; ?>
                    <p style="font-size:.8rem;color:#6b7280;margin:0 0 18px"><?= $t('1111 या 1234 जैसा आसान PIN न रखें, और PIN किसी को न बताएँ – Jobsence कभी आपका PIN नहीं पूछता।', 'Avoid easy PINs like 1111 or 1234 and never share your PIN – Jobsence will never ask for it.') ?></p>
                <?php endif; ?>

                <h2 style="font-size:1.1rem"><?= $t('याद रखे गए डिवाइस', 'Remembered devices') ?></h2>
                <p style="color:#4b5563;margin:0 0 10px;font-size:.92rem"><?= $t('इन डिवाइस पर सिर्फ़ मोबाइल नंबर या ईमेल से लॉगिन होता है (' . \App\Services\TrustedDeviceService::TRUST_DAYS . ' दिन तक)। जो डिवाइस आपका नहीं है उसे हटा दें।', 'These devices log in with just your mobile number or email (for ' . \App\Services\TrustedDeviceService::TRUST_DAYS . ' days). Remove any device that is not yours.') ?></p>
                <?php if (!$devices): ?>
                    <p style="color:#6b7280"><?= $t('कोई डिवाइस याद नहीं रखा गया।', 'No remembered devices.') ?></p>
                <?php else: ?>
                    <table class="sd-table" style="margin-bottom:14px">
                        <?php foreach ($devices as $d): ?>
                            <tr>
                                <td><b><?= $h($d['label'] ?: 'Device') ?></b><?= $d['current'] ? ' <span style="color:#047857;font-weight:800">· ' . $t('यह डिवाइस', 'this device') . '</span>' : '' ?><br>
                                    <span style="font-size:.82rem;color:#6b7280"><?= $t('आख़िरी बार', 'Last used') ?> <?= $h(date('d M Y, h:i A', strtotime((string)($d['last_used_at'] ?: $d['created_at'])))) ?> · IP <?= $h($d['ip_address']) ?></span></td>
                                <td class="amt"><form method="POST" action="/account/devices/forget"><?= $csrf ?><input type="hidden" name="id" value="<?= (int)$d['id'] ?>"><button class="sd-btn ghost" type="submit" style="padding:6px 12px;font-size:.85rem"><?= $t('हटाएँ', 'Remove') ?></button></form></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    <form method="POST" action="/account/devices/forget"><?= $csrf ?>
                        <button class="sd-btn" type="submit" style="background:#b91c1c;border-color:#b91c1c"><?= $tb('सभी डिवाइस भूल जाएँ', 'Forget all devices') ?></button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>
