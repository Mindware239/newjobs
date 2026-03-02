<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Candidate Login | Mindware Infotech</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-white text-gray-800 min-h-screen">
<!-- ================= HEADER (WORKFORGOOD STYLE) ================= -->
<header>

  <!-- TOP WHITE BAR -->
  <div style="
    background:#ffffff;
    border-bottom:1px solid #e5e5e5;
  ">
    <div style="
      max-width:1200px;
      margin:0 auto;
      padding:18px 20px;
      display:flex;
      align-items:center;
      justify-content:space-between;
    ">

      <!-- LOGO -->
      <a href="<?= $base ?>" style="text-decoration:none;">
        <img src="<?= $base ?>uploads/Mindware-infotech.png"
             alt="Mindware Infotech"
             style="height:46px;">
      </a>

      <!-- EMPLOYER LOGIN -->
      <a href="<?= $base ?>login"
         style="
           border:1px solid #4f63ff;
           color:#4f63ff;
           padding:8px 16px;
           font-size:14px;
           text-decoration:none;
           border-radius:6px;
         ">
        Employer Login
      </a>

    </div>
  </div>

  <!-- BLACK NAV BAR -->
  <div style="background:#000000;">
    <div style="
      max-width:1200px;
      margin:0 auto;
      padding:12px 20px;
      display:flex;
      gap:28px;
      align-items:center;
      color:#ffffff;
      font-size:14px;
    ">

      <a href="<?= $base ?>" style="color:#fff;text-decoration:none;">
        ⬅ Back to Home
      </a>
<nav class="flex gap-6 text-sm text-gray-700">

<?php
$current_page = basename($_SERVER['PHP_SELF'] ?? '', ".php");

$navItems = [
    ['label' => 'Home', 'url' => '/index'],
    ['label' => 'Find a job', 'url' => '/find-a-job'],
    ['label' => 'Create job alerts', 'url' => '/newsubscriptions'],
    ['label' => 'Search Employers', 'url' => '/searchEmployers'],
    ['label' => 'Career Insight', 'url' => '/hiringInsight'],
    ['label' => 'About Us', 'url' => '/aboutus'],
    ['label' => 'Support', 'url' => '/supports'],
];

foreach ($navItems as $item):

    $page = ltrim($item['url'], '/');
    $isActive = ($current_page === $page);

    $class = $isActive
        ? 'text-red-500'
        : 'text-gray-700 hover:text-red-500';
?>

    <a href="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>"
       class="<?= $class; ?>">
       <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
    </a>

<?php endforeach; ?>

</nav>


    </div>
  </div>

</header>


<!-- ================= MAIN ================= -->
<main class="max-w-6xl mx-auto px-6 py-20"
      x-data="{ view:'code', show:false }">

<div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-start">

  <!-- ================= LEFT : EMAIL (ALWAYS VISIBLE) ================= -->
  <div>
    <label class="block text-sm font-medium mb-1">
      Your email <span class="text-red-500">*</span>
    </label>

    <input type="email"
           placeholder="Enter email address"
           class="w-full border border-gray-300 rounded-md px-3 py-2">

    <!-- Helper text ONLY for create -->
    <template x-if="view==='register'">
      <p class="text-xs text-gray-500 mt-1">
        You will need to verify your email address. Be sure you can access it!
      </p>
    </template>

    <div class="mt-3">
      <a href="#"
         x-show="view!=='register'"
         @click.prevent="view='register'"
         class="text-sm text-red-500 hover:underline">
        Create account
      </a>

      <a href="#"
         x-show="view==='register'"
         @click.prevent="view='code'"
         class="text-sm text-red-500 hover:underline">
        ◯ Login
      </a>
    </div>
  </div>

  <!-- ================= RIGHT PANEL ================= -->
  <div>

    <!-- ===== LOGIN WITH CODE ===== -->
    <template x-if="view==='code'">
      <div>
        <button class="w-full bg-[#efb0a9] text-white py-2.5 rounded-md">
          Send me a login code
        </button>

        <div class="flex justify-between mt-3 text-sm text-red-500">
          <a href="#">I already have a code</a>
          <a href="#" @click.prevent="view='password'">Use password</a>
        </div>
      </div>
    </template>

    <!-- ===== LOGIN WITH PASSWORD ===== -->
    <template x-if="view==='password'">
      <div>
        <label class="block text-sm font-medium mb-1">
          Your password
        </label>

        <div class="relative">
          <input :type="show ? 'text' : 'password'"
                 placeholder="********"
                 class="w-full border border-gray-300 rounded-md px-3 py-2 pr-10">
          <span class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer"
                @click="show=!show">👁</span>
        </div>

        <button class="w-full bg-[#efb0a9] text-white py-2.5 rounded-md mt-4">
          Login
        </button>

        <div class="flex justify-between mt-3 text-sm text-red-500">
          <a href="#" @click.prevent="view='code'">← Back</a>
          <a href="#">Forgot password? Request code</a>
        </div>
      </div>
    </template>

    <!-- ===== CREATE ACCOUNT ===== -->
    <template x-if="view==='register'">
      <div>
        <label class="block text-sm font-medium mb-1">
          Create a password <span class="text-red-500">*</span>
        </label>

        <p class="text-xs text-gray-500 mb-2">
          Min. length 8 characters with uppercase, lowercase, number & special character.
        </p>

        <div class="relative">
          <input type="password"
                 placeholder="********"
                 class="w-full border border-gray-300 rounded-md px-3 py-2 pr-10">
          <span class="absolute right-3 top-1/2 -translate-y-1/2">👁</span>
        </div>

        <button class="w-full bg-[#efb0a9] text-white py-2.5 rounded-md mt-4">
          Create account
        </button>
      </div>
    </template>

  </div>

</div>
</main>

</body>
</html>
