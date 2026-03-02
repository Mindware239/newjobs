<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Candidate Account</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
[x-cloak]{display:none}
</style>
</head>

<body class="bg-white text-gray-800" x-data="{ edit:false }">

<!-- HEADER (simple version) -->
<header class="border-b p-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold">Mindware Infotech</h1>   

    <nav class="flex gap-6 text-sm">
        <a>Applications</a>
        <a>Job alerts</a>
        <a class="text-red-500 font-semibold">Account & profile</a>
        <a>Logout</a>
    </nav>
</header>

<!-- BLACK BAR -->
<div class="bg-black text-white py-3 px-6 flex gap-6 text-sm">
    <span>Back to Home</span>
    <span>Find a job</span>
    <span>Search employers</span>
    <span>Career insights</span>
    <span>About us</span>
    <span>Get Help</span>
</div>

<main class="max-w-6xl mx-auto px-6 py-12">

<!-- ================= PROFILE VIEW ================= -->
<div x-show="!edit" x-cloak>

<div class="flex justify-between items-center mb-10">
    <h2 class="text-lg font-semibold">Hello, Mr.! 👋</h2>

    <button @click="edit=true"
        class="bg-[#e15f55] text-white px-10 py-2.5 rounded-md font-medium">
        Edit profile
    </button>
</div>

<p class="text-sm text-gray-600 mb-6">
Details saved to your account for easier applications and better recommendations.
</p>

<hr class="mb-8">

<div class="grid md:grid-cols-3 gap-10 text-sm">

<div>
    <p class="font-semibold">Full name</p>
    <p>Mr. Mr. Mindware Info Sr.</p>
</div>

<div>
    <p class="font-semibold">Preferred name</p>
    <p>Mr.</p>
</div>

<div>
    <p class="font-semibold">Pronouns</p>
    <p>she / her / hers</p>
</div>

</div>

<div class="mt-10 space-y-6 text-sm">

<div>
    <p class="font-semibold">Role categories you are interested in:</p>
    <p class="text-gray-600">None selected</p>
</div>

<div>
    <p class="font-semibold">Mission focus areas you are interested in:</p>
    <p class="text-gray-600">None selected</p>
</div>

</div>

</div>

<!-- ================= EDIT FORM ================= -->
<div x-show="edit" x-cloak>

<div class="flex justify-between items-center mb-10">

<button @click="edit=false"
 class="text-red-500 text-sm underline">
Cancel
</button>

<button class="bg-[#e15f55] text-white px-14 py-2.5 rounded-md font-medium">
Submit
</button>

</div>

<div class="space-y-10 text-sm">

<div class="grid md:grid-cols-3 gap-6">

<div class="md:col-span-2">
<label class="font-medium">Your full name *</label>
<input class="w-full border rounded p-2 mt-1" 
       value="Mr. Mr. Mindware Info Sr.">
</div>

<div>
<label class="font-medium">Your preferred name *</label>
<input class="w-full border rounded p-2 mt-1" value="Mr.">
</div>

</div>

<div>
<label class="font-medium">Pronouns</label>
<select class="w-full border rounded p-2 mt-1">
    <option>She / Her / Hers</option>
    <option>He / Him / His</option>
    <option>They / Them</option>
</select>
</div>

<div class="grid md:grid-cols-5 gap-4">

<select class="border rounded p-2">
<option>Prefix</option>
<option>Mr.</option>
<option>Ms.</option>
</select>

<input class="border rounded p-2" placeholder="First name" value="Mr.">

<input class="border rounded p-2" placeholder="Middle name" value="Mindware">

<input class="border rounded p-2" placeholder="Last name" value="Info">

<select class="border rounded p-2">
<option>Suffix</option>
<option>Sr</option>
<option>Jr</option>
</select>

</div>

<div class="grid md:grid-cols-2 gap-6">

<select class="border rounded p-2">
<option>Select role categories</option>
<option>Technology</option>
<option>Education</option>
<option>Health</option>
</select>

<select class="border rounded p-2">
<option>Select mission focus</option>
<option>Community</option>
<option>Environment</option>
<option>Healthcare</option>
</select>

</div>

</div>

</div>

</main>

</body>
</html>
