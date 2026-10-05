<?php
require __DIR__ . '/../apply/_partials.php';
$sdShowcaseLimit = 200;
?>
<div class="sd">
    <section class="sd-hero">
        <div class="sd-wrap" style="text-align:center">
            <div style="display:flex;justify-content:flex-end;margin-bottom:12px"><?php $sdLangSwitcher(); ?></div>
            <span class="sd-badge">भारत को कुशल बनाने की Jobsence पहल</span>
            <h1><?= $tb('स्किल्स, मेंटर और ट्रेनिंग', 'Skills, Mentors & Training') ?></h1>
            <p class="sd-lead" style="max-width:760px;margin:0 auto"><?= $t(
                'देखें किन स्किल्स की ट्रेनिंग अभी उपलब्ध है (ऑनलाइन या दिल्ली-NCR में ऑफलाइन) और किन स्किल्स के लिए हम मेंटर ढूँढ रहे हैं। सीखने वाले अधिकतम 5 स्किल चुन सकते हैं।',
                'See which skills we offer training in right now (online or offline in Delhi-NCR) and which skills we are looking for mentors in. Learners can choose up to 5 skills.'
            ) ?></p>
            <div class="sd-cta-row" style="justify-content:center">
                <a class="sd-btn" href="/apply/skill-development"><?= $tb('सीखने के लिए आवेदन करें – ₹155', 'Apply to Learn – ₹155') ?></a>
                <a class="sd-btn ghost" href="/apply/skill-provider"><?= $tb('मेंटर / ट्रेनर बनें', 'Become a Mentor / Trainer') ?></a>
            </div>
        </div>
    </section>
</div>
<?php require __DIR__ . '/_skills_showcase.php'; ?>
<div class="sd">
    <section class="sd-section alt">
        <div class="sd-wrap">
            <div class="sd-grid">
                <div class="sd-card"><h3>1️⃣ <?= $tb('5 स्किल चुनें', 'Choose up to 5 skills') ?></h3><p><?= $t('पसंद के क्रम में – आपको इन्हीं स्किल्स में ट्रेनिंग मिलेगी।', 'In order of preference – you are trained only in these skills.') ?></p></div>
                <div class="sd-card"><h3>2️⃣ <?= $tb('मेंटर से जुड़ें', 'Get matched with a mentor') ?></h3><p><?= $t('चयन होने पर सबसे उपयुक्त मेंटर को अनुरोध भेजा जाता है।', 'Once selected, we send a request to the best-matching mentor.') ?></p></div>
                <div class="sd-card"><h3>3️⃣ <?= $tb('ऑनलाइन या ऑफलाइन चुनें', 'Online or offline') ?></h3><p><?= $t('मेंटर उपलब्ध न हो तो आप ऑनलाइन और ऑफलाइन मेंटर की सूची से खुद चुन सकते हैं।', 'If a mentor is not available, you can choose from the list of online and offline mentors yourself.') ?></p></div>
            </div>
            <div style="margin-top:20px"><?php $sdNotice((float)$fee); ?></div>
        </div>
    </section>
</div>
<?php $sdWhatsapp($whatsappNumber); ?>
