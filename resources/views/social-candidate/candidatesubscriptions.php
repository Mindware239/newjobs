<?php $base = $base ?? '/'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Job Alerts</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
[x-cloak]{display:none}
</style>
</head>

<body class="bg-white min-h-screen text-gray-800"
x-data="{ showForm:false, payTerm:'' }">
<header class="bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

    <!-- LOGO (YOUR IMAGE) -->
    <a href="<?= $base ?>" class="flex items-center gap-3 shrink-0">
      <img src="<?= $base ?>uploads/Mindware-infotech.png"
           class="h-10 sm:h-12 w-auto"
           alt="Mindware Infotech">
    </a>

    <!-- TOP NAV -->
 <nav class="hidden min-[900px]:flex items-center gap-4 lg:gap-8 text-base font-medium">
        <a href="/candidatelisting" class="hover:text-custom-red transition-colors pb-1">Applications & saved listings</a>
        <a href="/social-candidate/candidatesubscriptions" class="hover:text-custom-red transition-colors pb-1">Job alerts</a>
        <a href="<?php echo $base; ?>social-candidate/accountcandidate" class="text-custom-red border-b-2 border-custom-red pb-1">Account & profile</a>
        <a href="/social-services/logout" class="text-black hover:text-custom-red transition-colors pb-1">Logout</a>
    </nav>

  </div>
</header>


<!-- ===== NAV ===== -->
<nav class="bg-black">
<div class="max-w-7xl mx-auto px-6">
<ul class="flex gap-8 text-white text-sm py-3">
<li>← Back to Home</li>
<li>About us</li><li>
  <a href="/find-a-job" class="flex items-center gap-1 hover:text-black">
    findajob
  </a>
</li>

<li>Get Help</li>
</ul>
</div>
</nav>

<main class="max-w-7xl mx-auto px-6 py-10">

<!-- ================= LIST ================= -->
<div x-show="!showForm" x-cloak>

<div class="flex justify-between mb-6 items-center">
<p class="text-sm font-medium">
Subscribe to job alerts to receive emails when jobs are posted! 🤩
</p>

<button @click="showForm=true"
class="bg-[#e15f55] hover:bg-red-600 text-white px-6 py-2.5 rounded-md text-sm font-medium transition">
+ New subscription
</button>

</div>

<div class="flex justify-between border-t pt-4 text-sm text-gray-500">
<span>0 results of 0 total</span>
<a class="text-blue-600 hover:underline">Refresh ↻</a>
</div>

<div class="flex justify-center items-center min-h-[300px] text-gray-500 text-sm">
No subscriptions found.
</div>

</div>

<!-- ================= FORM ================= -->
<div x-show="showForm" x-cloak>

<div class="flex justify-between items-center mb-10">

<button @click="showForm=false"
class="text-[#e15f55] underline font-bold text-sm">
Cancel
</button>

</div>
<form method="POST" action="/job-alerts/store"
class="bg-gray-200 rounded-lg p-6">

<div class="grid md:grid-cols-2 gap-6 mb-6">

<div>
<label class="text-sm font-medium">Name / subject *</label>
<input type="text" name="subject_name" required
class="w-full border rounded-md p-2 bg-white">
</div>

<div>
<label class="text-sm font-medium">Alert Status</label>
<div class="flex items-center gap-2 mt-2">
<input type="checkbox" name="alert_status" value="1" checked>
<span>Active / Subscribed</span>
</div>
</div>

<div>
<label class="text-sm font-medium">Notification email</label>
<input type="email" name="notification_email"
class="w-full border rounded-md p-2 bg-white">
</div>

<div>
<label class="text-sm font-medium">Frequency</label>
<select name="frequency" class="w-full border rounded-md p-2 bg-white">
<option value="daily">Daily</option>
<option value="weekly">Weekly</option>
<option value="monthly">Monthly</option>
</select>
</div>

</div>


<div class="grid md:grid-cols-2 gap-6">

<select name="role_type" class="border rounded-md p-2 bg-white">
<option>Role types</option>
<option>Employee / Staff (Paid)</option>
<option>Volunteer</option>
<option>Intern</option>
<option>Consultant</option>
</select>

<select name="workplace_option" class="border rounded-md p-2 bg-white">
<option>Workplace options</option>
<option>Hybrid</option>
<option>In-Person</option>
<option>Remote</option>
</select>

<select name="time_commitment" class="border rounded-md p-2 bg-white">
<option>Time commitment</option>
<option>Full-Time</option>
<option>Part-Time</option>
<option>Temporary</option>
</select>

<select name="role_category" class="border rounded-md p-2 bg-white">
<option>Role categories</option>
<option>Accounting</option>
<option>Education</option>
<option>Health</option>
<option>Technology</option>
<option>Other</option>
</select>

<select name="minimum_education" class="border rounded-md p-2 bg-white">
<option>Minimum education</option>
<option>High school</option>
<option>Associate's Degree</option>
<option>Masters</option>
<option>Doctorate</option>
</select>

<input type="number" name="minimum_experience" value="0"
class="border rounded-md p-2 bg-white">

<div>
<label class="text-sm font-medium">Pay terms</label>
<select name="pay_term" x-model="payTerm"
class="w-full border rounded-md p-2 bg-white">

<option value="">Select any</option>
<option value="hourly">Hourly</option>
<option value="salary">Salary</option>
<option value="contract">Contract</option>

</select>
</div>

<div x-show="payTerm==='hourly'">
<label class="text-sm font-medium">Minimum hourly rate</label>
<input type="number" step="0.01" name="minimum_hourly_rate"
class="w-full border rounded-md p-2 bg-white">
</div>

<div x-show="payTerm==='salary'">
<label class="text-sm font-medium">Minimum salary</label>
<input type="number" name="minimum_salary"
class="w-full border rounded-md p-2 bg-white">
</div>

<select name="impact_area" class="border rounded-md p-2 bg-white">
<option>Impact areas</option>
<option>Education</option>
<option>Health Care</option>
<option>Environment</option>
<option>Community</option>
<option>Other</option>
</select>

</div>

<div class="flex justify-end mt-8">
<button type="submit"
class="bg-[#e15f55] hover:bg-red-600 text-white px-12 py-2 rounded-md font-bold">
Submit
</button>
</div>

</form>


</div>

</main>

</body>
</html>
