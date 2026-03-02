<?php $base = $base ?? '/'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Candidate Profile | Mindware Infotech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[x-cloak]').forEach(function (el) {
                el.removeAttribute('x-cloak');
            });
            // If Alpine fails to load, render <template x-if> contents and unhide sections
            setTimeout(function(){
                var alpineOk = !!(window.Alpine && window.Alpine.start && window.Alpine.version && window.Alpine.version !== 'stub');
                if (!alpineOk) {
                    document.documentElement.classList.add('no-alpine');
                    // Render template bodies
                    document.querySelectorAll('template[x-if]').forEach(function(tpl){
                        if (tpl.content) {
                            tpl.replaceWith(tpl.content.cloneNode(true));
                        }
                    });
                    // Ensure any x-show areas are visible
                    document.querySelectorAll('[x-show]').forEach(function(el){
                        el.style.display = '';
                        el.hidden = false;
                        el.removeAttribute('hidden');
                    });
                }
            }, 500);
        });

        window.multiSelect = function(formData, options, field) {
            return {
                open: false,
                options: options,
                toggle(value) {
                    if (formData[field].includes(value)) {
                        formData[field] = formData[field].filter(v => v !== value);
                    } else {
                        formData[field].push(value);
                    }
                },
                isChecked(value) {
                    return formData[field].includes(value);
                },
                count() {
                    return formData[field].length;
                }
            }
        }

        // Define component factory to avoid parsing a large object in x-data
        window.candidateAccount = function() {
            return {
                mobileMenu: false,
                isEditing: true,
                isSaving: false,
                formData: {
                    full_name: '',
                    preferred_name: '',
                    pronouns: '',
                    roles: [],
                    focus_areas: []
                },
                async saveProfile() {
                    this.isSaving = true;
                    try {
                        const response = await fetch('/social-candidate/accountcandidate/save', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-Token': (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')) || ''
                            },
                            body: JSON.stringify(this.formData)
                        });
                        const result = await response.json();
                        if (result?.success) {
                            this.isEditing = false;
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        } else {
                            alert('Error saving profile: ' + (result?.error || 'Unknown error'));
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred while saving.');
                    } finally {
                        this.isSaving = false;
                    }
                }
            }
        }
    </script>
    <script>
        (function () {
            var tried = 0;
            var sources = [
                'https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js',
                'https://unpkg.com/alpinejs@3.13.5/dist/cdn.min.js'
            ];
            function stub() {
                if (!window.Alpine) {
                    window.Alpine = { start: function(){}, data: function(){}, store: function(){}, version: 'stub' };
                }
            }
            function tryNext() {
                if (tried >= sources.length) {
                    console.warn('All Alpine CDNs failed; using stub');
                    stub();
                    return;
                }
                var src = sources[tried++];
                var s = document.createElement('script');
                s.src = src;
                s.defer = true;
                s.onload = function(){ /* loaded */ };
                s.onerror = function(){ tryNext(); };
                document.head.appendChild(s);
                // Fallback timeout in case of corrupted response
                setTimeout(function(){
                    if (!window.Alpine || (window.Alpine.version === 'stub')) return;
                }, 4000);
            }
            window.addEventListener('error', function(e){
                var f = (e && e.filename) ? e.filename : '';
                if (f && /alpinejs/i.test(f)) {
                    console.warn('Alpine parse error, falling back to stub');
                    stub();
                }
            }, true);
            tryNext();
        })();
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .text-custom-blue {
            color: #5b6bd5;
        }

        .bg-custom-blue {
            background-color: #5b6bd5;
        }

        .text-custom-red {
            color: #e15f55;
        }

        .border-custom-red {
            border-color: #e15f55;
        }

        .text-custom-body {
            color: #54595f;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col text-custom-body"
      x-data="candidateAccount()">

<header class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-10 lg:px-12 py-4 md:py-8 flex items-center justify-between">
        <div class="flex-shrink-0">
            <a href="<?php echo $base; ?>">
                <img src="<?= $base ?>uploads/Mindware-infotech.png" class="h-11">
            </a>
        </div>

        <button @click="mobileMenu = !mobileMenu" class="min-[900px]:hidden p-2 text-gray-600">
            <svg x-show="!mobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
            <svg x-show="mobileMenu" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <nav class="hidden min-[900px]:flex items-center gap-4 lg:gap-8 text-base font-medium">
            <a href="/candidatelisting" class="hover:text-custom-red transition-colors pb-1">Applications & saved
                listings</a>
            <a href="/social-candidate/candidatesubscriptions" class="hover:text-custom-red transition-colors pb-1">Job
                alerts</a>
            <a href="<?php echo $base; ?>social-candidate/accountcandidate"
               class="text-custom-red border-b-2 border-custom-red pb-1">Account & profile</a>
            <a href="/social-services/logout" class="text-black hover:text-custom-red transition-colors pb-1">Logout</a>
        </nav>
    </div>
</header>

<nav class="bg-black border-b border-gray-100 overflow-x-auto whitespace-nowrap">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-10 lg:px-12">
        <ul class="flex items-center justify-start min-[900px]:justify-center gap-4 sm:gap-6 md:gap-8 text-xs sm:text-sm py-4 text-white">
            <li><a href="<?php echo $base; ?>social-services" class="hover:text-custom-red transition">Back to Home</a>
            </li>
            <li><a href="/find-a-job" class="hover:text-custom-red transition">Find a job</a></li>
            <li><a href="/searchEmployers" class="hover:text-custom-red transition">Search employers</a></li>
            <li><a href="/hiringInsight" class="hover:text-custom-red transition">Career insights</a></li>
            <li><a href="/aboutus" class="hover:text-custom-red transition">About us</a></li>
            <li><a href="/supports" class="hover:text-custom-red transition">Get Help</a></li>
        </ul>
    </div>
</nav>

<main class="flex-1">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-10 lg:px-12 py-10">

        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight"
                    x-text="isEditing ? 'Tell us about yourself' : 'Hello, ' + formData.preferred_name + '! 👋'">
                    Tell us about yourself
                </h1>
                <p class="text-[15px] text-gray-500 mt-2" x-show="isEditing">
                    This helps us personalize job recommendations for you.
                </p>
                <div x-show="!isEditing" x-cloak class="mt-6">
                    <h2 class="font-bold text-black text-sm">Your profile:</h2>
                    <p class="text-gray-500 text-sm">Details saved to your account for easier applications.</p>
                </div>
            </div>
            <div>
                <template x-if="isEditing">
                    <button type="submit" form="candidateForm" :disabled="isSaving"
                            class="inline-flex items-center justify-center gap-2 w-40 h-12 px-4 py-2 text-sm font-semibold bg-red-500 text-white transition shadow-sm">
                        <span>Save Profile</span>
                    </button>
                </template>
                <!-- Fallback button when Alpine is unavailable -->
                <noscript>
                    <button type="submit" form="candidateForm"
                            class="inline-flex items-center justify-center gap-2 w-40 h-12 px-4 py-2 text-sm font-semibold bg-red-500 text-white transition shadow-sm">
                        <span>Save Profile</span>
                    </button>
                </noscript>

                <template x-if="!isEditing">
                    <button @click="isEditing = true"
                            class="bg-red-500 text-white px-10 py-3 rounded text-sm font-bold  border-2 border-custom-red flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                        Edit Profile
                    </button>
                </template>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 sm:p-10 transition-all duration-500">

            <div x-show="isEditing" x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-4">
                <form id="candidateForm" @submit.prevent="saveProfile"
                      class="grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-10">

                    <div>
                        <label class="block text-sm font-bold text-gray-800">Your full name <span
                                    class="text-custom-red">*</span></label>
                        <p class="text-xs text-gray-400 mb-2">Your legal name as per records.</p>
                        <input type="text" x-model="formData.full_name" required
                               class="w-full border border-gray-300 rounded p-3 focus:ring-2 focus:ring-custom-blue/20 focus:border-custom-blue focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-800">Preferred name <span
                                    class="text-custom-red">*</span></label>
                        <p class="text-xs text-gray-400 mb-2">What should we call you?</p>
                        <input type="text" x-model="formData.preferred_name" required
                               class="w-full border border-gray-300 rounded p-3 focus:ring-2 focus:ring-custom-blue/20 focus:border-custom-blue focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-800">Pronouns</label>
                        <p class="text-xs text-gray-400 mb-2">(Optional)</p>
                        <select x-model="formData.pronouns"
                                class="w-full border border-gray-300 rounded p-3 focus:ring-2 focus:ring-custom-blue/20 focus:outline-none bg-white">
                            <option value="">Select Pronouns</option>
                            <option>She / Her / Hers</option>
                            <option>He / Him / His</option>
                            <option>They / Them / Theirs</option>
                            <option>Ze / Hir</option>
                        </select>
                    </div>

                    <div
                            x-data="multiSelect(formData, [
    'Aging / Seniors','Agriculture & Nutrition','Alternative & Sustainable Energy',
    'Animal-Related','Arts, Culture & Humanities','Association / Mutual & Membership Benefit / Union',
    'Broadcast / Journalism','Childcare / Preschool / After-school Care',
    'Civil Rights, Social Action & Advocacy','Community Improvement & Capacity Building',
    'Conservation / Environment Advocacy','Crime & Legal-Related',
    'Culture & Humanities','Disability-Related','Disaster Preparedness & Relief',
    'Disease & Medical Disorder Related','Education','Employment',
    'Food, Agriculture & Nutrition','Foreign Affairs & National Security',
    'Government','Health Care','Housing & Shelter','Human Services',
    'International, Foreign Affairs & National Security','Medical Research',
    'Mental Health & Crisis Intervention','Philanthropy, Voluntarism & Grantmaking Foundations',
    'Public Safety, Disaster Preparedness & Relief','Recreation & Sports',
    'Religion-Related','Research','Science & Technology','Social Action & Advocacy',
    'Veterans','Voluntarism & Grantmaking Foundations',
    'Voluntary Health Associations & Medical Disciplines',
    'Youth Development','Zoo','Zoological Society','Unknown / Other'
  ], 'focus_areas')"
                            class="relative"
                    >
                        <label class="block text-sm font-bold text-gray-800">
                            Focus Areas
                        </label>
                        <p class="text-xs text-gray-400 mb-2">Select all that apply.</p>

                        <!-- Closed State -->
                        <div
                                @click="open = !open"
                                class="border border-gray-300 rounded p-3 bg-white cursor-pointer flex justify-between items-center text-sm hover:border-gray-400"
                        >
    <span class="text-gray-600"
          x-text="count() ? count() + ' focus areas selected' : 'Select focus areas'">
    </span>

                            <svg class="w-4 h-4 text-gray-400 transition-transform"
                                 :class="open ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>

                        <!-- Dropdown -->
                        <div
                                x-show="open"
                                x-cloak
                                @click.outside="open = false"
                                class="absolute z-50 mt-1 w-full bg-white border rounded shadow-xl max-h-64 overflow-y-auto"
                        >
                            <template x-for="opt in options" :key="opt">
                                <div
                                        @click="toggle(opt)"
                                        class="flex items-center px-4 py-3 cursor-pointer hover:bg-gray-50 border-b last:border-0"
                                >
                                    <input type="checkbox"
                                           class="mr-3 h-4 w-4 rounded text-custom-blue"
                                           :checked="isChecked(opt)">
                                    <span class="text-sm text-gray-700" x-text="opt"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div
                            x-data="multiSelect(formData, [
    'Accounting / Finance','Administrative / Clerical','Advocacy / Lobbying','Animal Care',
    'Campaign Management / Canvassing / Field Organizer',
    'Child Care / After school / Counselor / Mentor',
    'Childhood Development / Early Childhood Education','Community Engagement',
    'Conservation','Consulting','Creative / Art Production',
    'Customer Service','Customer Service / Retail','Development / Fundraising',
    'Direct Service / Social Service','Education / Teaching','Event Planning',
    'Executive / Senior Management',
    'Facilities & Warehouse Management / Equipment / Drivers',
    'Food Service','Health / Medical / Nutrition',
    'Home Health Aid / Senior Care','Horticulture / Groundskeeper',
    'Housing / Construction','Human Resources / Recruiting',
    'Journalism / Broadcasting','Legal','Library Science',
    'Marketing / Communications / Public Relations',
    'Member / Membership Management','Mental Health Services',
    'Operations / Business Management','Program / Project Management',
    'Public Policy / Administration',
    'Recreational / Camp Associates & Management','Research',
    'Sales / Business Development','Social Work / Counseling',
    'Technology / Data Management','Training / Curriculum Development',
    'Transportation','Volunteer Services','Unknown / Other'
  ], 'roles')"
                            class="relative"
                    >
                        <label class="block text-sm font-bold text-gray-800">
                            What categories of roles are you interested in?
                        </label>
                        <p class="text-xs text-gray-400 mb-2">Please select all that apply.</p>

                        <div
                                @click="open = !open"
                                class="border border-gray-300 rounded p-3 bg-white cursor-pointer flex justify-between items-center text-sm hover:border-gray-400"
                        >
    <span class="text-gray-600"
          x-text="count() ? count() + ' roles selected' : 'Select role categories'">
    </span>

                            <svg class="w-4 h-4 text-gray-400 transition-transform"
                                 :class="open ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>

                        <div
                                x-show="open"
                                x-cloak
                                @click.outside="open = false"
                                class="absolute z-50 mt-1 w-full bg-white border rounded shadow-xl max-h-64 overflow-y-auto"
                        >
                            <template x-for="opt in options" :key="opt">
                                <div
                                        @click="toggle(opt)"
                                        class="flex items-center px-4 py-3 cursor-pointer hover:bg-gray-50 border-b last:border-0"
                                >
                                    <input type="checkbox"
                                           class="mr-3 h-4 w-4 rounded text-custom-blue"
                                           :checked="isChecked(opt)">
                                    <span class="text-sm text-gray-700" x-text="opt"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                </form>
            </div>

            <div
                    x-show="!isEditing"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-cloak
            >
                <!-- SINGLE COLUMN LAYOUT -->
                <div class="space-y-10">

                    <!-- TOP INFO ROW -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-20 gap-y-6">

                        <div>
                            <h3 class="text-[13px] text-gray-600 mb-1">
                                Full name
                            </h3>
                            <p class="text-[14px] text-gray-900">
                                <span x-text="formData.full_name"></span>
                            </p>
                        </div>

                        <div>
                            <h3 class="text-[13px] text-gray-600 mb-1">
                                Preferred name
                            </h3>
                            <p class="text-[14px] text-gray-900">
                                <span x-text="formData.preferred_name"></span>
                            </p>
                        </div>

                        <div>
                            <h3 class="text-[13px] text-gray-600 mb-1">
                                Pronouns
                            </h3>
                            <p class="text-[14px] text-gray-900">
                                <span x-text="formData.pronouns || 'Not specified'"></span>
                            </p>
                        </div>

                    </div>

                    <!-- ROLE CATEGORIES -->
                    <div>
                        <h3 class="text-[14px] font-medium text-gray-900 mb-2">
                            Role categories you are interested in:
                        </h3>

                        <p class="text-[14px] text-gray-900 leading-relaxed max-w-5xl">
                            <template x-if="formData.roles.length">
                                <span x-text="formData.roles.join(', ')"></span>
                            </template>

                            <template x-if="formData.roles.length === 0">
          <span class="text-gray-400 italic">
            No roles selected
          </span>
                            </template>
                        </p>
                    </div>

                    <!-- MISSION FOCUS AREAS -->
                    <div>
                        <h3 class="text-[14px] font-medium text-gray-900 mb-2">
                            Mission focus areas you are interested in:
                        </h3>

                        <p class="text-[14px] text-gray-900 leading-relaxed max-w-5xl">
                            <template x-if="formData.focus_areas.length">
                                <span x-text="formData.focus_areas.join(', ')"></span>
                            </template>

                            <template x-if="formData.focus_areas.length === 0">
          <span class="text-gray-400 italic">
            No focus areas selected
          </span>
                            </template>
                        </p>
                    </div>

                </div>
            </div>


            <div class="mt-12 pt-8 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-xs text-gray-400 italic">
                    Your information is shared with employers only when you submit an application.
                </p>
            </div>

        </div>
    </div>
</main>

<footer class="bg-[#232323] py-[50px] text-center">
    <div class="text-[#7a7a7a] text-[13px]">
        <p>© 2026 Mindware Infotech.</p>
    </div>
</footer>

<script></script>


</body>
</html>
