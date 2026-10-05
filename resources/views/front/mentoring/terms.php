<?php
require __DIR__ . '/_shared.php';
use App\Services\Registration\Mentoring;
?>
<style>@media print { header, footer, .mt-noprint, .yi-band { display: none !important; } }</style>
<div class="sd">
    <section class="sd-section mt-hero">
        <div class="sd-wrap" style="max-width:900px">
            <div class="mt-noprint" style="display:flex;justify-content:flex-end;margin-bottom:10px"><?php $sdLangSwitcher(); ?></div>
            <nav class="mt-tabs mt-noprint" aria-label="Agreement type">
                <?php foreach (Mentoring::KINDS as $kk => $kc): ?><a href="/agreement-terms/<?= $h($kk) ?>" class="<?= $kk === $kind ? 'on' : '' ?>"><?= $tp($kc['what']) ?></a><?php endforeach; ?>
            </nav>
            <div class="mt-box" style="margin-top:14px">
                <span class="mt-tag g"><?= $t('मानक शर्तें', 'Standard terms') ?> · v<?= $h(Mentoring::AGREEMENT_VERSION) ?></span>
                <h1 style="margin:8px 0"><?= $t('त्रिपक्षीय समझौता', 'Tripartite Agreement') ?> – <?= $tp(Mentoring::KINDS[$kind]['what']) ?></h1>
                <p class="mt-meta" style="margin:0 0 12px"><?= $t('उम्मीदवार, प्रदाता और Jobsence (संचालक: ' . Mentoring::legalName() . ') के बीच। फ़ोन / ईमेल साझा होने से पहले दोनों पक्ष इसे ईमेल OTP से साइन करते हैं। “[स्किल / पद]” की जगह असली समझौते में चुना गया स्किल या पद होगा।', 'Between the candidate, the provider and Jobsence (operated by ' . Mentoring::legalName() . '). Both parties sign it with an email OTP before phone / email are shared. “[skill / role]” is replaced by the chosen skill or role in the actual agreement.') ?></p>
                <p class="mt-meta" style="margin:0 0 12px"><b><?= $h(Mentoring::legalName()) ?></b> · <?= $h(Mentoring::legalAddress()) ?> · gm@jobsence.com</p>
                <ol style="padding-left:20px;margin:0;list-style:none">
                    <?php foreach (Mentoring::clauses($kind, '[skill / role]') as $c): ?><li style="margin:8px 0"><?= $tp($c) ?></li><?php endforeach; ?>
                </ol>
                <div class="mt-act mt-noprint">
                    <button type="button" class="sd-btn ghost" onclick="window.print()"><?= $tb('प्रिंट / PDF', 'Print / save as PDF') ?></button>
                    <a class="sd-btn mt-green" href="<?= $h(Mentoring::KINDS[$kind]['seeker_form']) ?>"><?= $tb('रजिस्टर करें', 'Register') ?></a>
                </div>
            </div>
        </div>
    </section>
</div>
