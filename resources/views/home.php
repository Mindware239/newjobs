<?php
$cfgPath = __DIR__ . '/../../config/config.php';
$base = '/'; // fallback
if (file_exists($cfgPath)) {
    $cfg = require $cfgPath;
    if (!empty($cfg['base_url'])) $base = rtrim($cfg['base_url'], '/') . '/';
}
$stats = $stats ?? [];
$totalJobs = $stats['jobs'] ?? 25850;
$totalCandidates = $stats['candidates'] ?? 10250;
$totalCompanies = $stats['companies'] ?? 18400;

if (!function_exists('fix_url')) {
    function fix_url($url)
    {
        if (empty($url)) return $url;
        return str_replace(['http://localhost:8000', 'http://127.0.0.1:8000'], '', $url);
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Find Jobs & Hire Talent | Mindwareinfotech</title>
    <meta name="description" content="Find your dream job or hire top talent. Connect with verified employers and skilled candidates. Browse thousands of job openings across all industries." />
    <meta name="keywords" content="jobs, employment, career, hiring, recruitment, job portal, find jobs, hire talent" />
    <meta property="og:title" content="Job Portal - Find Jobs & Hire Talent" />
    <meta property="og:description" content="Find your dream job or hire top talent. Connect with verified employers and skilled candidates." />
    <meta property="og:type" content="website" />
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta property="og:url" content="<?= $_ENV['APP_URL'] ?? 'http://localhost:8000' ?>" />
    <link rel="canonical" href="<?= $_ENV['APP_URL'] ?? 'http://localhost:8000' ?>" />
    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        window.typedRoles = <?= json_encode($typedRoles ?? [], JSON_UNESCAPED_SLASHES) ?>;
        window.locationDropdown = function() {
            return {
                open: false,
                q: '',
                items: [],
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

                    // Request Notification Permission
                    if ("Notification" in window && Notification.permission !== "granted" && Notification.permission !== "denied") {
                        Notification.requestPermission().then(permission => {
                            if (permission === "granted") {
                                console.log("Notification permission granted");
                                // Optional: Register push subscription here if needed
                            }
                        });
                    }
                },
                handleGeoSuccess(pos) {
                    const lat = pos.coords.latitude;
                    const lon = pos.coords.longitude;
                    // Use OpenStreetMap Nominatim for reverse geocoding (Client-side)
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data && data.address) {
                                const country = data.address.country || '';
                                const city = data.address.city || data.address.town || data.address.village || '';
                                const state = data.address.state || '';
                                
                                // Try to find a match in the dropdown items
                                // Priority: City, State, Country
                                const match = this.items.find(i => {
                                    const label = i.label.toLowerCase();
                                    return (city && label.includes(city.toLowerCase())) ||
                                           (state && label.includes(state.toLowerCase())) ||
                                           (country && label === country.toLowerCase());
                                });

                                if (match) {
                                    this.selectItem(match);
                                    // Also update the native select to trigger any server-side logic if needed
                                    const sel = this.$refs.native;
                                    if (sel) {
                                        sel.value = match.value;
                                        sel.dispatchEvent(new Event('change'));
                                    }
                                }
                            }
                        })
                        .catch(e => console.error("Geocoding error:", e));
                },
                filteredItems() {
                    const q = this.q.trim().toLowerCase();
                    const src = this.items || [];
                    if (!q) return src.slice(0, 12);
                    return src.filter(i => i.label.toLowerCase().includes(q)).slice(0, 50);
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
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
    </style>
</head>

<body class="bg-gray-50 antialiased text-gray-800" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 800)">
    <!-- Skeleton Loader -->
    <div x-show="!loaded" x-cloak x-transition.opacity.duration.500ms class="fixed inset-0 bg-white z-50 flex flex-col overflow-hidden">
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
        <section class="relative hero-height bg-white text-gray-800">
            <div class="relative container mx-auto px-4 lg:px-8 pt-16 md:pt-20 pb-6">

                <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight text-gray-900 text-center leading-tight">
                    Handpicked Premium
                    <span id="hero-word" class="text-blue-600"></span>
                    <span id="hero-caret" class="text-blue-600">|</span>
                    Jobs
                </h1>
                <script>
                    (function() {
                        const words = ['Software', 'AI', 'Data Science', 'Full Stack', 'Cloud'];
                        const el = document.getElementById('hero-word');
                        const caret = document.getElementById('hero-caret');
                        let i = 0,
                            j = 0,
                            deleting = false;

                        function tick() {
                            const w = words[i];
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
                        setInterval(() => {
                            caret.style.opacity = caret.style.opacity === '0' ? '1' : '0';
                        }, 500);
                        tick();
                    })();
                </script>

                <p class="mt-3 text-gray-600 text-center">Connecting Talent with Opportunity; Your Gateway to Career Success</p>
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
                            <button @click="open = !open"
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
                                        class="w-full px-3 py-2 rounded-lg border text-sm focus:ring-2 focus:ring-blue-600" />
                                </div>
                                <ul class="max-h-64 overflow-auto p-1">
                                    <template x-for="item in filteredItems()" :key="item.value">
                                        <li @click="selectItem(item)"
                                            class="px-3 py-2 rounded-lg cursor-pointer hover:bg-blue-50 flex justify-between">
                                            <span x-text="item.label"></span>
                                            <span x-show="item.value === selectedValue" class="text-blue-600">✔</span>
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
                            <button @click="open = !open" class="w-full flex items-center justify-between font-medium text-gray-500">
                                <span x-text="selectedLabel"></span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            
                            <div x-show="open" x-transition.opacity.duration.200ms @click.away="open = false" 
                                 class="absolute left-0 right-0 top-full mt-2 bg-white border border-gray-200 rounded-xl shadow-2xl z-50 p-2">
                                <input type="text" x-model="search" placeholder="Filter experience..." 
                                       class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 mb-2">
                                <ul class="max-h-64 overflow-auto custom-scrollbar">
                                    <template x-for="opt in filtered" :key="opt.value">
                                        <li @click="select(opt)" 
                                            class="px-3 py-2 rounded-lg cursor-pointer hover:bg-blue-50 flex justify-between items-center transition-colors">
                                            <span x-text="opt.label" class="text-sm font-medium text-gray-700"></span>
                                            <span x-show="selectedValue === opt.value" class="text-blue-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            <!-- Hidden input for compatibility -->
                            <input type="hidden" id="home-exp-mobile" :value="selectedValue">
                        </div>


                        <!-- Search Button -->
                        <button
                            onclick="(function(){
              var k=document.getElementById('home-keyword-mobile').value||'';
              var l=document.getElementById('home-location-mobile').value||'';
              var e=document.getElementById('home-exp-mobile').value||'';
              var qs=[];
              if(k)qs.push('keyword='+encodeURIComponent(k));
              if(l)qs.push('location='+encodeURIComponent(l));
              if(e && e!=='Experience')qs.push('experience='+encodeURIComponent(e));
              location.href='<?= $base ?>jobs'+(qs.length?'?'+qs.join('&'):'');
            })()"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl shadow-md transition">
                            Search Jobs
                        </button>
                    </div>

                    <!-- ================= DESKTOP SEARCH ================= -->
                    <div class="hidden md:flex items-center bg-white rounded-full shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300 p-2 max-w-5xl mx-auto ring-1 ring-gray-50">

                        <!-- Keyword -->
                        <div class="flex items-center flex-1 px-4 relative group" x-data="homeKeywordSuggest()">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <div x-show="show && list.length>0" x-cloak class="absolute left-0 right-0 top-full mt-4 bg-white border border-gray-100 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] z-50 max-h-72 overflow-auto py-2">
                                <template x-for="(s, i) in list" :key="s.id ?? i">
                                    <div @click="select(s)" @mouseenter="selectedIndex=i" class="px-5 py-3 cursor-pointer hover:bg-blue-50/50 transition-colors flex items-center gap-3" :class="selectedIndex===i ? 'bg-blue-50/50' : ''">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
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
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between font-medium text-gray-700 hover:text-blue-600 transition-colors outline-none">
                                <div class="flex items-center gap-3 truncate">
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span x-text="selectedLabel" class="truncate"></span>
                                </div>
                                <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-500 transition-colors" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition.opacity.duration.200ms
                                @click.away="open = false"
                                class="absolute left-0 right-0 mt-6 bg-white border border-gray-100 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] z-50 overflow-hidden">
                                <div class="p-3 border-b border-gray-50 bg-gray-50/50">
                                    <div class="relative">
                                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <input type="text" x-model="q" placeholder="Filter locations..."
                                            class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none bg-white" />
                                    </div>
                                </div>
                                <ul class="max-h-64 overflow-auto p-2 custom-scrollbar">
                                    <template x-for="item in filteredItems()" :key="item.value">
                                        <li @click="selectItem(item)"
                                            class="px-4 py-2.5 rounded-lg hover:bg-blue-50 cursor-pointer flex justify-between items-center group/item transition-colors">
                                            <span x-text="item.label" class="text-sm text-gray-700 group-hover/item:text-blue-700 font-medium"></span>
                                            <span x-show="item.value === selectedValue" class="text-blue-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg></span>
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
                            <button type="button" @click="open = !open" class="w-full flex items-center justify-between font-medium text-gray-700 hover:text-blue-600 transition-colors outline-none">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span x-text="selected"></span>
                                </div>

                                <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Hidden input (for backend) -->
                            <input type="hidden" id="home-exp-desktop" :value="selected === 'Any Exp. Level' ? '' : selected">

                            <!-- Dropdown -->
                            <div x-show="open" x-transition.opacity.duration.200ms @click.away="open = false" class="absolute left-0 right-0 mt-6 bg-white border border-gray-100 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] z-50 overflow-hidden">
                                <!-- Search -->
                                <div class="p-3 border-b border-gray-100">
                                    <input type="text" x-model="search" placeholder="Filter experience…" class="w-full px-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Options -->
                                <div class="max-h-64 overflow-y-auto py-1 custom-scrollbar">
                                    <template x-for="opt in filtered" :key="opt">
                                        <div @click="selected = opt; open = false; search = ''" class="px-5 py-2.5 cursor-pointer text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors flex justify-between items-center">
                                            <span x-text="opt"></span>

                                            <svg x-show="selected === opt" class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
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
                        <button
                            onclick="(function(){
              var k=document.getElementById('home-keyword-desktop').value||'';
              var l=document.getElementById('home-location-desktop').value||'';
              var e=document.getElementById('home-exp-desktop').value||'';
              var qs=[];
              if(k)qs.push('keyword='+encodeURIComponent(k));
              if(l)qs.push('location='+encodeURIComponent(l));
              if(e && e!=='Experience')qs.push('experience='+encodeURIComponent(e));
              location.href='<?= $base ?>jobs'+(qs.length?'?'+qs.join('&'):'');
            })()"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-full shadow-md transition">
                            Search
                        </button>
                    </div>

                </div>


                <!-- Category Slider Job Categories -->
                <div class="mt-14 bg-white py-6 shadow-sm border-t border-b border-gray-100 relative" x-data="{
                    activeCat: 0,
                    categories: [],
                    init() {
                         // Collect categories from PHP rendered items below if needed, or just let the slider work with DOM elements
                         // We will implement a simple scroll button logic
                    }
                }">
                    <div class="container mx-auto px-6 lg:px-[7.5rem] relative group">
                        <button class="absolute left-4 lg:left-20 top-1/2 -translate-y-1/2 p-2 text-gray-300 hover:text-gray-800 transition" onclick="document.getElementById('cat-scroll').scrollBy({left: -200, behavior: 'smooth'})">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <div id="cat-scroll" class="flex items-center justify-between gap-8 overflow-x-auto no-scrollbar scroll-smooth px-12 py-1">
                            <?php if (!empty($categories) && is_array($categories)): ?>
                                <?php foreach ($categories as $index => $cat): ?>
                                    <a href="<?= $base ?>jobs?industry=<?= urlencode($cat['slug'] ?? $cat['name'] ?? '') ?>" class="group/cat flex flex-col items-center gap-4 min-w-[160px] cursor-pointer py-2">
                                        <div class="w-24 h-24 flex items-center justify-center text-gray-800 transition-transform duration-300 group-hover/cat:-translate-y-0.5">
                                            <?php if (!empty($cat['image'])): ?>
                                                <img src="<?= htmlspecialchars($cat['image']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="w-24 h-24 object-cover rounded-lg ring-1 ring-gray-200" />
                                            <?php else: ?>
                                                <?= getCategoryIcon($cat['name'] ?? '') ?>
                                            <?php endif; ?>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900 group-hover/cat:text-blue-600 transition-colors whitespace-nowrap">
                                            <?= htmlspecialchars($cat['name'] ?? 'Category') ?>
                                        </span>
                                        <span class="text-xs text-gray-600">Active Job
                                            <?= (int)($cat['count'] ?? 0) ?>
                                        </span>
                                        <div class="h-0.5 w-0 bg-blue-600 group-hover/cat:w-16 transition-all duration-300"></div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Static Categories for Demo/Fallback -->
                                <?php
                                $demoCats = [
                                    ['name' => 'AI/ML', 'icon' => 'cpu'],
                                    ['name' => 'Data Analytics & BI', 'icon' => 'chart-bar'],
                                    ['name' => 'Data Engineering', 'icon' => 'database'],
                                    ['name' => 'Backend Development', 'icon' => 'server'],
                                    ['name' => 'Frontend Development', 'icon' => 'code'],
                                    ['name' => 'Full Stack', 'icon' => 'layers'],
                                    ['name' => 'Mobile', 'icon' => 'smartphone'],
                                    ['name' => 'DevOps', 'icon' => 'cloud']
                                ];
                                foreach ($demoCats as $cat):
                                ?>
                                    <a href="#" class="group/cat flex flex-col items-center gap-4 min-w-[120px] cursor-pointer py-2">
                                        <div class="w-14 h-14 flex items-center justify-center text-gray-800 transition-transform duration-300 group-hover/cat:-translate-y-1">
                                            <!-- Simple SVG Placeholders -->
                                            <svg class="w-10 h-10 stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-bold text-gray-900 group-hover/cat:text-blue-600 transition-colors whitespace-nowrap">
                                            <?= $cat['name'] ?>
                                        </span>
                                        <div class="h-0.5 w-0 bg-blue-600 group-hover/cat:w-full transition-all duration-300 mt-1"></div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <button class="absolute right-4 lg:right-20 top-1/2 -translate-y-1/2 p-2 text-blue-600 hover:text-blue-700 transition" onclick="document.getElementById('cat-scroll').scrollBy({left: 200, behavior: 'smooth'})">
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
                            <div class="text-blue-600 mb-4">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M13 10V3L4 14H11V21L20 10H13Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <h2 class="text-3xl md:text-4xl text-gray-900 leading-[1.2] tracking-tight">
                                <span class="font-bold">Handpicked Premium </span>
                                <span id="tw-word" class="text-blue-600 font-semibold"></span>
                                <span id="tw-caret" class="text-blue-600">|</span>
                                <span class="font-bold"> Jobs</span>
                            </h2>
                            <script>
                                (function() {
                                    const words = ['Software', 'AI', 'Data Science', 'Full Stack', 'Cloud'];
                                    const el = document.getElementById('tw-word');
                                    const caret = document.getElementById('tw-caret');
                                    let i = 0,
                                        j = 0,
                                        deleting = false;

                                    function tick() {
                                        const w = words[i];
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
                                    setInterval(() => {
                                        caret.style.opacity = caret.style.opacity === '0' ? '1' : '0';
                                    }, 500);
                                    tick();
                                })();
                            </script>
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

                            <p class="mt-4 text-gray-500 text-lg font-light leading-relaxed">
                                Premium handpicked jobs that you will not find anywhere else!
                            </p>

                            <div class="flex items-center gap-4 mt-8">
                                <button @click="prev()" :class="{'opacity-30 cursor-not-allowed': active <= 0, 'hover:text-blue-700': active > 0}" class="text-gray-400 transition-colors">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7 7-7" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12h20" />
                                    </svg>
                                </button>
                                <button @click="next()" :class="{'opacity-30 cursor-not-allowed': active >= max, 'hover:text-blue-700': active < max}" class="text-blue-600 transition-colors">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7-7 7" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12H1" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Right Side: Slider Window -->
                        <div class="lg:w-3/4 overflow-hidden -mr-4 pr-4" @mouseenter="stopAuto()" @mouseleave="startAuto()">
                            <div class="flex transition-transform duration-500 ease-out" :style="'transform: translateX(-' + (active * (100 / itemsPerSlide)) + '%)'">
                                <?php foreach ($jobs as $index => $job): ?>
                                    <div class="flex-shrink-0 px-3 w-full md:w-1/2 xl:w-1/3">
                                        <a href="<?= $base ?>job/<?= htmlspecialchars($job['slug'] ?? $job['id'] ?? '') ?>" class="block h-full">
                                            <div class="h-full rounded-xl overflow-hidden bg-white border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 group/card">
                                                <!-- Card Cover Image -->
                                                <div class="h-48 bg-gray-100 relative">
                                                    <!-- Random Office/Tech Image -->
                                                    <img src="https://images.unsplash.com/photo-<?= ['1497215728101-856f4ea42174', '1497366216548-37526070297c', '1522071820081-009f0129c71c', '1542744173-8e7e53415bb0'][($index % 4)] ?>?auto=format&fit=crop&w=500&q=80"
                                                        alt="Cover" class="w-full h-full object-cover">

                                                    <!-- Company Logo Overlay -->
                                                    <div class="absolute -bottom-8 left-4 w-14 h-14 bg-white rounded shadow-md flex items-center justify-center p-1 border border-gray-100">
                                                        <?php if (!empty($job['company_logo'])): ?>
                                                            <img src="<?= htmlspecialchars(fix_url($job['company_logo'])) ?>" alt="Logo" class="max-w-full max-h-full object-contain">
                                                        <?php else: ?>
                                                            <span class="text-xs font-bold text-gray-400"><?= strtoupper(substr($job['company_name'] ?? 'C', 0, 2)) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="pt-8 pb-5 px-5">
                                                    <h3 class="text-base font-bold text-gray-900 line-clamp-2 min-h-[3rem] mb-1">
                                                        <?= htmlspecialchars($job['title'] ?? 'Job Title') ?>
                                                    </h3>
                                                    <p class="text-xs font-semibold text-gray-500 mb-3 uppercase tracking-wide">
                                                        <?= htmlspecialchars($job['company_name'] ?? 'Company') ?>
                                                    </p>

                                                    <div class="flex items-center text-xs text-gray-500 gap-4 mb-5">
                                                        <span class="truncate max-w-[50%]"><?= htmlspecialchars($job['location_display'] ?? 'Remote') ?></span>
                                                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                                        <span><?= htmlspecialchars($job['experience_display'] ?? '0-5 Years') ?></span>
                                                    </div>

                                                    <div class="block w-full text-center py-2.5 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors">
                                                        View Job
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
                            <img src="assets/images/footer-cta.webp" alt="Good Company" class="w-full h-82 object-fill">
                        </div>
                    </div>
                    <div>
                        <h3 class="text-3xl md:text-4xl font-extrabold text-gray-900">Good Life Begins With <br>A Good Company</h3>
                        <p class="mt-4 text-gray-600">Your best life begins with the right, impactful career move. Discover leading, future-focused companies worldwide that prioritize people, professional growth, and true well-being. Find your next fulfilling role and start to instantly thrive.</p>
                        <div class="mt-6 flex items-center gap-3">
                            <a href="<?= $base ?>jobs" class="px-6 py-3 rounded-full bg-blue-600 hover:bg-blue-700 hover:text-white text-white font-semibold">Search Job</a>
                            <a href="<?= $base ?>jobs" class="px-6 py-3 rounded-full bg-blue-50 text-blue-600 font-semibold">Learn more</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-12 bg-white">
            <div class="container mx-auto px-6 lg:px-[7.5rem]">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <p class="text-3xl font-extrabold text-blue-600">12k+</p>
                        <p class="font-semibold text-gray-900">Clients worldwide</p>
                        <p class="mt-1 text-gray-500 text-sm">Trusted by organizations worldwide for their talent needs and successful hiring.</p>
                    </div>
                    <div>
                        <p class="text-3xl font-extrabold text-blue-600">20k+</p>
                        <p class="font-semibold text-gray-900">Active resume</p>
                        <p class="mt-1 text-gray-500 text-sm">A vibrant community of top-tier professionals ready for their next career move.</p>
                    </div>
                    <div>
                        <p class="text-3xl font-extrabold text-blue-600">18k+</p>
                        <p class="font-semibold text-gray-900">Companies</p>
                        <p class="mt-1 text-gray-500 text-sm">Opportunities with leading employers committed to growth and exceptional work culture.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 bg-white">
            <div class="container mx-auto px-6 lg:px-[7.5rem]">
                <div class="rounded-3xl overflow-hidden bg-black relative min-h-[320px] md:min-h-[420px]">
                    <img src="https://img.freepik.com/free-photo/business-finance-employment-female-successful-entrepreneurs-concept-professional-asian-businesswoman-glasses-having-lunch-drinking-takeaway-coffee-using-mobile-phone_1258-94505.jpg?t=st=1765458060~exp=1765461660~hmac=f72564bf528341340be6657fa8c7e05519b922e24afe1186d1ba8568dd691752&w=1060"
                        alt=""
                        class="absolute inset-0 w-full h-full object-cover pointer-events-none" style="object-position: center 15%;" />
                    <div class="absolute inset-0 bg-gradient-to-r from-black/45 via-black/25 to-transparent"></div>
                    <div class="relative px-8 py-14 md:px-16 md:py-16 lg:pl-20 text-white max-w-xl md:ml-6 lg:ml-10 xl:ml-14">
                        <h3 class="text-3xl md:text-4xl font-semibold text-blue-600">Find Real Jobs <br>From Verified Employers</h3>
                        <p class="mt-4 font-semibold text-gray-700">Explore verified job opportunities across industries.
                            Apply directly to trusted employers and grow your career with confidence.
                        </p> <br>
                        <p class="font-semibold text-gray-700">Your next career move starts here.</p><br>
                        <a href="<?= $base ?>register-candidate" class="mt-6 inline-block px-6 py-3 rounded-full bg-blue-600 hover:bg-blue-700 hover:text-white text-white font-semibold">Get Started — It’s Free</a>
                    </div>
                </div>
            </div>
        </section>
        <?php
        // Helper function to get category icon based on category name - matching Figma design
        function getCategoryIcon($categoryName)
        {
            $name = strtolower(trim($categoryName ?? ''));
            if (strpos($name, 'agriculture') !== false || strpos($name, 'farming') !== false) {
                // Plant sprout with two leaves
                return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C8 2 6 4 6 8c0 4 2 6 6 6s6-2 6-6c0-4-2-6-6-6z"/><path d="M8 12h8"/><path d="M10 8c0 2 1 3 2 3s2-1 2-3"/></svg>';
            } elseif (strpos($name, 'metal') !== false || strpos($name, 'production') !== false || strpos($name, 'manufacturing') !== false) {
                // Gear/cog icon
                return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 1v6m0 6v6m9-9h-6m-6 0H3m15.364 6.364l-4.243-4.243m-4.242 4.242l-4.243-4.243m8.485 0l-4.242 4.242m-4.243-4.243l-4.242 4.242"/></svg>';
            } elseif (strpos($name, 'commerce') !== false || strpos($name, 'retail') !== false || strpos($name, 'shopping') !== false) {
                // Shopping bag icon
                return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>';
            } elseif (strpos($name, 'construction') !== false || strpos($name, 'building') !== false) {
                // Hard hat/safety helmet icon
                return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>';
            } elseif (strpos($name, 'hotel') !== false || strpos($name, 'tourism') !== false || strpos($name, 'hospitality') !== false) {
                // Building/hotel icon
                return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><line x1="9" y1="9" x2="9" y2="9"/><line x1="9" y1="12" x2="9" y2="12"/><line x1="9" y1="15" x2="9" y2="15"/><line x1="9" y1="18" x2="9" y2="18"/></svg>';
            } elseif (strpos($name, 'education') !== false || strpos($name, 'teaching') !== false || strpos($name, 'school') !== false) {
                // Graduation cap icon
                return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>';
            } elseif (strpos($name, 'financial') !== false || strpos($name, 'finance') !== false || strpos($name, 'banking') !== false) {
                // Stacked coins icon
                return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>';
            } elseif (strpos($name, 'transport') !== false || strpos($name, 'logistics') !== false || strpos($name, 'shipping') !== false) {
                // Bus/vehicle icon
                return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"/><rect x="2" y="4" width="20" height="16" rx="2"/><circle cx="6" cy="8" r="1" fill="currentColor"/><circle cx="18" cy="8" r="1" fill="currentColor"/></svg>';
            } else {
                // Default checkmark icon
                return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>';
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
                        <p class="text-sm font-semibold text-blue-600">Client Testimonials</p>
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
                                <div class="absolute w-72 h-72 bg-blue-50 rounded-full blur-2xl opacity-60 animate-pulse"></div>

                                <div class="relative w-64 h-64 md:w-80 md:h-80">
                                    <template x-for="(item, index) in items" :key="index">
                                        <img :src="item.avatar"
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
                                                <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="items[active].title"></h3>
                                            </template>
                                            <template x-if="items[active].video_url">
                                                <div class="space-y-3">
                                                    <template x-if="isYouTube(items[active].video_url)">
                                                        <iframe :src="youtubeEmbed(items[active].video_url)" class="w-full h-52 md:h-64 rounded-lg" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                                    </template>
                                                    <template x-if="!isYouTube(items[active].video_url)">
                                                        <video :src="items[active].video_url" class="w-full h-52 md:h-64 rounded-lg" controls></video>
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
                                                <p class="text-sm text-gray-600"><span x-text="items[active].designation"></span><span x-show="items[active].company"> • <span x-text="items[active].company"></span></span></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <div class="mt-4 flex items-center gap-3" x-show="items.length > 1">
                                    <button @click="prev()" class="w-10 h-10 rounded-full bg-white shadow ring-1 ring-gray-200 hover:bg-gray-50 flex items-center justify-center">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </button>
                                    <button @click="next()" class="w-10 h-10 rounded-full bg-white shadow ring-1 ring-gray-200 hover:bg-gray-50 flex items-center justify-center">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    <div class="ml-2 flex items-center gap-1">
                                        <template x-for="(d,i) in items" :key="i">
                                            <span @click="go(i)" :class="i===active ? 'w-6 bg-blue-600' : 'w-3 bg-blue-200'" class="h-2 rounded-full cursor-pointer transition-all"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- <div>
                    <p class="text-sm font-semibold text-blue-600">Candidate Testimonials</p>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900">What Candidates Say</h2>
                    <div x-data="{
                            items: [],
                            active: 0,
                            timer: null,
                            init() {
                                this.items = Array.isArray(window.candidateTestimonials) ? window.candidateTestimonials : [];
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
                            ring() { return this.items.slice(0, Math.min(this.items.length, 7)); },
                            orbitStyle(i) {
                                const count = Math.min(this.items.length, 7);
                                const r = 140;
                                const angle = (2 * Math.PI / count) * i;
                                const x = Math.cos(angle) * r;
                                const y = Math.sin(angle) * r;
                                return `left: calc(50% + ${x}px); top: calc(50% + ${y}px); transform: translate(-50%, -50%);`;
                            },
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
                        <div class="relative h-80 lg:h-[420px] scroll-fade-up">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-56 h-56 rounded-full bg-blue-50"></div>
                                <div class="absolute w-80 h-80 rounded-full border-2 border-gray-200"></div>
                            </div>
                            <div class="orbit-wrap absolute inset-0">
                                <template x-for="(t,i) in ring()" :key="i">
                                    <img :src="t.avatar" :alt="t.name" class="absolute w-16 h-16 rounded-full object-cover ring-2 ring-white shadow-lg" :style="orbitStyle(i)">
                                </template>
                            </div>
                        </div>
                        <div class="scroll-fade-up">
                            <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                                <template x-if="items.length">
                                    <div>
                                        <template x-if="items[active].title">
                                            <h3 class="text-xl font-bold text-gray-900 mb-2" x-text="items[active].title"></h3>
                                        </template>
                                        <template x-if="items[active].video_url">
                                            <div class="space-y-3">
                                                <template x-if="isYouTube(items[active].video_url)">
                                                    <iframe :src="youtubeEmbed(items[active].video_url)" class="w-full h-52 md:h-64 rounded-lg" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                                </template>
                                                <template x-if="!isYouTube(items[active].video_url)">
                                                    <video :src="items[active].video_url" class="w-full h-52 md:h-64 rounded-lg" controls></video>
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
                                            <p class="text-sm text-gray-600"><span x-text="items[active].designation"></span><span x-show="items[active].company"> • <span x-text="items[active].company"></span></span></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="mt-4 flex items-center gap-3" x-show="items.length > 1">
                                <button @click="prev()" class="w-10 h-10 rounded-full bg-white shadow ring-1 ring-gray-200 hover:bg-gray-50 flex items-center justify-center">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <button @click="next()" class="w-10 h-10 rounded-full bg-white shadow ring-1 ring-gray-200 hover:bg-gray-50 flex items-center justify-center">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div class="ml-2 flex items-center gap-1">
                                    <template x-for="(d,i) in items" :key="i">
                                        <span @click="go(i)" :class="i===active ? 'w-6 bg-blue-600' : 'w-3 bg-blue-200'" class="h-2 rounded-full cursor-pointer transition-all"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
                </div>
            </div>
        </section>
        <section class="relative py-16 bg-white overflow-hidden">
            <div class="absolute -top-4 right-0 pointer-events-none select-none">
                <img src="<?= $base ?>assets/images/bg-arrow.svg" alt="" class="w-24 h-24 opacity-40" />
            </div>
            <div class="container mx-auto px-6 lg:px-[7.5rem] relative z-10">
                <h1
                    class="text-center font-semibold text-gray-800"
                    style="font-size: 2.25rem; line-height: 2.75rem;"
                    data-aos="fade-up">
                    More than 12k recruiters from leading tech companies are hiring
                </h1>
                <div class="mt-8" data-aos="fade-up" data-aos-delay="100">
                    <?php if (!empty($employerLogos) && is_array($employerLogos)): ?>
                        <div class="relative overflow-hidden">
                            <div class="flex items-center gap-12" style="animation: partnerScroll 38s linear infinite;">
                                <?php foreach ($employerLogos as $emp): ?>
                                    <?php
                                    $logo = fix_url($emp['logo_url'] ?? '');
                                    if (!empty($logo) && strpos($logo, 'http') !== 0) $logo = $base . ltrim($logo, '/');
                                    ?>
                                    <div class="flex-shrink-0">
                                        <?php if (!empty($logo)): ?>
                                            <img src="<?= htmlspecialchars($logo) ?>" alt="<?= htmlspecialchars($emp['company_name'] ?? '') ?>" class="h-12 object-contain partners-logo transition duration-300 ease-out hover:scale-105" />
                                        <?php else: ?>
                                            <span class="text-gray-500 text-sm"><?= htmlspecialchars($emp['company_name'] ?? '') ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                                <?php foreach ($employerLogos as $emp): ?>
                                    <?php
                                    $logo = fix_url($emp['logo_url'] ?? '');
                                    if (!empty($logo) && strpos($logo, 'http') !== 0) $logo = $base . ltrim($logo, '/');
                                    ?>
                                    <div class="flex-shrink-0">
                                        <?php if (!empty($logo)): ?>
                                            <img src="<?= htmlspecialchars($logo) ?>" alt="<?= htmlspecialchars($emp['company_name'] ?? '') ?>" class="h-12 object-contain partners-logo transition duration-300 ease-out hover:scale-105" />
                                        <?php else: ?>
                                            <span class="text-gray-500 text-sm"><?= htmlspecialchars($emp['company_name'] ?? '') ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-gray-500">Recruiter logos will appear here.</div>
                    <?php endif; ?>
                </div>
            </div>
            <style>
                @keyframes partnerScroll {
                    0% {
                        transform: translateX(0);
                    }

                    100% {
                        transform: translateX(-50%);
                    }
                }
            </style>
        </section>
        <section class="py-16 bg-white">
            <div class="container mx-auto px-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 text-center w-full">Trending Blogs</h2>
                    <a href="<?= $base ?>blog" class="hidden md:inline-block text-blue-600 font-semibold hover:underline whitespace-nowrap">View all</a>
                </div>
                <?php if (!empty($blogs) && is_array($blogs)): ?>
                    <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <?php foreach (array_slice($blogs, 0, 8) as $blog): ?>
                            <a href="<?= $base ?>blog/<?= urlencode($blog['slug'] ?? ($blog['id'] ?? '')) ?>" class="group block rounded-2xl border border-gray-200 overflow-hidden bg-white hover:shadow-md transition" target="_blank">
                                <div class="bg-gray-100">
                                    <?php
                                    $img = $blog['featured_image'] ?? '';
                                    if (!empty($img) && strpos($img, 'http') !== 0) {
                                        $img = $base . ltrim($img, '/');
                                    }
                                    ?>
                                    <?php if (!empty($img)): ?>
                                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($blog['title'] ?? '') ?>" class="w-full h-40 object-cover">
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
    </div>
    <?php
    // require __DIR__ . '/include/footer.php';
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Find location selects by checking first option text or name
            const selects = document.querySelectorAll('select');
            let locSelects = [];
            selects.forEach(s => {
                // Check if first option mentions location/country or if it's the specific location select
                if (s.options.length > 0 && (
                        s.options[0].text.toLowerCase().includes('location') ||
                        s.options[0].text.toLowerCase().includes('country')
                    )) {
                    locSelects.push(s);
                }
            });

            if (locSelects.length > 0) {
                const applyCountry = (country) => {
                    if (!country) return;
                    const target = String(country).toLowerCase();
                    locSelects.forEach(sel => {
                        for (let i = 0; i < sel.options.length; i++) {
                            const optText = String(sel.options[i].text || '').toLowerCase();
                            const optVal = String(sel.options[i].value || '').toLowerCase();
                            if (optText.includes(target) || optVal.includes(target)) {
                                sel.selectedIndex = i;
                                sel.dispatchEvent(new Event('change'));
                                break;
                            }
                        }
                    });
                };
                const fallbackIp = () => {
                    fetch('/api/location/detect', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(data => applyCountry(data && data.country ? data.country : ''))
                    .catch(() => {});
                };
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition((pos) => {
                        const lat = pos.coords.latitude;
                        const lon = pos.coords.longitude;
                        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`)
                            .then(res => res.json())
                            .then(data => {
                                const addr = data && data.address ? data.address : {};
                                const name = addr.country || '';
                                const code = (addr.country_code || '').toUpperCase();
                                applyCountry(name || code);
                            })
                            .catch(fallbackIp);
                    }, fallbackIp, { enableHighAccuracy: true, timeout: 8000 });
                } else {
                    fallbackIp();
                }
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            try {
                if (window.Notification && Notification.permission === 'default') {
                    Notification.requestPermission();
                }
            } catch (e) {}
            try {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(){}, function(){}, { enableHighAccuracy: true, timeout: 8000 });
                }
            } catch (e) {}
        });
    </script>
</body>

</html>
