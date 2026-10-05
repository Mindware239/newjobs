<?php
use App\Helpers\Lang;
use App\Services\Registration\FormRegistry;

require __DIR__ . '/_partials.php';

$old = $old ?? [];
$errors = $errors ?? [];
$isProvider = !empty($form['otp']);
$feeLabel = number_format((float)$fee, 0);
$req = '<span class="req" aria-hidden="true">*</span>';

$val = static fn(string $k) => $h(is_scalar($old[$k] ?? null) ? $old[$k] : '');
$err = static fn(string $k): string => isset($errors[$k]) ? '<div class="err" role="alert">' . Lang::t($errors[$k][0], $errors[$k][1]) . '</div>' : '';
$cls = static fn(string $k, bool $full = false): string => 'sd-field' . (isset($errors[$k]) ? ' has-err' : '') . ($full ? ' sd-full' : '');
$isChecked = static function (string $k, string $v) use ($old): bool {
    $cur = $old[$k] ?? null;
    return is_array($cur) ? in_array($v, $cur, true) : (string)$cur === $v;
};
$label = static fn(array $f, string $for = ''): string => '<' . ($for !== '' ? 'label for="' . $for . '"' : 'span') . ' class="lbl">'
    . Lang::t($f['label'][0], $f['label'][1]) . ($f['required'] ? ' ' . '<span class="req" aria-hidden="true">*</span>' : '')
    . '</' . ($for !== '' ? 'label' : 'span') . '>';
