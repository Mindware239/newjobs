<?php $base = '/'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Candidate Login | Mindware Infotech</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col text-gray-800">

<header x-data="{ mobileMenu:false }" class="w-full bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50">

<div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 h-20 md:h-24 flex items-center justify-between">

<div class="flex-shrink-0">
<a href="<?= $base ?>">
<img src="<?= $base ?>uploads/Mindware-infotech.png"
class="h-10 sm:h-12 md:h-14 lg:h-16 w-auto">
</a>
</div>

<div class="hidden min-[900px]:flex items-center gap-8">

<div class="text-[14px]">
<span class="text-black font-medium mr-2">Candidates & Job Seekers:</span>

<a href="<?= $base ?>social-services/login"
class="text-red-400 font-semibold hover:underline">
Login/CreateAccount
</a>
</div>

<a href="<?= $base ?>employers"
class="bg-red-400 text-white px-7 py-3 rounded-[4px] text-[13px] font-bold uppercase hover:bg-white hover:border-red-400 border-2 hover:text-red-400 transition">
Employers
</a>

</div>

<button @click="mobileMenu = !mobileMenu"
class="min-[900px]:hidden p-2 text-[#333]">
<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path x-show="!mobileMenu" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
<path x-show="mobileMenu" x-cloak stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
</svg>
</button>

</div>

<nav class="hidden min-[900px]:block border-t border-gray-50">
<div class="max-w-[1140px] mx-auto px-4 sm:px-6 md:px-10 lg:px-12 flex gap-10">

<?php
$current_page = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), "/");
if ($current_page === '') $current_page = 'index';

$navItems = [
['label'=>'Home','url'=>'index'],
['label'=>'Find a job','url'=>'find-a-job'],
['label'=>'Create job alerts','url'=>'social-services/login'],
['label'=>'Search Employers','url'=>'searchEmployers'],
['label'=>'Career Insight','url'=>'hiringInsight'],
['label'=>'About Us','url'=>'aboutus'],
['label'=>'Support','url'=>'supports'],
];

foreach($navItems as $item):
$isActive = ($current_page === $item['url']);
$class = $isActive
? 'text-[#e15f55] border-b-2 border-[#e15f55]'
: 'text-black border-b-2 border-transparent hover:text-[#e15f55] hover:border-[#e15f55]';
?>

<a href="<?= $base.$item['url'] ?>"
class="py-4 text-[15px] font-semibold transition-all duration-300 <?= $class ?>">
<?= $item['label'] ?>
</a>

<?php endforeach; ?>
</div>
</nav>
</header>


<!-- ================= MAIN ================= -->
<main class="flex-1 flex items-center justify-center px-4">

<div class="w-full max-w-3xl"
x-data="{ mode: 'login', forgot:false }">

<!-- LOGIN -->
<div x-show="mode==='login'" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">

<form method="POST" action="/social-services/login" class="space-y-4">

<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

<label class="block text-sm font-medium">Email *</label>
<input type="email" name="email" required
class="w-full border border-gray-300 rounded-md p-2.5">

<label class="block text-sm font-medium">Password *</label>
<input type="password" name="password" required
class="w-full border border-gray-300 rounded-md p-2.5">

<div class="flex justify-between text-sm">

<button type="submit"
class="bg-[#5b6bd5] text-white px-6 py-2.5 rounded-md">
Login
</button>

<div class="flex flex-col items-end gap-1">

<button type="button" @click="mode='register'"
class="text-[#5b6bd5] hover:underline">
Create account
</button>

<button type="button" @click="forgot=true"
class="text-red-400 hover:underline text-xs">
Forgot password?
</button>

</div>

</div>
</form>
</div>


<!-- REGISTER -->
<div x-show="mode==='register'" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">

<form method="POST" action="/social-services/register" x-data="{ role: '' }" @change="role = $event.target.value">

<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
<input type="hidden" name="redirect" :value="role==='social_employer' ? '/social-employer/account' : (role==='social_candidate' ? '/social-candidate/accountcandidate' : '')">

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

<div>
<label class="block text-sm font-medium">Email *</label>
<input type="email" name="email" required
class="w-full border border-gray-300 rounded-md p-2.5">
</div>

<div>
<label class="block text-sm font-medium">Password *</label>
<input type="password" name="password" required
class="w-full border border-gray-300 rounded-md p-2.5">
</div>

</div>

<div class="mt-4">
<select name="role" required x-model="role"
class="w-full border border-gray-300 rounded-md p-2.5">
<option value="">Select role</option>
<option value="social_candidate">Social Candidate</option>
<option value="social_employer">Social Employer</option>
</select>
</div>

<div class="mt-6 flex justify-between">
<button type="submit"
class="bg-[#5b6bd5] text-white px-6 py-2.5 rounded-md">
Create account
</button>

<button type="button" @click="mode='login'"
class="text-[#5b6bd5] hover:underline">
Login
</button>
</div>

</form>
</div>


<!-- FORGOT PASSWORD MODAL -->
<div x-show="forgot" x-transition
class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">

<div @click.outside="forgot=false"
class="bg-white w-full max-w-md rounded-lg p-6">

<h2 class="text-lg font-bold mb-4">Reset Password</h2>

<form method="POST" action="/social-services/forgot-password" class="space-y-4">

<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

<input type="email" name="email" required placeholder="Enter your email"
class="w-full border border-gray-300 rounded-md p-2.5">

<div class="flex justify-end gap-3">

<button type="button" @click="forgot=false"
class="px-4 py-2 border rounded">
Cancel
</button>

<button type="submit"
class="px-6 py-2 bg-[#5b6bd5] text-white rounded">
Send Reset Link
</button>

</div>

</form>
</div>
</div>

</div>
</main>
</body>
</html>
