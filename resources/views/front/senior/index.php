<?php
/**
 * /senior – Senior Citizen corner. Logged out: what seniors (59+) and organisations get + login / register.
 * Logged in (SeniorController, email OTP): all their senior / organisation registrations with nearby matches.
 */
require dirname(__DIR__) . '/apply/_partials.php';
$smWorkLabels = ['full_time' => ['फ़ुल-टाइम', 'Full-time'], 'part_time' => ['पार्ट-टाइम', 'Part-time'], 'community' => ['सामुदायिक सेवा (मुफ़्त)', 'Community service (free)']];
?>
<div class="sd">
    <section class="sd-section">
        <div class="sd-wrap" style="max-width:820px">
            <div style="display:flex;justify-content:flex-end;margin-bottom:12px"><?php $sdLangSwitcher(); ?></div>

            <?php if (!$me): ?>
                <div class="sd-card" style="border-top:5px solid #b45309">
                    <h1 style="font-size:1.6rem;margin:0 0 6px">👴 <?= $tb('वरिष्ठ नागरिक – अनुभव कभी रिटायर नहीं होता', 'Senior Citizens – Experience Never Retires') ?></h1>
                    <p style="margin:0 0 14px;color:#4b5563"><?= $t(
                        '59 वर्ष या उससे अधिक उम्र के रिटायर लोग घर के पास फ़ुल-टाइम, पार्ट-टाइम काम या मुफ़्त सामुदायिक सेवा के लिए जुड़ें। स्कूल, अस्पताल, RWA, NGO और कंपनियाँ आपके अनुभव से जुड़ेंगी।',
                        'Retired people aged 59 and above connect for full-time or part-time work, or free community service, near home. Schools, hospitals, RWAs, NGOs and companies connect with your experience.'
                    ) ?></p>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;margin-bottom:14px">
                        <div style="border:1px solid #fde68a;background:#fffbeb;border-radius:14px;padding:14px">
                            <h2 style="font-size:1.1rem;margin:0 0 6px">🧓 <?= $t('वरिष्ठ नागरिक (59+)', 'Senior citizens (59+)') ?></h2>
                            <ul style="padding-left:18px;margin:0 0 10px;font-size:.92rem">
                                <li><?= $t('रजिस्ट्रेशन मुफ़्त', 'Registration is free') ?></li>
                                <li><?= $t('सामुदायिक सेवा या पेड काम – आप चुनें', 'Community service or paid work – you choose') ?></li>
                                <li><?= $t('केवल घर के पास (1 से 10 किमी)', 'Only near home (1 to 10 km)') ?></li>
                            </ul>
                            <a class="sd-btn" href="/apply/senior-citizen-jobs"><?= $t('मुफ़्त रजिस्टर करें', 'Register free') ?></a>
                        </div>
                        <div style="border:1px solid #e5e7eb;background:#fff;border-radius:14px;padding:14px">
                            <h2 style="font-size:1.1rem;margin:0 0 6px">🤝 <?= $t('संस्थाएँ', 'Organisations') ?></h2>
                            <ul style="padding-left:18px;margin:0 0 10px;font-size:.92rem">
                                <li><?= $t('सामुदायिक सेवा (स्वयंसेवा) – मुफ़्त', 'Community service (volunteer) – free') ?></li>
                                <li><?= $t('पेड काम – ₹500 प्रति नौकरी (GST सहित), 7 दिन', 'Paid work – ₹500 per job (incl. GST), 7 days') ?></li>
                                <li><?= $t('पास रहने वाले वरिष्ठ नागरिक मोबाइल नंबर के साथ', 'Seniors living nearby, with mobile number') ?></li>
                            </ul>
                            <a class="sd-btn ghost" href="/apply/senior-citizen-hiring"><?= $t('वरिष्ठ नागरिकों को जोड़ें', 'Engage senior citizens') ?></a>
                        </div>
                    </div>
                    <p style="margin:0"><a class="sd-btn" href="/login/senior">🔑 <?= $t('वरिष्ठ नागरिक लॉगिन', 'Senior Citizen Login') ?></a>
                        <span style="font-size:.88rem;color:#4b5563;margin-left:6px"><?= $t('रजिस्ट्रेशन वाला मोबाइल या ईमेल → ईमेल OTP', 'Registered mobile or email → email OTP') ?></span></p>
                </div>
            <?php else: ?>
                <div class="sd-card" style="border-top:5px solid #b45309">
                    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center">
                        <h1 style="font-size:1.4rem;margin:0">👋 <?= $t('नमस्ते', 'Hello') ?>, <?= $h($me['type'] === 'seniorhire' ? ($me['details']['business_name'] ?? $me['full_name']) : $me['full_name']) ?></h1>
                        <a class="sd-btn ghost" href="/senior/logout"><?= $t('लॉगआउट', 'Logout') ?></a>
                    </div>
                    <p style="margin:6px 0 12px;color:#4b5563;font-size:.92rem"><?= $t('आपके वरिष्ठ नागरिक रजिस्ट्रेशन और पास के मिलान।', 'Your senior citizen registrations and nearby matches.') ?></p>
                    <p style="margin:0;display:flex;gap:8px;flex-wrap:wrap">
                        <?php if ($me['type'] === 'seniorhire'): ?>
                            <a class="sd-btn" href="/apply/senior-citizen-hiring">➕ <?= $t('एक और काम / सामुदायिक सेवा जोड़ें', 'Add another job / community service need') ?></a>
                        <?php else: ?>
                            <a class="sd-btn ghost" href="/apply/senior-citizen-jobs"><?= $t('नया रजिस्ट्रेशन', 'New registration') ?></a>
                        <?php endif; ?>
                    </p>
                </div>

                <?php foreach (array_slice($regs, 0, 10) as $i => $reg):
                    $d = $reg['details'];
                    $live = $reg['valid_until'] === null || strtotime((string)$reg['valid_until']) > time(); ?>
                    <div class="sd-card" style="margin-top:14px">
                        <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap">
                            <b><?= $h($reg['reg_no']) ?> · <?= $reg['type'] === 'seniorhire' ? $t('संस्था', 'Organisation') : $t('वरिष्ठ नागरिक', 'Senior citizen') ?>
                                <?php if (!empty($d['work_type'])): ?> · <?= $tp($smWorkLabels[$d['work_type']] ?? ['', $d['work_type']]) ?><?php endif; ?></b>
                            <span style="font-size:.88rem;color:<?= $live ? '#047857' : '#b91c1c' ?>;font-weight:700">
                                <?= $live ? $t('सक्रिय', 'Active') : $t('समाप्त', 'Ended') ?><?= !empty($reg['valid_until']) ? ' · ' . $h(date('d/m/Y', strtotime((string)$reg['valid_until']))) : '' ?>
                            </span>
                        </div>
                        <div style="font-size:.9rem;color:#374151;margin:4px 0 0"><?= $h($reg['categories']) ?><?= !empty($reg['city']) ? ' · ' . $h($reg['city']) : '' ?></div>
                        <?php if ($live && $i < 5) { require dirname(__DIR__) . '/apply/_senior_matches.php'; } ?>
                        <a href="/apply/status/<?= $h($reg['token']) ?>" style="font-size:.88rem"><?= $t('रसीद और पूरा विवरण', 'Receipt and full details') ?> →</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>
