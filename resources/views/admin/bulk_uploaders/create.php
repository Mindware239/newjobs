<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
:root {
    --ink: #0D1117;
    --surface: #F4F6FB;
    --card: #FFFFFF;
    --border: #E4E8F0;
    --border-focus: #2563EB;
    --text-1: #0D1117;
    --text-2: #4A5568;
    --text-3: #8896AA;
    --blue: #2563EB;
    --blue-light: #EFF6FF;
    --blue-mid: #BFDBFE;
    --green: #059669;
    --green-light: #ECFDF5;
    --green-mid: #A7F3D0;
    --red: #DC2626;
    --red-light: #FEF2F2;
    --amber: #D97706;
    --amber-light: #FFFBEB;
    --violet: #7C3AED;
    --violet-light: #F5F3FF;
    --shadow-sm: 0 1px 2px rgba(13,17,23,0.05);
    --shadow: 0 2px 8px rgba(13,17,23,0.07), 0 0 1px rgba(13,17,23,0.06);
    --shadow-md: 0 6px 20px rgba(13,17,23,0.10), 0 0 1px rgba(13,17,23,0.06);
    --radius: 12px;
    --radius-sm: 8px;
    --radius-lg: 16px;
}

.buc-page { font-family: 'Sora', sans-serif; color: var(--text-1); padding: 28px 0 60px; }

/* ── Layout ── */
.buc-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 24px;
    max-width: 960px;
    margin: 0 auto;
}
@media(max-width: 860px) { .buc-layout { grid-template-columns: 1fr; } }

/* ── Back link ── */
.buc-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-3);
    text-decoration: none;
    margin-bottom: 20px;
    padding: 5px 0;
    transition: color 0.15s;
    max-width: 960px;
    margin-left: auto;
    margin-right: auto;
    display: flex;
}
.buc-back:hover { color: var(--blue); }

/* ── Main card ── */
.buc-form-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
    animation: fadeUp 0.4s both;
}

.buc-form-head {
    padding: 24px 28px 20px;
    border-bottom: 1px solid var(--border);
    background: linear-gradient(135deg, #f8faff 0%, #f0f4ff 100%);
    display: flex;
    align-items: flex-start;
    gap: 14px;
}
.buc-form-head-icon {
    width: 46px;
    height: 46px;
    border-radius: 11px;
    background: var(--blue);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 3px 10px rgba(37,99,235,0.3);
}
.buc-form-head-title { font-size: 18px; font-weight: 800; color: var(--text-1); letter-spacing: -0.4px; }
.buc-form-head-sub { font-size: 13px; color: var(--text-3); margin-top: 3px; line-height: 1.5; }

.buc-form-body { padding: 28px; }

/* ── Error ── */
.buc-error {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    background: var(--red-light);
    border: 1px solid #fecaca;
    border-radius: var(--radius-sm);
    padding: 12px 14px;
    margin-bottom: 22px;
    font-size: 13px;
    font-weight: 600;
    color: var(--red);
}

/* ── Section group ── */
.buc-section-title {
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    color: var(--text-3);
    margin-bottom: 14px;
    margin-top: 24px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.buc-section-title:first-child { margin-top: 0; }
.buc-section-title::after { content: ''; flex: 1; height: 1px; background: var(--border); }

/* ── Fields ── */
.buc-field { margin-bottom: 18px; }
.buc-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13px;
    font-weight: 700;
    color: var(--text-2);
    margin-bottom: 7px;
}
.buc-label-req { color: var(--red); margin-left: 2px; }
.buc-label-hint { font-size: 11px; font-weight: 500; color: var(--text-3); }

.buc-input, .buc-select {
    width: 100%;
    padding: 11px 14px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    font-size: 13.5px;
    font-family: 'Sora', sans-serif;
    color: var(--text-1);
    background: #fff;
    outline: none;
    transition: border-color 0.18s, box-shadow 0.18s;
    appearance: none;
}
.buc-input::placeholder { color: var(--text-3); }
.buc-input:focus, .buc-select:focus {
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
.buc-input.mono { font-family: 'JetBrains Mono', monospace; font-size: 13px; }

.buc-select-wrap { position: relative; }
.buc-select-wrap .buc-select { padding-right: 36px; cursor: pointer; }
.buc-select-arrow {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: var(--text-3);
}

.buc-input-icon-wrap { position: relative; }
.buc-input-icon-wrap .buc-input { padding-left: 40px; }
.buc-input-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-3);
    pointer-events: none;
}

.buc-field-hint { font-size: 11.5px; color: var(--text-3); margin-top: 5px; display: flex; align-items: center; gap: 5px; }

/* ── Grid ── */
.buc-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media(max-width: 540px) { .buc-grid-2 { grid-template-columns: 1fr; } }

