<?php
$cfgPath = __DIR__ . '/../../config/config.php';
$base = '/'; // fallback
if (file_exists($cfgPath)) {
    $cfg = require $cfgPath;
    if (!empty($cfg['base_url'])) $base = rtrim($cfg['base_url'], '/') . '/';
}
$stats = $stats ?? [];
$totalJobs = $stats['jobs'] ?? 0;
$totalCandidates = $stats['candidates'] ?? 0;
$totalCompanies = $stats['companies'] ?? 0;
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Find Jobs & Hire Talent | Jobsence</title>
    <meta name="description"
        content="Find your dream job or hire top talent. Connect with verified employers and skilled candidates. Browse thousands of job openings across all industries." />
    <meta name="keywords" content="jobs, employment, career, hiring, recruitment, job portal, find jobs, hire talent" />
    <meta property="og:title" content="Job Portal - Find Jobs & Hire Talent" />
    <meta property="og:description"
        content="Find your dream job or hire top talent. Connect with verified employers and skilled candidates." />
    <meta property="og:type" content="website" />
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta property="og:url" content="<?= $_ENV['APP_URL'] ?? 'http://localhost:8000' ?>" />
    <link rel="canonical"
        href="<?= htmlspecialchars(($_ENV['APP_URL'] ?? 'http://localhost:8000') . ($_SERVER['REQUEST_URI'] ?? '/')) ?>" />
    <link href="/css/output.css" rel="stylesheet">
    <style>
        :root {
            --color-primary: #f05537;
            --color-primary-hover: #FF6A3D;
            --color-active-menu-bg: #fff1ed;
        }

        body {
            font-family: 'Nunito Sans', sans-serif;
            font-weight: 600;
        }

        .text-primary,
        .text-primary-600,
        .text-primary,
        .text-primary,
        .text-primary,
        .text-primary {
            color: var(--color-primary) !important
        }

        .bg-primary,
        .bg-primary-600,
        .bg-primary,
        .bg-primary,
        .bg-primary,
        .bg-primary {
            background-color: var(--color-primary) !important
        }

        .hover\:bg-primary-600:hover,
        .hover\:bg-primary:hover,
        .hover\:bg-primary:hover,
        .hover\:bg-primary:hover {
            background-color: var(--color-primary-hover) !important
        }

        .bg-primary-50 {
            background-color: var(--color-active-menu-bg) !important
        }

        @keyframes blink {
            50% {
                opacity: 0;
            }
        }

        .blink {
            animation: blink 1s step-start infinite;
        }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        window.userId = <?= json_encode($_SESSION['user_id'] ?? null) ?>;
        window.typedRoles = <?= json_encode($typedRoles ?? [], JSON_UNESCAPED_SLASHES) ?>;
        window.locationDropdown = function() {
            return {
                open: false,
                q: '',
                items: [],
                filteredCache: [],
                selectedLabel: 'Locations',
                selectedValue: '',
                init() {
                    const sel = this.$refs.native;
                    if (sel) {
                        const opts = Array.from(sel.options || []);
                        this.items = opts.slice(1).map(o => ({
                            value: o.value,
                            label: o.text
                        }));
                        const current = sel.options[sel.selectedIndex] || null;
                        this.selectedLabel = current ? current.text : 'Locations';
                        this.selectedValue = current ? current.value : '';
                        sel.addEventListener('change', () => {
                            const cur = sel.options[sel.selectedIndex] || null;
                            this.selectedLabel = cur ? cur.text : 'Locations';
                            this.selectedValue = cur ? cur.value : '';
                        });
                    }

                    // Request Location Permission & Auto-select
                    if ("geolocation" in navigator) {
                        navigator.geolocation.getCurrentPosition(
                            (pos) => this.handleGeoSuccess(pos),
                            (err) => console.log("Location access denied or error:", err)
                        );
                    }

                    // Request Notification Permission and Register if logged in
                    if ("Notification" in window) {
                        if (Notification.permission === 'granted') {
                            this.registerPush();
                        } else if (Notification.permission !== 'denied') {
                            Notification.requestPermission().then(permission => {
                                if (permission === "granted") {
                                    console.log("Notification permission granted");
                                    this.registerPush();
                                }
                            });
                        }
                    }

                    this.$watch('q', value => {
                        const q = (value || '').trim().toLowerCase();
                        const src = this.items || [];
                        this.filteredCache = q ? src.filter(i => (i.label || '').toLowerCase().includes(q)).slice(0, 50) :
                            src.slice(0, 12);
                    });
                    this.$watch('items', () => {
                        const q = (this.q || '').trim().toLowerCase();
                        const src = this.items || [];
                        this.filteredCache = q ? src.filter(i => (i.label || '').toLowerCase().includes(q)).slice(0, 50) :
                            src.slice(0, 12);
                    });
                },
                async registerPush() {
                    if (!window.userId) return; // Only register if logged in

                    try {
                        if (!('serviceWorker' in navigator) || !window.firebase) return;

                        // Register service worker if not already
                        const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');

                        const messaging = window.firebase.messaging();
                        const token = await messaging.getToken({
                            serviceWorkerRegistration: registration
                        });

                        if (token) {
                            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                            await fetch('/api/push/register', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-Token': csrf
                                },
                                body: JSON.stringify({
                                    token: token
                                })
                            });
                            console.log('Push token registered successfully');
                        }
                    } catch (e) {
                        console.error('Push registration failed:', e);
                    }
                },
                handleGeoSuccess(pos) {
                    const lat = pos.coords.latitude;
                    const lon = pos.coords.longitude;
                    const url = `/api/geo/reverse?lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lon)}`;
                    const doFetch = (attempt = 1) => {
                        fetch(url, {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data && data.address) {
                                    const country = data.address.country || '';
                                    const city = data.address.city || data.address.town || data.address.village || '';
                                    const state = data.address.state || '';

                                    const match = this.items.find(i => {
                                        const label = (i.label || '').toLowerCase();
                                        return (city && label.includes((city || '').toLowerCase())) ||
                                            (state && label.includes((state || '').toLowerCase())) ||
                                            (country && label === (country || '').toLowerCase());
                                    });

                                    if (match) {
                                        this.selectItem(match);
                                        const sel = this.$refs.native;
                                        if (sel) {
                                            sel.value = match.value;
                                            sel.dispatchEvent(new Event('change'));
                                        }
                                    }
                                }
                            })
                            .catch(e => {
                                if (attempt < 2) {
                                    setTimeout(() => doFetch(attempt + 1), 800);
                                } else {
                                    console.error("Geocoding error:", e);
                                }
                            });
                    };
                    doFetch(1);
                },
                filteredItems() {
                    if (Array.isArray(this.filteredCache) && this.filteredCache.length) return this.filteredCache;
                    const q = (this.q || '').trim().toLowerCase();
                    const src = this.items || [];
                    const result = q ? src.filter(i => (i.label || '').toLowerCase().includes(q)).slice(0, 50) :
                        src.slice(0, 12);
                    this.filteredCache = result;
                    return result;
                },
                selectItem(item) {
                    this.selectedLabel = item.label;
                    this.selectedValue = item.value;
                    const sel = this.$refs.native;
                    if (sel) {
                        let idx = 0;
                        for (let i = 0; i < sel.options.length; i++) {
                            if (sel.options[i].value === item.value) {
                                idx = i;
                                break;
                            }
                        }
                        sel.selectedIndex = idx;
                        sel.dispatchEvent(new Event('change'));
                    }
                    this.open = false;
                }
            };
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito Sans', sans-serif;
            font-weight: 600;
        }

        /* Custom height to be full viewport height minus the header height */
        .hero-height {
            min-height: auto;
        }

        /* Custom scrollbar utility for the card carousel */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        /* Keyframe for a subtle float effect on the logo */
        @keyframes subtle-float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-2px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .animate-subtle-float {
            animation: subtle-float 4s ease-in-out infinite;
        }

        /* Override Tailwind container max-width at >=1536px */
        @media (min-width: 1536px) {
            .home-wide .container {
                max-width: 100% !important;
            }
        }

        .scroll-fade-up {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity 600ms ease, transform 600ms ease;
        }

        .scroll-fade-up.in-view {
            opacity: 1;
            transform: translateY(0);
        }

        @keyframes spin-slower {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .orbit-wrap {
            animation: spin-slower 32s linear infinite;
        }

        [x-cloak] {
            display: none !important;
        }

        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 8px;
            border: 2px solid transparent;
            background-clip: content-box;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: #94a3b8;
        }

        /* ── INFINITE MARQUEE ── */
        @keyframes marquee-infinite {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .animate-marquee-infinite {
            display: flex;
            width: max-content;
            animation: marquee-infinite 40s linear infinite;
        }
        .animate-marquee-infinite:hover {
            animation-play-state: paused;
        }
    </style>
</head>

<body class="bg-gray-50 antialiased text-gray-800" x-data="{ loaded: false }"
    x-init="setTimeout(() => loaded = true, 800)">
    <!-- Skeleton Loader -->
    <div x-show="!loaded" x-cloak x-transition.opacity.duration.500ms
        class="fixed inset-0 bg-white z-50 flex flex-col overflow-hidden">
        <!-- Header Skeleton -->
        <div class="h-20 border-b border-gray-100 flex items-center px-4 lg:px-8 justify-between bg-white shrink-0">
            <div class="flex items-center gap-4">
                <!-- Mobile Menu Button Skeleton -->
                <div class="w-10 h-10 bg-gray-200 rounded animate-pulse xl:hidden"></div>
                <div class="w-40 h-10 bg-gray-200 rounded animate-pulse"></div>
            </div>
            <div class="hidden xl:flex gap-8">
                <div class="w-20 h-4 bg-gray-200 rounded animate-pulse"></div>
                <div class="w-20 h-4 bg-gray-200 rounded animate-pulse"></div>
                <div class="w-20 h-4 bg-gray-200 rounded animate-pulse"></div>
            </div>
            <div class="flex gap-4">
                <div class="w-24 h-10 bg-gray-200 rounded animate-pulse"></div>
                <div class="w-24 h-10 bg-gray-200 rounded animate-pulse"></div>
            </div>
        </div>

        <!-- Hero Skeleton -->
        <div class="flex-1 bg-white relative animate-pulse flex items-center justify-center">
            <div class="w-full max-w-4xl px-6 flex flex-col items-center">
                <div class="w-3/4 h-16 bg-gray-200 rounded-lg mb-8"></div>
                <div class="w-1/2 h-6 bg-gray-200 rounded mb-10"></div>

                <!-- Search Bar Skeleton -->
                <div class="w-full h-20 bg-white rounded-2xl shadow-sm mb-12"></div>

                <!-- Stats Skeleton -->
                <div class="flex gap-8 mt-8">
                    <div class="w-32 h-20 bg-gray-200 rounded-lg"></div>
                    <div class="w-32 h-20 bg-gray-200 rounded-lg"></div>
                    <div class="w-32 h-20 bg-gray-200 rounded-lg"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="home-wide">
        <?php require __DIR__ . '/front/apply/_buttons.php'; ?>
        <?php require __DIR__ . '/include/_featured.php'; ?>
        <?php require __DIR__ . '/include/_login_cards.php'; ?>
        <?php $quotesMode = 'rotate'; require __DIR__ . '/include/_skill_quotes.php'; ?>
        <?php $sdShowcaseLimit = 12; require __DIR__ . '/front/skill-development/_skills_showcase.php'; ?>
        <section class="relative hero-height bg-white text-gray-800">
            <div class="relative container mx-auto px-4 lg:px-8 pt-16 md:pt-20 pb-6">

                <h2 class="text-3xl md:text-[2.85rem] font-extrabold tracking-tight text-black text-center leading-none">
                    Handpicked Premium
                    <span id="hero-word" class="text-primary"></span>
                    <span id="hero-caret" class="text-primary blink">|</span>
                    Jobs
                </h2>
                <script>
                    (function() {
                        const words = <?= json_encode($typedRoles ?? ['Software', 'AI', 'Data Science', 'Full Stack', 'Cloud']) ?>;
                        const el = document.getElementById('hero-word');
                        const caret = document.getElementById('hero-caret');
                        if (!el || !caret || !Array.isArray(words) || words.length === 0) return;

                        let i = 0,
                            j = 0,
                            deleting = false;

                        const colorPalette = [
                            '#f97316', // orange
                            '#2563eb', // blue
                            '#059669', // emerald
                            '#dc2626', // red
                            '#7c3aed', // violet
                            '#db2777', // pink
                            '#0d9488', // teal
                            '#ca8a04', // yellow
                            '#4f46e5', // indigo
                            '#ea580c' // deep orange
                        ];

                        function hashWord(word) {
                            const str = String(word || '').trim().toLowerCase();
                            let hash = 0;
                            for (let k = 0; k < str.length; k++) {
                                hash = ((hash << 5) - hash) + str.charCodeAt(k);
                                hash |= 0;
                            }
                            return Math.abs(hash);
                        }

                        function pickWordColor(word) {
                            const idx = hashWord(word) % colorPalette.length;
                            return colorPalette[idx] || '#f05537';
                        }

                        function applyWordColor(word) {
                            const color = pickWordColor(word);
                            el.style.setProperty('color', color, 'important');
                            caret.style.setProperty('color', color, 'important');
                        }

                        function tick() {
                            const w = words[i];
                            applyWordColor(w);

                            if (!deleting) {
                                j++;
                                el.textContent = w.slice(0, j);
                                if (j === w.length) {
                                    deleting = true;
                                    setTimeout(tick, 1200);
                                    return;
                                }
                            } else {
                                j--;
                                el.textContent = w.slice(0, j);
                                if (j === 0) {
                                    deleting = false;
                                    i = (i + 1) % words.length;
                                }
                            }
                            setTimeout(tick, deleting ? 50 : 90);
                        }

                        tick();
                    })();
                </script>

                <p class="mt-3 text-gray-600 text-center">Connecting Talent with Opportunity; Your Gateway to Career
                    Success</p>
                <div class="mt-10 max-w-6xl mx-auto px-4">

                    <!-- ================= MOBILE SEARCH ================= -->
                    <div class="md:hidden space-y-4">

                        <!-- Keyword -->
                        <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input id="home-keyword-mobile"
                                type="text"
                                placeholder="Job title, company, or keyword"
                                class="w-full bg-transparent focus:outline-none text-gray-700 placeholder-gray-400 font-medium" />
                        </div>

                        <!-- Location -->
                        <div x-data="locationDropdown()" class="relative">
                            <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm font-medium text-gray-700">
                                <span x-text="selectedLabel"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform"
                                    :class="open ? 'rotate-180' : ''"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition
                                class="absolute left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-2xl z-50">
                                <div class="p-3 border-b">
                                    <input type="text" x-model="q" placeholder="Search location"
                                        class="w-full px-3 py-2 rounded-lg border text-sm focus:ring-2 focus:ring-primary" />
                                </div>
                                <ul class="max-h-64 overflow-auto p-1 custom-scrollbar">
                                    <template x-for="item in filteredItems()" :key="item.value">
                                        <li @click="selectItem(item)"
                                            class="px-3 py-2 rounded-lg cursor-pointer hover:bg-primary-50 flex justify-between">
                                            <span x-text="item.label"></span>
                                            <span x-show="item.value === selectedValue" class="text-primary">✔</span>
                                        </li>
                                    </template>
                                </ul>
                            </div>

                            <select x-ref="native" id="home-location-mobile" class="hidden">
                                <option>Locations</option>
                                <?php foreach ($locations ?? [] as $loc): ?>
                                    <option value="<?= htmlspecialchars(($loc['city'] ?? '') . ',' . ($loc['state'] ?? '') . ',' . ($loc['country'] ?? '')) ?>">
                                        <?= htmlspecialchars($loc['display_name'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Experience (Custom Dropdown with Search) -->
                        <div class="bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm relative"
                            x-data="{
                                open: false,
                                search: '',
                                selectedLabel: 'Any Exp. Level',
                                selectedValue: '',
                                options: [
                                    { label: 'Any Exp. Level', value: '' },
                                    { label: '0 – 1 years', value: '0-1' },
                                    { label: '2 – 3 years', value: '2-3' },
                                    { label: '4 – 6 years', value: '4-6' },
                                    { label: '7 – 10 years', value: '7-10' },
                                    { label: '11 – 15 years', value: '11-15' },
                                    { label: '16 – 20 years', value: '16-20' },
                                    { label: '21 – 25 years', value: '21-25' },
                                    { label: '26+ years', value: '26+' }
                                ],
                                get filtered() {
                                    if (!this.search) return this.options;
                                    return this.options.filter(opt =>
                                        opt.label.toLowerCase().includes(this.search.toLowerCase())
                                    );
                                },
                                select(opt) {
                                    this.selectedLabel = opt.label;
                                    this.selectedValue = opt.value;
                                    this.open = false;
                                }
                             }">
                            <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between font-medium text-gray-500">
                                <span x-text="selectedLabel"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            </button>

                            <div x-show="open" x-transition.opacity.duration.200ms @click.away="open = false"
                                class="absolute left-0 right-0 top-full mt-2 bg-white border border-gray-200 rounded-xl shadow-2xl z-50 p-2">
                                <input type="text" x-model="search" placeholder="Filter experience..."
                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary mb-2">
                                <ul class="max-h-64 overflow-auto custom-scrollbar">
                                    <template x-for="opt in filtered" :key="opt.value">
                                        <li @click="select(opt)"
                                            class="px-3 py-2 rounded-lg cursor-pointer hover:bg-primary-50 flex justify-between items-center transition-colors">
                                            <span x-text="opt.label" class="text-sm font-medium text-gray-700"></span>
                                            <span x-show="selectedValue === opt.value" class="text-primary">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                            </span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            <!-- Hidden input for compatibility -->
                            <input type="hidden" id="home-exp-mobile" :value="selectedValue">
                        </div>


                        <!-- Search Button -->
                        <button type="button"
                            onclick="(function(){
                                    var k=document.getElementById('home-keyword-mobile').value||'';
                                    var l=document.getElementById('home-location-mobile').value||'';
                                    var e=document.getElementById('home-exp-mobile').value||'';
                                    var qs=[];
                                    if(k)qs.push('keyword='+encodeURIComponent(k));
                                    if(l)qs.push('location='+encodeURIComponent(l));
                                    if(e && e!=='Experience')qs.push('experience='+encodeURIComponent(e));
                                    try{ if(window.MWMarketing){ MWMarketing.trackSearch({search_string:k||'', location:l||'', experience:e||'', content_category:'jobs'});} }catch(_){}
                                    location.href='<?= $base ?>jobs'+(qs.length?'?'+qs.join('&'):'');
                                    })()"
                            class="w-full bg-primary hover:bg-primary-600 text-white font-semibold py-3 rounded-xl shadow-md transition">
                            Search Jobs
                        </button>
                    </div>

                    <!-- ================= DESKTOP SEARCH ================= -->
                    <div class="hidden md:flex items-center bg-white rounded-full shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300 p-2 max-w-5xl mx-auto ring-1 ring-gray-50">

                        <!-- Keyword -->
