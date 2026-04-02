<?php /** @var array $companies */ ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap');

.fc-page * { font-family: 'Plus Jakarta Sans', sans-serif; box-sizing: border-box; }

/* Layout */
.fc-page { background: #f8f7ff; min-height: 100vh; }

/* Hero */
.fc-hero {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4c1d95 100%);
    position: relative;
    overflow: hidden;
}
.fc-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.fc-hero-content { position: relative; z-index: 1; }

/* Search bar in hero */
.fc-hero-search {
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 14px;
    padding: 6px 6px 6px 18px;
    display: flex; align-items: center; gap: 8px;
}
.fc-hero-search input {
    flex: 1; background: transparent; border: none; outline: none;
    color: #fff; font-size: 14px; font-family: 'Plus Jakarta Sans', sans-serif;
}
.fc-hero-search input::placeholder { color: rgba(255,255,255,0.55); }
.fc-hero-search button {
    background: #fff; color: #4338ca; font-size: 13px; font-weight: 700;
    border: none; border-radius: 10px; padding: 9px 20px; cursor: pointer;
    white-space: nowrap; transition: .15s;
}
.fc-hero-search button:hover { background: #e0e7ff; }

/* Stat chips */
.stat-chip {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 50px;
    padding: 6px 14px;
    display: inline-flex; align-items: center; gap-6px;
    color: rgba(255,255,255,0.85); font-size: 12px; font-weight: 500;
}

/* Filter sidebar */
.filter-sidebar {
    background: #fff;
    border: 1px solid #ede9fe;
    border-radius: 16px;
    position: sticky; top: 20px;
    box-shadow: 0 1px 3px rgba(67,56,202,.06), 0 4px 16px rgba(67,56,202,.04);
}
.filter-label { font-size: 11px; font-weight: 600; color: #6b7280; letter-spacing: .05em; text-transform: uppercase; margin-bottom: 6px; display: block; }
.filter-input {
    width: 100%; padding: 9px 12px; font-size: 13px; font-family: 'Plus Jakarta Sans',sans-serif;
    border: 1.5px solid #e5e7eb; border-radius: 10px; outline: none; color: #111827;
    transition: border-color .15s, box-shadow .15s;
    background: #fafafa;
}
.filter-input:focus { border-color: #6d28d9; box-shadow: 0 0 0 3px rgba(109,40,217,.1); background: #fff; }
.filter-select {
    width: 100%; padding: 9px 12px; font-size: 13px; font-family: 'Plus Jakarta Sans',sans-serif;
    border: 1.5px solid #e5e7eb; border-radius: 10px; outline: none; color: #111827;
    background: #fafafa; cursor: pointer; transition: border-color .15s;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center; background-size: 16px;
}
.filter-select:focus { border-color: #6d28d9; box-shadow: 0 0 0 3px rgba(109,40,217,.1); }

/* Radio pills */
.radio-pill { display: inline-flex; align-items: center; gap: 6px; cursor: pointer; }
.radio-pill input[type=radio] { accent-color: #6d28d9; width:14px; height:14px; }
.radio-pill span { font-size: 12px; font-weight: 500; color: #374151; }

/* Apply button */
.btn-apply {
    width: 100%; padding: 11px; background: linear-gradient(135deg,#6d28d9,#4338ca);
    color: #fff; font-size: 13px; font-weight: 700; border: none; border-radius: 11px;
    cursor: pointer; font-family: 'Plus Jakarta Sans',sans-serif;
    box-shadow: 0 4px 12px rgba(109,40,217,.3); transition: .2s;
}
.btn-apply:hover { opacity: .88; transform: translateY(-1px); }

/* Company cards */
.company-card {
    background: #fff;
    border: 1px solid #ede9fe;
    border-radius: 16px;
    overflow: hidden;
    display: block; color: inherit; text-decoration: none;
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    box-shadow: 0 1px 3px rgba(67,56,202,.05);
}
.company-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 32px rgba(109,40,217,.12);
    border-color: #c4b5fd;
}
.company-logo {
    width: 48px; height: 48px; border-radius: 12px;
    background: #f3f4f6; overflow: hidden; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 18px; color: #6d28d9;
    border: 1px solid #ede9fe;
}
.company-logo img { width:100%; height:100%; object-fit:cover; }
.badge-pill {
    display: inline-flex; align-items: center;
    padding: 3px 10px; border-radius: 50px;
    font-size: 11px; font-weight: 600; background: #f3f4f6; color: #374151;
    white-space: nowrap;
}
.card-footer {
    background: #faf9ff;
    border-top: 1px solid #f0ebff;
    padding: 10px 16px;
    display: flex; align-items: center; justify-content: space-between;
}
.view-link {
    font-size: 12px; font-weight: 700; color: #6d28d9;
    display: inline-flex; align-items: center; gap: 3px;
}

/* How it works */
.hiw-section { background: #fff; }
.hiw-step-num {
    width: 32px; height: 32px; border-radius: 50%;
    background: linear-gradient(135deg,#6d28d9,#4338ca);
    color: #fff; font-size: 13px; font-weight: 800;
    display: flex; align-items: center; justify-center: center;
    flex-shrink: 0; justify-content: center;
}
.hiw-card {
    background: #faf9ff;
    border: 1px solid #ede9fe;
    border-radius: 18px;
    padding: 28px 20px 24px;
    text-align: center;
    transition: .2s;
}
.hiw-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(109,40,217,.1); }
.hiw-icon {
    width: 56px; height: 56px; border-radius: 16px;
    background: linear-gradient(135deg,#6d28d9,#4338ca);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px; color: #fff;
}
.hiw-connector {
    display: none;
}
@media(min-width:768px){ .hiw-connector { display:flex; align-items:center; justify-content:center; } }

/* CTA buttons */
.cta-row { background: linear-gradient(135deg,#1e1b4b,#312e81); }
.cta-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 14px 20px; border-radius: 12px;
    font-size: 14px; font-weight: 700; text-decoration: none;
    transition: .2s; border: none; cursor: pointer; font-family: 'Plus Jakarta Sans',sans-serif;
}
.cta-btn-primary { background: #fff; color: #4338ca; }
.cta-btn-primary:hover { background: #e0e7ff; }
.cta-btn-outline { background: transparent; color: #fff; border: 2px solid rgba(255,255,255,.3); }
.cta-btn-outline:hover { border-color: rgba(255,255,255,.7); background: rgba(255,255,255,.07); }

/* Results header */
.results-bar {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px;
}
.results-count { font-size: 13px; color: #6b7280; font-weight: 500; }
.results-count strong { color: #111827; font-weight: 700; }

/* Empty state */
.empty-state {
    background: #fff; border: 1px solid #ede9fe; border-radius: 16px;
    padding: 64px 24px; text-align: center;
}
</style>

<div class="fc-page">

    <!-- ── HERO ── -->
    <div class="fc-hero py-12 px-4">
        <div class="fc-hero-content max-w-3xl mx-auto text-center">
            <span style="display:inline-block;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);color:rgba(255,255,255,.85);font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;border-radius:50px;padding:4px 14px;margin-bottom:16px;">
                🏢 Featured Companies
            </span>
            <h1 style="font-size:clamp(26px,4vw,42px);font-weight:800;color:#fff;line-height:1.2;margin:0 0 12px;">
                Top Companies Hiring Now
            </h1>
            <p style="color:rgba(255,255,255,.7);font-size:15px;margin:0 0 28px;">
                Discover leading employers across industries and find your next opportunity
            </p>

            <!-- Inline search in hero -->
            <div class="fc-hero-search" style="max-width:520px;margin:0 auto 24px;">
                <svg style="width:18px;height:18px;color:rgba(255,255,255,.6);flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <form method="GET" action="/company/featured" style="flex:1;display:flex;align-items:center;gap:8px;">
                    <input type="text" name="q" value="<?= htmlspecialchars($filters['q'] ?? '') ?>" placeholder="Search companies, industries...">
                    <button type="submit">Search</button>
                </form>
            </div>

            <!-- Stat chips -->
            <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                <span class="stat-chip">
                    <svg style="width:13px;height:13px;margin-right:5px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <?= number_format(count($companies ?? [])) ?>+ Companies
                </span>
                <span class="stat-chip">
                    <svg style="width:13px;height:13px;margin-right:5px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Verified Employers
                </span>
                <span class="stat-chip">
                    <svg style="width:13px;height:13px;margin-right:5px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    Pan India
                </span>
            </div>
        </div>
    </div>

    <!-- ── MAIN CONTENT ── -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

            <!-- ── FILTER SIDEBAR ── -->
            <aside class="lg:col-span-1">
                <div class="filter-sidebar p-5">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:18px;">
                        <div style="width:32px;height:32px;background:linear-gradient(135deg,#6d28d9,#4338ca);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <svg style="width:16px;height:16px;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        </div>
                        <span style="font-size:14px;font-weight:700;color:#111827;">Filter Companies</span>
                    </div>

                    <form method="GET" action="/company/featured" class="space-y-4">

                        <div>
                            <label class="filter-label">Search Company</label>
                            <div style="position:relative;">
                                <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                                <input type="text" name="q" value="<?= htmlspecialchars($filters['q'] ?? '') ?>" class="filter-input" style="padding-left:32px;" placeholder="e.g. Infosys, TCS...">
                            </div>
                        </div>

                        <div>
                            <label class="filter-label">Location</label>
                            <div style="position:relative;">
                                <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                <input type="text" name="location" value="<?= htmlspecialchars($filters['location'] ?? '') ?>" class="filter-input" style="padding-left:32px;" placeholder="e.g. Mumbai, Bangalore">
                            </div>
                        </div>

                        <div>
                            <label class="filter-label">Industry</label>
                            <div style="position:relative;">
                                <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <input type="text" name="industry" value="<?= htmlspecialchars($filters['industry'] ?? '') ?>" class="filter-input" style="padding-left:32px;" placeholder="e.g. IT Services, Finance">
                            </div>
                        </div>

                        <div>
                            <label class="filter-label">Department</label>
                            <select name="department" class="filter-select">
                                <option value="" <?= (($filters['department'] ?? '') === '') ? 'selected' : '' ?>>All Departments</option>
                                <option value="Engineering - Software & QA" <?= (($filters['department'] ?? '') === 'Engineering - Software & QA') ? 'selected' : '' ?>>Engineering & QA</option>
                                <option value="Sales & Business Development" <?= (($filters['department'] ?? '') === 'Sales & Business Development') ? 'selected' : '' ?>>Sales & BD</option>
                                <option value="Finance & Accounting" <?= (($filters['department'] ?? '') === 'Finance & Accounting') ? 'selected' : '' ?>>Finance & Accounting</option>
                            </select>
                        </div>

                        <div>
                            <label class="filter-label">Experience Level</label>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:2px;">
                                <?php
                                $exp_options = [['entry','Entry Level'],['experienced','Experienced'],['','All']];
                                foreach ($exp_options as [$val, $lbl]):
                                ?>
                                <label style="display:inline-flex;align-items:center;gap:5px;cursor:pointer;background:<?= (($filters['experience'] ?? '') === $val) ? '#ede9fe' : '#f9fafb' ?>;border:1.5px solid <?= (($filters['experience'] ?? '') === $val) ? '#6d28d9' : '#e5e7eb' ?>;border-radius:8px;padding:6px 10px;">
                                    <input type="radio" name="experience" value="<?= $val ?>" <?= (($filters['experience'] ?? '') === $val) ? 'checked' : '' ?> style="accent-color:#6d28d9;width:13px;height:13px;">
                                    <span style="font-size:12px;font-weight:500;color:<?= (($filters['experience'] ?? '') === $val) ? '#6d28d9' : '#374151' ?>;"><?= $lbl ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div>
                            <label class="filter-label">Founded Year</label>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                                <div>
                                    <input type="number" name="year_from" value="<?= htmlspecialchars($filters['year_from'] ?? '') ?>" class="filter-input" placeholder="From">
                                </div>
                                <div>
                                    <input type="number" name="year_to" value="<?= htmlspecialchars($filters['year_to'] ?? '') ?>" class="filter-input" placeholder="To">
                                </div>
                            </div>
                        </div>

                        <div style="display:grid;gap:8px;">
                            <button type="submit" class="btn-apply">
                                Apply Filters
                            </button>
                            <a href="/company/featured" style="display:block;text-align:center;font-size:12px;font-weight:600;color:#6b7280;padding:8px;border-radius:10px;background:#f9fafb;border:1.5px solid #e5e7eb;text-decoration:none;transition:.15s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#f9fafb'">
                                Clear All
                            </a>
                        </div>
                    </form>
                </div>
            </aside>

            <!-- ── COMPANY GRID ── -->
            <section class="lg:col-span-3">

                <!-- Results bar -->
                <div class="results-bar">
                    <p class="results-count">
                        Showing <strong><?= count($companies ?? []) ?></strong> companies
                        <?php if (!empty($filters['q'])): ?>
                            for "<strong><?= htmlspecialchars($filters['q']) ?></strong>"
                        <?php endif; ?>
                    </p>
                    <span style="font-size:12px;color:#9ca3af;font-weight:500;">Updated daily</span>
                </div>

                <?php if (!empty($companies)): ?>
                <div class="grid md:grid-cols-2 gap-4">
                    <?php foreach ($companies as $co):
                        $rating   = (float)($co['rating'] ?? 0);
                        $reviews  = (int)($co['reviews_count'] ?? 0);
                        $href     = !empty($co['slug']) ? '/company/' . htmlspecialchars($co['slug']) : '/candidate/jobs?company=' . urlencode($co['name'] ?? '');
                        $initial  = strtoupper(substr($co['name'] ?? 'C', 0, 1));
                    ?>
                    <a href="<?= $href ?>" class="company-card">
                        <div style="padding:18px 18px 14px;">

                            <!-- Logo + Name row -->
                            <div style="display:flex;align-items:flex-start;gap:14px;">
                                <div class="company-logo">
                                    <?php if (!empty($co['logo_url'])): ?>
                                        <img src="<?= htmlspecialchars($co['logo_url']) ?>" alt="<?= htmlspecialchars($co['name'] ?? '') ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                        <span style="display:none;width:100%;height:100%;align-items:center;justify-content:center;font-weight:800;font-size:20px;color:#6d28d9;"><?= $initial ?></span>
                                    <?php else: ?>
                                        <span style="font-weight:800;font-size:20px;color:#6d28d9;"><?= $initial ?></span>
                                    <?php endif; ?>
                                </div>

                                <div style="flex:1;min-width:0;">
                                    <div style="display:flex;align-items:start;justify-content:space-between;gap:8px;">
                                        <h3 style="font-size:15px;font-weight:700;color:#111827;margin:0;line-height:1.3;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            <?= htmlspecialchars($co['name'] ?? 'Company') ?>
                                        </h3>
                                        <?php if ($rating > 0): ?>
                                        <span style="display:inline-flex;align-items:center;gap:3px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:3px 8px;flex-shrink:0;">
                                            <svg style="width:11px;height:11px;color:#f59e0b;" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            <span style="font-size:11px;font-weight:700;color:#92400e;"><?= number_format($rating, 1) ?></span>
                                        </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Industry + reviews -->
                                    <div style="margin-top:5px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                        <?php if (!empty($co['industry'])): ?>
                                        <span style="font-size:11px;color:#6d28d9;font-weight:600;"><?= htmlspecialchars($co['industry']) ?></span>
                                        <?php endif; ?>
                                        <?php if ($reviews > 0): ?>
                                        <span style="font-size:10px;color:#9ca3af;">·</span>
                                        <span style="font-size:11px;color:#9ca3af;"><?= $reviews ?> review<?= $reviews !== 1 ? 's' : '' ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Tags row -->
                            <div style="margin-top:12px;display:flex;flex-wrap:wrap;gap:6px;">
                                <?php if (!empty($co['company_size'])): ?>
                                <span class="badge-pill">
                                    <svg style="width:10px;height:10px;margin-right:3px;color:#6d28d9;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <?= htmlspecialchars($co['company_size']) ?>
                                </span>
                                <?php endif; ?>
                                <?php if (!empty($co['founded_year'])): ?>
                                <span class="badge-pill">
                                    <svg style="width:10px;height:10px;margin-right:3px;color:#6d28d9;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Est. <?= htmlspecialchars($co['founded_year']) ?>
                                </span>
                                <?php endif; ?>
                                <?php if (!empty($co['location'])): ?>
                                <span class="badge-pill">
                                    <svg style="width:10px;height:10px;margin-right:3px;color:#6d28d9;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    <?= htmlspecialchars($co['location']) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="card-footer">
                            <span style="font-size:11px;color:#9ca3af;font-weight:500;">View open positions</span>
                            <span class="view-link">
                                Explore
                                <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <?php else: ?>
                <div class="empty-state">
                    <div style="width:56px;height:56px;background:#ede9fe;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                        <svg style="width:28px;height:28px;color:#6d28d9;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                    </div>
                    <h3 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 6px;">No companies found</h3>
                    <p style="font-size:13px;color:#6b7280;margin:0 0 20px;">Try adjusting your filters or search query</p>
                    <a href="/company/featured" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;background:linear-gradient(135deg,#6d28d9,#4338ca);color:#fff;font-size:13px;font-weight:700;border-radius:10px;text-decoration:none;">
                        Clear Filters
                    </a>
                </div>
                <?php endif; ?>

            </section>
        </div>
    </div>

    <!-- ── HOW IT WORKS ── -->
    <section class="hiw-section py-14 mt-4">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div style="text-align:center;margin-bottom:40px;">
                <span style="display:inline-block;background:#ede9fe;color:#6d28d9;font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;border-radius:50px;padding:4px 14px;margin-bottom:12px;">Process</span>
                <h2 style="font-size:clamp(22px,3vw,32px);font-weight:800;color:#111827;margin:0 0 8px;">How it works</h2>
                <p style="font-size:14px;color:#6b7280;margin:0;">Your simple journey to the perfect job on Mindware Infotech</p>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
                <?php
                $steps = [
                    ['1','Create Account','Register as a candidate or employer in minutes','M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    ['2','Upload Resume','Build or upload your professional resume','M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ['3','Find Jobs','Explore thousands of opportunities across all industries','M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0'],
                    ['4','Apply & Get Hired','Apply with one click and connect directly with employers','M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0'],
                ];
                foreach ($steps as [$num, $title, $desc, $path]):
                ?>
                <div class="hiw-card">
                    <div style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;background:#ede9fe;color:#6d28d9;font-size:12px;font-weight:800;border-radius:50%;margin-bottom:16px;">
                        <?= $num ?>
                    </div>
                    <div class="hiw-icon">
                        <svg style="width:24px;height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="<?= $path ?>"/>
                        </svg>
                    </div>
                    <h3 style="font-size:15px;font-weight:700;color:#111827;margin:0 0 8px;"><?= $title ?></h3>
                    <p style="font-size:12px;color:#6b7280;line-height:1.6;margin:0;"><?= $desc ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ── CTA ROW ── -->
    <div class="cta-row py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div style="text-align:center;margin-bottom:24px;">
                <h2 style="font-size:22px;font-weight:800;color:#fff;margin:0 0 6px;">Ready to find your dream job?</h2>
                <p style="font-size:13px;color:rgba(255,255,255,.65);margin:0;">Join thousands of candidates already hired through Mindware Infotech</p>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;max-width:700px;margin:0 auto;">
                <a href="/register-candidate" class="cta-btn cta-btn-primary">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Register Free
                </a>
                <a href="/candidate/dashboard" class="cta-btn cta-btn-outline">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Upload Resume
                </a>
                <a href="/jobs" class="cta-btn cta-btn-outline">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                    Browse Jobs
                </a>
                <a href="/jobs" class="cta-btn cta-btn-outline">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>
                    Apply Now
                </a>
            </div>
        </div>
    </div>

</div>