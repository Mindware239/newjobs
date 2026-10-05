<?php
/**
 * "Jobsence initiative for Young India" – blinking tricolour banner (mentoring USP).
 * $youngIndiaBanner('full'|'compact') prints it; motion stops for prefers-reduced-motion.
 */
use App\Helpers\Lang;

if (!isset($youngIndiaBanner)) {
?>
<style>
.yi-band { position: relative; display: block; text-decoration: none; color: #111827; border-radius: 18px; padding: 3px; background: linear-gradient(90deg, #FF9933, #ffffff, #138808, #FF9933); background-size: 300% 100%; animation: yi-flow 4s linear infinite; box-shadow: 0 8px 24px rgba(19, 136, 8, .15); }
.yi-band .yi-in { display: flex; gap: 14px; align-items: center; flex-wrap: wrap; border-radius: 15px; padding: 14px 18px; background: linear-gradient(180deg, #fff3e0 0%, #ffffff 50%, #e8f5e9 100%); }
.yi-band .yi-chakra { width: 46px; height: 46px; flex: none; animation: yi-spin 12s linear infinite; }
.yi-band .yi-t { font-weight: 900; font-size: clamp(1.05rem, 2.6vw, 1.4rem); line-height: 1.25; }
.yi-band .yi-t .s { color: #e67700; } .yi-band .yi-t .g { color: #0f6e06; }
.yi-band .yi-sub { font-size: .9rem; color: #374151; font-weight: 600; }
.yi-band .yi-go { margin-left: auto; padding: 10px 16px; border-radius: 10px; background: #138808; color: #fff; font-weight: 900; white-space: nowrap; animation: yi-blink 1.4s ease-in-out infinite; }
.yi-band.compact .yi-in { padding: 8px 12px; } .yi-band.compact .yi-chakra { width: 30px; height: 30px; } .yi-band.compact .yi-sub { display: none; }
@keyframes yi-flow { to { background-position: 300% 0; } }
@keyframes yi-spin { to { transform: rotate(360deg); } }
@keyframes yi-blink { 0%, 100% { background: #138808; } 33% { background: #FF9933; } 66% { background: #0b3d91; } }
@media (max-width: 560px) { .yi-band .yi-go { margin-left: 0; width: 100%; text-align: center; } }
@media (prefers-reduced-motion: reduce) { .yi-band, .yi-band .yi-chakra, .yi-band .yi-go { animation: none; } }
</style>
<?php
    $youngIndiaBanner = static function (string $variant = 'full'): void { ?>
        <a class="yi-band <?= $variant === 'compact' ? 'compact' : '' ?>" href="/mentoring" aria-label="Jobsence initiative for Young India – mentoring">
            <span class="yi-in">
                <svg class="yi-chakra" viewBox="0 0 48 48" aria-hidden="true"><circle cx="24" cy="24" r="21" fill="none" stroke="#0b3d91" stroke-width="3"/><circle cx="24" cy="24" r="4" fill="#0b3d91"/><?php for ($i = 0; $i < 24; $i++): $a = $i * M_PI / 12; ?><line x1="24" y1="24" x2="<?= round(24 + 19 * cos($a), 2) ?>" y2="<?= round(24 + 19 * sin($a), 2) ?>" stroke="#0b3d91" stroke-width="1.2"/><?php endfor; ?></svg>
                <span>
                    <span class="yi-t"><span class="s"><?= Lang::t('युवा भारत के लिए', 'For Young India') ?></span> – <span class="g"><?= Lang::t('Jobsence पहल', 'a Jobsence initiative') ?></span></span><br>
                    <span class="yi-sub"><?= Lang::t('मेंटर, संस्थान और इंटर्नशिप कंपनियाँ मुफ़्त रजिस्टर करें · कौन क्या सीखना चाहता है देखें · त्रिपक्षीय समझौते से जुड़ें', 'Mentors, institutes & internship companies register free · see who wants to learn what · connect with a tripartite agreement') ?></span>
                </span>
                <span class="yi-go"><?= Lang::t('मेंटरिंग देखें →', 'Open mentoring →') ?></span>
            </span>
        </a>
    <?php };
}
