<?php
/** /resume-boost – job seekers bid to show their resume in employers' Top Candidates (TopPlacesController::resume). */
require dirname(__DIR__) . '/apply/_partials.php';
use App\Services\TopPlaces\TopBidding as TB;

$rs = static fn($n) => '₹' . number_format((float)$n, 0);
$withGst = static fn($n) => '₹' . number_format(TB::withGst((float)$n), 2);
$csrf = $h($_SESSION['csrf_token'] ?? '');
?>
<div class="sd">
    <section class="sd-section" style="background:linear-gradient(135deg,#eff6ff,#ecfdf5)">
        <div class="sd-wrap" style="max-width:900px">
            <div style="display:flex;justify-content:flex-end;margin-bottom:8px"><?php $sdLangSwitcher(); ?></div>
            <h1 style="font-size:clamp(1.5rem,3.2vw,2.1rem);font-weight:900;margin:0 0 6px">🚀 <?= $tb('रिज़्यूमे बूस्ट – कंपनियों को सबसे पहले दिखें', 'Resume Boost – be seen by employers first') ?></h1>
            <p class="sd-lead"><?= $t(
                'आपका रिज़्यूमे कंपनियों के डैशबोर्ड पर “टॉप कैंडिडेट्स” में सबसे ऊपर दिखेगा – ' . TB::RESUME_DAYS . ' दिन तक, अधिकतम ' . TB::RESUME_MAX_VIEWS . ' कंपनियों को।',
                'Your resume appears at the top of “Top Candidates” on employers\' dashboards – for ' . TB::RESUME_DAYS . ' days, shown to up to ' . TB::RESUME_MAX_VIEWS . ' employers.'
            ) ?></p>
            <ul style="margin:0;padding-left:18px;font-size:.92rem">
                <li><?= $t('हर नौकरी कैटेगरी में टॉप ' . TB::RESUME_SLOTS . ' जगहें – बोली ₹500 से, हर बार ₹500 बढ़ाकर (+18% GST)', 'Top ' . TB::RESUME_SLOTS . ' places per job category – bids from ₹500, in steps of ₹500 (+18% GST)') ?></li>
                <li><?= $t('बोली केवल सुबह 10 से शाम 6 बजे तक; अवधि शुरू होने से एक दिन पहले शाम 6 बजे बंद', 'Bidding only 10 AM – 6 PM; closes at 6 PM the day before the period starts') ?></li>
                <li><?= $t('जीतने पर रात 11 बजे तक भुगतान करें – हारने पर कोई पैसा नहीं। Jobsence की बाकी सेवाएँ नौकरी ढूँढने वालों के लिए मुफ़्त हैं।', 'Pay by 11 PM if you win – nothing to pay if you don\'t. All other Jobsence services stay free for job seekers.') ?></li>
            </ul>
            <?php if ($flash): ?><div class="sd-alert <?= $flash[0] ? 'ok' : 'err' ?>" role="status" style="margin-top:12px"><?= $h($flash[1]) ?></div><?php endif; ?>
        </div>
    </section>

    <section class="sd-section">
        <div class="sd-wrap" style="max-width:900px">
            <?php if (!$candidate): ?>
                <div class="sd-card" style="text-align:center"><b><?= $t('बोली लगाने के लिए जॉब सीकर खाते से लॉगिन करें।', 'Log in with a job seeker account to bid.') ?></b>
                    <div class="sd-cta-row" style="justify-content:center;margin-top:10px"><a class="sd-btn" href="/login/job-seeker?redirect=%2Fresume-boost"><?= $t('जॉब सीकर लॉगिन', 'Job seeker login') ?></a></div></div>
            <?php elseif (empty($candidate['resume_url'])): ?>
                <div class="sd-alert err"><?= $t('पहले अपनी प्रोफ़ाइल में रिज़्यूमे अपलोड करें।', 'Upload your resume in your profile first.') ?> <a href="/candidate/profile"><?= $t('प्रोफ़ाइल', 'Profile') ?> →</a></div>
            <?php endif; ?>

            <form method="GET" action="/resume-boost" style="display:flex;gap:8px;flex-wrap:wrap;align-items:end;margin:14px 0">
                <label style="flex:1;min-width:220px"><b><?= $t('आपकी नौकरी कैटेगरी', 'Your job category') ?></b>
                    <select name="category" class="sd-input" onchange="this.form.submit()">
                        <option value=""><?= $t('चुनें…', 'Choose…') ?></option>
                        <?php foreach ($categories as $c): ?><option value="<?= $h($c) ?>" <?= $c === $category ? 'selected' : '' ?>><?= $h($c) ?></option><?php endforeach; ?>
                    </select></label>
                <noscript><button class="sd-btn ghost" type="submit"><?= $t('देखें', 'Show') ?></button></noscript>
            </form>

            <?php if ($category !== ''): ?>
                <h2 id="bid" style="font-size:1.2rem"><?= $h($category) ?> · <span style="font-size:.85rem;color:<?= $open ? '#047857' : '#b91c1c' ?>"><?= $open ? $t('बोली अभी खुली है', 'bidding is open now') : $t('बोली बंद – सुबह 10 बजे खुलेगी', 'bidding closed – opens at 10 AM') ?></span></h2>
                <div style="display:grid;gap:10px">
                    <?php foreach ($auctions as $start => $a): ?>
                        <div class="sd-card" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between">
                            <div><b><?= $h(date('d M', strtotime($start))) ?> – <?= $h(date('d M Y', strtotime($a['end']))) ?></b>
                                <div style="font-size:.85rem;color:#4b5563"><?= count($a['bids']) ?> <?= $t('बोलियाँ', 'bids') ?> · <b><?= $t('जगह के लिए कम से कम', 'to get a place now') ?>: <?= $rs($a['min']) ?></b> + GST</div></div>
                            <?php if ($candidate && !empty($candidate['resume_url'])): ?>
                                <form method="POST" action="/resume-boost/bid" style="display:flex;gap:6px;align-items:center">
                                    <input type="hidden" name="_token" value="<?= $csrf ?>"><input type="hidden" name="date" value="<?= $h($start) ?>"><input type="hidden" name="category" value="<?= $h($category) ?>">
                                    <select name="amount" class="sd-input" style="width:auto" aria-label="Bid amount" <?= $open ? '' : 'disabled' ?>>
                                        <?php for ($v = max(TB::MIN_BID, $a['min']); $v <= max(TB::MIN_BID, $a['min']) + 10 * TB::STEP; $v += TB::STEP): ?>
                                            <option value="<?= $v ?>"><?= $rs($v) ?> + GST = <?= $withGst($v) ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <button class="sd-btn" type="submit" <?= $open ? '' : 'disabled' ?>><?= $t('बोली लगाएँ', 'Place bid') ?></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($myBids): ?>
                <h2 style="font-size:1.15rem;margin-top:18px"><?= $tb('आपकी बोलियाँ', 'Your bids') ?></h2>
                <table class="sd-table">
                    <tr><th><?= $t('अवधि', 'Period') ?></th><th><?= $t('कैटेगरी', 'Category') ?></th><th><?= $t('बोली', 'Bid') ?></th><th><?= $t('स्थिति', 'Status') ?></th><th></th></tr>
                    <?php foreach ($myBids as $b): ?>
                        <tr><td><?= $h(date('d M', strtotime((string)$b['slot_date']))) ?> – <?= $h(date('d M Y', strtotime(TB::periodEnd((string)$b['slot_date'])))) ?></td><td><?= $h($b['category']) ?></td><td><?= $rs($b['amount']) ?> + GST</td>
                            <td><?= $b['status'] === 'paid' ? $h('Active ✅ – seen by ' . $b['views'] . ' of ' . TB::RESUME_MAX_VIEWS . ' employers')
                                : $h(['active' => 'Bidding open', 'won' => 'Won – pay before ' . date('d M, h:i A', strtotime((string)$b['pay_deadline'])), 'waiting' => 'Next in line', 'lapsed' => 'Not paid in time', 'lost' => 'Not won', 'cancelled' => 'Cancelled'][$b['status']] ?? $b['status']) ?></td>
                            <td><?php if ($b['status'] === 'won'): ?><a class="sd-btn" style="padding:4px 12px" href="/top-bid/pay/<?= (int)$b['id'] ?>"><?= $t('भुगतान', 'Pay') ?> <?= $withGst($b['amount']) ?></a><?php endif; ?></td></tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
            <p style="font-size:.82rem;color:#6b7280;margin-top:14px"><?= $t('भुगतान वापसी योग्य नहीं। बूस्ट नौकरी की गारंटी नहीं है।', 'Payments are non-refundable. A boost is not a job guarantee.') ?></p>
        </div>
    </section>
</div>
