<?php
/** @var array $job */
/** @var array $company */
/** @var bool $isLoggedIn */
/** @var array $locationRows */
/** @var array $relatedJobs */
/** @var string $base */

$job = $job ?? [];
$company = $company ?? [];
$isLoggedIn = $isLoggedIn ?? false;
$otherJobs = $otherJobs ?? $relatedJobs ?? [];
$base = $base ?? '/';
$isExternalJob = (($job['job_type'] ?? 'internal') === 'external');

if (!function_exists('fix_url')) {
    function fix_url($url) {
        if (empty($url)) return '';
        $url = trim((string)$url);
        
        // Normalize backslashes to forward slashes
        $url = str_replace('\\', '/', $url);
        
        if (preg_match('#^https?://#i', $url)) return $url;
        
        // Migration fix: /companies/ or /company/ -> /uploads/companies/ or /uploads/company/
        if ((strpos($url, '/companies/') === 0 || strpos($url, 'companies/') === 0 || 
             strpos($url, '/company/') === 0 || strpos($url, 'company/') === 0) && 
            strpos($url, 'uploads/') === false) {
            $url = '/uploads/' . ltrim($url, '/');
        }
        
        return '/' . ltrim($url, '/');
    }
}

$companyBannerSource = $company['banner_url']
    ?? $company['cover_image']
    ?? $company['profile_image']
    ?? '';
$companyBannerUrl = !empty($companyBannerSource) ? fix_url($companyBannerSource) : '';
$jobHeaderFallback = fix_url('assets/images/background-img.webp');
$jobHeaderBackground = $companyBannerUrl ?: $jobHeaderFallback;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title><?= htmlspecialchars($job['title'] ?? 'Job') ?> - <?= htmlspecialchars($company['name'] ?? 'Company') ?> - Jobsence</title>
    <meta name="description" content="<?= htmlspecialchars($job['description'] ?? 'Job Description') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($job['keywords'] ?? 'Job Keywords') ?>">
    <meta name="author" content="<?= htmlspecialchars($company['name'] ?? 'Company') ?>">
    <meta name="robots" content="index, follow">
    

    <link href="/css/output.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        html, body {
            overflow-x: hidden;
            width: 100%;
            position: relative;
        }
        :root{
            --color-primary:#f05537;
            --color-primary-hover:#FF6A3D;
            --color-dark-header: #0f172a; /* Dark charcoal/navy substitute */
        }
        body { font-family: 'Nunito Sans', sans-serif; font-weight: 400; }
        .bg-primary{background-color:var(--color-primary) !important}
        .hover\:bg-primary-600:hover{background-color:var(--color-primary-hover) !important}
        .text-primary{color:var(--color-primary) !important}
        
        /* New Styles for Job Detail Redesign */
        .job-header-section {
            background-color: var(--color-dark-header);
            background-image: var(--banner-url);
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            color: #ffffff;
            padding: 3.5rem 0;
            position: relative;
            isolation: isolate;
        }
        .job-header-section::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 0;
            background: linear-gradient(90deg, rgba(15, 23, 42, 0.24), rgba(15, 23, 42, 0.10));
            pointer-events: none;
        }
        .job-tag {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .apply-btn-header {
            background-color: var(--color-primary);
            color: #ffffff;
            padding: 0.75rem 2.5rem;
            border-radius: 0.5rem;
            font-weight: 700;
            transition: all 0.2s;
        }
        .apply-btn-header:hover {
            background-color: var(--color-primary-hover);
            transform: translateY(-1px);
        }
        .save-btn-header {
            background-color: transparent;
            color: #ffffff;
            padding: 0.75rem 2.5rem;
            border-radius: 0.5rem;
            font-weight: 700;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.2s;
        }
        .save-btn-header:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: #ffffff;
        }
        .content-card {
            background-color: #ffffff;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .sidebar-card {
            background-color: #ffffff;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .breadcrumb-item {
            display: flex;
            items-center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.875rem;
        }
        .breadcrumb-item a:hover { color: #ffffff; }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Styles for Job Description HTML content */
        .job-description-content ul {
            list-style-type: disc !important;
            margin-left: 1.5rem !important;
            margin-bottom: 1rem !important;
        }
        .job-description-content ol {
            list-style-type: decimal !important;
            margin-left: 1.5rem !important;
            margin-bottom: 1rem !important;
        }
        .job-description-content li {
            margin-bottom: 0.5rem !important;
            display: list-item !important;
        }
        .job-description-content p {
            margin-bottom: 1rem !important;
        }
        .job-description-content h1, .job-description-content h2, .job-description-content h3, .job-description-content h4 {
            font-weight: bold !important;
            margin-top: 1.5rem !important;
            margin-bottom: 0.75rem !important;
        }
    </style>
</head>
<body class="bg-[#f8fafc]">
<?php if (!empty($isPreview)): ?>
    <div style="background:#fffbeb;border-bottom:1px solid #f59e0b;color:#92400e;padding:10px 16px;text-align:center;font-weight:700;font-size:14px;position:relative;z-index:60">
        Preview – this job is <u><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string)($previewStatus ?: 'draft')))) ?></u> and is not visible to candidates.
        <?php if (($previewStatus ?? '') === 'pending_review'): ?>It will go live after our team reviews it.<?php endif; ?>
    </div>
