<?php
/** One job row: $j = ['title','company','state','city','posted','url','source','last_date']; $showPlace adds the place. */
$sjBadge = ['free' => ['free', 'मुफ़्त पोस्ट', 'Free post'], 'jobsence' => ['jobsence', 'Jobsence', 'Jobsence'], 'govt' => ['govt', 'सरकारी / PSU', 'Govt / PSU']][$j['source']] ?? ['free', '', ''];
?>
<article class="sj-job">
    <div><span class="sj-badge <?= $sjBadge[0] ?>"><?= $t($sjBadge[1], $sjBadge[2]) ?></span><a class="t" href="<?= $h($j['url']) ?>"><?= $h($j['title']) ?></a></div>
    <?php if ($j['posted']): ?>
        <time datetime="<?= $h(date('c', strtotime((string)$j['posted']))) ?>">🕒 <?= $h(date('d M Y, h:i A', strtotime((string)$j['posted']))) ?></time>
    <?php endif; ?>
    <div class="m"><?= $h($j['company']) ?><?php if (!empty($showPlace)): ?> · 📍 <?= $h(trim($j['city'] . ($j['city'] !== '' && $j['state'] !== \App\Services\JobBoard\StateJobBoard::ALL_INDIA ? ', ' : '') . ($j['state'] !== \App\Services\JobBoard\StateJobBoard::ALL_INDIA ? $j['state'] : ($j['city'] === '' ? 'All India' : '')))) ?><?php endif; ?><?php if (!empty($j['last_date'])): ?> · <?= $t('अंतिम तिथि', 'Last date') ?>: <?= $h(date('d M Y', strtotime((string)$j['last_date']))) ?><?php endif; ?></div>
</article>
