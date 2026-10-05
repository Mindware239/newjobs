<?php
// resources/views/company/details.php

/** @var array $company */
/** @var array $jobs */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$company = $company ?? [];
$jobs = $jobs ?? [];
$activeTab = $activeTab ?? 'snapshot';
$reviews_count = $reviews_count ?? 0;
$rating = $rating ?? 0;
$whyPoints = $whyPoints ?? [];
$aboutText = $aboutText ?? '';
$isFollowing = $isFollowing ?? false;
$loggedInCandidateId = $loggedInCandidateId ?? $_SESSION['candidate_id'] ?? null;

// Helpers for safe access
if (!function_exists('e')) {
    /**
     * @param string|null $v
     * @return string
     */
    function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES); }
}

// Use an associative array for company data for easier structure mapping
$companyData = [
    'logo_url'      => !empty($company['logo_url']) ? (strpos($company['logo_url'], '/uploads/companies/') === false ? str_replace('/companies/', '/uploads/companies/', $company['logo_url']) : $company['logo_url']) : 'https://plus.unsplash.com/premium_photo-1667354097023-4b8d9c3f7767?q=80&w=726&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
    'banner_url'    => !empty($company['banner_url']) ? (strpos($company['banner_url'], '/uploads/companies/') === false ? str_replace('/companies/', '/uploads/companies/', $company['banner_url']) : $company['banner_url']) : 'https://plus.unsplash.com/premium_photo-1661963103403-32d25927f577?q=80&w=1194&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
    'ceo_photo'     => !empty($company['ceo_photo']) ? (strpos($company['ceo_photo'], '/uploads/companies/') === false ? str_replace('/companies/', '/uploads/companies/', $company['ceo_photo']) : $company['ceo_photo']) : '/assets/images/ceo-placeholder.jpg',
    'name'          => $company['name'] ?? 'Company Name',
    'rating'        => intval($company['rating'] ?? 0),
    'reviews_count' => intval($company['reviews_count'] ?? 0),
    'views'         => intval($company['views'] ?? 0),
    'description'   => $company['description'] ?? 'No description available.',
    'ceo_name'      => $company['ceo_name'] ?? 'CEO Name',
    'industry'      => $company['industry'] ?? 'Technology',
    'headquarters'  => $company['headquarters'] ?? 'San Jose, CA',
    'founded_year'  => $company['founded_year'] ?? '1998',
    'company_size'  => $company['company_size'] ?? 'More than 10,000',
    'revenue'       => $company['revenue'] ?? 'More than $830B',
    'website'       => $company['website'] ?? '#',
    'id'            => $company['id'] ?? 1, // Add an ID for job/review linking
    'slug'          => $company['slug'] ?? ($company['company_slug'] ?? 'company'),
];

$companyName   = $companyData['name'];
$rating        = $companyData['rating'];
$reviews_count = $companyData['reviews_count'];

// --- Placeholder/Mock Data for new sections (Replace with real data fetching) ---


$mockCulturePoints = [
    'Competitive salaries and benefits',
    'Global projects and career growth',
    'Supportive work culture and training',
    'Innovative and fast-paced environment',
];

// Dynamic Why Join points from company description JSON (about + why_points + tagline)
$whyPoints = [];
$aboutText = '';
$tagline = '';
$rawDesc = $company['description'] ?? '';
if (is_string($rawDesc)) {
    $parsed = json_decode($rawDesc, true);
    if (is_array($parsed)) {
        if (isset($parsed['why_points']) && is_array($parsed['why_points'])) {
            $whyPoints = array_values(array_filter(array_map('trim', $parsed['why_points'])));
        }
        if (isset($parsed['about']) && is_string($parsed['about'])) {
            $aboutText = trim($parsed['about']);
        }
        if (isset($parsed['tagline']) && is_string($parsed['tagline'])) {
            $tagline = trim($parsed['tagline']);
        }
    }
}
if (empty($whyPoints)) {
    $whyPoints = $mockCulturePoints;
}

// Tabs definition
$tabs = [
    'snapshot' => 'Overview',
    'why'      => 'Why Join Us',
    'reviews'  => 'Reviews',
    'jobs'     => 'Jobs',
    'blogs'    => 'Blogs'
];


// Simulate a default active tab (can be set from controller)
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$parts = explode('/', $path);

