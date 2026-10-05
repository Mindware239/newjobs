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