/* ── Status radio cards ── */
.buc-status-group { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.buc-status-card {
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 13px 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 11px;
    transition: all 0.15s;
    position: relative;
}
.buc-status-card:hover { border-color: var(--blue); }
.buc-status-card input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
.buc-status-card.checked-active { border-color: var(--green); background: var(--green-light); }
.buc-status-card.checked-suspended { border-color: var(--red); background: var(--red-light); }
.buc-status-radio-dot {
    width: 18px; height: 18px;
    border-radius: 50%;
    border: 2px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    transition: border-color 0.15s;
}
.buc-status-card.checked-active .buc-status-radio-dot { border-color: var(--green); }
.buc-status-card.checked-suspended .buc-status-radio-dot { border-color: var(--red); }
.buc-status-radio-fill {
    width: 9px; height: 9px;
    border-radius: 50%;
    transform: scale(0);
    transition: transform 0.15s;
}
.buc-status-card.checked-active .buc-status-radio-fill { background: var(--green); transform: scale(1); }
.buc-status-card.checked-suspended .buc-status-radio-fill { background: var(--red); transform: scale(1); }
.buc-status-label { font-size: 13px; font-weight: 700; }
.buc-status-card.checked-active .buc-status-label { color: var(--green); }
.buc-status-card.checked-suspended .buc-status-label { color: var(--red); }
.buc-status-desc { font-size: 11px; color: var(--text-3); margin-top: 1px; }

/* ── Submit row ── */
.buc-form-footer {
    padding: 20px 28px;
    border-top: 1px solid var(--border);
    background: var(--surface);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.buc-form-footer-note { font-size: 12px; color: var(--text-3); display: flex; align-items: center; gap: 6px; }
.buc-submit-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 24px;
    background: var(--blue);
    color: #fff;
    border: none;
    border-radius: var(--radius-sm);
    font-size: 14px;
    font-weight: 700;
    font-family: 'Sora', sans-serif;
    cursor: pointer;
    transition: all 0.15s;
    box-shadow: 0 3px 10px rgba(37,99,235,0.3);
}
.buc-submit-btn:hover { background: #1d4ed8; transform: translateY(-1px); box-shadow: 0 5px 16px rgba(37,99,235,0.4); }
.buc-cancel-link {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-3);
    text-decoration: none;
    padding: 11px 16px;
    border-radius: var(--radius-sm);
    transition: all 0.15s;
    border: 1.5px solid var(--border);
    background: #fff;
}
.buc-cancel-link:hover { color: var(--text-1); background: var(--surface); }

/* ── Sidebar ── */
.buc-sidebar { display: flex; flex-direction: column; gap: 16px; }

.buc-info-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    animation: fadeUp 0.4s 0.1s both;
}
.buc-info-card-head {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 9px;
    background: var(--surface);
}
.buc-info-card-head-icon {
    width: 28px; height: 28px;
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.buc-info-card-title { font-size: 12.5px; font-weight: 800; color: var(--text-1); }
.buc-info-card-body { padding: 16px 18px; }

/* What is this feature */
.buc-what-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
.buc-what-item { display: flex; align-items: flex-start; gap: 9px; font-size: 12.5px; color: var(--text-2); line-height: 1.55; }
.buc-what-dot { width: 18px; height: 18px; border-radius: 5px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }

/* Type cards in sidebar */
.buc-type-list { display: flex; flex-direction: column; gap: 8px; }
.buc-type-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    background: #FAFBFD;
}
.buc-type-pill {
    display: inline-flex;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 10.5px;
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
    margin-top: 1px;
}
.buc-type-item-desc { font-size: 11.5px; color: var(--text-2); line-height: 1.5; }

