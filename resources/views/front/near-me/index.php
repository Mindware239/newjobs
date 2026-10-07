<?php
require dirname(__DIR__) . '/apply/_partials.php';
use App\Services\Registration\ContactPass;

$closeLabel = [4 => ['आपके पिन कोड में', 'In your PIN code'], 3 => ['बहुत पास', 'Very near'], 2 => ['पास के इलाके में', 'Nearby area'], 1 => ['आपके क्षेत्र में', 'In your region']];
$feeLabel = '₹' . number_format((float)$fee, 0);
$seekerFeeLabel = '₹' . number_format((float)($seekerFee ?? 295), 0);
$unlocked = $pass !== null;
?>
<style>
.sd .nm-hero { background: linear-gradient(135deg, #ecfdf5 0%, #fff 70%); border-bottom: 1px solid #e5e7eb; }
.sd .nm-hero h1 { font-size: clamp(1.7rem, 4.5vw, 2.5rem); font-weight: 900; margin: 0 0 6px; line-height: 1.15; }
.sd .nm-cta { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px; margin: 18px 0; }
.sd .nm-cta a { display: flex; gap: 12px; align-items: center; padding: 16px; border-radius: 16px; background: #fff; border: 2px solid #e5e7eb; text-decoration: none; color: #111827; }
.sd .nm-cta a:hover { border-color: #f05537; }
.sd .nm-cta .ico { font-size: 2rem; }
.sd .nm-cta small { display: block; color: #6b7280; font-weight: 600; }
.sd .nm-search2 { display: grid; grid-template-columns: 1.4fr 130px auto 1fr 1fr auto; gap: 8px; align-items: end; background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 12px; box-shadow: 0 6px 18px rgba(0, 0, 0, .05); }
.sd .nm-f { display: flex; flex-direction: column; gap: 4px; min-width: 0; font-size: .8rem; font-weight: 800; color: #374151; }
.sd .nm-f input, .sd .nm-f select { padding: 11px 12px; border: 1px solid #d1d5db; border-radius: 10px; font: inherit; font-size: 1rem; font-weight: 600; min-width: 0; width: 100%; background: #fff; height: 46px; box-sizing: border-box; }
.sd .nm-or { align-self: center; padding-top: 18px; font-weight: 900; color: #9ca3af; }
.sd .nm-search2 .sd-btn { height: 46px; padding-top: 0; padding-bottom: 0; }
.sd .nm-tip { margin: 6px 0 0; font-size: .85rem; color: #6b7280; }
.sd .nm-geo { grid-column: 1 / -1; display: flex; gap: 10px; flex-wrap: wrap; align-items: end; border-top: 1px dashed #e5e7eb; padding-top: 10px; }
.sd .nm-geo .sd-btn { height: auto; min-height: 46px; padding-top: 6px; padding-bottom: 6px; line-height: 1.25; }
.sd .nm-tag.d { background: #ecfeff; color: #0e7490; }
.sd .nm-all { margin-top: 8px; font-size: .85rem; }
.sd .nm-all summary { cursor: pointer; font-weight: 800; color: #047857; }
@media (max-width: 1000px) { .sd .nm-search2 { grid-template-columns: 1fr 1fr; } .sd .nm-q { grid-column: 1 / -1; } .sd .nm-or { display: none; } .sd .nm-search2 .sd-btn { grid-column: 1 / -1; } }
@media (max-width: 480px) { .sd .nm-search2 { grid-template-columns: 1fr; } }
.sd .nm-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
.sd .nm-chips a { padding: 5px 11px; border-radius: 999px; background: #fff; border: 1px dashed #059669; color: #047857; font-size: .82rem; font-weight: 700; text-decoration: none; }
.sd .nm-chips a.on, .sd .nm-chips a:hover { background: #059669; color: #fff; }
.sd .nm-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 14px; }
@media (max-width: 400px) { .sd .nm-list { grid-template-columns: 1fr; } }
.sd .nm-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 14px; display: flex; gap: 12px; }
.sd .nm-card img, .sd .nm-card .nm-ph { width: 76px; height: 76px; border-radius: 14px; object-fit: cover; flex: none; background: #f3f4f6; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; }
.sd .nm-card h2 { font-size: 1.02rem; margin: 0; }
.sd .nm-card .meta { color: #4b5563; font-size: .86rem; margin-top: 2px; }
.sd .nm-tag { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: .72rem; font-weight: 800; background: #ecfdf5; color: #047857; margin-right: 4px; }
.sd .nm-tag.v { background: #dbeafe; color: #1e40af; }
.sd .nm-contact { margin-top: 8px; display: flex; gap: 6px; flex-wrap: wrap; }
.sd .nm-contact a { padding: 6px 10px; border-radius: 8px; font-weight: 800; font-size: .85rem; text-decoration: none; }
.sd .nm-call { background: #f05537; color: #fff; }
.sd .nm-wa { background: #16a34a; color: #fff; }
.sd .nm-locked { margin-top: 8px; font-size: .85rem; color: #6b7280; }
.sd .nm-time { color: #047857; font-weight: 700; }
.sd .nm-tag.p { background: #fff1ed; color: #c2410c; }
.sd .nm-tag.l { background: #f5f3ff; color: #6d28d9; }
.sd .nm-tag { margin-bottom: 3px; }
.sd .nm-stars { font-size: .85rem; margin: 2px 0; }
.sd .nm-stars .s, .sd .nm-review .s { color: #f59e0b; letter-spacing: 1px; }
.sd .nm-stars .n, .sd .nm-review .n { color: #6b7280; }
.sd .nm-review { font-size: .82rem; color: #374151; margin-top: 6px; background: #f9fafb; border-radius: 8px; padding: 6px 8px; }
.sd .nm-rate { margin-top: 8px; font-size: .85rem; }
.sd .nm-rate summary { cursor: pointer; font-weight: 800; color: #f05537; }
.sd .nm-rate textarea { width: 100%; margin: 6px 0; padding: 8px; border: 1px solid #d1d5db; border-radius: 8px; font: inherit; }
.sd .nm-starpick { display: inline-flex; flex-direction: row-reverse; gap: 2px; }
.sd .nm-starpick input { position: absolute; opacity: 0; width: 1px; height: 1px; }
.sd .nm-starpick label { font-size: 1.6rem; color: #d1d5db; cursor: pointer; line-height: 1; }
.sd .nm-starpick input:checked ~ label, .sd .nm-starpick label:hover, .sd .nm-starpick label:hover ~ label { color: #f59e0b; }
.sd .nm-starpick input:focus-visible + label { outline: 2px solid #f05537; border-radius: 4px; }
</style>
<div class="sd">
    <section class="sd-section nm-hero">
        <div class="sd-wrap">
            <div style="display:flex;justify-content:flex-end;margin-bottom:10px"><?php $sdLangSwitcher(); ?></div>
            <h1>📍 <?= $t('मेरे पास सेवा – Near Me', 'Services Near Me') ?></h1>
            <p style="color:#4b5563;margin:0;max-width:760px"><?= $t('प्लंबर, इलेक्ट्रीशियन, कारपेंटर, वेल्डर, पेंटर, हेयर ड्रेसर, सैलून और भी बहुत कुछ – अपने पिन कोड के पास। सभी सेवा प्रदाता फोटो और लाइव सेल्फ़ी से रजिस्टर होते हैं।', 'Plumbers, electricians, carpenters, welders, painters, hair dressers, salons and more – near your PIN code. Every provider registers with a photo and a live selfie.') ?></p>

            <div class="nm-cta">
                <a href="/apply/near-me-seeker"><span class="ico" aria-hidden="true">🔎</span><span><b><?= $t('मुझे सेवा चाहिए', 'I need a service') ?></b><small><?= $t("{$seekerFeeLabel} (₹250 + GST) एक बार – 3 महीने तक प्रदाता चुनें; दोनों के साइन के बाद नंबर", "{$seekerFeeLabel} (₹250 + GST) once – choose providers for 3 months; number after both sign") ?></small></span></a>
                <a href="/apply/near-me-provider"><span class="ico" aria-hidden="true">🛠️</span><span><b><?= $t('मैं सेवा देता/देती हूँ – रजिस्टर करें', 'I provide a service – enrol') ?></b><small><?= $t("{$feeLabel} (₹500 + GST) एक बार – 6 महीने, किराना / दुकान / सेवा; फोटो + सेल्फ़ी ज़रूरी", "{$feeLabel} (₹500 + GST) once – 6 months, kirana / shop / service; photo + selfie required") ?></small></span></a>
                <a href="/apply/near-me-job-giver"><span class="ico" aria-hidden="true">🏪</span><span><b><?= $t('दुकान / किराना पर काम देना है', 'Hiring for your shop / kirana') ?></b><small><?= $t('₹590 (₹500 + GST) एक बार – 6 महीने, पास रहने वाले लोग मोबाइल नंबर के साथ', '₹590 (₹500 + GST) once – 6 months, people living nearby with mobile numbers') ?></small></span></a>
                <a href="/apply/near-me-job-seeker"><span class="ico" aria-hidden="true">🧑‍🔧</span><span><b><?= $t('घर के पास काम चाहिए', 'I want work near home') ?></b><small><?= $t('₹295 (₹250 + GST) एक बार – 3 महीने, पास की दुकानों का काम', '₹295 (₹250 + GST) once – 3 months, work in shops near you') ?></small></span></a>
            </div>

            <?php if (!empty($flash)): ?><div class="sd-alert ok" role="status"><?= $tp($flash) ?></div><?php endif; ?>
            <?php if ($unlocked): ?>
                <div class="sd-alert ok">✅ <?= $t('आपका Near Me पास सक्रिय है – ' . date('d M Y', strtotime((string)$pass['valid_until'])) . ' तक। नंबर और पता दिख रहे हैं।', 'Your Near Me pass is active till ' . date('d M Y', strtotime((string)$pass['valid_until'])) . '. Choose a provider and sign the agreement – the number appears once both have signed.') ?> <a href="/mentoring"><?= $t('मेरे अनुरोध', 'My requests') ?></a></div>
            <?php endif; ?>

            <form method="GET" class="nm-search2" style="margin-top:6px" id="nm-form">
                <label class="nm-f nm-q"><span><?= $t('कौन सी सेवा', 'Which service') ?></span>
                    <input type="search" name="q" value="<?= $h($q) ?>" placeholder="Plumber, Electrician, Salon…" list="nm-prof"></label>
                <label class="nm-f"><span><?= $t('पिन कोड', 'PIN code') ?></span>
                    <input type="text" name="pin" value="<?= $h($pin) ?>" inputmode="numeric" autocomplete="postal-code" maxlength="8" placeholder="110001"></label>
                <div class="nm-or" aria-hidden="true"><?= $t('या', 'or') ?></div>
                <label class="nm-f"><span><?= $t('राज्य', 'State') ?></span>
                    <select name="state" id="nm-state">
                        <option value=""><?= $h('— सभी / All —') ?></option>
                        <?php foreach (array_keys($places) as $st): ?><option value="<?= $h($st) ?>" <?= $state === $st ? 'selected' : '' ?>><?= $h($st) ?></option><?php endforeach; ?>
                    </select></label>
                <label class="nm-f"><span><?= $t('शहर / ज़िला', 'City / District') ?></span>
                    <input type="text" name="city" id="nm-city" value="<?= $h($city) ?>" list="nm-cities" placeholder="Patna, Pune…" autocomplete="address-level2"></label>
                <button class="sd-btn" type="submit"><?= $tb('पास में खोजें', 'Search near me') ?></button>
                <div class="nm-geo">
                    <button type="button" class="sd-btn ghost" id="nm-geo-btn">📍 <?= $t('मेरी लोकेशन से खोजें', 'Use my current location') ?></button>
                    <label class="nm-f" style="flex:1 1 240px"><span><?= $t('आपका पता / इलाका / कॉलोनी', 'Your address / area / colony') ?></span>
                        <input type="text" name="area" value="<?= $h($area) ?>" placeholder="Lajpat Nagar, Sector 18, Boring Road…" autocomplete="address-line2"></label>
                    <input type="hidden" name="lat" id="nm-lat" value="<?= $geo ? $h($geo[0]) : '' ?>"><input type="hidden" name="lng" id="nm-lng" value="<?= $geo ? $h($geo[1]) : '' ?>">
                    <span id="nm-geo-status" aria-live="polite" style="font-size:.85rem;color:#047857"><?= $geo ? $t('✓ आपकी लोकेशन से', '✓ Using your location') : '' ?></span>
                </div>
                <datalist id="nm-prof"><?php foreach ($popular as $p): ?><option value="<?= $h($p) ?>"><?php endforeach; ?></datalist>
                <datalist id="nm-cities"></datalist>
            </form>
            <p class="nm-tip"><?= $t('अपनी लोकेशन से खोजें, या पिन कोड / राज्य-शहर / अपना इलाका लिखें – पूरे भारत में।', 'Use your location, or enter a PIN code, state & city, or your area – anywhere in India.') ?></p>
            <?php if ($pinError): ?><p class="err" style="margin:6px 0 0"><?= $t('6 अंकों का सही पिन कोड भरें, या राज्य / शहर चुनें', 'Enter a valid 6-digit PIN code, or choose a state / city') ?></p><?php endif; ?>
            <div class="nm-chips">
                <?php foreach (array_slice($popular, 0, 16) as $p): ?><a class="<?= strcasecmp($q, $p) === 0 ? 'on' : '' ?>" href="?<?= $h(http_build_query(array_filter(['q' => $p, 'pin' => $pin, 'state' => $state, 'city' => $city]))) ?>"><?= $h($p) ?></a><?php endforeach; ?>
            </div>
            <details class="nm-all"><summary><?= $t('सभी ' . count($popular) . ' सेवाएँ देखें', 'All ' . count($popular) . ' services') ?></summary>
                <div class="nm-chips"><?php foreach (array_slice($popular, 16) as $p): ?><a class="<?= strcasecmp($q, $p) === 0 ? 'on' : '' ?>" href="?<?= $h(http_build_query(array_filter(['q' => $p, 'pin' => $pin, 'state' => $state, 'city' => $city]))) ?>"><?= $h($p) ?></a><?php endforeach; ?></div>
            </details>
            <script>
            (function () {
                var places = <?= json_encode(array_map(static fn($s) => array_values(array_unique(array_merge($s['districts'], $s['towns']))), $places), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
                var st = document.getElementById('nm-state'), dl = document.getElementById('nm-cities'), city = document.getElementById('nm-city'), pin = document.querySelector('#nm-form [name=pin]');
                function fill() {
                    var list = st.value ? (places[st.value] || []) : [].concat.apply([], Object.keys(places).map(function (k) { return places[k]; }));
                    dl.innerHTML = list.slice().sort().map(function (c) { return '<option value="' + c.replace(/"/g, '&quot;') + '">'; }).join('');
                }
                st.addEventListener('change', fill); fill();
                // PIN accepts spaces / dashes; only the digits are sent.
                document.getElementById('nm-form').addEventListener('submit', function () { pin.value = pin.value.replace(/\D/g, '').slice(0, 6); });
                // Use my current location: coordinates (distance ranking) + PIN / city / state from OpenStreetMap.
                var gs = document.getElementById('nm-geo-status');
                document.getElementById('nm-geo-btn').addEventListener('click', function () {
                    if (!navigator.geolocation) { gs.textContent = 'Location not supported – type your area / PIN'; return; }
                    gs.textContent = '…';
                    navigator.geolocation.getCurrentPosition(function (p) {
                        var lat = p.coords.latitude.toFixed(5), lng = p.coords.longitude.toFixed(5), form = document.getElementById('nm-form');
                        document.getElementById('nm-lat').value = lat; document.getElementById('nm-lng').value = lng;
                        fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&zoom=16&addressdetails=1&lat=' + lat + '&lon=' + lng, { headers: { 'Accept-Language': 'en' } })
                            .then(function (r) { return r.json(); })
                            .then(function (d) {
                                var a = (d && d.address) || {}, code = (a.postcode || '').replace(/\D/g, '').slice(0, 6);
                                if (code.length === 6) { pin.value = code; }
                                else { city.value = a.city || a.town || a.village || a.county || ''; if (a.state) { st.value = Object.prototype.hasOwnProperty.call(places, a.state) ? a.state : ''; } }
                                form.submit();
                            }).catch(function () { form.submit(); });
                    }, function () { gs.textContent = 'Location permission denied – type your area / PIN'; }, { enableHighAccuracy: true, timeout: 15000 });
                });
            })();
            </script>
        </div>
    </section>

    <section class="sd-section" style="padding-top:10px">
        <div class="sd-wrap">
            <?php if (!$searched): ?>
                <div class="sd-card" style="text-align:center"><b><?= $t('ऊपर पिन कोड डालें या राज्य / शहर चुनकर “पास में खोजें” दबाएँ।', 'Enter a PIN code above, or choose a state / city, and press “Search near me”.') ?></b></div>
            <?php elseif (!$results): ?>
                <div class="sd-card" style="text-align:center">
                    <b><?= $t('इस इलाके में अभी कोई सत्यापित सेवा प्रदाता नहीं मिला।', 'No verified service provider found in this area yet.') ?></b>
                    <p style="margin:6px 0 0;color:#4b5563"><?= $t('क्या आप यह सेवा देते हैं? ', 'Do you provide this service? ') ?><a href="/apply/near-me-provider"><?= $t('अभी रजिस्टर करें', 'Enrol now') ?></a></p>
                </div>
            <?php else: ?>
                <p style="margin:0 0 10px;color:#4b5563"><?= $t('आपके सबसे पास के ' . count($results) . ' सेवा प्रदाता (अधिकतम 5) – फोटो और समय के साथ', 'Your ' . count($results) . ' nearest service provider' . (count($results) > 1 ? 's' : '') . ' (up to 5) – with photo and timings') ?></p>
                <?php if (!$unlocked): ?>
                    <div class="ij-pass" style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;border:2px dashed #f05537;border-radius:14px;padding:14px;margin-bottom:14px;background:#fff">
                        <span><b><?= $t('मोबाइल नंबर और पूरा पता देखने के लिए Near Me पास लें', 'Get the Near Me pass to see mobile numbers & full addresses') ?></b><br><?= $t("{$seekerFeeLabel} (₹250 + GST) एक बार – 3 महीने", "{$seekerFeeLabel} (₹250 + GST) once – 3 months") ?></span>
                        <a class="sd-btn" href="/apply/near-me-seeker"><?= $tb('पास लें', 'Get the pass') ?></a>
                    </div>
                <?php endif; ?>
                <div class="nm-list">
                    <?php foreach ($results as $r): $d = $r['details']; $c = (int)$r['closeness']; ?>
                        <article class="nm-card">
                            <?php if (!empty($d['photo_path'])): ?>
                                <img src="/near-me/photo/<?= (int)$r['id'] ?>" alt="" loading="lazy">
                            <?php else: ?><div class="nm-ph" aria-hidden="true">🛠️</div><?php endif; ?>
                            <div style="min-width:0">
                                <div>
                                    <?php if (($r['distance'] ?? null) !== null): ?><span class="nm-tag d">📍 <?= $h(number_format((float)$r['distance'], 1)) ?> km</span><?php else: ?>
                                    <span class="nm-tag"><?= $tp($closeLabel[$c] ?? $closeLabel[1]) ?></span><?php endif; ?>
                                    <span class="nm-tag v" title="Photo, live selfie and ID checked by Jobsence">✔ <?= $t('ID सत्यापित', 'ID Verified') ?></span>
                                    <span class="nm-tag p" title="Paid Jobsence platform member">₹ <?= $t('पेड मेंबर', 'Paid member') ?></span>
                                    <?php if ((int)$r['terms'] > 1): ?><span class="nm-tag l">🔁 <?= $t((int)$r['terms'] . ' बार रिन्यू', 'Renewed ' . ((int)$r['terms'] - 1) . '×') ?></span><?php endif; ?>
                                </div>
                                <div class="nm-stars" aria-label="Rating">
                                    <?php if ($r['rating']): $avg = (float)$r['rating']['avg']; ?>
                                        <span class="s"><?= str_repeat('★', (int)round($avg)) . str_repeat('☆', 5 - (int)round($avg)) ?></span> <b><?= $h(number_format($avg, 1)) ?></b> <span class="n">(<?= (int)$r['rating']['count'] ?> <?= $t('रेटिंग', 'ratings') ?>)</span>
                                    <?php else: ?><span class="n"><?= $t('अभी कोई रेटिंग नहीं', 'No ratings yet') ?></span><?php endif; ?>
                                </div>
                                <h2><?= $h($unlocked ? ($d['business_name'] ?? '') ?: $r['full_name'] : (($d['business_name'] ?? '') ?: ContactPass::maskName((string)$r['full_name']))) ?></h2>
                                <div class="meta"><?= $h($r['categories']) ?></div>
                                <div class="meta"><?= $h(implode(', ', array_filter([$d['village'] ?? '', $r['city'], $r['pincode']]))) ?></div>
                                <?php if (!empty($d['timing']) || !empty($d['available_days'])): ?>
                                    <div class="meta nm-time">🕘 <?= $h($d['timing'] ?? '') ?><?= !empty($d['available_days']) ? ' · ' . $h(implode(', ', array_map(static fn($x) => ucfirst((string)$x), (array)$d['available_days']))) : '' ?></div>
                                <?php endif; ?>
                                <?php if (isset($d['visit_charge']) && $d['visit_charge'] !== ''): ?><div class="meta"><?= $t('विज़िट चार्ज से', 'Visit charge from') ?> ₹<?= $h(number_format((int)$d['visit_charge'])) ?></div><?php endif; ?>
                                <?php $ag = $unlocked ? ($agreements[(int)$r['id']] ?? null) : null; ?>
                                <?php if ($ag && $ag['status'] === 'accepted'): ?>
                                    <div class="meta"><?= $h(implode(', ', array_filter([$d['address_line'] ?? '', $d['landmark'] ?? '', $r['district'], $r['state']]))) ?></div>
                                    <div class="nm-contact">
                                        <a class="nm-call" href="tel:+91<?= $h($r['mobile']) ?>">📞 <?= $h($r['mobile']) ?></a>
                                        <a class="nm-wa" href="https://wa.me/91<?= $h($r['whatsapp'] ?: $r['mobile']) ?>" target="_blank" rel="noopener">WhatsApp</a>
                                    </div>
                                <?php elseif ($ag): ?>
                                    <div class="nm-locked">⏳ <?= $t('समझौता साइन होना बाकी', 'Agreement awaiting signatures') ?> · <a href="<?= $h($ag['link']) ?>"><?= $t('समझौता खोलें', 'Open agreement') ?></a></div>
                                <?php elseif ($unlocked): ?>
                                    <form method="POST" action="/mentoring/request/<?= (int)$r['id'] ?>" style="margin-top:8px">
                                        <input type="hidden" name="_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                                        <input type="hidden" name="back" value="<?= $h($_SERVER['REQUEST_URI'] ?? '/near-me') ?>">
                                        <button class="sd-btn" type="submit" style="padding:7px 12px;font-size:.88rem;background:#138808;border-color:#138808"><?= $t('सेवा माँगें – समझौता साइन करें', 'Request service – sign agreement') ?></button>
                                    </form>
                                    <div class="nm-locked">🔒 <?= $h(ContactPass::maskMobile((string)$r['mobile'])) ?> · <?= $t('दोनों के साइन के बाद नंबर दिखेगा', 'Number shown after both sign') ?></div>
                                <?php else: ?>
                                    <div class="nm-locked">🔒 <?= $h(ContactPass::maskMobile((string)$r['mobile'])) ?> · <a href="/apply/near-me-seeker"><?= $t('नंबर देखें', 'See number') ?></a></div>
                                <?php endif; ?>
                                <?php foreach ($r['reviews'] as $rv): ?>
                                    <div class="nm-review"><span class="s"><?= str_repeat('★', (int)$rv['stars']) ?></span> “<?= $h(mb_strimwidth((string)$rv['feedback'], 0, 140, '…')) ?>” <span class="n">– <?= $h(ContactPass::maskName((string)$rv['seeker_name'])) ?> · ✔ <?= $t('सत्यापित ग्राहक', 'Verified customer') ?></span></div>
                                <?php endforeach; ?>
                                <?php if ($unlocked): $mine = \App\Models\NearMeRating::mine((int)$pass['id'], (string)$r['mobile']); ?>
                                    <details class="nm-rate" <?= $mine ? '' : '' ?>>
                                        <summary><?= $mine ? $t('आपकी रेटिंग: ' . (int)$mine['stars'] . '★ – बदलें', 'Your rating: ' . (int)$mine['stars'] . '★ – change') : $t('⭐ रेटिंग और फ़ीडबैक दें', '⭐ Rate & give feedback') ?></summary>
                                        <form method="POST" action="/near-me/rate/<?= (int)$r['id'] ?>">
                                            <input type="hidden" name="_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                                            <input type="hidden" name="pin" value="<?= $h($pin) ?>"><input type="hidden" name="q" value="<?= $h($q) ?>">
                                            <div class="nm-starpick" role="radiogroup" aria-label="Stars">
                                                <?php for ($s = 5; $s >= 1; $s--): ?>
                                                    <input type="radio" id="st<?= (int)$r['id'] ?>_<?= $s ?>" name="stars" value="<?= $s ?>" <?= (int)($mine['stars'] ?? 0) === $s ? 'checked' : '' ?> required><label for="st<?= (int)$r['id'] ?>_<?= $s ?>" title="<?= $s ?> stars">★</label>
                                                <?php endfor; ?>
                                            </div>
                                            <textarea name="feedback" rows="2" maxlength="500" placeholder="Work quality, timing, behaviour…"><?= $h($mine['feedback'] ?? '') ?></textarea>
                                            <button type="submit" class="sd-btn" style="padding:8px 14px"><?= $tb('सेव करें', 'Save rating') ?></button>
                                        </form>
                                    </details>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="ij-note" style="margin-top:22px;font-size:.85rem;color:#4b5563;background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px 14px">
                <?= $tp(\App\Services\Registration\FormRegistry::platformDisclaimer()) ?>
                <?= $t(' काम शुरू करने से पहले कीमत तय कर लें और पहचान देख लें।', ' Agree on the price and check identity before work starts.') ?>
            </div>
        </div>
    </section>
</div>
