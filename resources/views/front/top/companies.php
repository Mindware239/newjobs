<?php
/** /top-places – companies: daily bidding, place 1 for a month, logo right (TopPlacesController::companies). */
require dirname(__DIR__) . '/apply/_partials.php';
use App\Services\TopPlaces\TopBidding as TB;

$rs = static fn($n) => '₹' . number_format((float)$n, 0);
$withGst = static fn($n) => '₹' . number_format(TB::withGst((float)$n), 2);
$csrf = $h($_SESSION['csrf_token'] ?? '');
?>
<div class="sd">
    <section class="sd-section" style="background:linear-gradient(135deg,#fff7ed,#fef3c7)">
        <div class="sd-wrap" style="max-width:900px">
            <div style="display:flex;justify-content:flex-end;margin-bottom:8px"><?php $sdLangSwitcher(); ?></div>
            <h1 style="font-size:clamp(1.5rem,3.2vw,2.1rem);font-weight:900;margin:0 0 6px">🏆 <?= $tb('टॉप हायरिंग कंपनियाँ – सबसे ऊपर दिखें', 'Top Hiring Companies – be seen first') ?></h1>
            <p class="sd-lead"><?= $t(
                'आपकी कंपनी होमपेज और नौकरियों के पेज पर सबसे ऊपर (टॉप ' . TB::COMPANY_SLOTS . ') दिखेगी। हर दिन की जगह के लिए बोली लगाएँ, या बिना बोली के एक महीने के लिए पहला स्थान लें।',
                'Your company appears first (top ' . TB::COMPANY_SLOTS . ') on the homepage and the Jobs page. Bid for each day, or take place 1 for a whole month without bidding.'
            ) ?></p>
            <ul style="margin:0;padding-left:18px;font-size:.92rem">
                <li><?= $t('बोली ₹500 से, हर बार ₹500 बढ़ाकर – हर दिन के लिए अलग नीलामी (+18% GST)', 'Bids from ₹500, in steps of ₹500 – a separate auction for every day (+18% GST)') ?></li>
                <li><?= $t('बोली केवल सुबह 10 से शाम 6 बजे तक; किसी दिन की बोली एक दिन पहले शाम 6 बजे बंद', 'Bidding only 10 AM – 6 PM; the auction for a day closes at 6 PM the day before') ?></li>
                <li><?= $t('जीतने वाले रात 11 बजे तक भुगतान करें, वरना जगह अगली बोली को – हारने वालों से कोई पैसा नहीं', 'Winners pay by 11 PM, otherwise the place goes to the next bid – losing bidders pay nothing') ?></li>
                <li><?= $t('बिना बोली: पहला स्थान 30 दिन के लिए ' . $rs(TB::MONTH_PRICE) . ' + GST', 'No bidding: place 1 for 30 days for ' . $rs(TB::MONTH_PRICE) . ' + GST') ?></li>
                <li><?= $t('विज्ञापन में लोगो: एक बार ' . $rs(TB::LOGO_PRICE) . ' + GST', 'Logo in your top places: ' . $rs(TB::LOGO_PRICE) . ' + GST, once') ?></li>
            </ul>
            <?php if ($flash): ?><div class="sd-alert <?= $flash[0] ? 'ok' : 'err' ?>" role="status" style="margin-top:12px"><?= $h($flash[1]) ?></div><?php endif; ?>
        </div>
    </section>

    <section class="sd-section">
        <div class="sd-wrap" style="max-width:900px">
            <?php if (!$employer): ?>
                <div class="sd-card" style="text-align:center">
                    <b><?= $t('बोली लगाने के लिए एम्प्लॉयर खाते से लॉगिन करें।', 'Log in with an employer account to bid.') ?></b>
                    <div class="sd-cta-row" style="justify-content:center;margin-top:10px">
                        <a class="sd-btn" href="/login/employer?redirect=%2Ftop-places"><?= $t('एम्प्लॉयर लॉगिन', 'Employer login') ?></a>
                        <a class="sd-btn ghost" href="/register-employer"><?= $t('मुफ़्त एम्प्लॉयर खाता', 'Free employer account') ?></a>
                    </div>
                </div>
            <?php endif; ?>

            <h2 id="bid" style="font-size:1.2rem;margin-top:18px">📅 <?= $tb('दिन के अनुसार बोली', 'Bid for a day') ?>
                <span style="font-size:.85rem;font-weight:700;color:<?= $open ? '#047857' : '#b91c1c' ?>"> · <?= $open ? $t('बोली अभी खुली है', 'bidding is open now') : $t('बोली बंद – सुबह 10 बजे खुलेगी', 'bidding closed – opens at 10 AM') ?></span></h2>
            <div style="display:grid;gap:10px">
                <?php foreach ($auctions as $date => $a): ?>
                    <div class="sd-card" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between">
                        <div>
                            <b><?= $h(date('D, d M Y', strtotime($date))) ?></b>
                            <div style="font-size:.85rem;color:#4b5563">
                                <?= $a['slots'] ?> <?= $t('जगहें', 'places') ?><?= $a['month'] ? ' (' . $t('पहला स्थान मासिक बुक', 'place 1 booked monthly') . ')' : '' ?> ·
                                <?= count($a['bids']) ?> <?= $t('बोलियाँ', 'bids') ?><?= $a['bids'] ? ' · ' . $t('सबसे ऊँची', 'highest') . ' ' . $rs(max($a['bids'])) : '' ?> ·
                                <b><?= $t('जगह के लिए कम से कम', 'to get a place now') ?>: <?= $rs($a['min']) ?></b> + GST
                            </div>
                        </div>
                        <?php if ($employer): ?>
                            <form method="POST" action="/top-places/bid" style="display:flex;gap:6px;align-items:center">
                                <input type="hidden" name="_token" value="<?= $csrf ?>"><input type="hidden" name="date" value="<?= $h($date) ?>">
                                <label class="sr-only" for="amt-<?= $h($date) ?>"><?= $t('बोली राशि', 'Bid amount') ?></label>
                                <select id="amt-<?= $h($date) ?>" name="amount" class="sd-input" style="width:auto" <?= $open ? '' : 'disabled' ?>>
                                    <?php for ($v = max(TB::MIN_BID, $a['min']); $v <= max(TB::MIN_BID, $a['min']) + 20 * TB::STEP; $v += TB::STEP): ?>
                                        <option value="<?= $v ?>"><?= $rs($v) ?> + GST = <?= $withGst($v) ?></option>
                                    <?php endfor; ?>
                                </select>
                                <button class="sd-btn" type="submit" <?= $open ? '' : 'disabled' ?>><?= $t('बोली लगाएँ', 'Place bid') ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($employer): ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;margin-top:18px">
                    <div class="sd-card" id="month" style="border-top:5px solid #f05537">
                        <h3 style="margin:0 0 4px">🥇 <?= $t('पहला स्थान – पूरा महीना, बिना बोली', 'Place 1 – a whole month, no bidding') ?></h3>
                        <p style="margin:0 0 8px;font-size:.9rem"><?= $rs(TB::MONTH_PRICE) ?> + GST = <b><?= $withGst(TB::MONTH_PRICE) ?></b> · <?= TB::MONTH_DAYS ?> <?= $t('दिन', 'days') ?></p>
                        <?php if ($monthStart): ?>
                            <form method="POST" action="/top-places/month">
                                <input type="hidden" name="_token" value="<?= $csrf ?>"><input type="hidden" name="start" value="<?= $h($monthStart) ?>">
                                <p style="margin:0 0 8px;font-size:.88rem"><?= $t('शुरुआत', 'Starts') ?>: <b><?= $h(date('d M Y', strtotime($monthStart))) ?></b> → <?= $h(date('d M Y', strtotime($monthStart . ' +' . (TB::MONTH_DAYS - 1) . ' day'))) ?></p>
                                <button class="sd-btn" type="submit"><?= $t('भुगतान करें और बुक करें', 'Pay and book') ?></button>
                            </form>
                        <?php else: ?>
                            <p style="margin:0;font-size:.88rem;color:#b91c1c"><?= $t('अगले कुछ दिनों के लिए पहला स्थान बुक है – बाद में देखें।', 'Place 1 is booked for the coming days – please check again later.') ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="sd-card" style="border-top:5px solid #059669">
                        <h3 style="margin:0 0 4px">🖼️ <?= $t('विज्ञापन में आपका लोगो', 'Your logo in the top places') ?></h3>
                        <?php if ($hasLogo): ?>
                            <p style="margin:0;font-size:.9rem;color:#047857"><b>✅ <?= $t('लोगो सक्रिय है', 'Logo is active') ?></b> – <?= $t('आपकी कंपनी प्रोफ़ाइल का लोगो दिखता है।', 'the logo from your company profile is shown.') ?></p>
                        <?php else: ?>
                            <p style="margin:0 0 8px;font-size:.9rem"><?= $rs(TB::LOGO_PRICE) ?> + GST = <b><?= $withGst(TB::LOGO_PRICE) ?></b>, <?= $t('एक बार – सभी टॉप जगहों में', 'once – in all your top places') ?>.
                                <?= empty($employer['logo_url']) ? '<br><span style="color:#b45309">' . $t('पहले कंपनी प्रोफ़ाइल में लोगो अपलोड करें।', 'Upload a logo in your company profile first.') . '</span>' : '' ?></p>
                            <form method="POST" action="/top-places/logo"><input type="hidden" name="_token" value="<?= $csrf ?>">
                                <button class="sd-btn ghost" type="submit"><?= $t('लोगो के लिए भुगतान करें', 'Pay for logo') ?></button></form>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($myBids): ?>
                    <h2 style="font-size:1.15rem;margin-top:18px"><?= $tb('आपकी बोलियाँ', 'Your bids') ?></h2>
                    <table class="sd-table">
                        <tr><th><?= $t('दिन', 'Day') ?></th><th><?= $t('बोली', 'Bid') ?></th><th><?= $t('स्थिति', 'Status') ?></th><th></th></tr>
                        <?php foreach ($myBids as $b): ?>
                            <tr><td><?= $h(date('d M Y', strtotime((string)$b['slot_date']))) ?></td><td><?= $rs($b['amount']) ?> + GST</td>
                                <td><?= $h(['active' => 'Bidding open', 'won' => 'Won – pay before ' . date('d M, h:i A', strtotime((string)$b['pay_deadline'])), 'waiting' => 'Next in line', 'paid' => 'Paid ✅', 'lapsed' => 'Not paid in time', 'lost' => 'Not won', 'cancelled' => 'Cancelled'][$b['status']] ?? $b['status']) ?></td>
                                <td><?php if ($b['status'] === 'won'): ?><a class="sd-btn" style="padding:4px 12px" href="/top-bid/pay/<?= (int)$b['id'] ?>"><?= $t('भुगतान', 'Pay') ?> <?= $withGst($b['amount']) ?></a><?php endif; ?></td></tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            <?php endif; ?>
            <p style="font-size:.82rem;color:#6b7280;margin-top:14px"><?= $t('भुगतान वापसी योग्य नहीं। Jobsence सरकारी योजना नहीं है।', 'Payments are non-refundable. Jobsence is a private job portal, not a Government scheme.') ?></p>
        </div>
    </section>
</div>
