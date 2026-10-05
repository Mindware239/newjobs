<?php
/**
 * Shared styles + the blinking tricolour "Young India" banner for the mentoring screens.
 * Requires apply/_partials.php ($t, $tb, $tp, $h).
 */
require dirname(__DIR__) . '/apply/_partials.php';
require dirname(__DIR__, 2) . '/include/_young_india.php';

use App\Services\Registration\FormRegistry;

if (!isset($mtShared)) {
    $mtShared = true;
    $mtTiming = static fn($v) => implode(', ', array_map(static fn($k) => FormRegistry::TIMINGS[$k][1] ?? $k, (array)$v));
    $mtQual = static fn($q) => isset(FormRegistry::QUALIFICATIONS[$q]) ? FormRegistry::QUALIFICATIONS[$q][1] : (string)$q;
?>
<style>
.sd .mt-hero { background: linear-gradient(180deg, #fff7ed 0%, #fff 55%, #f0fdf4 100%); border-bottom: 1px solid #e5e7eb; }
.sd .mt-hero h1 { font-size: clamp(1.6rem, 4.2vw, 2.4rem); font-weight: 900; margin: 10px 0 6px; line-height: 1.2; }
.sd .mt-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin: 14px 0 0; }
.sd .mt-tabs a { padding: 8px 14px; border-radius: 999px; border: 1px solid #e5e7eb; background: #fff; color: #111827; font-weight: 800; font-size: .9rem; text-decoration: none; }
.sd .mt-tabs a.on, .sd .mt-tabs a:hover { background: #138808; border-color: #138808; color: #fff; }
.sd .mt-search { display: grid; grid-template-columns: 2fr 1.2fr 1.2fr 1fr 1fr auto; gap: 8px; align-items: end; background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 12px; margin-top: 14px; box-shadow: 0 6px 18px rgba(0,0,0,.05); }
.sd .mt-f { display: flex; flex-direction: column; gap: 4px; font-size: .78rem; font-weight: 800; color: #374151; min-width: 0; }
.sd .mt-f input, .sd .mt-f select { height: 44px; padding: 0 10px; border: 1px solid #d1d5db; border-radius: 10px; font: inherit; font-size: .95rem; width: 100%; background: #fff; }
.sd .mt-search .sd-btn { height: 44px; padding-top: 0; padding-bottom: 0; }
@media (max-width: 960px) { .sd .mt-search { grid-template-columns: 1fr 1fr; } .sd .mt-search .mt-q, .sd .mt-search .sd-btn { grid-column: 1 / -1; } }
@media (max-width: 460px) { .sd .mt-search { grid-template-columns: 1fr; } }
.sd .mt-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px; margin: 14px 0; }
.sd .mt-stat { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 12px 14px; }
.sd .mt-stat .n { font-size: 1.8rem; font-weight: 900; color: #138808; line-height: 1; }
.sd .mt-bars { list-style: none; margin: 6px 0 0; padding: 0; font-size: .85rem; }
.sd .mt-bars li { display: flex; justify-content: space-between; gap: 8px; padding: 3px 0; border-bottom: 1px dashed #f3f4f6; }
.sd .mt-bars a { color: #111827; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sd .mt-bars b { color: #FF9933; }
.sd .mt-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 12px; }
@media (max-width: 400px) { .sd .mt-list { grid-template-columns: 1fr; } }
.sd .mt-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 14px 16px; min-width: 0; display: flex; flex-direction: column; gap: 4px; }
.sd .mt-card h3 { font-size: 1.05rem; margin: 0; overflow-wrap: anywhere; }
.sd .mt-meta { color: #4b5563; font-size: .86rem; overflow-wrap: anywhere; }
.sd .mt-skills { display: flex; flex-wrap: wrap; gap: 4px; margin: 4px 0; }
.sd .mt-skills span { padding: 2px 8px; border-radius: 999px; background: #fff7ed; color: #9a3412; font-size: .74rem; font-weight: 800; }
.sd .mt-tag { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: .72rem; font-weight: 800; background: #ecfdf5; color: #047857; margin-right: 4px; }
.sd .mt-tag.w { background: #fef3c7; color: #92400e; }
.sd .mt-tag.g { background: #f3f4f6; color: #4b5563; }
.sd .mt-lock { font-size: .82rem; color: #6b7280; margin-top: auto; padding-top: 6px; }
.sd .mt-act { display: flex; gap: 6px; flex-wrap: wrap; margin-top: auto; padding-top: 8px; }
.sd .mt-act form { margin: 0; }
.sd .mt-act .sd-btn { padding: 8px 14px; font-size: .88rem; }
.sd .mt-green { background: #138808 !important; border-color: #138808 !important; }
.sd .mt-box { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 16px; margin-bottom: 14px; }
.sd .mt-pager { display: flex; gap: 8px; justify-content: center; margin: 16px 0; }
.sd .mt-pager a { padding: 6px 12px; border: 1px solid #e5e7eb; border-radius: 8px; text-decoration: none; color: #111827; font-weight: 700; }
.sd .mt-table { width: 100%; border-collapse: collapse; font-size: .9rem; }
.sd .mt-table td, .sd .mt-table th { padding: 8px 6px; border-bottom: 1px solid #f3f4f6; text-align: left; vertical-align: top; }
@media (max-width: 640px) { .sd .mt-table thead { display: none; } .sd .mt-table tr { display: block; border: 1px solid #e5e7eb; border-radius: 12px; margin-bottom: 8px; padding: 6px; } .sd .mt-table td { display: block; border: 0; padding: 3px 6px; } }
</style>
<?php
    /** Search bar shared by both directories. */
    $mtSearch = static function (array $f, bool $seekers) use ($h, $t, $tb): void { ?>
        <form method="GET" class="mt-search">
            <label class="mt-f mt-q"><span><?= $t('कीवर्ड – स्किल, नाम, योग्यता', 'Keyword – skill, name, qualification') ?></span>
                <input type="search" name="q" value="<?= $h($f['q']) ?>" placeholder="Electrician, Tally, Python, Ravi…"></label>
            <label class="mt-f"><span><?= $t('स्थान / पिन', 'Location / PIN') ?></span>
                <input type="text" name="loc" value="<?= $h($f['loc']) ?>" placeholder="Patna, 110001…"></label>
            <label class="mt-f"><span><?= $t('पसंदीदा स्थान', 'Preferred location') ?></span>
                <input type="text" name="pref" value="<?= $h($f['pref']) ?>" placeholder="Delhi, Online…"></label>
            <label class="mt-f"><span><?= $t('योग्यता', 'Qualification') ?></span>
                <select name="qual"><option value="">—</option><?php foreach (FormRegistry::QUALIFICATIONS as $k => $l): ?><option value="<?= $h($k) ?>" <?= $f['qual'] === $k ? 'selected' : '' ?>><?= $h($l[1]) ?></option><?php endforeach; ?></select></label>
            <?php if ($seekers): ?>
            <label class="mt-f"><span><?= $t('समय', 'Timing') ?></span>
                <select name="timing"><option value="">—</option><?php foreach (FormRegistry::TIMINGS as $k => $l): ?><option value="<?= $h($k) ?>" <?= $f['timing'] === $k ? 'selected' : '' ?>><?= $h($l[1]) ?></option><?php endforeach; ?></select></label>
            <?php else: ?><span></span><?php endif; ?>
            <button class="sd-btn mt-green" type="submit"><?= $tb('खोजें', 'Search') ?></button>
        </form>
    <?php };

    $mtFlash = static function (?array $flash) use ($tp): void {
        if ($flash) { ?><div class="sd-alert <?= $flash['ok'] ? 'ok' : 'err' ?>" role="status" style="margin-top:12px"><?= $tp($flash['msg']) ?></div><?php }
    };

    /** Tabs across all seeker / provider directories; $active is the current path. */
    $mtTabs = static function (string $active) use ($h, $tp, $t): void { ?>
        <nav class="mt-tabs" aria-label="Mentoring">
            <?php foreach (\App\Services\Registration\Mentoring::KINDS as $kk): if ($kk['seekers_page']): ?>
                <a href="<?= $h($kk['seekers_page']) ?>" class="<?= $active === $kk['seekers_page'] ? 'on' : '' ?>"><?= $tp($kk['seekers_label']) ?></a>
            <?php endif; endforeach; ?>
            <?php foreach (\App\Services\Registration\Mentoring::KINDS as $kk): ?>
                <a href="<?= $h($kk['providers_page']) ?>" class="<?= $active === $kk['providers_page'] ? 'on' : '' ?>"><?= $tp($kk['providers_label']) ?></a>
            <?php endforeach; ?>
            <a href="/mentoring" class="<?= $active === '/mentoring' ? 'on' : '' ?>"><?= $t('मेरा डैशबोर्ड', 'My dashboard') ?></a>
        </nav>
    <?php };

    $mtCsrf = static fn() => '<input type="hidden" name="_token" value="' . htmlspecialchars((string)($_SESSION['csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8') . '">';
}
