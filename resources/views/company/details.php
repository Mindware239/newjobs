<?php
// resources/views/company/details.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$company = $company ?? [];
$jobs = $jobs ?? [];

// Helpers for safe access
if (!function_exists('e')) {
    function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES); }
}

// Use an associative array for company data for easier structure mapping
$companyData = [
    'logo_url'      => !empty($company['logo_url']) ? $company['logo_url'] : 'https://plus.unsplash.com/premium_photo-1667354097023-4b8d9c3f7767?q=80&w=726&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
    'banner_url'    => !empty($company['banner_url']) ? $company['banner_url'] : 'https://plus.unsplash.com/premium_photo-1661963103403-32d25927f577?q=80&w=1194&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
    'ceo_photo'     => !empty($company['ceo_photo']) ? $company['ceo_photo'] : '/assets/images/ceo-placeholder.jpg',
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
    <div class="h-64 md:h-80 lg:h-96 overflow-hidden bg-gradient-to-r from-blue-600 to-blue-500 relative">
      <?php if (!empty($companyData['banner_url']) && $companyData['banner_url'] !== 'https://plus.unsplash.com/premium_photo-1661963103403-32d25927f577?q=80&w=1194&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'): ?>
        <img src="<?= e($companyData['banner_url']) ?>" alt="Banner" class="w-full h-full object-cover" loading="eager">
      <?php else: ?>
        <!-- Gradient Banner Background -->
        <div class="w-full h-full bg-gradient-to-r from-blue-600 via-blue-500 to-blue-400"></div>
      <?php endif; ?>
      <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-black/70"></div>
      
      <!-- Company Name on Banner -->
      <div class="absolute bottom-0 left-0 right-0 p-6 md:p-8 lg:p-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white mb-2 drop-shadow-lg">
            <?= e($companyName) ?>
          </h1>
          <?php if (!empty($tagline)): ?>
            <p class="text-xl md:text-2xl text-white/90 font-medium drop-shadow-md"><?= e($tagline) ?></p>
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
            <div class="w-32 h-32 md:w-40 md:h-40 bg-white rounded-xl shadow-lg p-3 flex items-center justify-center border-2 border-white">
              <?php if (!empty($companyData['logo_url'])): ?>
                <img src="<?= e($companyData['logo_url']) ?>" class="w-full h-full object-contain rounded-md" alt="<?= e($companyName) ?> Logo">
              <?php else: ?>
                <div class="w-full h-full bg-gray-200 rounded-md flex items-center justify-center text-4xl font-bold text-gray-400">
                  <?= strtoupper(substr($companyName, 0, 2)) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Company Info -->
          <div class="flex-1">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
              <div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2"><?= e($companyName) ?></h2>
                
                <div class="flex flex-wrap items-center gap-4 mb-4">
                  <?php if ($rating > 0): ?>
                  <div class="flex items-center gap-2">
                    <span class="text-lg font-bold text-green-600"><?= number_format($rating, 1) ?></span>
                    <div class="flex">
                      <?php for ($i = 1; $i <= 5; $i++): ?>
                        <svg class="w-4 h-4 <?= $i <= $rating ? 'text-yellow-400' : 'text-gray-300' ?>" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                      <?php endfor; ?>
                    </div>
                    <span class="text-sm text-gray-600">(<?= number_format($reviews_count) ?> reviews)</span>
                  </div>
                  <?php endif; ?>
                  
                  <?php 
                    $followersCount = \App\Models\CompanyFollower::countFollowers($companyData['id']);
                  ?>
                  <?php if ($followersCount > 0): ?>
                  <div class="text-sm text-gray-600">
                    <?= number_format($followersCount) ?> followers
                  </div>
                  <?php endif; ?>
                  
                  <?php if (!empty($companyData['industry'])): ?>
                  <div class="flex flex-wrap gap-2">
                    <?php 
                      $industries = is_array($companyData['industry']) ? $companyData['industry'] : explode(',', $companyData['industry']);
                      foreach (array_slice($industries, 0, 3) as $ind):
                    ?>
                      <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">
                        <?= e(trim($ind)) ?>
                      </span>
                    <?php endforeach; ?>
                  </div>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex flex-wrap gap-3">
                <a href="/company/<?= e($companyData['slug']) ?>/jobs"
                   class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm hover:shadow-md text-gray-700 font-medium transition">
                  See jobs (<?= count($jobs) ?>)
                </a>

                <?php if (!empty($companyData['website'])): ?>
                <a href="<?= e($companyData['website']) ?>" target="_blank"
                   class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium transition">
                  Visit website
                </a>
                <?php endif; ?>

                <!-- Follow Button -->
                <div
                  x-data="{
                    following: <?= $isFollowing ? 'true' : 'false' ?>,
                    toggleFollow() {
                      <?php if (!$loggedInCandidateId): ?>
                        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                        return;
                      <?php endif; ?>

                      fetch('/company/follow', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ company_id: <?= (int)$companyData['id'] ?> })
                      })
                      .then(res => {
                        const type = res.headers.get('content-type') || '';
                        if (!type.includes('application/json')) {
                          window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                          return;
                        }
                        return res.json();
                      })
                      .then(data => {
                        if (!data) return;
                        this.following = (data.status === 'followed');
                      });
                    }
                  }"
                >
                  <button
                    class="px-4 py-2 border rounded-md font-medium transition"
                    :class="following ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                    @click="toggleFollow()"
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
        <div class="bg-white p-4 rounded-lg shadow-sm sticky top-16 z-40 border-b border-gray-200">
          <nav class="flex flex-wrap gap-6 text-sm overflow-x-auto whitespace-nowrap">
            <?php foreach ($tabs as $key => $label): ?>
              <?php
                $isActive = ($key === $activeTab);
                $class = $isActive
                  ? 'text-blue-600 border-b-2 border-blue-600 font-semibold'
                  : 'text-gray-600 hover:text-blue-500';
              ?>
              <a href="<?= $baseUrl . '/' . $key ?>" class="<?= $class ?> pb-2">
                <?= e($label) ?>
              </a>
            <?php endforeach; ?>
          </nav>
        </div>

        <!-- SNAPSHOT TAB -->
        <?php if ($activeTab === 'snapshot'): ?>
          <article id="snapshot" class="bg-white rounded-lg shadow space-y-8">

            <section class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1">
                  <div class="h-full flex flex-col items-center">
                    <div class="w-32 h-32 rounded-lg overflow-hidden shadow-sm">
                      <img src="<?= e($companyData['ceo_photo']) ?>" alt="CEO" class="w-full h-full object-cover">
                    </div>
                    <div class="text-center mt-3">
                      <div class="text-sm text-gray-500">CEO</div>
                      <div class="font-semibold mt-1"><?= e($companyData['ceo_name']) ?></div>
                    </div>
                  </div>
                </div>

                <div class="md:col-span-2">
                  <h2 class="text-xl font-bold mb-3">About <?= e($companyName) ?></h2>
                  <div class="text-sm text-gray-700 leading-relaxed prose max-w-none">
                    <?php 
                    $displayText = $aboutText;
                    if (empty($displayText)) {
                        // If description is JSON, don't display it - show placeholder
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

                  <div class="grid grid-cols-2 gap-4 mt-6">
                    <div class="p-4 border rounded-lg">
                      <div class="text-xs text-gray-500">Industry</div>
                      <div class="font-semibold"><?= e($companyData['industry']) ?></div>
                    </div>
                    <div class="p-4 border rounded-lg">
                      <div class="text-xs text-gray-500">Headquarters</div>
                      <div class="font-semibold"><?= e($companyData['headquarters']) ?></div>
                    </div>
                    <div class="p-4 border rounded-lg">
                      <div class="text-xs text-gray-500">Founded</div>
                      <div class="font-semibold"><?= e($companyData['founded_year']) ?></div>
                    </div>
                    <div class="p-4 border rounded-lg">
                      <div class="text-xs text-gray-500">Company size</div>
                      <div class="font-semibold"><?= e($companyData['company_size']) ?></div>
                    </div>
                    <div class="p-4 border rounded-lg">
                      <div class="text-xs text-gray-500">Revenue</div>
                      <div class="font-semibold"><?= e($companyData['revenue']) ?></div>
                    </div>
                    <div class="p-4 border rounded-lg">
                      <div class="text-xs text-gray-500">Website</div>
                      <div class="font-semibold">
                        <a class="text-blue-600" href="<?= e($companyData['website']) ?>" target="_blank">
                          <?= e($companyData['website']) ?>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <hr class="border-gray-100 mx-6">

            <section class="p-6 pt-0">
              <h3 class="text-xl font-bold mb-4">Culture and Benefits</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <h4 class="text-lg font-semibold mb-3">Why join <?= e($companyName) ?>?</h4>
                  <ul class="space-y-3 text-sm text-gray-700 list-disc pl-5">
                    <?php foreach ($whyPoints as $point): ?>
                      <li><?= e($point) ?></li>
                    <?php endforeach; ?>
                  </ul>
                  <a href="/company/<?= e($companyData['slug']) ?>/why"
                     class="inline-block mt-4 text-sm text-blue-600 hover:underline">
                    See full benefits page &rarr;
                  </a>
                </div>

                <div>
                  <h4 class="text-lg font-semibold mb-3">Overall Reviews Summary</h4>
                  <div class="mb-4 text-center">
                    <div class="text-sm text-gray-500">Company Rating</div>
                    <div class="text-4xl font-extrabold text-blue-600 mt-1"><?= number_format($rating, 1) ?>★</div>
                    <div class="text-sm text-gray-500 mt-1">Based on <?= $reviews_count ?> reviews</div>
                  </div>
                  <div class="text-sm space-y-2">
                    <div class="flex justify-between border-b pb-1">
                      <span>Work-life balance:</span>
                      <span class="font-medium text-blue-600"><?= number_format($rating, 1) ?> ★</span>
                    </div>
                    <div class="flex justify-between border-b pb-1">
                      <span>Pay and benefits:</span>
                      <span class="font-medium text-blue-600"><?= number_format($rating, 1) ?> ★</span>
                    </div>
                    <a href="/company/<?= e($companyData['slug']) ?>/reviews" class="inline-block mt-2 text-sm text-blue-600 hover:underline">
                      Read all reviews &rarr;
                    </a>
                  </div>
                </div>
              </div>
            </section>

            <hr class="border-gray-100 mx-6">

            

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
                <button class="px-4 py-2 bg-blue-600 text-white rounded" onclick="(async()=>{
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
              <a class="text-sm text-blue-600" href="/login?redirect=<?= e('/company/' . $companyData['slug'] . '/reviews') ?>">Login to write a review</a>
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
              <button class="px-4 py-2 bg-blue-600 text-white rounded-md">Find Jobs</button>
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
                         class="text-lg font-semibold text-gray-900 hover:text-blue-600">
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
                         class="inline-block mt-1 px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
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
       class="text-sm text-blue-600 hover:underline">
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
          <a href="/blog/<?= e($blog['slug']) ?>">
            <img
              src="<?= !empty($blog['image']) ? e($blog['image']) : '/assets/images/blog-placeholder.jpg' ?>"
              class="w-full h-44 object-cover"
              alt="<?= e($blog['title']) ?>">
          </a>

          <!-- BLOG CONTENT -->
          <div class="p-4">
            <a href="/blog/<?= e($blog['slug']) ?>">
              <h4 class="text-lg font-semibold hover:text-blue-600">
                <?= e($blog['title']) ?>
              </h4>
            </a>

            <div class="text-xs text-gray-500 mt-1">
              <?= date('F d, Y', strtotime($blog['created_at'])) ?>
            </div>

            <p class="text-sm text-gray-700 mt-2 line-clamp-3">
              <?= e($blog['excerpt']) ?>
            </p>

            <a href="/blog/<?= e($blog['slug']) ?>"
               class="inline-block mt-3 text-sm text-blue-600 hover:underline">
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

        <div class="bg-white p-5 rounded-lg shadow text-center">
          <div class="text-sm text-gray-500">Company rating</div>
          <div class="text-3xl font-bold text-green-600 mt-2"><?= number_format($rating, 1) ?>/5</div>
          <div class="text-sm text-gray-500 mt-1"><?= $reviews_count ?> reviews</div>

          <div class="w-full bg-gray-100 rounded-full h-2 mt-4 overflow-hidden">
            <div class="h-2 bg-green-600" style="width: <?= max(0, min(100, (int)($rating * 20))) ?>%"></div>
          </div>
        </div>

        <div class="bg-white p-5 rounded-lg shadow">
          <h4 class="font-semibold mb-2">Quick stats</h4>
          <div class="text-sm text-gray-600">Jobs posted: <?= count($jobs) ?></div>
          <div class="text-sm text-gray-600">Founded: <?= e($companyData['founded_year']) ?></div>
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
                :class="following ? 'bg-blue-600 text-white' : ''"
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
                <a href="https://api.whatsapp.com/send?text=Check out <?= urlencode($companyName) ?> on Mindware: <?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="block px-4 py-3 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 flex items-center gap-3 transition-colors">
                  <span class="text-green-500 font-bold text-lg">WA</span> WhatsApp
                </a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="block px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center gap-3 transition-colors">
                  <span class="text-blue-700 font-bold text-lg">in</span> LinkedIn
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=Check out <?= urlencode($companyName) ?>" target="_blank" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-black flex items-center gap-3 transition-colors">
                  <span class="text-black font-bold text-lg">X</span> Twitter/X
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="block px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 flex items-center gap-3 transition-colors">
                  <span class="text-blue-600 font-bold text-lg">f</span> Facebook
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
