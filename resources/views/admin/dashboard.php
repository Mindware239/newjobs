<?php
// All original PHP variables used as-is:
// $stats['total_employers'], $stats['total_candidates'], $stats['total_jobs'], $stats['total_applications']
// $revenue['today'], $revenue['week'], $revenue['month'], $revenue['ytd']
// $alerts[] - array with type, title, message, link
// $dailySignups[] - date, employers, candidates
// $jobTrends[] - date, count
// $candidateByCategory[] - category, candidates, applications
// $candidateLocations[] - city, state, country, candidates
?>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
:root {
    --ink: #0D1117;
    --ink-2: #1C2333;
    --ink-3: #252D3D;
    --surface: #F4F6FB;
    --card: #FFFFFF;
    --border: #E4E8F0;
    --border-2: #D0D7E4;
    --text-1: #0D1117;
    --text-2: #4A5568;
    --text-3: #8896AA;
    --text-inv: #FFFFFF;
    --blue: #2563EB;
    --blue-light: #EFF6FF;
    --blue-mid: #BFDBFE;
    --green: #059669;
    --green-light: #ECFDF5;
    --green-mid: #A7F3D0;
    --violet: #7C3AED;
    --violet-light: #F5F3FF;
    --violet-mid: #DDD6FE;
    --amber: #D97706;
    --amber-light: #FFFBEB;
    --amber-mid: #FDE68A;
    --red: #DC2626;
    --red-light: #FEF2F2;
    --red-mid: #FECACA;
    --shadow-sm: 0 1px 2px rgba(13,17,23,0.05);
    --shadow: 0 2px 8px rgba(13,17,23,0.07), 0 0 1px rgba(13,17,23,0.08);
    --shadow-md: 0 4px 16px rgba(13,17,23,0.09), 0 0 1px rgba(13,17,23,0.08);
    --shadow-lg: 0 12px 32px rgba(13,17,23,0.12), 0 0 1px rgba(13,17,23,0.06);
    --radius: 12px;
    --radius-sm: 8px;
    --radius-lg: 16px;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

.adash { 
    font-family: 'Sora', sans-serif; 
    background: var(--surface); 
    min-height: 100vh;
    color: var(--text-1);
}

/* ── Page header ── */
.adash-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 12px;
}
.adash-header-left h1 {
    font-size: 22px;
    font-weight: 800;
    color: var(--text-1);
    letter-spacing: -0.5px;
}
.adash-header-left p {
    font-size: 13px;
    color: var(--text-3);
    margin-top: 3px;
}
.adash-header-right {
    display: flex;
    align-items: center;
    gap: 10px;
}
.adash-date-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 13px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 600;
    color: var(--text-2);
    box-shadow: var(--shadow-sm);
}
.adash-refresh-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 13px;
    background: var(--blue);
    border: none;
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 700;
    color: #fff;
    cursor: pointer;
    font-family: 'Sora', sans-serif;
    transition: background 0.15s, transform 0.1s;
}
.adash-refresh-btn:hover { background: #1d4ed8; transform: translateY(-1px); }

/* ── Section label ── */
.adash-section-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.09em;
    color: var(--text-3);
    text-transform: uppercase;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.adash-section-label::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}

/* ── Stat cards ── */
.adash-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 16px;
}
@media(max-width:1024px){ .adash-stats-grid { grid-template-columns: repeat(2,1fr); } }
@media(max-width:560px){ .adash-stats-grid { grid-template-columns: 1fr; } }

.adash-stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px 22px;
    box-shadow: var(--shadow);
    position: relative;
    overflow: hidden;
    transition: box-shadow 0.2s, transform 0.2s;
    animation: fadeUp 0.5s both;
    text-decoration: none;
    display: block;
    color: inherit;
}
.adash-stat-card:nth-child(1){ animation-delay: 0.05s; }
.adash-stat-card:nth-child(2){ animation-delay: 0.1s; }
.adash-stat-card:nth-child(3){ animation-delay: 0.15s; }
.adash-stat-card:nth-child(4){ animation-delay: 0.2s; }
.adash-stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); color: inherit; }

