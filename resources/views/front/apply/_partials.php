<?php
/**
 * Shared styles, language switcher, notice strip and WhatsApp button for the Jobsence
 * registration pages (/apply/*, /skill-development, homepage buttons).
 * Scoped under .sd so the pages don't depend on the compiled Tailwind build.
 *
 * $t($hi, $en)  inline bilingual text    $tb($hi, $en)  block bilingual text (headings / paragraphs)
 */
use App\Helpers\Lang;

if (!isset($sdPartialsLoaded)) {
    $sdPartialsLoaded = true;
    $h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    $t = static fn(string $hi, string $en): string => Lang::t($hi, $en);
    $tb = static fn(string $hi, string $en): string => Lang::b($hi, $en);
    $tp = static fn(array $pair): string => Lang::t($pair[0], $pair[1]);
    $sdRegional = array_keys(Lang::regional());
    $sdLangMode = Lang::mode();
?>
<script>document.documentElement.setAttribute('data-jl', <?= json_encode($sdLangMode) ?>);</script>
<style>
    /* ---- language visibility ---- */
    .t-en, <?php foreach ($sdRegional as $c) { echo ".t-{$c}, "; } ?>.t-none { display: none; }
    html[data-jl="both"] .t-en { display: inline; color: var(--sd-muted, #4b5563); }
    html[data-jl="both"] .t-en::before { content: " / "; }
    html[data-jl="both"] .t-blk > .t-en { display: block; font-size: .86em; margin-top: 2px; }
    html[data-jl="both"] .t-blk > .t-en::before { content: none; }
    /* English half stays readable on dark / coloured buttons and active tabs */
    html[data-jl="both"] :is(.ct-want, .ct-offer, .ct-tabs a.on, .mt-tabs a.on, .mt-green, .yi-go, .sd-btn, .sd-near) .t-en { color: inherit; opacity: .92; }
    html[data-jl="en"] .t-hi { display: none; }
    html[data-jl="en"] .t-en { display: inline; color: inherit; }
<?php foreach ($sdRegional as $c): ?>
    html[data-jl="<?= $c ?>"] .t-hi, html[data-jl="<?= $c ?>"] .t-en { display: none; }
    html[data-jl="<?= $c ?>"] .t-<?= $c ?> { display: inline; }
<?php endforeach; ?>

    .sd { --sd-primary: #f05537; --sd-primary-dark: #d9432a; --sd-soft: #fff1ed; --sd-ink: #1a1a1a; --sd-muted: #4b5563; --sd-line: #e5e7eb; --sd-warn-bg: #fffbeb; --sd-warn-line: #f59e0b; color: var(--sd-ink); line-height: 1.6; }
    .sd *, .sd *::before, .sd *::after { box-sizing: border-box; }
    .sd-wrap { max-width: 1120px; margin: 0 auto; padding: 0 16px; }
    .sd-section { padding: 44px 0; }
    .sd-section.alt { background: #fafafa; }
    .sd h1, .sd h2, .sd h3 { line-height: 1.25; margin: 0 0 12px; color: var(--sd-ink); }
    .sd h1 { font-size: clamp(1.6rem, 4vw, 2.5rem); font-weight: 900; }
    .sd h2 { font-size: clamp(1.3rem, 3vw, 1.85rem); font-weight: 800; }
    .sd h3 { font-size: 1.08rem; font-weight: 800; }
    .sd p { margin: 0 0 12px; }
    .sd-lead { font-size: 1.06rem; color: var(--sd-muted); }
    .sd-grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); }
    .sd-card { background: #fff; border: 1px solid var(--sd-line); border-radius: 14px; padding: 20px; }
    .sd-step { position: relative; padding-top: 52px; }
    .sd-step b.num { position: absolute; top: 16px; left: 20px; width: 30px; height: 30px; border-radius: 50%; background: var(--sd-primary); color: #fff; display: grid; place-items: center; font-size: .95rem; }
    .sd-btn { display: inline-flex; flex-direction: column; align-items: center; justify-content: center; padding: 14px 22px; border-radius: 12px; font-weight: 800; text-decoration: none; border: 2px solid var(--sd-primary); background: var(--sd-primary); color: #fff; cursor: pointer; font-size: 1rem; text-align: center; line-height: 1.3; }
    .sd-btn:hover { background: var(--sd-primary-dark); border-color: var(--sd-primary-dark); color: #fff; }
    .sd-btn.ghost { background: #fff; color: var(--sd-primary); }
    .sd-btn.ghost:hover { background: var(--sd-soft); color: var(--sd-primary-dark); }
    .sd-btn.small { padding: 9px 14px; font-size: .86rem; border-radius: 10px; flex-direction: row; gap: 4px; white-space: nowrap; flex: none; }
    .sd [hidden] { display: none !important; }
    .sd-btn[disabled] { opacity: .6; cursor: not-allowed; }
    html .sd-btn .t-en, html .sd-btn .t-blk > .t-en { color: inherit !important; opacity: .92; }
    .sd-hero { background: linear-gradient(135deg, #fff1ed 0%, #fff 60%); padding: 48px 0 36px; border-bottom: 1px solid var(--sd-line); }
    .sd-badge { display: inline-block; background: #fff; border: 1px solid var(--sd-primary); color: var(--sd-primary); border-radius: 999px; padding: 4px 12px; font-size: .82rem; font-weight: 800; margin-bottom: 14px; }
    .sd-cta-row { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 22px; }
    .sd-notice { background: var(--sd-warn-bg); border: 1px solid var(--sd-warn-line); border-radius: 12px; padding: 14px 16px; font-size: .9rem; }
    .sd-notice ul { margin: 6px 0 0; padding-left: 18px; }
    .sd-notice li { margin-bottom: 4px; }
    .sd-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .sd-chip { background: var(--sd-soft); color: var(--sd-primary-dark); border-radius: 999px; padding: 6px 12px; font-size: .88rem; font-weight: 700; }
    .sd-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid var(--sd-line); }
    .sd-table th, .sd-table td { padding: 12px 14px; border-bottom: 1px solid var(--sd-line); text-align: left; vertical-align: top; font-size: .95rem; }
    .sd-table tr:last-child td { border-bottom: 0; }
    .sd-table .amt { text-align: right; font-weight: 800; }
    .sd-faq details { background: #fff; border: 1px solid var(--sd-line); border-radius: 12px; padding: 14px 16px; margin-bottom: 10px; }
    .sd-faq summary { cursor: pointer; font-weight: 800; }
    .sd-faq details p { margin: 10px 0 0; }
    .sd-states { columns: 4 200px; column-gap: 16px; font-size: .92rem; }
    .sd-states a { display: block; padding: 4px 0; color: var(--sd-muted); text-decoration: none; break-inside: avoid; }
    .sd-states a:hover { color: var(--sd-primary); }
    .sd-wa { position: fixed; right: 16px; bottom: 16px; z-index: 60; width: 56px; height: 56px; border-radius: 50%; background: #25d366; color: #fff; display: grid; place-items: center; box-shadow: 0 6px 18px rgba(0,0,0,.2); }
    .sd-wa:hover { background: #1ebe5b; color: #fff; }
    .sd-alert { border-radius: 12px; padding: 14px 16px; margin-bottom: 18px; font-size: .95rem; }
    .sd-alert.err { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
    .sd-alert.ok { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
    .sd-alert.info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; }
    /* language switcher */
    .sd-lang { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; font-size: .85rem; }
    .sd-lang button { border: 1px solid var(--sd-line); background: #fff; border-radius: 999px; padding: 5px 12px; cursor: pointer; font: inherit; font-weight: 700; color: var(--sd-ink); }
    .sd-lang button[aria-pressed="true"] { border-color: var(--sd-primary); background: var(--sd-soft); color: var(--sd-primary-dark); }
    .sd-lang .soon { color: var(--sd-muted); font-size: .78rem; }
    /* big apply buttons */
    /* Cards wrap into even, centred rows: 3 per row on desktop (5 cards = 3 + 2 centred), 2 on tablet, 1 on phone */
    .sd-apply-grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 14px; }
    .sd-apply-grid > .sd-apply { flex: 0 1 calc((100% - 28px) / 3); }
    @media (max-width: 899px) { .sd-apply-grid > .sd-apply { flex-basis: calc((100% - 14px) / 2); } }
    @media (max-width: 599px) { .sd-apply-grid > .sd-apply { flex-basis: 100%; } }
    .sd-apply { display: flex; align-items: center; gap: 14px; padding: 18px; border-radius: 14px; background: var(--sd-primary); color: #fff; text-decoration: none; font-weight: 800; font-size: 1.05rem; line-height: 1.3; box-shadow: 0 4px 14px rgba(240,85,55,.25); min-height: 84px; }
    .sd-apply:hover { background: var(--sd-primary-dark); color: #fff; }
    .sd-apply .ico { font-size: 1.9rem; flex: none; }
    .sd-apply .go { margin-left: auto; font-size: 1.3rem; flex: none; }
    .sd-apply .t-blk > .t-en { color: rgba(255,255,255,.9) !important; }
    .sd-apply.provider { background: #fff; color: var(--sd-ink); border: 2px solid var(--sd-line); box-shadow: none; }
    .sd-apply.provider:hover { border-color: var(--sd-primary); color: var(--sd-ink); }
    .sd-apply.provider .t-blk > .t-en { color: var(--sd-muted) !important; }
    /* form */
    .sd-form fieldset { border: 1px solid var(--sd-line); border-radius: 14px; padding: 18px; margin: 0 0 20px; background: #fff; min-width: 0; }
    .sd-form legend { font-weight: 900; padding: 0 8px; font-size: 1.05rem; }
    .sd-fields { display: grid; gap: 16px 18px; grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; }
    .sd-field { min-width: 0; }
    .sd-field .lbl { display: block; font-weight: 800; font-size: .92rem; margin-bottom: 6px; }
    .sd-field .req { color: var(--sd-primary); }
    .sd-field input[type=text], .sd-field input[type=email], .sd-field input[type=tel], .sd-field input[type=date], .sd-field input[type=number], .sd-field input[type=file], .sd-field select, .sd-field textarea { width: 100%; padding: 11px 12px; border: 1px solid #d1d5db; border-radius: 10px; background: #f9fafb; font: inherit; font-weight: 600; color: var(--sd-ink); }
    .sd-field input:focus, .sd-field select:focus, .sd-field textarea:focus { outline: 2px solid var(--sd-primary); outline-offset: 1px; background: #fff; }
    .sd-field .err, .sd-err { color: #b91c1c; font-size: .85rem; margin-top: 4px; }
    .sd-field.has-err input, .sd-field.has-err select, .sd-field.has-err textarea { border-color: #b91c1c; }
    .sd-field .hint { color: var(--sd-muted); font-size: .82rem; margin-top: 4px; }
    .sd-field .ok-msg { color: #15803d; font-size: .85rem; margin-top: 4px; font-weight: 700; }
    .sd-full { grid-column: 1 / -1; }
    .sd-opts { display: grid; gap: 8px; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); }
    .sd-opts.compact { grid-template-columns: repeat(auto-fill, minmax(118px, 1fr)); }
    .sd-opt { display: flex; gap: 8px; align-items: flex-start; padding: 10px 12px; border: 1px solid var(--sd-line); border-radius: 10px; cursor: pointer; font-size: .92rem; background: #fff; }
    .sd-opt input { margin-top: 4px; accent-color: var(--sd-primary); flex: none; }
    .sd-opt .opt-txt { display: block; min-width: 0; line-height: 1.35; }
    .sd-opt:has(input:checked) { border-color: var(--sd-primary); background: var(--sd-soft); }
    .sd-inline { display: flex; gap: 8px; align-items: stretch; }
    .sd-inline > input { flex: 1; min-width: 0; }
    .sd-picker { position: relative; }
    .sd-picker .list { position: absolute; z-index: 20; left: 0; right: 0; top: 100%; margin-top: 4px; max-height: 260px; overflow-y: auto; background: #fff; border: 1px solid var(--sd-line); border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.12); }
    .sd-picker .list button { display: block; width: 100%; text-align: left; padding: 9px 12px; border: 0; background: none; font: inherit; cursor: pointer; }
    .sd-picker .list button:hover, .sd-picker .list button.active { background: var(--sd-soft); }
    .sd-img-pv { display: block; max-width: 220px; max-height: 220px; margin-top: 8px; border-radius: 12px; border: 1px solid #e5e7eb; object-fit: cover; }
    .sd-selfie video { display: block; width: 100%; max-width: 320px; border-radius: 12px; background: #111; transform: scaleX(-1); }
    .sd-picked { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .sd-picked span { display: inline-flex; align-items: center; gap: 6px; background: var(--sd-soft); color: var(--sd-primary-dark); border-radius: 999px; padding: 5px 6px 5px 12px; font-size: .88rem; font-weight: 700; }
    .sd-picked button { border: 0; background: #fff; color: var(--sd-primary-dark); border-radius: 50%; width: 22px; height: 22px; cursor: pointer; line-height: 1; }
    .sd-declare ol, .sd-declare ul { padding-left: 20px; margin: 0 0 12px; font-size: .92rem; }
    .sd-declare li { margin-bottom: 8px; }
    .sd-captcha { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .sd-captcha b { font-size: 1.3rem; background: var(--sd-soft); padding: 6px 14px; border-radius: 10px; letter-spacing: 2px; }
    .sd-captcha input { width: 110px !important; }
    .sd-hp { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }
    @media (max-width: 700px) { .sd-fields { grid-template-columns: 1fr; } }
    @media (max-width: 600px) { .sd-section { padding: 32px 0; } .sd-btn { width: 100%; } .sd-hero { padding: 32px 0 24px; } .sd-inline { flex-direction: column; }
        .sd-form fieldset { padding: 14px; } .sd-opts, .sd-opts.compact { grid-template-columns: repeat(2, minmax(0, 1fr)); } .sd-opt { padding: 9px 10px; font-size: .88rem; } }
</style>
<?php
    $sdLangSwitcher = static function () use ($sdLangMode): void { ?>
        <div class="sd-lang" role="group" aria-label="भाषा / Language">
            <span>🌐 भाषा / Language:</span>
            <?php foreach (Lang::languages() as $code => $name): ?>
                <button type="button" data-jl-set="<?= htmlspecialchars($code) ?>" aria-pressed="<?= $code === $sdLangMode ? 'true' : 'false' ?>"><?= htmlspecialchars($name) ?></button>
            <?php endforeach; ?>
            <?php if (count(Lang::languages()) <= 3): ?>
                <span class="soon">क्षेत्रीय भाषाएँ जल्द / Regional languages coming soon</span>
            <?php endif; ?>
        </div>
    <?php };

    $sdNotice = static function (float $fee, string $money = '') use ($h): void { ?>
        <div class="sd-notice" role="note">
            <strong><?= Lang::t('⚠️ ज़रूरी सूचना', '⚠️ Important Notice') ?></strong>
            <ul>
                <li><?= Lang::t('यह भारत को कुशल बनाने की Jobsence पहल है – भारत सरकार की योजना नहीं।', 'This is Jobsence’s initiative to make India skilled – NOT a Government of India scheme.') ?></li>
                <li><?= Lang::t('एकमुश्त शुल्क ' . ($money !== '' ? $money : '₹' . number_format($fee, 0) . ' (GST सहित)') . ' किसी भी स्थिति में वापसी योग्य नहीं है।', 'One-time fee ' . ($money !== '' ? $money : '₹' . number_format($fee, 0) . ' (including GST)') . ' is non-refundable in any condition.') ?></li>
                <li><?= Lang::t('Jobsence किसी नौकरी, इंटर्नशिप या प्लेसमेंट की गारंटी नहीं देता।', 'Jobsence does not guarantee any job, internship or placement.') ?></li>
            </ul>
        </div>
    <?php };

    $sdWhatsapp = static function (string $number) use ($h): void {
        if ($number === '') {
            return;
        }
        $text = rawurlencode('नमस्ते, मुझे Jobsence के बारे में जानकारी चाहिए। / Hello, I want information about Jobsence.'); ?>
        <a class="sd-wa" href="https://wa.me/<?= $h($number) ?>?text=<?= $h($text) ?>" target="_blank" rel="noopener" aria-label="WhatsApp पर बात करें / Chat on WhatsApp">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.88 1.21 3.08.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.69.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35zM12.04 21.5h-.01a9.43 9.43 0 0 1-4.8-1.32l-.35-.2-3.57.93.95-3.48-.22-.36a9.4 9.4 0 0 1-1.44-5.03c0-5.2 4.24-9.44 9.45-9.44a9.38 9.38 0 0 1 6.68 2.77 9.38 9.38 0 0 1 2.76 6.68c0 5.21-4.24 9.45-9.45 9.45zm8.04-17.49A11.3 11.3 0 0 0 12.04.67C5.77.67.67 5.77.67 12.04c0 2 .52 3.96 1.52 5.69L.57 23.6l6.02-1.58a11.35 11.35 0 0 0 5.44 1.39h.01c6.27 0 11.37-5.1 11.37-11.37 0-3.04-1.18-5.89-3.33-8.04z"/></svg>
        </a>
    <?php };
?>
<script>
document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-jl-set]');
    if (!b) return;
    var mode = b.getAttribute('data-jl-set');
    document.documentElement.setAttribute('data-jl', mode);
    document.cookie = '<?= Lang::COOKIE ?>=' + encodeURIComponent(mode) + ';path=/;max-age=31536000;SameSite=Lax';
    document.querySelectorAll('[data-jl-set]').forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
});
</script>
<?php } ?>
