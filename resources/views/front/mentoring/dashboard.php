<?php
require __DIR__ . "/_shared.php";
use App\Services\Registration\Mentoring;

$statusLabel = [
    'pending' => ['साइन बाकी', 'Awaiting signatures'], 'accepted' => ['समझौता पूरा', 'Agreement signed'], 'declined' => ['अस्वीकार', 'Declined'],
    'expired' => ['समय समाप्त', 'Expired'], 'cancelled' => ['रद्द', 'Cancelled'], 'ended' => ['समाप्त', 'Ended'],
];
?>
<div class="sd">
    <section class="sd-section mt-hero">
        <div class="sd-wrap">
            <div style="display:flex;justify-content:flex-end;margin-bottom:10px"><?php $sdLangSwitcher(); ?></div>
            <?php $youngIndiaBanner(); ?>
            <h1><?= $t('मेरा मेंटरिंग डैशबोर्ड', 'My Mentoring Dashboard') ?></h1>
            <?php $mtFlash($flash); ?>

            <?php if (!$me): ?>
                <p style="color:#4b5563;max-width:780px"><?= $t('स्किल सीखने वाले और इंटर्नशिप चाहने वाले ₹155 का फॉर्म भरते हैं। मेंटर, संस्थान, ग्रुप मेंटर और इंटर्नशिप देने वाली कंपनियाँ मुफ़्त रजिस्टर करती हैं। फ़ोन और ईमेल केवल दोनों के शुल्क और त्रिपक्षीय समझौते के बाद।', 'Skill and internship seekers fill the ₹155 form. Mentors, institutes, group mentors and internship companies register free. Phone and email only after both have paid and signed the tripartite agreement.') ?></p>
                <div class="mt-stats">
                    <?php foreach ($stats as $kk => $st): $kc = Mentoring::KINDS[$kk]; $lbl = [$kc['seekers_label'][0], $kc['seekers_label'][1], $kc['providers_label'][0], $kc['providers_label'][1]]; ?>
                        <div class="mt-stat">
                            <div class="n"><?= (int)$st['seekers'] ?></div><b><?= $t($lbl[0], $lbl[1]) ?></b> · <?= (int)$st['providers'] ?> <?= $t($lbl[2], $lbl[3]) ?>
                            <div class="mt-act">
                                <a class="sd-btn" href="<?= $h($kc['seeker_form']) ?>"><?= $t('₹155 फॉर्म भरें', 'Apply – ₹155') ?></a>
                                <a class="sd-btn mt-green" href="<?= $h($kc['provider_form']) ?>"><?= $t('मुफ़्त रजिस्टर करें', 'Register free') ?></a>
                            </div>
                            <div class="mt-act"><a href="<?= $h($kc['seekers_page']) ?>"><?= $t($lbl[1] . ' देखें', 'See ' . strtolower($lbl[1])) ?></a> · <a href="<?= $h($kc['providers_page']) ?>"><?= $t($lbl[3] . ' देखें', 'See ' . strtolower($lbl[3])) ?></a></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="sd-card"><?= $t('पहले से रजिस्टर हैं? अपनी रसीद या ईमेल में “मेरा मेंटरिंग डैशबोर्ड” लिंक खोलें।', 'Already registered? Open the “My mentoring dashboard” link in your receipt or email.') ?></div>
            <?php else: ?>
                <div class="mt-box">
                    <b style="font-size:1.1rem"><?= $h($role === 'provider' ? Mentoring::displayName($me) : $me['full_name']) ?></b>
                    <span class="mt-tag g"><?= $h($me['reg_no']) ?></span>
                    <span class="mt-tag"><?= $tp($role === 'provider' ? $k['providers_label'] : $k['seekers_label']) ?></span>
                    <div class="mt-meta"><?= $h(implode(', ', array_filter([$me['city'], $me['state']]))) ?> · <?= $h($me['categories']) ?></div>

                    <?php if ($role === 'seeker' && ($me['type'] ?? '') === 'intljob'): $countries = Mentoring::countriesOf($me); ?>
                        <div id="countries" style="margin-top:12px">
                            <b>🌍 <?= $t('मेरे देश', 'My countries') ?></b>
                            <span class="mt-meta"><?= $t('हर देश एक बार ₹1,180 (₹1,000 + GST) या USD 10 – उस देश की कंपनियाँ आपको देख सकेंगी।', 'Each country one-time ₹1,180 (₹1,000 + GST) or USD 10 – employers in that country can see you.') ?></span>
                            <div class="mt-skills" style="margin:8px 0">
                                <?php foreach ($countries as $c): ?>
                                    <?php if ($c['paid']): ?><span style="background:#ecfdf5;color:#047857">✔ <?= $h($c['country']) ?></span>
                                    <?php else: ?><a href="/apply/pay/<?= $h($c['token']) ?>" style="padding:2px 8px;border-radius:999px;background:#fef3c7;color:#92400e;font-size:.74rem;font-weight:800;text-decoration:none">⏳ <?= $h($c['country']) ?> – <?= $t('भुगतान करें', 'pay') ?></a><?php endif; ?>
                                <?php endforeach; ?>
                                <?php if (!$countries): ?><span class="mt-meta"><?= $t('अभी कोई देश अनलॉक नहीं – नीचे जोड़ें।', 'No country unlocked yet – add one below.') ?></span><?php endif; ?>
                            </div>
                            <?php $wanted = array_filter(array_map('trim', explode(',', (string)($me['details']['preferred_countries'] ?? '')))); ?>
                            <form method="POST" action="/mentoring/country" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                                <?= $mtCsrf() ?>
                                <input name="country" list="mt-countries" required placeholder="Japan, UAE, USA…" style="flex:1 1 200px;height:44px;padding:0 10px;border:1px solid #d1d5db;border-radius:10px">
                                <datalist id="mt-countries"><?php foreach (array_unique(array_merge($wanted, array_values(\App\Controllers\Front\JobsAbroadController::countries()))) as $cn): ?><option value="<?= $h($cn) ?>"><?php endforeach; ?></datalist>
                                <button class="sd-btn" name="currency" value="INR" type="submit"><?= $t('अनलॉक – ₹1,180', 'Unlock – ₹1,180') ?></button>
                                <button class="sd-btn mt-green" name="currency" value="USD" type="submit"><?= $t('अनलॉक – USD 10', 'Unlock – USD 10') ?></button>
                            </form>
                        </div>
                    <?php endif; ?>
                    <?php if ($role === 'seeker'): ?>
                        <?php if ($state === 'open'): ?>
                            <p class="sd-alert ok" style="margin:10px 0 0"><?= $kind === 'nearme'
                                ? $t('आपका Near Me पास सक्रिय है – ' . date('d M Y', strtotime((string)$me['valid_until'])) . ' तक।', 'Your Near Me pass is active till ' . date('d M Y', strtotime((string)$me['valid_until'])) . '.')
                                : $t('आपका रजिस्ट्रेशन सक्रिय है – मिलान होने तक मान्य।', 'Your registration is active – valid until you are matched.') ?></p>
                            <div class="mt-act"><a class="sd-btn mt-green" href="<?= $h($k['providers_page'] . ($kind === 'nearme' && $me['pincode'] ? '?pin=' . $me['pincode'] : '')) ?>"><?= $t($k['providers_label'][0] . ' देखें और चुनें', 'See & choose ' . strtolower($k['providers_label'][1])) ?></a></div>
                        <?php elseif ($state === 'matched'): ?>
                            <p class="sd-alert ok" style="margin:10px 0 0">✅ <?= $t('आपका मिलान हो गया है – नीचे समझौता खोलकर संपर्क देखें।', 'You are matched – open the agreement below for contact details.') ?></p>
                        <?php else: ?>
                            <p class="sd-alert err" style="margin:10px 0 0"><?= $t('आपका रजिस्ट्रेशन बंद है। नया फॉर्म भरें।', 'Your registration is closed. Fill a new form.') ?></p>
                            <div class="mt-act"><a class="sd-btn" href="<?= $h($k['seeker_form']) ?>"><?= $tb('नया फॉर्म भरें', 'Fill a new form') ?></a></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p style="margin:10px 0 0"><?= $verified ? '<span class="mt-tag">✔ ' . $t('Jobsence सत्यापित', 'Jobsence verified') . '</span>' : '<span class="mt-tag w">⏳ ' . $t('सत्यापन बाकी – उसके बाद आप दिखेंगे और साइन कर सकेंगे', 'Verification pending – after that you are listed and can sign') . '</span>' ?></p>
                        <?php if ($kind === 'nearme'): ?>
                            <?php if ($plan): ?>
                                <p class="sd-alert ok" style="margin:10px 0 0"><?= $t('आपकी Near Me लिस्टिंग ' . date('d M Y', strtotime((string)$plan['valid_until'])) . ' तक सक्रिय है। ग्राहक अनुरोध भेजेंगे – समझौते पर साइन करें।', 'Your Near Me listing is active till ' . date('d M Y', strtotime((string)$plan['valid_until'])) . '. Customers send requests – sign the agreement to share contacts.') ?></p>
                            <?php else: ?>
                                <p class="sd-alert err" style="margin:10px 0 0"><?= $t('आपकी लिस्टिंग की अवधि समाप्त है।', 'Your listing has expired.') ?></p>
                                <div class="mt-act"><a class="sd-btn" href="/apply/renew/<?= $h($me['token']) ?>"><?= $tb('लिस्टिंग रिन्यू करें', 'Renew listing') ?></a></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ($plan): ?>
                                <p class="sd-alert ok" style="margin:10px 0 0">
                                    <?= $t('प्लान सक्रिय – ' . date('d M Y', strtotime((string)$plan['valid_until'])) . ' तक।', 'Plan active till ' . date('d M Y', strtotime((string)$plan['valid_until'])) . '.') ?>
                                    <b><?= $t('आज बची प्रोफ़ाइल: ' . $quota['today'], 'Profiles left today: ' . $quota['today']) ?></b>
                                    <?php if ($quota['total'] !== null): ?> · <?= $t('प्लान में बची: ' . $quota['total'] . '/20', 'Left in plan: ' . $quota['total'] . '/20') ?><?php endif; ?>
                                </p>
                            <?php else: ?>
                                <p class="sd-alert err" style="margin:10px 0 0"><?= $kind === 'job'
                                    ? $t('नौकरी चाहने वालों की पूरी प्रोफ़ाइल, रिज़्यूमे और समझौते के लिए नीचे भर्ती प्लान चुनें।', 'Choose a hiring plan below for full job-seeker profiles, resumes and agreements.')
                                    : ($kind === 'internship'
                                    ? $t('उम्मीदवारों की पूरी प्रोफ़ाइल, रिज़्यूमे और समझौते के लिए ₹155 का प्लान लें – 10 दिन, रोज़ 3, अधिकतम 20।', 'Get the ₹155 plan for full candidate profiles, resumes and agreements – 10 days, 3 a day, up to 20.')
                                    : $t('उम्मीदवारों की पूरी प्रोफ़ाइल, रिज़्यूमे और समझौते के लिए ₹155 का प्लान लें – 30 दिन, रोज़ 3 प्रोफ़ाइल।', 'Get the ₹155 plan for full candidate profiles, resumes and agreements – 30 days, 3 profiles a day.')) ?></p>
                            <?php endif; ?>
                            <div class="mt-act">
                                <a class="sd-btn mt-green" href="<?= $h((string)$k['seekers_page']) ?>"><?= $t($k['seekers_label'][0] . ' देखें', 'See ' . strtolower($k['seekers_label'][1])) ?></a>
                                <?php if (!Mentoring::planOptions($me)): ?>
                                <form method="POST" action="/mentoring/plan"><?= $mtCsrf() ?><button class="sd-btn" type="submit"><?= $plan ? $tb('प्लान बढ़ाएँ – ₹155', 'Extend plan – ₹155') : $tb('₹155 प्लान लें', 'Get the ₹155 plan') ?></button></form>
                                <?php endif; ?>
                            </div>
                            <?php if ($opts = Mentoring::planOptions($me)): ?>
                                <form method="POST" action="/mentoring/plan" id="plan" class="mt-box" style="margin:12px 0 0;background:#f9fafb">
                                    <?= $mtCsrf() ?>
                                    <b><?= $plan ? $t('भर्ती प्लान बढ़ाएँ', 'Extend hiring plan') : $t('भर्ती प्लान चुनें', 'Choose a hiring plan') ?></b> <span class="mt-meta"><?= $t('रोज़ 3 पूरी प्रोफ़ाइल + रिज़्यूमे + समझौता', '3 full profiles + resume + agreement a day') ?></span>
                                    <div style="display:flex;gap:10px;flex-wrap:wrap;margin:10px 0">
                                        <?php $first = true; foreach ($opts as $ok => $o): ?>
                                            <label style="flex:1 1 180px;border:2px solid #e5e7eb;border-radius:12px;padding:10px 12px;cursor:pointer;background:#fff">
                                                <input type="radio" name="option" value="<?= $h($ok) ?>" <?= $first ? 'checked' : '' ?> required> <b><?= $tp($o['label']) ?></b>
                                                <span class="mt-meta" style="display:block"><?= $o['currency'] === 'USD' ? $t('भारत के बाहर – अंतरराष्ट्रीय कार्ड', 'Outside India – international card') : $t('GST सहित', 'incl. GST') ?></span>
                                            </label>
                                        <?php $first = false; endforeach; ?>
                                    </div>
                                    <button class="sd-btn mt-green" type="submit"><?= $tb('भुगतान करें', 'Continue to payment') ?></button>
                                    <span class="mt-meta"><?= $t('कोई भी राशि किसी भी स्थिति में वापसी योग्य नहीं।', 'No amount is refundable in any condition.') ?></span>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    <p style="margin:10px 0 0;font-size:.82rem"><a href="/mentoring/logout"><?= $t('इस डिवाइस से बाहर निकलें', 'Sign out on this device') ?></a></p>
                </div>

                <div class="mt-box">
                    <h2 style="font-size:1.15rem"><?= $t('मेरे समझौते', 'My agreements') ?></h2>
                    <?php if (!$agreements): ?>
                        <p style="color:#6b7280;margin:0"><?= $t('अभी कोई समझौता नहीं।', 'No agreements yet.') ?></p>
                    <?php else: ?>
                        <table class="mt-table">
                            <thead><tr><th>#</th><th><?= $t('किसके साथ', 'With') ?></th><th><?= $t('स्किल / डोमेन', 'Skill / domain') ?></th><th><?= $t('स्थिति', 'Status') ?></th><th></th></tr></thead>
                            <tbody>
                            <?php foreach ($agreements as $a): $o = $a['other']; $mineSigned = $role === 'provider' ? $a['mentor_signed_at'] : $a['candidate_signed_at']; ?>
                                <tr>
                                    <td>JSA-<?= (int)$a['id'] ?></td>
                                    <td><?= $h($o ? ($role === 'provider' ? $o['full_name'] : Mentoring::displayName($o)) : '—') ?><div class="mt-meta"><?= $h($o ? implode(', ', array_filter([$o['city'], $o['state']])) : '') ?></div></td>
                                    <td><?= $h($a['skill']) ?></td>
                                    <td><span class="mt-tag <?= $a['status'] === 'accepted' ? '' : 'w' ?>"><?= $tp($statusLabel[$a['status']] ?? [$a['status'], $a['status']]) ?></span>
                                        <?php if ($a['status'] === 'pending' && !$mineSigned): ?><br><b style="color:#c2410c"><?= $t('आपका साइन बाकी', 'Your signature needed') ?></b><?php endif; ?></td>
                                    <td><a class="sd-btn <?= $a['status'] === 'pending' && !$mineSigned ? '' : 'ghost' ?>" style="padding:6px 12px;font-size:.85rem" href="<?= $h($a['link']) ?>"><?= $a['status'] === 'accepted' ? $t('संपर्क देखें', 'See contact') : $t('खोलें', 'Open') ?></a></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <?php if ($role === 'provider' && $opened): ?>
                    <div class="mt-box">
                        <h2 style="font-size:1.15rem"><?= $t('मेरे द्वारा खोली गई प्रोफ़ाइल', 'Profiles I opened') ?></h2>
                        <div class="mt-list">
                            <?php foreach ($opened as $s): ?>
                                <a class="mt-card" style="text-decoration:none;color:inherit" href="/mentoring/seeker/<?= (int)$s['id'] ?>">
                                    <h3><?= $h($s['full_name']) ?></h3>
                                    <div class="mt-meta"><?= $h(implode(', ', array_filter([$s['city'], $s['state']]))) ?> · <?= $t('खोली', 'Opened') ?> <?= $h(date('d M', strtotime((string)$s['viewed_on']))) ?></div>
                                    <div class="mt-skills"><?php foreach (array_slice(array_filter(array_map('trim', explode(',', (string)$s['categories']))), 0, 5) as $c): ?><span><?= $h($c) ?></span><?php endforeach; ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</div>
