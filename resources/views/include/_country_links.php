<?php
/**
 * "Jobsence is open for: Nepal · Sri Lanka …" and "Mentors welcome from: … Russia · Israel · USA" – every country a
 * link to its own page (/jobsence-in/{slug}) in its language. $clStyle: optional inline style for the wrapper.
 */
use App\Controllers\Front\CountryPagesController;
use App\Helpers\Lang;

$clPages = CountryPagesController::all();
$clH = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$clLink = static fn(string $slug, array $p) => '<a href="/jobsence-in/' . $clH($slug) . '" lang="' . $clH($p['lang']) . '" title="' . $clH($p['name']) . '" style="color:inherit;text-decoration:underline;text-underline-offset:3px;white-space:nowrap">'
    . $p['flag'] . ' ' . $clH($p['native']) . ($p['native'] !== $p['name'] ? ' <small style="opacity:.75">(' . $clH($p['name']) . ')</small>' : '') . '</a>';
?>
<div style="<?= $clH($clStyle ?? 'text-align:center;margin:0 0 10px') ?>">
    <p style="margin:0 0 4px;font-weight:800;color:#3730a3">🤝 <?= Lang::t('Jobsence इनके लिए खुला है:', 'Jobsence is open for:') ?>
        <span style="white-space:nowrap">🇮🇳 भारत (India)</span>
        <?php foreach ($clPages as $slug => $p) { if (!empty($p['open'])) { echo ' · ' . $clLink($slug, $p); } } ?>
    </p>
    <p style="margin:0;font-weight:700;color:#6d28d9;font-size:.95em">🎓 <?= Lang::t('इन देशों से भी मेंटर जुड़ें:', 'Mentors welcome from:') ?>
        <?php $first = true; foreach ($clPages as $slug => $p) { if (empty($p['open'])) { echo ($first ? '' : ' · ') . $clLink($slug, $p); $first = false; } } ?>
        <?= Lang::t('और हर देश से', 'and every country') ?>
    </p>
</div>
