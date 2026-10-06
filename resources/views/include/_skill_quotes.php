<?php
/**
 * "Giving skill is punya" – classic lines on sharing knowledge (Sanskrit, Hindi, English, Chinese, Urdu,
 * Arabic) with meaning and source. $quotesMode: 'rotate' (homepage – one at a time, changes every few
 * seconds) or 'grid' (mentor form – all of them).
 */
use App\Helpers\Lang;
use App\Services\Registration\FormRegistry;

$sqMode = ($quotesMode ?? 'rotate') === 'grid' ? 'grid' : 'rotate';
$sqH = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$sqQuotes = FormRegistry::skillGivingQuotes();
$sqLangs = require dirname(__DIR__, 2) . '/data/skill_quotes_meanings.php'; // code => [name, dir?, m[10]]
?>
<section class="sq sq-<?= $sqMode ?>" aria-labelledby="sq-title-<?= $sqMode ?>" <?= $sqMode === 'rotate' ? 'aria-roledescription="carousel"' : '' ?>>
    <h2 id="sq-title-<?= $sqMode ?>" class="sq-title">🪔 <?= Lang::t('कौशल देना पुण्य है – मानवता की सेवा', 'Giving skill is punya – a good deed for humankind') ?></h2>
    <div class="sq-langbar">
        <label for="sq-ml-<?= $sqMode ?>"><?= Lang::t('अर्थ इस भाषा में', 'Meaning in') ?>:</label>
        <select id="sq-ml-<?= $sqMode ?>" class="sq-ml">
            <option value="">हिंदी + English</option>
            <?php foreach ($sqLangs as $code => $l): ?><option value="<?= $sqH($code) ?>"><?= $sqH($l['name']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="sq-list" <?= $sqMode === 'rotate' ? 'aria-live="polite"' : '' ?>>
        <?php foreach ($sqQuotes as $i => $q): ?>
            <figure class="sq-quote<?= $sqMode === 'rotate' && $i === 0 ? ' on' : '' ?>" <?= $sqMode === 'rotate' && $i > 0 ? 'hidden' : '' ?>>
                <blockquote lang="<?= $sqH($q['lang']) ?>" dir="<?= $sqH($q['dir'] ?? 'ltr') ?>"><?= nl2br($sqH($q['text'])) ?></blockquote>
                <p class="sq-meaning sq-default"><?= Lang::t($q['meaning'][0], $q['meaning'][1]) ?></p>
                <?php foreach ($sqLangs as $code => $l): if (isset($l['m'][$i])): ?>
                    <p class="sq-meaning" data-ml="<?= $sqH($code) ?>" lang="<?= $sqH($code) ?>" dir="<?= $sqH($l['dir'] ?? 'ltr') ?>"><?= $sqH($l['m'][$i]) ?></p>
                <?php endif; endforeach; ?>
                <figcaption><?= Lang::t('स्रोत', 'Source') ?>: <cite><?= Lang::t($q['source'][0], $q['source'][1]) ?></cite></figcaption>
            </figure>
        <?php endforeach; ?>
    </div>
    <?php if ($sqMode === 'rotate'): ?>
        <div class="sq-dots" role="tablist" aria-label="Quotes">
            <?php foreach ($sqQuotes as $i => $q): ?>
                <button type="button" class="sq-dot<?= $i === 0 ? ' on' : '' ?>" aria-label="<?= $i + 1 ?>" data-i="<?= $i ?>"></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<style>
    .sq { margin: 0 auto 18px; padding: 18px 16px; border-radius: 16px; background: linear-gradient(135deg, #fff7ed, #fefce8 55%, #f0fdf4); border: 1px solid #fde68a; max-width: 1100px; box-sizing: border-box; }
    .sq-rotate { margin: 16px auto; width: calc(100% - 32px); }
    .sq-title { margin: 0 0 12px; font-size: 1.15rem; font-weight: 800; color: #9a3412; text-align: center; }
    .sq-grid .sq-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px; }
    .sq-quote { margin: 0; padding: 14px 16px; background: #fff; border-radius: 12px; border-left: 4px solid #f59e0b; box-shadow: 0 1px 3px rgba(0,0,0,.06); display: flex; flex-direction: column; gap: 8px; }
    .sq-rotate .sq-quote { min-height: 170px; justify-content: center; text-align: center; border-left: 0; border-top: 4px solid #f59e0b; }
    .sq-rotate .sq-quote.on { animation: sq-in .6s ease; }
    @keyframes sq-in { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
    .sq-quote blockquote { margin: 0; font-size: 1.05rem; font-weight: 700; line-height: 1.6; color: #1f2937; }
    .sq-rotate .sq-quote blockquote { font-size: 1.25rem; }
    .sq-quote blockquote[dir="rtl"] { font-size: 1.15rem; line-height: 1.9; }
    .sq-grid .sq-quote blockquote[dir="rtl"] { text-align: right; }
    .sq-quote blockquote[lang="ur"] { font-family: "Noto Nastaliq Urdu", "Jameel Noori Nastaleeq", serif; line-height: 2.3; }
    .sq-meaning { margin: 0; font-size: .9rem; color: #4b5563; }
    .sq-meaning[data-ml] { display: none; }
    <?php foreach (array_keys($sqLangs) as $code): ?>.sq[data-ml-sel="<?= $code ?>"] .sq-meaning[data-ml="<?= $code ?>"] { display: block; }
    <?php endforeach; ?>.sq[data-ml-sel]:not([data-ml-sel=""]) .sq-default { display: none; }
    .sq-langbar { display: flex; justify-content: center; align-items: center; gap: 8px; flex-wrap: wrap; margin: -4px 0 12px; font-size: .88rem; font-weight: 700; color: #92400e; }
    .sq-ml { padding: 4px 8px; border-radius: 8px; border: 1px solid #fcd34d; background: #fff; font-size: .9rem; }
    .sq-quote figcaption { margin-top: auto; font-size: .82rem; font-weight: 800; color: #b45309; }
    .sq-quote cite { font-style: normal; }
    .sq-dots { display: flex; justify-content: center; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
    .sq-dot { width: 10px; height: 10px; padding: 0; border-radius: 50%; border: 0; background: #fcd34d; cursor: pointer; }
    .sq-dot.on { background: #c2410c; transform: scale(1.25); }
    @media (prefers-reduced-motion: reduce) { .sq-rotate .sq-quote.on { animation: none; } }
</style>
<script>
(function () {
    document.querySelectorAll('.sq .sq-ml').forEach(function (sel) {
        if (sel.dataset.bound) return; sel.dataset.bound = '1';
        var box = sel.closest('.sq');
        function set(v) { box.setAttribute('data-ml-sel', v); sel.value = v; }
        try { var saved = localStorage.getItem('sq_ml'); if (saved && sel.querySelector('option[value="' + saved + '"]')) set(saved); } catch (e) {}
        sel.addEventListener('change', function () { set(sel.value); try { localStorage.setItem('sq_ml', sel.value); } catch (e) {} });
    });
})();
</script>
<?php if ($sqMode === 'rotate'): ?>
<script>
(function () {
    var box = document.querySelector('.sq-rotate');
    var quotes = box.querySelectorAll('.sq-quote'), dots = box.querySelectorAll('.sq-dot'), i = 0, timer = null;
    function show(n) {
        quotes[i].hidden = true; quotes[i].classList.remove('on'); dots[i].classList.remove('on');
        i = (n + quotes.length) % quotes.length;
        quotes[i].hidden = false; quotes[i].classList.add('on'); dots[i].classList.add('on');
    }
    function start() { stop(); timer = setInterval(function () { show(i + 1); }, 6000); }
    function stop() { if (timer) clearInterval(timer); timer = null; }
    dots.forEach(function (d) { d.addEventListener('click', function () { show(+d.dataset.i); start(); }); });
    box.addEventListener('mouseenter', stop); box.addEventListener('mouseleave', start);
    box.addEventListener('focusin', stop); box.addEventListener('focusout', start);
    start();
})();
</script>
<?php endif; ?>
