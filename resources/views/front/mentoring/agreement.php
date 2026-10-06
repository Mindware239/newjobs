<?php
require __DIR__ . "/_shared.php";
use App\Services\Registration\Mentoring;

if (!$a): ?>
<div class="sd"><section class="sd-section"><div class="sd-wrap" style="max-width:700px"><div class="sd-card"><?= $t('यह समझौता नहीं मिला। अपने ईमेल का लिंक दोबारा खोलें।', 'This agreement was not found. Open the link from your email again.') ?> <a href="/mentoring"><?= $t('डैशबोर्ड', 'Dashboard') ?></a></div></div></section></div>
<?php return; endif;

$p = Mentoring::decode($provider);
$kind = $a['kind'] ?? 'skill';
$mySigned = $role === 'provider' ? $a['mentor_signed_at'] : $a['candidate_signed_at'];
$other = $role === 'provider' ? $seeker : $p;
$otherName = $role === 'provider' ? $seeker['full_name'] : Mentoring::displayName($p);
$when = static fn($v) => $v ? date('d M Y, h:i A', strtotime((string)$v)) : null;
$statusText = [
    'pending' => ['साइन बाकी', 'Awaiting signatures'], 'accepted' => ['पूरा – तीनों के साइन', 'Complete – signed by all three'],
    'declined' => ['अस्वीकार', 'Declined'], 'expired' => ['समय समाप्त', 'Expired'], 'cancelled' => ['रद्द (दूसरा समझौता पूरा हुआ)', 'Cancelled (another agreement was signed)'], 'ended' => ['समाप्त', 'Ended'],
][$a['status']] ?? [$a['status'], $a['status']];
?>
<style>
@media print { header, footer, .mt-noprint, .yi-band { display: none !important; } }
.sd .mt-sig { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
@media (max-width: 640px) { .sd .mt-sig { grid-template-columns: 1fr; } }
.sd .mt-sig div { border: 1px solid #e5e7eb; border-radius: 12px; padding: 10px 12px; }
.sd .mt-sig .ok { border-color: #138808; background: #f0fdf4; }
.sd .mt-otp { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.sd .mt-otp input[type=text] { height: 46px; width: 160px; font-size: 1.3rem; letter-spacing: .3em; text-align: center; border: 1px solid #d1d5db; border-radius: 10px; }
</style>
<div class="sd">
    <section class="sd-section mt-hero">
        <div class="sd-wrap" style="max-width:900px">
            <div class="mt-noprint" style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px">
                <a href="/mentoring">← <?= $t('मेरा डैशबोर्ड', 'My dashboard') ?></a><?php $sdLangSwitcher(); ?>
            </div>
            <?php $mtFlash($flash); ?>
            <div class="mt-box">
                <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
                    <span class="mt-tag g">JSA-<?= (int)$a['id'] ?> · v<?= $h($a['agreement_version'] ?: Mentoring::AGREEMENT_VERSION) ?></span>
                    <span class="mt-tag <?= $a['status'] === 'accepted' ? '' : 'w' ?>"><?= $tp($statusText) ?></span>
                </div>
                <h1 style="margin:8px 0"><?= $t('त्रिपक्षीय समझौता', 'Tripartite Agreement') ?> – <?= $tp(Mentoring::KINDS[$kind]['what']) ?></h1>
                <table class="mt-table"><tbody>
                    <tr><td style="width:34%;color:#4b5563"><?= $t('उम्मीदवार', 'Candidate') ?></td><td><b><?= $h($seeker['full_name']) ?></b> (<?= $h($seeker['reg_no']) ?>) · <?= $h(implode(', ', array_filter([$seeker['city'], $seeker['state']]))) ?></td></tr>
                    <tr><td style="color:#4b5563"><?= $tp(Mentoring::KINDS[$kind]['providers_label']) ?></td><td><b><?= $h(Mentoring::displayName($p)) ?></b><?= Mentoring::displayName($p) !== $p['full_name'] ? ' – ' . $h($p['full_name']) : '' ?> (<?= $h($p['reg_no']) ?>) · <?= $h(implode(', ', array_filter([$p['city'], $p['state']]))) ?></td></tr>
                    <tr><td style="color:#4b5563"><?= $t('सुविधा देने वाला', 'Facilitator') ?></td><td><b>Jobsence</b> – <?= $h(Mentoring::legalName()) ?>, <?= $h(Mentoring::legalAddress()) ?> · gm@jobsence.com</td></tr>
                    <tr><td style="color:#4b5563"><?= $kind === 'skill' ? $t('स्किल', 'Skill') : ($kind === 'nearme' ? $t('सेवा', 'Service') : $t('पद / डोमेन', 'Role / domain')) ?></td><td><b><?= $h($a['skill']) ?></b><?= $a['mode'] === 'offline_ncr' ? ' · ' . $t('ऑफलाइन (दिल्ली-NCR)', 'Offline (Delhi-NCR)') : ($a['mode'] === 'online' ? ' · ' . $t('ऑनलाइन', 'Online') : '') ?></td></tr>
                </tbody></table>

                <h2 style="font-size:1.05rem;margin:16px 0 6px"><?= $t('शर्तें', 'Terms') ?> <small style="font-weight:600"><?= $t('संस्करण', 'version') ?> <?= $h($a['agreement_version'] ?: '2026-10-v1') ?></small></h2>
                <ol style="padding-left:20px;margin:0">
                    <?php foreach (Mentoring::clauses($kind, (string)$a['skill'], $a['agreement_version'] ?: '2026-10-v1') as $c): ?><li style="margin:4px 0"><?= $tp($c) ?></li><?php endforeach; ?>
                </ol>

                <h2 style="font-size:1.05rem;margin:16px 0 6px"><?= $t('साइन', 'Signatures') ?></h2>
                <div class="mt-sig">
                    <?php foreach ([[$t('उम्मीदवार', 'Candidate'), $a['candidate_signed_at']], [$t('प्रदाता', 'Provider'), $a['mentor_signed_at']], ['Jobsence', $a['jobsence_signed_at']]] as [$who, $at]): ?>
                        <div class="<?= $at ? 'ok' : '' ?>"><b><?= $who ?></b><br><?= $at ? '✅ ' . $h($when($at)) . ($who === 'Jobsence' ? '' : ' · OTP') : '⏳ ' . $t('बाकी', 'Pending') ?></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($a['status'] === 'accepted'): ?>
                <div class="mt-box" style="border:2px solid #138808">
                    <h2 style="font-size:1.15rem;margin-top:0">📞 <?= $t('संपर्क', 'Contact') ?> – <?= $h($otherName) ?></h2>
                    <table class="mt-table"><tbody>
                        <tr><td style="width:34%;color:#4b5563"><?= $t('मोबाइल', 'Mobile') ?></td><td><a href="tel:+91<?= $h($other['mobile']) ?>"><b><?= $h($other['mobile']) ?></b></a> · <a href="https://wa.me/91<?= $h($other['whatsapp'] ?: $other['mobile']) ?>" target="_blank" rel="noopener">WhatsApp</a></td></tr>
                        <tr><td style="color:#4b5563"><?= $t('ईमेल', 'Email') ?></td><td><a href="mailto:<?= $h($other['email']) ?>"><?= $h($other['email']) ?></a></td></tr>
                        <tr><td style="color:#4b5563"><?= $t('पता', 'Address') ?></td><td><?= $h(implode(', ', array_filter([$other['details']['address_line'] ?? '', $other['details']['village'] ?? '', $other['city'], $other['district'], $other['state'], $other['pincode']]))) ?></td></tr>
                    </tbody></table>
                    <p class="mt-meta" style="margin:8px 0 0"><?= $t('ये जानकारी केवल इसी उद्देश्य के लिए है।', 'Use these details only for this purpose.') ?></p>
                </div>
                <div class="mt-box mt-noprint">
                    <button type="button" class="sd-btn ghost" onclick="window.print()"><?= $tb('समझौता प्रिंट / PDF करें', 'Print / save as PDF') ?></button>
                    <details style="margin-top:12px">
                        <summary style="cursor:pointer;font-weight:800;color:#b91c1c"><?= $role === 'seeker' ? $t('मेंटर / प्रदाता को छोड़ें', 'Reject my mentor / provider') : $t('समझौता समाप्त करें', 'End this agreement') ?></summary>
                        <form method="POST" action="/mentoring/agreement/<?= $h($token) ?>/end" style="margin-top:8px">
                            <?= $mtCsrf() ?>
                            <?php if ($role === 'seeker'): ?><p class="sd-alert err" style="margin:0 0 8px"><?= $t('ध्यान दें: छोड़ने पर आपका रजिस्ट्रेशन बंद हो जाएगा और नए मेंटर / इंटर्नशिप के लिए नया फॉर्म शुल्क देना होगा।', 'Note: if you reject, your registration closes and a new form fee is needed for a new mentor / internship.') ?></p><?php endif; ?>
                            <textarea name="reason" rows="2" maxlength="500" style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:8px" placeholder="Reason / कारण"></textarea>
                            <label style="display:block;margin:6px 0"><input type="checkbox" name="confirm" value="1"> <?= $t('मैं समझता/समझती हूँ और समाप्त करना चाहता/चाहती हूँ', 'I understand and want to end it') ?></label>
                            <button class="sd-btn" style="background:#b91c1c;border-color:#b91c1c" type="submit"><?= $tb('समाप्त करें', 'End agreement') ?></button>
                        </form>
                    </details>
                </div>
            <?php elseif ($a['status'] === 'pending'): ?>
                <div class="mt-box mt-noprint" id="sign">
                    <?php if ($mySigned): ?>
                        <p style="margin:0">✅ <?= $t('आपने साइन कर दिया है। दूसरे पक्ष के साइन का इंतज़ार है – पूरा होते ही आपको ईमेल मिलेगा।', 'You have signed. Waiting for the other party – you will get an email once it is complete.') ?></p>
                    <?php elseif ($blocker): ?>
                        <p class="sd-alert err" style="margin:0 0 10px"><?= $tp($blocker) ?></p>
                        <?php if ($role === 'provider' && !Mentoring::activePlan($provider) && Mentoring::isVerified($provider)): ?>
                            <?php if (Mentoring::planOptions($provider)): ?><a class="sd-btn mt-green" href="/mentoring#plan"><?= $tb('भर्ती प्लान चुनें – फिर यहीं साइन करें', 'Choose a hiring plan – then sign here') ?></a><?php else: ?>
                            <form method="POST" action="/mentoring/plan"><?= $mtCsrf() ?><button class="sd-btn mt-green" type="submit"><?php $pp = Mentoring::planPrice($provider); ?><?= $tb("{$pp} प्लान लें – फिर यहीं साइन करें", "Get the {$pp} plan – then sign here") ?></button></form><?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <h2 style="font-size:1.1rem;margin-top:0"><?= $t('ईमेल OTP से साइन करें', 'Sign with an email OTP') ?></h2>
                        <form method="POST" action="/mentoring/agreement/<?= $h($token) ?>/otp" style="margin-bottom:10px">
                            <?= $mtCsrf() ?>
                            <button class="sd-btn ghost" type="submit"><?= $otpSent ? $tb('OTP दोबारा भेजें', 'Resend OTP') : $tb('1. मेरे ईमेल पर OTP भेजें', '1. Send OTP to my email') ?></button>
                            <span class="mt-meta"><?= $h(preg_replace('/(^.).*(@.*$)/', '$1•••$2', (string)($role === 'provider' ? $provider['email'] : $seeker['email']))) ?></span>
                        </form>
                        <form method="POST" action="/mentoring/agreement/<?= $h($token) ?>/sign">
                            <?= $mtCsrf() ?>
                            <label style="display:block;margin:0 0 8px"><input type="checkbox" name="agree" value="1" required> <?= $t('मैंने ऊपर की सभी शर्तें पढ़ ली हैं और स्वीकार करता/करती हूँ।', 'I have read and accept all the terms above.') ?></label>
                            <div class="mt-otp">
                                <input type="text" name="otp" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" placeholder="••••••" required aria-label="OTP">
                                <button class="sd-btn mt-green" type="submit"><?= $tb('2. साइन करें', '2. Sign agreement') ?></button>
                            </div>
                        </form>
                    <?php endif; ?>
                    <form method="POST" action="/mentoring/agreement/<?= $h($token) ?>/decline" style="margin-top:12px">
                        <?= $mtCsrf() ?><button class="sd-btn ghost" type="submit" style="font-size:.85rem;padding:6px 12px"><?= $tb('अनुरोध अस्वीकार करें', 'Decline this request') ?></button>
                        <span class="mt-meta"><?= $t('समझौते से पहले अस्वीकार करने पर कोई शुल्क नहीं।', 'Declining before signing costs nothing.') ?></span>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
