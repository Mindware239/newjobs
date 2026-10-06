<?php
/**
 * Homepage: the three separate logins (Job Seeker / Employer / Mentor) side by side with bold captions,
 * what each gets and register links. Hidden for visitors who are already logged in.
 */
use App\Helpers\Lang;

if (isset($_SESSION['user_id'])) {
    return;
}
$lcRoles = require dirname(__DIR__) . '/auth/_login_roles.php';
$lcH = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<section class="lc" aria-labelledby="lc-title">
    <h2 id="lc-title" class="lc-title">🔑 <?= Lang::t('लॉगिन करें – अपना प्रकार चुनें', 'Login – choose who you are') ?></h2>
    <div class="lc-grid">
        <?php foreach ($lcRoles as $key => $r): ?>
            <article class="lc-card" style="--rc:<?= $lcH($r['color']) ?>">
                <h3 class="lc-cap"><span aria-hidden="true"><?= $r['icon'] ?></span> <?= Lang::t($r['title'][0], $r['title'][1]) ?></h3>
                <p class="lc-tag"><?= Lang::t($r['tagline'][0], $r['tagline'][1]) ?></p>
                <ul>
                    <?php foreach (array_slice($r['points'], 0, 4) as $pt): ?><li><?= Lang::t($pt[0], $pt[1]) ?></li><?php endforeach; ?>
                </ul>
                <a class="lc-btn" href="<?= $lcH($r['href']) ?>"><?= Lang::t($r['title'][0], $r['title'][1]) ?> →</a>
                <a class="lc-reg" href="<?= $lcH($r['register'][0][0]) ?>"><?= Lang::t('नए हैं? ' . $r['register'][0][1][0], 'New? ' . $r['register'][0][1][1]) ?></a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<style>
    .lc { max-width: 1100px; width: calc(100% - 32px); margin: 16px auto; box-sizing: border-box; }
    .lc-title { text-align: center; font-size: 1.35rem; font-weight: 900; color: #111827; margin: 0 0 12px; }
    .lc-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
    .lc-card { background: #fff; border: 1px solid #e5e7eb; border-top: 6px solid var(--rc); border-radius: 16px; padding: 18px; display: flex; flex-direction: column; gap: 8px; box-shadow: 0 2px 6px rgba(0,0,0,.05); }
    .lc-cap { margin: 0; font-size: 1.3rem; font-weight: 900; color: var(--rc); line-height: 1.25; }
    .lc-tag { margin: 0; font-size: .9rem; font-weight: 600; color: #4b5563; }
    .lc-card ul { margin: 2px 0 6px; padding: 0; list-style: none; display: grid; gap: 6px; }
    .lc-card li { font-size: .88rem; color: #374151; padding-left: 20px; position: relative; }
    .lc-card li::before { content: '✔'; position: absolute; left: 0; color: var(--rc); font-weight: 900; }
    .lc-btn { margin-top: auto; display: block; text-align: center; padding: 12px; border-radius: 12px; background: var(--rc); color: #fff !important; font-weight: 900; font-size: 1.02rem; text-decoration: none; }
    .lc-btn:hover { filter: brightness(1.08); }
    .lc-reg { text-align: center; font-size: .85rem; font-weight: 700; color: var(--rc); text-decoration: underline; }
    @media (max-width: 860px) { .lc-grid { grid-template-columns: 1fr; } }
</style>