.adash-stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: var(--radius) var(--radius) 0 0;
}
.adash-stat-card.blue::before { background: var(--blue); }
.adash-stat-card.green::before { background: var(--green); }
.adash-stat-card.violet::before { background: var(--violet); }
.adash-stat-card.amber::before { background: var(--amber); }

.adash-stat-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.adash-stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.adash-stat-icon.blue { background: var(--blue-light); color: var(--blue); }
.adash-stat-icon.green { background: var(--green-light); color: var(--green); }
.adash-stat-icon.violet { background: var(--violet-light); color: var(--violet); }
.adash-stat-icon.amber { background: var(--amber-light); color: var(--amber); }

.adash-stat-trend {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    font-weight: 500;
    padding: 3px 7px;
    border-radius: 5px;
}
.adash-stat-trend.up { background: var(--green-light); color: var(--green); }
.adash-stat-trend.neutral { background: var(--surface); color: var(--text-3); }

.adash-stat-label {
    font-size: 12px;
    font-weight: 500;
    color: var(--text-3);
    margin-bottom: 4px;
}
.adash-stat-value {
    font-size: 28px;
    font-weight: 800;
    color: var(--text-1);
    letter-spacing: -1px;
    line-height: 1;
    font-family: 'JetBrains Mono', monospace;
}
.adash-stat-sub {
    font-size: 11px;
    color: var(--text-3);
    margin-top: 6px;
}

/* ── Revenue strip ── */
.adash-rev-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}
@media(max-width:1024px){ .adash-rev-grid { grid-template-columns: repeat(2,1fr); } }
@media(max-width:560px){ .adash-rev-grid { grid-template-columns: 1fr; } }

.adash-rev-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 16px 20px;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    gap: 14px;
    animation: fadeUp 0.5s both;
    transition: box-shadow 0.2s, transform 0.2s;
    text-decoration: none;
    color: inherit;
}
.adash-rev-card:nth-child(1){ animation-delay: 0.25s; }
.adash-rev-card:nth-child(2){ animation-delay: 0.3s; }
.adash-rev-card:nth-child(3){ animation-delay: 0.35s; }
.adash-rev-card:nth-child(4){ animation-delay: 0.4s; }
.adash-rev-card:hover { box-shadow: var(--shadow); transform: translateY(-2px); color: inherit; }

.adash-rev-icon {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    background: linear-gradient(135deg, #2563EB, #1d4ed8);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-shrink: 0;
}
.adash-rev-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-3);
    margin-bottom: 2px;
}
.adash-rev-value {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-1);
    font-family: 'JetBrains Mono', monospace;
    letter-spacing: -0.5px;
}