$activeTab = $parts[2] ?? 'snapshot';

// VALIDATE TAB
$validTabs = array_keys($tabs);
if (!in_array($activeTab, $validTabs, true)) {
    $activeTab = 'snapshot';
    
}

$baseUrl = '/company/' . $companyData['slug'];

// Follow state
$loggedInCandidateId = $_SESSION['candidate_id'] ?? null;
$isFollowing = false;

if ($loggedInCandidateId) {
    $isFollowing = \App\Models\CompanyFollower::isFollowing($loggedInCandidateId, $companyData['id']);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= e($companyName) ?> — Company Profile</title>
  <link href="/css/output.css" rel="stylesheet">
  <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <style>
    .glass {
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(6px);
    }
    
    /* Skeleton Loading Styles */
    .skeleton {
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200% 100%;
      animation: loading 1.5s ease-in-out infinite;
    }
    
    @keyframes loading {
      0% {
        background-position: 200% 0;
      }
      100% {
        background-position: -200% 0;
      }
    }
    
    .skeleton-text {
      height: 1rem;
      background-color: #e5e7eb;
      border-radius: 0.25rem;
    }
    
    .skeleton-title {
      height: 2rem;
      background-color: #d1d5db;
      border-radius: 0.25rem;
    }
    
    .skeleton-image {
      background-color: #e5e7eb;
      border-radius: 0.25rem;
    }
  </style>
</head>
<body class="bg-gray-50 text-gray-800" x-data="{ isLoading: false }" x-cloak>
  <?php 
  // Include standard header
  $base = $base ?? '/';
  require __DIR__ . '/../include/header.php';
  ?>
  
  <header class="relative">
    <div class="h-64 md:h-80 lg:h-96 overflow-hidden relative" style="background: #0f172a;">
      <?php if (!empty($companyData['banner_url']) && $companyData['banner_url'] !== 'https://plus.unsplash.com/premium_photo-1661963103403-32d25927f577?q=80&w=1194&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'): ?>
        <img src="<?= e($companyData['banner_url']) ?>" alt="Banner" class="w-full h-full object-cover opacity-50" loading="eager">
      <?php else: ?>
        <!-- Dark Header Background with Fallback Image and Pattern -->
        <div class="w-full h-full" style="background: radial-gradient(circle at 20% 50%, rgba(240, 85, 55, 0.15) 0%, transparent 50%), url(&quot;/assets/images/background-img.webp&quot;), #0f172a; background-size: cover; background-position: center; opacity: 0.8;"></div>
        <div class="absolute inset-0" style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 86c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm66-3c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm-46-4c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm37-3c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM61 5c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM33 5c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm-5 60c0-4.418 3.582-8 8-8s8 3.582 8 8-3.582 8-8 8-8-3.582-8-8zm63 22c0-4.418 3.582-8 8-8s8 3.582 8 8-3.582 8-8 8-8-3.582-8-8zm-10-64c0-4.418 3.582-8 8-8s8 3.582 8 8-3.582 8-8 8-8-3.582-8-8zM14 43c0-4.418 3.582-8 8-8s8 3.582 8 8-3.582 8-8 8-8-3.582-8-8zm31 2c0-4.418 3.582-8 8-8s8 3.582 8 8-3.582 8-8 8-8-3.582-8-8z' fill='%23ffffff' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E&quot;); opacity: 0.1;"></div>
      <?php endif; ?>
      <div class="absolute inset-0 bg-gradient-to-t from-[#0f172a] via-transparent to-transparent"></div>
      
      <!-- Company Name on Banner -->
      <div class="absolute bottom-0 left-0 right-0 p-6 md:p-8 lg:p-12 mb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white mb-2 tracking-tight">
            <?= e($companyName) ?>
          </h1>
          <?php if (!empty($tagline)): ?>
            <p class="text-xl md:text-2xl text-white/80 font-medium"><?= e($tagline) ?></p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="-mt-20 md:-mt-24 relative z-10 pt-16">
      <!-- Company Info Card -->
      <div class="bg-white rounded-xl shadow-xl border border-gray-200 p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-start gap-6">
          <!-- Logo -->
          <div class="flex-shrink-0">
            <div class="w-32 h-32 md:w-40 md:h-40 bg-white rounded-2xl shadow-xl p-4 flex items-center justify-center border-4 border-white -mt-16 md:-mt-24 relative z-20">
              <?php if (!empty($companyData['logo_url'])): ?>
                <img src="<?= e($companyData['logo_url']) ?>" class="w-full h-full object-contain" alt="<?= e($companyName) ?> Logo">
              <?php else: ?>
                <div class="w-full h-full bg-gray-100 rounded-xl flex items-center justify-center text-4xl font-bold text-primary">
                  <?= strtoupper(substr($companyName, 0, 1)) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Company Info -->
          <div class="flex-1">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
              <div>
                <div class="flex items-center gap-3 mb-2">
                    <h2 class="text-3xl font-bold text-gray-900"><?= e($companyName) ?></h2>
                    <span class="px-2 py-0.5 bg-green-50 text-green-700 text-[10px] font-bold rounded uppercase tracking-wider border border-green-100">Verified</span>
                </div>
                
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-500 font-medium">
                  <?php if ($rating > 0): ?>
                  <div class="flex items-center gap-2">
                    <div class="flex text-yellow-400">
                      <?php for ($i = 1; $i <= 5; $i++): ?>
                        <svg class="w-4 h-4 <?= $i <= $rating ? 'fill-current' : 'text-gray-300 fill-current' ?>" viewBox="0 0 20 20">
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                      <?php endfor; ?>
                    </div>
                    <span class="text-gray-900 font-bold"><?= number_format($rating, 1) ?></span>
                    <span>(<?= number_format($reviews_count) ?> Reviews)</span>
                  </div>
                  <?php endif; ?>
                  
                  <div class="flex items-center gap-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                      <?= e($companyData['headquarters']) ?>
                  </div>

                  <div class="flex items-center gap-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                      <?= e($companyData['industry']) ?>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex items-center gap-3">
                <a href="/company/<?= e($companyData['slug']) ?>/jobs"
                   class="px-6 py-2.5 text-white rounded-xl font-bold shadow-lg hover:-translate-y-0.5 transition-all"
                   style="background-color:#f05537;">
                  See Jobs (<?= count($jobs) ?>)
                </a>

                <!-- Follow Button -->
                <div x-data="{ following: <?= $isFollowing ? 'true' : 'false' ?> }">
                  <button @click="following = !following"
                    class="px-6 py-2.5 border-2 rounded-xl font-bold transition-all"
                    :class="following ? 'bg-gray-100 border-gray-100 text-gray-700' : 'border-[#f05537] text-[#f05537] hover:bg-[#fff1ed]'"
                  >
                    <span x-text="following ? '✓ Following' : '+ Follow'"></span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-6">
      <section class="lg:col-span-2 space-y-6">

        <!-- Tabs -->
        <div class="bg-white p-4 rounded-xl shadow-sm sticky top-20 z-40 border border-gray-200">
          <nav class="flex flex-wrap gap-8 text-sm overflow-x-auto whitespace-nowrap px-2">
            <?php foreach ($tabs as $key => $label): ?>
              <?php
                $isActive = ($key === $activeTab);
                $class = $isActive
                  ? 'text-primary border-b-2 border-primary font-bold'
                  : 'text-gray-500 hover:text-primary font-medium';
              ?>
              <a href="<?= $baseUrl . '/' . $key ?>" class="<?= $class ?> pb-3 transition-colors">
                <?= e($label) ?>
              </a>
            <?php endforeach; ?>
          </nav>
        </div>

        <!-- SNAPSHOT TAB -->
        <?php if ($activeTab === 'snapshot'): ?>
          <article id="snapshot" class="space-y-6">

            <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div class="md:col-span-2">
                  <h2 class="text-2xl font-bold text-gray-900 mb-4">About <?= e($companyName) ?></h2>
                  <div class="text-gray-600 leading-relaxed prose max-w-none mb-8">
                    <?php 
                    $displayText = $aboutText;
                    if (empty($displayText)) {
                        $descCheck = $companyData['description'] ?? '';
                        if (is_string($descCheck) && (strpos($descCheck, '{') === 0 || strpos($descCheck, '[') === 0)) {
                            $jsonCheck = json_decode($descCheck, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $displayText = 'No description available.';
                            } else {
                                $displayText = $descCheck;
                            }
                        } else {
                            $displayText = $descCheck ?: 'No description available.';
                        }
                    }
                    ?>
                    <?= nl2br(e($displayText)) ?>
                  </div>

                  <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-1">
                      <div class="text-xs text-gray-400 font-bold uppercase tracking-wider">Industry</div>
                      <div class="font-bold text-gray-900"><?= e($companyData['industry']) ?></div>
                    </div>
                    <div class="space-y-1">
                      <div class="text-xs text-gray-400 font-bold uppercase tracking-wider">Headquarters</div>
                      <div class="font-bold text-gray-900"><?= e($companyData['headquarters']) ?></div>
                    </div>
                    <div class="space-y-1">
                      <div class="text-xs text-gray-400 font-bold uppercase tracking-wider">Founded</div>
                      <div class="font-bold text-gray-900"><?= e($companyData['founded_year']) ?></div>
                    </div>
                    <div class="space-y-1">
                      <div class="text-xs text-gray-400 font-bold uppercase tracking-wider">Website</div>
                      <div class="font-bold text-primary hover:underline">
                        <a href="<?= e($companyData['website']) ?>" target="_blank"><?= str_replace(['https://', 'http://', 'www.'], '', e($companyData['website'])) ?></a>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="md:col-span-1 border-l border-gray-100 pl-8">
                  <div class="flex flex-col items-center text-center">
                    <div class="w-28 h-28 rounded-2xl overflow-hidden shadow-lg border-4 border-white mb-4">
                      <img src="<?= e($companyData['ceo_photo']) ?>" alt="CEO" class="w-full h-full object-cover">
                    </div>
                    <div class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">CEO</div>
                    <div class="font-bold text-gray-900 text-lg"><?= e($companyData['ceo_name']) ?></div>
                    <div class="text-xs text-gray-500 mt-2 italic">"Empowering the future of technology through innovation."</div>
                  </div>
                </div>
              </div>
            </section>

            <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
              <h3 class="text-xl font-bold text-gray-900 mb-6">Culture & Benefits</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div>
                  <h4 class="text-lg font-bold text-gray-900 mb-4">Why join <?= e($companyName) ?>?</h4>
                  <ul class="space-y-4">
                    <?php foreach ($whyPoints as $point): ?>
                      <li class="flex items-start gap-3">
                          <span class="w-5 h-5 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 mt-0.5">
                              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                          </span>
                          <span class="text-gray-600 text-sm leading-relaxed"><?= e($point) ?></span>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>

                <div class="bg-gray-50 rounded-2xl p-6">
                  <h4 class="text-lg font-bold text-gray-900 mb-4 text-center">Reviews Overview</h4>
                  <div class="flex flex-col items-center mb-6">
                    <div class="text-5xl font-black text-gray-900 mb-2"><?= number_format($rating, 1) ?><span class="text-2xl text-yellow-400">★</span></div>
                    <div class="text-sm text-gray-500 font-medium">Average Rating from <?= $reviews_count ?> Employees</div>
                  </div>
                  <div class="space-y-4">
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs font-bold text-gray-500 uppercase">
                            <span>Work-Life Balance</span>
                            <span class="text-primary"><?= number_format($rating, 1) ?></span>
                        </div>
                        <div class="w-full bg-gray-200 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-primary h-full rounded-full" style="width: <?= ($rating/5)*100 ?>%"></div>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs font-bold text-gray-500 uppercase">
                            <span>Pay & Benefits</span>
                            <span class="text-primary"><?= number_format($rating, 1) ?></span>
                        </div>
                        <div class="w-full bg-gray-200 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-primary h-full rounded-full" style="width: <?= ($rating/5)*100 ?>%"></div>
                        </div>
                    </div>
                    <a href="<?= $baseUrl ?>/reviews" class="block w-full text-center py-2 text-sm font-bold text-primary hover:underline mt-4">
                      Read all employee reviews →
                    </a>
                  </div>
                </div>
              </div>
            </section>

          </article>
        <?php endif; ?>

        <!-- WHY TAB -->
        <?php if ($activeTab === 'why'): ?>
          <section id="why" class="bg-white rounded-lg p-6 shadow">
            <div class="mb-4">
              <h3 class="text-xl font-semibold">Why join <?= e($companyName) ?>?</h3>
              <p class="text-sm text-gray-600 mt-1">What makes working at <?= e($companyName) ?> great</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <?php foreach ($whyPoints as $point): ?>
                <div class="flex items-start gap-3 border rounded-lg p-4 bg-gray-50">
                  <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-green-100 text-green-600">✔</span>
                  <div class="text-sm text-gray-800">
                    <?= e($point) ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>

        <!-- REVIEWS TAB -->
        <?php if ($activeTab === 'reviews'): ?>
          <section id="reviews" class="bg-white rounded-lg p-6 shadow">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold">Reviews for <?= e($companyName) ?></h3>
              <span class="text-sm text-gray-500">Latest reviews</span>
            </div>

            <?php if ($loggedInCandidateId): ?>
            <div class="mb-6 border rounded-lg p-4 bg-gray-50">
              <div class="font-semibold mb-3">Write a review</div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm text-gray-700 mb-1">Title *</label>
                  <input id="r_title" type="text" class="w-full px-3 py-2 border rounded">
                </div>
                <div>
                  <label class="block text-sm text-gray-700 mb-1">Reviewer Name</label>
                  <input id="r_name" type="text" class="w-full px-3 py-2 border rounded">
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                <div>
                  <label class="block text-sm text-gray-700 mb-1">Rating (1–5) *</label>
                  <input id="r_rating" type="number" min="1" max="5" class="w-full px-3 py-2 border rounded">
                </div>
              </div>
              <div class="mt-3">
                <label class="block text-sm text-gray-700 mb-1">Review Text *</label>
                <textarea id="r_text" rows="3" class="w-full px-3 py-2 border rounded"></textarea>
              </div>
              <div class="mt-3">
                <button class="px-4 py-2 bg-primary text-white rounded" onclick="(async()=>{
                  const fd = new FormData();
                  fd.append('title', document.getElementById('r_title').value);
                  fd.append('reviewer_name', document.getElementById('r_name').value);
                  fd.append('rating', document.getElementById('r_rating').value);
                  fd.append('review_text', document.getElementById('r_text').value);
                  const res = await fetch('/company/<?= (int)$companyData['id'] ?>\/review', { method: 'POST', body: fd });
                  const data = await res.json();
                  if (res.ok && data.success) { alert('Review published'); window.location.reload(); } else { alert('Error: ' + (data.error||'Failed')); }
                })()">Publish Review</button>
              </div>
            </div>
            <?php else: ?>
            <div class="mb-6">
              <a class="text-sm text-primary" href="/login?redirect=<?= e('/company/' . $companyData['slug'] . '/reviews') ?>">Login to write a review</a>
            </div>
            <?php endif; ?>

            <?php if (empty($reviews) || !is_array($reviews)): ?>
              <div class="text-gray-600">No reviews yet.</div>
            <?php else: ?>
              <div class="space-y-4">
                <?php foreach ($reviews as $rev): ?>
                  <div class="border rounded p-4">
                    <div class="flex items-center justify-between">
                      <div class="font-semibold"><?= e($rev['title'] ?? 'Review') ?></div>
                      <div class="text-sm text-gray-500">
                        <?= !empty($rev['created_at']) ? date('M Y', strtotime($rev['created_at'])) : '' ?>
                      </div>
                    </div>
                    <div class="mt-1 text-xs text-gray-500">
                      By <?= e($rev['reviewer_name'] ?? 'Anonymous') ?> • Rating: <span class="text-green-600 font-semibold"><?= e((string)($rev['rating'] ?? 0)) ?>/5</span>
                    </div>
                    <p class="text-sm text-gray-700 mt-2">
                      <?= e($rev['review_text'] ?? '') ?>
                    </p>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </section>
        <?php endif; ?>

        <!-- SALARIES TAB -->
       

        <!-- JOBS TAB -->
        <?php if ($activeTab === 'jobs'): ?>
          <section id="jobs" class="bg-white rounded-lg p-6 shadow">
            <h3 class="text-lg font-semibold mb-4">Open positions at <?= e($companyName) ?></h3>

            <div class="mb-6 p-4 border rounded-lg flex gap-3 bg-gray-50 flex-wrap">
              <input type="text" placeholder="job title, keywords" class="flex-grow border px-3 py-2 rounded-md">
              <input type="text" placeholder="city or state" class="w-48 border px-3 py-2 rounded-md">
              <button class="px-4 py-2 bg-primary text-white rounded-md">Find Jobs</button>
            </div>

            <?php if (empty($jobs)): ?>
              <div class="text-gray-600">No job listings available from this company yet.</div>
            <?php else: ?>
              <ul class="space-y-4">
                <?php foreach ($jobs as $j): 
                  $jobId      = $j['job_id'] ?? ($j['id'] ?? '');
                  $jobSlug    = $j['slug'] ?? '';
                  $jobLink    = !empty($jobSlug) ? "/job/{$jobSlug}" : "/job/{$jobId}";
                  $jobTitle   = $j['title'] ?? 'Job Title';
                  $jobLocation= $j['location'] ?? ($j['city'] ?? 'Multiple');
                  $jobType    = $j['employment_type'] ?? ($j['employment'] ?? 'Full-time');
                  $jobSalary  = $j['salary'] ?? '';
                  $posted     = !empty($j['created_at']) ? date('M d, Y', strtotime($j['created_at'])) : '';
                ?>
                  <li class="border rounded p-4 flex items-start justify-between hover:shadow-md transition">
                    <div>
                      <a href="<?= e($jobLink) ?>"
                         class="text-lg font-semibold text-gray-900 hover:text-primary">
                        <?= e($jobTitle) ?>
                      </a>
                      <div class="text-sm text-gray-600 mt-1">
                        <?= e($jobLocation) ?> • <?= e($jobType) ?>
                      </div>
                      <div class="text-xs text-gray-400 mt-1">
                        Posted: <?= e($posted) ?>
                      </div>
                    </div>
                    <div class="text-right flex flex-col items-end">
                      <?php if ($jobSalary): ?>
                        <div class="text-sm text-green-600 font-semibold mb-2">
                          <?= e($jobSalary) ?>
                        </div>
                      <?php endif; ?>
                      <a href="<?= e($jobLink) ?>"
                         class="inline-block mt-1 px-3 py-1 bg-primary text-white rounded text-sm hover:bg-primary-600">
                        View Job
                      </a>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </section>
        <?php endif; ?>

        <!-- NEW BLOG SECTION TAB -->
        <?php if ($activeTab === 'blogs'): ?>
