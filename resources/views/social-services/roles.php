<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Browse Roles | mindware</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

  
<!-- ================= HEADER ================= -->
<header class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

        <a href="<?php echo $base; ?>" class="flex items-center gap-3">
            <img src="<?php echo $base; ?>uploads/Mindware-infotech.png" class="h-12 w-auto">
        </a>

        <nav class="hidden md:flex items-center gap-8">
            <a href="<?php echo rtrim ($base,'/'); ?>/social-services" class="font-medium hover:text-[#5b6bd5]">Home</a>
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

            <a href="/register"
               class="px-4 py-2 rounded-md text-white transition"
               style="background-color:#5b6bd5;"
               onmouseover="this.style.backgroundColor='#4a59c8'"
               onmouseout="this.style.backgroundColor='#5b6bd5'">
                Candidate Login
            </a>
        </div>

    </div>
</header>

  <main class="max-w-7xl mx-auto p-6 mt-8 font-serif text-black">

  <h1 class="text-3xl font-semibold mb-8">
    Browse by role category
  </h1>

  <ul class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-2 gap-x-16">
    <li>Accounting / Finance</li>
    <li>Administrative / Clerical</li>
    <li>Advocacy / Lobbying</li>
    <li>Animal Care</li>
    <li>Campaign Management / Canvassing / Field Organizer</li>
    <li>Child Care / After school / Counselor / Mentor</li>
    <li>Community Engagement</li>
    <li>Conservation</li>
    <li>Consulting</li>

    <li>Creative / Art Production</li>
    <li>Customer Service / Retail</li>
    <li >Development / Fundraising</li>

    <li>Direct Service / Social Service</li>
    <li>Education / Teaching</li>
    <li>Event Planning</li>

    <li>Executive / Senior Management</li>
    <li>Facilities & Warehouse Management / Equipment / Drivers</li>
    <li>Food Service</li>

    <li>Health / Medical / Nutrition</li>
    <li>Home Health Aid / Senior Care</li>
    <li>Horticulture / Groundskeeper</li>

    <li>Housing / Construction</li>
    <li>Human Resources / Recruiting</li>
    <li>Journalism / Broadcasting</li>

    <li>Legal</li>
    <li>Library Science</li>
    <li>Marketing / Communications / Public Relations</li>

    <li>Member / Membership Management</li>
    <li>Operations / Business Management</li>
    <li>Program / Project Management</li>

    <li>Public Policy / Administration</li>
    <li>Recreational / Camp Associates & Management</li>
    <li>Research</li>

    <li>Sales / Business Development</li>
    <li>Social Work / Counseling</li>
    <li>Technology / Data Management</li>

    <li>Training / Curriculum Development</li>
    <li>Unknown / Other</li>
    <li>Volunteer Services</li>
  </ul>

</main>

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
