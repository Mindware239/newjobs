<?php
require __DIR__ . '/../apply/_partials.php';
$feeLabel = number_format((float)$fee, 0);
$free = (float)$fee <= 0; // job seekers free (FormRegistry::SEEKERS_FREE)
$courseLabel = number_format((float)$courseFee, 0);
$courseGst = number_format((float)$courseFee * 0.18, 0);
$placeHi = $stateHi ? "{$stateHi} के" : 'भारत के';
$placeEn = $stateEn ? "in {$stateEn}" : 'of India';
?>
<div class="sd">

    <section class="sd-hero">
        <div class="sd-wrap">
            <div style="display:flex;justify-content:flex-end;margin-bottom:12px"><?php $sdLangSwitcher(); ?></div>
            <span class="sd-badge">भारत को कुशल बनाने की Jobsence पहल · <?= $t('सरकारी योजना नहीं', 'Not a Govt. scheme') ?></span>
            <h1><?= $tb("{$placeHi} हर बेरोज़गार युवा के लिए कौशल विकास – गाँव से राजधानी तक", "Skill Development for Every Unemployed Youth {$placeEn} – From Village to Capital") ?></h1>
            <p class="sd-lead"><?= $t(
                ($free ? "रजिस्ट्रेशन मुफ़्त।" : "एकमुश्त शुल्क केवल ₹{$feeLabel} (GST सहित)।") . " 2000+ स्किल्स। पूरे भारत में ऑनलाइन वीडियो क्लास या दिल्ली-NCR में ऑफलाइन क्लास। ट्रेनिंग के बाद इंटर्नशिप और नौकरी के लिए आवेदन करें।",
                ($free ? "Registration is free." : "One-time fee only ₹{$feeLabel} (including GST).") . " 2000+ skills. Online video classes Pan-India or offline classes in Delhi-NCR. After training, apply for internships and jobs."
            ) ?></p>
            <div class="sd-cta-row">
                <a class="sd-btn" href="/apply/skill-development"><?= $free ? $tb('फॉर्म भरें – मुफ़्त', 'Fill Form Now – Free') : $tb("फॉर्म भरें – केवल ₹{$feeLabel}", "Fill Form Now – Pay ₹{$feeLabel} Only") ?></a>
                <a class="sd-btn ghost" href="/apply/skill-provider"><?= $tb('Jobsence मेंटर टीम से जुड़ें', 'Join the Jobsence Mentor Team') ?></a>
            </div>
            <div style="margin-top:24px"><?php $sdNotice((float)$fee); ?></div>
        </div>
    </section>