<section id="blogs" class="bg-white rounded-lg p-6 shadow">

  <div class="flex justify-between items-center mb-6">
    <h3 class="text-lg font-semibold">Blogs about <?= e($companyName) ?></h3>
    <a href="/company/<?= e($companyData['slug']) ?>/blogs"
       class="text-sm text-primary hover:underline">
      View all
    </a>
  </div>

  <?php if (empty($blogs)): ?>
    <div class="text-gray-600 border rounded-lg p-4 bg-gray-50 text-center">
      No blog posts published yet.
    </div>
  <?php else: ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <?php foreach ($blogs as $blog): ?>

        <article class="border rounded-lg overflow-hidden hover:shadow-lg transition bg-white">

          <!-- BLOG IMAGE -->
          <a href="/company/<?= e($companyData['slug']) ?>/blog/<?= e($blog['slug']) ?>">
            <img
              src="<?= !empty($blog['image']) ? e(fix_url($blog['image'])) : '/assets/images/blog-placeholder.jpg' ?>"
              class="w-full h-44 object-cover"
              alt="<?= e($blog['title']) ?>">
          </a>

          <!-- BLOG CONTENT -->
          <div class="p-4">
            <a href="/company/<?= e($companyData['slug']) ?>/blog/<?= e($blog['slug']) ?>">
              <h4 class="text-lg font-semibold hover:text-primary">
                <?= e($blog['title']) ?>
              </h4>
            </a>
            <p class="text-gray-500 text-xs mt-1">
              <?= !empty($blog['created_at']) ? date('F j, Y', strtotime($blog['created_at'])) : '' ?>
            </p>
            <p class="text-gray-600 text-sm mt-3 line-clamp-2">
              <?= e($blog['excerpt'] ?? '') ?>
            </p>
            <a href="/company/<?= e($companyData['slug']) ?>/blog/<?= e($blog['slug']) ?>"
               class="inline-block mt-4 text-sm text-primary font-medium hover:underline">
              Read more →
            </a>
          </div>

        </article>

      <?php endforeach; ?>
    </div>

  <?php endif; ?>