<!-- restored: keyword suggestions for the hero search -->
                        <script>
                            function homeKeywordSuggest() {
                                return {
                                    show: false,
                                    list: [],
                                    selectedIndex: -1,
                                    searchTimeout: null,
                                    async search(q) {
                                        if (!q || q.length < 2) {
                                            this.list = [];
                                            this.show = false;
                                            return;
                                        }
                                        if (this.searchTimeout) clearTimeout(this.searchTimeout);
                                        this.searchTimeout = setTimeout(async () => {
                                            try {
                                                const res = await fetch('<?= $base ?>api/job-titles/search?q=' + encodeURIComponent(q) + '&limit=8');
                                                const data = await res.json();
                                                this.list = Array.isArray(data.suggestions) ? data.suggestions : [];
                                                this.show = this.list.length > 0;
                                            } catch (e) {
                                                this.list = [];
                                                this.show = false;
                                            }
                                        }, 150);
                                    },
                                    select(s) {
                                        const el = document.getElementById('home-keyword-desktop');
                                        if (el && s && s.title) el.value = s.title;
                                        this.show = false;
                                        this.list = [];
                                    },
                                    handleKey(e) {
                                        if (!this.show || this.list.length === 0) return;
                                        if (e.key === 'ArrowDown') {
                                            e.preventDefault();
                                            this.selectedIndex = Math.min(this.selectedIndex + 1, this.list.length - 1);
                                        } else if (e.key === 'ArrowUp') {
                                            e.preventDefault();
                                            this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
                                        } else if (e.key === 'Enter' && this.selectedIndex >= 0) {
                                            e.preventDefault();
                                            this.select(this.list[this.selectedIndex]);
                                        } else if (e.key === 'Escape') {
                                            this.show = false;
                                        }
                                    }
                                }
                            }
                        </script>
                        <div class="flex items-center flex-1 px-4 relative group" x-data="homeKeywordSuggest()">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-primary transition-colors mr-3 shrink-0"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input id="home-keyword-desktop"
                                type="text"
                                placeholder="Job title, company, or keyword"
                                class="w-full bg-transparent focus:outline-none text-gray-700 font-medium placeholder-gray-400 group-hover:placeholder-gray-500 transition-colors"
                                @input="search($event.target.value)"
                                @keydown="handleKey($event)"
                                @focus="if($event.target.value && $event.target.value.length>=2) search($event.target.value)"
                                @blur="setTimeout(()=>show=false,200)" />
                            <div x-show="show && list.length>0" x-cloak
                                class="absolute left-0 right-0 top-full mt-4 bg-white border border-gray-100 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] z-50 max-h-72 overflow-auto py-2">
                                <template x-for="(s, i) in list" :key="s.id ?? i">
                                    <div @click="select(s)" @mouseenter="selectedIndex=i"
                                        class="px-5 py-3 cursor-pointer hover:bg-primary-50/50 transition-colors flex items-center gap-3"
                                        :class="selectedIndex===i ? 'bg-primary-50/50' : ''">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <div class="text-sm font-medium text-gray-700" x-text="s.title"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Vertical Divider -->
                        <div class="w-px h-10 bg-gray-200 mx-2"></div>

                        <!-- Location -->
                        <div x-data="locationDropdown()" class="relative min-w-[240px] px-6 group">
                            <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between font-medium text-gray-700 hover:text-primary transition-colors outline-none">
                                <div class="flex items-center gap-3 truncate">
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-primary transition-colors shrink-0"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span x-text="selectedLabel" class="truncate"></span>
                                </div>
                                <svg class="w-4 h-4 text-gray-300 group-hover:text-primary transition-colors"
                                    :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition.opacity.duration.200ms
                                @click.away="open = false"
                                class="absolute left-0 right-0 mt-6 bg-white border border-gray-100 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] z-50 overflow-hidden">
                                <div class="p-3 border-b border-gray-50 bg-gray-50/50">
                                    <div class="relative">
                                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <input type="text" x-model="q" placeholder="Filter locations..."
                                            style="border-color: #e5e7eb; outline: none;"
                                            onfocus="this.style.borderColor='#f05537'; this.style.boxShadow='0 0 0 2px rgba(240, 85, 55, 0.2)'"
                                            onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'"
                                            class="w-full pl-9 pr-3 py-2.5 rounded-xl border text-sm bg-white" />
                                    </div>
                                </div>
                                <ul class="max-h-64 overflow-auto p-2 custom-scrollbar">
                                    <template x-for="item in filteredItems()" :key="item.value">
                                        <li @click="selectItem(item)"
                                            class="px-4 py-2.5 rounded-lg hover:bg-primary-50 cursor-pointer flex justify-between items-center group/item transition-colors">
                                            <span x-text="item.label"
                                                class="text-sm text-gray-700 group-hover/item:text-primary-600 font-medium"></span>
                                            <span x-show="item.value === selectedValue" class="text-primary">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                            </span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            <select x-ref="native" id="home-location-desktop" class="hidden">
                                <option>Locations</option>
                                <?php foreach ($locations ?? [] as $loc): ?>
                                    <option value="<?= htmlspecialchars(($loc['city'] ?? '') . ',' . ($loc['state'] ?? '') . ',' . ($loc['country'] ?? '')) ?>">
                                        <?= htmlspecialchars($loc['display_name'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Vertical Divider -->
                        <div class="w-px h-10 bg-gray-200 mx-2"></div>

                        <!-- Experience (Custom Dropdown with Search) -->
                        <div x-data="{
                            open: false, 
                            selected: 'Any Exp. Level', 
                            search: '', 
                            options: [ 
                                'Any Exp. Level', 
                                '0 – 1 years', 
                                '2 – 3 years', 
                                '4 – 6 years', 
                                '7 – 10 years', 
                                '11 – 15 years', 
                                '16 – 20 years', 
                                '21 – 25 years', 
                                '26+ years' 
                            ], 
                            get filtered() { 
                                if (!this.search) return this.options; 
                                return this.options.filter(opt => 
                                    opt.toLowerCase().includes(this.search.toLowerCase()) 
                                ); 
                            } 
                        }" class="relative min-w-[220px] px-6 group">
                            <!-- Trigger -->
                            <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between font-medium text-gray-700 hover:text-primary transition-colors outline-none">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-primary transition-colors shrink-0"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span x-text="selected"></span>
                                </div>

                                <svg class="w-4 h-4 text-gray-300 group-hover:text-primary transition-transform"
                                    :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Hidden input (for backend) -->
                            <input type="hidden" id="home-exp-desktop"
                                :value="selected === 'Any Exp. Level' ? '' : selected">

                            <!-- Dropdown -->
                            <div x-show="open" x-transition.opacity.duration.200ms @click.away="open = false"
                                class="absolute left-0 right-0 mt-6 bg-white border border-gray-100 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] z-50 overflow-hidden">
                                <!-- Search -->
                                <div class="p-3 border-b border-gray-100">
                                    <input type="text" x-model="search" placeholder="Filter experience…"
                                        style="border-color: #e5e7eb; outline: none;"
                                        onfocus="this.style.borderColor='#f05537'; this.style.boxShadow='0 0 0 2px rgba(240, 85, 55, 0.2)'"
                                        onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'"
                                        class="w-full px-4 py-2 text-sm border rounded-xl">
                                </div>

                                <!-- Options -->
                                <div class="max-h-64 overflow-y-auto py-1 custom-scrollbar">
                                    <template x-for="opt in filtered" :key="opt">
                                        <div @click="selected = opt; open = false; search = ''"
                                            class="px-5 py-2.5 cursor-pointer text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition-colors flex justify-between items-center">
                                            <span x-text="opt"></span>

                                            <svg x-show="selected === opt" class="w-4 h-4 text-primary" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </template>

                                    <!-- No results -->
                                    <div x-show="filtered.length === 0" class="px-5 py-3 text-sm text-gray-400">
                                        No results found
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Button -->
                        <button type="button"
                            onclick="(function(){
                                    var k=document.getElementById('home-keyword-desktop').value||'';
                                    var l=document.getElementById('home-location-desktop').value||'';
                                    var e=document.getElementById('home-exp-desktop').value||'';
                                    var qs=[];
                                    if(k)qs.push('keyword='+encodeURIComponent(k));
                                    if(l)qs.push('location='+encodeURIComponent(l));
                                    if(e && e!=='Experience')qs.push('experience='+encodeURIComponent(e));
                                    try{ if(window.MWMarketing){ MWMarketing.trackSearch({search_string:k||'', location:l||'', experience:e||'', content_category:'jobs'});} }catch(_){}
                                    location.href='<?= $base ?>jobs'+(qs.length?'?'+qs.join('&'):'');
                                    })()"
                            class="bg-primary hover:bg-primary-600 text-white font-bold px-10 py-3 rounded-full shadow-lg shadow-orange-500/25 transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                            Search
                        </button>
                    </div>

                </div>


                <!-- Category Slider Job Categories -->
                <div class="mt-14 bg-white py-6 shadow-sm border-t border-b border-gray-100 relative overflow-hidden">
                    <div class="container mx-auto px-6 lg:px-[7.5rem] relative group">
                        
                        <!-- Manual Scroll Buttons -->
                        <button type="button"
                            class="absolute left-4 lg:left-20 top-1/2 -translate-y-1/2 p-2 text-gray-300 hover:text-gray-800 transition z-20"
                            onclick="document.getElementById('cat-scroll').scrollBy({left: -400, behavior: 'smooth'})">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <div id="cat-scroll"
                            class="overflow-x-auto no-scrollbar scroll-smooth px-12 py-1 select-none cursor-grab active:cursor-grabbing">
                            
                            <div class="animate-marquee-infinite gap-8">
                                <?php 
                                $allCats = !empty($categories) && is_array($categories) ? $categories : [
                                    ['name' => 'AI/ML'], ['name' => 'IT / Software'], ['name' => 'Sales & Marketing'], 
                                    ['name' => 'Finance & Accounting'], ['name' => 'Fashion & Apparel'], ['name' => 'Digital Marketing'],
                                    ['name' => 'Cybersecurity'], ['name' => 'Driver']
                                ];
                                
                                // Triple the array for seamless loop
                                $loopedCats = array_merge($allCats, $allCats, $allCats);
                                
                                foreach ($loopedCats as $index => $cat): 
                                ?>
                                    <a href="<?= isset($cat['slug']) ? $base . 'jobs?industry=' . urlencode($cat['slug']) : '#' ?>"
                                        class="group/cat flex flex-col items-center gap-4 min-w-[160px] cursor-pointer py-2">
                                        <div class="w-24 h-24 flex items-center justify-center text-gray-800 transition-transform duration-300 group-hover/cat:-translate-y-0.5">
                                            <?php if (!empty($cat['image'])): ?>
                                                <img loading="lazy" src="<?= htmlspecialchars($cat['image']) ?>"
                                                    alt="<?= htmlspecialchars($cat['name'] ?? '') ?>"
                                                    class="w-24 h-24 object-cover rounded-lg ring-1 ring-gray-200" />
                                            <?php else: ?>
                                                <?= getCategoryIcon($cat['name'] ?? '') ?>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900 group-hover/cat:text-primary transition-colors whitespace-nowrap">
                                            <?= htmlspecialchars($cat['name'] ?? 'Category') ?>
                                        </span>
                                        <span class="text-xs text-gray-600">Active Job
                                            <?= (int)($cat['count'] ?? 0) ?>
                                        </span>
                                        <div class="h-0.5 w-0 bg-primary group-hover/cat:w-16 transition-all duration-300"></div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button type="button"
                            class="absolute right-4 lg:right-20 top-1/2 -translate-y-1/2 p-2 text-primary hover:text-primary-600 transition z-20"
                            onclick="document.getElementById('cat-scroll').scrollBy({left: 400, behavior: 'smooth'})">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>

            </div>

        </section>
        <section class="py-16 bg-white">
            <div class="container mx-auto px-6 lg:px-[7.5rem]">
                <?php if (!empty($jobs) && is_array($jobs)): ?>
                    <div x-data="{
                    active: 0,
                    items: <?= count($jobs) ?>,
                    itemsPerSlide: 1,
                    get max() { return Math.max(0, this.items - this.itemsPerSlide) },
                    next() { if(this.active < this.max) this.active++ },
                    prev() { if(this.active > 0) this.active-- },
                    advance() { this.active = (this.active < this.max) ? this.active + 1 : 0 },
                    updateItemsPerSlide() {
                        if (window.innerWidth >= 1280) this.itemsPerSlide = 3;
                        else if (window.innerWidth >= 1024) this.itemsPerSlide = 2;
                        else if (window.innerWidth >= 768) this.itemsPerSlide = 2;
                        else this.itemsPerSlide = 1;
                        if (this.active > this.max) this.active = this.max;
                    },
                    intervalId: null,
                    autoplayDelay: 4000,
                    startAuto() { if (!this.intervalId) { this.intervalId = setInterval(() => this.advance(), this.autoplayDelay); } },
                    stopAuto() { if (this.intervalId) { clearInterval(this.intervalId); this.intervalId = null; } }
                }"
                        x-init="updateItemsPerSlide(); window.addEventListener('resize', () => updateItemsPerSlide()); startAuto(); document.addEventListener('visibilitychange', () => { if (document.hidden) stopAuto(); else startAuto(); });"
                        class="flex flex-col lg:flex-row gap-8 lg:gap-12">
                        <!-- Left Side: Text & Controls -->
                        <div class="lg:w-1/4 flex-shrink-0 flex flex-col justify-center">
                            <div class="text-primary mb-4">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M13 10V3L4 14H11V21L20 10H13Z" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <h2 class="text-3xl md:text-4xl text-gray-900 leading-[1.2] tracking-tight font-bold">
                                Premium Handpicked <br> jobs for you
                            </h2>
                            <p class="mt-4 text-gray-500 text-lg font-light leading-relaxed">
                                Premium handpicked jobs that you will not find anywhere else!
                            </p>

                            <div class="flex items-center gap-4 mt-8">
                                <button type="button" @click="prev()"
                                    :class="{'opacity-30 cursor-not-allowed': active <= 0, 'hover:text-primary-600': active > 0}"
                                    class="text-gray-400 transition-colors">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M10 19l-7-7 7-7" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M3 12h20" />
                                    </svg>
                                </button>
                                <button type="button" @click="next()"
                                    :class="{'opacity-30 cursor-not-allowed': active >= max, 'hover:text-primary-600': active < max}"
                                    class="text-primary transition-colors">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M14 5l7 7-7 7" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M21 12H1" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Right Side: Slider Window -->
                        <div class="lg:w-3/4 overflow-hidden -mr-4 pr-4" @mouseenter="stopAuto()" @mouseleave="startAuto()">
                            <div class="flex transition-transform duration-500 ease-out"
                                :style="'transform: translateX(-' + (active * (100 / itemsPerSlide)) + '%)'">
                                <?php $unsplashImgs = ['1497215728101-856f4ea42174', '1497366216548-37526070297c', '1522071820081-009f0129c71c', '1542744173-8e7e53415bb0']; ?>
                                <?php foreach ($jobs as $index => $job): ?>
                                    <div class="flex-shrink-0 px-3 w-full md:w-1/2 xl:w-1/3">
                                        <a href="<?= $base ?>job/<?= htmlspecialchars($job['slug'] ?? $job['id'] ?? '') ?>"
                                            class="block h-full">
                                            <div class="h-full rounded-xl overflow-hidden bg-white border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 group/card">
                                                <!-- Card Cover Image -->
                                                <div class="h-48 bg-gray-100 relative">
                                                    <!-- Dynamic Company/Category/Job Image -->
                                                    <?php
                                                    $jobCover = !empty($job['company_banner']) ? fix_url($job['company_banner']) : null;
                                                    if (!$jobCover && !empty($job['image'])) {
                                                        $jobCover = fix_url($job['image']);
                                                    }
                                                    if (!$jobCover && !empty($job['category_image'])) {
                                                        $jobCover = fix_url($job['category_image']);
                                                    }
                                                    if (!$jobCover) {
                                                        $jobCover = "https://images.unsplash.com/photo-" . $unsplashImgs[$index % count($unsplashImgs)] . "?auto=format&fit=crop&w=500&q=80";
                                                    }
                                                    ?>
                                                    <img loading="lazy"
                                                        src="<?= htmlspecialchars($jobCover) ?>"
                                                        alt="Cover" class="w-full h-full object-cover">

                                                    <!-- Company Logo Overlay -->
                                                    <div class="absolute -bottom-8 left-4 w-14 h-14 bg-white rounded shadow-md flex items-center justify-center p-1 border border-gray-100 overflow-hidden">
                                                        <?php if (!empty($job['company_logo'])): ?>
                                                            <img loading="lazy"
                                                                src="<?= htmlspecialchars(fix_url($job['company_logo'])) ?>"
                                                                alt="Logo" class="w-full h-full object-contain">
                                                        <?php else: ?>
                                                            <div class="w-full h-full flex items-center justify-center bg-primary-50 text-primary font-bold text-lg">
                                                                <?= strtoupper(substr($job['company_name'] ?? 'C', 0, 1)) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="pt-8 pb-5 px-5">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span class="text-xs font-bold text-primary uppercase tracking-wider"><?= htmlspecialchars($job['company_name'] ?? 'Company') ?></span>
                                                        <?php if (!empty($job['verified'])): ?>
                                                            <svg class="w-3.5 h-3.5 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812 3.066 3.066 0 00.723 1.745 3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                            </svg>
                                                        <?php endif; ?>
                                                        <?php if (($job['job_type'] ?? 'internal') === 'external'): ?>
                                                            <span class="px-1.5 py-0.5 bg-orange-50 text-orange-600 text-[10px] font-bold rounded uppercase tracking-tighter">External</span>
                                                        <?php else: ?>
                                                            <span class="px-1.5 py-0.5 bg-green-50 text-green-600 text-[10px] font-bold rounded uppercase tracking-tighter">Direct</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <h3 class="text-base font-bold text-gray-900 line-clamp-1 mb-3">
                                                        <?= htmlspecialchars($job['title'] ?? 'Job Title') ?>
                                                    </h3>

                                                    <div class="flex items-center text-xs text-gray-500 gap-4 mb-5">
                                                        <div class="flex items-center gap-1">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            </svg>
                                                            <span><?= htmlspecialchars($job['location_display'] ?? 'Remote') ?></span>
                                                        </div>
                                                        <div class="flex items-center gap-1">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                            </svg>
                                                            <span><?= htmlspecialchars($job['experience_display'] ?? '0-5 Years') ?></span>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center justify-between mt-auto">
                                                        <div class="bg-primary text-white px-5 py-2 rounded-full text-xs font-bold hover:bg-primary-600 transition-all duration-300 shadow-lg shadow-orange-500/20 transform group-hover/card:-translate-y-0.5">
                                                            View Job
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="py-16 bg-white">
            <div class="container mx-auto px-6 lg:px-[7.5rem]">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
                    <div>
                        <div class="rounded-2xl overflow-hidden shadow-xl bg-white">
                            <img loading="lazy" src="assets/images/footer-cta.webp" alt="Good Company"
                                class="w-full h-82 object-fill">
                        </div>
                    </div>
                    <div>
                        <h3 class="text-3xl md:text-4xl font-extrabold text-gray-900">Good Life Begins With <br>A Good
                            Company</h3>
                        <p class="mt-4 text-gray-600">Your best life begins with the right, impactful career move. Discover
                            leading, future-focused companies worldwide that prioritize people, professional growth, and
                            true well-being. Find your next fulfilling role and start to instantly thrive.</p>
                        <div class="mt-6 flex items-center gap-4">
                            <a href="<?= $base ?>jobs"
                                class="px-8 py-3.5 rounded-full bg-primary hover:bg-primary-600 text-white font-bold shadow-lg shadow-orange-500/20 transition-all transform hover:-translate-y-0.5">Search
                                Job</a>
                            <a href="<?= $base ?>jobs"
                                class="px-8 py-3.5 rounded-full bg-[#ff6a3d]/5 text-[#ff6a3d] font-bold hover:bg-[#ff6a3d]/10 transition-all">Learn more</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-12 bg-white">
            <div class="container mx-auto px-6 lg:px-[7.5rem]">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <p class="text-3xl font-extrabold text-primary">12k+</p>
                        <p class="font-semibold text-gray-900">Clients worldwide</p>
                        <p class="mt-1 text-gray-500 text-sm">Trusted by organizations worldwide for their talent needs and
                            successful hiring.</p>
                    </div>
                    <div>
                        <p class="text-3xl font-extrabold text-primary">20k+</p>
                        <p class="font-semibold text-gray-900">Active resume</p>
                        <p class="mt-1 text-gray-500 text-sm">A vibrant community of top-tier professionals ready for their
                            next career move.</p>
                    </div>
                    <div>
                        <p class="text-3xl font-extrabold text-primary">18k+</p>
                        <p class="font-semibold text-gray-900">Companies</p>
                        <p class="mt-1 text-gray-500 text-sm">Opportunities with leading employers committed to growth and
                            exceptional work culture.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 bg-white">
            <div class="container mx-auto px-6 lg:px-[7.5rem]">
                <div class="rounded-3xl overflow-hidden bg-black relative min-h-[320px] md:min-h-[420px]">
                    <img loading="lazy"
                        src="https://img.freepik.com/free-photo/business-finance-employment-female-successful-entrepreneurs-concept-professional-asian-businesswoman-glasses-having-lunch-drinking-takeaway-coffee-using-mobile-phone_1258-94505.jpg?t=st=1765458060~exp=1765461660~hmac=f72564bf528341340be6657fa8c7e05519b922e24afe1186d1ba8568dd691752&w=1060"
                        alt=""
                        class="absolute inset-0 w-full h-full object-cover pointer-events-none"
                        style="object-position: center 15%;"/>
                    
                    <div class="relative px-8 py-14 md:px-16 md:py-16 lg:pl-20 text-white max-w-xl md:ml-6 lg:ml-10 xl:ml-14">
                        <h3 class="text-3xl md:text-4xl font-semibold text-primary">Find Real Jobs <br>From Verified
                            Employers</h3>
                        <p class="mt-4 font-semibold text-gray-700">Explore verified job opportunities across industries.
                            Apply directly to trusted employers and grow your career with confidence.
                        </p> <br>
                        <p class="font-semibold text-gray-700">Your next career move starts here.</p><br>
                        <a href="<?= $base ?>register-candidate"
                            class="mt-6 inline-block px-6 py-3 rounded-full bg-primary hover:bg-primary-600 hover:text-white text-white font-semibold shadow-lg shadow-orange-500/20 transition-all transform hover:-translate-y-0.5">Get
                            Started — It’s Free</a>
                    </div>
                </div>
            </div>
        </section>
        <?php
    // Helper function to get category icon based on category name - matching Figma design
        /**
         * @param string|null $categoryName
         * @return string
         */
        function getCategoryIcon($categoryName)
        {
            $name = strtolower(trim($categoryName ?? ''));
            $size = 'w-12 h-12';

            // Check for common categories and return professional icons
            if (strpos($name, 'digital marketing') !== false || strpos($name, 'seo') !== false || strpos($name, 'content writing') !== false) {
                // Megaphone / Marketing icon
                return '<svg class="' . $size . ' text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5L6 9H2v6h4l5 4V5z"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>';
            } elseif (strpos($name, 'it') !== false || strpos($name, 'software') !== false || strpos($name, 'technology') !== false || strpos($name, 'development') !== false || strpos($name, 'cybersecurity') !== false || strpos($name, 'data science') !== false) {
                // Code / Tech icon
                return '<svg class="' . $size . ' text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>';
            } elseif (strpos($name, 'driver') !== false || strpos($name, 'transport') !== false || strpos($name, 'logistics') !== false || strpos($name, 'delivery') !== false || strpos($name, 'courier') !== false) {
                // Truck / Delivery icon
                return '<svg class="' . $size . ' text-orange-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polyline points="16 8 20 8 23 11 23 16 16 16"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>';
            } elseif (strpos($name, 'healthcare') !== false || strpos($name, 'medical') !== false || strpos($name, 'doctor') !== false || strpos($name, 'nurse') !== false || strpos($name, 'pharmacy') !== false || strpos($name, 'ward boy') !== false) {
                // Medical icon
                return '<svg class="' . $size . ' text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>';
            } elseif (strpos($name, 'sales') !== false || strpos($name, 'marketing') !== false || strpos($name, 'business development') !== false) {
                // Sales / Growth icon
                return '<svg class="' . $size . ' text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>';
            } elseif (strpos($name, 'finance') !== false || strpos($name, 'banking') !== false || strpos($name, 'account') !== false || strpos($name, 'insurance') !== false) {
                // Finance / Money icon
                return '<svg class="' . $size . ' text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>';
            } elseif (strpos($name, 'education') !== false || strpos($name, 'teaching') !== false || strpos($name, 'school') !== false || strpos($name, 'training') !== false) {
                // Education icon
                return '<svg class="' . $size . ' text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>';
            } elseif (strpos($name, 'construction') !== false || strpos($name, 'building') !== false || strpos($name, 'engineering') !== false || strpos($name, 'real estate') !== false) {
                // Construction icon
                return '<svg class="' . $size . ' text-yellow-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>';
            } elseif (strpos($name, 'hotel') !== false || strpos($name, 'tourism') !== false || strpos($name, 'hospitality') !== false || strpos($name, 'travel') !== false || strpos($name, 'cook') !== false || strpos($name, 'chef') !== false) {
                // Hospitality icon
                return '<svg class="' . $size . ' text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>';
            } elseif (strpos($name, 'technician') !== false || strpos($name, 'electrician') !== false || strpos($name, 'plumber') !== false || strpos($name, 'mechanic') !== false || strpos($name, 'repair') !== false || strpos($name, 'maintenance') !== false) {
                // Tools / Technician icon
                return '<svg class="' . $size . ' text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.77 3.77z"/></svg>';
            } elseif (strpos($name, 'manufacturing') !== false || strpos($name, 'production') !== false || strpos($name, 'factory') !== false || strpos($name, 'metal') !== false || strpos($name, 'textiles') !== false) {
                // Factory icon
                return '<svg class="' . $size . ' text-zinc-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20V9l4 2 4-2 4 2 8-4v15H2z"/><path d="M18 11v4M14 13v2M10 13v2M6 13v2"/></svg>';
            } elseif (strpos($name, 'admin') !== false || strpos($name, 'clerical') !== false || strpos($name, 'human resources') !== false || strpos($name, 'hr') !== false || strpos($name, 'office') !== false || strpos($name, 'peon') !== false) {
                // Office / Admin icon
                return '<svg class="' . $size . ' text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>';
            } elseif (strpos($name, 'customer service') !== false || strpos($name, 'telecommunications') !== false || strpos($name, 'call center') !== false) {
                // Customer Service icon
                return '<svg class="' . $size . ' text-cyan-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10z"/></svg>';
            } elseif (strpos($name, 'security') !== false || strpos($name, 'safety') !== false || strpos($name, 'government') !== false) {
                // Security icon
                return '<svg class="' . $size . ' text-slate-800" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>';
            } elseif (strpos($name, 'beauty') !== false || strpos($name, 'wellness') !== false || strpos($name, 'fashion') !== false || strpos($name, 'beautician') !== false || strpos($name, 'hair') !== false) {
                // Beauty / Wellness icon
                return '<svg class="' . $size . ' text-pink-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
            } elseif (strpos($name, 'warehouse') !== false || strpos($name, 'picker') !== false || strpos($name, 'packer') !== false || strpos($name, 'loading') !== false) {
                // Warehouse / Box icon
                return '<svg class="' . $size . ' text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>';
            } else {
                // Default generic briefcase icon
                return '<svg class="' . $size . ' text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>';
            }
        }

        ?>
        <section class="py-20 bg-[#e5e7eb] relative">
            <div class="container mx-auto px-6 lg:px-[7.5rem]">
                <?php
                $clientItems = [];
                if (!empty($testimonials_client) && is_array($testimonials_client)) {
                    foreach ($testimonials_client as $t) {
                        $clientItems[] = [
                            'title' => (string)($t['title'] ?? ''),
                            'name' => (string)($t['name'] ?? ''),
                            'designation' => (string)($t['designation'] ?? ''),
                            'company' => (string)($t['company'] ?? ''),
                            'message' => (string)($t['message'] ?? ''),
                            'video_url' => (string)($t['video_url'] ?? ''),
                            'avatar' => (string)($t['image'] ?? 'https://images.unsplash.com/photo-1527980965255-d3b416303d12?q=80&w=300&auto=format&fit=crop')
                        ];
                    }
                }
                $candidateItems = [];
                if (!empty($testimonials_candidate) && is_array($testimonials_candidate)) {
                    foreach ($testimonials_candidate as $t) {
                        $candidateItems[] = [
                            'title' => (string)($t['title'] ?? ''),
                            'name' => (string)($t['name'] ?? ''),
                            'designation' => (string)($t['designation'] ?? ''),
                            'company' => (string)($t['company'] ?? ''),
                            'message' => (string)($t['message'] ?? ''),
                            'video_url' => (string)($t['video_url'] ?? ''),
                            'avatar' => (string)($t['image'] ?? 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=300&auto=format&fit=crop')
                        ];
                    }
                }
                ?>
                <script>
                    window.clientTestimonials = <?= json_encode($clientItems, JSON_UNESCAPED_SLASHES) ?>;
                    window.candidateTestimonials = <?= json_encode($candidateItems, JSON_UNESCAPED_SLASHES) ?>;
                </script>
                <div class="grid grid-cols-1 gap-16">
                    <div>
                        <p class="text-sm font-semibold text-primary">Client Testimonials</p>
                        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900">What Employers Say</h2>
                        <div x-data="{
                            items: [],
                            active: 0,
                            timer: null,
                            init() {
                                this.items = Array.isArray(window.clientTestimonials) ? window.clientTestimonials : [];
                                const els = document.querySelectorAll('.scroll-fade-up');
                                const io = new IntersectionObserver(e=>e.forEach(x=>{ if(x.isIntersecting) x.target.classList.add('in-view'); }), { threshold: 0.2 });
                                els.forEach(el=>io.observe(el));
                                if (this.items.length > 1) this.start();
                            },
                            start() { this.timer = setInterval(()=> this.next(), 5000); },
                            stop() { if (this.timer) clearInterval(this.timer); },
                            restart() { this.stop(); this.start(); },
                            next() { this.active = (this.active + 1) % this.items.length; },
                            prev() { this.active = (this.active - 1 + this.items.length) % this.items.length; },
                            go(i) { this.active = i; this.restart(); },
                            isYouTube(u){ return typeof u==='string' && (u.includes('youtube.com') || u.includes('youtu.be')); },
                            youtubeEmbed(u){
                                try {
                                    if (u.includes('youtu.be/')) {
                                        const id = u.split('youtu.be/')[1].split('?')[0];
                                        return 'https://www.youtube.com/embed/' + id;
                                    }
                                    const url = new URL(u);
                                    const id = url.searchParams.get('v');
                                    return id ? ('https://www.youtube.com/embed/' + id) : u;
                                } catch(e){ return u; }
                            }
                        }" class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center mt-4">
                            <div class="relative h-80 lg:h-[420px] scroll-fade-up flex items-center justify-center">
                                <!-- Background Decoration -->
                                <div class="absolute w-72 h-72 bg-primary-50 rounded-full blur-2xl opacity-60 animate-pulse"></div>

                                <div class="relative w-64 h-64 md:w-80 md:h-80">
                                    <template x-for="(item, index) in items" :key="index">
                                        <img loading="lazy" :src="item.avatar"
                                            :alt="item.name"
                                            class="absolute inset-0 w-full h-full object-cover rounded-full shadow-2xl border-4 border-white transition-all duration-700"
                                            x-show="active === index"
                                            x-transition:enter="transition ease-out duration-700"
                                            x-transition:enter-start="opacity-0 scale-90 rotate-12"
                                            x-transition:enter-end="opacity-100 scale-100 rotate-0"
                                            x-transition:leave="transition ease-in duration-700"
                                            x-transition:leave-start="opacity-100 scale-100 rotate-0"
                                            x-transition:leave-end="opacity-0 scale-90 -rotate-12">
                                    </template>
                                </div>
                            </div>
                            <div class="scroll-fade-up">
                                <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                                    <template x-if="items.length">
                                        <div>
                                            <template x-if="items[active].title">
                                                <h3 class="text-xl font-bold text-gray-900 mb-2"
                                                    x-text="items[active].title"></h3>
                                            </template>
                                            <template x-if="items[active].video_url">
                                                <div class="space-y-3">
                                                    <template x-if="isYouTube(items[active].video_url)">
                                                        <iframe :src="youtubeEmbed(items[active].video_url)"
                                                            class="w-full h-52 md:h-64 rounded-lg" frameborder="0"
                                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                            allowfullscreen></iframe>
                                                    </template>
                                                    <template x-if="!isYouTube(items[active].video_url)">
                                                        <video :src="items[active].video_url"
                                                            class="w-full h-52 md:h-64 rounded-lg" controls></video>
                                                    </template>
                                                    <template x-if="items[active].message">
                                                        <p class="text-gray-600" x-text="items[active].message"></p>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="!items[active].video_url">
                                                <p class="text-gray-600" x-text="items[active].message"></p>
                                            </template>
                                            <div class="mt-4">
                                                <p class="font-semibold text-gray-900" x-text="items[active].name"></p>
                                                <p class="text-sm text-gray-600"><span
                                                        x-text="items[active].designation"></span><span
                                                        x-show="items[active].company"> • <span
                                                            x-text="items[active].company"></span></span></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <div class="mt-4 flex items-center gap-3" x-show="items.length > 1">
                                    <button type="button" @click="prev()"
                                        class="w-10 h-10 rounded-full bg-white shadow ring-1 ring-gray-200 hover:bg-gray-50 flex items-center justify-center">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </button>
                                    <button type="button" @click="next()"
                                        class="w-10 h-10 rounded-full bg-white shadow ring-1 ring-gray-200 hover:bg-gray-50 flex items-center justify-center">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    <div class="ml-2 flex items-center gap-1">
                                        <template x-for="(d,i) in items" :key="i">
                                            <span @click="go(i)" :class="i===active ? 'w-6 bg-primary' : 'w-3 bg-primary'"
                                                class="h-2 rounded-full cursor-pointer transition-all"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        <section class="py-16 bg-white overflow-hidden border-y border-gray-50">
            <div class="container mx-auto px-6 lg:px-[7.5rem] relative z-10">
                <div class="text-center mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight" data-aos="fade-up">
                        More than 12k recruiters from leading tech companies are hiring
                    </h2>
                </div>

                <div class="relative group" data-aos="fade-up" data-aos-delay="100">
                    <!-- Navigation Buttons -->
                    <button type="button"
                        class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 w-10 h-10 rounded-full bg-white shadow-lg border border-gray-100 flex items-center justify-center text-gray-400 hover:text-primary z-20 transition-all opacity-0 group-hover:opacity-100 group-hover:translate-x-0"
                        onclick="document.getElementById('employer-scroll').scrollBy({left: -300, behavior: 'smooth'})">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    
                    <button type="button"
                        class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 w-10 h-10 rounded-full bg-white shadow-lg border border-gray-100 flex items-center justify-center text-gray-400 hover:text-primary z-20 transition-all opacity-0 group-hover:opacity-100 group-hover:translate-x-0"
                        onclick="document.getElementById('employer-scroll').scrollBy({left: 300, behavior: 'smooth'})">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>

                    <div id="employer-scroll" class="overflow-x-auto no-scrollbar scroll-smooth">
                        <div class="flex items-center gap-6 py-4 px-2">
                            <?php if (!empty($employerLogos) && is_array($employerLogos)): ?>
                                <?php foreach ($employerLogos as $emp): 
                                    $logo = fix_url($emp['logo_url'] ?? '');
                                    if (!empty($logo) && strpos($logo, 'http') !== 0) $logo = $base . ltrim($logo, '/');
                                ?>
                                    <div class="flex-shrink-0">
                                        <div class="bg-gray-50/50 px-6 py-4 rounded-xl border border-gray-100 hover:border-primary/20 hover:bg-white hover:shadow-xl hover:shadow-primary/5 transition-all duration-300 flex items-center justify-center min-w-[180px] h-24 group/logo overflow-hidden">
                                            <?php if (!empty($logo)): ?>
                                                <img loading="lazy" src="<?= htmlspecialchars($logo) ?>"
                                                    alt="<?= htmlspecialchars($emp['company_name'] ?? 'Company') ?>"
                                                    class="h-12 w-auto max-w-[140px] object-contain transition duration-300" 
                                                    onerror="this.parentElement.innerHTML='<span class=\'text-gray-400 font-bold text-xs uppercase tracking-wider\'><?= htmlspecialchars(substr($emp['company_name'] ?? 'C', 0, 12)) ?></span>'" />
                                            <?php else: ?>
                                                <span class="text-gray-400 font-bold text-xs uppercase tracking-wider"><?= htmlspecialchars(substr($emp['company_name'] ?? 'Company', 0, 12)) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php for($i=0;$i<8;$i++): ?>
                                    <div class="w-40 h-20 bg-gray-50 rounded-xl border border-gray-100"></div>
                                <?php endfor; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
       

        <section class="py-16 bg-white">
            <div class="container mx-auto px-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 text-center w-full">Trending Blogs</h2>
                    <a href="<?= $base ?>blog"
                        class="hidden md:inline-block text-primary font-semibold hover:underline whitespace-nowrap">View
                        all</a>
                </div>
                <?php if (!empty($blogs) && is_array($blogs)): ?>
                    <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <?php foreach (array_slice($blogs, 0, 8) as $blog): ?>
                            <a href="<?= $base ?>blog/<?= urlencode($blog['slug'] ?? ($blog['id'] ?? '')) ?>"
                                class="group block rounded-2xl border border-gray-200 overflow-hidden bg-white hover:shadow-md transition"
                                target="_blank">
                                <div class="bg-gray-100">
                                    <?php
                                    $img = $blog['featured_image'] ?? '';
                                    if (!empty($img) && strpos($img, 'http') !== 0) {
                                        $img = $base . ltrim($img, '/');
                                    }
                                    ?>
                                    <?php if (!empty($img)): ?>
                                        <img loading="lazy" src="<?= htmlspecialchars($img) ?>"
                                            alt="<?= htmlspecialchars($blog['title'] ?? '') ?>"
                                            class="w-full h-40 object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-40 bg-gradient-to-br from-gray-200 to-gray-300"></div>
                                    <?php endif; ?>
                                </div>
                                <div class="p-4">
                                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-gray-800"><?= htmlspecialchars($blog['title'] ?? '') ?></h3>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="rounded-2xl border border-gray-200 overflow-hidden">
                            <div class="w-full h-40 bg-gray-200"></div>
                            <div class="p-4">
                                <h3 class="text-base font-semibold text-gray-900">Sample Blog</h3>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-gray-200 overflow-hidden">
                            <div class="w-full h-40 bg-gray-200"></div>
                            <div class="p-4">
                                <h3 class="text-base font-semibold text-gray-900">Sample Blog</h3>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-gray-200 overflow-hidden">
                            <div class="w-full h-40 bg-gray-200"></div>
                            <div class="p-4">
                                <h3 class="text-base font-semibold text-gray-900">Sample Blog</h3>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-gray-200 overflow-hidden">
                            <div class="w-full h-40 bg-gray-200"></div>
                            <div class="p-4">
                                <h3 class="text-base font-semibold text-gray-900">Sample Blog</h3>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
         <!-- App download section removed (2026-10-06): no Jobsence app is published in the stores yet, and
              badges / "get link" without a real app made Google Safe Browsing flag the site as deceptive. -->
    </div>
    <?php
    // require __DIR__ . '/include/footer.php';
    ?>
</body>

</html>