<?php 
$base = $base ?? '/'; 
// Auto-detect base if default or root
if ($base === '/' || $base === '') {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    
    // CRITICAL FIX: If REQUEST_URI contains /public/, always set base to root
    if (strpos($requestUri, '/public/') !== false) {
        $base = '/';
    } else {
        // Normal detection from script name
        $scriptDir = dirname($scriptName);
        $base = rtrim($scriptDir, '/\\') . '/';
        
        // Remove /public/ from base if it exists
        if (strpos($base, '/public/') !== false) {
            $base = str_replace('/public/', '/', $base);
        }
    }
}
// Ensure base always ends with /
if (substr($base, -1) !== '/') {
    $base .= '/';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Social Services Jobs | Mindware Infotech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-gray-50 text-gray-800 font-sans">

<!-- ================= HEADER ================= -->
<header class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

        <a href="<?php echo $base; ?>" class="flex items-center gap-3">
            <img src="<?php echo $base; ?>uploads/Mindware-infotech.png" class="h-12 w-auto">
        </a>

        <nav class="hidden md:flex items-center gap-8">
            <a href="<?php echo $base; ?>" class="font-medium hover:text-[#5b6bd5]">Home</a>
            <a href="<?php echo rtrim($base, '/'); ?>/find-a-job" class="font-medium hover:text-[#5b6bd5]">Find a Job</a>
            <a href="<?php echo rtrim($base, '/'); ?>/createjob" class="font-medium hover:text-[#5b6bd5]">Create Job</a>
            <a href="<?php echo $base; ?>about" class="font-medium hover:text-[#5b6bd5]">About Us</a>
            <a href="<?php echo $base; ?>contact" class="font-medium hover:text-[#5b6bd5]">Contact Us</a>
        </nav>

        <div class="flex gap-4">
            <a href="/login"
               class="px-4 py-2 border rounded-md transition"
               style="border-color:#5b6bd5; color:#5b6bd5;"
               onmouseover="this.style.backgroundColor='#5b6bd51a'"
               onmouseout="this.style.backgroundColor='transparent'">
                Employer Login
            </a>

            <a href="/employers"
               class="px-4 py-2 rounded-md text-white transition"
               style="background-color:#5b6bd5;"
               onmouseover="this.style.backgroundColor='#4a59c8'"
               onmouseout="this.style.backgroundColor='#5b6bd5'">
               Jobsseekers
            </a>
        </div>

    </div>
</header>

<!-- ================= SEARCH SECTION ================= -->
<section class="bg-white py-14 border-b">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-extrabold text-gray-900">
            Find your career and your calling
        </h1>
        <p class="mt-3 text-gray-600">
            Jobs that create real social impact
        </p>

        <div class="mt-10 grid grid-cols-1 md:grid-cols-4 gap-4">
            <select class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#5b6bd5]">
                <option>Select Career Area</option>
                <option>Community Service</option>
                <option>Education</option>
                <option>Healthcare</option>
            </select>

            <input type="text"
                   placeholder="Enter location"
                   class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#5b6bd5]">

            <select class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#5b6bd5]">
                <option>Paid</option>
                <option>Unpaid</option>
                <option>Volunteer</option>
            </select>

            <button
                class="text-white px-6 py-3 rounded-lg transition"
                style="background-color:#5b6bd5;"
                onmouseover="this.style.backgroundColor='#4a59c8'"
                onmouseout="this.style.backgroundColor='#5b6bd5'">
                Search Jobs
            </button>
        </div>
    </div>
</section>
<section x-data="{ tab: 'mission' }" class="max-w-7xl mx-auto px-6 mt-16">

  <!-- Tabs -->
  <div class="flex gap-2 border-b border-gray-300">
  
  <button
    :class="tab === 'mission'
      ? 'bg-white border border-red-300 border-b-white text-red-500 font-semibold'
      : 'bg-gray-100 text-gray-400 border border-gray-300'"
    class="px-6 py-2 rounded-t-md transition-colors duration-200"
    @click="tab = 'mission'">
    MISSION FOCUS AREA
  </button>

  <button
    :class="tab === 'role'
      ? 'bg-white border border-red-300 border-b-white text-red-500 font-semibold'
      : 'bg-gray-100 text-gray-400 border border-gray-300'"
    class="px-6 py-2 rounded-t-md transition-colors duration-200"
    @click="tab = 'role'">
    ROLE CATEGORY
  </button>

</div>


  <!-- Content -->
  <div class="bg-white border border-gray-300 rounded-b-md p-8 mt-0">

    <!-- Mission Content -->
    <div x-show="tab === 'mission'" class="grid grid-cols-1 md:grid-cols-3 gap-8 text-sm text-black" x-cloak>
      <div class="space-y-3">
        <p>Aging / Seniors</p>
        <p>Alternative & Sustainable Energy</p>
        <p>Animal-Related</p>
        <p>Arts, Culture & Humanities</p>
        <p>Association / Mutual & Membership Benefit / Union</p>
      </div>
      <div class="space-y-3">
        <p>Broadcast / Journalism</p>
        <p>Childcare / Preschool / After-school Care</p>
        <p>Civil Rights, Social Action & Advocacy</p>
        <p>Community Improvement & Capacity Building</p>
        <p>Conservation / Environment Advocacy</p>
      </div>
      <div class="space-y-3">
        <p>Crime & Legal-Related</p>
        <p>Disability Related</p>
        <p>Disease & Medical Disorder Related</p>
        <p>Education</p>
        <p>Employment</p>
      </div>
    </div>

    <!-- Role Content (subset only) -->
    <div x-show="tab === 'role'" class="grid grid-cols-1 md:grid-cols-3 gap-8 text-sm text-black" x-cloak>
      <div class="space-y-3">
        <p>Accounting / Finance</p>
        <p>Administrative / Clerical</p>
        <p>Advocacy / Lobbying</p>
        <p>Animal Care</p>
         <p>Event Planning</p>

      </div>
      <div class="space-y-3">
        <p>Campaign Management / Canvassing / Field Organizer</p>
        <p>Child Care / After school / Counselor / Mentor</p>
        <p>Community Engagement</p>
        <p>Conservation</p>
        <p>Education / Teaching</p>
      </div>
      <div class="space-y-3">
        <p>Consulting</p>
        <p>Creative / Art Production</p>
        <p>Customer Service / Retail</p>
        <p>Development / Fundraising</p>
        <p>Direct Service / Social Service</p>
      </div>
    </div>

    <!-- View all link (go to new page) -->
    <div class="text-right mt-6 text-sm text-red-500 cursor-pointer">
      <a href="/roles" class="hover:underline">View all</a>
    </div>

  </div>
</section>

<!-- ================= ABOUT ================= -->
<section class="mt-12 mb-12 px-4">
    <div class="max-w-7xl mx-auto bg-gray-100 rounded-3xl px-10 py-14 grid md:grid-cols-2 gap-12 items-center">

        <div>
            <h2 class="text-4xl font-bold" style="color:#5b6bd5;">
                About Social Services
            </h2>

            <p class="mt-5 text-gray-700 max-w-lg">
                Mindware connects people with meaningful careers in NGOs,
                healthcare, education, and social development.
            </p>

            <div class="bg-[#5b6bd5]/10 border-l-4 border-[#5b6bd5] p-4 rounded-md">
                Your work can create impact while building a meaningful career.
            </div>
        </div>

        <div class="flex justify-center">
            <img src="https://images.unsplash.com/photo-1521791136064-7986c2920216"
                 class="w-80 rounded-xl">
        </div>
    </div>
</section>
<?php
$featuredJobs = [
    [   'title'   => 'Chief Program Officer',
        'company' => 'Rising Sun Foundation',
        'location'=> 'New Delhi, India',
        'salary'  => '₹1.2 Cr – ₹1.6 Cr per annum',
        'image' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216'
    ],
    [
        
        'title'   => 'Program Director – Education',
        'company' => 'Teach For India',
        'location'=> 'Mumbai, Maharashtra, India',
        'salary'  => '₹45 LPA – ₹60 LPA',
        'image'   => 'https://images.unsplash.com/photo-1521791136064-7986c2920216'
    ],
    [
        'title'   => 'Grants & Partnerships Manager',
        'company' => 'Care India',
        'location'=> 'Gurugram, Haryana, India',
        'salary'  => '₹28 LPA – ₹35 LPA',
        'image'   => 'https://images.unsplash.com/photo-1521791136064-7986c2920216'
    ],
];
?><section class="py-14 bg-white">
  <div class="max-w-7xl mx-auto px-6">

    <div class="grid grid-cols-12 gap-0">

      <!-- ================= LEFT : FEATURED EMPLOYERS (9/12) ================= -->
      <div class="col-span-9 bg-gray-50 rounded-l-2xl px-8 py-8">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-base font-semibold text-gray-900">
            Featured employers
          </h2>
          <a href="#" class="text-sm text-gray-500 hover:text-gray-700">
            View all employers
          </a>
        </div>

        <!-- Logos -->
        <div class="flex items-center gap-6">

          <div class="bg-white rounded-lg p-3 w-28 h-16 flex items-center justify-center shadow-sm">
            <img src="https://www.mindwareinfotech.com/storage/uploads/job-categories/693d42cddd8f2_Information_Technology.avif"
                 class="max-h-12 object-contain" alt="">
          </div>

          <div class="bg-white rounded-lg p-3 w-28 h-16 flex items-center justify-center shadow-sm">
            <img src="https://www.mindwareinfotech.com/storage/uploads/job-categories/6942978eb7c04_delivery.webp"
                 class="max-h-12 object-contain" alt="">
          </div>

          <div class="bg-white rounded-lg p-3 w-28 h-16 flex items-center justify-center shadow-sm">
            <img src="https://www.mindwareinfotech.com/storage/uploads/job-categories/693d2cea48e71_driver.webp"
                 class="max-h-12 object-contain" alt="">
          </div>

          <div class="bg-white rounded-lg p-3 w-28 h-16 flex items-center justify-center shadow-sm">
            <img src="https://www.mindwareinfotech.com/storage/uploads/job-categories/694291a484238_banking-service-online-app.webp"
                 class="max-h-12 object-contain" alt="">
          </div>

        </div>
      </div>

      <!-- ================= RIGHT : QUICK SEARCH (3/12) ================= -->
      <aside class="col-span-3 bg-white rounded-r-2xl">

        <!-- FULL AREA CLICKABLE (NO HOVER EFFECT) -->
        <a href="<?php echo $base; ?>find-a-job"
           class="block h-full px-8 py-8 bg-white cursor-pointer">

          <!-- Header -->
          <div class="flex justify-between items-center mb-5">
            <h3 class="text-base font-semibold text-gray-900">
              Quick search
            </h3>
            <span class="text-sm text-gray-500">
              More jobs
            </span>
          </div>

          <!-- List -->
          <ul class="space-y-3 text-sm text-gray-700">
            <li class="flex items-center gap-2">
              <span class="text-red-500 text-xs">●</span> DC Nonprofit Jobs
            </li>
            <li class="flex items-center gap-2">
              <span class="text-red-500 text-xs">●</span> Tri-state Nonprofit Jobs
            </li>
            <li class="flex items-center gap-2">
              <span class="text-red-500 text-xs">●</span> Los Angeles Nonprofit Jobs
            </li>
            <li class="flex items-center gap-2">
              <span class="text-red-500 text-xs">●</span> Bay Area Nonprofit Jobs
            </li>
          </ul>

        </a>

      </aside>

    </div>

  </div>
</section>




<!-- ================= FOOTER ================= -->
<footer class="bg-white border-t border-gray-200 mt-20">
    <div class="max-w-7xl mx-auto px-6 py-10 text-sm text-gray-600 flex justify-between">
        <p>© <?= date('Y'); ?> Mindware Infotech. All Rights Reserved.</p>
        <div class="flex gap-4">
            <a href="<?php echo $base; ?>terms" class="hover:text-[#5b6bd5]">Terms</a>
            <a href="<?php echo $base; ?>privacy" class="hover:text-[#5b6bd5]">Privacy</a>
            <a href="<?php echo $base; ?>contact" class="hover:text-[#5b6bd5]">Contact</a>
        </div>
    </div>
</footer>

</body>
</html>