</section>
<?php endif; ?>

        

      </section>

      <!-- SIDEBAR -->
      <aside class="space-y-6 lg:col-span-1">

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
          <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6">Quick Stats</h4>
          <div class="space-y-6">
              <div class="flex items-center gap-4">
                  <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                  </div>
                  <div>
                      <div class="text-2xl font-bold text-gray-900"><?= count($jobs) ?></div>
                      <div class="text-xs text-gray-500 font-medium">Active Job Openings</div>
                  </div>
              </div>
              <div class="flex items-center gap-4">
                  <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"/></svg>
                  </div>
                  <div>
                      <div class="text-2xl font-bold text-gray-900"><?= e($companyData['founded_year']) ?></div>
                      <div class="text-xs text-gray-500 font-medium">Founded Year</div>
                  </div>
              </div>
              <div class="flex items-center gap-4">
                  <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                  </div>
                  <div>
                      <div class="text-2xl font-bold text-gray-900"><?= e($companyData['company_size']) ?></div>
                      <div class="text-xs text-gray-500 font-medium">Employee Count</div>
                  </div>
              </div>
          </div>
          <div class="mt-8 pt-6 border-t border-gray-50">
              <a href="<?= e($companyData['website']) ?>" target="_blank" class="flex items-center justify-center gap-2 w-full py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-black transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                  Official Website
              </a>
          </div>
        </div>

        <div class="bg-white p-5 rounded-lg shadow">
          <h4 class="font-semibold mb-2">Follow &amp; share</h4>
          <div class="flex gap-2 items-center flex-wrap">

            <!-- Sidebar Follow Button -->
            <div
              x-data="{
                following: <?= $isFollowing ? 'true' : 'false' ?>,
                toggleFollow() {
                  <?php if (!$loggedInCandidateId): ?>
                    window.location.href = '/login';
                    return;
                  <?php endif; ?>

                  fetch('/company/follow', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ company_id: <?= (int)$companyData['id'] ?> })
                  })
                  .then(res => res.json())
                  .then(data => {
                    if (!data) return;
                    this.following = (data.status === 'followed');
                  });
                }
              }"
            >
              <button
                @click="toggleFollow()"
                class="px-3 py-2 border rounded-md text-sm"
                :class="following ? 'bg-primary text-white' : ''"
              >
                <span x-text="following ? 'Following' : 'Follow'"></span>
              </button>
            </div>

            <div x-data="{ open: false }" class="relative">
              <button @click="open = !open" @click.outside="open = false" class="px-3 py-2 border rounded-md text-sm flex items-center gap-2 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                Share
              </button>
              <div x-show="open" x-transition.origin.top.right class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-xl z-50 border border-gray-100 py-1 overflow-hidden" style="display: none;">
                <div class="px-4 py-2 bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  Share this profile
                </div>
                <a href="https://api.whatsapp.com/send?text=Check out <?= urlencode($companyName) ?> on Jobsence: <?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="block px-4 py-3 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 flex items-center gap-3 transition-colors">
                  <span class="text-green-500 font-bold text-lg">WA</span> WhatsApp
                </a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="block px-4 py-3 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 flex items-center gap-3 transition-colors">
                  <span class="text-primary-600 font-bold text-lg">in</span> LinkedIn
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=Check out <?= urlencode($companyName) ?>" target="_blank" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-black flex items-center gap-3 transition-colors">
                  <span class="text-black font-bold text-lg">X</span> Twitter/X
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="block px-4 py-3 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary flex items-center gap-3 transition-colors">
                  <span class="text-primary font-bold text-lg">f</span> Facebook
                </a>
                <div class="border-t border-gray-100 mt-1 pt-1">
                    <button @click="navigator.clipboard.writeText(window.location.href); alert('Link copied to clipboard!'); open = false;" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3 transition-colors">
                      <span class="text-gray-500 font-bold text-lg">🔗</span> Copy Link
                    </button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </aside>
    </div>
  </main><br>
  <script>
    // Skeleton loading - remove after page load
    document.addEventListener('DOMContentLoaded', function() {
      // Hide any skeleton elements after content loads
      const skeletons = document.querySelectorAll('.skeleton');
      setTimeout(() => {
        skeletons.forEach(el => {
          el.classList.remove('skeleton');
        });
      }, 500);
    });
  </script>
     <?php
require __DIR__ . '/../include/footer.php';
?>
</body>
</html>











