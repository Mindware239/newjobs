<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Candidate Profile | Mindware Infotech</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Tailwind & Alpine -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col text-gray-800">

<!-- ================= TOP HEADER ================= -->
<header class="bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
    <a href="<?= $base ?>">
      <img src="<?= $base ?>uploads/Mindware-infotech.png" class="h-12">
    </a>

    <nav class="hidden md:flex items-center gap-8 text-sm">
      <a href="#" class="text-gray-600 hover:text-[#5b6bd5]">Applications & saved listings</a>
      <a href="#" class="text-gray-600 hover:text-[#5b6bd5]">Job alerts</a>
      <a href="#" class="text-[#5b6bd5] font-medium">Account & profile</a>
      <a href="#" class="text-gray-600 hover:text-[#5b6bd5]">Logout</a>
    </nav>
  </div>
</header>

<!-- ================= SECONDARY NAV (SOFT) ================= -->
<nav class="bg-white border-b border-gray-100">
  <div class="max-w-7xl mx-auto px-6">
    <ul class="flex items-center gap-8 text-sm py-3 text-gray-600">
      <li>
        <a href="<?=rtrim($base,'/') ?>/social-services" class="hover:text-[#5b6bd5]">
          ← Back to Home
        </a>
      </li>
      <li><a href="<?= rtrim($base,'/') ?>/find-a-job" class="hover:text-[#5b6bd5]">Find a job</a></li>
      <li><a href="<?= $base ?>about" class="hover:text-[#5b6bd5]">About us</a></li>
      <li><a href="<?= $base ?>help" class="hover:text-[#5b6bd5]">Get Help</a></li>
    </ul>
  </div>
</nav>

<!-- ================= MAIN ================= -->
<main class="flex-1">
  <div class="max-w-7xl mx-auto px-6 py-10">

    <!-- Heading -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-xl font-semibold text-gray-900">
          Tell us about yourself
        </h1>
        <p class="text-sm text-gray-500 mt-1">
          This helps us personalize job recommendations for you.
        </p>
      </div>

      <button type="submit" form="candidateForm"
              class="bg-[#5b6bd5] hover:bg-[#4a59c8] text-white px-10 py-2.5 rounded-md text-sm transition">
        Save Profile
      </button>
    </div>

    <!-- FORM CARD -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-8">

      <form id="candidateForm" method="post" action="/candidate/save"
            class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Full Name -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium">
            Your full name <span class="text-red-500">*</span>
          </label>
          <p class="text-xs text-gray-500 mb-1">Your legal name as per records.</p>
          <input type="text" name="full_name"
                 class="w-full border border-gray-300 rounded-md p-2.5
                        focus:ring-2 focus:ring-[#5b6bd5]/40 focus:outline-none"
                 required>
        </div>

        <!-- Preferred Name -->
        <div>
          <label class="block text-sm font-medium">
            Preferred name <span class="text-red-500">*</span>
          </label>
          <p class="text-xs text-gray-500 mb-1">What should we call you?</p>
          <input type="text" name="preferred_name"
                 class="w-full border border-gray-300 rounded-md p-2.5
                        focus:ring-2 focus:ring-[#5b6bd5]/40 focus:outline-none"
                 required>
        </div>

        <!-- Pronouns -->
        <div>
          <label class="block text-sm font-medium">Pronouns</label>
          <p class="text-xs text-gray-500 mb-1">(Optional)</p>
          <select name="pronouns"
                  class="w-full border border-gray-300 rounded-md p-2.5
                         focus:ring-2 focus:ring-[#5b6bd5]/40 focus:outline-none">
            <option value="">Select</option>
            <option>She / Her / Hers</option>
            <option>He / Him / His</option>
            <option>They / Them / Theirs</option>
            <option>Ze / Hir</option>
          </select>
        </div>

        <!-- Role Categories -->
        <div x-data="multiSelect([
          {id:1,name:'Accounting / Finance'},
          {id:2,name:'Administrative / Clerical'},
          {id:3,name:'Advocacy / Lobbying'},
          {id:4,name:'Animal Care'},
          {id:5,name:'Community Engagement'},
          {id:6,name:'Conservation'}
        ])" class="relative md:col-span-2">

          <label class="block text-sm font-medium">
            Role categories you’re interested in
          </label>
          <p class="text-xs text-gray-500 mb-1">Select all that apply.</p>

          <div @click="open=!open"
               class="border border-gray-300 rounded-md p-2.5 cursor-pointer bg-white text-sm">
            <span x-text="selected.length ? selected.length + ' selected' : 'Select categories'"></span>
          </div>

          <div x-show="open" @click.outside="open=false"
               class="absolute z-50 mt-1 w-full bg-white border rounded-md shadow max-h-60 overflow-y-auto">
            <template x-for="opt in options" :key="opt.id">
              <div @click="toggle(opt.id)"
                   class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" class="mr-2" :checked="selected.includes(opt.id)">
                <span x-text="opt.name"></span>
              </div>
            </template>
          </div>

          <template x-for="id in selected">
            <input type="hidden" name="role_categories[]" :value="id">
          </template>
        </div>

        <!-- Mission Focus -->
        <div x-data="multiSelect([
          {id:1,name:'Aging / Seniors'},
          {id:2,name:'Arts, Culture & Humanities'},
          {id:3,name:'Civil Rights & Advocacy'},
          {id:4,name:'Community Improvement'},
          {id:5,name:'Environment / Conservation'}
        ])" class="relative md:col-span-2">

          <label class="block text-sm font-medium">
            Mission focus areas
          </label>
          <p class="text-xs text-gray-500 mb-1">Select all that apply.</p>

          <div @click="open=!open"
               class="border border-gray-300 rounded-md p-2.5 cursor-pointer bg-white text-sm">
            <span x-text="selected.length ? selected.length + ' selected' : 'Select focus areas'"></span>
          </div>

          <div x-show="open" @click.outside="open=false"
               class="absolute z-50 mt-1 w-full bg-white border rounded-md shadow max-h-60 overflow-y-auto">
            <template x-for="opt in options" :key="opt.id">
              <div @click="toggle(opt.id)"
                   class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" class="mr-2" :checked="selected.includes(opt.id)">
                <span x-text="opt.name"></span>
              </div>
            </template>
          </div>

          <template x-for="id in selected">
            <input type="hidden" name="mission_focus[]" :value="id">
          </template>
        </div>

      </form>

      <p class="text-xs text-gray-500 mt-6 text-right">
        Your information is shared only when you apply for a job.
      </p>

    </div>

  </div>
</main>

<!-- Alpine Helper -->
<script>
function multiSelect(options) {
  return {
    open: false,
    selected: [],
    options,
    toggle(id) {
      this.selected.includes(id)
        ? this.selected = this.selected.filter(i => i !== id)
        : this.selected.push(id);
    }
  }
}
</script>

</body>
</html>
