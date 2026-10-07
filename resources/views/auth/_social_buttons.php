<?php
/**
 * "Continue with" buttons. Google always; Facebook / LinkedIn only when their keys are set in .env
 * (config/social.php) – never show a provider that is not wired (Safe Browsing "deceptive" flag, Oct 2026).
 * In: $socialRedirect (path after login), $socialClass (button class of the page).
 */
use App\Services\SocialOAuthService;

$socialRedirect = rawurlencode((string)($socialRedirect ?? '/candidate/dashboard'));
$socialClass = $socialClass ?? 'social-btn';
$socialButtons = [['google', 'Google', '<img src="https://www.gstatic.com/images/branding/product/1x/googleg_48dp.png" alt="" width="20" height="20">']];
if (SocialOAuthService::enabled('facebook')) {
    $socialButtons[] = ['facebook', 'Facebook', '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><circle cx="12" cy="12" r="12" fill="#1877F2"/><path fill="#fff" d="M13.4 19.5v-6.1h2.1l.3-2.4h-2.4V9.5c0-.7.2-1.2 1.2-1.2h1.3V6.2c-.2 0-1-.1-1.9-.1-1.9 0-3.2 1.2-3.2 3.3V11H8.7v2.4h2.1v6.1h2.6z"/></svg>'];
}
if (SocialOAuthService::enabled('linkedin')) {
    $socialButtons[] = ['linkedin', 'LinkedIn', '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><rect width="24" height="24" rx="4" fill="#0A66C2"/><path fill="#fff" d="M7.1 9.5h2.4V18H7.1V9.5zm1.2-3.9a1.4 1.4 0 110 2.8 1.4 1.4 0 010-2.8zM11 9.5h2.3v1.2c.3-.6 1.1-1.3 2.4-1.3 2.5 0 3 1.6 3 3.8V18h-2.4v-4.3c0-1 0-2.3-1.4-2.3s-1.6 1.1-1.6 2.2V18H11V9.5z"/></svg>'];
}
?>
<div class="social-grid" style="grid-template-columns:repeat(<?= count($socialButtons) ?>,1fr)">
    <?php foreach ($socialButtons as [$key, $label, $icon]): ?>
        <a href="/auth/<?= $key ?>?redirect=<?= $socialRedirect ?>" class="<?= htmlspecialchars($socialClass, ENT_QUOTES, 'UTF-8') ?>" aria-label="Continue with <?= $label ?>"
           style="gap:8px;font-weight:700;color:#334155;text-decoration:none"><?= $icon ?><span><?= $label ?></span></a>
    <?php endforeach; ?>
</div>
