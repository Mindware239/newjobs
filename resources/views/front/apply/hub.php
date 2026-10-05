<?php
$sdButtonsHeadingTag = 'h1';
require __DIR__ . '/_buttons.php';
$sdShowcaseLimit = 16;
require __DIR__ . '/../skill-development/_skills_showcase.php';
?>
<div class="sd">
    <section class="sd-section">
        <div class="sd-wrap">
            <h2><?= $tb('3 आसान कदम – आपकी यात्रा', 'Your Journey in 3 Steps') ?></h2>
            <div class="sd-grid">
                <?php foreach ([
                    ['🎓', 'कौशल विकास', 'Skill Development', 'पहले कुशल बनें – ऑनलाइन वीडियो या दिल्ली-NCR में ऑफलाइन।', 'First become skilled – online video or offline in Delhi-NCR.'],
                    ['🧑‍💻', 'इंटर्नशिप', 'Internship', 'प्रैक्टिकल अनुभव पाएँ।', 'Get practical experience.'],
                    ['💼', 'फुल-टाइम / पार्ट-टाइम / वर्क फ्रॉम होम', 'Full-time / Part-time / Work from Home', 'नियमित काम पाएँ।', 'Get regular work.'],
                ] as $i => $step): ?>
                    <div class="sd-card sd-step">
                        <b class="num"><?= $i + 1 ?></b>
                        <h3><?= $step[0] ?> <?= $tb($step[1], $step[2]) ?></h3>
                        <p><?= $t($step[3], $step[4]) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="sd-section alt">
        <div class="sd-wrap">
            <h2><?= $tb('सभी फॉर्म के नियम', 'Rules for All Forms') ?></h2>
            <div class="sd-grid">
                <div class="sd-card"><h3>💳 <?= $tb('₹155 शुल्क', '₹155 Fee') ?></h3><p><?= $t('एकमुश्त प्रोसेसिंग शुल्क ₹155 (GST सहित) – वापसी योग्य नहीं। पार्ट-टाइम जॉब: ₹250 + GST = ₹295, 3 महीने के लिए।', 'One-time processing fee ₹155 (including GST) – non-refundable. Part-time jobs: ₹250 + GST = ₹295 for 3 months.') ?></p></div>
                <div class="sd-card"><h3>🔎 <?= $tb('30,000+ कैटेगरी', '30,000+ Categories') ?></h3><p><?= $t('हर फॉर्म में खोजने योग्य 30,000+ कैटेगरी।', 'Every form has a searchable list of 30,000+ categories.') ?></p></div>
                <div class="sd-card"><h3>📧 <?= $tb('पुष्टि ईमेल', 'Confirmation Email') ?></h3><p><?= $t('भुगतान के बाद gm@jobsence.com से पुष्टि ईमेल।', 'Confirmation email from gm@jobsence.com after payment.') ?></p></div>
                <div class="sd-card"><h3>🗣️ <?= $tb('भाषा', 'Language') ?></h3><p><?= $t('हिंदी + अंग्रेज़ी में फॉर्म; क्षेत्रीय भाषाएँ जल्द।', 'Forms in Hindi + English; regional languages coming soon.') ?></p></div>
            </div>
            <div style="margin-top:20px"><?php $sdNotice((float)$fee); ?></div>
        </div>
    </section>
</div>
<?php $sdWhatsapp($whatsappNumber); ?>
