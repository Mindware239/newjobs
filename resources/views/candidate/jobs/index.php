<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Jobs - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <meta name="description" content="Find your dream job with Mindware Infotech. Browse our latest job listings and apply today!">
    <meta name="keywords" content="jobs, job listings, job search, Mindware Infotech">
    <meta name="author" content="Mindware Infotech">
    <link rel="canonical" href="<?= htmlspecialchars($job['url'] ?? '') ?>">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .job-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .job-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .btn-primary {
            background: #2563eb;
            border: none;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
        }
        .btn-primary:hover {
            background: #1d4ed8;
            box-shadow: 0 6px 12px rgba(37, 99, 235, 0.3);
            transform: translateY(-1px);
        }
        .search-input {
            background-color: #F9FAFB;
            border-color: #E5E7EB;
            transition: all 0.2s ease;
        }
        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            background-color: #fff;
        }
        
        /* Premium Badges */
        .badge-premium {
            background: linear-gradient(135deg, #2563eb 0%, #93c5fd 100%);
            color: white;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-gray-50 antialiased text-gray-800 min-h-screen" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 800)">
    <!-- Skeleton Loader -->
    <div x-show="!loaded" x-transition.opacity.duration.500ms class="fixed inset-0 bg-white z-50 flex flex-col overflow-hidden">
        <!-- Header Skeleton -->
        <div class="h-20 border-b border-gray-100 flex items-center px-6 lg:px-[7.5rem] justify-between bg-white shrink-0">
            <div class="w-40 h-10 bg-gray-200 rounded animate-pulse"></div>
            <div class="hidden md:flex gap-8">
                <div class="w-20 h-4 bg-gray-200 rounded animate-pulse"></div>
                <div class="w-20 h-4 bg-gray-200 rounded animate-pulse"></div>
                <div class="w-20 h-4 bg-gray-200 rounded animate-pulse"></div>
            </div>
            <div class="flex gap-4">
                <div class="w-24 h-10 bg-gray-200 rounded animate-pulse"></div>
                <div class="w-24 h-10 bg-gray-200 rounded animate-pulse"></div>
            </div>
        </div>
        
        <!-- Content Skeleton -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex gap-8 h-full overflow-hidden">
            <!-- Sidebar Skeleton -->
            <div class="hidden lg:block w-64 flex-shrink-0 space-y-6">
                <div class="h-8 w-32 bg-gray-200 rounded animate-pulse"></div>
                <div class="h-40 bg-gray-100 rounded-lg animate-pulse"></div>
                <div class="h-40 bg-gray-100 rounded-lg animate-pulse"></div>
                <div class="h-40 bg-gray-100 rounded-lg animate-pulse"></div>
            </div>
            
            <!-- Main List Skeleton -->
            <div class="flex-1 space-y-6">
                <div class="h-14 bg-gray-100 rounded-lg animate-pulse"></div>
                <div class="space-y-4">
                    <div class="h-48 bg-white border border-gray-100 rounded-lg p-6 shadow-sm">
                        <div class="flex justify-between mb-4">
                            <div class="w-1/2 h-6 bg-gray-200 rounded animate-pulse"></div>
                            <div class="w-10 h-10 bg-gray-200 rounded animate-pulse"></div>
                        </div>
                        <div class="w-1/4 h-4 bg-gray-200 rounded animate-pulse mb-6"></div>
                        <div class="flex gap-3">
                            <div class="w-24 h-8 bg-gray-200 rounded animate-pulse"></div>
                            <div class="w-24 h-8 bg-gray-200 rounded animate-pulse"></div>
                            <div class="w-24 h-8 bg-gray-200 rounded animate-pulse"></div>
                        </div>
                    </div>
                    <div class="h-48 bg-white border border-gray-100 rounded-lg p-6 shadow-sm">
                        <div class="flex justify-between mb-4">
                            <div class="w-1/2 h-6 bg-gray-200 rounded animate-pulse"></div>
                            <div class="w-10 h-10 bg-gray-200 rounded animate-pulse"></div>
                        </div>
                        <div class="w-1/4 h-4 bg-gray-200 rounded animate-pulse mb-6"></div>
                        <div class="flex gap-3">
                            <div class="w-24 h-8 bg-gray-200 rounded animate-pulse"></div>
                            <div class="w-24 h-8 bg-gray-200 rounded animate-pulse"></div>
                            <div class="w-24 h-8 bg-gray-200 rounded animate-pulse"></div>
                        </div>
                    </div>
                    <div class="h-48 bg-white border border-gray-100 rounded-lg p-6 shadow-sm">
                        <div class="flex justify-between mb-4">
                            <div class="w-1/2 h-6 bg-gray-200 rounded animate-pulse"></div>
                            <div class="w-10 h-10 bg-gray-200 rounded animate-pulse"></div>
                        </div>
                        <div class="w-1/4 h-4 bg-gray-200 rounded animate-pulse mb-6"></div>
                        <div class="flex gap-3">
                            <div class="w-24 h-8 bg-gray-200 rounded animate-pulse"></div>
                            <div class="w-24 h-8 bg-gray-200 rounded animate-pulse"></div>
                            <div class="w-24 h-8 bg-gray-200 rounded animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php 
    $base = $base ?? '/'; 
    require __DIR__ . '/../../include/header.php'; 
    ?>
    
    <!-- Breadcrumbs -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <?php if (!empty($breadcrumbs)): ?>
                        <?php foreach ($breadcrumbs as $index => $crumb): ?>
                            <li>
                                <a href="<?= htmlspecialchars($crumb['url']) ?>" class="<?= $index === count($breadcrumbs) - 1 ? 'text-gray-900 font-medium' : 'text-gray-500 hover:text-gray-700' ?> text-sm">
                                    <?= htmlspecialchars($crumb['name']) ?>
                                </a>
                            </li>
                            <?php if ($index < count($breadcrumbs) - 1): ?>
                            <li>
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <li>
                        <a href="/" class="text-gray-500 hover:text-gray-700 text-sm">Home</a>
                    </li>
                    <li>
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </li>
                    <li>
                        <a href="/jobs" class="text-gray-500 hover:text-gray-700 text-sm">Jobs</a>
                    </li>
                    <?php if (!empty($filters['location'])): ?>
                    <li>
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </li>
                    <li>
                        <span class="text-gray-900 font-medium text-sm"><?= htmlspecialchars($filters['location']) ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($filters['keyword'])): ?>
                    <li>
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </li>
                    <li>
                        <span class="text-gray-900 font-medium text-sm"><?= htmlspecialchars($filters['keyword']) ?></span>
                    </li>
                    <?php endif; ?>
                    <?php endif; ?>
                </ol>
            </nav>
        </div>
    </div>

    <div x-data="jobSearch()" x-init="init()" x-cloak>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <!-- H1 Heading -->
            <div class="mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                    <?= htmlspecialchars($seo['h1'] ?? $pageTitle ?? 'Latest Jobs') ?>
                </h1>
                <?php if (!empty($jobCount)): ?>
                <p class="text-sm text-gray-500 mt-1">Showing <?= $jobCount ?> jobs</p>
                <?php endif; ?>
            </div>

            <!-- Top Search Bar -->
            <div class="bg-white rounded-lg p-4 mb-4">
                <form @submit.prevent="searchJobs()" class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative" x-data="jobTitleAutocomplete()">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <svg class="w-4 h-4 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Job Title or Keywords
                        </label>
                        <input type="text" 
                               x-model="filters.keyword"
                               @input="searchJobTitles($event.target.value)"
                               @keydown="handleJobTitleKeyDown($event)"
                               @focus="if(filters.keyword && filters.keyword.length >= 2) searchJobTitles(filters.keyword)"
                               @blur="setTimeout(() => showSuggestions = false, 200)"
                               placeholder="e.g. Web Developer, Software Engineer"
                               class="search-input w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-gray-500 text-sm bg-white">
                        <!-- Job Title Suggestions -->
                        <div x-show="showSuggestions && suggestions.length > 0" 
                             x-cloak
                             class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg max-h-60 overflow-auto">
                            <template x-for="(suggestion, index) in suggestions" :key="'title-' + index">
                                <div @click="selectJobTitle(suggestion, $event)"
                                     @mouseenter="selectedIndex = index"
                                     class="px-4 py-2 cursor-pointer hover:bg-gray-50"
                                     :class="selectedIndex === index ? 'bg-gray-50' : ''">
                                    <div class="text-sm font-medium text-gray-900" x-text="suggestion.title"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="flex-1 relative" x-data="locationAutocomplete()">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <svg class="w-4 h-4 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Location
                        </label>
                        <input type="text" 
                               x-model="filters.location"
                               @input="searchLocations($event.target.value)"
                               @keydown="handleLocationKeyDown($event)"
                               @focus="if(filters.location && filters.location.length >= 2) searchLocations(filters.location)"
                               @blur="setTimeout(() => showSuggestions = false, 200)"
                               placeholder="e.g. Delhi, Mumbai, Bangalore"
                               class="search-input w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:border-gray-500 text-sm bg-white">
                        <!-- Location Suggestions -->
                        <div x-show="showSuggestions && suggestions.length > 0" 
                             x-cloak
                             class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg max-h-60 overflow-auto">
                            <template x-for="(suggestion, index) in suggestions" :key="'loc-' + index">
                                <div @click="selectLocation(suggestion, $event)"
                                     @mouseenter="selectedIndex = index"
                                     class="px-4 py-2 cursor-pointer hover:bg-gray-50"
                                     :class="selectedIndex === index ? 'bg-gray-50' : ''">
                                    <div class="text-sm font-medium text-gray-900" x-text="suggestion.display"></div>
                                    <div class="text-xs text-gray-500" x-text="(suggestion.job_count || 0) + ' jobs'"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="flex items-end">
                    <button type="submit" 
                                class="btn-primary px-5 py-2.5 text-white font-semibold rounded-lg text-sm w-full sm:w-auto flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search Jobs
                    </button>
                    </div>
                </form>
            </div>

            <!-- Main Content: Filters + Job Listings -->
            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Mobile Filter Toggle Button -->
                <div class="lg:hidden">
                    <button @click="showFilters = !showFilters" 
                            class="w-full px-4 py-2.5 bg-blue-600 text-white font-medium rounded-lg border border-blue-600 flex items-center justify-between hover:bg-blue-700 hover:border-blue-700 transition">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Filters
                        </span>
                        <span class="bg-white text-gray-800 px-2 py-0.5 rounded text-xs font-medium" x-show="appliedFiltersCount > 0" x-text="appliedFiltersCount"></span>
                        <svg class="w-5 h-5 transition-transform" :class="showFilters ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>

                <!-- Left Sidebar: Filters -->
                <div class="w-full lg:w-64 flex-shrink-0 hidden lg:block" :class="{'!block': showFilters}" x-cloak>
                    <div class="bg-white rounded-lg p-4 lg:sticky lg:top-20">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                All Filters
                            </h3>
                            <span class="bg-blue-600 text-white px-2 py-0.5 rounded text-xs font-medium" x-show="appliedFiltersCount > 0" x-text="appliedFiltersCount"></span>
                        </div>
                        
                        <!-- Work Mode Filter -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2 cursor-pointer" @click="workModeExpanded = !workModeExpanded">
                                <h4 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    Work mode
                                </h4>
                                <svg class="w-4 h-4 text-gray-500 transition-transform" :class="workModeExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                            <div x-show="workModeExpanded" class="space-y-2 mt-2">
                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="checkbox" x-model="filters.work_mode" value="office" @change="applyFilters()" class="mr-2 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Work from office</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="checkbox" x-model="filters.work_mode" value="hybrid" @change="applyFilters()" class="mr-2 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Hybrid</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="checkbox" x-model="filters.work_mode" value="remote" @change="applyFilters()" class="mr-2 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">Remote</span>
                                </label>
                            </div>
                        </div>

                        <!-- Experience Filter -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2 cursor-pointer" @click="experienceExpanded = !experienceExpanded">
                                <h4 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                    </svg>
                                    Experience
                                </h4>
                                <svg class="w-4 h-4 text-gray-500 transition-transform" :class="experienceExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                            <div x-show="experienceExpanded" class="mt-2">
                                <select x-model="filters.experience" @change="applyFilters()" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white">
                                    <option value="">Any</option>
                                    <option value="0">0 Yrs</option>
                                    <option value="0-1">0-1 Yrs</option>
                                    <option value="1-3">1-3 Yrs</option>
                                    <option value="3-5">3-5 Yrs</option>
                                    <option value="5-10">5-10 Yrs</option>
                                    <option value="10+">10+ Yrs</option>
                                </select>
                            </div>
                        </div>

                        <!-- Location Filter -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2 cursor-pointer" @click="locationExpanded = !locationExpanded">
                                <h4 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Location
                                </h4>
                                <svg class="w-4 h-4 text-gray-500 transition-transform" :class="locationExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                            <div x-show="locationExpanded" class="mt-2">
                                <!-- Search Input -->
                                <input type="text" 
                                       x-model="locationSearch" 
                                       @input="searchLocationsFilter($event.target.value)"
                                       placeholder="Search location..."
                                       class="w-full px-3 py-2 mb-2 border border-gray-300 rounded-lg text-sm focus:border-gray-500 focus:ring-1 focus:ring-gray-500">
                                
                                <!-- Popular Locations (Top 5) -->
                                <div class="space-y-1 max-h-48 overflow-y-auto" x-show="!showAllLocations">
                                    <template x-for="location in popularLocations" :key="location.value">
                                        <label class="flex items-center cursor-pointer hover:text-gray-900">
                                            <input type="checkbox" 
                                                   :value="location.value" 
                                                   x-model="filters.location_filter" 
                                                   @change="applyFilters()" 
                                                   class="mr-2 w-4 h-4 text-gray-800 border-gray-300 rounded focus:ring-gray-500">
                                            <span class="text-sm text-gray-700 flex-1" x-text="location.label"></span>
                                            <span class="text-xs text-gray-500" x-text="'(' + location.count + ')'"></span>
                                </label>
                                    </template>
                                </div>
                                
                                <!-- All Locations (when searching or View More clicked) -->
                                <div class="space-y-1 max-h-60 overflow-y-auto" x-show="showAllLocations || locationSearch.length > 0">
                                    <template x-for="(location, index) in filteredLocations" :key="'filt-' + index">
                                        <label class="flex items-center cursor-pointer hover:text-gray-900">
                                            <input type="checkbox" 
                                                   :value="location.value" 
                                                   x-model="filters.location_filter" 
                                                   @change="applyFilters()" 
                                                   class="mr-2 w-4 h-4 text-gray-800 border-gray-300 rounded focus:ring-gray-500">
                                            <span class="text-sm text-gray-700 flex-1" x-text="location.label"></span>
                                            <span class="text-xs text-gray-500" x-text="'(' + location.count + ')'"></span>
                                </label>
                                    </template>
                                    <div x-show="filteredLocations.length === 0 && locationSearch.length > 0" class="text-center py-3 text-sm text-gray-500">
                                        No locations found
                                    </div>
                                </div>
                                
                                <!-- View More Button -->
                                <button @click="showAllLocations = !showAllLocations; if(showAllLocations && allLocations.length === 0) loadAllLocations()" 
                                        class="w-full mt-2 px-3 py-1.5 text-xs font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded transition">
                                    <span x-text="showAllLocations ? 'Show Less' : 'View More'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Company Filter -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2 cursor-pointer" @click="companyExpanded = !companyExpanded">
                                <h4 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    Company
                                </h4>
                                <svg class="w-4 h-4 text-gray-500 transition-transform" :class="companyExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                            <div x-show="companyExpanded" class="mt-2">
                                <input type="text" 
                                       x-model="companySearch" 
                                       placeholder="Search company..."
                                       class="w-full px-3 py-2 mb-2 border border-gray-300 rounded-lg text-sm focus:border-gray-500 focus:ring-1 focus:ring-gray-500">
                                
                                <div class="space-y-1 max-h-48 overflow-y-auto">
                                    <template x-for="comp in filteredCompanies" :key="comp.id">
                                        <label class="flex items-center cursor-pointer hover:text-gray-900">
                                            <input type="checkbox" 
                                                   :value="comp.company_name" 
                                                   x-model="filters.company_filter" 
                                                   @change="applyFilters()" 
                                                   class="mr-2 w-4 h-4 text-gray-800 border-gray-300 rounded focus:ring-gray-500">
                                            <span class="text-sm text-gray-700 flex-1 ml-2" x-text="comp.company_name"></span>
                                        </label>
                                    </template>
                                    <div x-show="filteredCompanies.length === 0" class="text-center text-sm text-gray-500 py-2">
                                        No companies found
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Industry Filter -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2 cursor-pointer" @click="industryExpanded = !industryExpanded">
                                <h4 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    Industry
                                </h4>
                                <svg class="w-4 h-4 text-gray-500 transition-transform" :class="industryExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                            <div x-show="industryExpanded" class="mt-2 space-y-1">
                                <input type="text" 
                                       x-model="industrySearch" 
                                       @input.debounce.300ms="searchIndustriesFilter()"
                                       placeholder="Search industry..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 mb-2">
                                
                                <!-- Popular Industries (Top 5) - Always visible when not searching -->
                                <div class="space-y-1 max-h-48 overflow-y-auto" x-show="!industrySearch || industrySearch.length === 0">
                                    <template x-for="(ind, index) in popularIndustries" :key="'pop-ind-' + index">
                                        <label class="flex items-center cursor-pointer hover:text-gray-900">
                                            <input type="checkbox" 
                                                   :value="ind.value" 
                                                   x-model="filters.industry_filter" 
                                                   @change="applyFilters()" 
                                                   class="mr-2 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <span class="text-sm text-gray-700 flex-1" x-text="ind.label"></span>
                                            <span class="text-xs text-gray-500" x-text="'(' + ind.count + ')'"></span>
                                </label>
                                    </template>
                                </div>
                                
                                <!-- All Industries (when searching or View More clicked) -->
                                <div class="space-y-1 max-h-60 overflow-y-auto" x-show="showAllIndustries && (!industrySearch || industrySearch.length === 0)">
                                    <template x-for="ind in allIndustries" :key="ind.value">
                                        <label class="flex items-center cursor-pointer hover:text-gray-900">
                                            <input type="checkbox" 
                                                   :value="ind.value" 
                                                   x-model="filters.industry_filter" 
                                                   @change="applyFilters()" 
                                                   class="mr-2 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <span class="text-sm text-gray-700 flex-1" x-text="ind.label"></span>
                                            <span class="text-xs text-gray-500" x-text="'(' + ind.count + ')'"></span>
                                </label>
                                    </template>
                                </div>
                                
                                <!-- Filtered Industries (when searching) -->
                                <div class="space-y-1 max-h-60 overflow-y-auto" x-show="industrySearch && industrySearch.length > 0">
                                    <template x-for="ind in filteredIndustries" :key="ind.value">
                                        <label class="flex items-center cursor-pointer hover:text-gray-900">
                                            <input type="checkbox" 
                                                   :value="ind.value" 
                                                   x-model="filters.industry_filter" 
                                                   @change="applyFilters()" 
                                                   class="mr-2 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <span class="text-sm text-gray-700 flex-1" x-text="ind.label"></span>
                                            <span class="text-xs text-gray-500" x-text="'(' + ind.count + ')'"></span>
                                </label>
                                    </template>
                                    <div x-show="filteredIndustries.length === 0" class="text-center text-sm text-gray-500 py-3">No industries found.</div>
                                </div>
                                
                                <!-- View More Button -->
                                <button x-show="!showAllIndustries && allIndustries.length > 5 && (!industrySearch || industrySearch.length === 0)" 
                                        @click="loadAllIndustries(); showAllIndustries = true" 
                                        class="w-full mt-2 px-3 py-1.5 text-xs font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded transition">
                                    <span>View More</span>
                                </button>
                                
                                <!-- Show Less Button -->
                                <button x-show="showAllIndustries && allIndustries.length > 5 && (!industrySearch || industrySearch.length === 0)" 
                                        @click="showAllIndustries = false; loadPopularIndustries()" 
                                        class="w-full mt-2 px-3 py-1.5 text-xs font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded transition">
                                    <span>Show Less</span>
                                </button>
                            </div>
                        </div>

                        <!-- Salary Filter -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2 cursor-pointer" @click="salaryExpanded = !salaryExpanded">
                                <h4 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Salary
                                </h4>
                                <svg class="w-4 h-4 text-gray-500 transition-transform" :class="salaryExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                            <div x-show="salaryExpanded" class="mt-2 space-y-1.5">
                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="radio" x-model="filters.salary_range" value="0-3" @change="applyFilters()" class="mr-2 w-4 h-4 text-gray-800 border-gray-300 focus:ring-gray-500">
                                    <span class="text-sm text-gray-700">0-3 Lakhs</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="radio" x-model="filters.salary_range" value="3-6" @change="applyFilters()" class="mr-2 w-4 h-4 text-gray-800 border-gray-300 focus:ring-gray-500">
                                    <span class="text-sm text-gray-700">3-6 Lakhs</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="radio" x-model="filters.salary_range" value="6-10" @change="applyFilters()" class="mr-2 w-4 h-4 text-gray-800 border-gray-300 focus:ring-gray-500">
                                    <span class="text-sm text-gray-700">6-10 Lakhs</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="radio" x-model="filters.salary_range" value="10-15" @change="applyFilters()" class="mr-2 w-4 h-4 text-gray-800 border-gray-300 focus:ring-gray-500">
                                    <span class="text-sm text-gray-700">10-15 Lakhs</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="radio" x-model="filters.salary_range" value="15+" @change="applyFilters()" class="mr-2 w-4 h-4 text-gray-800 border-gray-300 focus:ring-gray-500">
                                    <span class="text-sm text-gray-700">15+ Lakhs</span>
                                </label>
                            </div>
                        </div>

                        <!-- Job Type Filter -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2 cursor-pointer" @click="jobTypeExpanded = !jobTypeExpanded">
                                <h4 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    Job Type
                                </h4>
                                <svg class="w-4 h-4 text-gray-500 transition-transform" :class="jobTypeExpanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                            <div x-show="jobTypeExpanded" class="mt-2 space-y-1.5">
                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="checkbox" x-model="filters.job_type" value="full_time" @change="applyFilters()" class="mr-2 w-4 h-4 text-gray-800 border-gray-300 rounded focus:ring-gray-500">
                                    <span class="text-sm text-gray-700">Full-time</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="checkbox" x-model="filters.job_type" value="part_time" @change="applyFilters()" class="mr-2 w-4 h-4 text-gray-800 border-gray-300 rounded focus:ring-gray-500">
                                    <span class="text-sm text-gray-700">Part-time</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="checkbox" x-model="filters.job_type" value="contract" @change="applyFilters()" class="mr-2 w-4 h-4 text-gray-800 border-gray-300 rounded focus:ring-gray-500">
                                    <span class="text-sm text-gray-700">Contract</span>
                                </label>
                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="checkbox" x-model="filters.job_type" value="internship" @change="applyFilters()" class="mr-2 w-4 h-4 text-gray-800 border-gray-300 rounded focus:ring-gray-500">
                                    <span class="text-sm text-gray-700">Internship</span>
                                </label>
                            </div>
                        </div>
                        <!-- Date Posted Filter -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2 cursor-pointer"
                                 @click="datePostedExpanded = !datePostedExpanded">
                                <h4 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            Date Posted
        </h4>

        <svg class="w-4 h-4 text-gray-500 transition-transform"
             :class="datePostedExpanded ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 9l-7 7-7-7"></path>
        </svg>
    </div>

                            <div x-show="datePostedExpanded" class="mt-2 space-y-1.5">
                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="checkbox" :checked="filters.date_posted.includes('all')" @change="toggleDatePosted('all')"
                                           class="mr-2 w-4 h-4 text-gray-800 border-gray-300 rounded focus:ring-gray-500">
                                    <span class="text-sm text-gray-700 flex-1">All</span>
                                    <span class="ml-auto px-2 py-0.5 text-xs font-medium rounded bg-gray-100 text-gray-700" x-text="jobs.length"></span>
                                </label>

                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="checkbox" :checked="filters.date_posted.includes('last_hour')" @change="toggleDatePosted('last_hour')"
                                           class="mr-2 w-4 h-4 text-gray-800 border-gray-300 rounded focus:ring-gray-500">
                                    <span class="text-sm text-gray-700 flex-1">Last Hour</span>
                                    <span class="ml-auto px-2 py-0.5 text-xs font-medium rounded bg-gray-100 text-gray-700" x-text="getDatePostedCount('last_hour')"></span>
                                </label>

                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="checkbox" :checked="filters.date_posted.includes('last_24_hours')" @change="toggleDatePosted('last_24_hours')"
                                           class="mr-2 w-4 h-4 text-gray-800 border-gray-300 rounded focus:ring-gray-500">
                                    <span class="text-sm text-gray-700 flex-1">Last 24 Hours</span>
                                    <span class="ml-auto px-2 py-0.5 text-xs font-medium rounded bg-gray-100 text-gray-700" x-text="getDatePostedCount('last_24_hours')"></span>
                                </label>

                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="checkbox" :checked="filters.date_posted.includes('last_3_days')" @change="toggleDatePosted('last_3_days')"
                                           class="mr-2 w-4 h-4 text-gray-800 border-gray-300 rounded focus:ring-gray-500">
                                    <span class="text-sm text-gray-700 flex-1">Last 3 Days</span>
                                    <span class="ml-auto px-2 py-0.5 text-xs font-medium rounded bg-gray-100 text-gray-700" x-text="getDatePostedCount('last_3_days')"></span>
                                </label>

                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="checkbox" :checked="filters.date_posted.includes('last_7_days')" @change="toggleDatePosted('last_7_days')"
                                           class="mr-2 w-4 h-4 text-gray-800 border-gray-300 rounded focus:ring-gray-500">
                                    <span class="text-sm text-gray-700 flex-1">Last 7 Days</span>
                                    <span class="ml-auto px-2 py-0.5 text-xs font-medium rounded bg-gray-100 text-gray-700" x-text="getDatePostedCount('last_7_days')"></span>
                                </label>

                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="checkbox" :checked="filters.date_posted.includes('last_30_days')" @change="toggleDatePosted('last_30_days')"
                                           class="mr-2 w-4 h-4 text-gray-800 border-gray-300 rounded focus:ring-gray-500">
                                    <span class="text-sm text-gray-700 flex-1">Last 30 Days</span>
                                    <span class="ml-auto px-2 py-0.5 text-xs font-medium rounded bg-gray-100 text-gray-700" x-text="getDatePostedCount('last_30_days')"></span>
                                </label>

                                <label class="flex items-center cursor-pointer hover:text-gray-900">
                                    <input type="checkbox" :checked="filters.date_posted.includes('last_3_months')" @change="toggleDatePosted('last_3_months')"
                                           class="mr-2 w-4 h-4 text-gray-800 border-gray-300 rounded focus:ring-gray-500">
                                    <span class="text-sm text-gray-700 flex-1">Last 3 Months</span>
                                    <span class="ml-auto px-2 py-0.5 text-xs font-medium rounded bg-gray-100 text-gray-700" x-text="getDatePostedCount('last_3_months')"></span>
                                </label>
                            </div>
                        </div>
                        <!-- Tags Section -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h6"></path>
                                    </svg>
                                    Tags
                                </h4>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="tag in popularIndustries" :key="tag.value">
                                    <button type="button"
                                            class="px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded cursor-pointer hover:bg-gray-200 transition"
                                            @click="toggleIndustryTag(tag.value)">
                                        <span x-text="tag.label"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        <!-- Hiring Banner -->
                        <div class="mb-4 bg-blue-600 rounded-lg p-4 text-white text-center">
                            <h3 class="text-lg font-semibold mb-1">WE ARE HIRING</h3>
                            <p class="text-sm mb-3 text-blue-100">Apply Today!</p>
                            <a href="/candidate/jobs"
                               class="inline-block px-4 py-2 bg-white text-blue-600 font-medium rounded-lg border border-white hover:bg-blue-50 transition text-sm">
                                View Open Positions
                            </a>
                        </div>

                        <button @click="clearFilters()" class="w-full py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Clear All Filters
                        </button>
                    </div>
                </div>

                <!-- Center: Job Listings -->
                <div class="flex-1 min-w-0">
                    <!-- Results Header -->
                    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white rounded-lg p-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-gray-800 text-white px-3 py-1.5 rounded font-medium">
                                <span x-text="pagination.total || jobs.length || 0"></span>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">
                                Jobs Found
                            </h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-700 whitespace-nowrap flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
                                </svg>
                                Sort by:
                            </span>
                                <select @change="sortJobs($event.target.value)" 
                                    class="border border-gray-300 bg-white text-gray-700 font-medium cursor-pointer focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-500 rounded px-2 py-1 text-sm">
                                    <option value="relevance">Relevance</option>
                                    <option value="date">Date</option>
                                    <option value="salary_high">Salary: High to Low</option>
                                    <option value="salary_low">Salary: Low to High</option>
                                </select>
                        </div>
                    </div>

                    <!-- Job Listings -->
                    <div class="space-y-3 mb-6">
                        <!-- No Jobs Message -->
                        <div x-show="jobs.length === 0" class="bg-white rounded-lg p-8 text-center">
                            <div class="mb-4">
                                <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-800 mb-2">No jobs found</h3>
                            <p class="text-gray-600 mb-6">Try adjusting your search filters or check back later for new opportunities.</p>
                            <a href="/candidate/jobs" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-lg border border-blue-600 hover:bg-blue-700 hover:border-blue-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                View All Jobs
                            </a>
                        </div>

                        <!-- Job Cards -->
                        <template x-for="job in jobs" :key="job.id || Math.random()">
                            <a :href="'/job/' + (job.slug || job.id || '')" class="block">
                                <div class="bg-white rounded-lg border border-gray-200 hover:border-gray-300 transition job-card">
                                    <div class="p-4">
                                        <!-- Job Title and Company -->
                                        <div class="flex items-start justify-between mb-3 gap-3">
                                            <div class="flex-1 min-w-0">
                                                <h3 class="text-lg font-semibold text-gray-900 mb-2 hover:text-gray-700 transition break-words">
                                                    <span x-text="job.title || 'Job Title'"></span>
                                                </h3>
                                                <div class="flex items-center gap-2 mb-3">
                                                    <!-- Company Logo with Fallback -->
                                                    <div class="w-10 h-10 rounded overflow-hidden flex-shrink-0 bg-gray-100 flex items-center justify-center">
                                                        <img x-show="job.company_logo" 
                                                             :src="job.company_logo" 
                                                             :alt="job.company_name || 'Company'"
                                                             class="w-full h-full object-cover"
                                                             @error="$el.style.display='none'; $el.nextElementSibling.style.display='flex';">
                                                        <div x-show="!job.company_logo" class="w-full h-full flex items-center justify-center text-gray-600 font-semibold text-base">
                                                            <span x-text="(job.company_name || 'C')[0].toUpperCase()"></span>
                                                        </div>
                                                    </div>
                                                    <p class="text-sm font-medium text-gray-700 break-words" x-text="job.company_name || 'Company Name Not Available'"></p>
                                                </div>
                                            </div>
                                            <button @click.stop="bookmarkJob(job.slug || job.id || 0)" 
                                                    x-show="isLoggedIn"
                                                    class="p-2 hover:bg-gray-100 rounded transition">
                                                <svg x-show="!job.is_bookmarked" class="w-5 h-5 text-gray-400 hover:text-gray-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                                </svg>
                                                <svg x-show="job.is_bookmarked" class="w-5 h-5 text-gray-800 fill-current" viewBox="0 0 24 24">
                                                    <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        
                                        <!-- Job Details: Industry, Employment Type, Salary, Location -->
                                        <div class="flex flex-wrap items-center gap-3 mb-3 text-xs text-gray-600">
                                            <!-- Industry/Category Icon -->
                                            <span class="flex items-center flex-shrink-0 gap-1.5">
                                                <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                                <span class="text-gray-700" x-text="job.category || job.industry || 'Industry'"></span>
                                            </span>

                                            <!-- Experience Icon -->
                                            <span class="flex items-center flex-shrink-0 gap-1.5" x-show="(job.min_experience != null && job.min_experience >= 0) || (job.max_experience != null && job.max_experience > 0)">
                                                <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                </svg>
                                                <span class="text-gray-700 whitespace-nowrap">
                                                    <template x-if="(job.min_experience != null && job.min_experience >= 0) && (job.max_experience != null && job.max_experience > 0)">
                                                        <span><span x-text="job.min_experience"></span> - <span x-text="job.max_experience"></span> Yrs</span>
                                                    </template>
                                                    <template x-if="(job.min_experience != null && job.min_experience >= 0) && (job.max_experience == null || job.max_experience == 0)">
                                                        <span><span x-text="job.min_experience"></span>+ Yrs</span>
                                                    </template>
                                                    <template x-if="(job.min_experience == null) && (job.max_experience != null && job.max_experience > 0)">
                                                        <span>Upto <span x-text="job.max_experience"></span> Yrs</span>
                                                    </template>
                                                </span>
                                            </span>
                                            
                                            <!-- Employment Type Icon -->
                                            <span class="flex items-center flex-shrink-0 gap-1.5">
                                                <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                <span class="text-gray-700" x-text="(job.employment_type_display || job.employment_type || 'Full-time').replace('_', ' ')"></span>
                                            </span>
                                            
                                            <!-- Salary Icon -->
                                            <span class="flex items-center flex-shrink-0 gap-1.5" x-show="(job.salary_min != null && job.salary_min > 0) || (job.salary_max != null && job.salary_max > 0)">
                                                <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span class="text-gray-700 whitespace-nowrap">
                                                    <template x-if="(job.salary_min != null && job.salary_min > 0) && (job.salary_max != null && job.salary_max > 0)">
                                                        <span><span x-text="currencySymbol(job.currency)"></span><span x-text="Number(job.salary_min).toLocaleString('en-IN')"></span> - <span x-text="currencySymbol(job.currency)"></span><span x-text="Number(job.salary_max).toLocaleString('en-IN')"></span></span>
                                                    </template>
                                                    <template x-if="(job.salary_min != null && job.salary_min > 0) && (job.salary_max == null || job.salary_max == 0)">
                                                        <span><span x-text="currencySymbol(job.currency)"></span><span x-text="Number(job.salary_min).toLocaleString('en-IN')"></span>+</span>
                                                    </template>
                                                    <template x-if="(job.salary_min == null || job.salary_min == 0) && (job.salary_max != null && job.salary_max > 0)">
                                                        <span>Upto <span x-text="currencySymbol(job.currency)"></span><span x-text="Number(job.salary_max).toLocaleString('en-IN')"></span></span>
                                                    </template>
                                                </span>
                                            </span>
                                            
                                            <!-- Location Icon -->
                                            <span class="flex items-center flex-shrink-0 gap-1.5">
                                                <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                <span class="text-gray-700 truncate max-w-[150px] sm:max-w-none" x-text="(job.is_remote == 1 || job.is_remote === '1') ? 'Remote' : (job.location_display || 'Location not specified')"></span>
                                            </span>
                                        </div>

                                        <!-- Job Type Tags -->
                                        <div class="flex flex-wrap gap-2 mb-3">
                                            <span class="px-2.5 py-1 bg-gray-50 text-gray-700 text-xs font-medium rounded whitespace-nowrap">
                                                <span x-text="(job.employment_type_display || job.employment_type || 'Full-time').replace('_', ' ')"></span>
                                            </span>
                                            <span x-show="job.is_remote == 1 || job.is_remote === '1'" 
                                                  class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded whitespace-nowrap">
                                                Remote
                                            </span>
                                        </div>

                                        <!-- Posted Date and Action Button -->
                                        <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                                            <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span x-text="getTimeAgo(job.created_at)"></span>
                                            </span>
                                            <button class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg border border-blue-600 hover:bg-blue-700 hover:border-blue-700 transition">
                                                Job Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </template>
                    </div>

                    <!-- Pagination -->
                    <div x-show="pagination.total_pages > 1" class="flex justify-center items-center gap-3 mt-6">
                        <button @click="changePage(pagination.page - 1)" 
                                :disabled="pagination.page === 1"
                                class="px-4 py-2 border border-gray-300 rounded-lg font-medium hover:border-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Previous
                        </button>
                        <div class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium">
                            Page <span x-text="pagination.page"></span> of <span x-text="pagination.total_pages"></span>
                        </div>
                        <button @click="changePage(pagination.page + 1)" 
                                :disabled="pagination.page >= pagination.total_pages"
                                class="px-4 py-2 border border-gray-300 rounded-lg font-medium hover:border-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-2">
                            Next
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Right Sidebar: Featured Companies -->
                <div class="hidden lg:block w-64 flex-shrink-0">
                    <div class="bg-white rounded-lg p-4 sticky top-20">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Featured Companies
                        </h3>
                        <div>
                            <a href="/company/featured" class="block px-4 py-2.5 bg-gray-800 text-white font-semibold rounded-lg border border-gray-800 hover:bg-gray-900 hover:border-gray-900 transition text-center flex items-center justify-center gap-2">
                                See all featured companies
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                            <?php if (!empty($featuredCompanies) && is_array($featuredCompanies)): ?>
                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <?php foreach ($featuredCompanies as $fc): ?>
                                <a href="<?= !empty($fc['slug']) ? '/company/' . htmlspecialchars($fc['slug']) : '/candidate/jobs?company=' . urlencode($fc['company_name'] ?? '') ?>" 
                                   class="group flex items-center gap-2 p-2 rounded hover:bg-gray-50 border border-gray-100">
                                    <div class="w-8 h-8 rounded bg-gray-100 overflow-hidden flex items-center justify-center">
                                        <?php if (!empty($fc['company_logo'])): ?>
                                            <img src="<?= htmlspecialchars($fc['company_logo']) ?>" alt="<?= htmlspecialchars($fc['company_name'] ?? 'Company') ?>" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="hidden w-full h-full items-center justify-center text-gray-600 text-xs font-semibold">
                                                <?= strtoupper(substr($fc['company_name'] ?? 'C', 0, 1)) ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center text-gray-600 text-xs font-semibold">
                                                <?= strtoupper(substr($fc['company_name'] ?? 'C', 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-sm text-gray-800 font-medium truncate group-hover:text-gray-900">
                                        <?= htmlspecialchars($fc['company_name'] ?? 'Company') ?>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                                <div class="mt-4 text-sm text-gray-500">No featured companies yet</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

           
        </div>
         <!-- Top Company Section -->
            <?php if (!empty($topCompanies) && is_array($topCompanies)): ?>
            <section id="top-companies" class="mt-12 py-8 bg-gray-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl md:text-3xl font-semibold text-gray-900 mb-2">Top Companies</h2>
                        <p class="text-gray-600 text-sm">Connect with leading companies offering exciting career opportunities.</p>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        <?php foreach (array_slice($topCompanies, 0, 8) as $company): ?>
                        <a href="/candidate/jobs?company=<?= urlencode($company['company_name'] ?? '') ?>" 
                           class="group bg-white rounded-lg p-4 border border-gray-200 hover:border-gray-300 transition">
                            <div class="flex flex-col items-center text-center">
                                <!-- Company Logo -->
                                <div class="w-16 h-16 mb-3 rounded overflow-hidden bg-gray-100 flex items-center justify-center">
                                    <?php if (!empty($company['company_logo'])): ?>
                                        <img src="<?= htmlspecialchars($company['company_logo']) ?>" 
                                             alt="<?= htmlspecialchars($company['company_name'] ?? 'Company') ?>"
                                             class="w-full h-full object-cover"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="w-full h-full hidden items-center justify-center text-gray-600 font-semibold text-lg">
                                            <?= strtoupper(substr($company['company_name'] ?? 'C', 0, 1)) ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-gray-600 font-semibold text-lg">
                                            <?= strtoupper(substr($company['company_name'] ?? 'C', 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <!-- Company Name -->
                                <h3 class="text-sm font-semibold text-gray-900 mb-1 group-hover:text-gray-700 transition">
                                    <?= htmlspecialchars($company['company_name'] ?? 'Company') ?>
                                </h3>
                                <!-- Job Count -->
                                <p class="text-xs text-gray-600 font-medium">
                                    <?= (int)($company['job_count'] ?? 0) ?> open jobs
                                </p>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>
    </div>

    <script>
        // Job Title Autocomplete Component
        function jobTitleAutocomplete() {
            return {
                showSuggestions: false,
                suggestions: [],
                selectedIndex: -1,
                searchTimeout: null,
                async searchJobTitles(query) {
                    if (!query || query.length < 2) {
                        this.suggestions = [];
                        this.showSuggestions = false;
                        return;
                    }
                    
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(async () => {
                        try {
                            const url = `/api/job-titles/search?q=${encodeURIComponent(query)}&limit=10`;
                            const response = await fetch(url);
                            if (!response.ok) {
                                this.suggestions = [];
                                this.showSuggestions = false;
                                return;
                            }
                            const data = await response.json();
                            this.suggestions = data.suggestions || [];
                            this.showSuggestions = this.suggestions.length > 0;
                            this.selectedIndex = -1;
                        } catch (error) {
                            console.error('Error fetching job titles:', error);
                            this.suggestions = [];
                            this.showSuggestions = false;
                        }
                    }, 300);
                },
                selectJobTitle(suggestion, event) {
                    // Update filters.keyword in parent component
                    // Find the parent jobSearch component
                    const jobSearchEl = document.querySelector('[x-data*="jobSearch"]');
                    if (jobSearchEl && jobSearchEl._x_dataStack) {
                        const jobSearchData = jobSearchEl._x_dataStack[0];
                        if (jobSearchData && jobSearchData.filters) {
                            jobSearchData.filters.keyword = suggestion.title;
                        }
                    }
                    this.showSuggestions = false;
                    this.suggestions = [];
                },
                handleJobTitleKeyDown(event) {
                    if (!this.showSuggestions || this.suggestions.length === 0) return;
                    
                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        this.selectedIndex = Math.min(this.selectedIndex + 1, this.suggestions.length - 1);
                    } else if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
                    } else if (event.key === 'Enter' && this.selectedIndex >= 0) {
                        event.preventDefault();
                        this.selectJobTitle(this.suggestions[this.selectedIndex], event);
                    } else if (event.key === 'Escape') {
                        this.showSuggestions = false;
                    }
                }
            }
        }

        // Location Autocomplete Component
        function locationAutocomplete() {
            return {
                showSuggestions: false,
                suggestions: [],
                selectedIndex: -1,
                searchTimeout: null,
                async searchLocations(query) {
                    if (!query || query.length < 2) {
                        this.suggestions = [];
                        this.showSuggestions = false;
                        return;
                    }
                    
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(async () => {
                        try {
                            const url = `/api/locations/search?q=${encodeURIComponent(query)}&limit=10`;
                            const response = await fetch(url);
                            if (!response.ok) {
                                this.suggestions = [];
                                this.showSuggestions = false;
                                return;
                            }
                            const data = await response.json();
                            this.suggestions = data.suggestions || [];
                            this.showSuggestions = this.suggestions.length > 0;
                            this.selectedIndex = -1;
                        } catch (error) {
                            console.error('Error fetching locations:', error);
                            this.suggestions = [];
                            this.showSuggestions = false;
                        }
                    }, 300);
                },
                selectLocation(suggestion, event) {
                    // Update filters.location in parent component
                    // Find the parent jobSearch component
                    const jobSearchEl = document.querySelector('[x-data*="jobSearch"]');
                    if (jobSearchEl && jobSearchEl._x_dataStack) {
                        const jobSearchData = jobSearchEl._x_dataStack[0];
                        if (jobSearchData && jobSearchData.filters) {
                            jobSearchData.filters.location = suggestion.display;
                        }
                    }
                    this.showSuggestions = false;
                    this.suggestions = [];
                },
                handleLocationKeyDown(event) {
                    if (!this.showSuggestions || this.suggestions.length === 0) return;
                    
                    if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        this.selectedIndex = Math.min(this.selectedIndex + 1, this.suggestions.length - 1);
                    } else if (event.key === 'ArrowUp') {
                        event.preventDefault();
                        this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
                    } else if (event.key === 'Enter' && this.selectedIndex >= 0) {
                        event.preventDefault();
                        this.selectLocation(this.suggestions[this.selectedIndex], event);
                    } else if (event.key === 'Escape') {
                        this.showSuggestions = false;
                    }
                }
            }
        }

        function jobSearch() {
            const jobsData = <?= json_encode($jobs ?? []) ?>;
            const filtersData = <?= json_encode($filters ?? []) ?>;
            const paginationData = <?= json_encode($pagination ?? []) ?>;
            const isLoggedIn = <?= isset($isLoggedIn) && $isLoggedIn ? 'true' : 'false' ?>;
            const filterCompanies = <?= json_encode($filterCompanies ?? []) ?>;
            
            // Ensure arrays are arrays
            const workMode = Array.isArray(filtersData?.work_mode) ? filtersData.work_mode : (filtersData?.work_mode ? [filtersData.work_mode] : []);
            const locationFilter = Array.isArray(filtersData?.location_filter) ? filtersData.location_filter : (filtersData?.location_filter ? [filtersData.location_filter] : []);
            const jobType = Array.isArray(filtersData?.job_type) ? filtersData.job_type : (filtersData?.job_type ? [filtersData.job_type] : []);
            const companyFilter = Array.isArray(filtersData?.company_filter) ? filtersData.company_filter : (filtersData?.company_filter ? [filtersData.company_filter] : []);
            
            return {
                isLoggedIn: isLoggedIn,
                jobs: jobsData || [],
                filters: {
                    keyword: filtersData?.keyword || '',
                    location: filtersData?.location || '',
                    salary_min: filtersData?.salary_min || '',
                    salary_max: filtersData?.salary_max || '',
                    experience: filtersData?.experience || '',
                    job_type: jobType,
                    is_remote: filtersData?.is_remote || '',
                    work_mode: workMode,
                    location_filter: locationFilter,
                    industry_filter: Array.isArray(filtersData?.industry_filter) ? filtersData.industry_filter : (filtersData?.industry_filter ? [filtersData.industry_filter] : []),
                    company_filter: companyFilter,
                    salary_range: filtersData?.salary_range || '',
                    date_posted: Array.isArray(filtersData?.date_posted) ? filtersData.date_posted : (filtersData?.date_posted ? [filtersData.date_posted] : [])
                },
                pagination: paginationData || {
                    page: 1,
                    per_page: 20,
                    total: 0,
                    total_pages: 1
                },
                workModeExpanded: true,
                experienceExpanded: true,
                locationExpanded: true,
                salaryExpanded: true,
                jobTypeExpanded: true,
                datePostedExpanded: true,
                industryExpanded: true,
                companyExpanded: true,
                showFilters: false,
                locationSearch: '',
                popularLocations: [],
                allLocations: [],
                showAllLocations: false,
                industrySearch: '',
                popularIndustries: [],
                allIndustries: [],
                showAllIndustries: false,
                companySearch: '',
                filterCompanies: filterCompanies || [],
                
                get filteredCompanies() {
                     if (!this.companySearch) return this.filterCompanies;
                     const query = this.companySearch.toLowerCase();
                     return this.filterCompanies.filter(c => c.company_name.toLowerCase().includes(query));
                },

                async init() {
                    // Load popular locations (top 5)
                    await this.loadPopularLocations();
                    // Load popular industries (top 5)
                    await this.loadPopularIndustries();
                },
                async loadPopularLocations() {
                    try {
                        const response = await fetch('/api/locations/all');
                        const data = await response.json();
                        if (data.locations && data.locations.length > 0) {
                            this.allLocations = data.locations;
                            this.popularLocations = data.locations.slice(0, 5);
                        }
                    } catch (error) {
                        console.error('Error loading locations:', error);
                    }
                },
                async loadAllLocations() {
                    if (this.allLocations.length === 0) {
                        await this.loadPopularLocations();
                    }
                },
                get filteredLocations() {
                    if (!this.locationSearch) {
                        return this.allLocations;
                    }
                    const search = this.locationSearch.toLowerCase();
                    return this.allLocations.filter(loc => 
                        loc.label.toLowerCase().includes(search) || 
                        loc.value.toLowerCase().includes(search) ||
                        (loc.state && loc.state.toLowerCase().includes(search))
                    );
                },
                searchLocationsFilter(query) {
                    this.locationSearch = query;
                    if (query.length > 0) {
                        this.showAllLocations = true;
                    }
                },
                async loadPopularIndustries() {
                    try {
                        // Load top 5 popular industries (sorted by job count - most popular first)
                        const response = await fetch('/api/industries/all?limit=5');
                        const data = await response.json();
                        if (data.industries && data.industries.length > 0) {
                            this.popularIndustries = data.industries; // Top 5 already sorted by popularity
                            // If allIndustries is empty, initialize it with popular ones
                            if (this.allIndustries.length === 0) {
                                this.allIndustries = data.industries;
                            }
                        }
                    } catch (error) {
                        console.error('Error loading popular industries:', error);
                    }
                },
                async loadAllIndustries() {
                    try {
                        // Load all industries (sorted by job count DESC - most popular first)
                        const response = await fetch('/api/industries/all');
                        const data = await response.json();
                        if (data.industries && data.industries.length > 0) {
                            this.allIndustries = data.industries; // All industries sorted by popularity
                        }
                    } catch (error) {
                        console.error('Error loading all industries:', error);
                    }
                },
                searchIndustriesFilter() {
                    // When searching, automatically show all industries for filtering
                    if (this.industrySearch && this.industrySearch.length > 0) {
                        if (this.allIndustries.length === 0) {
                            this.loadAllIndustries();
                        }
                    }
                },
                get filteredIndustries() {
                    // If searching, filter from all industries
                    if (this.industrySearch && this.industrySearch.length > 0) {
                        const query = this.industrySearch.toLowerCase();
                        return this.allIndustries.filter(ind => 
                            ind.label.toLowerCase().includes(query) || 
                            ind.value.toLowerCase().includes(query)
                        );
                    }
                    // If not searching and showing all, return all industries
                    if (this.showAllIndustries) {
                        return this.allIndustries;
                    }
                    // Otherwise return popular industries
                    return this.popularIndustries;
                },
                get appliedFiltersCount() {
                    let count = 0;
                    if (this.filters.keyword) count++;
                    if (this.filters.location) count++;
                    if (this.filters.salary_min || this.filters.salary_max) count++;
                    if (this.filters.experience) count++;
                    if (this.filters.job_type) count++;
                    if (this.filters.is_remote) count++;
                    if (this.filters.work_mode && this.filters.work_mode.length > 0) count++;
                    if (this.filters.location_filter && this.filters.location_filter.length > 0) count++;
                    if (this.filters.industry_filter && this.filters.industry_filter.length > 0) count++;
                    if (this.filters.company_filter && this.filters.company_filter.length > 0) count++;
                    if (this.filters.salary_range) count++;
                    return count;
                },
                applyFilters() {
                    // Auto-apply filters when changed
                    const params = new URLSearchParams();
                    
                    // Handle keyword and location from search bar
                    if (this.filters.keyword) {
                        params.append('keyword', this.filters.keyword);
                    }
                    if (this.filters.location) {
                        params.append('location', this.filters.location);
                    }
                    
                    // Handle work_mode array
                    if (this.filters.work_mode && this.filters.work_mode.length > 0) {
                        this.filters.work_mode.forEach(mode => {
                            params.append('work_mode[]', mode);
                        });
                    }
                    
                    // Handle location_filter array
                    if (this.filters.location_filter && this.filters.location_filter.length > 0) {
                        this.filters.location_filter.forEach(loc => {
                            params.append('location_filter[]', loc);
                        });
                    }
                    
                    // Handle industry_filter array
                    if (this.filters.industry_filter && this.filters.industry_filter.length > 0) {
                        this.filters.industry_filter.forEach(ind => {
                            params.append('industry_filter[]', ind);
                        });
                    }

                    // Handle company_filter array
                    if (this.filters.company_filter && this.filters.company_filter.length > 0) {
                        this.filters.company_filter.forEach(comp => {
                            params.append('company_filter[]', comp);
                        });
                    }
                    
                    // Handle salary_range
                    if (this.filters.salary_range) {
                        const [min, max] = this.filters.salary_range.split('-');
                        if (max) {
                            params.append('salary_min', (parseInt(min) * 100000).toString());
                            params.append('salary_max', (parseInt(max) * 100000).toString());
                        } else if (min === '15+') {
                            params.append('salary_min', '1500000');
                        }
                    }
                    
                    // Handle experience
                    if (this.filters.experience) {
                        params.append('experience', this.filters.experience);
                    }
                    
                    // Handle job_type array
                    if (this.filters.job_type && this.filters.job_type.length > 0) {
                        this.filters.job_type.forEach(type => {
                            params.append('job_type[]', type);
                        });
                    }
                    
                    // Handle is_remote
                    if (this.filters.is_remote) {
                        params.append('is_remote', this.filters.is_remote);
                    }

                    // Handle date_posted array
                    if (this.filters.date_posted && this.filters.date_posted.length > 0) {
                        this.filters.date_posted.forEach(dp => {
                            params.append('date_posted[]', dp);
                        });
                    }
                    
                    // Reset to page 1 when filters change
                    params.set('page', '1');
                    
                    window.location.href = '/candidate/jobs?' + params.toString();
                },
                searchJobs() {
                    this.applyFilters();
                },
                clearFilters() {
                    this.filters = {
                        keyword: '',
                        location: '',
                        salary_min: '',
                        salary_max: '',
                        experience: '',
                        job_type: '',
                        is_remote: '',
                        work_mode: [],
                        location_filter: [],
                        salary_range: '',
                        date_posted: []
                    };
                    window.location.href = '/candidate/jobs';
                },
                changePage(page) {
                    const params = new URLSearchParams(window.location.search);
                    params.set('page', page);
                    window.location.href = '/candidate/jobs?' + params.toString();
                },
                sortJobs(sortBy) {
                    const params = new URLSearchParams(window.location.search);
                    params.set('sort', sortBy);
                    window.location.href = '/candidate/jobs?' + params.toString();
                },
                async bookmarkJob(jobSlugOrId) {
                    if (!jobSlugOrId || jobSlugOrId === 0) {
                        console.error('Invalid job slug/ID:', jobSlugOrId);
                        return;
                    }
                    if (!this.isLoggedIn) {
                        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                        return;
                    }
                    try {
                        const response = await fetch(`/candidate/jobs/${jobSlugOrId}/bookmark`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-Token': this.getCsrfToken()
                            }
                        });
                        if (response.status === 401 || response.status === 403) {
                            window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
                            return;
                        }
                        const data = await response.json();
                        if (data.success) {
                            const job = this.jobs.find(j => (j.slug === jobSlugOrId) || (j.id === jobSlugOrId));
                            if (job) { job.is_bookmarked = data.bookmarked; }
                        }
                    } catch (error) {
                        console.error('Bookmark error:', error);
                    }
                },
                getCsrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.content || '';
                },
                toggleDatePosted(value) {
                    const idx = this.filters.date_posted.indexOf(value);
                    if (value === 'all') {
                        // Selecting 'all' clears other selections
                        this.filters.date_posted = ['all'];
                    } else {
                        // Remove 'all' if any specific range is selected
                        this.filters.date_posted = this.filters.date_posted.filter(v => v !== 'all');
                        if (idx >= 0) {
                            this.filters.date_posted.splice(idx, 1);
                        } else {
                            this.filters.date_posted.push(value);
                        }
                    }
                    this.applyFilters();
                },
                getDatePostedCount(range) {
                    const now = new Date();
                    let threshold = null;
                    switch (range) {
                        case 'last_hour': threshold = new Date(now.getTime() - 60 * 60 * 1000); break;
                        case 'last_24_hours': threshold = new Date(now.getTime() - 24 * 60 * 60 * 1000); break;
                        case 'last_3_days': threshold = new Date(now.getTime() - 3 * 24 * 60 * 60 * 1000); break;
                        case 'last_7_days': threshold = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000); break;
                        case 'last_30_days': threshold = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000); break;
                        case 'last_3_months': threshold = new Date(now.getTime() - 90 * 24 * 60 * 60 * 1000); break;
                        default: return this.jobs.length || 0;
                    }
                    let count = 0;
                    for (const job of this.jobs) {
                        const created = job.created_at ? new Date(job.created_at) : null;
                        if (created && created >= threshold) count++;
                    }
                    return count;
                },
                toggleIndustryTag(value) {
                    const idx = this.filters.industry_filter.indexOf(value);
                    if (idx >= 0) {
                        this.filters.industry_filter.splice(idx, 1);
                    } else {
                        this.filters.industry_filter.push(value);
                    }
                    this.applyFilters();
                },
                currencySymbol(currency) {
                    const symbols = {
                        'INR': '₹',
                        'USD': '$',
                        'EUR': '€',
                        'GBP': '£'
                    };
                    return symbols[currency] || '₹';
                },
                getTimeAgo(dateString) {
                    if (!dateString) return 'Recently';
                    const now = new Date();
                    const date = new Date(dateString);
                    const diffMs = now - date;
                    const diffMins = Math.floor(diffMs / 60000);
                    const diffHours = Math.floor(diffMs / 3600000);
                    const diffDays = Math.floor(diffMs / 86400000);
                    
                    if (diffMins < 1) return 'Just now';
                    if (diffMins < 60) return diffMins + ' min ago';
                    if (diffHours < 24) return diffHours + ' hour' + (diffHours > 1 ? 's' : '') + ' ago';
                    if (diffDays < 7) return diffDays + ' day' + (diffDays > 1 ? 's' : '') + ' ago';
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }
            }
        }
    </script>
    <?php
require __DIR__ . '/../../include/footer.php';
?>
</body>
</html>