$hint = static fn(array $f): string => !empty($f['hint']) ? '<div class="hint">' . Lang::t($f['hint'][0], $f['hint'][1]) . '</div>' : '';
$oldCategories = array_values(array_filter(array_map('strval', (array)($old['categories'] ?? []))));
?>
<div class="sd">
    <section class="sd-section">
        <div class="sd-wrap" style="max-width:940px">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:14px">
                <a href="/apply" style="font-weight:700;color:#4b5563;text-decoration:none">← <?= $t('सभी फॉर्म', 'All forms') ?></a>
                <?php $sdLangSwitcher(); ?>
            </div>

            <div style="text-align:center;margin-bottom:22px">
                <p style="margin:0;font-weight:800">Jobsence – <?= $t('भारत का Job Portal', 'India’s Job Portal') ?></p>
                <p style="margin:0 0 10px;font-weight:800;color:#f05537">भारत को कुशल बनाने की Jobsence पहल</p>
                <h1><span aria-hidden="true"><?= $form['icon'] ?></span> <?= $tb($form['title'][0], $form['title'][1]) ?></h1>
                <p class="sd-lead" style="margin-bottom:6px"><?= $t($form['intro'][0], $form['intro'][1]) ?></p>
                <p style="margin:0;font-weight:800"><?= $t("एकमुश्त प्रोसेसिंग शुल्क: ₹{$feeLabel} (GST सहित, वापसी योग्य नहीं)", "One-time processing fee: ₹{$feeLabel} (including GST, non-refundable)") ?></p>
                <p style="margin:4px 0 0;font-size:.9rem;color:#4b5563">(<?= $t('यह भारत सरकार की योजना नहीं है', 'This is NOT a Government of India scheme') ?>)</p>
            </div>

            <?php if (!empty($form['info'])): ?>
                <div class="sd-alert info sd-declare">
                    <b><?= $tp($form['info']['title']) ?></b>
                    <ul style="margin-top:8px">
                        <?php foreach ($form['info']['points'] as $p): ?><li><?= $tp($p) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="sd-alert err" role="alert">
                    <b><?= $t('कृपया नीचे लाल रंग में दिखाई गई गलतियाँ ठीक करें।', 'Please correct the errors shown in red below.') ?></b>
                    <?php if (isset($errors['general'])): ?><div><?= $tp($errors['general']) ?></div><?php endif; ?>
                </div>
            <?php endif; ?>

            <p style="font-size:.9rem;color:#4b5563"><?= $req ?> = <?= $t('ज़रूरी', 'required') ?></p>

            <form class="sd-form" id="sd-apply-form" method="POST" action="/apply/<?= $h($slug) ?>" <?= !empty($form['multipart']) ? 'enctype="multipart/form-data"' : '' ?>>
                <input type="hidden" name="_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
                <div class="sd-hp" aria-hidden="true"><label>Website <input type="text" name="_hp_website" tabindex="-1" autocomplete="off"></label></div>

                <?php foreach ($form['sections'] as $si => $section): ?>
                    <fieldset>
                        <legend><?= chr(65 + $si) ?>. <?= $tp($section['title']) ?></legend>
                        <div class="sd-fields">
                        <?php foreach ($section['fields'] as $f):
                            $k = $f['key'];
                            $id = 'f_' . $k;
                            $full = !empty($f['full']);
                            $required = $f['required'] ? 'required' : '';
                            switch ($f['type']):
                                case 'text': ?>
                                    <div class="<?= $cls($k, $full) ?>">
                                        <?= $label($f, $id) ?>
                                        <input type="text" id="<?= $id ?>" name="<?= $k ?>" value="<?= $val($k) ?>" maxlength="<?= (int)($f['max'] ?? 190) ?>" <?= $required ?> <?= !empty($f['autocomplete']) ? 'autocomplete="' . $h($f['autocomplete']) . '"' : '' ?>>
                                        <?= $hint($f) ?><?= $err($k) ?>
                                    </div>
                                <?php break;
                                case 'textarea': ?>
                                    <div class="<?= $cls($k, true) ?>">
                                        <?= $label($f, $id) ?>
                                        <textarea id="<?= $id ?>" name="<?= $k ?>" rows="3" maxlength="<?= (int)($f['max'] ?? 2000) ?>" <?= $required ?>><?= $val($k) ?></textarea>
                                        <?= $err($k) ?>
                                    </div>
                                <?php break;
                                case 'number': ?>
                                    <div class="<?= $cls($k, $full) ?>">
                                        <?= $label($f, $id) ?>
                                        <input type="number" id="<?= $id ?>" name="<?= $k ?>" value="<?= $val($k) ?>" min="<?= (int)($f['min'] ?? 0) ?>" max="<?= (int)($f['maxv'] ?? 0) ?>" inputmode="numeric" <?= $required ?>>
                                        <?= $err($k) ?>
                                    </div>
                                <?php break;
                                case 'date': ?>
                                    <div class="<?= $cls($k, $full) ?>">
                                        <?= $label($f, $id) ?>
                                        <input type="date" id="<?= $id ?>" name="<?= $k ?>" value="<?= $val($k) ?>" max="<?= date('Y-m-d', strtotime('-14 years')) ?>" <?= $required ?>>
                                        <?= $err($k) ?>
                                    </div>
                                <?php break;
                                case 'mobile': ?>
                                    <div class="<?= $cls($k, $full) ?>">
                                        <?= $label($f, $id) ?>
                                        <input type="tel" id="<?= $id ?>" name="<?= $k ?>" value="<?= $val($k) ?>" inputmode="numeric" maxlength="14" pattern="[0-9+\s\-]{10,14}" <?= $required ?> <?= $k === 'mobile' ? 'autocomplete="tel"' : '' ?> <?= $isProvider && $k === 'mobile' ? 'data-unique="mobile"' : '' ?>>
                                        <div class="sd-err" data-unique-msg="<?= $k ?>"></div>
                                        <?= $err($k) ?>
                                    </div>
                                <?php break;
                                case 'email': ?>
                                    <div class="<?= $cls($k, $full) ?>">
                                        <?= $label($f, $id) ?>
                                        <?php if ($isProvider): ?>
                                            <div class="sd-inline">
                                                <input type="email" id="<?= $id ?>" name="email" value="<?= $val('email') ?>" maxlength="190" required autocomplete="email" data-unique="email" <?= $emailVerified ? 'readonly' : '' ?>>
                                                <button type="button" class="sd-btn small" id="sd-otp-send" <?= $emailVerified ? 'hidden' : '' ?>><?= $t('OTP भेजें', 'Send OTP') ?></button>
                                            </div>
                                            <div class="sd-err" data-unique-msg="email"></div>
                                            <div id="sd-otp-box" class="sd-inline" style="margin-top:8px" hidden>
                                                <input type="text" id="sd-otp-code" inputmode="numeric" maxlength="6" placeholder="6 अंकों का OTP / 6-digit OTP" autocomplete="one-time-code">
                                                <button type="button" class="sd-btn small" id="sd-otp-verify"><?= $t('OTP सत्यापित करें', 'Verify OTP') ?></button>
                                            </div>
                                            <div id="sd-otp-msg" class="hint" aria-live="polite"><?= $emailVerified ? '<span class="ok-msg">ईमेल सत्यापित ✓ / Email verified ✓</span>' : $t('OTP केवल ईमेल पर भेजा जाएगा (10 मिनट मान्य, 3 प्रयास)।', 'OTP is sent only to your email (valid 10 minutes, 3 attempts).') ?></div>
                                        <?php else: ?>
                                            <input type="email" id="<?= $id ?>" name="email" value="<?= $val('email') ?>" maxlength="190" <?= $required ?> autocomplete="email">
                                            <?= $hint($f) ?>
                                        <?php endif; ?>
                                        <?= $err('email') ?>
                                    </div>
                                <?php break;
                                case 'aadhaar': ?>
                                    <div class="<?= $cls($k, $full) ?>">
                                        <?= $label($f, $id) ?>
                                        <input type="text" id="<?= $id ?>" name="aadhaar" value="<?= $val('aadhaar') ?>" inputmode="numeric" maxlength="14" autocomplete="off" <?= $required ?>>
                                        <?= $hint($f) ?><?= $err('aadhaar') ?>
                                    </div>
                                <?php break;
                                case 'geo': ?>
                                    <div class="<?= $cls($k, true) ?>">
                                        <?= $label($f, $id) ?>
                                        <input type="hidden" name="geo" id="<?= $id ?>" value="<?= $val('geo') ?>">
                                        <button type="button" class="sd-btn ghost sd-geo-btn" data-target="<?= $id ?>" style="padding:10px 16px">📍 <?= $t('मेरी अभी की लोकेशन लें', 'Use my current location') ?></button>
                                        <span class="sd-geo-status" aria-live="polite" style="margin-left:8px;font-size:.88rem;color:#047857"><?= $val('geo') !== '' ? '✓ ' . $h($val('geo')) : '' ?></span>
                                        <?= $hint($f) ?>
                                    </div>
                                <?php break;
                                case 'pincode': ?>
                                    <div class="<?= $cls($k, $full) ?>">
                                        <?= $label($f, $id) ?>
                                        <input type="text" id="<?= $id ?>" name="pincode" value="<?= $val('pincode') ?>" inputmode="numeric" maxlength="6" pattern="[1-9][0-9]{5}" required autocomplete="postal-code">
                                        <?= $err('pincode') ?>
                                    </div>
                                <?php break;
                                case 'state': ?>
                                    <div class="<?= $cls($k, $full) ?>">
                                        <?= $label($f, $id) ?>
                                        <select id="<?= $id ?>" name="state" required>
                                            <option value="">— चुनें / Select —</option>
                                            <?php foreach ($states as $names): ?>
                                                <option value="<?= $h($names[0]) ?>" <?= ($old['state'] ?? '') === $names[0] ? 'selected' : '' ?>><?= $h($names[1]) ?> / <?= $h($names[0]) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?= $err('state') ?>
                                    </div>
                                <?php break;
                                case 'select': ?>
                                    <div class="<?= $cls($k, !empty($f['full'])) ?>">
                                        <?= $label($f, $id) ?>
                                        <select id="<?= $id ?>" name="<?= $k ?>" <?= $required ?>>
                                            <option value="">— चुनें / Select —</option>
                                            <?php foreach ($f['options'] as $ov => $ol): $ov = (string)$ov; ?>
                                                <option value="<?= $h($ov) ?>" <?= (string)($old[$k] ?? '') === $ov ? 'selected' : '' ?>><?= $h($ol[0]) ?> / <?= $h($ol[1]) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?= $hint($f) ?><?= $err($k) ?>
                                    </div>
                                <?php break;
                                case 'radio':
                                case 'checkboxes':
                                    $multi = $f['type'] === 'checkboxes';
                                    // Short option groups (e.g. Gender, Duration) sit half-width; long ones span the row.
                                    $optCount = count($f['options']);
                                    $longest = max(array_map(static fn($o) => mb_strlen($o[1]), $f['options']));
                                    $compact = $optCount <= 3 && $longest <= 8;
                                    $optFull = $f['full'] ?? !$compact; ?>
                                    <div class="<?= $cls($k, $optFull) ?>" role="group" aria-label="<?= $h(Lang::plain($f['label'][0], $f['label'][1])) ?>">
                                        <?= $label($f) ?>
                                        <div class="sd-opts<?= $compact ? ' compact' : '' ?>">
                                            <?php foreach ($f['options'] as $ov => $ol): $ov = (string)$ov; ?>
                                                <label class="sd-opt"><input type="<?= $multi ? 'checkbox' : 'radio' ?>" name="<?= $k . ($multi ? '[]' : '') ?>" value="<?= $h($ov) ?>" <?= $isChecked($k, $ov) ? 'checked' : '' ?> <?= !$multi ? $required : '' ?>> <span class="opt-txt"><?= $tp($ol) ?></span></label>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php if (!empty($f['other'])): $ph = $f['other_placeholder'] ?? ['अन्य हो तो लिखें', 'If other, specify']; ?>
                                            <input type="text" name="<?= $h($f['other']) ?>" value="<?= $val($f['other']) ?>" maxlength="190" placeholder="<?= $h(Lang::plain($ph[0], $ph[1])) ?>" style="margin-top:8px">
                                        <?php endif; ?>
                                        <?= $err($k) ?>
                                    </div>
                                <?php break;
                                case 'categories': ?>
                                    <div class="<?= $cls($k, true) ?>">
                                        <label class="lbl" for="sd-cat-input"><?= $tp($form['categories_label']) ?> <?= $f['required'] ? $req : '' ?></label>
                                        <div class="sd-picker">
                                            <input type="text" id="sd-cat-input" autocomplete="off" placeholder="टाइप करें… जैसे Electrician, Data Entry / Type to search…" aria-controls="sd-cat-list" aria-autocomplete="list">
                                            <div class="list" id="sd-cat-list" role="listbox" hidden></div>
                                        </div>
                                        <div class="sd-picked" id="sd-cat-picked" aria-live="polite"></div>
                                        <div class="hint"><?= $t((FormRegistry::minCategories($form) > 1 ? 'कम से कम ' . FormRegistry::minCategories($form) . ', ' : '') . 'अधिकतम ' . FormRegistry::maxCategories($form) . ' चुनें' . (!empty($form['ordered_categories']) ? ' – पहला विकल्प सबसे ज़्यादा पसंद।' : '।') . ' सूची में न हो तो लिखकर Enter दबाएँ।', (FormRegistry::minCategories($form) > 1 ? 'Pick at least ' . FormRegistry::minCategories($form) . ', up to ' : 'Pick up to ') . FormRegistry::maxCategories($form) . (!empty($form['ordered_categories']) ? ' – your 1st choice is your top preference.' : '.') . ' Not in the list? Type it and press Enter.') ?></div>
                                        <div id="sd-cat-hidden"></div>
                                        <?= $err('categories') ?>
                                    </div>
                                <?php break;
                                case 'file': ?>
                                    <div class="<?= $cls($k, true) ?>">
                                        <?= $label($f, $id) ?>
                                        <input type="file" id="<?= $id ?>" name="<?= $k ?>" accept="<?= !empty($f['allow_images']) ? '.pdf,.doc,.docx,.jpg,.jpeg,.png' : '.pdf,.doc,.docx' ?>" data-max-mb="5">
                                        <?= $err($k) ?>
                                    </div>
                                <?php break;
                                case 'image': ?>
                                    <div class="<?= $cls($k, true) ?>">
                                        <?= $label($f, $id) ?>
                                        <input type="file" id="<?= $id ?>" name="<?= $k ?>" accept="image/jpeg,image/png,image/webp" data-max-mb="5" data-preview="<?= $id ?>_pv" <?= $f['required'] ? 'required' : '' ?>>
                                        <img id="<?= $id ?>_pv" alt="" class="sd-img-pv" hidden>
                                        <?= $hint($f) ?><?= $err($k) ?>
                                    </div>
                                <?php break;
                                case 'selfie': ?>
                                    <div class="<?= $cls($k, true) ?>">
                                        <?= $label($f, $id) ?>
                                        <div class="sd-selfie" data-selfie="<?= $id ?>">
                                            <video id="<?= $id ?>_cam" playsinline muted hidden></video>
                                            <img id="<?= $id ?>_pv" alt="" class="sd-img-pv" hidden>
                                            <div class="sd-cta-row" style="margin:8px 0">
                                                <button type="button" class="sd-btn ghost" data-selfie-open><?= $tb('📷 कैमरा खोलें', '📷 Open camera') ?></button>
                                                <button type="button" class="sd-btn" data-selfie-snap hidden><?= $tb('सेल्फ़ी लें', 'Take selfie') ?></button>
                                            </div>
                                            <div class="hint"><?= $t('कैमरा न खुले तो फ़ोन के फ्रंट कैमरे से फोटो लें:', 'If the camera does not open, take a photo with your phone’s front camera:') ?></div>
                                            <input type="file" id="<?= $id ?>" name="<?= $k ?>" accept="image/*" capture="user" data-max-mb="5" data-preview="<?= $id ?>_pv" <?= $f['required'] ? 'required' : '' ?>>
                                        </div>
                                        <?= $hint($f) ?><?= $err($k) ?>
                                    </div>
                                <?php break;
                                case 'video': ?>
                                    <div class="<?= $cls($k, true) ?>">
                                        <?= $label($f, $id) ?>
                                        <input type="file" id="<?= $id ?>" name="video" accept="video/*" data-max-mb="200">
                                        <?= $hint($f) ?><?= $err('video') ?>
                                    </div>
                                <?php break;
                                case 'gst': ?>
                                    <div class="<?= $cls('gstin', true) ?>">
                                        <?= $label($f, $id) ?>
                                        <input type="text" id="<?= $id ?>" name="gstin" value="<?= $val('gstin') ?>" maxlength="15" style="text-transform:uppercase" placeholder="22AAAAA0000A1Z5" data-unique="gstin" <?= !empty($old['no_gst']) ? 'disabled' : '' ?>>
                                        <label class="sd-opt" style="margin-top:8px;display:inline-flex"><input type="checkbox" name="no_gst" value="1" id="sd-no-gst" <?= !empty($old['no_gst']) ? 'checked' : '' ?>> <span class="opt-txt"><?= $t('मेरे पास GST नहीं है', 'I don’t have GST') ?></span></label>
                                        <div class="sd-err" data-unique-msg="gstin"></div>
                                        <?= $err('gstin') ?>
                                    </div>
                                <?php break;
                                case 'bank_account': ?>
                                    <div class="<?= $cls($k, $full) ?>">
                                        <?= $label($f, $id) ?>
                                        <input type="text" id="<?= $id ?>" name="bank_account" value="<?= $val('bank_account') ?>" inputmode="numeric" maxlength="20" autocomplete="off" <?= $required ?>>
                                        <?= $hint($f) ?><?= $err($k) ?>
                                    </div>
                                <?php break;
                                case 'ifsc': ?>
                                    <div class="<?= $cls($k, $full) ?>">
                                        <?= $label($f, $id) ?>
                                        <input type="text" id="<?= $id ?>" name="ifsc" value="<?= $val('ifsc') ?>" maxlength="11" style="text-transform:uppercase" placeholder="SBIN0001234" <?= $required ?>>
                                        <?= $err($k) ?>
                                    </div>
                                <?php break;
                            endswitch;
                        endforeach; ?>
                        </div>
                    </fieldset>
                <?php endforeach; ?>

                <fieldset class="sd-declare">
                    <legend><?= chr(65 + count($form['sections'])) ?>. <?= $t('घोषणा', 'Declaration') ?> <?= $req ?></legend>
                    <p><?= $t('मैं सहमत हूँ कि:', 'I agree that:') ?></p>
                    <ol>
                        <?php foreach ($form['declaration'] as $d): ?><li><?= $tp($d) ?></li><?php endforeach; ?>
                    </ol>
                    <div class="<?= $cls('declaration') ?>">
                        <label class="sd-opt" style="font-weight:800"><input type="checkbox" name="declaration" value="1" <?= !empty($old['declaration']) ? 'checked' : '' ?> required> <span class="opt-txt"><?= $t('मैं सभी नियम व शर्तें स्वीकार करता/करती हूँ (डिजिटल स्वीकृति)।', 'I accept all terms & conditions (digital acceptance).') ?></span></label>
                        <?= $err('declaration') ?>
                    </div>
                </fieldset>

                <fieldset>
                    <legend><?= $t('सुरक्षा जाँच (CAPTCHA)', 'Security Check (CAPTCHA)') ?> <?= $req ?></legend>
                    <div class="<?= $cls('captcha') ?>">
                        <label class="lbl" for="f_captcha"><?= $t('इस सवाल का उत्तर दें', 'Answer this question') ?></label>
                        <div class="sd-captcha">
                            <b aria-hidden="true"><?= (int)$captcha[0] ?> + <?= (int)$captcha[1] ?> = ?</b>
                            <span class="sd-hp"><?= (int)$captcha[0] ?> plus <?= (int)$captcha[1] ?></span>
                            <input type="text" id="f_captcha" name="captcha" inputmode="numeric" maxlength="3" required autocomplete="off">
                        </div>
                        <?= $err('captcha') ?>
                    </div>
                </fieldset>

                <div class="sd-alert ok">
                    <b><?= $t('भुगतान', 'Payment') ?>:</b>
                    <?= $t("अगले पेज पर UPI, कार्ड या नेट बैंकिंग से ₹{$feeLabel} का भुगतान करें। सफल भुगतान के बाद ही फॉर्म मान्य होगा।", "Pay ₹{$feeLabel} on the next page via UPI, card or net banking. The form is considered only after successful payment.") ?>
                </div>

                <button type="submit" class="sd-btn" style="width:100%" id="sd-submit"><?= $tb("फॉर्म जमा करें और ₹{$feeLabel} भुगतान करें", "Submit Form & Pay ₹{$feeLabel}") ?></button>
            </form>
        </div>
    </section>
