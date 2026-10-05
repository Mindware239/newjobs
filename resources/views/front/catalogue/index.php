<?php
require dirname(__DIR__) . '/apply/_partials.php';
require dirname(__DIR__, 2) . '/include/_young_india.php';
use App\Controllers\Front\CatalogueController as C;
use App\Services\Registration\SkillTaxonomy as T;

$fmt = static fn($n) => number_format((int)$n);
$want = static fn(string $cat) => $m['want_url'] . rawurlencode($cat);
$offer = static fn(string $cat) => $m['offer_url'] . rawurlencode($cat);
$sum = static fn(array $skills) => T::sum($skills, $mode);
$ctBase = '/categories/' . $mode;
$perPage = 200;
$page = max(1, (int)($_GET['page'] ?? 1));
$counts = static function (array $s) use ($m, $t, $fmt): string {
    $out = [];
    if ($m['want_n'][1] !== '' && $s['want'] > 0) {
        $out[] = '<span class="ct-n w">' . $fmt($s['want']) . ' ' . $t($m['want_n'][0], $m['want_n'][1]) . '</span>';
    }
    if ($s['offer'] > 0) {
        $out[] = '<span class="ct-n o">' . $fmt($s['offer']) . ' ' . $t($m['offer_n'][0], $m['offer_n'][1]) . '</span>';
    }
    return implode(' ', $out);
};
$ctas = static function (string $cat, bool $small = false) use ($want, $offer, $m, $t, $h): string {
    return '<span class="ct-ctas' . ($small ? ' sm' : '') . '"><a class="ct-want" href="' . $h($want($cat)) . '">🎯 ' . $t($m['want'][0], $m['want'][1]) . '</a>'
        . '<a class="ct-offer" href="' . $h($offer($cat)) . '">🤝 ' . $t($m['offer'][0], $m['offer'][1]) . '</a></span>';
};
?>
<style>
.sd .ct-hero { background: linear-gradient(180deg, #fff7ed 0%, #fff 55%, #f0fdf4 100%); border-bottom: 1px solid #e5e7eb; }
.sd .ct-hero h1 { font-size: clamp(1.5rem, 4vw, 2.3rem); font-weight: 900; margin: 10px 0 6px; line-height: 1.2; }
.sd .ct-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin: 14px 0 6px; }
.sd .ct-tabs a { padding: 8px 14px; border-radius: 999px; border: 1px solid #e5e7eb; background: #fff; color: #111827; font-weight: 800; font-size: .9rem; text-decoration: none; }
.sd .ct-tabs a.on, .sd .ct-tabs a:hover { background: #0b3d91; border-color: #0b3d91; color: #fff; }
.sd .ct-crumbs { font-size: .85rem; color: #6b7280; } .sd .ct-crumbs a { color: #6b7280; }
.sd .ct-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 14px; }
@media (max-width: 400px) { .sd .ct-grid { grid-template-columns: 1fr; } }
.sd .ct-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 14px 16px; display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.sd .ct-card h2, .sd .ct-card h3 { font-size: 1.05rem; margin: 0; overflow-wrap: anywhere; }
.sd .ct-card h2 a, .sd .ct-card h3 a { color: #111827; text-decoration: none; } .sd .ct-card h2 a:hover, .sd .ct-card h3 a:hover { color: #f05537; }
.sd .ct-big { font-size: 1.6rem; font-weight: 900; color: #0b3d91; line-height: 1; }
.sd .ct-meta { color: #4b5563; font-size: .86rem; }
.sd .ct-n { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: .74rem; font-weight: 800; }
.sd .ct-n.w { background: #fff7ed; color: #c2410c; } .sd .ct-n.o { background: #ecfdf5; color: #047857; }
.sd .ct-chips { display: flex; flex-wrap: wrap; gap: 4px; }
.sd .ct-chips a { padding: 3px 9px; border-radius: 999px; background: #f9fafb; border: 1px solid #e5e7eb; color: #374151; font-size: .8rem; text-decoration: none; }
.sd .ct-chips a:hover { border-color: #f05537; color: #f05537; }
.sd .ct-ctas { display: flex; flex-wrap: wrap; gap: 6px; margin-top: auto; padding-top: 6px; }
.sd .ct-ctas a { flex: 1 1 140px; text-align: center; padding: 8px 10px; border-radius: 10px; font-weight: 900; font-size: .84rem; text-decoration: none; line-height: 1.3; }
.sd .ct-want { background: #FF9933; color: #111827; box-shadow: 0 0 0 0 rgba(255, 153, 51, .6); animation: ct-pulse 2.4s ease-in-out infinite; }
.sd .ct-offer { background: #138808; color: #fff; }
.sd .ct-want:hover { background: #f08a1f; } .sd .ct-offer:hover { background: #0f6e06; }
.sd .ct-ctas.sm a { flex: 0 0 auto; padding: 4px 9px; font-size: .76rem; animation: none; }
@keyframes ct-pulse { 50% { box-shadow: 0 0 0 5px rgba(255, 153, 51, .25); } }
@media (prefers-reduced-motion: reduce) { .sd .ct-want { animation: none; } }
.sd .ct-row { display: flex; justify-content: space-between; gap: 10px; align-items: center; flex-wrap: wrap; padding: 8px 4px; border-bottom: 1px solid #f3f4f6; }
.sd .ct-row b { overflow-wrap: anywhere; }
.sd .ct-filter { width: 100%; max-width: 420px; height: 44px; padding: 0 12px; border: 1px solid #d1d5db; border-radius: 10px; font: inherit; margin: 6px 0 10px; }
.sd .ct-pager { display: flex; gap: 8px; justify-content: center; margin: 14px 0; }
.sd .ct-pager a { padding: 6px 12px; border: 1px solid #e5e7eb; border-radius: 8px; text-decoration: none; color: #111827; font-weight: 700; }
</style>
<div class="sd">
    <section class="sd-section ct-hero">
        <div class="sd-wrap">
            <div style="display:flex;justify-content:flex-end;margin-bottom:10px"><?php $sdLangSwitcher(); ?></div>
            <?php $youngIndiaBanner('compact'); ?>
            <nav class="ct-tabs" aria-label="Catalogue">
                <?php foreach (C::MODES as $k => $x): ?><a class="<?= $k === $mode ? 'on' : '' ?>" href="/categories/<?= $h($k) ?>"><?= $tp($x['title']) ?></a><?php endforeach; ?>
            </nav>
            <nav class="ct-crumbs" aria-label="Breadcrumb">
                <a href="<?= $h($ctBase) ?>"><?= $tp($m['title']) ?></a>
                <?php if ($sector): ?> › <a href="<?= $h($ctBase . '/' . $sector['slug']) ?>"><?= $h($sector['short']) ?></a><?php endif; ?>
                <?php if ($sub): ?> › <?= $h($sub['name']) ?><?php endif; ?>
            </nav>

            <?php if (!$sector): ?>
                <h1><?= $tp($m['title']) ?></h1>
                <p style="margin:0;color:#4b5563"><b class="ct-big"><?= $fmt($total) ?></b> <?= $t($m['unit'][0], $m['unit'][1]) ?> · <?= count($tree) ?> <?= $t('कैटेगरी', 'categories') ?> · <?= array_sum(array_map(static fn($s) => count($s['subs']), $tree)) ?> <?= $t('सब-कैटेगरी', 'subcategories') ?></p>
            <?php elseif (!$sub): ?>
                <h1><?= $h($sector['name']) ?></h1>
                <p style="margin:0;color:#4b5563"><b class="ct-big"><?= $fmt($sector['count']) ?></b> <?= $t($m['unit'][0], $m['unit'][1]) ?> · <?= count($sector['subs']) ?> <?= $t('सब-कैटेगरी', 'subcategories') ?> <?= $counts($sum(array_merge(...array_column($sector['subs'], 'skills')))) ?></p>
            <?php else: ?>
                <h1><?= $h($sub['name']) ?> <small style="font-size:.55em;color:#6b7280">– <?= $h($sector['short']) ?></small></h1>
                <p style="margin:0 0 8px;color:#4b5563"><b class="ct-big"><?= $fmt(count($sub['skills'])) ?></b> <?= $t($m['unit'][0], $m['unit'][1]) ?> <?= $counts($sum($sub['skills'])) ?></p>
                <?= $ctas($sub['name']) ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="sd-section" style="padding-top:18px">
        <div class="sd-wrap">
            <?php if (!$sector): ?>
                <div class="ct-grid">
                    <?php foreach ($tree as $s): $all = array_merge(...array_column($s['subs'], 'skills')); ?>
                        <article class="ct-card">
                            <h2><a href="<?= $h($ctBase . '/' . $s['slug']) ?>"><?= $h($s['short']) ?></a></h2>
                            <div class="ct-meta"><b><?= $fmt($s['count']) ?></b> <?= $t($m['unit'][0], $m['unit'][1]) ?> · <?= count($s['subs']) ?> <?= $t('सब-कैटेगरी', 'subcategories') ?></div>
                            <div><?= $counts($sum($all)) ?></div>
                            <div class="ct-chips"><?php foreach (array_slice($s['subs'], 0, 6) as $x): ?><a href="<?= $h($ctBase . '/' . $s['slug'] . '/' . $x['slug']) ?>"><?= $h($x['name']) ?> (<?= $fmt(count($x['skills'])) ?>)</a><?php endforeach; ?></div>
                            <?= $ctas($s['subs'][0]['name'] ?? $s['short']) ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php elseif (!$sub): ?>
                <div class="ct-grid">
                    <?php foreach ($sector['subs'] as $x): ?>
                        <article class="ct-card">
                            <h3><a href="<?= $h($ctBase . '/' . $sector['slug'] . '/' . $x['slug']) ?>"><?= $h($x['name']) ?></a></h3>
                            <div class="ct-meta"><b><?= $fmt(count($x['skills'])) ?></b> <?= $t($m['unit'][0], $m['unit'][1]) ?> <?= $counts($sum($x['skills'])) ?></div>
                            <div class="ct-chips"><?php foreach (array_slice($x['skills'], 0, 8) as $sk): ?><a href="<?= $h($want($sk)) ?>"><?= $h($sk) ?></a><?php endforeach; ?><?php if (count($x['skills']) > 8): ?><a href="<?= $h($ctBase . '/' . $sector['slug'] . '/' . $x['slug']) ?>">+<?= $fmt(count($x['skills']) - 8) ?> <?= $t('और', 'more') ?></a><?php endif; ?></div>
                            <?= $ctas($x['name']) ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: $live = $live ?? []; $rows = array_slice($sub['skills'], ($page - 1) * $perPage, $perPage); ?>
                <input class="ct-filter" type="search" placeholder="<?= $h('इस सूची में खोजें / Filter this list') ?>" aria-label="Filter" oninput="var q=this.value.toLowerCase();document.querySelectorAll('.ct-row').forEach(function(r){r.style.display=r.dataset.n.indexOf(q)>-1?'':'none'})">
                <div>
                    <?php foreach ($rows as $sk): $l = $live[mb_strtolower($sk)] ?? []; ?>
                        <div class="ct-row" data-n="<?= $h(mb_strtolower($sk)) ?>">
                            <span><b><?= $h($sk) ?></b> <?= $counts(['want' => $l['want'] ?? 0, 'offer' => $l['offer'] ?? 0]) ?></span>
                            <?= $ctas($sk, true) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($sub['skills']) > $perPage): ?>
                    <div class="ct-pager">
                        <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>">← <?= $t('पिछला', 'Previous') ?></a><?php endif; ?>
                        <span style="padding:6px"><?= $page ?> / <?= (int)ceil(count($sub['skills']) / $perPage) ?></span>
                        <?php if ($page * $perPage < count($sub['skills'])): ?><a href="?page=<?= $page + 1 ?>"><?= $t('अगला', 'Next') ?> →</a><?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</div>