/* Permission list */
.buc-perm-list { display: flex; flex-direction: column; gap: 7px; }
.buc-perm-item { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text-2); }
.buc-perm-icon { width: 18px; height: 18px; border-radius: 5px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

/* Animations */
@keyframes fadeUp { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
</style>

<div class="buc-page">

    <!-- Back link -->
    <a href="/admin/bulk-uploaders" class="buc-back" style="max-width:960px; margin: 0 auto 18px; display:flex;">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        Back to Bulk Uploaders
    </a>

    <div class="buc-layout">

        <!-- ── MAIN FORM CARD ── -->
        <div class="buc-form-card">

            <!-- Card Header -->
            <div class="buc-form-head">
                <div class="buc-form-head-icon">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <div class="buc-form-head-title">Create Bulk Uploader Account</div>
                    <div class="buc-form-head-sub">Grant a third-party (college, HR firm, consultancy) access to upload candidate resumes in bulk — without exposing any employer or candidate data.</div>
                </div>
            </div>

            <!-- Form Body -->
            <div class="buc-form-body">

                <?php if (!empty($error)): ?>
                <div class="buc-error">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0; margin-top:1px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form method="post">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <!-- Section: Identity -->
                    <div class="buc-section-title">Account Identity</div>

                    <div class="buc-field">
                        <div class="buc-label">
                            Full Name <span class="buc-label-req">*</span>
                            <span class="buc-label-hint">Organisation or contact name</span>
                        </div>
                        <div class="buc-input-icon-wrap">
                            <span class="buc-input-icon">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                            <input type="text" name="name" class="buc-input" placeholder="e.g. Mindware HR Solutions" required>
                        </div>
                    </div>

                    <div class="buc-grid-2">
                        <div class="buc-field" style="margin-bottom:0;">
                            <div class="buc-label">Username <span class="buc-label-req">*</span></div>
                            <div class="buc-input-icon-wrap">
                                <span class="buc-input-icon">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                                </span>
                                <input type="text" name="username" class="buc-input mono" placeholder="e.g. mindware_hr" required>
                            </div>
                            <div class="buc-field-hint">
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Used to log in to the upload portal
                            </div>
                        </div>
                        <div class="buc-field" style="margin-bottom:0;">
                            <div class="buc-label">Password <span class="buc-label-req">*</span></div>
                            <div class="buc-input-icon-wrap">
                                <span class="buc-input-icon">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                                <input type="password" name="password" class="buc-input" placeholder="Strong password" required>
                            </div>
                            <div class="buc-field-hint">
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Share securely with the uploader
                            </div>
                        </div>
                    </div>

                    <!-- Section: Configuration -->
                    <div class="buc-section-title">Upload Configuration</div>

                    <div class="buc-field">
                        <div class="buc-label">
                            Account Type <span class="buc-label-req">*</span>
                            <span class="buc-label-hint">Classifies the uploader's organisation</span>
                        </div>
                        <div class="buc-select-wrap">
                            <select name="type" class="buc-select" required>
                                <option value="">Select account type…</option>
                                <option value="College">College</option>
                                <option value="HR">HR</option>
                                <option value="Consultancy">Consultancy</option>
                                <option value="Institution">Institution</option>
                            </select>
                            <span class="buc-select-arrow">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </span>
                        </div>
                    </div>

                    <div class="buc-grid-2">
                        <div class="buc-field" style="margin-bottom:0;">
                            <div class="buc-label">
                                Upload Limit <span class="buc-label-req">*</span>
                            </div>
                            <div class="buc-input-icon-wrap">
                                <span class="buc-input-icon">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </span>
                                <input type="number" name="limit_total" min="1" class="buc-input mono" placeholder="e.g. 100" required>
                            </div>
                            <div class="buc-field-hint">
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Max number of CVs this account can upload
                            </div>
                        </div>
                        <div class="buc-field" style="margin-bottom:0;">
                            <div class="buc-label">Expiry Date</div>
                            <div class="buc-input-icon-wrap">
                                <span class="buc-input-icon">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </span>
                                <input type="date" name="expires_at" class="buc-input">
                            </div>
                            <div class="buc-field-hint">
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Leave blank for no expiry
                            </div>
                        </div>
                    </div>

                    <!-- Section: Status -->
                    <div class="buc-section-title" style="margin-top:26px;">Initial Status</div>

                    <div class="buc-field" style="margin-bottom:0;">
                        <div class="buc-status-group" id="statusGroup">
                            <label class="buc-status-card checked-active" id="card-active" onclick="selectStatus('active')">
                                <input type="radio" name="status" value="active" checked>
                                <div class="buc-status-radio-dot"><div class="buc-status-radio-fill"></div></div>
                                <div>
                                    <div class="buc-status-label">Active</div>
                                    <div class="buc-status-desc">Account is enabled immediately — uploader can log in and start uploading right away.</div>
                                </div>
                            </label>
                            <label class="buc-status-card" id="card-suspended" onclick="selectStatus('suspended')">
                                <input type="radio" name="status" value="suspended">
                                <div class="buc-status-radio-dot"><div class="buc-status-radio-fill"></div></div>
                                <div>
                                    <div class="buc-status-label">Suspended</div>
                                    <div class="buc-status-desc">Account is created but access is blocked. Activate later from the Bulk Uploaders list.</div>
                                </div>
                            </label>
                        </div>
                    </div>

                </form>
            </div><!-- end form body -->

            <!-- Footer with CTA -->
            <div class="buc-form-footer">
                <div class="buc-form-footer-note">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Credentials are stored encrypted — share them securely.
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <a href="/admin/bulk-uploaders" class="buc-cancel-link">Cancel</a>
                    <button type="submit" form="" onclick="document.querySelector('form').submit()" class="buc-submit-btn">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Create Account
                    </button>
                </div>
            </div>

        </div><!-- end form card -->

        <!-- ── SIDEBAR ── -->
        <div class="buc-sidebar">

            <!-- What is this? -->
            <div class="buc-info-card">
                <div class="buc-info-card-head">
                    <div class="buc-info-card-head-icon" style="background:#EFF6FF; color:#2563EB;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="buc-info-card-title">What is a Bulk Uploader?</div>
                </div>
                <div class="buc-info-card-body">
                    <ul class="buc-what-list">
                        <li class="buc-what-item">
                            <div class="buc-what-dot" style="background:#EFF6FF; color:#2563EB;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            </div>
                            A <strong>Bulk Uploader</strong> is a trusted third-party (college, HR firm, or consultancy) given a dedicated login to upload candidate resumes directly into your platform.
                        </li>
                        <li class="buc-what-item">
                            <div class="buc-what-dot" style="background:#ECFDF5; color:#059669;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4"/></svg>
                            </div>
                            Each account has a fixed <strong>CV upload limit</strong> and an optional expiry — after which the account auto-locks.
                        </li>
                        <li class="buc-what-item">
                            <div class="buc-what-dot" style="background:#FFFBEB; color:#D97706;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01"/></svg>
                            </div>
                            Uploaders operate in a <strong>sandboxed environment</strong> — they cannot see job listings, employer accounts, or candidate profiles.
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Account Types -->
            <div class="buc-info-card">
                <div class="buc-info-card-head">
                    <div class="buc-info-card-head-icon" style="background:#F5F3FF; color:#7C3AED;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    </div>
                    <div class="buc-info-card-title">Account Types Explained</div>
                </div>
                <div class="buc-info-card-body">
                    <div class="buc-type-list">
                        <div class="buc-type-item">
                            <span class="buc-type-pill" style="background:#EFF6FF; color:#2563EB; border:1px solid #bfdbfe;">College</span>
                            <div class="buc-type-item-desc">Universities & institutions uploading fresh graduate CVs in batch placements.</div>
                        </div>
                        <div class="buc-type-item">
                            <span class="buc-type-pill" style="background:#ECFDF5; color:#059669; border:1px solid #a7f3d0;">HR</span>
                            <div class="buc-type-item-desc">In-house HR teams sourcing pre-screened candidates from offline databases.</div>
                        </div>
                        <div class="buc-type-item">
                            <span class="buc-type-pill" style="background:#F5F3FF; color:#7C3AED; border:1px solid #ddd6fe;">Consultancy</span>
                            <div class="buc-type-item-desc">Recruitment firms submitting curated candidate pools on behalf of employers.</div>
                        </div>
                        <div class="buc-type-item">
                            <span class="buc-type-pill" style="background:#FFFBEB; color:#D97706; border:1px solid #fde68a;">Institution</span>
                            <div class="buc-type-item-desc">Training centres or skill development bodies uploading certified trainees.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="buc-info-card">
                <div class="buc-info-card-head">
                    <div class="buc-info-card-head-icon" style="background:#FEF2F2; color:#DC2626;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div class="buc-info-card-title">Access Permissions</div>
                </div>
                <div class="buc-info-card-body">
                    <div class="buc-perm-list">
                        <div class="buc-perm-item">
                            <div class="buc-perm-icon" style="background:#ECFDF5; color:#059669;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            Upload candidate resumes (CSV / Excel)
                        </div>
                        <div class="buc-perm-item">
                            <div class="buc-perm-icon" style="background:#ECFDF5; color:#059669;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            View their own upload history & batches
                        </div>
                        <div class="buc-perm-item">
                            <div class="buc-perm-icon" style="background:#ECFDF5; color:#059669;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            Track remaining upload quota
                        </div>
                        <div style="height:1px; background: var(--border); margin: 8px 0;"></div>
                        <div class="buc-perm-item">
                            <div class="buc-perm-icon" style="background:#FEF2F2; color:#DC2626;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                            <span style="color:var(--text-3);">Access employer accounts or jobs</span>
                        </div>
                        <div class="buc-perm-item">
                            <div class="buc-perm-icon" style="background:#FEF2F2; color:#DC2626;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                            <span style="color:var(--text-3);">View candidate profiles or contact details</span>
                        </div>
                        <div class="buc-perm-item">
                            <div class="buc-perm-icon" style="background:#FEF2F2; color:#DC2626;">
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                            <span style="color:var(--text-3);">Access admin panel or platform settings</span>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- end sidebar -->

    </div><!-- end layout -->
</div>

<script>
function selectStatus(val) {
    const cardActive    = document.getElementById('card-active');
    const cardSuspended = document.getElementById('card-suspended');
    cardActive.className    = 'buc-status-card' + (val === 'active'    ? ' checked-active'    : '');
    cardSuspended.className = 'buc-status-card' + (val === 'suspended' ? ' checked-suspended' : '');
    document.querySelector('input[name="status"][value="' + val + '"]').checked = true;
}
</script>