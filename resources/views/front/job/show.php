<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title><?= htmlspecialchars($job['title'] ?? 'Job') ?> - <?= htmlspecialchars($company['name'] ?? 'Company') ?> - Mindware Infotech</title>
    <meta name="description" content="<?= htmlspecialchars($job['description'] ?? 'Job Description') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($job['keywords'] ?? 'Job Keywords') ?>">
    <meta name="author" content="<?= htmlspecialchars($company['name'] ?? 'Company') ?>">
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    

    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .line-clamp-6 {
            display: -webkit-box;
            -webkit-line-clamp: 6;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php $base = $base ?? '/';
     require __DIR__ . '/../../include/header.php';
      ?>
    
    <!-- Breadcrumbs -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
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
                    <?php if (!empty($locationRows)): ?>
                        <?php 
                        $loc = $locationRows[0]; 
                        // Country
                        if (!empty($loc['country']) && !empty($loc['country_slug'])): 
                        ?>
                        <li>
                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </li>
                        <li>
                            <a href="/jobs-in-<?= htmlspecialchars($loc['country_slug']) ?>" class="text-gray-500 hover:text-gray-700 text-sm"><?= htmlspecialchars($loc['country']) ?></a>
                        </li>
                        <?php endif; ?>

                        <?php 
                        // State
                        if (!empty($loc['state']) && !empty($loc['state_slug'])): 
                        ?>
                        <li>
                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </li>
                        <li>
                            <a href="/jobs-in-<?= htmlspecialchars($loc['state_slug']) ?>" class="text-gray-500 hover:text-gray-700 text-sm"><?= htmlspecialchars($loc['state']) ?></a>
                        </li>
                        <?php endif; ?>

                        <?php 
                        // City
                        if (!empty($loc['city']) && !empty($loc['city_slug'])): 
                        ?>
                        <li>
                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </li>
                        <li>
                            <a href="/jobs-in-<?= htmlspecialchars($loc['city_slug']) ?>" class="text-gray-500 hover:text-gray-700 text-sm"><?= htmlspecialchars($loc['city']) ?></a>
                        </li>
                        <?php endif; ?>

                    <?php elseif (!empty($job['location'])): ?>
                    <li>
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </li>
                    <li>
                        <a href="/jobs-in-<?= strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $job['location']))) ?>" class="text-gray-500 hover:text-gray-700 text-sm"><?= htmlspecialchars($job['location']) ?></a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </li>
                    <li>
                        <span class="text-gray-900 font-medium text-sm line-clamp-1 max-w-[200px]"><?= htmlspecialchars($job['title']) ?></span>
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- Company Banner Section -->
    <?php if (!empty($company['banner_url'])): ?>
    <div class="w-full h-64 md:h-80 lg:h-96 overflow-hidden bg-gray-200 relative">
        <img src="<?= htmlspecialchars($company['banner_url']) ?>" 
             alt="<?= htmlspecialchars($company['name'] ?? 'Company') ?> Banner"
             class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/60"></div>
    </div>
    <?php endif; ?>

    <div x-data="publicJobDetail()" x-cloak>
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Company Info Card (if banner exists, show below banner, else at top) -->
            <?php if (!empty($company['name'])): ?>
            <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6 mt-0 !mt-0 relative z-10 shadow-lg">
                <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
                    <?php if (!empty($company['logo_url'])): ?>
                    <img src="<?= htmlspecialchars($company['logo_url']) ?>" 
                         alt="<?= htmlspecialchars($company['name']) ?>"
                         class="w-20 h-20 md:w-24 md:h-24 rounded-lg object-cover border-2 border-white shadow-md">
                    <?php else: ?>
                    <div class="w-20 h-20 md:w-24 md:h-24 rounded-lg bg-gray-200 flex items-center justify-center text-gray-600 font-semibold text-2xl border-2 border-white shadow-md">
                        <?= strtoupper(substr($company['name'], 0, 1)) ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($company['name']) ?></h2>
                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                            <?php if (!empty($companyStats['rating'])): ?>
                            <span class="flex items-center gap-1">
                                <span class="text-green-600 font-semibold"><?= number_format($companyStats['rating'], 1) ?></span>
                                <span>rating</span>
                            </span>
                            <?php endif; ?>
                            <?php if (!empty($companyStats['reviews_count'])): ?>
                            <span><?= $companyStats['reviews_count'] ?> reviews</span>
                            <?php endif; ?>
                            <?php if (!empty($company['headquarters'])): ?>
                            <span><?= htmlspecialchars($company['headquarters']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($company['founded_year'])): ?>
                            <span>Founded <?= htmlspecialchars($company['founded_year']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="flex gap-3">
                        <?php if ($isLoggedIn): ?>
                        <button @click="toggleFollow()" 
                                class="px-4 py-2 border rounded-md font-medium transition"
                                :class="isFollowing ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'">
                            <span x-text="isFollowing ? '✓ Following' : '+ Follow'"></span>
                        </button>
                        <?php else: ?>
                        <a href="/login?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
                           class="px-4 py-2 bg-white border border-gray-300 rounded-md font-medium hover:bg-gray-50 transition">
                            + Follow
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($company['slug'])): ?>
                        <a href="/company/<?= htmlspecialchars($company['slug']) ?>" 
                           class="px-4 py-2 bg-blue-600 text-white rounded-md font-medium hover:bg-blue-700 transition">
                            View Company Profile
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Job Header Section -->
            <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
                <div class="mb-4">
                    <span class="text-sm text-gray-500">
                        <?php 
                        $createdAt = $job['created_at'] ?? date('Y-m-d H:i:s');
                        $timeDiff = time() - strtotime($createdAt);
                        if ($timeDiff < 60) {
                            echo 'Just now';
                        } elseif ($timeDiff < 3600) {
                            echo floor($timeDiff / 60) . ' min ago';
                        } elseif ($timeDiff < 86400) {
                            echo floor($timeDiff / 3600) . ' hour' . (floor($timeDiff / 3600) > 1 ? 's' : '') . ' ago';
                        } else {
                            echo floor($timeDiff / 86400) . ' day' . (floor($timeDiff / 86400) > 1 ? 's' : '') . ' ago';
                        }
                        ?>
                    </span>
                </div>

                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-4">
                    <div class="flex-1">
                        <h1 class="text-2xl lg:text-3xl font-semibold text-gray-900 mb-2"><?= htmlspecialchars($job['title'] ?? 'Job Title') ?></h1>
                        <p class="text-lg text-gray-700 font-medium"><?= htmlspecialchars($company['name'] ?? $job['company_name'] ?? 'Company') ?></p>
                    </div>
                    <div class="flex items-center gap-3">
                        <?php if ($isLoggedIn): ?>
                        <button @click="bookmarkJob()" 
                                :class="job.is_bookmarked ? 'text-gray-800' : 'text-gray-400'"
                                class="p-2 hover:bg-gray-100 rounded transition">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"></path>
                            </svg>
                        </button>
                        <?php else: ?>
                        <a href="/login?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
                           class="p-2 hover:bg-gray-100 rounded transition text-gray-400"
                           title="Login to bookmark">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"></path>
                            </svg>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($isLoggedIn): ?>
                            <?php if (!$job['has_applied']): ?>
                            <a href="/candidate/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id']) ?>" 
                               class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition whitespace-nowrap">
                                Apply Now
                            </a>
                            <?php else: ?>
                            <button disabled class="px-6 py-3 bg-gray-200 text-gray-600 font-semibold rounded-lg cursor-not-allowed whitespace-nowrap">
                                ✓ Already Applied
                            </button>
                            <?php endif; ?>
                        <?php else: ?>
                        <a href="/login?redirect=<?= urlencode('/candidate/jobs/' . ($job['slug'] ?? $job['id'])) ?>" 
                           class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition whitespace-nowrap">
                            Apply Now
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Job Attributes -->
                <div class="flex flex-wrap items-center gap-4 pt-4 border-t border-gray-200">
                    <?php if (!empty($job['category'])): ?>
                    <div class="flex items-center gap-2 text-gray-700">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="font-medium"><?= htmlspecialchars($job['category']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex items-center gap-2 text-gray-700">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-medium"><?= htmlspecialchars($job['employment_type_display'] ?? 'Full-time') ?></span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-700">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span class="font-medium">
                            <?php 
                            $etype = $job['experience_type'] ?? '';
                            $minExp = $job['min_experience'] ?? null;
                            $maxExp = $job['max_experience'] ?? null;
                            if ($etype === 'fresher') {
                                echo 'Fresher';
                            } elseif ($etype === 'any') {
                                echo 'Any Experience';
                            } elseif (($minExp !== null && $maxExp !== null) && (($minExp > 0) || ($maxExp > 0))) {
                                echo $minExp . '-' . $maxExp . ' Yrs';
                            } else {
                                echo 'Any Experience';
                            }
                            ?>
                        </span>
                    </div>
                    <?php if (($job['salary_min'] ?? 0) > 0 || ($job['salary_max'] ?? 0) > 0): ?>
                    <div class="flex items-center gap-2 text-gray-700">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-medium">
                            <?php 
                            $symbol = $job['currency_symbol'] ?? '₹';
                            if (($job['salary_min'] ?? 0) > 0 && ($job['salary_max'] ?? 0) > 0) {
                                echo $symbol . number_format($job['salary_min']) . '-' . $symbol . number_format($job['salary_max']);
                            } elseif (($job['salary_min'] ?? 0) > 0) {
                                echo $symbol . number_format($job['salary_min']);
                            }
                            ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <div class="flex items-center gap-2 text-gray-700">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="font-medium"><?= htmlspecialchars($job['location_display'] ?? 'Location not specified') ?></span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Job Description -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold mb-4">Job Description</h2>
                        <div class="prose max-w-none text-gray-700">
                            <?= $job['description'] ?? 'No description available' ?>
                        </div>
                    </div>

                    <!-- About Company -->
                    <?php if (!empty($company['description']) || !empty($company['about'])): ?>
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold mb-4">About <?= htmlspecialchars($company['name'] ?? 'Company') ?></h2>
                        <div class="prose max-w-none text-gray-700">
                            <?php
                            $aboutText = $company['description'] ?? $company['about'] ?? '';
                            if (is_string($aboutText)) {
                                $parsed = json_decode($aboutText, true);
                                if (is_array($parsed) && isset($parsed['about'])) {
                                    echo htmlspecialchars($parsed['about']);
                                } else {
                                    echo htmlspecialchars($aboutText);
                                }
                            }
                            ?>
                        </div>
                        <?php if (!empty($company['slug'])): ?>
                        <a href="/company/<?= htmlspecialchars($company['slug']) ?>" 
                           class="inline-block mt-4 text-blue-600 hover:text-blue-800 font-medium">
                            View full company profile →
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Other Jobs from Company -->
                    <?php if (!empty($otherJobs)): ?>
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold mb-4">Other Jobs from <?= htmlspecialchars($company['name'] ?? 'Company') ?></h2>
                        <div class="space-y-4">
                            <?php foreach (array_slice($otherJobs, 0, 5) as $otherJob): ?>
                            <a href="/job/<?= htmlspecialchars($otherJob['slug'] ?? $otherJob['id']) ?>" 
                               class="block border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:shadow-md transition">
                                <h3 class="font-semibold text-gray-900 mb-1"><?= htmlspecialchars($otherJob['title'] ?? 'Job') ?></h3>
                                <div class="text-sm text-gray-600">
                                    <?= htmlspecialchars($otherJob['location_display'] ?? 'Location not specified') ?>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Job Overview -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold mb-4">Job Overview</h3>
                        <div class="space-y-4">
                            <div>
                                <div class="text-sm text-gray-600">Job Type</div>
                                <div class="font-semibold text-gray-900"><?= htmlspecialchars($job['employment_type_display'] ?? 'Full-time') ?></div>
                            </div>
                            <?php if (!empty($job['category'])): ?>
                            <div>
                                <div class="text-sm text-gray-600">Category</div>
                                <div class="font-semibold text-gray-900"><?= htmlspecialchars($job['category']) ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if (($job['salary_min'] ?? 0) > 0 || ($job['salary_max'] ?? 0) > 0): ?>
                            <div>
                                <div class="text-sm text-gray-600">Salary</div>
                                <div class="font-semibold text-gray-900">
                                    <?php 
                                    $symbol = $job['currency_symbol'] ?? '₹';
                                    if (($job['salary_min'] ?? 0) > 0 && ($job['salary_max'] ?? 0) > 0) {
                                        echo $symbol . number_format($job['salary_min']) . ' - ' . $symbol . number_format($job['salary_max']);
                                    } elseif (($job['salary_min'] ?? 0) > 0) {
                                        echo $symbol . number_format($job['salary_min']);
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div>
                                <div class="text-sm text-gray-600">Location</div>
                                <div class="font-semibold text-gray-900"><?= htmlspecialchars($job['location_display'] ?? 'Not specified') ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Company Info -->
                    <?php if (!empty($company['name'])): ?>
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold mb-4">Company</h3>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <?php if (!empty($company['logo_url'])): ?>
                                <img src="<?= htmlspecialchars($company['logo_url']) ?>" 
                                     alt="<?= htmlspecialchars($company['name']) ?>"
                                     class="w-12 h-12 rounded-lg object-cover">
                                <?php endif; ?>
                                <div>
                                    <div class="font-semibold text-gray-900"><?= htmlspecialchars($company['name']) ?></div>
                                    <?php if (!empty($companyStats['rating'])): ?>
                                    <div class="text-sm text-gray-600">
                                        <?= number_format($companyStats['rating'], 1) ?> rating
                                        <?php if (!empty($companyStats['reviews_count'])): ?>
                                        (<?= $companyStats['reviews_count'] ?> reviews)
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($company['slug'])): ?>
                            <a href="/company/<?= htmlspecialchars($company['slug']) ?>" 
                               class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                View Company Profile
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function publicJobDetail() {
            return {
                job: {
                    id: <?= $job['id'] ?? 0 ?>,
                    slug: <?= json_encode($job['slug'] ?? '') ?>,
                    is_bookmarked: <?= ($job['is_bookmarked'] ?? false) ? 'true' : 'false' ?>,
                    has_applied: <?= ($job['has_applied'] ?? false) ? 'true' : 'false' ?>
                },
                isFollowing: <?= ($isFollowing ?? false) ? 'true' : 'false' ?>,
                isLoggedIn: <?= ($isLoggedIn ?? false) ? 'true' : 'false' ?>,
                companyId: <?= $company['id'] ?? 0 ?>,
                async bookmarkJob() {
                    if (!this.isLoggedIn) {
                        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                        return;
                    }
                    try {
                        const response = await fetch(`/candidate/jobs/${this.job.slug || this.job.id}/bookmark`, {
                            method: 'POST',
                            headers: { 'X-CSRF-Token': this.getCsrfToken() }
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.job.is_bookmarked = data.bookmarked;
                        }
                    } catch (error) {
                        console.error('Bookmark error:', error);
                    }
                },
                async toggleFollow() {
                    if (!this.isLoggedIn) {
                        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                        return;
                    }
                    try {
                        const response = await fetch('/company/follow', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': this.getCsrfToken()
                            },
                            body: JSON.stringify({ company_id: this.companyId })
                        });
                        const data = await response.json();
                        if (data.status) {
                            this.isFollowing = (data.status === 'followed');
                        }
                    } catch (error) {
                        console.error('Follow error:', error);
                    }
                },
                getCsrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.content || '';
                }
            }
        }
    </script>
       <?php
require __DIR__ . '/../../include/footer.php';
?>
</body>
</html>

