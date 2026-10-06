<?php
/**
 * Blinking "See Jobs in India" bar at the very top of every page:
 * latest Railways / Army / Police / State Govt notifications scroll beside it.
 */
$ijTicker = [];
try {
    $ijTicker = \App\Models\ExternalJob::ticker(12);
} catch (\Throwable $e) {
    $ijTicker = [];
}
$ijHi = (($_COOKIE['jl_lang'] ?? 'both') === 'hi');
$ijFallback = ['Indian Railways', 'Indian Army', 'Navy & Air Force', 'Police Bharti', 'State Govt Jobs', 'PSU – GAIL, BHEL, NTPC', 'Bank Jobs', 'Reliance, Tata & more'];
$ijItems = $ijTicker
    ? array_map(static fn($j) => ['/india-jobs/' . (int)$j['id'] . '-' . $j['slug'], $j['org_name'] . ': ' . $j['title']], $ijTicker)
    : array_map(static fn($l) => ['/india-jobs', $l], $ijFallback);
?>
<style>
.ijbar { position: relative; z-index: 11; display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 8px 12px; padding: 8px 12px 6px; background: #111827; color: #fff; font-size: 13px; overflow: hidden; }
.ijbar-main { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px 12px; width: 100%; }
.ijbar-main a { font-size: 15px; padding: 7px 16px !important; letter-spacing: .2px; }
.ijbar-cta { flex: none; display: inline-flex; align-items: center; gap: 7px; font-weight: 900; color: #fff !important; text-decoration: none; background: #dc2626; padding: 4px 10px; border-radius: 999px; animation: ijbarBlink 1.2s steps(2, start) infinite; }
.ijbar-cta:hover { background: #f05537; animation-play-state: paused; }
.ijbar-dot { width: 8px; height: 8px; border-radius: 50%; background: #fff; }
@keyframes ijbarBlink { 50% { background: #f05537; box-shadow: 0 0 10px rgba(240, 85, 55, .8); } }
.ijbar-near { flex: none; font-weight: 900; color: #fff !important; text-decoration: none; background: #059669; padding: 4px 10px; border-radius: 999px; }
.ijbar-near:hover { background: #047857; }
.ijbar-yi { flex: none; font-weight: 900; color: #111827 !important; text-decoration: none; padding: 4px 10px; border-radius: 999px; background: linear-gradient(90deg, #FF9933 0 33%, #fff 33% 66%, #138808 66%); background-size: 300% 100%; animation: ijyi 3s linear infinite; border: 1px solid #fff; }
@keyframes ijyi { 0%, 100% { background-position: 0 0; box-shadow: 0 0 0 0 rgba(255, 153, 51, .7); } 50% { background-position: 100% 0; box-shadow: 0 0 0 4px rgba(19, 136, 8, .35); } }
.ijbar-flag { display: inline-block; width: 16px; height: 11px; vertical-align: -1px; border: 1px solid rgba(0,0,0,.2); background: linear-gradient(#FF9933 0 33%, #fff 33% 66%, #138808 66%); }
@media (prefers-reduced-motion: reduce) { .ijbar-yi { animation: none; } }
@media (max-width: 520px) { .ijbar-yi .l { display: none; } }
.ijbar-track { flex: 1 1 100%; min-width: 0; overflow: hidden; height: 22px; line-height: 22px; mask-image: linear-gradient(90deg, transparent, #000 4%, #000 96%, transparent); }
.ijbar-run { display: inline-flex; gap: 28px; white-space: nowrap; animation: ijbarRun 60s linear infinite; padding-left: 100%; }
.ijbar-track:hover .ijbar-run { animation-play-state: paused; }
.ijbar-run a { color: #e5e7eb !important; text-decoration: none; }
.ijbar-run a:hover { color: #fff !important; text-decoration: underline; }
.ijbar-run a::before { content: "●"; color: #f05537; margin-right: 8px; font-size: 10px; }
@keyframes ijbarRun { to { transform: translateX(-100%); } }
/* Senior citizen jobs – Jobsence USP: gold button with a blinking "NEW – first in India" badge */
.ijbar-senior { flex: none; display: inline-flex; align-items: center; gap: 6px; font-weight: 900; color: #111827 !important; text-decoration: none; background: linear-gradient(90deg, #fde68a, #fbbf24); padding: 4px 12px; border-radius: 999px; border: 2px solid #fff; box-shadow: 0 0 0 0 rgba(251,191,36,.7); animation: ijSenior 1.6s ease-in-out infinite; }
.ijbar-senior:hover { background: #fbbf24; }
.ijbar-new { background: #dc2626; color: #fff; font-size: 10px; font-weight: 900; padding: 1px 6px; border-radius: 999px; letter-spacing: .5px; animation: ijbarBlink 1s steps(2, start) infinite; }
@keyframes ijSenior { 50% { box-shadow: 0 0 0 5px rgba(251,191,36,.35); } }
@media (prefers-reduced-motion: reduce) { .ijbar-cta, .ijbar-run, .ijbar-senior, .ijbar-new { animation: none; } .ijbar-run { padding-left: 0; } }
@media (max-width: 480px) { .ijbar { font-size: 12px; padding: 6px 8px 4px; } .ijbar-main a { font-size: 13px; padding: 6px 11px !important; } }
</style>
<div class="ijbar" role="region" aria-label="See Jobs in India">
    <div class="ijbar-main">
    <a class="ijbar-senior" href="/apply/senior-citizen-jobs" title="<?= $ijHi ? 'भारत में पहली बार – Jobsence द्वारा' : 'First in India – by Jobsence' ?>">👴 <?= $ijHi ? 'वरिष्ठ नागरिक नौकरियाँ (59+)' : 'Senior Citizen Jobs (59+)' ?> <span class="ijbar-new"><?= $ijHi ? 'नया – भारत में पहली बार' : 'NEW – first in India' ?></span></a>
    <a class="ijbar-cta" href="/india-jobs"><span class="ijbar-dot" aria-hidden="true"></span><?= $ijHi ? 'भारत की नौकरियाँ देखें' : 'See Jobs in India' ?></a>
    <a class="ijbar-yi" href="/mentoring"><span class="ijbar-flag" aria-hidden="true"></span> <span class="l"><?= $ijHi ? 'युवा भारत मेंटरिंग' : 'Young India Mentoring' ?></span></a>
    <a class="ijbar-near" href="/jobs-by-state" style="background:#f59e0b">📍 <?= $ijHi ? 'राज्य अनुसार नौकरियाँ' : 'Jobs by State A–Z' ?></a>
    <a class="ijbar-near" href="/near-me">📍 <?= $ijHi ? 'मेरे पास सेवा' : 'Near Me' ?></a>
    </div>
    <div class="ijbar-track">
        <div class="ijbar-run">
            <?php foreach ($ijItems as [$u, $label]): ?>
                <a href="<?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(mb_strimwidth($label, 0, 90, '…'), ENT_QUOTES, 'UTF-8') ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