/* ── Alerts ── */
.adash-alerts {
    margin-bottom: 28px;
    animation: fadeUp 0.5s 0.3s both;
}
.adash-alert {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 16px;
    border-radius: var(--radius-sm);
    border: 1px solid;
    margin-bottom: 10px;
}
.adash-alert:last-child { margin-bottom: 0; }
.adash-alert.error { background: var(--red-light); border-color: var(--red-mid); }
.adash-alert.warning { background: var(--amber-light); border-color: var(--amber-mid); }
.adash-alert.info { background: var(--blue-light); border-color: var(--blue-mid); }
.adash-alert-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 4px;
}
.adash-alert.error .adash-alert-dot { background: var(--red); }
.adash-alert.warning .adash-alert-dot { background: var(--amber); }
.adash-alert.info .adash-alert-dot { background: var(--blue); }
.adash-alert-content { flex: 1; }
.adash-alert-title { font-size: 13px; font-weight: 700; }
.adash-alert.error .adash-alert-title { color: #991b1b; }
.adash-alert.warning .adash-alert-title { color: #92400e; }
.adash-alert.info .adash-alert-title { color: #1e40af; }
.adash-alert-msg { font-size: 12px; margin-top: 2px; }
.adash-alert.error .adash-alert-msg { color: #b91c1c; }
.adash-alert.warning .adash-alert-msg { color: #a16207; }
.adash-alert.info .adash-alert-msg { color: #1d4ed8; }
.adash-alert-link { font-size: 12px; font-weight: 700; white-space: nowrap; text-decoration: none; }
.adash-alert.error .adash-alert-link { color: var(--red); }
.adash-alert.warning .adash-alert-link { color: var(--amber); }
.adash-alert.info .adash-alert-link { color: var(--blue); }

/* ── Charts ── */
.adash-charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}
@media(max-width:900px){ .adash-charts-grid { grid-template-columns: 1fr; } }

.adash-chart-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 22px 24px;
    box-shadow: var(--shadow);
    animation: fadeUp 0.5s 0.4s both;
}
.adash-chart-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 18px;
    gap: 10px;
    flex-wrap: wrap;
}
.adash-chart-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-1);
}
.adash-chart-sub {
    font-size: 11.5px;
    color: var(--text-3);
    margin-top: 2px;
}
.adash-chart-legend {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
}
.adash-legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 600;
    color: var(--text-2);
}
.adash-legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}
.adash-chart-wrap { position: relative; height: 220px; }

/* ── Tables ── */
.adash-tables-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 20px;
}
@media(max-width:900px){ .adash-tables-grid { grid-template-columns: 1fr; } }

.adash-table-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    animation: fadeUp 0.5s 0.5s both;
}
.adash-table-head {
    padding: 18px 20px 14px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.adash-table-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-1);
}
.adash-table-count {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-3);
    background: var(--surface);
    padding: 3px 8px;
    border-radius: 20px;
    border: 1px solid var(--border);
}
.adash-table { width: 100%; border-collapse: collapse; }
.adash-table thead tr th {
    padding: 10px 20px;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--text-3);
    background: var(--surface);
    text-align: left;
    border-bottom: 1px solid var(--border);
}
.adash-table tbody tr {
    border-bottom: 1px solid var(--border);
    transition: background 0.1s;
}
.adash-table tbody tr:last-child { border-bottom: none; }
.adash-table tbody tr:hover { background: var(--surface); }
.adash-table tbody td {
    padding: 11px 20px;
    font-size: 13px;
    color: var(--text-2);
}
.adash-table tbody td:first-child {
    font-weight: 600;
    color: var(--text-1);
}
.adash-num-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 26px;
    padding: 2px 7px;
    border-radius: 5px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    font-weight: 500;
    background: var(--surface);
    color: var(--text-1);
    border: 1px solid var(--border);
}
.adash-table-empty {
    padding: 28px 20px;
    text-align: center;
    font-size: 13px;
    color: var(--text-3);
}

/* ── Animations ── */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ── Live indicator ── */
.adash-live {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 700;
    color: var(--green);
    background: var(--green-light);
    border: 1px solid var(--green-mid);
    padding: 3px 9px;
    border-radius: 20px;
}
.adash-live-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--green);
    animation: pulse 1.5s infinite;
}
@keyframes pulse {
    0%,100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.8); }
}

/* ── Sparkline mini ── */
.adash-sparkline {
    height: 32px;
    width: 80px;
    flex-shrink: 0;
}

/* ── Divider ── */
.adash-divider {
    border: none;
    border-top: 1px solid var(--border);
    margin: 24px 0;
}
</style>