<?php endif; ?>
    <?php $base = $base ?? '/';
     require __DIR__ . '/../../include/header.php';
    ?>

    <div x-data="publicJobDetail()" x-cloak>
        
        <!-- Apply Confirmation Modal -->
        <div x-show="showApplyModal" 
             class="fixed inset-0 z-[100] overflow-y-auto" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" @click="showApplyModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                    
                    <div class="px-6 py-6 sm:px-8 sm:py-8">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-extrabold text-gray-900 tracking-tight">Apply for this position</h3>
                            <button @click="showApplyModal = false" class="text-gray-400 hover:text-gray-500 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <!-- Job Mini Info -->
                        <div class="flex items-center gap-4 p-4 mb-6 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shrink-0 shadow-sm">
                                <?php if (!empty($company['logo_url'])): ?>
                                    <img src="<?= fix_url($company['logo_url']) ?>" class="max-w-full max-h-full object-contain">
                                <?php else: ?>
                                    <span class="text-lg font-bold text-primary"><?= strtoupper(substr($company['name'] ?? 'J', 0, 1)) ?></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900 text-sm"><?= htmlspecialchars($job['title']) ?></div>
                                <div class="text-xs text-gray-500 font-medium"><?= htmlspecialchars($company['name']) ?></div>
                            </div>
                        </div>

                        <!-- Profile Check -->
                        <template x-if="isIncomplete">
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-bold text-gray-700">Profile Strength</span>
                                    <span class="text-sm font-bold text-primary" x-text="profileStrength + '%'"></span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2 mb-4">
                                    <div class="bg-primary h-2 rounded-full transition-all duration-500" :style="'width: ' + profileStrength + '%'"></div>
                                </div>
                                
                                <div class="p-4 bg-orange-50 border border-orange-100 rounded-xl">
                                    <div class="flex gap-3">
                                        <svg class="w-5 h-5 text-orange-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        <div>
                                            <div class="text-sm font-bold text-orange-800 mb-1">Incomplete Profile</div>
                                            <p class="text-xs text-orange-700 leading-relaxed mb-3">Professional jobs require at least 80% profile completion. Please add the following details:</p>
                                            <ul class="grid grid-cols-2 gap-y-1 gap-x-4">
                                                <template x-for="field in missingFields" :key="field">
                                                    <li class="text-[11px] text-orange-800 flex items-center gap-1.5 font-medium">
                                                        <span class="w-1 h-1 rounded-full bg-orange-400"></span>
                                                        <span x-text="field"></span>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="!isIncomplete">
                            <div class="mb-8">
                                <p class="text-sm text-gray-600 leading-relaxed">By clicking confirm, your professional profile snapshot will be shared with <span class="font-bold text-gray-900"><?= htmlspecialchars($company['name']) ?></span>. Make sure your resume and contact details are up to date.</p>
                            </div>
                        </template>

                        <!-- Buttons -->
                        <div class="flex gap-3">
                            <button @click="showApplyModal = false" class="flex-1 px-4 py-3 text-sm font-bold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">Cancel</button>
                            
                            <template x-if="isIncomplete">
                                <a href="/candidate/profile/complete" class="flex-2 px-8 py-3 text-sm font-bold text-white bg-primary rounded-xl hover:bg-primary-hover shadow-lg shadow-primary/20 transition-all text-center">Complete Profile</a>
                            </template>
                            
                            <template x-if="!isIncomplete">
                                <button @click="submitApplication()" 
                                        :disabled="isSubmitting"
                                        class="flex-2 px-8 py-3 text-sm font-bold text-white bg-primary rounded-xl hover:bg-primary-hover shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2">
                                    <svg x-show="isSubmitting" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span x-text="isSubmitting ? 'Applying...' : 'Confirm Application'"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Dark Header Section -->
        <div class="job-header-section"
             style="--banner-url: url('<?= htmlspecialchars($jobHeaderBackground) ?>')">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Breadcrumbs -->
                <nav class="flex items-center gap-2 mb-8 opacity-80">
                    <div class="breadcrumb-item"><a href="/">Home</a> <span>›</span></div>
                    <?php if(!empty($job['category'])): ?>
                        <div class="breadcrumb-item"><a href="/jobs?category=<?= urlencode($job['category']) ?>"><?= htmlspecialchars($job['category']) ?></a> <span>›</span></div>
                    <?php endif; ?>
                    <div class="breadcrumb-item text-white"><?= htmlspecialchars($job['title']) ?></div>
                </nav>

                <div class="flex flex-col lg:flex-row gap-8 items-start lg:items-center relative z-10">
                    <!-- Company Logo -->
                    <div class="w-24 h-24 bg-white rounded-xl p-2 flex items-center justify-center shrink-0 shadow-2xl border-2 border-white/20">
                        <?php if (!empty($company['logo_url'])): ?>
                            <img src="<?= fix_url($company['logo_url']) ?>" alt="<?= htmlspecialchars($company['name']) ?>" class="max-w-full max-h-full object-contain">
                        <?php else: ?>
                            <span class="text-3xl font-bold text-primary"><?= strtoupper(substr($company['name'] ?? 'J', 0, 1)) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Job Info -->
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight"><?= htmlspecialchars($job['title']) ?></h1>
                            <button @click="bookmarkJob()" class="text-white/40 hover:text-primary transition-colors">
                                <svg class="w-6 h-6" :class="job.is_bookmarked ? 'fill-primary text-primary' : 'fill-none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
                            </button>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-y-2 gap-x-6 text-lg font-medium opacity-90 mb-6">
                            <span class="text-primary-400 font-bold"><?= htmlspecialchars($company['name'] ?? 'Company') ?></span>
                            <span class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <?php 
                                $minExp = $job['min_experience'] ?? 0;
                                $maxExp = $job['max_experience'] ?? 0;
                                echo ($minExp == 0 && $maxExp == 0) ? 'Any Experience' : "$minExp - $maxExp Years";
                                ?>
                            </span>
                            <span class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                <?php 
                                $locRaw = $job['location_display'] ?? '';
                                if (strpos($locRaw, '{') === 0 || strpos($locRaw, '[') === 0) {
                                    $locJson = json_decode($locRaw, true);
                                    if (json_last_error() === JSON_ERROR_NONE) {
                                        $locArr = is_array($locJson) ? (isset($locJson[0]) ? $locJson[0] : $locJson) : [];
                                        $locParts = [];
                                        if (!empty($locArr['city'])) $locParts[] = $locArr['city'];
                                        if (!empty($locArr['country'])) $locParts[] = $locArr['country'];
                                        echo htmlspecialchars(implode(', ', $locParts));
                                    } else echo htmlspecialchars($locRaw);
                                } else echo htmlspecialchars($locRaw ?: 'Location not specified');
                                ?>
                            </span>
                        </div>
                        
                        <!-- Tags -->
                        <div class="flex flex-wrap gap-2">
                            <?php if(!empty($job['skills'])): 
                                $skills = is_array($job['skills']) ? $job['skills'] : explode(',', $job['skills']);
                                foreach(array_slice($skills, 0, 8) as $skill): 
                                    $skillName = is_array($skill) ? ($skill['name'] ?? '') : (is_string($skill) ? $skill : '');
                                    if (empty(trim($skillName))) continue;
                                ?>
                                    <span class="job-tag uppercase tracking-wider text-[10px]"><?= htmlspecialchars(trim($skillName)) ?></span>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>

                    <!-- Action Section -->
                    <div class="flex flex-col items-start lg:items-end gap-4 min-w-[200px]">
                        <div class="text-xs font-bold uppercase tracking-widest text-white/40">
                            Posted <?= date('d M Y', strtotime($job['created_at'] ?? 'now')) ?>
                        </div>
                        <div class="flex gap-4">
                            <?php if (($job['job_type'] ?? 'internal') === 'external'): ?>
                                <a href="<?= htmlspecialchars($job['apply_link'] ?? '#') ?>" target="_blank" class="apply-btn-header shadow-xl shadow-primary/20">Apply Now</a>
                            <?php elseif ($isLoggedIn): ?>
                                <?php if (!($job['has_applied'] ?? false)): ?>
                                    <button @click="applyNow()" class="apply-btn-header shadow-xl shadow-primary/20">Apply Now</button>
                                <?php else: ?>
                                    <button disabled class="apply-btn-header opacity-50 cursor-not-allowed">Already Applied</button>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="/login?redirect=<?= urlencode('/job/' . ($job['slug'] ?? ($job['id'] ?? ''))) ?>" class="apply-btn-header shadow-xl shadow-primary/20">Apply Now</a>
                            <?php endif; ?>

                            <button @click="bookmarkJob()" class="save-btn-header">
                                <span x-text="job.is_bookmarked ? 'Saved' : 'Save Job'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column: Job Description -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="content-card">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-4 border-b border-gray-100">Job Description</h2>
                        <div class="prose max-w-none text-gray-700 leading-relaxed job-description-content">
                            <?php 
                            $desc = $job['description'] ?? 'No description available';
                            if ($desc !== '' && strip_tags($desc) !== $desc) {
                                echo $desc; 
                            } else {
                                echo nl2br(htmlspecialchars($desc));
                            }
                            ?>
                        </div>
                    </div>

                    <!-- About Company -->
                    <?php 
                    $aboutText = $company['description'] ?? $company['about'] ?? '';
                    $displayAbout = '';
                    if (is_string($aboutText) && !empty(trim($aboutText))) {
                        $parsed = json_decode($aboutText, true);
                        $displayAbout = $parsed['about'] ?? $aboutText;
                    }
                    ?>
                    <?php if (!empty($company['name']) && !empty(trim((string)$displayAbout))): ?>
                    <div class="content-card">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">About <?= htmlspecialchars($company['name']) ?></h2>
                        <div class="text-gray-700 leading-relaxed mb-6">
                            <?= nl2br(htmlspecialchars($displayAbout)) ?>
                        </div>
                        <?php if (!$isExternalJob && !empty($company['slug'])): ?>
                            <a href="/company/<?= htmlspecialchars($company['slug']) ?>" class="text-primary font-bold hover:underline">View full company profile →</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <!-- Similar Jobs Section -->
                    <div class="mt-12">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                            <span class="w-8 h-8 bg-primary/10 text-primary rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </span>
                            Similar Jobs That You Might Be Interested In
                        </h2>
                        
                        <div class="space-y-4">
                            <?php if(!empty($otherJobs)): foreach(array_slice($otherJobs, 0, 4) as $sJob): ?>
                                <div class="bg-white border border-gray-100 rounded-xl p-5 hover:border-primary hover:shadow-lg transition-all group relative">
                                    <div class="flex gap-5">
                                        <div class="w-14 h-14 bg-gray-50 rounded-lg flex items-center justify-center shrink-0 group-hover:bg-primary/5 transition-colors">
                                            <span class="text-xl font-bold text-gray-400 group-hover:text-primary"><?= strtoupper(substr($sJob['company_name'] ?? 'C', 0, 1)) ?></span>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-bold text-gray-900 group-hover:text-primary transition-colors"><?= htmlspecialchars($sJob['title']) ?></h3>
                                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-xs text-gray-500 font-medium">
                                                <span class="text-primary"><?= htmlspecialchars($sJob['company_name'] ?? 'Company') ?></span>
                                                <span><?= htmlspecialchars($sJob['min_experience'] ?? 0) ?>-<?= htmlspecialchars($sJob['max_experience'] ?? 0) ?> Yrs</span>
                                                <span><?= htmlspecialchars($sJob['location_display'] ?? 'Remote') ?></span>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end justify-between">
                                            <div class="flex gap-2">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Premium</span>
                                            </div>
                                            <a href="/job/<?= htmlspecialchars($sJob['slug'] ?? $sJob['id']) ?>" class="text-xs font-bold text-primary hover:underline">View Details →</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; else: ?>
                                <p class="text-sm text-gray-500 italic">No similar jobs found at the moment.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Sidebar -->
                <div class="space-y-6">
                    <!-- Posted By Card -->
                    <div class="sidebar-card overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-primary/5 rounded-full -mr-12 -mt-12"></div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 relative">Posted by</h3>
                        <div class="flex items-center gap-4 relative">
                            <div class="w-14 h-14 bg-primary/10 rounded-full flex items-center justify-center text-primary font-black text-xl border-2 border-primary/20">
                                <?= strtoupper(substr($company['name'] ?? 'A', 0, 1)) ?>
                            </div>
                            <div>
                                <div class="font-extrabold text-gray-900 leading-tight"><?= htmlspecialchars($company['name'] ?? 'Company') ?></div>
                                <?php if ($isExternalJob): ?>
                                    <div class="text-[10px] font-bold text-amber-600 uppercase tracking-wider mt-1 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.72-1.36 3.486 0l5.58 9.922c.75 1.334-.213 2.979-1.743 2.979H4.42c-1.53 0-2.493-1.645-1.743-2.979l5.58-9.922zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-2a1 1 0 01-1-1V7a1 1 0 112 0v3a1 1 0 01-1 1z" clip-rule="evenodd"></path></svg>
                                        External Listing
                                    </div>
                                <?php else: ?>
                                    <div class="text-[10px] font-bold text-green-600 uppercase tracking-wider mt-1 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                        Verified Employer
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-2 mt-8 border-t border-gray-50 pt-6">
                            <div class="text-center">
                                <div class="text-xl font-black text-gray-900"><?= number_format((int)($job['views_count'] ?? 0)) ?></div>
                                <div class="text-[9px] text-gray-400 uppercase font-bold tracking-tighter">Views</div>
                            </div>
                            <div class="text-center border-x border-gray-100 px-2">
                                <div class="text-xl font-black text-gray-900"><?= number_format((int)($job['applications_count'] ?? 0)) ?></div>
                                <div class="text-[9px] text-gray-400 uppercase font-bold tracking-tighter">Applied</div>
                            </div>
                            <div class="text-center">
                                <div class="text-xl font-black text-gray-900"><?= number_format((int)($job['shortlisted_count'] ?? 0)) ?></div>
                                <div class="text-[9px] text-gray-400 uppercase font-bold tracking-tighter">Shortlisted</div>
                            </div>
                        </div>
                    </div>

                    <!-- Job Quick Details -->
                    <div class="sidebar-card">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6">Key Highlights</h3>
                        <div class="space-y-5">
                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 bg-orange-50 text-orange-600 rounded-lg flex items-center justify-center shrink-0 mt-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <div class="text-[10px] text-gray-400 font-bold uppercase">Job Type</div>
                                    <div class="font-bold text-gray-900 text-sm"><?= htmlspecialchars($job['employment_type_display'] ?? 'Full-time') ?></div>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center shrink-0 mt-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                                <div>
                                    <div class="text-[10px] text-gray-400 font-bold uppercase">Category</div>
                                    <div class="font-bold text-gray-900 text-sm"><?= htmlspecialchars($job['category'] ?? 'Not specified') ?></div>
                                </div>
                            </div>
                            <?php if (($job['salary_min'] ?? 0) > 0): ?>
                            <div class="flex items-start gap-4">
                                <div class="w-8 h-8 bg-green-50 text-green-600 rounded-lg flex items-center justify-center shrink-0 mt-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <div class="text-[10px] text-gray-400 font-bold uppercase">Salary</div>
                                    <div class="font-bold text-gray-900 text-sm">
                                        <?= ($job['currency_symbol'] ?? '₹') . number_format($job['salary_min']) ?>
                                        <?php if(!empty($job['salary_max'])): ?> - <?= number_format($job['salary_max']) ?><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Jobs by Location (Dynamic) -->
                    <div class="sidebar-card">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Jobs by location</h3>
                        <div class="space-y-2">
                            <?php 
                            $locList = !empty($locationRows) ? array_map(function($l) { return $l['city']; }, $locationRows) : ['Bangalore', 'Mumbai', 'Delhi NCR', 'Noida', 'Gurgaon', 'Hyderabad'];
                            foreach(array_unique(array_slice($locList, 0, 8)) as $l): if(empty($l)) continue; ?>
                                <a href="/jobs?q=<?= urlencode($l) ?>" class="text-sm text-gray-700 hover:text-primary transition-colors flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                    <span>Jobs in <?= htmlspecialchars($l) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Apply On The Go -->
                    <!--<div class="sidebar-card">-->
                    <!--    <div class="flex items-start gap-4">-->
                    <!--        <div class="flex-1">-->
                    <!--            <h3 class="text-2xl font-semibold text-gray-900 leading-tight">Apply on the go!</h3>-->
                    <!--            <p class="text-sm md:text-base font-semibold text-gray-900 leading-snug mt-1">-->
                    <!--                Download the Jobsence app to apply for jobs anywhere, anytime-->
                    <!--            </p>-->
                    <!--            <div class="flex items-center gap-2 mt-4">-->
                    <!--                <a href="#" class="inline-block">-->
                    <!--                    <div class="h-10 bg-black rounded-md flex items-center px-3">-->
                    <!--                        <svg class="w-4 h-4 text-white mr-2" fill="currentColor" viewBox="0 0 24 24">-->
                    <!--                            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>-->
                    <!--                        </svg>-->
                    <!--                        <div class="leading-none">-->
                    <!--                            <div class="text-[9px] text-white">Download on</div>-->
                    <!--                            <div class="text-xs text-white font-semibold mt-0.5">App Store</div>-->
                    <!--                        </div>-->
                    <!--                    </div>-->
                    <!--                </a>-->
                    <!--                <a href="#" class="inline-block">-->
                    <!--                    <div class="h-10 bg-black rounded-md flex items-center px-3">-->
                    <!--                        <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none">-->
                    <!--                            <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5Z" fill="#00D9FF"/>-->
                    <!--                            <path d="M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12Z" fill="#FFCE00"/>-->
                    <!--                            <path d="M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.5,12.92 20.16,13.19L17.19,15.12L14.54,12.85L17.19,10.81L20.16,10.81Z" fill="#00F076"/>-->
                    <!--                            <path d="M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z" fill="#FF3A44"/>-->
                    <!--                        </svg>-->
                    <!--                        <div class="leading-none">-->
                    <!--                            <div class="text-[9px] text-white">Get it on</div>-->
                    <!--                            <div class="text-xs text-white font-semibold mt-0.5">Google Play</div>-->
                    <!--                        </div>-->
                    <!--                    </div>-->
                    <!--                </a>-->
                    <!--            </div>-->
                    <!--        </div>-->
                    <!--        <div class="shrink-0 text-center">-->
                    <!--            <div class="w-24 h-24 border border-gray-300 rounded-lg p-1 bg-white">-->
                    <!--                <img src="/uploads/qr.jpeg" alt="QR code" class="w-full h-full object-contain rounded">-->
                    <!--            </div>-->
                    <!--            <p class="text-[11px] text-gray-600 mt-2">Scan to Download</p>-->
                    <!--        </div>-->
                    <!--    </div>-->
                    <!--</div>-->

                    <?php
                    $displayBlogs = isset($interviewBlogs) && is_array($interviewBlogs) ? $interviewBlogs : [];
                    if (empty($displayBlogs) && isset($companyBlogs) && is_array($companyBlogs)) {
                        $displayBlogs = $companyBlogs;
                    }
                    $displayBlogs = array_values(array_filter($displayBlogs, static function ($blog) {
                        return is_array($blog) && !empty($blog['slug']) && !empty($blog['title']);
                    }));
                    $blogCount = min(count($displayBlogs), 5);
                    ?>
                    <div class="sidebar-card">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-sm font-semibold text-gray-900">Interview Questions for you</h3>
                            <a href="/blog" class="text-xs font-medium text-primary hover:underline">View All</a>
                        </div>
                        <?php if ($blogCount > 0): ?>
                        <div x-data="blogCardSlider(<?= $blogCount ?>)" class="relative">
                            <div class="overflow-hidden rounded-lg">
                                <div class="flex transition-transform duration-300 ease-in-out"
                                     :style="'transform: translateX(-' + (currentSlide * 100) + '%)'">
                                    <?php foreach (array_slice($displayBlogs, 0, $blogCount) as $blog): ?>
                                    <div class="min-w-full">
                                        <a href="/blog/<?= htmlspecialchars($blog['slug']) ?>" class="block border border-gray-200 rounded-lg overflow-hidden bg-white hover:border-primary/40 transition">
                                            <?php if (!empty($blog['featured_image'])): ?>
                                            <img src="<?= htmlspecialchars(fix_url($blog['featured_image'])) ?>"
                                                 alt="<?= htmlspecialchars($blog['title']) ?>"
                                                 class="w-full h-44 object-cover">
                                            <?php else: ?>
                                            <div class="w-full h-44 bg-gray-100 flex items-center justify-center text-gray-400 text-sm">No image</div>
                                            <?php endif; ?>
                                            <div class="p-3">
                                                <h4 class="text-sm font-semibold text-gray-900 leading-snug line-clamp-2"><?= htmlspecialchars($blog['title']) ?></h4>
                                            </div>
                                        </a>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php if ($blogCount > 1): ?>
                            <div class="flex justify-center gap-2 mt-3">
                                <?php for ($i = 0; $i < $blogCount; $i++): ?>
                                <button type="button"
                                        @click="currentSlide = <?= $i ?>"
                                        :class="currentSlide === <?= $i ?> ? 'bg-primary' : 'bg-gray-300'"
                                        class="w-2 h-2 rounded-full transition-colors"></button>
                                <?php endfor; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-sm text-gray-500">No interview blogs available right now.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function blogCardSlider(totalSlides) {
            return {
                currentSlide: 0,
                totalSlides: totalSlides || 1
            };
        }

        function publicJobDetail() {
            return {
                job: {
                    id: <?= $job['id'] ?? 0 ?>,
                    slug: <?= json_encode($job['slug'] ?? '') ?>,
                    job_type: <?= json_encode($job['job_type'] ?? 'internal') ?>,
                    apply_link: <?= json_encode($job['apply_link'] ?? '') ?>,
                    is_bookmarked: <?= ($job['is_bookmarked'] ?? false) ? 'true' : 'false' ?>,
                    has_applied: <?= ($job['has_applied'] ?? false) ? 'true' : 'false' ?>
                },
                isLoggedIn: <?= ($isLoggedIn ?? false) ? 'true' : 'false' ?>,
                showApplyModal: false,
                isSubmitting: false,
                isIncomplete: false,
                profileStrength: 0,
                missingFields: [],

                async bookmarkJob() {
                    if (!this.isLoggedIn) {
                        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                        return;
                    }
                    try {
                        const response = await fetch(`/candidate/jobs/${this.job.slug || this.job.id}/bookmark`, {
                            method: 'POST',
                            headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content }
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.job.is_bookmarked = data.bookmarked;
                        }
                    } catch (error) { console.error('Bookmark error:', error); }
                },

                async applyNow() {
                    if (!this.isLoggedIn) {
                        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                        return;
                    }
                    if (this.job.job_type === 'external' && this.job.apply_link) {
                        window.open(this.job.apply_link, '_blank', 'noopener,noreferrer');
                        return;
                    }

                    // Open confirmation modal first
                    this.showApplyModal = true;
                    this.isIncomplete = false;
                    this.missingFields = [];
                    this.profileStrength = 0;
                },

                async submitApplication() {
                    if (this.isSubmitting) return;
                    
                    this.isSubmitting = true;
                    try {
                        const response = await fetch(`/candidate/jobs/${this.job.slug || this.job.id}/apply`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            },
                            body: JSON.stringify({})
                        });
                        
                        const data = await response.json();
                        
                        if (response.ok && data.success) {
                            this.job.has_applied = true;
                            this.showApplyModal = false;
                            // Show success toast or alert
                            alert(data.message || 'Application submitted successfully!');
                        } else if (response.status === 422 && data.incomplete_profile) {
                            this.isIncomplete = true;
                            this.missingFields = data.missing_fields || [];
                            this.profileStrength = data.profile_strength || 0;
                        } else {
                            alert(data.error || 'Failed to submit application');
                            if (!data.incomplete_profile) {
                                this.showApplyModal = false;
                            }
                        }
                    } catch (error) {
                        console.error('Apply error:', error);
                        alert('Unable to apply right now. Please try again.');
                        this.showApplyModal = false;
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }
        }
    </script>
    <?php require __DIR__ . '/../../include/footer.php'; ?>
</body>
</html>


