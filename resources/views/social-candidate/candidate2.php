<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Application Submitted</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#f6f6f6] text-gray-800">

<!-- HEADER -->
<header class="bg-white border-b">
  <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
    <h1 class="text-3xl font-serif font-bold">
      Work for <span class="text-black">GOOD</span>
    </h1>

    <div class="flex items-center space-x-6 text-sm">
      <a class="text-red-500 font-medium">Applications & saved listings</a>
      <a>Job alerts</a>
      <a>Account & profile</a>
       <a href="/logout">Logout</a>
    </div>
  </div>
</header>

<!-- BLACK NAV -->
<nav class="bg-black text-white">
  <div class="max-w-7xl mx-auto px-6 py-3 flex space-x-8 text-sm">
    <a>◀ Back to Home</a>
    <a>Find a job</a>
    <a>Search employers</a>
    <a>Career insights</a>
    <a>About us</a>
    <a>Get Help</a>
  </div>
</nav>

<!-- CONTENT -->
<main class="max-w-4xl mx-auto mt-10 bg-white py-14 text-center">

  <a class="text-red-500 text-sm block mb-6">◀ Back to your saved listings</a>

  <p class="font-semibold mb-12">
    Your Application for:
    <span class="font-bold">
      Part-Time Administrative Assistant / Coordinator
    </span>
    with <em>Global Servant Leaders Inc.</em>
  </p>

  <!-- PROGRESS -->
  <div class="flex justify-center items-center mb-12">

    <!-- STEP 1 -->
    <div class="flex flex-col items-center">
      <div class="w-7 h-7 rounded-full bg-blue-800 text-white flex items-center justify-center text-sm">✓</div>
      <span class="text-xs mt-2">Initial Screening</span>
    </div>

    <div class="w-20 h-[2px] bg-blue-800 mx-2"></div>

    <!-- STEP 2 -->
    <div class="flex flex-col items-center">
      <div class="w-7 h-7 rounded-full bg-blue-800 text-white flex items-center justify-center text-sm">✓</div>
      <span class="text-xs mt-2">Application</span>
    </div>

    <div class="w-20 h-[2px] bg-blue-800 mx-2"></div>

    <!-- STEP 3 -->
    <div class="flex flex-col items-center">
      <div class="w-7 h-7 rounded-full bg-blue-800 text-white flex items-center justify-center text-sm">3</div>
      <span class="text-xs mt-2 font-semibold">Confirmation</span>
    </div>

  </div>

  <!-- SUCCESS -->
  <p class="font-medium mb-6">
    Your application was successfully submitted! 🎉
  </p>

  <a class="text-red-500 text-sm block mb-10">◀ Back to your saved listings</a>

  <!-- EDIT BUTTON -->
  <button class="bg-[#e25b4f] hover:bg-red-600 text-white px-12 py-3 rounded font-medium">
    ✎ Edit application
  </button>

</main>

</body>
</html>