</div>
<?php $sdWhatsapp($whatsappNumber); ?>

<script>
(function () {
    var form = document.getElementById('sd-apply-form');
    var csrf = <?= json_encode((string)($_SESSION['csrf_token'] ?? '')) ?>;

    function post(url, data) {
        var body = new URLSearchParams(data);
        body.append('_token', csrf);
        return fetch(url, { method: 'POST', headers: { 'X-CSRF-Token': csrf, 'X-Requested-With': 'XMLHttpRequest' }, body: body, credentials: 'same-origin' })
            .then(function (r) { return r.json().catch(function () { return { success: false, error: 'Network error' }; }); });
    }

    // ---- file size guard ----
    form.querySelectorAll('input[type=file][data-max-mb]').forEach(function (inp) {
        inp.addEventListener('change', function () {
            var max = +inp.getAttribute('data-max-mb') * 1024 * 1024;
            if (inp.files[0] && inp.files[0].size > max) {
                alertBox(inp, 'फ़ाइल बहुत बड़ी है (अधिकतम ' + inp.getAttribute('data-max-mb') + 'MB) / File too large (max ' + inp.getAttribute('data-max-mb') + 'MB)');
                inp.value = '';
            } else {
                alertBox(inp, '');
            }
        });
    });
    function alertBox(inp, msg) {
        var box = inp.parentNode.querySelector('.sd-err.js') || inp.parentNode.appendChild(Object.assign(document.createElement('div'), { className: 'sd-err js' }));
        box.textContent = msg;
    }

    // ---- "Use my current location": coordinates + reverse geocoding fill PIN / city / district / state ----
    document.querySelectorAll('.sd-geo-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var status = btn.parentNode.querySelector('.sd-geo-status'), hidden = document.getElementById(btn.dataset.target), form = btn.form;
            if (!navigator.geolocation) { status.textContent = 'Location not supported on this device'; return; }
            status.textContent = '…';
            navigator.geolocation.getCurrentPosition(function (pos) {
                var lat = pos.coords.latitude.toFixed(5), lng = pos.coords.longitude.toFixed(5);
                hidden.value = lat + ',' + lng;
                status.textContent = '✓ ' + lat + ', ' + lng;
                fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&zoom=18&addressdetails=1&lat=' + lat + '&lon=' + lng, { headers: { 'Accept-Language': 'en' } })
                    .then(function (r) { return r.json(); })
                    .then(function (d) {
                        var a = (d && d.address) || {};
                        var set = function (name, v) { var el = form.querySelector('[name="' + name + '"]'); if (el && v && !el.value) { el.value = v; el.dispatchEvent(new Event('change')); } };
                        set('pincode', (a.postcode || '').replace(/\D/g, '').slice(0, 6));
                        set('city', a.city || a.town || a.village || a.county);
                        set('district', a.state_district || a.county);
                        set('village', a.suburb || a.neighbourhood || a.village || a.hamlet);
                        set('address_line', [a.house_number, a.road].filter(Boolean).join(', '));
                        var st = form.querySelector('select[name="state"]');
                        if (st && !st.value && a.state) {
                            Array.prototype.forEach.call(st.options, function (o) { if (o.value.toLowerCase() === a.state.toLowerCase()) st.value = o.value; });
                        }
                        status.textContent = '✓ ' + [a.suburb || a.village, a.city || a.town, a.postcode].filter(Boolean).join(', ');
                    }).catch(function () { status.textContent = '✓ ' + lat + ', ' + lng + ' – please type your address below'; });
            }, function () { status.textContent = 'Location permission denied – please type your address below'; }, { enableHighAccuracy: true, timeout: 15000 });
        });
    });

    // ---- searchable categories (3000+) ----
    var catInput = document.getElementById('sd-cat-input');
    if (catInput) {
        var list = document.getElementById('sd-cat-list'), picked = document.getElementById('sd-cat-picked'), hidden = document.getElementById('sd-cat-hidden');
        var MAX = <?= (int)FormRegistry::maxCategories($form) ?>, MIN = <?= (int)FormRegistry::minCategories($form) ?>, ORDERED = <?= !empty($form['ordered_categories']) ? 'true' : 'false' ?>, all = null, chosen = <?= json_encode($oldCategories, JSON_UNESCAPED_UNICODE) ?>, active = -1;
        function load() {
            if (all) return Promise.resolve(all);
            return fetch('/apply/categories').then(function (r) { return r.json(); }).then(function (d) { all = d; return d; }).catch(function () { all = []; return all; });
        }
        function render() {
            picked.innerHTML = ''; hidden.innerHTML = '';
            chosen.forEach(function (c, i) {
                var s = document.createElement('span'); s.textContent = ORDERED ? (i + 1) + '. ' + c : c;
                var x = document.createElement('button'); x.type = 'button'; x.textContent = '×'; x.setAttribute('aria-label', 'हटाएँ / Remove ' + c);
                x.onclick = function () { chosen.splice(i, 1); render(); };
                s.appendChild(x); picked.appendChild(s);
                var h = document.createElement('input'); h.type = 'hidden'; h.name = 'categories[]'; h.value = c; hidden.appendChild(h);
            });
            catInput.disabled = chosen.length >= MAX;
        }
        function add(c) {
            c = c.trim();
            if (!c || chosen.length >= MAX || chosen.some(function (x) { return x.toLowerCase() === c.toLowerCase(); })) return;
            chosen.push(c); render(); catInput.value = ''; list.hidden = true;
        }
        function search() {
            var q = catInput.value.trim().toLowerCase();
            if (q.length < 1) { list.hidden = true; return; }
            load().then(function (items) {
                var starts = [], contains = [];
                for (var i = 0; i < items.length; i++) {
                    var n = items[i].toLowerCase();
                    if (n.indexOf(q) === 0) starts.push(items[i]); else if (contains.length < 200 && n.indexOf(q) > 0) contains.push(items[i]);
                }
                // Shortest (closest) matches first, e.g. "Electrician" before "Electrical and Maintenance Engineer".
                var byLen = function (a, b) { return a.length - b.length || a.localeCompare(b); };
                var res = starts.sort(byLen).concat(contains.sort(byLen)).slice(0, 40);
                list.innerHTML = ''; active = -1;
                res.forEach(function (r) {
                    var b = document.createElement('button'); b.type = 'button'; b.textContent = r; b.setAttribute('role', 'option');
                    b.onmousedown = function (e) { e.preventDefault(); add(r); };
                    list.appendChild(b);
                });
                if (!res.length) {
                    var b = document.createElement('button'); b.type = 'button';
                    b.textContent = '➕ "' + catInput.value.trim() + '" जोड़ें / Add';
                    b.onmousedown = function (e) { e.preventDefault(); add(catInput.value); };
                    list.appendChild(b);
                }
                list.hidden = false;
            });
        }
        catInput.addEventListener('focus', load);
        catInput.addEventListener('input', search);
        catInput.addEventListener('blur', function () { setTimeout(function () { list.hidden = true; }, 150); });
        catInput.addEventListener('keydown', function (e) {
            var opts = list.querySelectorAll('button');
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                active = Math.max(0, Math.min(opts.length - 1, active + (e.key === 'ArrowDown' ? 1 : -1)));
                opts.forEach(function (o, i) { o.classList.toggle('active', i === active); });
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (active >= 0 && opts[active] && !list.hidden) { opts[active].dispatchEvent(new Event('mousedown')); } else { add(catInput.value); }
            }
        });
        render();
        form.addEventListener('submit', function (e) {
            if (chosen.length < MIN) {
                e.preventDefault();
                catInput.focus();
                if (MIN > 1) { alertBox(catInput.parentNode, 'Select at least ' + MIN + ' options / कम से कम ' + MIN + ' विकल्प चुनें'); return; }
                alertBox(catInput.parentNode, 'सूची से कम से कम एक चुनें / Select at least one from the list');
            }
        });
    }

    // ---- Photo preview + live selfie (camera → canvas → file input) ----
    form.querySelectorAll('input[type=file][data-preview]').forEach(function (inp) {
        inp.addEventListener('change', function () {
            var pv = document.getElementById(inp.getAttribute('data-preview'));
            if (pv && inp.files && inp.files[0] && /^image\//.test(inp.files[0].type)) { pv.src = URL.createObjectURL(inp.files[0]); pv.hidden = false; }
        });
    });
    form.querySelectorAll('[data-selfie]').forEach(function (box) {
        var id = box.getAttribute('data-selfie'), video = document.getElementById(id + '_cam'), input = document.getElementById(id),
            pv = document.getElementById(id + '_pv'), openBtn = box.querySelector('[data-selfie-open]'), snapBtn = box.querySelector('[data-selfie-snap]'), stream = null;
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || !window.DataTransfer) { openBtn.hidden = true; return; }
        openBtn.addEventListener('click', function () {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 720 } }, audio: false }).then(function (s) {
                stream = s; video.srcObject = s; video.hidden = false; pv.hidden = true; video.play(); snapBtn.hidden = false;
            }).catch(function () { alertBox(box, 'कैमरा नहीं खुल सका – नीचे से फोटो लें / Could not open the camera – use the photo option below'); });
        });
        snapBtn.addEventListener('click', function () {
            var c = document.createElement('canvas'); c.width = video.videoWidth || 640; c.height = video.videoHeight || 480;
            c.getContext('2d').drawImage(video, 0, 0, c.width, c.height);
            c.toBlob(function (blob) {
                var dt = new DataTransfer(); dt.items.add(new File([blob], 'selfie.jpg', { type: 'image/jpeg' })); input.files = dt.files;
                pv.src = URL.createObjectURL(blob); pv.hidden = false; video.hidden = true; snapBtn.hidden = true;
                if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; }
                openBtn.textContent = 'दोबारा लें / Retake';
            }, 'image/jpeg', 0.88);
        });
    });

    // ---- GST "I don't have GST" ----
    var noGst = document.getElementById('sd-no-gst'), gst = document.getElementById('f_gstin');
    if (noGst && gst) {
        noGst.addEventListener('change', function () { gst.disabled = noGst.checked; if (noGst.checked) { gst.value = ''; document.querySelector('[data-unique-msg="gstin"]').textContent = ''; } });
    }

