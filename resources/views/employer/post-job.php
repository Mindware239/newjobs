<?php
// Add styles and scripts to head
$scripts = ($scripts ?? '') . '
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    [x-cloak] { display: none !important; }

    :root {
        --brand: #0A65CC;
        --brand-dark: #084fa0;
        --brand-light: #EAF3FF;
        --brand-mid: #b8d6f5;
        --accent: #00A36C;
        --danger: #E53E3E;
        --warning: #F6AD55;
        --neutral-50: #F8FAFC;
        --neutral-100: #F1F5F9;
        --neutral-200: #E2E8F0;
        --neutral-300: #CBD5E1;
        --neutral-400: #94A3B8;
        --neutral-500: #64748B;
        --neutral-700: #334155;
        --neutral-900: #0F172A;
        --radius: 10px;
        --radius-lg: 14px;
        --shadow-sm: 0 1px 3px rgba(15,23,42,0.07), 0 1px 2px rgba(15,23,42,0.04);
        --shadow: 0 4px 12px rgba(15,23,42,0.09), 0 1px 3px rgba(15,23,42,0.06);
        --shadow-lg: 0 10px 30px rgba(15,23,42,0.12), 0 2px 6px rgba(15,23,42,0.06);
    }

    *, *::before, *::after { box-sizing: border-box; }

    body { font-family: \'Plus Jakarta Sans\', sans-serif; }

    .jwiz-wrap { background: #F0F4FA; min-height: 100vh; font-family: \'Plus Jakarta Sans\', sans-serif; color: var(--neutral-900); }

    /* Loading */
    .jwiz-loader { position: fixed; inset: 0; background: #fff; z-index: 9999; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .jwiz-spinner { width: 44px; height: 44px; border: 3px solid var(--brand-light); border-top-color: var(--brand); border-radius: 50%; animation: spin 0.7s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .jwiz-loader-text { margin-top: 14px; font-size: 13px; font-weight: 600; color: var(--neutral-400); letter-spacing: 0.04em; }

    /* Modal overlay */
    .jwiz-modal-bg { position: fixed; inset: 0; z-index: 200; display: flex; align-items: center; justify-content: center; background: rgba(15,23,42,0.45); backdrop-filter: blur(4px); }
    .jwiz-modal { background: #fff; border-radius: 18px; box-shadow: var(--shadow-lg); width: 100%; max-width: 480px; padding: 28px 32px; border: 1px solid var(--neutral-200); }
    .jwiz-modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; }
    .jwiz-modal-title { font-size: 17px; font-weight: 800; color: var(--neutral-900); }
    .jwiz-modal-close { background: none; border: none; font-size: 22px; color: var(--neutral-400); cursor: pointer; line-height: 1; padding: 2px 6px; border-radius: 6px; transition: color 0.15s, background 0.15s; }
    .jwiz-modal-close:hover { background: var(--neutral-100); color: var(--neutral-700); }
    .jwiz-modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; }

    /* Layout */
    .jwiz-container { max-width: 980px; margin: 0 auto; padding: 28px 16px 80px; }

    /* Page header */
    .jwiz-page-header { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 24px; }
    .jwiz-page-title { font-size: 22px; font-weight: 800; color: var(--neutral-900); letter-spacing: -0.4px; }
    .jwiz-page-sub { font-size: 13px; color: var(--neutral-400); margin-top: 3px; }
    .jwiz-badge-premium { display: inline-flex; align-items: center; gap: 6px; background: #fff8e6; color: #92600A; font-size: 12px; font-weight: 700; padding: 5px 12px; border-radius: 20px; border: 1px solid #f5d98a; letter-spacing: 0.02em; }
    .jwiz-badge-dot { width: 7px; height: 7px; background: #F6AD55; border-radius: 50%; }

    /* Stepper */
    .jwiz-stepper-card { background: #fff; border-radius: var(--radius-lg); border: 1px solid var(--neutral-200); padding: 20px 28px 28px; margin-bottom: 24px; box-shadow: var(--shadow-sm); }
    .jwiz-stepper-track { position: relative; display: flex; align-items: center; justify-content: space-between; }
    .jwiz-stepper-line { position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 100%; height: 3px; background: var(--neutral-100); border-radius: 99px; z-index: 0; }
    .jwiz-stepper-line-fill { position: absolute; left: 0; top: 50%; transform: translateY(-50%); height: 3px; background: var(--brand); border-radius: 99px; z-index: 1; transition: width 0.5s cubic-bezier(.4,0,.2,1); }
    .jwiz-step-item { display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2; cursor: pointer; }
    .jwiz-step-circle { width: 38px; height: 38px; border-radius: 50%; border: 2.5px solid var(--neutral-200); background: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; color: var(--neutral-400); transition: all 0.25s; box-shadow: var(--shadow-sm); }
    .jwiz-step-circle.active { border-color: var(--brand); color: var(--brand); box-shadow: 0 0 0 4px var(--brand-light); transform: scale(1.1); }
    .jwiz-step-circle.done { border-color: var(--brand); background: var(--brand); color: #fff; }
    .jwiz-step-label { font-size: 11px; font-weight: 600; color: var(--neutral-400); margin-top: 8px; white-space: nowrap; letter-spacing: 0.02em; text-transform: uppercase; transition: color 0.2s; }
    .jwiz-step-label.active, .jwiz-step-label.done { color: var(--brand); }

    /* Main card */
    .jwiz-card { background: #fff; border-radius: var(--radius-lg); border: 1px solid var(--neutral-200); box-shadow: var(--shadow); overflow: hidden; display: flex; flex-direction: column; }
    .jwiz-card-body { flex: 1; padding: 32px 36px; }
    @media(max-width: 640px){ .jwiz-card-body { padding: 22px 18px; } }

    /* Step header */
    .jwiz-step-header { display: flex; align-items: center; gap: 12px; margin-bottom: 28px; padding-bottom: 20px; border-bottom: 1px solid var(--neutral-100); }
    .jwiz-step-icon { width: 42px; height: 42px; border-radius: 10px; background: var(--brand-light); display: flex; align-items: center; justify-content: center; color: var(--brand); flex-shrink: 0; }
    .jwiz-step-title { font-size: 18px; font-weight: 800; color: var(--neutral-900); letter-spacing: -0.3px; }
    .jwiz-step-desc { font-size: 13px; color: var(--neutral-400); margin-top: 2px; }

    /* Form fields */
    .jwiz-field { margin-bottom: 22px; }
    .jwiz-label { display: block; font-size: 13px; font-weight: 700; color: var(--neutral-700); margin-bottom: 7px; letter-spacing: 0.01em; }
    .jwiz-label-req { color: var(--danger); margin-left: 2px; }
    .jwiz-input, .jwiz-select, .jwiz-textarea {
        width: 100%;
        padding: 11px 14px;
        border: 1.5px solid var(--neutral-200);
        border-radius: var(--radius);
        font-size: 14px;
        font-family: \'Plus Jakarta Sans\', sans-serif;
        color: var(--neutral-900);
        background: #fff;
        transition: border-color 0.18s, box-shadow 0.18s;
        outline: none;
        appearance: none;
    }
    .jwiz-input::placeholder, .jwiz-textarea::placeholder { color: var(--neutral-400); }
    .jwiz-input:focus, .jwiz-select:focus, .jwiz-textarea:focus {
        border-color: var(--brand);
        box-shadow: 0 0 0 3px rgba(10,101,204,0.1);
    }
    .jwiz-input:disabled, .jwiz-select:disabled { background: var(--neutral-50); color: var(--neutral-400); cursor: not-allowed; }
    .jwiz-select-wrap { position: relative; }
    .jwiz-select-wrap .jwiz-select { padding-right: 36px; }
    .jwiz-select-arrow { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--neutral-400); }
    .jwiz-textarea { resize: vertical; min-height: 88px; line-height: 1.6; }

    .jwiz-input-icon-wrap { position: relative; }
    .jwiz-input-icon-wrap .jwiz-input { padding-right: 40px; }
    .jwiz-input-icon { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--neutral-400); }

    /* Grid */
    .jwiz-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .jwiz-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 18px; }
    @media(max-width: 640px){ .jwiz-grid-2, .jwiz-grid-3 { grid-template-columns: 1fr; gap: 14px; } }

    /* Info banner */
    .jwiz-info-banner { display: flex; align-items: center; justify-content: space-between; background: var(--brand-light); border: 1px solid var(--brand-mid); border-radius: var(--radius); padding: 13px 16px; margin-bottom: 24px; gap: 12px; }
    .jwiz-info-banner-left { display: flex; align-items: center; gap: 11px; }
    .jwiz-info-banner-icon { color: var(--brand); flex-shrink: 0; }
    .jwiz-info-banner-text { font-size: 13px; color: #1a3a6e; font-weight: 600; }
    .jwiz-info-banner-sub { font-size: 12px; color: var(--brand); margin-top: 1px; }
    .jwiz-info-banner-btn { font-size: 12px; font-weight: 700; color: var(--brand); background: #fff; border: 1px solid var(--brand-mid); border-radius: 7px; padding: 6px 13px; cursor: pointer; white-space: nowrap; transition: all 0.15s; }
    .jwiz-info-banner-btn:hover { background: var(--brand); color: #fff; }

    /* Chip selectors */
    .jwiz-chip-group { display: flex; flex-wrap: wrap; gap: 9px; }
    .jwiz-chip { min-height: 38px; padding: 8px 14px; border: 1.5px solid var(--neutral-200); border-radius: 7px; font-size: 13px; font-weight: 700; color: var(--neutral-700); cursor: pointer; background: #fff; transition: all 0.15s; user-select: none; display: inline-flex; align-items: center; gap: 8px; }
    .jwiz-chip:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-light); }
    .jwiz-chip.selected { border-color: var(--brand); color: #fff; background: var(--brand); box-shadow: 0 4px 12px rgba(10,101,204,0.22); }
    .jwiz-chip-check { width: 16px; height: 16px; border-radius: 50%; background: rgba(255,255,255,0.2); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }

    /* Radio cards */
    .jwiz-radio-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media(max-width: 640px){ .jwiz-radio-cards { grid-template-columns: 1fr; } }
    .jwiz-radio-card { border: 1.5px solid var(--neutral-200); border-radius: var(--radius); padding: 15px 16px; cursor: pointer; display: flex; align-items: flex-start; gap: 12px; transition: all 0.18s; background: #fff; }
    .jwiz-radio-card:hover { border-color: var(--brand); background: var(--brand-light); }
    .jwiz-radio-card.selected { border-color: var(--brand); background: var(--brand-light); }
    .jwiz-radio-dot-wrap { width: 18px; height: 18px; border-radius: 50%; border: 2px solid var(--neutral-300); display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px; transition: border-color 0.18s; }
    .jwiz-radio-card.selected .jwiz-radio-dot-wrap { border-color: var(--brand); }
    .jwiz-radio-dot { width: 9px; height: 9px; border-radius: 50%; background: var(--brand); transform: scale(0); transition: transform 0.18s; }
    .jwiz-radio-card.selected .jwiz-radio-dot { transform: scale(1); }
    .jwiz-radio-label { font-size: 13px; font-weight: 700; color: var(--neutral-900); }
    .jwiz-radio-desc { font-size: 12px; color: var(--neutral-400); margin-top: 3px; line-height: 1.5; }

    /* Pay type cards */
    .jwiz-pay-cards { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 22px; }
    @media(max-width: 640px){ .jwiz-pay-cards { grid-template-columns: 1fr; } }
    .jwiz-pay-card { border: 1.5px solid var(--neutral-200); border-radius: var(--radius); padding: 14px 16px; cursor: pointer; text-align: center; transition: all 0.18s; background: #fff; }
    .jwiz-pay-card:hover { border-color: var(--brand); }
    .jwiz-pay-card.selected { border-color: var(--brand); background: var(--brand-light); }
    .jwiz-pay-card-title { font-size: 13px; font-weight: 700; color: var(--neutral-900); }
    .jwiz-pay-card-sub { font-size: 11px; color: var(--neutral-400); margin-top: 2px; }

    /* Experience pills */
    .jwiz-exp-pills { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
    .jwiz-exp-pill { padding: 8px 18px; border-radius: 7px; border: 1.5px solid var(--neutral-200); font-size: 13px; font-weight: 600; color: var(--neutral-500); cursor: pointer; background: #fff; transition: all 0.15s; }
    .jwiz-exp-pill:hover { border-color: var(--brand); color: var(--brand); }
    .jwiz-exp-pill.active { background: var(--brand); border-color: var(--brand); color: #fff; }

    /* Benefits */
    .jwiz-benefit-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    @media(max-width: 640px){ .jwiz-benefit-grid { grid-template-columns: 1fr 1fr; } }
    .jwiz-benefit-item { display: flex; align-items: center; gap: 8px; border: 1.5px solid var(--neutral-200); border-radius: 8px; padding: 9px 12px; cursor: pointer; transition: all 0.15s; background: #fff; }
    .jwiz-benefit-item:hover { border-color: var(--brand-mid); }
    .jwiz-benefit-item.selected { border-color: var(--brand); background: var(--brand-light); }
    .jwiz-benefit-item label { font-size: 12.5px; font-weight: 600; color: var(--neutral-700); cursor: pointer; }
    .jwiz-benefit-item.selected label { color: var(--brand); }
    .jwiz-benefit-item input[type="checkbox"] { accent-color: var(--brand); width: 15px; height: 15px; flex-shrink: 0; }

    /* Call availability */
    .jwiz-call-pills { display: flex; gap: 8px; flex-wrap: wrap; }
    .jwiz-call-pill { padding: 8px 16px; border: 1.5px solid var(--neutral-200); border-radius: 7px; font-size: 13px; font-weight: 600; color: var(--neutral-500); cursor: pointer; background: #fff; transition: all 0.15s; }
    .jwiz-call-pill:hover { border-color: var(--brand); }
    .jwiz-call-pill.active { background: var(--brand); border-color: var(--brand); color: #fff; }

    /* Skills tags */
    .jwiz-skills-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; min-height: 10px; }
    .jwiz-skill-tag { display: inline-flex; align-items: center; gap: 6px; background: var(--brand-light); color: var(--brand); border: 1px solid var(--brand-mid); border-radius: 6px; padding: 5px 11px; font-size: 13px; font-weight: 600; }
    .jwiz-skill-tag-remove { background: none; border: none; cursor: pointer; color: var(--brand); opacity: 0.6; display: flex; align-items: center; padding: 0; transition: opacity 0.15s; }
    .jwiz-skill-tag-remove:hover { opacity: 1; color: var(--danger); }

    /* Dropdown suggest */
    .jwiz-dropdown { position: absolute; z-index: 100; width: 100%; margin-top: 6px; background: #fff; border: 1px solid var(--neutral-200); border-radius: var(--radius); box-shadow: var(--shadow-lg); max-height: 220px; overflow-y: auto; }
    .jwiz-dropdown-item { padding: 11px 14px; cursor: pointer; border-bottom: 1px solid var(--neutral-100); transition: background 0.1s; }
    .jwiz-dropdown-item:last-child { border-bottom: none; }
    .jwiz-dropdown-item:hover, .jwiz-dropdown-item.highlighted { background: var(--brand-light); }
    .jwiz-dropdown-item-title { font-size: 13.5px; font-weight: 600; color: var(--neutral-900); }
    .jwiz-dropdown-item-sub { font-size: 11.5px; color: var(--neutral-400); margin-top: 1px; }

    /* Section divider */
    .jwiz-divider { border: none; border-top: 1px solid var(--neutral-100); margin: 24px 0; }

    /* Address note */
    .jwiz-note { display: inline-flex; align-items: center; gap: 6px; background: var(--neutral-50); border: 1px solid var(--neutral-200); border-radius: 7px; padding: 7px 12px; font-size: 12px; color: var(--neutral-500); margin-top: 8px; }

    /* Salary section */
    .jwiz-salary-box { background: linear-gradient(135deg, #f0f7ff 0%, #e8f2fd 100%); border: 1px solid var(--brand-mid); border-radius: var(--radius-lg); padding: 26px 28px; }
    .jwiz-salary-box-header { display: flex; align-items: center; gap: 10px; margin-bottom: 22px; }
    .jwiz-salary-box-icon { width: 38px; height: 38px; border-radius: 9px; background: var(--brand); display: flex; align-items: center; justify-content: center; color: #fff; flex-shrink: 0; }
    .jwiz-salary-box-title { font-size: 16px; font-weight: 800; color: #0f2a5e; }
    .jwiz-currency-input { position: relative; }
    .jwiz-currency-symbol { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); font-size: 14px; font-weight: 700; color: var(--neutral-500); pointer-events: none; }
    .jwiz-currency-input .jwiz-input { padding-left: 26px; }
    .jwiz-err { font-size: 11.5px; color: var(--danger); margin-top: 5px; font-weight: 600; }

    /* Bonus radios */
    .jwiz-radio-row { display: flex; gap: 24px; }
    .jwiz-radio-option { display: flex; align-items: center; gap: 7px; cursor: pointer; font-size: 14px; font-weight: 600; color: var(--neutral-700); }
    .jwiz-radio-option input { accent-color: var(--brand); width: 16px; height: 16px; }

    /* Quill editor overrides */
    .ql-editor { min-height: 260px; font-size: 14px; line-height: 1.7; color: var(--neutral-700); font-family: \'Plus Jakarta Sans\', sans-serif; }
    .ql-toolbar.ql-snow { border-radius: var(--radius) var(--radius) 0 0; border-color: var(--neutral-200); background: var(--neutral-50); }
    .ql-container.ql-snow { border-radius: 0 0 var(--radius) var(--radius); border-color: var(--neutral-200); background: #fff; }

    /* AI btn */
    .jwiz-ai-btn { display: inline-flex; align-items: center; gap: 7px; padding: 8px 16px; background: linear-gradient(135deg, #f05537, #f05537); color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.18s; box-shadow: 0 3px 10px rgba(79,70,229,0.3); font-family: \'Plus Jakarta Sans\', sans-serif; }
    .jwiz-ai-btn:hover { transform: translateY(-1px); box-shadow: 0 5px 16px rgba(79,70,229,0.4); }
    .jwiz-desc-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }

    /* Review preview */
    .jwiz-preview { border: 1px solid var(--neutral-200); border-radius: var(--radius-lg); overflow: hidden; max-width: 900px; margin: 0 auto; }
    .jwiz-preview-head { padding: 28px 32px; border-bottom: 1px solid var(--neutral-100); }
    .jwiz-preview-job-title { font-size: 22px; font-weight: 800; color: var(--neutral-900); letter-spacing: -0.3px; margin-bottom: 10px; }
    .jwiz-preview-meta { display: flex; flex-wrap: wrap; gap: 14px; }
    .jwiz-preview-meta-item { display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--neutral-500); }
    .jwiz-preview-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px; }
    .jwiz-preview-badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 13px; border-radius: 20px; font-size: 12px; font-weight: 700; }
    .jwiz-preview-badge-blue { background: var(--brand-light); color: var(--brand); border: 1px solid var(--brand-mid); }
    .jwiz-preview-badge-red { background: #fff0f0; color: var(--danger); border: 1px solid #ffc9c9; }
    .jwiz-preview-body { display: grid; grid-template-columns: 1fr 300px; }
    @media(max-width: 700px){ .jwiz-preview-body { grid-template-columns: 1fr; } }
    .jwiz-preview-main { padding: 28px 32px; border-right: 1px solid var(--neutral-100); }
    .jwiz-preview-sidebar { padding: 28px 24px; background: var(--neutral-50); }
    .jwiz-preview-section-title { font-size: 15px; font-weight: 800; color: var(--neutral-900); margin-bottom: 14px; }
    .jwiz-preview-text { font-size: 14px; color: var(--neutral-500); line-height: 1.7; }
    .jwiz-preview-skill-tag { display: inline-flex; padding: 5px 12px; background: #fff; border: 1px solid var(--neutral-200); border-radius: 6px; font-size: 13px; font-weight: 600; color: var(--neutral-700); }
    .jwiz-sidebar-label { font-size: 10.5px; font-weight: 700; letter-spacing: 0.07em; color: var(--neutral-400); text-transform: uppercase; margin-bottom: 14px; }
    .jwiz-overview-item { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 16px; }
    .jwiz-overview-icon { width: 32px; height: 32px; border-radius: 8px; background: #fff; border: 1px solid var(--neutral-200); display: flex; align-items: center; justify-content: center; color: var(--neutral-400); flex-shrink: 0; }
    .jwiz-overview-key { font-size: 11px; color: var(--neutral-400); font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
    .jwiz-overview-val { font-size: 13.5px; font-weight: 700; color: var(--neutral-900); margin-top: 1px; }
    .jwiz-perk-item { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--neutral-700); margin-bottom: 9px; font-weight: 500; }
    .jwiz-perk-check { color: var(--accent); flex-shrink: 0; }
    .jwiz-contact-card { display: flex; align-items: center; gap: 11px; background: #fff; border: 1px solid var(--neutral-200); border-radius: var(--radius); padding: 12px 14px; margin-top: 12px; }
    .jwiz-contact-avatar { width: 38px; height: 38px; border-radius: 50%; background: var(--brand-light); color: var(--brand); font-weight: 800; font-size: 15px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .jwiz-contact-name { font-size: 13px; font-weight: 700; color: var(--neutral-900); }
    .jwiz-contact-detail { font-size: 12px; color: var(--neutral-400); }

    /* Review success header */
    .jwiz-review-hero { text-align: center; padding: 28px 0 20px; }
    .jwiz-review-icon { width: 60px; height: 60px; border-radius: 50%; background: #f0fdf4; border: 1.5px solid #bbf7d0; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; }
    .jwiz-review-title { font-size: 22px; font-weight: 800; color: var(--neutral-900); }
    .jwiz-review-sub { font-size: 14px; color: var(--neutral-400); margin-top: 4px; }

    /* Footer */
    .jwiz-footer { background: #fff; border-top: 1px solid var(--neutral-200); padding: 16px 36px; display: flex; align-items: center; justify-content: space-between; gap: 12px; position: sticky; bottom: 0; z-index: 50; }
    .jwiz-footer-right { display: flex; align-items: center; gap: 10px; margin-left: auto; }
    .jwiz-btn { display: inline-flex; align-items: center; gap: 7px; padding: 10px 22px; border-radius: var(--radius); font-size: 14px; font-weight: 700; cursor: pointer; border: none; transition: all 0.18s; font-family: \'Plus Jakarta Sans\', sans-serif; line-height: 1; }
    .jwiz-btn-outline { background: #fff; border: 1.5px solid var(--neutral-200); color: var(--neutral-700); }
    .jwiz-btn-outline:hover { background: var(--neutral-50); border-color: var(--neutral-300); }
    .jwiz-btn-ghost { background: none; border: none; color: var(--neutral-500); padding: 10px 14px; }
    .jwiz-btn-ghost:hover { color: var(--neutral-900); background: var(--neutral-50); border-radius: var(--radius); }
    .jwiz-btn-primary { background: var(--brand); color: #fff; box-shadow: 0 3px 10px rgba(10,101,204,0.25); }
    .jwiz-btn-primary:hover:not(:disabled) { background: var(--brand-dark); box-shadow: 0 5px 14px rgba(10,101,204,0.35); transform: translateY(-1px); }
    .jwiz-btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
    .jwiz-btn-success { background: #15803d; color: #fff; box-shadow: 0 3px 10px rgba(21,128,61,0.25); }
    .jwiz-btn-success:hover:not(:disabled) { background: #166534; transform: translateY(-1px); }
    .jwiz-btn-success:disabled { opacity: 0.5; cursor: not-allowed; }

    /* Animations */
    @keyframes fadeSlideIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    .jwiz-step-content { animation: fadeSlideIn 0.3s ease-out both; }
    .jwiz-notice { display: flex; gap: 12px; align-items: flex-start; border-radius: 12px; padding: 14px 16px; margin-bottom: 22px; font-size: 14px; line-height: 1.5; }
    .jwiz-notice-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
    .jwiz-notice-warning { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; }
    .jwiz-notice-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
    .jwiz-notice-link { display: inline-block; margin-top: 8px; font-weight: 700; text-decoration: underline; color: inherit; }
    .jwiz-notice-close { border: 0; background: none; font-size: 22px; line-height: 1; cursor: pointer; color: inherit; opacity: .7; }

    /* custom scrollbar */
    .jwiz-dropdown::-webkit-scrollbar { width: 5px; }
    .jwiz-dropdown::-webkit-scrollbar-thumb { background: var(--neutral-200); border-radius: 99px; }

    /* Detect location btn */
    .jwiz-locate-btn { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; color: var(--brand); background: none; border: none; cursor: pointer; padding: 4px 8px; border-radius: 6px; transition: background 0.15s; font-family: \'Plus Jakarta Sans\', sans-serif; }
    .jwiz-locate-btn:hover { background: var(--brand-light); }

    /* Spinner inline */
    .jwiz-spin-sm { display: inline-block; width: 14px; height: 14px; border: 2px solid var(--brand-light); border-top-color: var(--brand); border-radius: 50%; animation: spin 0.7s linear infinite; }

    /* Section box */
    .jwiz-section-box { background: var(--neutral-50); border: 1px solid var(--neutral-200); border-radius: var(--radius-lg); padding: 22px 24px; }
</style>
';
?>

<?php
$isEditMode = isset($isEdit) && $isEdit;
$existingJobData = isset($job) && is_array($job) ? $job : null;
$existingLocations = isset($locations) && is_array($locations) ? $locations : [];
$existingSkills = [];
if (isset($skills) && is_array($skills)) {
    foreach ($skills as $skill) {
        if (is_array($skill)) $existingSkills[] = $skill['name'] ?? '';
        elseif (is_string($skill)) $existingSkills[] = $skill;
    }
}
$existingSkills = array_filter($existingSkills);
$allBenefits = isset($benefits) && is_array($benefits) ? $benefits : [];
$existingBenefits = isset($jobBenefits) && is_array($jobBenefits) ? $jobBenefits : [];
$categoriesList = isset($categories) && is_array($categories) ? $categories : [];
$employerArray = isset($employer) && method_exists($employer, 'toArray') ? $employer->toArray() : [];
$userArray = isset($user) && method_exists($user, 'toArray') ? $user->toArray() : [];
?>

<div x-data="jobPostWizard()" x-init="init()" x-cloak class="jwiz-wrap">

    <!-- Loading Overlay -->
    <div x-show="isLoading" class="jwiz-loader">
        <div class="jwiz-spinner"></div>
        <p class="jwiz-loader-text">Loading workspace...</p>
    </div>

    <div class="jwiz-container" x-show="!isLoading" x-transition.opacity.duration.400ms>
        <?php if (!empty($payPerPost) && !$isEditMode): ?>
        <div style="margin:0 0 16px;padding:12px 16px;border-radius:12px;background:#fffbeb;border:1px solid #fcd34d;color:#78350f;font-size:14px;line-height:1.5">
            <b>₹200 + GST for this job.</b> Your free job / plan credits are used. Fill in the job and submit – you will then pay ₹200 + GST (₹236) for this one job and it goes for review.
            Posting many jobs? <a href="/employer/subscription/plans?upgrade=1&amp;feature=job_posting&amp;hide_free=1" style="color:#b45309;font-weight:700;text-decoration:underline">Choose a plan</a>.
        </div>
        <?php endif; ?>

        <!-- Language/Country Modal -->
        <div x-show="showLanguageModal" x-cloak class="jwiz-modal-bg">
            <div class="jwiz-modal">
                <div class="jwiz-modal-header">
                    <h3 class="jwiz-modal-title">Posting Settings</h3>
                    <button @click="showLanguageModal=false" class="jwiz-modal-close">&times;</button>
                </div>
                <div class="jwiz-field">
                    <label class="jwiz-label">Posting Country</label>
                    <div class="jwiz-select-wrap">
                        <select x-model="formData.location.country" @change="onCountryChange" class="jwiz-select">
                            <option value="">Select Country</option>
                            <template x-for="c in countries" :key="c.name">
                                <option :value="c.name" x-text="c.name"></option>
                            </template>
                        </select>
                        <span class="jwiz-select-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span>
                    </div>
                </div>
                <div class="jwiz-grid-2">
                    <div class="jwiz-field">
                        <label class="jwiz-label">State</label>
                        <div class="jwiz-select-wrap">
                            <select x-model="formData.location.state" @change="onStateChange" :disabled="statesLoading || !formData.location.country" class="jwiz-select">
                                <option value="">Select State</option>
                                <template x-for="s in states" :key="s"><option :value="s" x-text="s"></option></template>
                            </select>
                            <span class="jwiz-select-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span>
                        </div>
                    </div>
                    <div class="jwiz-field">
                        <label class="jwiz-label">City</label>
                        <div class="jwiz-select-wrap">
                            <select x-model="formData.location.city" :disabled="citiesLoading || !formData.location.state" class="jwiz-select">
                                <option value="">Select City</option>
                                <template x-for="ct in cities" :key="ct"><option :value="ct" x-text="ct"></option></template>
                            </select>
                            <span class="jwiz-select-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span>
                        </div>
                    </div>
                </div>
                <div class="jwiz-field">
                    <label class="jwiz-label">Language</label>
                    <div class="jwiz-select-wrap">
                        <select x-model="formData.language" class="jwiz-select">
                            <template x-for="lang in languages" :key="lang"><option :value="lang" x-text="lang"></option></template>
                        </select>
                        <span class="jwiz-select-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span>
                    </div>
                </div>
                <div class="jwiz-modal-footer">
                    <button @click="showLanguageModal=false" class="jwiz-btn jwiz-btn-outline">Cancel</button>
                    <button @click="showLanguageModal=false" class="jwiz-btn jwiz-btn-primary">Save Settings</button>
                </div>
            </div>
        </div>

        <!-- Page Header -->
        <div class="jwiz-page-header">
            <div>
                <h1 class="jwiz-page-title"><?= $isEditMode ? 'Edit Job Posting' : 'Post a New Job' ?></h1>
                <p class="jwiz-page-sub">Reach thousands of verified candidates across the platform</p>
            </div>
            <div class="jwiz-badge-premium">
                <span class="jwiz-badge-dot"></span>
                Premium Listing
            </div>
        </div>

        <!-- Stepper -->
        <div class="jwiz-stepper-card">
            <div class="jwiz-stepper-track" style="padding: 10px 20px;">
                <div class="jwiz-stepper-line"></div>
                <div class="jwiz-stepper-line-fill" :style="'width: ' + (currentStep / (totalSteps - 1) * 100) + '%'"></div>
                <template x-for="(step, index) in totalSteps" :key="index">
                    <div class="jwiz-step-item" @click="if(index < currentStep) currentStep = index">
                        <div class="jwiz-step-circle"
                             :class="{ 'active': index === currentStep, 'done': index < currentStep }">
                            <template x-if="index < currentStep">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="index >= currentStep">
                                <span x-text="index + 1"></span>
                            </template>
                        </div>
                        <span class="jwiz-step-label" :class="{ 'active': index <= currentStep, 'done': index < currentStep }" x-text="stepTitles[index]"></span>
                    </div>
                </template>
            </div>
        </div>

        <!-- Form Card -->
        <div class="jwiz-card">
            <div class="jwiz-card-body">

                <div x-show="notice.text" x-cloak class="jwiz-notice" :class="'jwiz-notice-' + notice.type" role="alert" aria-live="assertive">
                    <div style="flex:1;min-width:0">
                        <div style="font-weight:700" x-text="notice.text"></div>
                        <template x-if="notice.errors && Object.keys(notice.errors).length > 1">
                            <ul style="margin:6px 0 0;padding-left:18px">
                                <template x-for="msg in Object.values(notice.errors)" :key="msg"><li x-text="msg"></li></template>
                            </ul>
                        </template>
                        <a x-show="notice.link" :href="notice.link" class="jwiz-notice-link" x-text="notice.linkText || 'Continue'"></a>
                    </div>
                    <button type="button" @click="notice.text = ''" class="jwiz-notice-close" aria-label="Dismiss">&times;</button>
                </div>

                <!-- ===================== STEP 0: JOB BASICS ===================== -->
                <div x-show="currentStep === 0" class="jwiz-step-content">
                    <div class="jwiz-step-header">
                        <div class="jwiz-step-icon">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <div class="jwiz-step-title">Job Basics</div>
                            <div class="jwiz-step-desc">Start with the essentials — title, type and location</div>
                        </div>
                    </div>

                    <!-- Posting location banner -->
                    <div class="jwiz-info-banner">
                        <div class="jwiz-info-banner-left">
                            <span class="jwiz-info-banner-icon">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                            </span>
                            <div>
                                <div class="jwiz-info-banner-text">Posting for <strong x-text="formData.location.country || 'Global'"></strong></div>
                                <div class="jwiz-info-banner-sub">Language: <span x-text="formData.language"></span></div>
                            </div>
                        </div>
                        <button @click="showLanguageModal = true" class="jwiz-info-banner-btn">Change</button>
                    </div>

                    <!-- Job Title -->
                    <div class="jwiz-field" style="position:relative;">
                        <label class="jwiz-label">Job Title <span class="jwiz-label-req">*</span></label>
                        <div class="jwiz-input-icon-wrap" style="position:relative;">
                            <input type="text"
                                   x-model="formData.title"
                                   @input="searchJobTitles($event.target.value)"
                                   @keydown="handleJobTitleKeyDown($event)"
                                   @focus="if(formData.title && formData.title.length >= 2) searchJobTitles(formData.title)"
                                   @blur="setTimeout(() => jobTitleSuggestions.show = false, 200)"
                                   placeholder="e.g. Senior Product Designer"
                                   class="jwiz-input">
                            <span class="jwiz-input-icon">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <div x-show="jobTitleSuggestions.show && jobTitleSuggestions.list.length > 0" x-cloak class="jwiz-dropdown">
                                <template x-for="(suggestion, index) in jobTitleSuggestions.list" :key="suggestion.id">
                                    <div @click="selectJobTitleSuggestion(suggestion)"
                                         @mouseenter="jobTitleSuggestions.selectedIndex = index"
                                         class="jwiz-dropdown-item"
                                         :class="jobTitleSuggestions.selectedIndex === index ? 'highlighted' : ''">
                                        <div class="jwiz-dropdown-item-title" x-text="suggestion.title"></div>
                                        <div class="jwiz-dropdown-item-sub" x-show="suggestion.category" x-text="suggestion.category"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="jwiz-field">
                        <label class="jwiz-label">Industry / Category <span class="jwiz-label-req">*</span></label>
                        <div class="jwiz-select-wrap">
                            <select x-model="formData.category" @change="refreshSkillContextSuggestions()" class="jwiz-select">
                                <option value="">Select Industry</option>
                                <template x-for="cat in categories" :key="cat.value">
                                    <option :value="cat.value" x-text="cat.label"></option>
                                </template>
                            </select>
                            <span class="jwiz-select-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span>
                        </div>
                    </div>

                    <!-- Employment Type -->
                    <div class="jwiz-field">
                        <label class="jwiz-label">Employment Type <span class="jwiz-label-req">*</span></label>
                        <div class="jwiz-chip-group">
                            <template x-for="type in jobTypes" :key="type.value">
                                <div @click="formData.employment_type = type.value"
                                     class="jwiz-chip"
                                     role="button"
                                     :aria-pressed="formData.employment_type === type.value"
                                     :class="formData.employment_type === type.value ? 'selected' : ''">
                                    <span x-text="type.label"></span>
                                    <span x-show="formData.employment_type === type.value" class="jwiz-chip-check" x-cloak>
                                        <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="jwiz-field">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:7px;">
                            <label class="jwiz-label" style="margin-bottom:0;">Job Location <span class="jwiz-label-req">*</span></label>
                            <button type="button" @click="getCurrentLocation()" class="jwiz-locate-btn">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Detect Location
                            </button>
                        </div>
                        <div class="jwiz-grid-3">
                            <div class="jwiz-select-wrap">
                                <select x-model="formData.location.country" @change="onCountryChange" class="jwiz-select">
                                    <option value="">Country</option>
                                    <template x-for="c in countries" :key="c.name"><option :value="c.name" x-text="c.name"></option></template>
                                </select>
                                <span class="jwiz-select-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span>
                            </div>
                            <div class="jwiz-select-wrap">
                                <select x-model="formData.location.state" @change="onStateChange" :disabled="statesLoading || !formData.location.country" class="jwiz-select">
                                    <option value="">State</option>
                                    <template x-for="s in states" :key="s"><option :value="s" x-text="s"></option></template>
                                </select>
                                <span class="jwiz-select-arrow">
                                    <span x-show="statesLoading" class="jwiz-spin-sm"></span>
                                    <svg x-show="!statesLoading" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </div>
                            <div class="jwiz-select-wrap">
                                <select x-model="formData.location.city" :disabled="citiesLoading || !formData.location.state" class="jwiz-select">
                                    <option value="">City</option>
                                    <template x-for="ct in cities" :key="ct"><option :value="ct" x-text="ct"></option></template>
                                </select>
                                <span class="jwiz-select-arrow">
                                    <span x-show="citiesLoading" class="jwiz-spin-sm"></span>
                                    <svg x-show="!citiesLoading" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Number of Openings -->
                    <div class="jwiz-field">
                        <label class="jwiz-label">Number of Openings <span class="jwiz-label-req">*</span></label>
                        <input type="number" x-model="formData.vacancies" min="1" placeholder="e.g. 2" class="jwiz-input" style="max-width:200px;">
                    </div>

                    <!-- Workplace Type -->
                    <div class="jwiz-field">
                        <label class="jwiz-label">Workplace Setting <span class="jwiz-label-req">*</span></label>
                        <div class="jwiz-radio-cards">
                            <div @click="formData.work_address_type = 'specific'"
                                 class="jwiz-radio-card"
                                 :class="formData.work_address_type === 'specific' ? 'selected' : ''">
                                <div class="jwiz-radio-dot-wrap">
                                    <div class="jwiz-radio-dot"></div>
                                </div>
                                <div>
                                    <div class="jwiz-radio-label">On-site / Office</div>
                                    <div class="jwiz-radio-desc">Employees work from a specific office or location</div>
                                </div>
                            </div>
                            <div @click="formData.work_address_type = 'none'"
                                 class="jwiz-radio-card"
                                 :class="formData.work_address_type === 'none' ? 'selected' : ''">
                                <div class="jwiz-radio-dot-wrap">
                                    <div class="jwiz-radio-dot"></div>
                                </div>
                                <div>
                                    <div class="jwiz-radio-label">Remote / Field</div>
                                    <div class="jwiz-radio-desc">No fixed office address required for this role</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Street Address -->
                    <div x-show="formData.work_address_type === 'specific'"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="jwiz-field">
                        <label class="jwiz-label">Street Address <span class="jwiz-label-req">*</span></label>
                        <textarea x-model="formData.job_address" rows="3"
                                  placeholder="e.g. 123 Business Park, Building A, Floor 4"
                                  class="jwiz-textarea"></textarea>
                        <div class="jwiz-note">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Address is only visible to registered candidates
                        </div>
                    </div>
                </div>

                <!-- ===================== STEP 1: JOB DETAILS ===================== -->
                <div x-show="currentStep === 1" class="jwiz-step-content">
                    <div class="jwiz-step-header">
                        <div class="jwiz-step-icon">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <div class="jwiz-step-title">Job Details</div>
                            <div class="jwiz-step-desc">Industry, experience, timings and scheduling preferences</div>
                        </div>
                    </div>

                    <!-- Openings & Language -->
                    <div class="jwiz-grid-2">
                        <div class="jwiz-field">
                            <label class="jwiz-label">Job Language</label>
                            <div class="jwiz-select-wrap">
                                <select x-model="formData.language" class="jwiz-select">
                                    <template x-for="lang in languages" :key="lang"><option :value="lang" x-text="lang"></option></template>
                                </select>
                                <span class="jwiz-select-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span>
                            </div>
                        </div>
                    </div>

                    <!-- Experience -->
                    <div class="jwiz-field">
                        <div class="jwiz-section-box">
                            <label class="jwiz-label" style="margin-bottom:14px;">Experience Required <span class="jwiz-label-req">*</span></label>
                            <div class="jwiz-exp-pills">
                                <button type="button" @click="setExperienceType('any')" class="jwiz-exp-pill" :class="formData.experience_type === 'any' ? 'active' : ''">No Preference</button>
                                <button type="button" @click="setExperienceType('fresher')" class="jwiz-exp-pill" :class="formData.experience_type === 'fresher' ? 'active' : ''">Fresher Only</button>
                                <button type="button" @click="setExperienceType('experienced')" class="jwiz-exp-pill" :class="formData.experience_type === 'experienced' ? 'active' : ''">Experienced Only</button>
                            </div>
                            <div x-show="formData.experience_type === 'experienced'" x-transition class="jwiz-grid-2" style="max-width: 340px;">
                                <div>
                                    <label class="jwiz-label" style="font-size:11px; color:var(--neutral-400);">Min Years</label>
                                    <div class="jwiz-select-wrap">
                                        <select x-model="formData.min_experience" @change="if(formData.max_experience && parseInt(formData.max_experience) < parseInt(formData.min_experience)) formData.max_experience = ''" class="jwiz-select">
                                            <option value="">Min</option>
                                            <option value="0">0</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5+</option>
                                        </select>
                                        <span class="jwiz-select-arrow"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span>
                                    </div>
                                </div>
                                <div>
                                    <label class="jwiz-label" style="font-size:11px; color:var(--neutral-400);">Max Years</label>
                                    <div class="jwiz-select-wrap">
                                        <select x-model="formData.max_experience" class="jwiz-select">
                                            <option value="">Max</option>
                                            <template x-for="n in [1,2,3,4,5,6,7,8,9,10]" :key="n">
                                                <option :value="n" :disabled="formData.min_experience && n < parseInt(formData.min_experience)" x-text="n === 10 ? '10+' : n"></option>
                                            </template>
                                        </select>
                                        <span class="jwiz-select-arrow"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hiring Urgency & Job Timings -->
                    <div class="jwiz-grid-2">
                        <div class="jwiz-field">
                            <label class="jwiz-label">Hiring Urgency</label>
                            <div class="jwiz-select-wrap">
                                <select x-model="formData.hiring_urgency" class="jwiz-select">
                                    <option value="immediate">Immediate Joining</option>
                                    <option value="15_days">Within 15 Days</option>
                                    <option value="30_days">Within 30 Days</option>
                                </select>
                                <span class="jwiz-select-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span>
                            </div>
                        </div>
                        <div class="jwiz-field">
                            <label class="jwiz-label">Job Timings</label>
                            <input type="text" x-model="formData.job_timings" placeholder="e.g. 9:00 AM – 6:00 PM" class="jwiz-input">
                        </div>
                    </div>

                    <!-- Interview Timings -->
                    <div class="jwiz-field">
                        <label class="jwiz-label">Interview Timings</label>
                        <input type="text" x-model="formData.interview_timings" placeholder="e.g. 11:00 AM – 4:00 PM (Mon–Fri)" class="jwiz-input">
                    </div>
                </div>

                <!-- ===================== STEP 2: COMPENSATION ===================== -->
                <div x-show="currentStep === 2" class="jwiz-step-content">
                    <div class="jwiz-step-header">
                        <div class="jwiz-step-icon">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <div class="jwiz-step-title">Pay & Benefits</div>
                            <div class="jwiz-step-desc">Salary structure, perks and contact availability</div>
                        </div>
                    </div>

                    <div class="jwiz-salary-box" style="margin-bottom:24px;">
                        <div class="jwiz-salary-box-header">
                            <div class="jwiz-salary-box-icon">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="jwiz-salary-box-title">Salary Structure</span>
                        </div>

                        <div class="jwiz-pay-cards">
                            <div @click="formData.pay_type = 'range'" class="jwiz-pay-card" :class="formData.pay_type === 'range' ? 'selected' : ''">
                                <div class="jwiz-pay-card-title">Range</div>
                                <div class="jwiz-pay-card-sub">Min – Max</div>
                            </div>
                            <div @click="formData.pay_type = 'fixed'" class="jwiz-pay-card" :class="formData.pay_type === 'fixed' ? 'selected' : ''">
                                <div class="jwiz-pay-card-title">Fixed Amount</div>
                                <div class="jwiz-pay-card-sub">Exact salary</div>
                            </div>
                            <div @click="formData.pay_type = 'negotiable'" class="jwiz-pay-card" :class="formData.pay_type === 'negotiable' ? 'selected' : ''">
                                <div class="jwiz-pay-card-title">Negotiable</div>
                                <div class="jwiz-pay-card-sub">To be discussed</div>
                            </div>
                        </div>

                        <div x-show="formData.pay_type !== 'negotiable'" x-transition class="jwiz-grid-3">
                            <div x-show="formData.pay_type === 'range'">
                                <label class="jwiz-label">Minimum <span style="font-weight:400; color:var(--neutral-400); font-size:11px;">(Actual salary only)</span></label>
                                <div class="jwiz-currency-input">
                                    <span class="jwiz-currency-symbol" x-text="currencySymbol"></span>
                                    <input type="number" x-model="formData.pay_min" class="jwiz-input">
                                </div>
                            </div>
                            <div :class="formData.pay_type === 'fixed' ? 'col-span-2' : ''">
                                <label class="jwiz-label" x-text="formData.pay_type === 'range' ? 'Maximum' : 'Amount'"></label>
                                <div class="jwiz-currency-input">
                                    <span class="jwiz-currency-symbol" x-text="currencySymbol"></span>
                                    <input type="number" x-model="formData.pay_amount" class="jwiz-input"
                                           :style="formData.pay_type === 'range' && formData.pay_min && formData.pay_amount && Number(formData.pay_amount) < Number(formData.pay_min) ? 'border-color: var(--danger);' : ''">
                                </div>
                                <div x-show="formData.pay_type === 'range' && formData.pay_min && formData.pay_amount && Number(formData.pay_amount) < Number(formData.pay_min)" class="jwiz-err">Max salary cannot be less than min salary</div>
                            </div>
                            <div>
                                <label class="jwiz-label">Frequency</label>
                                <div class="jwiz-select-wrap">
                                    <select x-model="formData.pay_frequency" class="jwiz-select">
                                        <option value="monthly">Monthly</option>
                                        <option value="yearly">Yearly</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="hourly">Hourly</option>
                                    </select>
                                    <span class="jwiz-select-arrow"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bonus -->
                    <div class="jwiz-field">
                        <label class="jwiz-label">Do you offer bonus in addition to monthly salary?</label>
                        <div class="jwiz-radio-row">
                            <label class="jwiz-radio-option">
                                <input type="radio" x-model="formData.offers_bonus" value="yes"> Yes
                            </label>
                            <label class="jwiz-radio-option">
                                <input type="radio" x-model="formData.offers_bonus" value="no"> No
                            </label>
                        </div>
                    </div>

                    <!-- Benefits -->
                    <div class="jwiz-field" x-show="availableBenefits.length > 0">
                        <label class="jwiz-label">Benefits & Perks</label>
                        <div class="jwiz-benefit-grid">
                            <template x-for="benefit in availableBenefits" :key="benefit.id">
                                <label class="jwiz-benefit-item" :class="formData.benefit_ids.includes(benefit.id) ? 'selected' : ''">
                                    <input type="checkbox" :value="benefit.id" x-model="formData.benefit_ids">
                                    <span x-text="benefit.name"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <!-- Call Availability -->
                    <div class="jwiz-field">
                        <label class="jwiz-label">Candidates can call me</label>
                        <div class="jwiz-call-pills">
                            <button type="button" @click="formData.call_availability = 'everyday'" class="jwiz-call-pill" :class="formData.call_availability === 'everyday' ? 'active' : ''">Everyday</button>
                            <button type="button" @click="formData.call_availability = 'weekdays'" class="jwiz-call-pill" :class="formData.call_availability === 'weekdays' ? 'active' : ''">Mon – Fri</button>
                            <button type="button" @click="formData.call_availability = 'weekdays_saturday'" class="jwiz-call-pill" :class="formData.call_availability === 'weekdays_saturday' ? 'active' : ''">Mon – Sat</button>
                            <button type="button" @click="formData.call_availability = 'custom'" class="jwiz-call-pill" :class="formData.call_availability === 'custom' ? 'active' : ''">Custom</button>
                        </div>
                    </div>

                    <hr class="jwiz-divider">

                    <!-- Skills -->
                    <div class="jwiz-field">
                        <label class="jwiz-label">Required Skills</label>
                        <div style="position:relative;">
                            <input type="text"
                                   x-ref="skillInput"
                                   x-model="skillQuery"
                                   @input="onSkillInput($event)"
                                   @keydown="handleSkillKeyDown($event)"
                                   @keydown.enter.prevent="addSkillFromQuery()"
                                   @focus="if((skillQuery || '').length >= 1) searchSkills(skillQuery); skillSuggestions.show = true"
                                   @blur="setTimeout(() => skillSuggestions.show = false, 150)"
                                   placeholder="Type a skill and press Enter (e.g. Java, Photoshop)"
                                   class="jwiz-input">
                            <div x-show="skillSuggestions.show && skillSuggestions.list.length > 0" x-cloak class="jwiz-dropdown">
                                <template x-for="(suggestion, index) in skillSuggestions.list" :key="suggestion.id || suggestion.name">
                                    <div @click="selectSkillSuggestion(suggestion)"
                                         @mouseenter="skillSuggestions.selectedIndex = index"
                                         class="jwiz-dropdown-item"
                                         :class="skillSuggestions.selectedIndex === index ? 'highlighted' : ''">
                                        <div class="jwiz-dropdown-item-title" x-text="suggestion.name"></div>
                                        <div class="jwiz-dropdown-item-sub" x-show="suggestion.usage_count">Popular for this role</div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="jwiz-skills-tags">
                            <template x-for="(skill, index) in formData.skills" :key="index">
                                <span class="jwiz-skill-tag">
                                    <span x-text="typeof skill === 'object' ? skill.name : skill"></span>
                                    <button type="button" @click="removeSkill(index)" class="jwiz-skill-tag-remove">
                                        <svg width="13" height="13" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                    </button>
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- ===================== STEP 3: DESCRIPTION ===================== -->
                <div x-show="currentStep === 3" class="jwiz-step-content">
                    <div class="jwiz-step-header">
                        <div class="jwiz-step-icon">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <div class="jwiz-step-title">Job Description</div>
                            <div class="jwiz-step-desc">Describe the role, responsibilities and qualifications clearly</div>
                        </div>
                    </div>

                    <div class="jwiz-field">
                        <div class="jwiz-desc-header">
                            <label class="jwiz-label" style="margin-bottom:0;">Description <span class="jwiz-label-req">*</span></label>
                            <button type="button" @click="generateJD" class="jwiz-ai-btn">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                AI Generate
                            </button>
                        </div>
                        <div id="job-description-editor"></div>
                    </div>

                    <div class="jwiz-field">
                        <label class="jwiz-label">Education & Qualifications</label>
                        <div x-data="tagInput({ 
                            initialTags: formData.qualifications,
                            suggestions: ['B.Tech', 'M.Tech', 'BCA', 'MCA', 'B.Sc', 'M.Sc', 'B.Com', 'M.Com', 'MBA', 'Diploma', '10th Pass', '12th Pass', 'Any Graduate', 'Post Graduate', 'Ph.D']
                        })" x-init="$watch('tags', value => formData.qualifications = value)" class="relative">
                            <div @click="$refs.input.focus()" class="form-input w-full px-4 py-3 rounded-xl border-gray-300 focus-within:border-primary focus-within:ring focus-within:ring-primary-100 focus-within:ring-opacity-50 flex flex-wrap items-center gap-2">
                                <template x-for="(tag, index) in tags" :key="index">
                                    <div class="bg-primary-50 text-primary-900 text-sm font-medium px-2.5 py-1 rounded-full flex items-center gap-2">
                                        <span x-text="tag"></span>
                                        <button @click.stop="removeTag(index)" class="text-primary hover:text-primary-600">&times;</button>
                                    </div>
                                </template>
                                <input type="text" x-ref="input" x-model="newTag" @keydown.enter.prevent="addTag()" @keydown.backspace="if (newTag === '') removeLastTag()" class="flex-1 bg-transparent border-none focus:ring-0 p-0" placeholder="Add a qualification and press Enter">
                            </div>
                            
                            <!-- Suggestions -->
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="text-xs text-gray-500 w-full mb-1 font-semibold">Suggestions:</span>
                                <template x-for="s in suggestions" :key="s">
                                    <button type="button" 
                                            @click="newTag = s; addTag()" 
                                            x-show="!tags.includes(s)"
                                            class="px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-medium text-gray-600 hover:border-primary hover:text-primary transition-colors"
                                            x-text="s"></button>
                                </template>
                            </div>
                        </div>
                        <div class="jwiz-note">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Add each qualification and press Enter. Shown to candidates in the job preview.
                        </div>
                    </div>
                </div>

                <!-- ===================== STEP 4: REVIEW ===================== -->
                <div x-show="currentStep === 4" class="jwiz-step-content">
                    <div class="jwiz-review-hero">
                        <div class="jwiz-review-icon">
                            <svg width="28" height="28" fill="none" stroke="#22c55e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="jwiz-review-title">Preview & Publish</div>
                        <div class="jwiz-review-sub">This is exactly how your job post will appear to candidates</div>
                    </div>

                    <div class="jwiz-preview">
                        <!-- Job Head -->
                        <div class="jwiz-preview-head">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
                                <div>
                                    <div class="jwiz-preview-job-title" x-text="formData.title"></div>
                                    <div class="jwiz-preview-meta">
                                        <span class="jwiz-preview-meta-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            <span x-text="formData.company_name"></span>
                                        </span>
                                        <span class="jwiz-preview-meta-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span x-text="getLocationDisplay()"></span>
                                        </span>
                                        <span class="jwiz-preview-meta-item">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Posted Today
                                        </span>
                                    </div>
                                </div>
                                <div style="display:flex; flex-direction:column; gap:7px; align-items:flex-end;">
                                    <span class="jwiz-preview-badge jwiz-preview-badge-blue" x-text="getJobTypeLabel()"></span>
                                    <span x-show="formData.hiring_urgency === 'immediate'" class="jwiz-preview-badge jwiz-preview-badge-red">
                                        <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Urgent Hiring
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="jwiz-preview-body">
                            <!-- Main -->
                            <div class="jwiz-preview-main">
                                <div style="margin-bottom:28px;">
                                    <div class="jwiz-preview-section-title">Job Description</div>
                                    <div class="jwiz-preview-text" x-html="formData.description || '<em style=\'color:#94a3b8\'>No description provided.</em>'"></div>
                                </div>
                                <div x-show="formData.qualifications.length > 0" style="margin-bottom:28px;">
                                    <div class="jwiz-preview-section-title">Education & Qualifications</div>
                                    <ul style="padding-left:18px; margin:0;">
                                        <template x-for="(q, idx) in formData.qualifications" :key="idx">
                                            <li class="jwiz-preview-text" style="margin-bottom:5px;" x-text="q"></li>
                                        </template>
                                    </ul>
                                </div>
                                <div x-show="formData.skills.length > 0">
                                    <div class="jwiz-preview-section-title">Skills Required</div>
                                    <div style="display:flex; flex-wrap:wrap; gap:8px;">
                                        <template x-for="skill in formData.skills" :key="typeof skill === 'object' ? skill.name : skill">
                                            <span class="jwiz-preview-skill-tag" x-text="typeof skill === 'object' ? skill.name : skill"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar -->
                            <div class="jwiz-preview-sidebar">
                                <div class="jwiz-sidebar-label">Job Overview</div>
                                <div class="jwiz-overview-item">
                                    <div class="jwiz-overview-icon"><svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                                    <div><div class="jwiz-overview-key">Salary</div><div class="jwiz-overview-val" x-text="getPayDisplay()"></div></div>
                                </div>
                                <div class="jwiz-overview-item" x-show="formData.experience_type">
                                    <div class="jwiz-overview-icon"><svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></div>
                                    <div>
                                        <div class="jwiz-overview-key">Experience</div>
                                        <div class="jwiz-overview-val">
                                            <span x-show="formData.experience_type === 'any'">No Preference</span>
                                            <span x-show="formData.experience_type === 'fresher'">Fresher Only</span>
                                            <span x-show="formData.experience_type === 'experienced'"><span x-text="formData.min_experience"></span>–<span x-text="formData.max_experience"></span> Yrs</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="jwiz-overview-item">
                                    <div class="jwiz-overview-icon"><svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                                    <div><div class="jwiz-overview-key">Openings</div><div class="jwiz-overview-val" x-text="formData.vacancies"></div></div>
                                </div>
                                <div class="jwiz-overview-item" x-show="formData.job_timings">
                                    <div class="jwiz-overview-icon"><svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                                    <div><div class="jwiz-overview-key">Job Timings</div><div class="jwiz-overview-val" x-text="formData.job_timings"></div></div>
                                </div>
                                <div class="jwiz-overview-item" x-show="formData.interview_timings">
                                    <div class="jwiz-overview-icon"><svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                                    <div><div class="jwiz-overview-key">Interview</div><div class="jwiz-overview-val" x-text="formData.interview_timings"></div></div>
                                </div>
                                <div class="jwiz-overview-item" x-show="formData.job_address">
                                    <div class="jwiz-overview-icon"><svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                                    <div>
                                        <div class="jwiz-overview-key">Address <span style="font-weight:400; font-size:10px;">(Registered candidates only)</span></div>
                                        <div class="jwiz-overview-val" style="filter:blur(2px); cursor:pointer; transition:filter 0.2s;" onmouseover="this.style.filter='none'" onmouseout="this.style.filter='blur(2px)'" x-text="formData.job_address"></div>
                                    </div>
                                </div>

                                <div x-show="selectedBenefits.length > 0" style="margin-top:20px;">
                                    <div class="jwiz-sidebar-label">Perks & Benefits</div>
                                    <template x-for="benefitId in selectedBenefits" :key="benefitId">
                                        <div class="jwiz-perk-item">
                                            <svg class="jwiz-perk-check" width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span x-text="availableBenefits.find(b => b.id == benefitId)?.name || 'Benefit'"></span>
                                        </div>
                                    </template>
                                </div>

                                <div style="margin-top:20px;">
                                    <div class="jwiz-sidebar-label">Contact</div>
                                    <div class="jwiz-contact-card">
                                        <div class="jwiz-contact-avatar" x-text="(formData.contact_person || 'HR').charAt(0)"></div>
                                        <div>
                                            <div class="jwiz-contact-name" x-text="formData.contact_person"></div>
                                            <div class="jwiz-contact-detail" x-text="formData.email"></div>
                                            <div class="jwiz-contact-detail" x-show="formData.phone" x-text="formData.phone"></div>
                                        </div>
                                    </div>
                                    <div x-show="formData.call_availability" style="display:flex; align-items:center; gap:6px; font-size:12px; color:var(--neutral-400); margin-top:8px;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        <span x-text="'Call: ' + (formData.call_availability === 'everyday' ? 'Everyday' : (formData.call_availability === 'weekdays' ? 'Mon–Fri' : (formData.call_availability === 'weekdays_saturday' ? 'Mon–Sat' : 'Custom')))"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- end card body -->

            <!-- Footer -->
            <div class="jwiz-footer">
                <button @click="goBack()"
                        x-show="currentStep > 0"
                        class="jwiz-btn jwiz-btn-outline">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    Back
                </button>
                <div x-show="currentStep === 0" style="flex:1;"></div>

                <div class="jwiz-footer-right">
                    <?php if (!isset($isEdit) || !$isEdit): ?>
                    <button @click="saveDraft()"
                            x-show="formData.title"
                            :disabled="isSubmitting"
                            class="jwiz-btn jwiz-btn-ghost">
                        <span x-text="isSavingDraft ? 'Saving…' : 'Save Draft'"></span>
                    </button>
                    <?php endif; ?>

                    <button @click="goNext()"
                            x-show="currentStep < totalSteps - 1"
                            :disabled="!canProceed()"
                            class="jwiz-btn jwiz-btn-primary">
                        Continue
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    <button @click="submitJob()"
                            x-show="currentStep === totalSteps - 1"
                            :disabled="isSubmitting || !canProceed()"
                            class="jwiz-btn jwiz-btn-success">
                        <span x-show="!isSubmitting" style="display:flex; align-items:center; gap:7px;">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Publish Job
                        </span>
                        <span x-show="isSubmitting" style="display:flex; align-items:center; gap:7px;">
                            <span class="jwiz-spin-sm" style="border-color:rgba(255,255,255,0.3); border-top-color:#fff;"></span>
                            Publishing...
                        </span>
                    </button>
                </div>
            </div>
        </div><!-- end card -->
    </div><!-- end container -->
</div>

<?php
ob_start();
?>
<script>
window.quillEditor = null;
const isEditMode = <?= json_encode($isEdit ?? false) ?>;
const existingJobData = <?= json_encode($job ?? null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
const existingLocations = <?= json_encode($locations ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
const existingSkills = <?= json_encode($existingSkills ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
const allBenefits = <?= json_encode($benefits ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
const existingBenefits = <?= json_encode($jobBenefits ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
const employerData = <?= json_encode($employer ?? null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
const userData = <?= json_encode($user ?? null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;

document.addEventListener('alpine:init', () => {
    Alpine.data('tagInput', (config) => ({
        tags: config.initialTags || [],
        suggestions: config.suggestions || [],
        newTag: '',
        addTag() {
            const val = this.newTag.trim();
            if (val && !this.tags.includes(val)) {
                this.tags.push(val);
                this.newTag = '';
            }
        },
        removeTag(index) {
            this.tags.splice(index, 1);
        },
        removeLastTag() {
            if (this.tags.length > 0) {
                this.tags.pop();
            }
        }
    }));

    Alpine.data('jobPostWizard', () => {
    const job = existingJobData || {};
    const parseJsonArray = (value) => {
        if (Array.isArray(value)) return value;
        if (typeof value !== 'string' || value.trim() === '') return [];
        try {
            const parsed = JSON.parse(value);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    };
    const normalizeLocation = (loc) => {
        if (typeof loc === 'string') {
            const parts = loc.split(',').map(part => part.trim()).filter(Boolean);
            return {
                city: parts[0] || '',
                state: parts[1] || '',
                country: parts[2] || ''
            };
        }
        if (!loc || typeof loc !== 'object') return {};
        return {
            country: loc.country || loc.country_name || '',
            state: loc.state || loc.state_name || '',
            city: loc.city || loc.city_name || '',
            latitude: loc.latitude || loc.lat || null,
            longitude: loc.longitude || loc.lng || null
        };
    };
    const normalizeSkills = (items) => (Array.isArray(items) ? items : [])
        .map(skill => {
            if (typeof skill === 'string') return skill;
            if (skill && typeof skill === 'object') return skill.name || skill.skill_name || '';
            return '';
        })
        .map(skill => String(skill).trim())
        .filter(Boolean);
    const locs = Array.isArray(existingLocations) ? existingLocations.map(normalizeLocation).filter(loc => loc.country || loc.state || loc.city) : [];
    const jobLocations = parseJsonArray(job.locations).map(normalizeLocation).filter(loc => loc.country || loc.state || loc.city);
    const skills = normalizeSkills(existingSkills);
    const emp = employerData || {};
    const usr = userData || {};
    let empAddress = {};
    try {
        empAddress = typeof emp.address === 'string' ? JSON.parse(emp.address || '{}') : (emp.address || {});
    } catch (e) {
        empAddress = {};
    }
    const primaryLocation = (locs.length > 0 && locs[0]) ? locs[0] : ((jobLocations.length > 0 && jobLocations[0]) ? jobLocations[0] : {});
    const benefitIds = (Array.isArray(existingBenefits) ? existingBenefits : [])
        .map(b => (typeof b === 'object' && b !== null) ? (b.id ?? b.benefit_id ?? null) : b)
        .filter(id => id !== null && id !== undefined && id !== '')
        .map(id => Number(id))
        .filter(id => Number.isInteger(id));
    const defaultJobAddress = [
        empAddress.street || '',
        primaryLocation.city || emp.city || empAddress.city || '',
        primaryLocation.state || emp.state || empAddress.state || '',
        primaryLocation.country || emp.country || empAddress.country || ''
    ].filter(Boolean).join(', ');

    let payType = 'range';
    if (job.pay_type) {
        payType = job.pay_type;
    } else if (job.salary_min && job.salary_max && job.salary_min === job.salary_max) {
        payType = 'fixed';
    } else if (job.salary_min || job.salary_max) {
        payType = 'range';
    }

    return {
        isEditMode,
        existingJobData: job,
        existingLocations: locs,
        isLoading: true,
        currentStep: 0,
        totalSteps: 5,
        stepTitles: ["Basics", "Details", "Pay & Skills", "Description", "Review"],
        quillInitialized: false,
        locationLoading: false,
        countries: [],
        states: [],
        cities: [],
        statesLoading: false,
        citiesLoading: false,
        languageManuallySelected: false,
        initialLanguageAutoApplied: false,
        jobTitleSuggestions: { show: false, list: [], selectedIndex: -1, searchTimeout: null },
        skillSuggestions: { show: false, list: [], selectedIndex: -1, searchTimeout: null },
        skillQuery: '',
        requireSuggestionOnly: false,
        currencySymbol: '',
        showLanguageModal: false,
        symbolMap: {'INR':'₹','USD':'$','EUR':'€','GBP':'£','AUD':'$','CAD':'$'},
        jobTypes: [
            { value: 'full_time', label: 'Full-time' },
            { value: 'part_time', label: 'Part-time' },
            { value: 'contract', label: 'Contract' },
            { value: 'freelance', label: 'Freelance' },
            { value: 'internship', label: 'Internship' },
            { value: 'one_time', label: 'One-time job' }
        ],
        languages: ['English','Spanish','French','German','Hindi','Arabic','Chinese','Japanese'],
        categories: <?= json_encode($categoriesList) ?>,
        availableBenefits: allBenefits,
        formData: {
            title: job.title || '',
            work_address_type: (job.is_remote == 1 || (isEditMode && !job.job_address)) ? 'none' : 'specific',
            job_address: job.job_address || defaultJobAddress,
            location: {
                country: primaryLocation.country || emp.country || empAddress.country || 'India',
                state: primaryLocation.state || emp.state || empAddress.state || '',
                city: primaryLocation.city || emp.city || empAddress.city || '',
                latitude: primaryLocation.latitude || primaryLocation.lat || null,
                longitude: primaryLocation.longitude || primaryLocation.lng || null
            },
            employment_type: job.employment_type || (['full_time', 'part_time', 'one_time', 'internship', 'contract', 'freelance'].includes(new URLSearchParams(location.search).get('type')) ? new URLSearchParams(location.search).get('type') : 'full_time'),
            category: job.category || emp.industry || '',
            vacancies: (job.vacancies !== undefined && job.vacancies !== null) ? job.vacancies : 1,
            pay_type: job.pay_type || payType,
            pay_min: (job.salary_min !== undefined && job.salary_min !== null) ? job.salary_min : '',
            pay_amount: (job.pay_type === 'fixed') ? (job.pay_fixed_amount || job.salary_min || '') : ((job.salary_max !== undefined && job.salary_max !== null) ? job.salary_max : ''),
            pay_frequency: job.pay_frequency || 'monthly',
            currency: job.currency || 'INR',
            language: job.language || 'English',
            description: (job.description_html || job.description || ''),
            qualifications: (function() {
                if (job.qualifications_array && Array.isArray(job.qualifications_array)) return job.qualifications_array;
                if (!job.qualifications) return [];
                if (Array.isArray(job.qualifications)) return job.qualifications;
                try {
                    const parsed = JSON.parse(job.qualifications);
                    return Array.isArray(parsed) ? parsed : [];
                } catch(e) { return []; }
            })(),
            skills: skills,
            benefit_ids: benefitIds,
            min_experience: (job.min_experience !== undefined && job.min_experience !== null) ? job.min_experience : '',
            max_experience: (job.max_experience !== undefined && job.max_experience !== null) ? job.max_experience : '',
            experience_type: job.experience_type || ((job.min_experience == 0 && job.max_experience == 0) ? 'fresher' : ((job.min_experience || job.max_experience) ? 'experienced' : 'any')),
            offers_bonus: job.offers_bonus || 'no',
            hiring_urgency: job.hiring_urgency || 'immediate',
            job_timings: job.job_timings || '',
            interview_timings: job.interview_timings || '',
            call_availability: job.call_availability || 'everyday',
            company_name: job.company_name || emp.company_name || '',
            contact_person: job.contact_person || emp.contact_person || usr.name || '',
            phone: job.phone || usr.phone || '',
            email: job.email || usr.email || '',
            contact_profile: job.contact_profile || '',
            company_size: job.company_size || emp.size || ''
        },
        isSubmitting: false,
        isSavingDraft: false,
        notice: { text: '', type: 'error', errors: null, link: '', linkText: '' },

        get selectedBenefits() {
            return (this.formData.benefit_ids || []).map(id => Number(id)).filter(id => Number.isInteger(id));
        },

        async init() {
            setTimeout(() => this.isLoading = false, 600);
            await this.loadCountries();
            await this.loadCategories();
            await this.hydrateEditData();
            this.updateCurrencySymbol();

            this.$nextTick(() => {
                setTimeout(() => this.initQuill(), 100);
            });

        },

        async hydrateEditData() {
            if (!this.isEditMode || !this.existingJobData) return;

            const currentLocation = { ...(this.formData.location || {}) };
            this.formData = {
                ...this.formData,
                ...this.existingJobData,
                location: currentLocation
            };

            this.formData.employment_type = this.existingJobData.employment_type || this.existingJobData.job_type || this.formData.employment_type || 'full_time';
            this.formData.category = this.existingJobData.category || this.existingJobData.work_category || this.formData.category || '';
            if (this.formData.category && !this.categories.some(cat => cat.value === this.formData.category)) {
                this.categories = [
                    ...this.categories,
                    { label: this.formData.category, value: this.formData.category }
                ];
            }
            this.formData.skills = normalizeSkills(this.formData.skills.length ? this.formData.skills : existingSkills);
            this.formData.benefit_ids = (this.formData.benefit_ids || []).map(id => Number(id)).filter(id => Number.isInteger(id));
            this.formData.description = this.existingJobData.description_html || this.existingJobData.description || this.formData.description || '';
            this.formData.qualifications = (() => {
                const qualifications = this.existingJobData.qualifications_array || this.existingJobData.qualifications || this.formData.qualifications || [];
                if (Array.isArray(qualifications)) return qualifications;
                try {
                    const parsed = JSON.parse(qualifications);
                    return Array.isArray(parsed) ? parsed : [];
                } catch(e) {
                    return [];
                }
            })();

            const loc = this.existingLocations.length > 0
                ? this.existingLocations[0]
                : (jobLocations.length > 0 ? jobLocations[0] : null);

            if (loc) {
                const country = loc.country || '';
                const state = loc.state || '';
                const city = loc.city || '';

                if (country && !this.countries.some(item => item.name === country)) {
                    this.countries = [{ name: country }, ...this.countries];
                }

                this.formData.location = {
                    ...this.formData.location,
                    country,
                    state: '',
                    city: '',
                    latitude: loc.latitude || null,
                    longitude: loc.longitude || null
                };

                if (country) {
                    await this.onCountryChange();
                }

                if (state && !this.states.includes(state)) {
                    this.states = [state, ...this.states];
                }
                this.formData.location.state = state;

                if (state) {
                    await this.onStateChange();
                }

                if (city && !this.cities.includes(city)) {
                    this.cities = [city, ...this.cities];
                }
                this.formData.location.city = city;
            }
        },

        async loadCountries() {
            try {
                const res = await fetch('https://cdn.jsdelivr.net/npm/world-countries@3/countries.json');
                const data = await res.json();
                this.countries = data.map(d => ({ name: d.name.common, currencies: d.currencies }));
            } catch (e) {
                this.countries = [{ name: 'India' }, { name: 'United States' }];
            }
        },

        async loadCategories() {
            try {
                const response = await fetch('/api/industries/all');
                const data = await response.json();
                if (data.industries && Array.isArray(data.industries)) {
                    this.categories = data.industries;
                }
            } catch (e) { console.error(e); }
        },

        async onCountryChange(preserveValues = false) {
            if (!preserveValues) {
                this.states = []; 
                this.cities = []; 
                this.formData.location.state = ''; 
                this.formData.location.city = '';
            }
            
            this.statesLoading = true;
            try {
                const res = await fetch('https://countriesnow.space/api/v0.1/countries/states', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ country: this.formData.location.country })
                });
                const data = await res.json();
                this.states = (data.data?.states || []).map(s => s.name);
            } catch(e) {}
            this.statesLoading = false;

            const c = this.countries.find(x => x.name === this.formData.location.country);
            if(c && c.currencies) this.formData.currency = Object.keys(c.currencies)[0] || 'INR';
            this.updateCurrencySymbol();
        },

        async onStateChange(preserveValues = false) {
            if (!preserveValues) {
                this.cities = []; 
                this.formData.location.city = '';
            }
            
            this.citiesLoading = true;
            try {
                const res = await fetch('https://countriesnow.space/api/v0.1/countries/state/cities', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ country: this.formData.location.country, state: this.formData.location.state })
                });
                const data = await res.json();
                this.cities = data.data || [];
            } catch(e) {}
            this.citiesLoading = false;
        },

        updateCurrencySymbol() {
            this.currencySymbol = this.symbolMap[this.formData.currency] || this.formData.currency;
        },

        initQuill() {
            if (this.quillInitialized || !document.getElementById('job-description-editor')) return;
            try {
                window.quillEditor = new Quill('#job-description-editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            ['link', 'clean']
                        ]
                    },
                    placeholder: 'Describe the role, responsibilities, and requirements...'
                });
                window.quillEditor.on('text-change', () => {
                    this.formData.description = window.quillEditor.root.innerHTML;
                });
                if (this.formData.description) {
                    window.quillEditor.root.innerHTML = this.formData.description;
                }
                this.quillInitialized = true;
            } catch (e) { console.error(e); }
        },

        async searchJobTitles(query) {
            if (!query || query.length < 2) { this.jobTitleSuggestions.show = false; return; }
            clearTimeout(this.jobTitleSuggestions.searchTimeout);
            this.jobTitleSuggestions.searchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`/api/job-titles/search?q=${encodeURIComponent(query)}&limit=8`);
                    const data = await response.json();
                    this.jobTitleSuggestions.list = data.suggestions || [];
                    this.jobTitleSuggestions.show = this.jobTitleSuggestions.list.length > 0;
                } catch (e) {}
            }, 300);
        },

        selectJobTitleSuggestion(s) {
            this.formData.title = s.title;
            if (s.category) {
                this.formData.category = s.category;
            }
            this.jobTitleSuggestions.show = false;
            this.refreshSkillContextSuggestions();
        },

        handleJobTitleKeyDown(e) {},

        selectJobType(t) { this.formData.employment_type = t; },
        setExperienceType(t) { this.formData.experience_type = t; },

        onSkillInput(e) {
            const val = (e.target.value || '').trim();
            clearTimeout(this.skillSuggestions.searchTimeout);
            if (val.length < 1) { this.skillSuggestions.list = []; return; }
            this.skillSuggestions.searchTimeout = setTimeout(() => this.searchSkills(val), 250);
        },
        async searchSkills(query) {
            const params = new URLSearchParams({
                q: query || '',
                title: this.formData.title || '',
                category: this.formData.category || '',
                limit: '8'
            }).toString();
            try {
                const res = await fetch(`/api/skills/suggest?${params}`);
                const data = await res.json();
                this.skillSuggestions.list = (data.suggestions || []).filter(s => !this.formData.skills.includes(s.name));
                this.skillSuggestions.show = this.skillSuggestions.list.length > 0;
                this.skillSuggestions.selectedIndex = this.skillSuggestions.list.length > 0 ? 0 : -1;
            } catch (e) {}
        },
        refreshSkillContextSuggestions() {
            this.searchSkills('');
        },
        selectSkillSuggestion(s) {
            const name = (s.name || '').trim();
            if (name && !this.formData.skills.includes(name)) {
                this.formData.skills.push(name);
            }
            this.skillQuery = '';
            this.skillSuggestions.show = false;
            if (this.$refs.skillInput) this.$refs.skillInput.value = '';
        },
        addSkillFromQuery() {
            const val = (this.skillQuery || '').trim();
            if (this.skillSuggestions.show && this.skillSuggestions.selectedIndex >= 0 && this.skillSuggestions.list[this.skillSuggestions.selectedIndex]) {
                this.selectSkillSuggestion(this.skillSuggestions.list[this.skillSuggestions.selectedIndex]);
                return;
            }
            if (this.requireSuggestionOnly) return;
            if (val && !this.formData.skills.includes(val)) {
                this.formData.skills.push(val);
            }
            this.skillQuery = '';
            if (this.$refs.skillInput) this.$refs.skillInput.value = '';
            this.skillSuggestions.show = false;
        },
        handleSkillKeyDown(e) {
            if (!this.skillSuggestions.show || this.skillSuggestions.list.length === 0) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.skillSuggestions.selectedIndex = Math.min(this.skillSuggestions.selectedIndex + 1, this.skillSuggestions.list.length - 1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.skillSuggestions.selectedIndex = Math.max(this.skillSuggestions.selectedIndex - 1, 0);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                this.addSkillFromQuery();
            }
        },
        removeSkill(i) { this.formData.skills.splice(i, 1); },
        escapeHtml(str) { const div = document.createElement('div'); div.innerText = str; return div.innerHTML; },

        async getCurrentLocation() {
            if (!navigator.geolocation) return;
            this.locationLoading = true;
            const done = () => { this.locationLoading = false; };
            navigator.geolocation.getCurrentPosition(async (pos) => {
                try {
                    this.formData.location.latitude = pos.coords.latitude;
                    this.formData.location.longitude = pos.coords.longitude;
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${pos.coords.latitude}&lon=${pos.coords.longitude}`);
                    const data = await res.json();
                    const addr = data.address || {};
                    const country = addr.country || '';
                    const state = addr.state || addr.region || addr.state_district || '';
                    const city = addr.city || addr.town || addr.village || addr.suburb || '';
                    if (country) {
                        this.formData.location.country = country;
                        await this.onCountryChange();
                        if (state) {
                            this.formData.location.state = state;
                            if (!this.states.includes(state)) { this.states.unshift(state); }
                            await this.onStateChange();
                        }
                        if (city) {
                            this.formData.location.city = city;
                            if (!this.cities.includes(city)) { this.cities.unshift(city); }
                        }
                    }
                } catch(e) {}
                done();
            }, done);
        },

        canProceed() {
            if(this.currentStep === 0) {
                return this.formData.employment_type &&
                       this.formData.title &&
                       this.formData.category &&
                       this.formData.location.country &&
                       this.formData.location.state &&
                       this.formData.location.city &&
                       this.formData.vacancies &&
                       this.formData.work_address_type &&
                       (this.formData.work_address_type === 'none' || this.formData.job_address);
            }
            if(this.currentStep === 1) {
                const expValid = this.formData.experience_type && (this.formData.experience_type !== 'experienced' || (this.formData.min_experience !== '' && this.formData.max_experience !== ''));
                return this.formData.category && this.formData.interview_timings && this.formData.hiring_urgency && expValid;
            }
            if(this.currentStep === 2) {
                let payValid = true;
                if(this.formData.pay_type === 'range') {
                    payValid = this.formData.pay_min && this.formData.pay_amount &&
                               Number(this.formData.pay_amount) >= Number(this.formData.pay_min);
                } else if(this.formData.pay_type === 'fixed') {
                    payValid = this.formData.pay_amount;
                }
                return payValid;
            }
            if(this.currentStep === 3) return this.formData.description || (window.quillEditor && window.quillEditor.root.textContent.trim().length > 0);
            return true;
        },

        goNext() {
            if (this.canProceed() && this.currentStep < this.totalSteps - 1) {
                if (this.currentStep === 3 && window.quillEditor) this.formData.description = window.quillEditor.root.innerHTML;
                this.currentStep++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        goBack() {
            if (this.currentStep > 0) {
                this.currentStep--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        getLocationDisplay() {
            return [this.formData.location.city, this.formData.location.state, this.formData.location.country].filter(Boolean).join(', ') || 'Not set';
        },
        getJobTypeLabel() {
            const t = this.jobTypes.find(x => x.value === this.formData.employment_type);
            return t ? t.label : this.formData.employment_type;
        },
        getPayDisplay() {
            if(this.formData.pay_type === 'negotiable') return 'Negotiable';
            if(this.formData.pay_type === 'fixed') return `${this.currencySymbol}${this.formData.pay_amount} ${this.formData.pay_frequency}`;
            return `${this.currencySymbol}${this.formData.pay_min} – ${this.currencySymbol}${this.formData.pay_amount} ${this.formData.pay_frequency}`;
        },

        async generateJD() {
            if (!this.formData.title) { alert('Enter a job title first'); return; }
            try {
                const res = await fetch('/employer/jobs/generate-description', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ title: this.formData.title, skills: this.formData.skills })
                });
                const data = await res.json();
                if(data.success) {
                    if(window.quillEditor) window.quillEditor.root.innerHTML = data.description;
                    this.formData.description = data.description;
                }
            } catch(e) { alert('AI generation failed. Please try again.'); }
        },

        showNotice(text, type = 'error', extra = {}) {
            this.notice = { text, type, errors: extra.errors || null, link: extra.link || '', linkText: extra.linkText || '' };
            this.$nextTick(() => { const el = this.$root.querySelector('.jwiz-notice'); if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' }); });
        },

        stepForField(field) {
            const map = { title: 0, category: 0, employment_type: 0, location: 0, job_address: 0, vacancies: 0,
                          interview_timings: 1, hiring_urgency: 1, min_experience: 1, max_experience: 1, experience_type: 1,
                          salary_min: 2, salary_max: 2, pay_fixed_amount: 2, pay_amount: 2, skills: 2,
                          description: 3, email: 4, phone: 4 };
            return map[field] ?? null;
        },

        buildPayload(status) {
            let description = window.quillEditor ? window.quillEditor.root.innerHTML : (this.formData.description || '');
            if (window.quillEditor && window.quillEditor.root.textContent.trim() === '') description = '';
            this.formData.description = description;

            // Qualifications are rendered as a list after the description (built fresh each time – never appended twice)
            let fullDescription = description;
            if (this.formData.qualifications && this.formData.qualifications.length > 0 && !description.includes('<h5>Education &amp; Qualifications</h5>')) {
                const items = this.formData.qualifications.map(l => '<li>' + this.escapeHtml(l) + '</li>').join('');
                fullDescription += '<h5>Education &amp; Qualifications</h5><ul>' + items + '</ul>';
            }

            const payload = {
                ...this.formData,
                description: fullDescription,
                location: [{
                    city: this.formData.location.city,
                    state: this.formData.location.state,
                    country: this.formData.location.country,
                    latitude: this.formData.location.latitude,
                    longitude: this.formData.location.longitude
                }],
                benefit_ids: this.selectedBenefits,
                skills: this.formData.skills.map(s => typeof s === 'object' ? s.name : s),
                salary_min: this.formData.pay_type === 'range' ? Number(this.formData.pay_min || 0) : null,
                salary_max: this.formData.pay_type === 'range' ? Number(this.formData.pay_amount || 0) : null,
                pay_fixed_amount: this.formData.pay_type === 'fixed' ? Number(this.formData.pay_amount || 0) : null
            };
            delete payload.status;
            if (status) payload.status = status;
            return payload;
        },

        async sendJob(status) {
            this.notice.text = '';
            const url = isEditMode ? `/employer/jobs/${existingJobData.slug || existingJobData.id}` : '/employer/jobs';
            let res, data = {};
            try {
                res = await fetch(url, {
                    method: isEditMode ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(this.buildPayload(status))
                });
                data = await res.json().catch(() => ({}));
            } catch (e) {
                this.showNotice('Could not reach the server. Please check your internet connection and try again.');
                return null;
            }

            if (res.ok) return data;

            if (res.status === 422 && data.errors) {
                const first = Object.keys(data.errors)[0];
                const step = this.stepForField(first);
                if (step !== null && step < this.currentStep) this.currentStep = step;
                this.showNotice(data.message || 'Please fix the highlighted fields.', 'error', { errors: data.errors });
            } else if ((res.status === 402 || res.status === 403) && data.redirect) {
                this.showNotice(data.message || 'Action required before you can post this job.', 'warning',
                    { link: data.redirect, linkText: res.status === 402 ? (data.pay_per_post ? 'Pay ₹200 + GST for this job →' : 'View subscription plans →') : 'Continue →' });
            } else if (res.status === 401 || res.status === 419) {
                this.showNotice('Your session has expired. Please log in again – your form will need to be re-submitted.', 'warning', { link: '/login?redirect=/employer/jobs/create', linkText: 'Log in →' });
            } else {
                this.showNotice(data.message || data.error || 'Something went wrong while saving the job. Please try again.');
            }
            return null;
        },

        async submitJob() {
            if (this.isSubmitting) return;
            this.isSubmitting = true;
            try {
                const submitStatus = isEditMode ? (['draft', 'rejected', 'closed'].includes(existingJobData.status) ? 'submit' : undefined) : 'submit';
                const data = await this.sendJob(submitStatus);
                if (!data) return;
                try {
                    if (window.MWMarketing) {
                        const loc = [this.formData.location.city, this.formData.location.state, this.formData.location.country].filter(Boolean).join(', ');
                        window.MWMarketing.trackEmployerPostJob({
                            content_type: 'job_post', content_ids: data.job_id ? [parseInt(data.job_id, 10)] : [],
                            content_name: this.formData.title || '', content_category: this.formData.category || '',
                            content_slug: data.slug || '', location: loc, value: 0, currency: 'INR'
                        });
                    }
                } catch (_) {}
                window.location.href = data.redirect || '/employer/jobs';
            } finally {
                this.isSubmitting = false;
            }
        },

        async saveDraft() {
            if (this.isSubmitting) return;
            if (!this.formData.title || this.formData.title.trim().length < 3) {
                this.currentStep = 0;
                this.showNotice('Add a job title (at least 3 characters) before saving a draft.');
                return;
            }
            this.isSubmitting = true;
            this.isSavingDraft = true;
            try {
                const data = await this.sendJob(isEditMode ? undefined : 'draft');
                if (data) window.location.href = data.redirect || '/employer/jobs?saved=draft';
            } finally {
                this.isSubmitting = false;
                this.isSavingDraft = false;
            }
        }
    };
    });
});
</script>
<?php
$scripts = ($scripts ?? '') . ob_get_clean();
?>










