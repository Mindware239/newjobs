<?php
$cty = $c; // _partials.php reuses $c in its own loops
require dirname(__DIR__) . '/apply/_partials.php';
$en = static fn(string $k) => $h(str_replace('{c}', $cty['name'], $cty['en'][$k]));
$tr = static fn(string $k) => $h(str_replace('{c}', $cty['native'], $cty['t'][$k] ?? ''));
$same = $cty['lang'] === 'en';
$block = static function (string $k, string $tag = 'p', string $extra = '') use ($cty, $en, $tr, $same, $h): void {
    if (!isset($cty['t'][$k])) {
        return;
    } ?>
    <<?= $tag ?> <?= $extra ?>><span lang="<?= $h($cty['lang']) ?>" dir="<?= $h($cty['dir']) ?>" style="display:block"><?= $tr($k) ?></span><?php if (!$same): ?><span lang="en" style="display:block;font-size:.88em;color:#4b5563;margin-top:2px"><?= $en($k) ?></span><?php endif; ?></<?= $tag ?>>
<?php };
?>
<div class="sd">
    <section class="sd-section" style="background:linear-gradient(135deg,#eef2ff,#fff7ed)">
        <div class="sd-wrap" style="max-width:860px">
            <a href="/" class="ij-more">← Jobsence</a>
            <?php $block('title', 'h1', 'style="font-size:clamp(1.6rem,3.5vw,2.3rem);font-weight:900;margin:10px 0 8px"'); ?>
            <p style="font-size:2.4rem;margin:0" aria-hidden="true"><?= $cty['flag'] ?> 🤝 🇮🇳</p>
            <?php $block('intro', 'p', 'style="font-size:1.05rem;color:#1f2937"'); ?>
        </div>
    </section>
    <section class="sd-section">
        <div class="sd-wrap" style="max-width:860px;display:grid;gap:16px">
            <article class="sd-card" style="border-top:5px solid #7c3aed">
                <?php $block('mentor_h', 'h2', 'style="font-size:1.3rem;margin:0 0 8px;color:#6d28d9"'); ?>
                <?php $block('mentor_p'); ?>
                <a class="sd-btn" href="/apply/skill-provider" style="margin-top:8px"><span lang="<?= $h($cty['lang']) ?>"><?= $tr('mentor_btn') ?></span><?php if (!$same): ?> · <?= $en('mentor_btn') ?><?php endif; ?></a>
            </article>
            <?php if (!empty($cty['open'])): ?>
                <article class="sd-card" style="border-top:5px solid #f05537">
                    <?php $block('jobs_h', 'h2', 'style="font-size:1.3rem;margin:0 0 8px;color:#c2410c"'); ?>
                    <?php $block('jobs_p'); ?>
                    <a class="sd-btn" href="/apply/international-job" style="margin-top:8px"><span lang="<?= $h($cty['lang']) ?>"><?= $tr('jobs_btn') ?></span><?php if (!$same): ?> · <?= $en('jobs_btn') ?><?php endif; ?></a>
                </article>
            <?php endif; ?>
            <p style="font-size:.9rem"><b>🌍 Jobsence:</b>
                <?php foreach ($others as $s => $o): if ($s === $slug) { continue; } ?>
                    <a href="/jobsence-in/<?= $h($s) ?>" lang="<?= $h($o['lang']) ?>" style="margin-right:10px;white-space:nowrap"><?= $o['flag'] ?> <?= $h($o['native']) ?></a>
                <?php endforeach; ?>
            </p>
        </div>
    </section>
</div>