<?php if ($isProvider): ?>
    // ---- real-time uniqueness (Email / Mobile / GST) ----
    var conflicts = {};
    form.querySelectorAll('[data-unique]').forEach(function (inp) {
        var field = inp.getAttribute('data-unique');
        inp.addEventListener('blur', function () {
            var v = inp.value.trim();
            var box = form.querySelector('[data-unique-msg="' + field + '"]');
            if (!v) { box.textContent = ''; delete conflicts[field]; return; }
            post('/apply/check-unique', { field: field, value: v }).then(function (r) {
                if (r && r.available === false) { conflicts[field] = true; box.textContent = r.message_hi + ' / ' + r.message_en; }
                else { delete conflicts[field]; box.textContent = ''; }
            });
        });
    });

    // ---- email OTP ----
    var email = document.getElementById('f_email'), sendBtn = document.getElementById('sd-otp-send'), box = document.getElementById('sd-otp-box'),
        code = document.getElementById('sd-otp-code'), verifyBtn = document.getElementById('sd-otp-verify'), msg = document.getElementById('sd-otp-msg');
    var verified = <?= $emailVerified ? 'true' : 'false' ?>;
    function say(text, ok) { msg.innerHTML = ''; var s = document.createElement('span'); s.className = ok ? 'ok-msg' : 'sd-err'; s.textContent = text; msg.appendChild(s); }
    sendBtn && sendBtn.addEventListener('click', function () {
        if (!email.value.trim() || !email.checkValidity()) { say('सही ईमेल भरें / Enter a valid email', false); return; }
        sendBtn.disabled = true;
        post('/apply/otp/send', { email: email.value.trim(), form: <?= json_encode($slug) ?> }).then(function (r) {
            if (r.success) {
                say(r.message, true); box.hidden = false; code.focus();
                var left = 60; sendBtn.textContent = left + 's';
                var timer = setInterval(function () { left--; sendBtn.textContent = left > 0 ? left + 's' : 'OTP दोबारा भेजें / Resend'; if (left <= 0) { clearInterval(timer); sendBtn.disabled = false; } }, 1000);
            } else { say(r.error || 'Error', false); sendBtn.disabled = false; }
        });
    });
    verifyBtn && verifyBtn.addEventListener('click', function () {
        verifyBtn.disabled = true;
        post('/apply/otp/verify', { email: email.value.trim(), otp: code.value.trim() }).then(function (r) {
            verifyBtn.disabled = false;
            if (r.success) { verified = true; say(r.message, true); box.hidden = true; sendBtn.hidden = true; email.readOnly = true; }
            else { say(r.error || 'Invalid OTP', false); if (/30/.test(r.error || '')) { box.hidden = true; } }
        });
    });
    form.addEventListener('submit', function (e) {
        if (!verified) { e.preventDefault(); say('आगे बढ़ने से पहले ईमेल OTP सत्यापित करें / Please verify your email with OTP first', false); email.scrollIntoView({ block: 'center' }); return; }
        if (Object.keys(conflicts).length) { e.preventDefault(); form.querySelector('[data-unique="' + Object.keys(conflicts)[0] + '"]').scrollIntoView({ block: 'center' }); }
    });
<?php endif; ?>
})();
</script>