</div>
<?php $sdShowcaseLimit = 16; require __DIR__ . '/_skills_showcase.php'; ?>
<div class="sd">
    <section class="sd-section">
        <div class="sd-wrap">
            <h2><?= $tb('कौन आवेदन कर सकता है?', 'Who Can Apply?') ?></h2>
            <p><?= $t('कोई भी बेरोज़गार – पढ़ा-लिखा हो या नहीं, गाँव से हो या शहर से।', 'Anyone who is unemployed – educated or not, from a village or a city.') ?></p>
            <div class="sd-chips">
                <?php foreach ([
                    ['अशिक्षित', 'Uneducated'], ['10वीं पास', '10th Pass'], ['12वीं पास', '12th Pass'], ['आईटीआई / डिप्लोमा', 'ITI / Diploma'], ['ग्रेजुएट', 'Graduates'],
                    ['इंजीनियर', 'Engineers'], ['डॉक्टर', 'Doctors'], ['ड्राइवर', 'Drivers'], ['गृहिणी', 'Homemakers'], ['दिहाड़ी मज़दूर', 'Daily wage workers'],
                    ['विद्यार्थी', 'Students'], ['गाँव व शहर के युवा', 'Village & city youth'],
                ] as $who): ?>
                    <span class="sd-chip"><?= $tp($who) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="sd-section alt">
        <div class="sd-wrap">
            <h2><?= $tb('यह कैसे काम करता है?', 'How It Works – 4 Simple Steps') ?></h2>
            <div class="sd-grid">
                <?php foreach ([
                    ['फॉर्म भरें', 'Fill the form', 'हिंदी या अंग्रेज़ी में अपनी जानकारी और पसंदीदा स्किल भरें।', 'Enter your details and preferred skills in Hindi or English.'],
                    $free ? ['ईमेल OTP से पुष्टि करें', 'Confirm with email OTP', 'फॉर्म मुफ़्त है – ईमेल OTP से पुष्टि होते ही रजिस्ट्रेशन पूरा।', 'The form is free – your registration is complete once your email OTP is confirmed.']
                          : ["₹{$feeLabel} भुगतान करें", "Pay ₹{$feeLabel}", 'UPI, कार्ड या नेट बैंकिंग से सुरक्षित ऑनलाइन भुगतान।', 'Pay securely online via UPI, card or net banking.'],
                    ['जाँच का इंतज़ार करें', 'Wait for scrutiny', 'फॉर्म और रिज़्यूमे की जाँच में कम से कम 3–6 महीने लगते हैं।', 'Scrutiny of forms and resumes takes minimum 3–6 months.'],
                    ['क्लास जॉइन करें', 'Join classes', 'चयन के बाद ऑनलाइन या दिल्ली-NCR में ऑफलाइन ट्रेनिंग शुरू करें।', 'After selection, start training online or offline in Delhi-NCR.'],
                ] as $i => $step): ?>
                    <div class="sd-card sd-step">
                        <b class="num"><?= $i + 1 ?></b>
                        <h3><?= $tb($step[0], $step[1]) ?></h3>
                        <p><?= $t($step[2], $step[3]) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="sd-cta-row"><a class="sd-btn" href="/apply/skill-development"><?= $tb('अभी फॉर्म भरें', 'Fill Form Now') ?></a></div>
        </div>
    </section>

    <section class="sd-section">
        <div class="sd-wrap">
            <h2><?= $tb('कोर्स की जानकारी', 'Course Details, Training Modes & Languages') ?></h2>
            <div class="sd-grid">
                <div class="sd-card"><h3>⏱️ <?= $tb('समय', 'Schedule') ?></h3><p><?= $t('रोज़ 2 घंटे, हफ्ते में 3 दिन, प्रैक्टिकल ट्रेनिंग सहित।', '2 hours/day, 3 days a week, including practical training.') ?></p></div>
                <div class="sd-card"><h3>💻 <?= $tb('ऑनलाइन – पूरे भारत में', 'Online Video Classes – Pan-India') ?></h3><p><?= $t('मोबाइल या कंप्यूटर से कहीं से भी सीखें।', 'Learn from anywhere on your mobile or computer.') ?></p></div>
                <div class="sd-card"><h3>🏫 <?= $tb('ऑफलाइन – केवल दिल्ली-NCR', 'Offline Classes – Delhi-NCR only') ?></h3><p><?= $t('प्रैक्टिकल ट्रेनिंग के साथ क्लासरूम में पढ़ाई।', 'Classroom learning with hands-on practical training.') ?></p></div>
                <div class="sd-card"><h3>🗣️ <?= $tb('भाषा', 'Languages') ?></h3><p><?= $t('हिंदी, अंग्रेज़ी या स्थानीय / क्षेत्रीय भाषा – मेंटर की उपलब्धता के अनुसार।', 'Hindi, English or local/regional language – depending on mentor availability.') ?></p></div>
            </div>
            <h3 style="margin-top:28px"><?= $tb('2000+ स्किल्स – कुछ उदाहरण', '2000+ skills – a few examples') ?></h3>
            <div class="sd-chips">
                <?php foreach ($popularSkills as $skill): ?><span class="sd-chip" lang="en"><?= $h($skill) ?></span><?php endforeach; ?>
            </div>
            <p style="margin-top:12px;font-size:.92rem;color:#4b5563"><?= $t('ट्रेनिंग Jobsence मेंटर टीम और थर्ड-पार्टी स्किल डेवलपमेंट पार्टनर्स द्वारा दी जाती है।', 'Training is delivered by the Jobsence Mentor Team and third-party skill development partners.') ?></p>
        </div>
    </section>

    <section class="sd-section alt" id="fees">
        <div class="sd-wrap">
            <h2><?= $tb('फीस विवरण', 'Fee Structure') ?></h2>
            <table class="sd-table">
                <tbody>
                    <tr><td><?= $t('रजिस्ट्रेशन (प्रोसेसिंग) शुल्क', 'Registration (processing) fee') ?></td><td class="amt"><?= $free ? $t('मुफ़्त', 'Free') : '₹' . $h($feeLabel) . ' ' . $t('(GST सहित)', '(incl. GST)') ?></td></tr>
                    <tr><td><?= $t('चयन के बाद कुल कोर्स फीस', 'Total course fee after selection') ?></td><td class="amt">₹<?= $h($courseLabel) ?> + 18% GST (₹<?= $h($courseGst) ?>)</td></tr>
                </tbody>
            </table>
            <p style="margin-top:12px;font-size:.92rem;color:#4b5563"><?= $free ? $t('कोर्स फीस वापसी योग्य नहीं है।', 'The course fee is non-refundable.') : $t('दोनों शुल्क वापसी योग्य नहीं हैं।', 'Both fees are non-refundable.') ?></p>
        </div>
    </section>

    <section class="sd-section" id="mentors">
        <div class="sd-wrap">
            <h2><?= $tb('Jobsence मेंटर टीम से जुड़ें', 'Join the Jobsence Mentor Team') ?></h2>
            <p><?= $t('Jobsence भारत के बेरोज़गार युवाओं को स्किल देने के लिए अपनी मेंटर टीम बना रहा है। अगर आप किसी भी स्किल में अच्छे हैं, तो नामांकन करें।', 'Jobsence is building its own Team of Mentors to give skills to unemployed youth of India. If you are good at any skill, enrol yourself.') ?></p>
            <div class="sd-grid">
                <div class="sd-card"><h3>💰 <?= $tb('तय मानदेय', 'Fixed Emolument') ?></h3><p><?= $t('प्रति प्रशिक्षार्थी तय भुगतान – वीडियो / ऑनलाइन और ऑफलाइन (दिल्ली-NCR) क्लास के लिए, ट्रेनिंग पूरी होने के बाद।', 'Fixed payment per candidate – for video / online and offline (Delhi-NCR) classes, paid after training is completed.') ?></p></div>
                <div class="sd-card"><h3>🎥 <?= $tb('डेमो वीडियो', 'Demo Video') ?></h3><p><?= $t('अपनी जानकारी के साथ डेमो वीडियो भेजें – वीडियो में “This video is for Jobsence Skill Development” बताएँ।', 'Send your details with a demo video that mentions “This video is for Jobsence Skill Development”.') ?></p></div>
                <div class="sd-card"><h3>📜 <?= $tb('शर्तें', 'Conditions') ?></h3><p><?= $t("सभी वीडियो Jobsence की संपत्ति होंगी। ईमेल OTP सत्यापन ज़रूरी। एकमुश्त शुल्क ₹{$feeLabel}।", "All videos become Jobsence property. Email OTP verification required. One-time fee ₹{$feeLabel}.") ?></p></div>
            </div>
            <div class="sd-cta-row"><a class="sd-btn ghost" href="/apply/skill-provider"><?= $tb('मेंटर नामांकन फॉर्म भरें', 'Fill Mentor Enrolment Form') ?></a></div>
        </div>
    </section>

    <section class="sd-section alt" id="disclaimer">
        <div class="sd-wrap sd-declare">
            <h2><?= $tb('घोषणा व अस्वीकरण', 'Declaration & Disclaimer') ?></h2>
            <ol>
                <li><?= $free ? $t('रजिस्ट्रेशन मुफ़्त है – Jobsence नौकरी या ट्रेनिंग के चयन के लिए कभी पैसे नहीं माँगता।', 'Registration is free – Jobsence never asks for money to select you for a job or training.') : $t("₹{$feeLabel} प्रोसेसिंग शुल्क (GST सहित) वापसी योग्य नहीं है।", "The ₹{$feeLabel} processing fee (including GST) is non-refundable.") ?></li>
                <li><?= $t("चयन के बाद ₹{$courseLabel} + 18% GST कोर्स फीस भी वापसी योग्य नहीं है।", "After selection, the ₹{$courseLabel} + 18% GST course fee is also non-refundable.") ?></li>
                <li><?= $t('Jobsence किसी नौकरी या प्लेसमेंट की गारंटी नहीं देता।', 'Jobsence does not guarantee any job or placement.') ?></li>
                <li><?= $t('हम केवल रिज़्यूमे कंपनियों तक पहुँचाने, जॉब-रेडी बनाने या स्व-रोज़गार में मार्गदर्शन की कोशिश करते हैं।', 'We only try to push your resume to companies, make you job-ready, or guide you towards self-employment.') ?></li>
                <li><?= $t('यह भारत को कुशल बनाने की Jobsence पहल है – भारत सरकार की योजना नहीं।', 'This is Jobsence’s initiative to make India skilled – not a Government of India scheme.') ?></li>
                <li><?= $t('फॉर्म और रिज़्यूमे की जाँच में कम से कम 3–6 महीने लगते हैं।', 'Scrutiny of forms and resumes takes minimum 3–6 months.') ?></li>
            </ol>
        </div>
    </section>

    <section class="sd-section sd-faq" id="faq">
        <div class="sd-wrap">
            <h2><?= $tb('अक्सर पूछे जाने वाले सवाल', 'Frequently Asked Questions') ?></h2>
            <?php foreach ($faqs as $faq): ?>
                <details>
                    <summary><?= $t($faq[0], $faq[1]) ?></summary>
                    <p><?= $t($faq[2], $faq[3]) ?></p>
                </details>
            <?php endforeach; ?>
            <div class="sd-cta-row">
                <a class="sd-btn" href="/apply/skill-development"><?= $free ? $tb('फॉर्म भरें – मुफ़्त', 'Fill Form Now – Free') : $tb("फॉर्म भरें – ₹{$feeLabel}", 'Fill Form Now') ?></a>
                <a class="sd-btn ghost" href="/apply"><?= $tb('इंटर्नशिप / नौकरी के फॉर्म', 'Internship / Job forms') ?></a>
            </div>
        </div>
    </section>

    <section class="sd-section alt">
        <div class="sd-wrap">
            <h2><?= $tb('राज्य अनुसार कौशल विकास', 'Skill Development by State / UT') ?></h2>
            <nav class="sd-states" aria-label="States">
                <?php foreach ($states as $slug => $names): ?>
                    <a href="/skill-development/<?= $h($slug) ?>"><?= $h($names[1]) ?> / <?= $h($names[0]) ?></a>
                <?php endforeach; ?>
            </nav>
        </div>
    </section>
</div>
<?php $sdWhatsapp($whatsappNumber); ?>
