<?php
// Candidate Registration Page - Mindware Infotech
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Candidate Registration - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            margin: 0;
            background: #f8fafc;
            display: flex;
        }

        /* ═══════ PAGE LAYOUT ═══════ */
        .page-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100vw;
            min-height: 100vh;
        }

        /* ═══════ LEFT SLIDER PANEL ═══════ */
        /* Calm indigo-to-blue gradient matching Mindware brand */
        .slider-panel {
            position: sticky;
            top: 0;
            height: 100vh;
            overflow: hidden;
            background: linear-gradient(150deg, #eef2ff 0%, #e0e7ff 35%, #dbeafe 70%, #eff6ff 100%);
        }

        /* Slider content */
        .s-content {
            position: relative; z-index: 10;
            height: 100%;
            display: flex; flex-direction: column;
            overflow: hidden;
        }

        /* Soft mesh overlay */
        .s-mesh {
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 70% 55% at 20% 20%, rgba(99,102,241,0.12) 0%, transparent 65%),
                radial-gradient(ellipse 50% 65% at 80% 70%, rgba(59,130,246,0.1) 0%, transparent 65%),
                radial-gradient(ellipse 60% 40% at 55% 5%, rgba(139,92,246,0.07) 0%, transparent 70%);
        }

        /* Subtle dot grid */
        .s-dots {
            position: absolute; inset: 0;
            background-image: radial-gradient(circle, rgba(99,102,241,0.18) 1px, transparent 1px);
            background-size: 28px 28px;
            opacity: 0.6;
        }

        /* Decorative blobs */
        .blob {
            position: absolute; border-radius: 50%;
            filter: blur(55px); pointer-events: none;
        }
        .blob-1 { width:280px;height:280px;top:-60px;left:-60px;background:rgba(99,102,241,.12);animation:blobFloat 14s ease-in-out infinite; }
        .blob-2 { width:200px;height:200px;bottom:60px;right:-50px;background:rgba(59,130,246,.1);animation:blobFloat 10s ease-in-out infinite reverse; }
        .blob-3 { width:150px;height:150px;bottom:-30px;left:80px;background:rgba(139,92,246,.1);animation:blobFloat 17s ease-in-out infinite 5s; }
        @keyframes blobFloat{0%,100%{transform:translate(0,0) scale(1)}33%{transform:translate(22px,-18px) scale(1.04)}66%{transform:translate(-16px,24px) scale(.96)}}

        /* Slider content */
        .s-logo {
            padding: 32px 40px 0;
            display: flex; align-items: center; gap: 10px;
        }
        .s-logo-mark {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, #4f46e5, #3b82f6);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800; font-size: 18px; color: white;
            box-shadow: 0 4px 14px rgba(79,70,229,.3);
        }
        .s-logo-name {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700; font-size: 18px; color: #1e1b4b;
            letter-spacing: -.2px;
        }

        /* Slides */
        .slides-wrap {
            flex: 1; display: flex; flex-direction: column;
            justify-content: center; padding: 0 40px 32px;
            min-height: 0;
        }
        .slides-container {
            position: relative;
            flex: 1;
            min-height: 0;
            /* Use a grid trick so container takes height of tallest slide */
            display: grid;
        }

        .slide {
            grid-area: 1 / 1;
            display: flex; flex-direction: column; gap: 18px;
            opacity: 0; transform: translateX(50px);
            transition: all .65s cubic-bezier(.4,0,.2,1);
            pointer-events: none;
            /* Allow natural height — no clipping */
            align-self: start;
        }
        .slide.active { opacity:1; transform:translateX(0); pointer-events:auto; }
        .slide.exiting { opacity:0; transform:translateX(-50px); }

        /* Slide elements */
        .s-badge {
            display:inline-flex; align-items:center; gap:7px;
            background: rgba(79,70,229,.1); border:1px solid rgba(79,70,229,.2);
            border-radius:100px; padding:5px 13px;
            font-size:11px; font-weight:600; color:#4338ca;
            letter-spacing:.5px; text-transform:uppercase; width:fit-content;
        }
        .s-badge .pulse {
            width:6px;height:6px;border-radius:50%;background:#4f46e5;
            animation:pulse 2s ease-in-out infinite;
        }
        @keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.4;transform:scale(.8)}}

        .s-title {
            font-family:'Plus Jakarta Sans',sans-serif;
            font-size: clamp(20px, 2.4vw, 30px);
            font-weight:800; color:#1e1b4b; line-height:1.2; letter-spacing:-.6px;
        }
        .s-title .accent {
            background:linear-gradient(135deg,#4f46e5,#3b82f6);
            -webkit-background-clip:text;-webkit-text-fill-color:transparent;
        }
        .s-desc { font-size:13.5px; color:#4b5563; line-height:1.65; max-width:380px; }

        /* Stat cards */
        .stat-row { display:flex; gap:10px; }
        .stat-card {
            background:white; border:1px solid #e0e7ff;
            border-radius:12px; padding:12px 14px; flex:1;
            box-shadow:0 2px 10px rgba(79,70,229,.05);
        }
        .stat-val { font-family:'Plus Jakarta Sans',sans-serif;font-size:20px;font-weight:800;color:#1e1b4b; }
        .stat-val span { color:#4f46e5; }
        .stat-lbl { font-size:11px;color:#6b7280;margin-top:1px; }

        /* Feature list */
        .feat-list { display:flex;flex-direction:column;gap:8px; }
        .feat-item { display:flex;align-items:center;gap:10px;font-size:13px;color:#374151; }
        .feat-icon {
            width:30px;height:30px;border-radius:8px;
            display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;
        }

        /* Profile cards */
        .profile-list { display:flex;flex-direction:column;gap:8px; }
        .p-card {
            background:white; border:1px solid #e0e7ff;
            border-radius:11px; padding:10px 13px;
            display:flex;align-items:center;gap:9px;
            box-shadow:0 1px 6px rgba(79,70,229,.04);
        }
        .p-avatar { width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:white;flex-shrink:0; }
        .p-name { font-size:12.5px;font-weight:600;color:#111827; }
        .p-role { font-size:11px;color:#6b7280; }
        .p-badge {
            margin-left:auto;font-size:10px;font-weight:700;
            background:#ecfdf5;border:1px solid #a7f3d0;color:#059669;
            border-radius:100px;padding:2px 8px;white-space:nowrap;
        }

        /* Slider nav */
        .s-nav { display:flex;align-items:center;gap:16px;margin-top:20px; }
        .s-dots-nav { display:flex;gap:7px;flex:1; }
        .s-dot {
            height:4px;border-radius:2px;
            background:rgba(79,70,229,.2);cursor:pointer;
            transition:all .4s ease;flex:1;max-width:38px;
        }
        .s-dot.active { background:#4f46e5;max-width:52px;box-shadow:0 0 8px rgba(79,70,229,.4); }
        .s-arrows { display:flex;gap:7px; }
        .s-arrow-btn {
            width:34px;height:34px;border-radius:50%;
            background:white;border:1.5px solid #e0e7ff;
            color:#4338ca;cursor:pointer;
            display:flex;align-items:center;justify-content:center;
            transition:all .2s;box-shadow:0 1px 6px rgba(79,70,229,.08);
        }
        .s-arrow-btn:hover { background:#eef2ff;border-color:#c7d2fe;transform:scale(1.06); }
        .s-arrow-btn svg { width:15px;height:15px; }

        .s-progress { height:3px;background:rgba(79,70,229,.12);border-radius:2px;overflow:hidden;margin-top:10px; }
        .s-progress-bar { height:100%;background:linear-gradient(90deg,#4f46e5,#3b82f6);border-radius:2px;transition:width .1s linear; }

        /* ═══════ RIGHT REGISTER PANEL ═══════ */
        .reg-panel {
            background:#ffffff;
            display:flex;align-items:flex-start;justify-content:center;
            padding:44px 52px;overflow-y:auto;min-height:100vh;
            border-left:1px solid #f1f5f9;
        }

        .reg-box {
            width:100%;max-width:432px;
            animation:fadeUp .5s cubic-bezier(.4,0,.2,1) both;
        }
        @keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}

        /* Back link */
        .back-link {
            display:inline-flex;align-items:center;gap:5px;
            font-size:13px;color:#6b7280;text-decoration:none;
            margin-bottom:28px;transition:color .2s;font-weight:500;
        }
        .back-link:hover{color:#111827;}
        .back-link svg{width:15px;height:15px;}

        /* Brand row */
        .brand-row { display:flex;align-items:center;gap:11px;margin-bottom:22px; }
        .brand-mark {
            width:42px;height:42px;
            background:linear-gradient(135deg,#4f46e5,#3b82f6);
            border-radius:12px;display:flex;align-items:center;justify-content:center;
            font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:20px;color:white;
            box-shadow:0 4px 14px rgba(79,70,229,.22);
        }
        .brand-name-text{font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:16px;color:#111827;}
        .brand-sub{font-size:12px;color:#9ca3af;}

        /* Headings */
        .reg-h1 {
            font-family:'Plus Jakarta Sans',sans-serif;
            font-size:26px;font-weight:800;color:#111827;letter-spacing:-.5px;margin-bottom:4px;
        }
        .reg-sub { font-size:13.5px;color:#6b7280;margin-bottom:26px; }

        /* Alert boxes */
        .alert {
            border-radius:10px;padding:12px 14px;
            display:flex;align-items:flex-start;gap:9px;
            margin-bottom:16px;font-size:13px;font-weight:500;
        }
        .alert-error{background:#fef2f2;border-left:3px solid #f87171;color:#b91c1c;}
        .alert-success{background:#f0fdf4;border-left:3px solid #4ade80;color:#166534;}
        .alert svg{width:16px;height:16px;flex-shrink:0;margin-top:1px;}

        /* Form fields */
        .f-group{margin-bottom:16px;}
        .f-label{
            display:block;font-size:12.5px;font-weight:600;
            color:#374151;margin-bottom:6px;
        }
        .f-label .req{color:#ef4444;}
        .f-wrap{position:relative;}

        .f-input{
            width:100%;padding:11px 15px;
            border:1.5px solid #e5e7eb;
            border-radius:10px;font-size:14px;
            font-family:'Plus Jakarta Sans',sans-serif;color:#111827;
            background:#fafafa;outline:none;transition:all .2s;
        }
        .f-input::placeholder{color:#9ca3af;}
        .f-input:focus{
            border-color:#4f46e5;background:#fff;
            box-shadow:0 0 0 3px rgba(79,70,229,.1);
        }
        .f-input.v-valid{border-color:#22c55e;background:#fff;}
        .f-input.v-invalid{border-color:#ef4444;}
        .f-input.has-eye{padding-right:44px;}

        .eye-btn{
            position:absolute;right:12px;top:50%;transform:translateY(-50%);
            background:none;border:none;cursor:pointer;color:#9ca3af;
            transition:color .2s;padding:3px;display:flex;
        }
        .eye-btn:hover{color:#6b7280;}
        .eye-btn svg{width:17px;height:17px;}

        /* Password strength */
        .strength-wrap{margin-top:9px;}
        .s-bar{
            height:4px;border-radius:2px;
            transition:all .3s ease;margin-bottom:5px;
        }
        .strength-weak  {background:linear-gradient(to right,#ef4444 0%,#ef4444 33%,#e5e7eb 33%,#e5e7eb 100%);}
        .strength-fair  {background:linear-gradient(to right,#f59e0b 0%,#f59e0b 66%,#e5e7eb 66%,#e5e7eb 100%);}
        .strength-good  {background:linear-gradient(to right,#3b82f6 0%,#3b82f6 100%);}
        .strength-strong{background:linear-gradient(to right,#10b981 0%,#10b981 100%);}

        .s-bar-row{display:flex;align-items:center;justify-content:space-between;}
        .s-bar-label{font-size:11.5px;font-weight:600;}
        .s-bar-chars{font-size:11px;color:#9ca3af;}

        /* Requirements */
        .req-panel{
            margin-top:10px;padding:13px 15px;
            background:#f8fafc;border:1.5px solid #e5e7eb;border-radius:10px;
            animation:fadeSlide .2s ease;
        }
        @keyframes fadeSlide{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
        .req-head{font-size:11.5px;font-weight:700;color:#374151;margin-bottom:9px;}
        .req-row{
            display:flex;align-items:center;font-size:12px;
            margin-bottom:6px;transition:color .2s;
        }
        .req-row:last-child{margin-bottom:0;}
        .req-row.ok{color:#16a34a;}
        .req-row.no{color:#9ca3af;}

        /* Pure CSS check/cross icons */
        .ico-check{
            display:inline-flex;align-items:center;justify-content:center;
            width:15px;height:15px;border-radius:50%;background:#22c55e;
            margin-right:7px;flex-shrink:0;
        }
        .ico-check::after{
            content:'';width:3.5px;height:6.5px;
            border:solid white;border-width:0 2px 2px 0;
            transform:rotate(45deg);margin-top:-1px;
        }
        .ico-cross{
            display:inline-flex;align-items:center;justify-content:center;
            width:15px;height:15px;border-radius:50%;background:#e5e7eb;
            margin-right:7px;flex-shrink:0;font-size:8px;color:#9ca3af;
        }
        .ico-cross::after{content:'✕';}

        /* Suggestions */
        .sug-box{
            margin-top:9px;padding:10px 13px;
            background:#eff6ff;border-left:3px solid #3b82f6;border-radius:7px;
            animation:fadeSlide .2s ease;
        }
        .sug-title{font-size:11.5px;font-weight:700;color:#1d4ed8;margin-bottom:5px;}
        .sug-list{padding-left:13px;}
        .sug-list li{font-size:11px;color:#1d4ed8;margin-bottom:2px;}

        /* Field messages */
        .f-hint{font-size:11.5px;color:#9ca3af;margin-top:5px;}
        .f-err{font-size:11.5px;color:#ef4444;margin-top:4px;display:flex;align-items:center;gap:4px;}
        .f-ok {font-size:11.5px;color:#22c55e;margin-top:4px;display:flex;align-items:center;gap:4px;}
        .f-err svg,.f-ok svg{width:12px;height:12px;flex-shrink:0;}

        /* Divider */
        .divider{display:flex;align-items:center;gap:10px;margin:16px 0;}
        .div-line{flex:1;height:1px;background:#e5e7eb;}
        .div-text{font-size:11.5px;color:#9ca3af;font-weight:500;white-space:nowrap;}

        /* Social buttons */
        .social-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:9px;margin-bottom:16px;}
        .soc-btn{
            display:flex;align-items:center;justify-content:center;
            padding:10px;border:1.5px solid #e5e7eb;border-radius:9px;
            background:white;cursor:pointer;transition:all .18s;text-decoration:none;
        }
        .soc-btn:hover{border-color:#c7d2fe;background:#f5f3ff;transform:translateY(-1px);box-shadow:0 3px 10px rgba(79,70,229,.1);}
        .soc-btn img,.soc-btn svg{width:20px;height:20px;}

        /* Terms */
        .terms-row{display:flex;align-items:flex-start;gap:9px;margin-bottom:18px;}
        .terms-cb{width:16px;height:16px;margin-top:2px;flex-shrink:0;accent-color:#4f46e5;cursor:pointer;}
        .terms-txt{font-size:12.5px;color:#4b5563;line-height:1.55;}
        .terms-txt a{color:#4f46e5;font-weight:600;text-decoration:none;}
        .terms-txt a:hover{color:#4338ca;text-decoration:underline;}

        /* Submit */
        .submit-btn{
            width:100%;padding:13px;
            background:linear-gradient(135deg,#4f46e5,#3b82f6);
            color:white;border:none;border-radius:11px;
            font-family:'Plus Jakarta Sans',sans-serif;font-size:15px;font-weight:700;
            cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;
            transition:all .22s;box-shadow:0 4px 16px rgba(79,70,229,.28);
            letter-spacing:.1px;margin-bottom:14px;
        }
        .submit-btn:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 8px 24px rgba(79,70,229,.35);}
        .submit-btn:disabled{opacity:.55;cursor:not-allowed;transform:none;box-shadow:none;}
        .submit-btn svg{width:16px;height:16px;}
        .spinner{width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:white;border-radius:50%;animation:spin .7s linear infinite;}
        @keyframes spin{to{transform:rotate(360deg)}}

        /* Footer */
        .reg-footer{text-align:center;font-size:12.5px;color:#6b7280;}
        .reg-footer a{font-weight:700;color:#4f46e5;text-decoration:none;}
        .reg-footer a:hover{color:#4338ca;}

        /* Responsive */
        @media(max-width:900px){
            .page-grid{grid-template-columns:1fr;}
            .slider-panel{display:none;}
            .reg-panel{padding:36px 24px;}
        }
    </style>
</head>
<body>
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            try{ if(window.MWMarketing){ MWMarketing.trackInitiateRegistration({role:'candidate'}); } }catch(_){}
        });
    </script>

<div class="page-grid">

    <!-- ═══════ LEFT: SLIDER PANEL ═══════ -->
    <div class="slider-panel">
        <div class="s-mesh"></div>
        <div class="s-dots"></div>
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>

        <div class="s-content">
            <!-- Logo -->
            <div class="s-logo">
                <div class="s-logo-mark">M</div>
                <span class="s-logo-name">Mindware</span>
            </div>

            <!-- Slides -->
            <div class="slides-wrap">
                <div class="slides-container" id="slidesContainer">

                    <!-- Slide 1: Stats -->
                    <div class="slide active" data-slide="0">
                        <div class="s-badge"><span class="pulse"></span>For Candidates</div>
                        <div class="s-title">Start Your <span class="accent">Career Journey</span><br>The Right Way</div>
                        <div class="s-desc">Thousands of verified employers are actively searching for talent like you. Create your profile once and let opportunities come to you.</div>
                        <div class="stat-row">
                            <div class="stat-card">
                                <div class="stat-val">12<span>K+</span></div>
                                <div class="stat-lbl">Active Jobs</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-val">98<span>%</span></div>
                                <div class="stat-lbl">Verified Employers</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-val">4.9<span>★</span></div>
                                <div class="stat-lbl">Candidate Rating</div>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 2: Features -->
                    <div class="slide" data-slide="1">
                        <div class="s-badge"><span class="pulse"></span>Smart Tools</div>
                        <div class="s-title">Everything You Need<br>to <span class="accent">Get Hired Faster</span></div>
                        <div class="s-desc">From AI-powered job matching to real-time tracking — we give you every edge to land the perfect role.</div>
                        <div class="feat-list">
                            <div class="feat-item">
                                <div class="feat-icon" style="background:#eef2ff;">🎯</div>
                                <span>AI job matching based on your skills & preferences</span>
                            </div>
                            <div class="feat-item">
                                <div class="feat-icon" style="background:#eff6ff;">📊</div>
                                <span>Live application tracking with instant status updates</span>
                            </div>
                            <div class="feat-item">
                                <div class="feat-icon" style="background:#fef3c7;">🔒</div>
                                <span>Full privacy controls — you own your data</span>
                            </div>
                            <div class="feat-item">
                                <div class="feat-icon" style="background:#ecfdf5;">⚡</div>
                                <span>One-click apply to hundreds of top companies</span>
                            </div>
                        </div>
                    </div>

                    <!-- Slide 3: Success Stories -->
                    <div class="slide" data-slide="2">
                        <div class="s-badge"><span class="pulse"></span>Success Stories</div>
                        <div class="s-title">Real People,<br><span class="accent">Real Careers</span> Built Here</div>
                        <div class="s-desc">Join thousands of professionals who found their dream roles through Mindware's trusted network.</div>
                        <div class="profile-list">
                            <div class="p-card">
                                <div class="p-avatar" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">P</div>
                                <div><div class="p-name">Priya Sharma</div><div class="p-role">Senior UX Designer · Bangalore</div></div>
                                <span class="p-badge">✓ Hired</span>
                            </div>
                            <div class="p-card">
                                <div class="p-avatar" style="background:linear-gradient(135deg,#3b82f6,#06b6d4);">R</div>
                                <div><div class="p-name">Rahul Mehta</div><div class="p-role">Backend Engineer · Mumbai</div></div>
                                <span class="p-badge">✓ Hired</span>
                            </div>
                            <div class="p-card">
                                <div class="p-avatar" style="background:linear-gradient(135deg,#f59e0b,#ef4444);">A</div>
                                <div><div class="p-name">Ananya Iyer</div><div class="p-role">Product Manager · Hyderabad</div></div>
                                <span class="p-badge">✓ Hired</span>
                            </div>
                        </div>
                    </div>

                </div><!-- /slides-container -->

                <div class="s-progress">
                    <div class="s-progress-bar" id="progressBar" style="width:0%"></div>
                </div>
                <div class="s-nav">
                    <div class="s-dots-nav">
                        <div class="s-dot active" onclick="goToSlide(0)"></div>
                        <div class="s-dot" onclick="goToSlide(1)"></div>
                        <div class="s-dot" onclick="goToSlide(2)"></div>
                    </div>
                    <div class="s-arrows">
                        <button class="s-arrow-btn" onclick="prevSlide()" aria-label="Previous">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button class="s-arrow-btn" onclick="nextSlide()" aria-label="Next">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════ RIGHT: REGISTER PANEL ═══════ -->
    <div class="reg-panel">
        <div x-data="registrationForm()" x-cloak class="reg-box">

            <a href="/" class="back-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Home
            </a>

            <div class="brand-row">
                <div class="brand-mark">M</div>
                <div>
                    <div class="brand-name-text">Mindware</div>
                    <div class="brand-sub">Recruitment Platform</div>
                </div>
            </div>

            <h1 class="reg-h1">Create your candidate account</h1>
            <p class="reg-sub">Join our trusted recruitment platform — it's free.</p>

            <!-- Error -->
            <div x-show="error"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="alert alert-error">
                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <span x-text="error"></span>
            </div>

            <!-- Success -->
            <div x-show="success"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="alert alert-success">
                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span x-text="success"></span>
            </div>

            <form @submit.prevent="submitRegistration()">

                <!-- Email -->
                <div class="f-group">
                    <label class="f-label">Email Address <span class="req">*</span></label>
                    <div class="f-wrap">
                        <input type="email"
                               x-model="formData.email"
                               @input="validateEmail()"
                               @blur="validateEmail()"
                               required
                               placeholder="your@email.com"
                               :class="(emailValid && formData.email) ? 'v-valid' : (emailError ? 'v-invalid' : '')"
                               class="f-input">
                    </div>
                    <p class="f-hint">We'll use this to create your account. You can add more details after registration.</p>
                    <p x-show="emailError" class="f-err">
                        <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <span x-text="emailError"></span>
                    </p>
                </div>

                <!-- Password -->
                <div class="f-group">
                    <label class="f-label">Password <span class="req">*</span></label>
                    <div class="f-wrap">
                        <input :type="showPassword ? 'text' : 'password'"
                               x-model="formData.password"
                               @input="checkPasswordStrength()"
                               required
                               placeholder="Create a strong password"
                               :class="passwordValid ? 'v-valid' : (passwordError ? 'v-invalid' : '')"
                               class="f-input has-eye">
                        <button type="button" class="eye-btn" @click="showPassword = !showPassword" aria-label="Toggle password">
                            <svg x-show="!showPassword" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showPassword" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.717m0 0L21 21"/></svg>
                        </button>
                    </div>

                    <!-- Strength bar -->
                    <div x-show="formData.password.length > 0" class="strength-wrap" style="animation:fadeSlide .2s ease">
                        <div class="s-bar" :class="passwordStrengthClass"></div>
                        <div class="s-bar-row">
                            <span class="s-bar-label" :class="passwordStrengthTextClass" x-text="passwordStrengthText"></span>
                            <span class="s-bar-chars" x-text="formData.password.length + ' / 20 characters'"></span>
                        </div>
                    </div>

                    <!-- Requirements -->
                    <div x-show="formData.password.length > 0" class="req-panel">
                        <div class="req-head">Password Requirements</div>
                        <div class="req-row" :class="passwordChecks.lowercase ? 'ok' : 'no'">
                            <span x-show="passwordChecks.lowercase" class="ico-check"></span>
                            <span x-show="!passwordChecks.lowercase" class="ico-cross"></span>
                            At least one lowercase letter (a-z)
                        </div>
                        <div class="req-row" :class="passwordChecks.uppercase ? 'ok' : 'no'">
                            <span x-show="passwordChecks.uppercase" class="ico-check"></span>
                            <span x-show="!passwordChecks.uppercase" class="ico-cross"></span>
                            At least one uppercase letter (A-Z)
                        </div>
                        <div class="req-row" :class="passwordChecks.number ? 'ok' : 'no'">
                            <span x-show="passwordChecks.number" class="ico-check"></span>
                            <span x-show="!passwordChecks.number" class="ico-cross"></span>
                            At least one number (0-9)
                        </div>
                        <div class="req-row" :class="passwordChecks.special ? 'ok' : 'no'">
                            <span x-show="passwordChecks.special" class="ico-check"></span>
                            <span x-show="!passwordChecks.special" class="ico-cross"></span>
                            At least one special character (!@#$%^&*…)
                        </div>
                        <div class="req-row" :class="passwordChecks.length ? 'ok' : 'no'">
                            <span x-show="passwordChecks.length" class="ico-check"></span>
                            <span x-show="!passwordChecks.length" class="ico-cross"></span>
                            Between 8 and 20 characters (NIST recommended)
                        </div>
                        <div class="req-row" :class="passwordChecks.noCommon ? 'ok' : 'no'">
                            <span x-show="passwordChecks.noCommon" class="ico-check"></span>
                            <span x-show="!passwordChecks.noCommon" class="ico-cross"></span>
                            Not a common password (e.g., "password123")
                        </div>

                        <div x-show="!passwordValid && formData.password.length > 0" class="sug-box">
                            <div class="sug-title">💡 Password Suggestions:</div>
                            <ul class="sug-list">
                                <template x-for="suggestion in passwordSuggestions" :key="suggestion">
                                    <li x-text="suggestion"></li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <p x-show="passwordError" class="f-err" style="margin-top:7px">
                        <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <span x-text="passwordError"></span>
                    </p>
                </div>

                <!-- Confirm Password -->
                <div class="f-group">
                    <label class="f-label">Confirm Password <span class="req">*</span></label>
                    <div class="f-wrap">
                        <input :type="showConfirmPassword ? 'text' : 'password'"
                               x-model="formData.password_confirm"
                               @input="validatePasswordMatch()"
                               required
                               placeholder="Re-enter your password"
                               :class="(passwordMatch && formData.password_confirm) ? 'v-valid' : (passwordMatchError ? 'v-invalid' : '')"
                               class="f-input has-eye">
                        <button type="button" class="eye-btn" @click="showConfirmPassword = !showConfirmPassword" aria-label="Toggle confirm password">
                            <svg x-show="!showConfirmPassword" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showConfirmPassword" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.717m0 0L21 21"/></svg>
                        </button>
                    </div>
                    <p x-show="passwordMatch && formData.password_confirm.length > 0" class="f-ok">
                        <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Passwords match
                    </p>
                    <p x-show="passwordMatchError" class="f-err">
                        <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <span x-text="passwordMatchError"></span>
                    </p>
                </div>

                <!-- Social OAuth -->
                <div class="divider">
                    <div class="div-line"></div>
                    <span class="div-text">Or continue with</span>
                    <div class="div-line"></div>
                </div>
                <div class="social-grid">
                    <a href="/auth/google?redirect=/candidate/dashboard" class="soc-btn" aria-label="Continue with Google">
                        <img alt="Google" src="https://www.gstatic.com/images/branding/product/1x/googleg_48dp.png">
                    </a>
                    <a href="/auth/facebook?redirect=/candidate/dashboard" class="soc-btn" aria-label="Continue with Facebook">
                        <svg viewBox="0 0 24 24"><path fill="#1877F2" d="M24 12.073C24 5.403 18.627 0 12 0S0 5.403 0 12.073C0 18.09 4.388 23.092 10.125 24v-8.437H7.078V12.07h3.047V9.412c0-3.007 1.792-4.667 4.533-4.667 1.313 0 2.686.235 2.686.235v2.955h-1.513c-1.49 0-1.953.93-1.953 1.887v2.248h3.328l-.532 3.493h-2.796V24C19.612 23.092 24 18.09 24 12.073z"/><path fill="#fff" d="M16.906 15.563l.532-3.493h-3.328V9.822c0-.957.463-1.887 1.953-1.887h1.513V4.98s-1.373-.235-2.686-.235c-2.741 0-4.533 1.66-4.533 4.667v2.658H7.078v3.055h3.047V24h3.984v-8.437h2.796z"/></svg>
                    </a>
                    <a href="/auth/linkedin?redirect=/candidate/dashboard" class="soc-btn" aria-label="Continue with LinkedIn">
                        <svg viewBox="0 0 24 24"><rect width="24" height="24" rx="4" fill="#0A66C2"/><path fill="#fff" d="M6.21 9.03h2.61v8.16H6.21V9.03zm1.31-4.22c.84 0 1.52.68 1.52 1.52s-.68 1.52-1.52 1.52-1.52-.68-1.52-1.52.68-1.52 1.52-1.52zM10.28 9.03h2.5v1.12h.04c.35-.66 1.19-1.36 2.45-1.36 2.62 0 3.1 1.72 3.1 3.95v4.44h-2.6v-3.93c0-.94-.02-2.16-1.32-2.16-1.32 0-1.52 1.03-1.52 2.09v4H10.28V9.03z"/></svg>
                    </a>
                    <a href="/auth/microsoft?redirect=/candidate/dashboard" class="soc-btn" aria-label="Continue with Microsoft">
                        <svg viewBox="0 0 24 24"><rect x="2" y="2" width="9" height="9" fill="#F25022"/><rect x="13" y="2" width="9" height="9" fill="#7FBA00"/><rect x="2" y="13" width="9" height="9" fill="#00A4EF"/><rect x="13" y="13" width="9" height="9" fill="#FFB900"/></svg>
                    </a>
                </div>

                <!-- Terms -->
                <div class="terms-row">
                    <input type="checkbox" id="agree_terms" x-model="formData.agree_terms" required class="terms-cb">
                    <label for="agree_terms" class="terms-txt">
                        I agree to the <a href="#">Terms and Conditions</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>

                <!-- Submit -->
                <button type="submit"
                        :disabled="isSubmitting || !passwordValid || !passwordMatch || !emailValid || !formData.agree_terms"
                        class="submit-btn">
                    <template x-if="!isSubmitting">
                        <span style="display:flex;align-items:center;gap:7px;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            Create Account
                        </span>
                    </template>
                    <template x-if="isSubmitting">
                        <span style="display:flex;align-items:center;gap:7px;">
                            <div class="spinner"></div>
                            Creating Account...
                        </span>
                    </template>
                </button>

                <div class="reg-footer">
                    Already have an account? <a href="/login">Sign in</a>
                </div>

            </form>
        </div>
    </div>

</div><!-- /page-grid -->

<!-- ─── Slider JS ─── -->
<script>
    const SLIDE_MS = 5000;
    let cur = 0, total = 3, timer = null, pStart = null;
    const slides = document.querySelectorAll('.slide');
    const dots   = document.querySelectorAll('.s-dot');
    const bar    = document.getElementById('progressBar');

    function goToSlide(i) {
        slides[cur].classList.remove('active');
        slides[cur].classList.add('exiting');
        dots[cur].classList.remove('active');
        const prev = cur;
        setTimeout(() => slides[prev].classList.remove('exiting'), 650);
        cur = (i + total) % total;
        slides[cur].classList.add('active');
        dots[cur].classList.add('active');
        resetProgress();
    }
    function nextSlide() { goToSlide(cur + 1); }
    function prevSlide() { goToSlide(cur - 1); }
    function resetProgress() {
        clearInterval(timer);
        pStart = Date.now();
        bar.style.width = '0%';
        bar.style.transition = 'none';
        requestAnimationFrame(() => {
            timer = setInterval(() => {
                const pct = Math.min(((Date.now() - pStart) / SLIDE_MS) * 100, 100);
                bar.style.transition = 'width .1s linear';
                bar.style.width = pct + '%';
                if (pct >= 100) { clearInterval(timer); nextSlide(); }
            }, 100);
        });
    }
    resetProgress();
</script>

<!-- ─── Alpine Registration Logic (100% original, untouched) ─── -->
<script>
    function registrationForm() {
        return {
            isSubmitting: false,
            error: '',
            success: '',
            showPassword: false,
            showConfirmPassword: false,
            emailValid: true,
            emailError: '',
            passwordValid: false,
            passwordError: '',
            passwordMatch: false,
            passwordMatchError: '',
            passwordStrength: 0,
            passwordStrengthText: '',
            passwordStrengthTextClass: '',
            passwordStrengthClass: '',
            passwordChecks: {
                lowercase: false,
                uppercase: false,
                number: false,
                special: false,
                length: false,
                noCommon: false
            },
            passwordSuggestions: [],
            formData: {
                email: '',
                password: '',
                password_confirm: '',
                agree_terms: false
            },

            validateEmail() {
                const email = this.formData.email;
                if (!email) { this.emailValid = true; this.emailError = ''; return; }
                const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
                if (!emailRegex.test(email)) {
                    this.emailValid = false;
                    this.emailError = 'Please enter a valid email address';
                } else {
                    this.emailValid = true;
                    this.emailError = '';
                }
            },

            checkPasswordStrength() {
                const password = this.formData.password;
                this.passwordChecks = {
                    lowercase: /[a-z]/.test(password),
                    uppercase: /[A-Z]/.test(password),
                    number: /[0-9]/.test(password),
                    special: /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(password),
                    length: password.length >= 8 && password.length <= 20,
                    noCommon: !this.isCommonPassword(password)
                };
                let score = 0;
                if (this.passwordChecks.lowercase) score += 15;
                if (this.passwordChecks.uppercase) score += 15;
                if (this.passwordChecks.number) score += 15;
                if (this.passwordChecks.special) score += 20;
                if (this.passwordChecks.length) score += 20;
                if (this.passwordChecks.noCommon) score += 15;
                if (password.length >= 12) score += 5;
                if (password.length >= 16) score += 5;
                this.passwordStrength = score;
                if (score < 30) {
                    this.passwordStrengthText = 'Very Weak'; this.passwordStrengthTextClass = 'text-red-600'; this.passwordStrengthClass = 'strength-weak';
                } else if (score < 50) {
                    this.passwordStrengthText = 'Weak'; this.passwordStrengthTextClass = 'text-orange-600'; this.passwordStrengthClass = 'strength-weak';
                } else if (score < 70) {
                    this.passwordStrengthText = 'Fair'; this.passwordStrengthTextClass = 'text-yellow-600'; this.passwordStrengthClass = 'strength-fair';
                } else if (score < 85) {
                    this.passwordStrengthText = 'Good'; this.passwordStrengthTextClass = 'text-blue-600'; this.passwordStrengthClass = 'strength-good';
                } else {
                    this.passwordStrengthText = 'Strong'; this.passwordStrengthTextClass = 'text-green-600'; this.passwordStrengthClass = 'strength-strong';
                }
                this.generatePasswordSuggestions();
                this.passwordValid = Object.values(this.passwordChecks).every(check => check === true);
                this.passwordError = (!this.passwordValid && password.length > 0) ? 'Password does not meet all requirements' : '';
                this.validatePasswordMatch();
            },

            isCommonPassword(password) {
                const commonPasswords = ['password','password123','12345678','123456789','1234567890','qwerty123','admin123','letmein','welcome123','monkey123','dragon','master','sunshine','princess','football'];
                return commonPasswords.includes(password.toLowerCase());
            },

            generatePasswordSuggestions() {
                this.passwordSuggestions = [];
                if (!this.passwordChecks.lowercase) this.passwordSuggestions.push('Add lowercase letters (a-z)');
                if (!this.passwordChecks.uppercase) this.passwordSuggestions.push('Add uppercase letters (A-Z)');
                if (!this.passwordChecks.number) this.passwordSuggestions.push('Add numbers (0-9)');
                if (!this.passwordChecks.special) this.passwordSuggestions.push('Add special characters (!@#$%^&*)');
                if (!this.passwordChecks.length) {
                    if (this.formData.password.length < 8) this.passwordSuggestions.push('Make it at least 8 characters long');
                    else this.passwordSuggestions.push('Keep it under 20 characters');
                }
                if (!this.passwordChecks.noCommon) this.passwordSuggestions.push('Avoid common passwords - use a unique combination');
                if (this.passwordChecks.length && this.formData.password.length < 12) this.passwordSuggestions.push('Consider making it 12+ characters for better security');
            },

            validatePasswordMatch() {
                if (!this.formData.password_confirm) { this.passwordMatch = false; this.passwordMatchError = ''; return; }
                if (this.formData.password === this.formData.password_confirm) {
                    this.passwordMatch = true; this.passwordMatchError = '';
                } else {
                    this.passwordMatch = false; this.passwordMatchError = 'Passwords do not match';
                }
            },

            async submitRegistration() {
                this.error = ''; this.success = '';
                this.validateEmail();
                this.checkPasswordStrength();
                this.validatePasswordMatch();
                if (!this.emailValid) { this.error = 'Please enter a valid email address'; return; }
                if (!this.passwordValid) { this.error = 'Password does not meet all security requirements'; return; }
                if (!this.passwordMatch) { this.error = 'Passwords do not match'; return; }
                if (!this.formData.agree_terms) { this.error = 'Please agree to the Terms and Conditions'; return; }
                this.isSubmitting = true;
                try {
                    const response = await fetch('/register-candidate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-Token': this.getCsrfToken()
                        },
                        body: JSON.stringify(this.formData)
                    });
                    const data = await response.json();
                    // Handle wrapped response format from Response::json()
                    const isSuccess = response.ok && (data.status === true || data.data?.success === true);
                    const successMsg = data.message || data.data?.message || 'Registration successful! Please check your email for confirmation. Redirecting to login...';
                    const redirectUrl = data.data?.redirect || data.redirect || '/login?registered=1';
                    
                    if (isSuccess) {
                        this.success = successMsg;
                        setTimeout(() => { window.location.href = redirectUrl; }, 3000);
                    } else {
                        const errorData = data.data || data;
                        if (errorData.errors) {
                            const errorMessages = Object.values(errorData.errors).flat();
                            this.error = errorMessages.join(', ');
                        } else {
                            this.error = errorData.error || errorData.message || 'Registration failed. Please try again.';
                        }
                    }
                } catch (error) {
                    this.error = 'An error occurred. Please try again.';
                    console.error('Registration error:', error);
                } finally {
                    this.isSubmitting = false;
                }
            },

            getCsrfToken() {
                return document.querySelector('meta[name="csrf-token"]')?.content || '';
            }
        }
    }
</script>

</body>
</html>