<div class="adash">

    <!-- Header -->
    <div class="adash-header">
        <div class="adash-header-left">
            <h1>Platform Dashboard</h1>
            <p>Real-time overview of your job portal — employers, candidates, revenue</p>
        </div>
        <div class="adash-header-right">
            <span class="adash-live"><span class="adash-live-dot"></span> Live</span>
            <span class="adash-date-badge">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <?= date('D, d M Y') ?>
            </span>
            <button class="adash-refresh-btn" onclick="location.reload()">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- ── PRIMARY STATS ── -->
    <div class="adash-section-label">Platform Metrics</div>
    <div class="adash-stats-grid" style="margin-bottom:16px;">

        <a href="/admin/employers" class="adash-stat-card blue">
            <div class="adash-stat-top">
                <div class="adash-stat-icon blue">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <span class="adash-stat-trend up">↑ Active</span>
            </div>
            <div class="adash-stat-label">Total Employers</div>
            <div class="adash-stat-value"><?= number_format($stats['total_employers'] ?? 0) ?></div>
            <div class="adash-stat-sub">Registered on platform</div>
        </a>

        <a href="/admin/candidates" class="adash-stat-card green">
            <div class="adash-stat-top">
                <div class="adash-stat-icon green">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <span class="adash-stat-trend up">↑ Growing</span>
            </div>
            <div class="adash-stat-label">Total Candidates</div>
            <div class="adash-stat-value"><?= number_format($stats['total_candidates'] ?? 0) ?></div>
            <div class="adash-stat-sub">Job seekers registered</div>
        </a>

        <a href="/admin/jobs" class="adash-stat-card violet">
            <div class="adash-stat-top">
                <div class="adash-stat-icon violet">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <span class="adash-stat-trend neutral">Live Jobs</span>
            </div>
            <div class="adash-stat-label">Total Jobs</div>
            <div class="adash-stat-value"><?= number_format($stats['total_jobs'] ?? 0) ?></div>
            <div class="adash-stat-sub">Active job postings</div>
        </a>

        <a href="/admin/interviews" class="adash-stat-card amber">
            <div class="adash-stat-top">
                <div class="adash-stat-icon amber">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <span class="adash-stat-trend up">↑ This week</span>
            </div>
            <div class="adash-stat-label">Total Applications</div>
            <div class="adash-stat-value"><?= number_format($stats['total_applications'] ?? 0) ?></div>
            <div class="adash-stat-sub">Submitted by candidates</div>
        </a>

    </div>

    <!-- ── REVENUE ── -->
    <div class="adash-section-label" style="margin-top:24px;">Revenue Overview</div>
    <div class="adash-rev-grid">

        <a href="/admin/payments" class="adash-rev-card">
            <div class="adash-rev-icon">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="adash-rev-label">Today</div>
                <div class="adash-rev-value">₹<?= number_format($revenue['today'] ?? 0, 2) ?></div>
            </div>
        </a>

        <a href="/admin/payments" class="adash-rev-card">
            <div class="adash-rev-icon" style="background: linear-gradient(135deg,#059669,#047857);">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
            </div>
            <div>
                <div class="adash-rev-label">This Week</div>
                <div class="adash-rev-value">₹<?= number_format($revenue['week'] ?? 0, 2) ?></div>
            </div>
        </a>

        <a href="/admin/payments" class="adash-rev-card">
            <div class="adash-rev-icon" style="background: linear-gradient(135deg,#7c3aed,#6d28d9);">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
                <div class="adash-rev-label">This Month</div>
                <div class="adash-rev-value">₹<?= number_format($revenue['month'] ?? 0, 2) ?></div>
            </div>
        </a>

        <a href="/admin/payments" class="adash-rev-card">
            <div class="adash-rev-icon" style="background: linear-gradient(135deg,#d97706,#b45309);">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div>
                <div class="adash-rev-label">Year to Date</div>
                <div class="adash-rev-value">₹<?= number_format($revenue['ytd'] ?? 0, 2) ?></div>
            </div>
        </a>

    </div>

    <!-- ── ALERTS ── -->
    <?php if (!empty($alerts)): ?>
    <div class="adash-alerts">
        <div class="adash-section-label">Alerts & Notifications</div>
        <?php foreach ($alerts as $alert):
            $t = $alert['type'] === 'error' ? 'error' : ($alert['type'] === 'warning' ? 'warning' : 'info');
        ?>
        <div class="adash-alert <?= $t ?>">
            <div class="adash-alert-dot"></div>
            <div class="adash-alert-content">
                <div class="adash-alert-title"><?= htmlspecialchars($alert['title']) ?></div>
                <div class="adash-alert-msg"><?= htmlspecialchars($alert['message']) ?></div>
            </div>
            <a href="<?= htmlspecialchars($alert['link'] ?? '#') ?>" class="adash-alert-link">View →</a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── CHARTS ── -->
    <div class="adash-section-label">Analytics</div>
    <div class="adash-charts-grid">

        <div class="adash-chart-card">
            <div class="adash-chart-header">
                <div>
                    <div class="adash-chart-title">Daily Signups</div>
                    <div class="adash-chart-sub">Last 30 days — Employers vs Candidates</div>
                </div>
                <div class="adash-chart-legend">
                    <span class="adash-legend-item"><span class="adash-legend-dot" style="background:#2563EB;"></span>Employers</span>
                    <span class="adash-legend-item"><span class="adash-legend-dot" style="background:#059669;"></span>Candidates</span>
                </div>
            </div>
            <div class="adash-chart-wrap">
                <canvas id="signupsChart"></canvas>
            </div>
        </div>

        <div class="adash-chart-card">
            <div class="adash-chart-header">
                <div>
                    <div class="adash-chart-title">Job Posting Trends</div>
                    <div class="adash-chart-sub">Last 30 days — Total jobs posted daily</div>
                </div>
                <div class="adash-chart-legend">
                    <span class="adash-legend-item"><span class="adash-legend-dot" style="background:#7c3aed;"></span>Jobs Posted</span>
                </div>
            </div>
            <div class="adash-chart-wrap">
                <canvas id="jobsChart"></canvas>
            </div>
        </div>

    </div>

    <!-- ── TABLES ── -->
    <div class="adash-tables-grid">

        <!-- Candidates by Category -->
        <div class="adash-table-card">
            <div class="adash-table-head">
                <span class="adash-table-title">Candidates by Category</span>
                <span class="adash-table-count"><?= count($candidateByCategory ?? []) ?> categories</span>
            </div>
            <table class="adash-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Candidates</th>
                        <th>Applications</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($candidateByCategory)): ?>
                        <?php foreach ($candidateByCategory as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['category'] ?? '') ?></td>
                            <td><span class="adash-num-badge"><?= (int)($row['candidates'] ?? 0) ?></span></td>
                            <td><span class="adash-num-badge"><?= (int)($row['applications'] ?? 0) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3"><div class="adash-table-empty">No category data available yet</div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Top Candidate Locations -->
        <div class="adash-table-card">
            <div class="adash-table-head">
                <span class="adash-table-title">Top Candidate Locations</span>
                <span class="adash-table-count"><?= count($candidateLocations ?? []) ?> cities</span>
            </div>
            <table class="adash-table">
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>Candidates</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($candidateLocations)): ?>
                        <?php foreach ($candidateLocations as $row):
                            $parts = array_filter([trim($row['city'] ?? ''), trim($row['state'] ?? ''), trim($row['country'] ?? '')]);
                            $loc = !empty($parts) ? implode(', ', $parts) : 'Unknown';
                        ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span style="width:24px;height:24px;border-radius:6px;background:var(--surface);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <svg width="11" height="11" fill="none" stroke="#8896AA" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </span>
                                    <?= htmlspecialchars($loc) ?>
                                </div>
                            </td>
                            <td><span class="adash-num-badge"><?= (int)($row['candidates'] ?? 0) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="2"><div class="adash-table-empty">No location data available yet</div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div><!-- end .adash -->

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Chart.js global defaults ──
    Chart.defaults.font.family = "'Sora', sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#8896AA';
    Chart.defaults.plugins.legend.display = false;
    Chart.defaults.plugins.tooltip.backgroundColor = '#0D1117';
    Chart.defaults.plugins.tooltip.titleColor = '#fff';
    Chart.defaults.plugins.tooltip.bodyColor = '#94A3B8';
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
    Chart.defaults.plugins.tooltip.displayColors = true;
    Chart.defaults.plugins.tooltip.boxWidth = 8;
    Chart.defaults.plugins.tooltip.boxHeight = 8;
    Chart.defaults.plugins.tooltip.boxPadding = 4;
    Chart.defaults.scale.grid.color = '#E4E8F0';
    Chart.defaults.scale.grid.drawBorder = false;
    Chart.defaults.scale.ticks.padding = 8;

    // ── Signups Chart ──
    const signupsCtx = document.getElementById('signupsChart');
    if (signupsCtx) {
        const signupsData = <?= json_encode($dailySignups ?? []) ?>;
        const labels = signupsData.map(d => {
            const dt = new Date(d.date);
            return dt.toLocaleDateString('en-IN', { day: '2-digit', month: 'short' });
        });

        // Gradient fill for employers
        const ctxEl = signupsCtx.getContext('2d');
        const blueGrad = ctxEl.createLinearGradient(0, 0, 0, 220);
        blueGrad.addColorStop(0, 'rgba(37,99,235,0.18)');
        blueGrad.addColorStop(1, 'rgba(37,99,235,0)');

        const greenGrad = ctxEl.createLinearGradient(0, 0, 0, 220);
        greenGrad.addColorStop(0, 'rgba(5,150,105,0.15)');
        greenGrad.addColorStop(1, 'rgba(5,150,105,0)');

        new Chart(signupsCtx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Employers',
                    data: signupsData.map(d => d.employers || 0),
                    borderColor: '#2563EB',
                    backgroundColor: blueGrad,
                    borderWidth: 2,
                    tension: 0.45,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#2563EB',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 5,
                }, {
                    label: 'Candidates',
                    data: signupsData.map(d => d.candidates || 0),
                    borderColor: '#059669',
                    backgroundColor: greenGrad,
                    borderWidth: 2,
                    tension: 0.45,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#059669',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { maxTicksLimit: 8, maxRotation: 0 }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            callback: v => Number.isInteger(v) ? v : null
                        }
                    }
                }
            }
        });
    }

    // ── Jobs Chart ──
    const jobsCtx = document.getElementById('jobsChart');
    if (jobsCtx) {
        const jobsData = <?= json_encode($jobTrends ?? []) ?>;
        const labels = jobsData.map(d => {
            const dt = new Date(d.date);
            return dt.toLocaleDateString('en-IN', { day: '2-digit', month: 'short' });
        });

        const jCtx = jobsCtx.getContext('2d');
        const violetGrad = jCtx.createLinearGradient(0, 0, 0, 220);
        violetGrad.addColorStop(0, 'rgba(124,58,237,0.75)');
        violetGrad.addColorStop(1, 'rgba(124,58,237,0.15)');

        new Chart(jobsCtx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Jobs Posted',
                    data: jobsData.map(d => d.count || 0),
                    backgroundColor: violetGrad,
                    borderColor: '#7C3AED',
                    borderWidth: 0,
                    borderRadius: 5,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` Jobs Posted: ${ctx.parsed.y}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { maxTicksLimit: 8, maxRotation: 0 }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            callback: v => Number.isInteger(v) ? v : null
                        }
                    }
                }
            }
        });
    }

    // ── Auto-refresh every 60 seconds ──
    setTimeout(() => {
        // Soft counter on the header
        const btn = document.querySelector('.adash-refresh-btn');
        if (btn) btn.textContent = '↻ Auto-refreshing...';
        // location.reload(); // Uncomment to enable auto-reload
    }, 60000);
});
</script>