<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
:root {
    --ink: #0D1117;
    --surface: #F4F6FB;
    --card: #FFFFFF;
    --border: #E4E8F0;
    --text-1: #0D1117;
    --text-2: #4A5568;
    --text-3: #8896AA;
    --blue: #2563EB;
    --blue-light: #EFF6FF;
    --green: #059669;
    --green-light: #ECFDF5;
    --red: #DC2626;
    --red-light: #FEF2F2;
    --amber: #D97706;
    --amber-light: #FFFBEB;
    --violet: #7C3AED;
    --violet-light: #F5F3FF;
    --slate: #334155;
    --shadow-sm: 0 1px 2px rgba(13,17,23,0.05);
    --shadow: 0 2px 8px rgba(13,17,23,0.07), 0 0 1px rgba(13,17,23,0.06);
    --shadow-md: 0 4px 16px rgba(13,17,23,0.09), 0 0 1px rgba(13,17,23,0.06);
    --radius: 12px;
    --radius-sm: 8px;
}

.bu-wrap { font-family: 'Sora', sans-serif; color: var(--text-1); padding: 28px 0; }

/* ── Page Header ── */
.bu-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 24px;
    gap: 16px;
    flex-wrap: wrap;
}
.bu-page-header h1 {
    font-size: 22px;
    font-weight: 800;
    letter-spacing: -0.5px;
    color: var(--text-1);
}
.bu-page-header p {
    font-size: 13px;
    color: var(--text-3);
    margin-top: 3px;
}
.bu-create-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 10px 18px;
    background: var(--blue);
    color: #fff;
    border-radius: var(--radius-sm);
    font-size: 13px;
    font-weight: 700;
    font-family: 'Sora', sans-serif;
    text-decoration: none;
    transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
    box-shadow: 0 3px 10px rgba(37,99,235,0.25);
    white-space: nowrap;
}
.bu-create-btn:hover { background: #1d4ed8; transform: translateY(-1px); box-shadow: 0 5px 16px rgba(37,99,235,0.35); }

/* ── Stats Strip ── */
.bu-stats-strip {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}
@media(max-width:900px){ .bu-stats-strip { grid-template-columns: repeat(2,1fr); } }
@media(max-width:500px){ .bu-stats-strip { grid-template-columns: 1fr 1fr; } }

.bu-stat-mini {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 14px 18px;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    gap: 12px;
}
.bu-stat-mini-icon {
    width: 36px; height: 36px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.bu-stat-mini-label { font-size: 11px; font-weight: 600; color: var(--text-3); margin-bottom: 2px; }
.bu-stat-mini-val { font-size: 18px; font-weight: 800; color: var(--text-1); font-family: 'JetBrains Mono', monospace; letter-spacing: -0.5px; }

/* ── Cards grid ── */
.bu-cards-grid {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.bu-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: box-shadow 0.2s;
    animation: fadeUp 0.4s both;
}
.bu-card:hover { box-shadow: var(--shadow-md); }
.bu-card:nth-child(1){ animation-delay: 0.05s; }
.bu-card:nth-child(2){ animation-delay: 0.1s; }
.bu-card:nth-child(3){ animation-delay: 0.15s; }
.bu-card:nth-child(4){ animation-delay: 0.2s; }

/* Status accent bar on left */
.bu-card.status-active { border-left: 3px solid var(--green); }
.bu-card.status-suspended { border-left: 3px solid var(--red); }
.bu-card.status-expired { border-left: 3px solid var(--amber); }

/* ── Card top row ── */
.bu-card-top {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 16px;
    padding: 20px 24px 16px;
    align-items: flex-start;
    border-bottom: 1px solid var(--border);
}
.bu-card-identity { display: flex; align-items: center; gap: 14px; }
.bu-avatar {
    width: 44px; height: 44px;
    border-radius: 11px;
    background: linear-gradient(135deg, #2563EB, #7C3AED);
    color: #fff;
    font-size: 16px;
    font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-family: 'Sora', sans-serif;
}
.bu-name { font-size: 15px; font-weight: 700; color: var(--text-1); }
.bu-username { font-size: 12px; color: var(--text-3); margin-top: 2px; font-family: 'JetBrains Mono', monospace; }

.bu-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 11px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.02em;
}
.bu-status-badge.active { background: var(--green-light); color: var(--green); border: 1px solid #a7f3d0; }
.bu-status-badge.suspended { background: var(--red-light); color: var(--red); border: 1px solid #fecaca; }
.bu-status-badge.expired { background: var(--amber-light); color: var(--amber); border: 1px solid #fde68a; }
.bu-badge-dot { width: 6px; height: 6px; border-radius: 50%; }
.bu-status-badge.active .bu-badge-dot { background: var(--green); animation: pulse 1.5s infinite; }
.bu-status-badge.suspended .bu-badge-dot { background: var(--red); }
.bu-status-badge.expired .bu-badge-dot { background: var(--amber); }

/* ── Card meta strip ── */
.bu-card-meta {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0;
    padding: 14px 24px;
    border-bottom: 1px solid var(--border);
    background: #FAFBFD;
}
@media(max-width:700px){ .bu-card-meta { grid-template-columns: repeat(3,1fr); } }
@media(max-width:480px){ .bu-card-meta { grid-template-columns: repeat(2,1fr); } }

.bu-meta-item { padding: 4px 0; }
.bu-meta-label { font-size: 10px; font-weight: 700; letter-spacing: 0.07em; color: var(--text-3); text-transform: uppercase; margin-bottom: 4px; }
.bu-meta-val { font-size: 13px; font-weight: 600; color: var(--text-1); }
.bu-meta-val.mono { font-family: 'JetBrains Mono', monospace; font-size: 14px; }

/* Progress bar */
.bu-progress-wrap { padding: 12px 24px; background: #FAFBFD; border-bottom: 1px solid var(--border); }
.bu-progress-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
.bu-progress-label { font-size: 11px; font-weight: 600; color: var(--text-3); }
.bu-progress-nums { font-size: 11px; font-weight: 700; color: var(--text-2); font-family: 'JetBrains Mono', monospace; }
.bu-progress-track { height: 6px; background: var(--border); border-radius: 99px; overflow: hidden; }
.bu-progress-fill { height: 100%; border-radius: 99px; transition: width 0.6s ease; }

/* ── Actions row ── */
.bu-card-actions {
    padding: 16px 24px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

/* Action group separators */
.bu-action-divider { width: 1px; height: 28px; background: var(--border); flex-shrink: 0; }
@media(max-width:640px){ .bu-action-divider { display: none; } }

/* All action buttons */
.bu-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 700;
    font-family: 'Sora', sans-serif;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
    text-decoration: none;
    line-height: 1;
}
.bu-btn:hover { transform: translateY(-1px); }

.bu-btn-suspend { background: var(--red-light); color: var(--red); border: 1px solid #fecaca; }
.bu-btn-suspend:hover { background: var(--red); color: #fff; }

.bu-btn-activate { background: var(--green-light); color: var(--green); border: 1px solid #a7f3d0; }
.bu-btn-activate:hover { background: var(--green); color: #fff; }

.bu-btn-reset { background: var(--surface); color: var(--text-2); border: 1px solid var(--border); }
.bu-btn-reset:hover { background: var(--border); color: var(--text-1); }

.bu-btn-uploads { background: var(--slate); color: #fff; border: 1px solid transparent; }
.bu-btn-uploads:hover { background: #1e293b; }

.bu-btn-password { background: var(--violet-light); color: var(--violet); border: 1px solid #ddd6fe; }
.bu-btn-password:hover { background: var(--violet); color: #fff; }

.bu-btn-credits { background: var(--green-light); color: var(--green); border: 1px solid #a7f3d0; }
.bu-btn-credits:hover { background: var(--green); color: #fff; }

.bu-btn-delete { background: var(--red-light); color: var(--red); border: 1px solid #fecaca; }
.bu-btn-delete:hover { background: var(--red); color: #fff; }

/* Inline inputs in action row */
.bu-action-input {
    padding: 8px 12px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-family: 'Sora', sans-serif;
    color: var(--text-1);
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
    width: 130px;
    background: var(--card);
}
.bu-action-input:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
.bu-action-input::placeholder { color: var(--text-3); }

/* inline form flex helper */
.bu-action-form { display: inline-flex; align-items: center; gap: 7px; }

/* ── Footer note ── */
.bu-footer-note {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 20px;
    font-size: 12.5px;
    color: var(--text-3);
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 10px 16px;
    box-shadow: var(--shadow-sm);
}

/* ── Empty state ── */
.bu-empty {
    background: var(--card);
    border: 1px dashed var(--border);
    border-radius: var(--radius);
    padding: 48px 24px;
    text-align: center;
    color: var(--text-3);
}
.bu-empty-icon { font-size: 32px; margin-bottom: 12px; }
.bu-empty-title { font-size: 15px; font-weight: 700; color: var(--text-2); margin-bottom: 6px; }
.bu-empty-sub { font-size: 13px; }

/* ── Type pill ── */
.bu-type-pill {
    display: inline-flex;
    padding: 3px 9px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 700;
    background: var(--blue-light);
    color: var(--blue);
    border: 1px solid #bfdbfe;
    text-transform: capitalize;
}

/* Animations */
@keyframes fadeUp { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.5;transform:scale(0.8)} }
</style>

<?php
// Compute stats from items
$totalAccounts = count($items ?? []);
$activeCount   = 0;
$suspendedCount = 0;
$totalCVs = 0;
foreach (($items ?? []) as $acc) {
    $s = $acc->attributes['status'] ?? '';
    if ($s === 'active') $activeCount++;
    elseif ($s === 'suspended') $suspendedCount++;
    $totalCVs += (int)($acc->attributes['limit_total'] ?? 0);
}
?>

<div class="bu-wrap">

    <!-- Page Header -->
    <div class="bu-page-header">
        <div>
            <h1>Bulk Uploaders</h1>
            <p>Manage accounts that can upload resumes in bulk. Each account has its own CV limits and expiry.</p>
        </div>
        <a href="/admin/bulk-uploaders/create" class="bu-create-btn">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v12m6-6H6"/></svg>
            Create Account
        </a>
    </div>

    <!-- Stats Strip -->
    <div class="bu-stats-strip">
        <div class="bu-stat-mini">
            <div class="bu-stat-mini-icon" style="background:#EFF6FF; color:#2563EB;">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <div class="bu-stat-mini-label">Total Accounts</div>
                <div class="bu-stat-mini-val"><?= $totalAccounts ?></div>
            </div>
        </div>
        <div class="bu-stat-mini">
            <div class="bu-stat-mini-icon" style="background:#ECFDF5; color:#059669;">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="bu-stat-mini-label">Active</div>
                <div class="bu-stat-mini-val"><?= $activeCount ?></div>
            </div>
        </div>
        <div class="bu-stat-mini">
            <div class="bu-stat-mini-icon" style="background:#FEF2F2; color:#DC2626;">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="bu-stat-mini-label">Suspended</div>
                <div class="bu-stat-mini-val"><?= $suspendedCount ?></div>
            </div>
        </div>
        <div class="bu-stat-mini">
            <div class="bu-stat-mini-icon" style="background:#F5F3FF; color:#7C3AED;">
                <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div class="bu-stat-mini-label">Total CV Slots</div>
                <div class="bu-stat-mini-val"><?= number_format($totalCVs) ?></div>
            </div>
        </div>
    </div>

    <!-- Cards List -->
    <?php if (!empty($items ?? [])): ?>
    <div class="bu-cards-grid">

        <?php foreach (($items ?? []) as $acc):
            $name       = htmlspecialchars($acc->attributes['name'] ?? '');
            $username   = htmlspecialchars($acc->attributes['username'] ?? '');
            $type       = htmlspecialchars($acc->attributes['type'] ?? '');
            $limitTotal = (int)($acc->attributes['limit_total'] ?? 0);
            $limitUsed  = (int)($acc->attributes['limit_used'] ?? 0);
            $remaining  = max(0, $limitTotal - $limitUsed);
            $expires    = htmlspecialchars($acc->attributes['expires_at'] ?? '—');
            $status     = $acc->attributes['status'] ?? 'active';
            $id         = (int)$acc->id;
            $csrf       = htmlspecialchars($_SESSION['csrf_token'] ?? '');
            $initial    = strtoupper(mb_substr($name, 0, 1));
            $pct        = $limitTotal > 0 ? min(100, round(($limitUsed / $limitTotal) * 100)) : 0;
            $barColor   = $pct >= 90 ? '#DC2626' : ($pct >= 60 ? '#D97706' : '#059669');
            // Expires formatting
            $expiresFormatted = '—';
            if ($expires && $expires !== '—') {
                try {
                    $dt = new DateTime($expires);
                    $now = new DateTime();
                    $diff = $now->diff($dt);
                    $expiresFormatted = $dt->format('d M Y');
                    $isExpired = $dt < $now;
                    if (!$isExpired && $diff->days <= 7) {
                        $expiresFormatted .= ' ⚠️';
                    }
                } catch(Exception $e) {
                    $expiresFormatted = $expires;
                    $isExpired = false;
                }
            } else { $isExpired = false; }

            $statusClass = ($status === 'active' && !$isExpired) ? 'active' : ($isExpired ? 'expired' : 'suspended');
            $statusLabel = $isExpired ? 'Expired' : ucfirst($status);
        ?>

        <div class="bu-card status-<?= $statusClass ?>">

            <!-- Top: identity + status -->
            <div class="bu-card-top">
                <div class="bu-card-identity">
                    <div class="bu-avatar"><?= $initial ?></div>
                    <div>
                        <div class="bu-name"><?= $name ?></div>
                        <div class="bu-username"><?= $username ?></div>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; justify-content:flex-end;">
                    <span class="bu-type-pill"><?= $type ?: 'Standard' ?></span>
                    <span class="bu-status-badge <?= $statusClass ?>">
                        <span class="bu-badge-dot"></span>
                        <?= $statusLabel ?>
                    </span>
                </div>
            </div>

            <!-- Meta strip -->
            <div class="bu-card-meta">
                <div class="bu-meta-item">
                    <div class="bu-meta-label">CV Limit</div>
                    <div class="bu-meta-val mono"><?= $limitTotal ?></div>
                </div>
                <div class="bu-meta-item">
                    <div class="bu-meta-label">Used</div>
                    <div class="bu-meta-val mono"><?= $limitUsed ?></div>
                </div>
                <div class="bu-meta-item">
                    <div class="bu-meta-label">Remaining</div>
                    <div class="bu-meta-val mono" style="color:<?= $remaining === 0 ? 'var(--red)' : ($remaining <= 2 ? 'var(--amber)' : 'var(--green)') ?>;"><?= $remaining ?></div>
                </div>
                <div class="bu-meta-item">
                    <div class="bu-meta-label">Expires</div>
                    <div class="bu-meta-val" style="font-size:12px; <?= $isExpired ? 'color:var(--red);' : '' ?>"><?= $expiresFormatted ?></div>
                </div>
                <div class="bu-meta-item">
                    <div class="bu-meta-label">Usage</div>
                    <div class="bu-meta-val mono" style="color:<?= $pct >= 90 ? 'var(--red)' : ($pct >= 60 ? 'var(--amber)' : 'var(--green)') ?>;"><?= $pct ?>%</div>
                </div>
            </div>

            <!-- Progress bar -->
            <div class="bu-progress-wrap">
                <div class="bu-progress-header">
                    <span class="bu-progress-label">CV Usage Progress</span>
                    <span class="bu-progress-nums"><?= $limitUsed ?> / <?= $limitTotal ?> used</span>
                </div>
                <div class="bu-progress-track">
                    <div class="bu-progress-fill" style="width:<?= $pct ?>%; background:<?= $barColor ?>;"></div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bu-card-actions">

                <!-- Group 1: Toggle + Reset Used + View Uploads -->
                <form method="post" action="/admin/bulk-uploaders/<?= $id ?>/toggle">
                    <input type="hidden" name="_token" value="<?= $csrf ?>">
                    <button type="submit" class="bu-btn <?= $status === 'active' ? 'bu-btn-suspend' : 'bu-btn-activate' ?>">
                        <?php if($status === 'active'): ?>
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Suspend
                        <?php else: ?>
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Activate
                        <?php endif; ?>
                    </button>
                </form>

                <form method="post" action="/admin/bulk-uploaders/<?= $id ?>/reset">
                    <input type="hidden" name="_token" value="<?= $csrf ?>">
                    <button type="submit" class="bu-btn bu-btn-reset">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Reset Used
                    </button>
                </form>

                <a href="/admin/bulk-uploaders/<?= $id ?>/batches" class="bu-btn bu-btn-uploads">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    View Uploads
                </a>

                <div class="bu-action-divider"></div>

                <!-- Group 2: Reset Password -->
                <form method="post" action="/admin/bulk-uploaders/<?= $id ?>/password" class="bu-action-form">
                    <input type="hidden" name="_token" value="<?= $csrf ?>">
                    <input type="password" name="password" placeholder="New password" class="bu-action-input">
                    <button type="submit" class="bu-btn bu-btn-password">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        Set Password
                    </button>
                </form>

                <div class="bu-action-divider"></div>

                <!-- Group 3: Add Credits + Delete -->
                <form method="post" action="/admin/bulk-uploaders/<?= $id ?>/credits" class="bu-action-form">
                    <input type="hidden" name="_token" value="<?= $csrf ?>">
                    <input type="number" name="add" min="1" placeholder="Add CVs" class="bu-action-input" style="width:110px;">
                    <button type="submit" class="bu-btn bu-btn-credits">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add Limit
                    </button>
                </form>

                <form method="post" action="/admin/bulk-uploaders/<?= $id ?>/delete" onsubmit="return confirm('Delete this bulk uploader account and all its uploads? This cannot be undone.');">
                    <input type="hidden" name="_token" value="<?= $csrf ?>">
                    <button type="submit" class="bu-btn bu-btn-delete">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                </form>

            </div><!-- end actions -->
        </div><!-- end bu-card -->

        <?php endforeach; ?>
    </div><!-- end cards grid -->

    <?php else: ?>
    <div class="bu-empty">
        <div class="bu-empty-icon">📂</div>
        <div class="bu-empty-title">No Bulk Uploader Accounts Yet</div>
        <div class="bu-empty-sub">Create the first account to get started with bulk resume uploads.</div>
    </div>
    <?php endif; ?>

    <!-- Footer note -->
    <div class="bu-footer-note">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0; color:#8896AA;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Bulk uploaders have isolated access — they cannot view candidate profiles, employer data, or any other admin sections.
    </div>

</div>