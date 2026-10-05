<?php
/**
 * Compact "Skills we teach / Mentors we are looking for" section.
 * Include anywhere after front/apply/_partials.php. Set $sdShowcaseLimit (default 12) before including.
 */
use App\Helpers\Lang;
use App\Services\Registration\MentorMatching;

require __DIR__ . '/../apply/_partials.php'; // guarded: only outputs styles/scripts once per page

$sdShowcaseLimit = $sdShowcaseLimit ?? 12;
try {
    $sdShowcase = MentorMatching::skillShowcase($sdShowcaseLimit);
} catch (\Throwable $e) {
    $sdShowcase = ['offered' => [], 'wanted' => []];
}
?>
<section class="sd sd-section" style="background:#fff">
    <div class="sd-wrap">
        <div style="text-align:center;margin-bottom:20px">
            <h2 style="margin-bottom:6px"><?= Lang::b('स्किल्स जो हम सिखाते हैं और मेंटर जिनकी हमें तलाश है', 'Skills We Teach & Mentors We Are Looking For') ?></h2>
            <p class="sd-lead" style="margin:0"><?= Lang::t('सीखना चाहते हैं? या किसी स्किल में माहिर हैं? दोनों के लिए Jobsence पर जगह है।', 'Want to learn? Or are you an expert in a skill? There is a place for both on Jobsence.') ?></p>
        </div>
        <div class="sd-grid" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr))">
            <div class="sd-card">
                <h3>🎓 <?= Lang::b('ट्रेनिंग उपलब्ध', 'Training Available') ?></h3>
                <?php if ($sdShowcase['offered']): ?>
                    <div class="sd-chips">
                        <?php foreach ($sdShowcase['offered'] as $s): ?>
                            <span class="sd-chip" title="<?= (int)$s['mentors'] ?> mentor(s)">
                                <?= $h($s['skill']) ?>
                                <small style="font-weight:600;opacity:.8"><?= $s['online'] ? ' · 💻' : '' ?><?= $s['offline'] ? ' · 🏫' : '' ?></small>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <p style="font-size:.82rem;color:#4b5563;margin:10px 0 0">💻 <?= Lang::t('ऑनलाइन', 'Online') ?> · 🏫 <?= Lang::t('ऑफलाइन दिल्ली-NCR', 'Offline Delhi-NCR') ?></p>
                <?php else: ?>
                    <p style="color:#4b5563"><?= Lang::t('हमारी मेंटर टीम बन रही है – पहले मेंटर बनें!', 'Our mentor team is forming – be one of the first mentors!') ?></p>
                <?php endif; ?>
                <div class="sd-cta-row" style="margin-top:14px"><a class="sd-btn small" href="/apply/skill-development"><?= Lang::t('सीखने के लिए आवेदन करें', 'Apply to learn') ?></a></div>
            </div>
            <div class="sd-card">
                <h3>👩‍🏫 <?= Lang::b('इन स्किल्स के लिए मेंटर चाहिए', 'Mentors Wanted For') ?></h3>
                <div class="sd-chips">
                    <?php foreach ($sdShowcase['wanted'] as $s): ?>
                        <span class="sd-chip" style="background:#fff7ed;color:#9a3412">
                            <?= $h($s['skill']) ?><?php if ($s['learners'] > 0): ?><small style="font-weight:600;opacity:.8"> · <?= (int)$s['learners'] ?> <?= Lang::t('इच्छुक', 'learners') ?></small><?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
                <div class="sd-cta-row" style="margin-top:14px"><a class="sd-btn small ghost" href="/apply/skill-provider"><?= Lang::t('मेंटर बनें', 'Become a mentor') ?></a> <a class="sd-btn small ghost" href="/skills"><?= Lang::t('सभी स्किल्स देखें', 'See all skills') ?></a></div>
            </div>
        </div>
    </div>
</section>
