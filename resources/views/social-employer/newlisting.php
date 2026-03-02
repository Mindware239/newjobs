<?php $base = $base ?? '/'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= $title ?? 'Create new listing' ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<style>
  body { font-size:14px; color:#333; }
  .container-wf { max-width:1200px; }
  [x-cloak] { display:none !important; }
</style>
</head>

<body class="bg-white min-h-screen"
      x-data="{ step:1, createOrg:false, orgName:'<?= addslashes($job['organization_name'] ?? '') ?>' }">

<!-- ================= HEADER ================= -->
<header class="border-b">
  <div class="container-wf mx-auto px-6 py-4 flex justify-between items-center">
    <div class="flex-shrink-0">
            <a href="<?php echo $base; ?>">
                <img src="<?php echo $base; ?>uploads/Mindware-infotech.png" alt="Logo" class="h-10 md:h-14 w-auto">
            </a>
        </div>
    <nav class="flex gap-6 text-sm text-gray-700">
      <a href="/social-employer/newlisting">＋ Post a job</a>
      <a href="/social-employer/listings" class="text-red-500">Job listings</a>
      <a href="/social-employer/application">Applications</a>
      <a href="/social-employer/organisation"class="text-red-500">Organizations & users</a>
       <a href="/social-employer/account"class="text-red-500">Account & profile</a>
      <a href="/logout">Logout</a>
    </nav>
  </div>
</header>

<!-- ================= BLACK BAR ================= -->
<nav class="bg-black text-white">
  <div class="container-wf mx-auto px-6 py-3 flex gap-6 text-sm">
    <a href="/social-services" class="hover:underline">◀ Back to Home</a>
    <a href="/pricing" class="hover:underline">Pricing</a>
    <a href="/blog" class="hover:underline">Hiring insights</a>
    <a href="/aboutus" class="hover:underline">About us</a>
    <a href="/supports" class="hover:underline">Get Help</a>
  </div>
</nav>
<!-- ================= MAIN ================= -->
<div class="container-wf mx-auto px-6 py-10">

<a href="/social-employer/listings" class="text-sm text-red-500 mb-6 inline-block">◀ Back to your listings</a>

<form action="<?= $action ?? '/social-employer/newlisting' ?>" method="POST" id="createJobForm" @submit="step=5">
    <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

<!-- ================= WIZARD STEPPER ================= -->
<div class="flex justify-center mb-12">

<div class="flex items-center gap-6 text-sm">

<!-- STEP 1 -->
<div class="flex flex-col items-center">
<div class="w-7 h-7 rounded-full flex items-center justify-center"
     :class="step>=1 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600'">
1
</div>
<span class="mt-2">Organization</span>
</div>

<div class="w-20 h-[2px]"
     :class="step>1 ? 'bg-blue-600' : 'bg-gray-300'"></div>

<!-- STEP 2 -->
<div class="flex flex-col items-center">
<div class="w-7 h-7 rounded-full flex items-center justify-center"
     :class="step>=2 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600'">
2
</div>
<span class="mt-2">Role</span>
</div>

<div class="w-20 h-[2px]" :class="step>2 ? 'bg-blue-600' : 'bg-gray-300'"></div>

<!-- STEP 3 -->
<div class="flex flex-col items-center">
<div class="w-7 h-7 rounded-full flex items-center justify-center"
     :class="step>=3 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600'">
3
</div>
<span class="mt-2">Location</span>
</div>

<div class="w-20 h-[2px]" :class="step>3 ? 'bg-blue-600' : 'bg-gray-300'"></div>

<!-- STEP 4 -->
<div class="flex flex-col items-center">
<div class="w-7 h-7 rounded-full flex items-center justify-center"
     :class="step>=4 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600'">
4
</div>
<span class="mt-2">options</span>
</div>

<div class="w-20 h-[2px]" :class="step>4 ? 'bg-blue-600' : 'bg-gray-300'"></div>

<!-- STEP 5 -->
<div class="flex flex-col items-center">
<div class="w-7 h-7 rounded-full flex items-center justify-center"
     :class="step>=5 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600'">
5
</div>
<span class="mt-2">Confirmation</span>
</div>

</div>
</div>

<div x-show="step===1" x-cloak>

<!-- Candidate Type -->
<div class="bg-gray-200 p-6 rounded mb-6 grid grid-cols-2 gap-6">

<div>
  <label class="font-semibold">
    What type of candidate(s) are you seeking? *
  </label>
  <p class="text-sm text-gray-600 mb-2">
    Determines the amount of credits / cost
  </p>

  <select name="candidate_type" class="w-full border rounded px-3 py-2">
    <option value="Employee / Staff (Paid)" <?= ($job['candidate_type'] ?? '') == 'Employee / Staff (Paid)' ? 'selected' : '' ?>>Employee / Staff (Paid)</option>
    <option value="Board Member" <?= ($job['candidate_type'] ?? '') == 'Board Member' ? 'selected' : '' ?>>Board Member</option>
    <option value="Consultant" <?= ($job['candidate_type'] ?? '') == 'Consultant' ? 'selected' : '' ?>>Consultant</option>
    <option value="Intern" <?= ($job['candidate_type'] ?? '') == 'Intern' ? 'selected' : '' ?>>Intern</option>
    <option value="Volunteer" <?= ($job['candidate_type'] ?? '') == 'Volunteer' ? 'selected' : '' ?>>Volunteer</option>
  </select>
</div>

<div class="flex items-center">
  <div>
    <p class="font-semibold">Starts at $105</p>
    <p class="text-gray-600">(Standard listing for 30 days)</p>
  </div>
</div>

</div>

<!-- Organization -->
<div class="bg-gray-200 p-6 rounded">

<div x-show="!createOrg">

<h3 class="font-semibold mb-2">
What account is this listing related to?
</h3>

<p class="text-sm text-gray-600 mb-4">
Select your organization
</p>

<select name="organization_select" x-model="orgName" class="w-full border rounded px-3 py-2 mb-3">
<option value="">Begin typing to search</option>
<option value="National Co+ Grocers" <?= ($job['organization_name'] ?? '') == 'National Co+ Grocers' ? 'selected' : '' ?>>National Co+ Grocers</option>
<option value="Equal Rights Advocates" <?= ($job['organization_name'] ?? '') == 'Equal Rights Advocates' ? 'selected' : '' ?>>Equal Rights Advocates</option>
<option value="David Geffen School of Drama" <?= ($job['organization_name'] ?? '') == 'David Geffen School of Drama' ? 'selected' : '' ?>>David Geffen School of Drama</option>
</select>

<button type="button" @click="createOrg=true"
        class="text-sm text-red-500 hover:underline">
Create new organization
</button>

</div>
<div x-show="createOrg" x-transition>
    <h3 class="font-semibold text-gray-900 mb-2">
  What account is this listing related to?
</h3>

<p class="text-sm text-gray-700 mb-2">
  If you are a recruiter or posting on behalf of your clients, please select
  your own recruitment agency, not your client’s organization.
</p>

<p class="text-sm text-gray-700">
  To add yourself to an existing organization account, please search our
  database of existing organizations to request access. If you still don’t see
  your organization, you may create a new one.
</p>

  <div class="flex justify-between items-center mb-3">
    <h3 class="font-semibold">Create a new organization</h3>
   <button type="button"
        @click="createOrg=false"
        class="text-sm text-red-500 hover:underline">
  Cancel & search again
</button>

  </div>

  <p class="text-sm text-gray-600 mb-6">
    Please tell us a little about this organization.
  </p>

  <div class="grid grid-cols-2 gap-6">

    <div>
      <label class="font-medium block mb-1">
        Organization name <span class="text-red-500">*</span>
      </label>
      <input name="organization_name" x-model="orgName" class="w-full border rounded px-3 py-2"
             placeholder="Enter an organization name">
    </div>

    <div>
      <label class="font-medium block mb-1">
        Common short names or acronyms
      </label>
      <input name="org_acronyms" class="w-full border rounded px-3 py-2"
             placeholder="Enter alternative, common or short names">
    </div>

    <div>
      <label class="font-medium block mb-1">
        Type of organization <span class="text-red-500">*</span>
      </label>
      <select name="org_type" class="w-full border rounded px-3 py-2">
        <option value="">Select the most applicable</option>
        <option value="Non-Profit Organisation" <?= ($job['organization_type'] ?? '') == 'Non-Profit Organisation' ? 'selected' : '' ?>>Non-Profit Organisation</option>
        <option value="Private Bussiness" <?= ($job['organization_type'] ?? '') == 'Private Bussiness' ? 'selected' : '' ?>>Private Bussiness</option>
        
      </select>
    </div>

    <div class="flex items-center gap-2 mt-6">
      <input type="checkbox" name="is_agency" value="1" <?= ($job['is_agency'] ?? 0) ? 'checked' : '' ?>>
      <span class="text-sm">
        Is this a recruiting or staffing agency?
      </span>
    </div>

    <div>
      <label class="font-medium block mb-1">Website (Home)</label>
      <input name="website" class="w-full border rounded px-3 py-2"
             placeholder="i.e. https://workforgood.org" value="<?= htmlspecialchars($job['website'] ?? '') ?>">
    </div>

    <div>
      <label class="font-medium block mb-1">EIN</label>
      <input name="ein" class="w-full border rounded px-3 py-2"
             placeholder="i.e. 12-1234567" value="<?= htmlspecialchars($job['ein'] ?? '') ?>">
    </div>

    <div>
      <label class="font-medium block mb-1">Number of people / staff</label>
      <input name="staff_count" type="number" class="w-full border rounded px-3 py-2" value="<?= htmlspecialchars($job['staff_count'] ?? '') ?>">
    </div>

    <div>
      <label class="font-medium block mb-1">Primary mission focus areas</label>
      <select name="mission_focus" class="w-full border rounded px-3 py-2">
        <option value="">Select any that apply</option>
    <option value="Aging/Seniors" <?= ($job['org_mission_focus'] ?? '') == 'Aging/Seniors' ? 'selected' : '' ?>>Aging/Seniors</option>
    <option value="Animal-Related" <?= ($job['org_mission_focus'] ?? '') == 'Animal-Related' ? 'selected' : '' ?>>Animal-Related</option>
    <option value="Crime & legal-Related" <?= ($job['org_mission_focus'] ?? '') == 'Crime & legal-Related' ? 'selected' : '' ?>>Crime & legal-Related</option>

      </select>
    </div>

    <div>
      <label class="font-medium block mb-1">Organization’s mission</label>
      <textarea name="org_mission" class="w-full border rounded px-3 py-2"
                placeholder="Write a brief mission statement"><?= htmlspecialchars($job['organization_mission'] ?? '') ?></textarea>
    </div>

    <div>
      <label class="font-medium block mb-1">Who does this organization serve?</label>
      <textarea name="org_impact" class="w-full border rounded px-3 py-2"
                placeholder="Write a short impact statement"><?= htmlspecialchars($job['organization_impact'] ?? '') ?></textarea>
    </div>

  </div>

<button type="button" @click="createOrg=false"
        class="text-sm text-red-500 mt-4">
Cancel
</button>

</div>

<div class="text-right mt-6">
<button type="button" @click="step=2"
        class="bg-[#f3b3ac] text-white px-12 py-3 rounded">
Save & continue →
</button>
</div>

</div>
</div>

<!-- ===================================================== -->
<!-- STEP 2 : ROLE -->
<!-- ===================================================== -->
<div x-show="step===2" x-cloak>

<div class="grid grid-cols-2 gap-6 bg-gray-200 p-6 rounded">

<!-- Role name -->
<div>
  <label class="font-semibold block mb-1">
    Role name (i.e. employee or position title) <span class="text-red-500">*</span>
  </label>
  <p class="text-xs text-gray-600 mb-2">
    Please use less than 125 characters for display in search results.
  </p>
  <input name="role_name" class="w-full border rounded px-3 py-2"
         placeholder="Use a recognizable title for best results!" value="<?= htmlspecialchars($job['role_name'] ?? '') ?>">
</div>

<!-- Time commitment -->
<div>
  <label class="font-semibold block mb-1">
    What is the expected time commitment? <span class="text-red-500">*</span>
  </label>
  <p class="text-xs text-gray-600 mb-2">
    Time expectations for position
  </p>
  <select name="time_commitment" class="w-full border rounded px-3 py-2">
    <option value="">Select any that may apply</option>
    <option value="Full-time" <?= ($job['time_commitment'] ?? '') == 'Full-time' ? 'selected' : '' ?>>Full-time</option>
    <option value="Part-time" <?= ($job['time_commitment'] ?? '') == 'Part-time' ? 'selected' : '' ?>>Part-time</option>
    <option value="Contract" <?= ($job['time_commitment'] ?? '') == 'Contract' ? 'selected' : '' ?>>Contract</option>
    <option value="Volunteer" <?= ($job['time_commitment'] ?? '') == 'Volunteer' ? 'selected' : '' ?>>Volunteer</option>
  </select>
</div>

<!-- Details about time commitment -->
<div>
  <label class="font-semibold block mb-1">
    Details about time commitment
  </label>
  <p class="text-xs text-gray-600 mb-2">(optional)</p>
  <input name="time_details" class="w-full border rounded px-3 py-2"
         placeholder="e.g. 4 hours per week" value="<?= htmlspecialchars($job['time_details'] ?? '') ?>">
</div>

<!-- Work categories -->
<div>
  <label class="font-semibold block mb-1">
    What categories of work are involved? <span class="text-red-500">*</span>
  </label>
  <p class="text-xs text-gray-600 mb-2">
    Skills & responsibilities expected from candidates
  </p>
  <select name="work_category" class="w-full border rounded px-3 py-2">
    <option value="">Select any that apply</option>
    <option value="Technology" <?= ($job['work_category'] ?? '') == 'Technology' ? 'selected' : '' ?>>Technology</option>
    <option value="Administration" <?= ($job['work_category'] ?? '') == 'Administration' ? 'selected' : '' ?>>Administration</option>
    <option value="Finance" <?= ($job['work_category'] ?? '') == 'Finance' ? 'selected' : '' ?>>Finance</option>
    <option value="Marketing" <?= ($job['work_category'] ?? '') == 'Marketing' ? 'selected' : '' ?>>Marketing</option>
    <option value="Operations" <?= ($job['work_category'] ?? '') == 'Operations' ? 'selected' : '' ?>>Operations</option>
  </select>
</div>

<!-- Years experience -->
<div>
  <label class="font-semibold block mb-1">
    How many years experience with similar roles is required?
  </label>
  <p class="text-xs text-gray-600 mb-2">
    In years, minimum experience requirement
  </p>
  <input name="experience_years" type="number"
         value="<?= htmlspecialchars($job['experience_years'] ?? '0') ?>"
         class="w-full border rounded px-3 py-2">
</div>

<!-- Education -->
<div>
  <label class="font-semibold block mb-1">
    Does this role require an education level?
  </label>
  <p class="text-xs text-gray-600 mb-2">
    Select the minimum level that may apply
  </p>
  <select name="education_level" class="w-full border rounded px-3 py-2">
    <option value="">Select the minimum level that may apply</option>
    <option value="High School" <?= ($job['education_level'] ?? '') == 'High School' ? 'selected' : '' ?>>High School</option>
    <option value="Diploma" <?= ($job['education_level'] ?? '') == 'Diploma' ? 'selected' : '' ?>>Diploma</option>
    <option value="Bachelor’s" <?= ($job['education_level'] ?? '') == 'Bachelor’s' ? 'selected' : '' ?>>Bachelor’s</option>
    <option value="Master’s" <?= ($job['education_level'] ?? '') == 'Master’s' ? 'selected' : '' ?>>Master’s</option>
  </select>
</div>

<!-- HOW PAID -->
<div class="col-span-2 bg-gray-200 p-4 rounded"
     x-data="{ payType: '<?= $job['pay_type'] ?? '' ?>' }">

<label class="font-semibold block mb-3">
How is this role paid?
</label>

<select name="pay_type" x-model="payType"
        class="w-full border rounded px-3 py-2 mb-4">
  <option value="">Select any that may apply</option>
  <option value="hourly">Hourly</option>
  <option value="salary">Salary</option>
  <option value="contract">Contract</option>
</select>

<!-- RATE FIELDS -->

<div x-show="payType" class="grid grid-cols-2 gap-6">

<!-- MIN -->
<div>
  <label class="text-sm font-medium block mb-1"
         x-text="payType === 'hourly' ? 'Minimum hourly rate' :
                 payType === 'salary' ? 'Minimum salary' :
                 'Minimum contract amount'">
  </label>

  <div class="flex border rounded overflow-hidden bg-white">
    <span class="px-3 flex items-center bg-gray-100">$</span>
    <input name="pay_min" type="number"
           class="w-full px-3 py-2 outline-none"
           placeholder="0" value="<?= htmlspecialchars($job['min_pay'] ?? '') ?>">
    <span class="px-3 flex items-center bg-gray-100 text-sm"
          x-text="payType === 'hourly' ? 'per hour' :
                  payType === 'salary' ? 'per year' :
                  'total'">
    </span>
  </div>
</div>

<!-- MAX -->
<div>
  <label class="text-sm font-medium block mb-1"
         x-text="payType === 'hourly' ? 'Maximum hourly rate' :
                 payType === 'salary' ? 'Maximum salary' :
                 'Maximum contract amount'">
  </label>

  <div class="flex border rounded overflow-hidden bg-white">
    <span class="px-3 flex items-center bg-gray-100">$</span>
    <input name="pay_max" type="number"
           class="w-full px-3 py-2 outline-none"
           placeholder="0" value="<?= htmlspecialchars($job['max_pay'] ?? '') ?>">
    <span class="px-3 flex items-center bg-gray-100 text-sm"
          x-text="payType === 'hourly' ? 'per hour' :
                  payType === 'salary' ? 'per year' :
                  'total'">
    </span>
  </div>
</div>

</div>

</div>


<!-- Empty right column (same as screenshot spacing) -->
<div></div>

<!-- Mission focus (full width) -->
<div class="col-span-2">
  <label class="font-semibold block mb-2">
    What mission focus areas apply to this role? <span class="text-red-500">*</span>
  </label>
  <select name="role_focus" class="w-full border rounded px-3 py-2">
    <option value="">Select any that apply</option>
    <option value="Healthcare" <?= (($job['role_mission_focus'] ?? $job['role_mission_focused'] ?? '') ) == 'Healthcare' ? 'selected' : '' ?>>Healthcare</option>
    <option value="Education" <?= (($job['role_mission_focus'] ?? $job['role_mission_focused'] ?? '') ) == 'Education' ? 'selected' : '' ?>>Education</option>
    <option value="Environment" <?= (($job['role_mission_focus'] ?? $job['role_mission_focused'] ?? '') ) == 'Environment' ? 'selected' : '' ?>>Environment</option>
    <option value="Community" <?= (($job['role_mission_focus'] ?? $job['role_mission_focused'] ?? '') ) == 'Community' ? 'selected' : '' ?>>Community</option>
  </select>
</div>

<!-- Short description (full width) -->
<div class="col-span-2">
  <label class="font-semibold block mb-1">
    Short description <span class="text-red-500">*</span>
  </label>
  <p class="text-xs text-gray-600 mb-2">
    Please use less than 250 characters for display in search results.
  </p>
  <input name="short_description" class="w-full border rounded px-3 py-2"
         placeholder="Be concise and descriptive for best results!" value="<?= htmlspecialchars($job['short_description'] ?? '') ?>">
</div>

</div>
<!-- Overview / Details section -->
<div class="col-span-2 bg-[#f2f2f2] p-6 mt-6 rounded">

  <label class="font-semibold block mb-1">
    Enter an overview and any other details here <span class="text-red-500">*</span>
  </label>

  <p class="text-xs text-gray-600 mb-3">
    Please note that pasted content may have formatting removed for best display on your listing.
  </p>

  <!-- Simple toolbar (visual like screenshot) -->
  <div class="border bg-white rounded mb-2 p-2 flex flex-wrap gap-2 text-sm">

    <select class="border px-2 py-1 text-sm rounded">
      <option>Normal</option>
      <option>Heading</option>
      <option>Bold</option>
    </select>

    <button type="button" class="font-bold">B</button>
    <button type="button" class="italic">I</button>
    <button type="button" class="underline">U</button>
    <button type="button">❝ ❞</button>
    <button type="button">• List</button>
    <button type="button">↔</button>
    <button type="button">🔗</button>
    <button type="button">🖼</button>
    <button type="button">Tx</button>

  </div>

  <!-- Text area -->
  <textarea name="job_overview"
    rows="6"
    class="w-full border rounded px-3 py-2 resize-y bg-white"><?= htmlspecialchars($job['full_description'] ?? '') ?></textarea>

</div>

<div class="flex justify-between mt-10">

<a @click="step=1"
   class="cursor-pointer text-[#f3b3ac]">
← Previous
</a>
<button type="button" @click="step=3"
 class="bg-[#f3b3ac] text-white px-12 py-3 rounded">
Save & continue →
</button>

</div>
</div>
<!-- ================= STEP 3 : LOCATION ================= -->
<div x-show="step===3" x-cloak>

<div class="bg-gray-200 p-6 rounded">

<div class="grid grid-cols-2 gap-6 mb-4">

<div>
<label class="font-semibold block mb-1">
Workplace options *
</label>
<select name="workplace_type" class="w-full border rounded px-3 py-2">
  <option value="">Can this be done remotely?</option>
  <option value="Remote" <?= ($job['workplace_option'] ?? '') == 'Remote' ? 'selected' : '' ?>>Remote</option>
  <option value="On-site" <?= ($job['workplace_option'] ?? '') == 'On-site' ? 'selected' : '' ?>>On-site</option>
  <option value="Hybrid" <?= ($job['workplace_option'] ?? '') == 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
</select>
</div>

<div>
<label class="font-semibold block mb-1">
Details
</label>
<input name="workplace_details" class="w-full border rounded px-3 py-2"
 placeholder="Work from home on Tuesdays, warehouse/office..." value="<?= htmlspecialchars($job['workplace_details'] ?? '') ?>">
</div>

</div>

<label class="font-semibold block mb-1">
What area or location should be shown? *
</label>

<input name="location" class="w-full border rounded px-3 py-2 mb-3"
 placeholder="Search for addresses and locations..." value="<?= htmlspecialchars($job['job_location'] ?? '') ?>">

<p class="text-sm text-gray-600 mb-4">
We recommend choosing a city or zipcode.
</p>

<label class="font-semibold block mb-1">
Details about location
</label>

<input name="location_details" class="w-full border rounded px-3 py-2"
 placeholder="i.e. headquarters in Houston" value="<?= htmlspecialchars($job['location_details'] ?? '') ?>">

</div>

<div class="flex justify-between mt-10">

<a @click="step=2"
 class="cursor-pointer text-[#f3b3ac]">
← Previous
</a>
<button type="button" @click="step=4"
 class="bg-[#f3b3ac] text-white px-12 py-3 rounded">
Save & continue →
</button>

</div>



</div>
<!-- ================= STEP 4 : OPTIONS ================= -->
<div x-show="step===4" x-cloak>

<!-- Publish Section -->
<div class="bg-gray-200 p-6 rounded mb-8">

  <div class="grid grid-cols-3 gap-6 items-end">

    <div>
      <label class="font-semibold block mb-1">
        When would you like your listing published? *
      </label>

      <select name="publish_type" class="w-full border rounded px-3 py-2">
        <option value="As soon as possible" <?= ($job['publish_type'] ?? '') == 'As soon as possible' ? 'selected' : '' ?>>As soon as possible</option>
        <option value="Schedule for later" <?= ($job['publish_type'] ?? '') == 'Schedule for later' ? 'selected' : '' ?>>Schedule for later</option>
      </select>
    </div>

    <div>
      <label class="font-semibold block mb-1">
        Earliest date listing should be published *
      </label>

      <input name="publish_date" type="date" class="w-full border rounded px-3 py-2" value="<?= htmlspecialchars($job['publish_date'] ?? '') ?>">
    </div>

    <div class="flex items-center gap-3 text-gray-700">
      <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
        📅
      </div>
      <div>
        <p class="font-medium">Listed until</p>
        <p class="text-sm">30 days after published</p>
      </div>
    </div>

  </div>

</div>


<!-- Apply Method -->
<div class="bg-gray-200 p-6 rounded mb-8">

<label class="font-semibold block mb-4">
How should applicants apply? *
</label>

<div class="space-y-4">

<label class="flex gap-3 cursor-pointer">
  <input type="radio" name="apply_method" value="website" <?= ($job['apply_method'] ?? 'email') == 'website' ? 'checked' : '' ?>>
  <div>
    <p class="font-medium">On your website / applicant system</p>
    <p class="text-sm text-gray-600">
      Candidates will be sent to your URL
    </p>
  </div>
</label>

<label class="flex gap-3 cursor-pointer">
  <input type="radio" name="apply_method" value="email" <?= ($job['apply_method'] ?? 'email') == 'email' ? 'checked' : '' ?>>
  <div>
    <p class="font-medium">Send details / resume to email(s)</p>
    <p class="text-sm text-gray-600">
      Candidate info will be emailed to you
    </p>
  </div>
</label>

</div>

</div>


<!-- Notification Emails -->
<div class="bg-gray-200 p-6 rounded mb-8" x-data="{ emails: <?= !empty($job['notification_emails']) ? $job['notification_emails'] : "['sales@indianbarcode.com']" ?> }">

<label class="font-semibold block mb-3">
Notification emails
</label>

<template x-for="(email,i) in emails" :key="i">

<div class="flex gap-3 mb-3">

<input type="email"
       class="w-full border rounded px-3 py-2"
       x-model="emails[i]"
       :name="'emails[]'">

<button type="button" x-show="emails.length>1"
        @click="emails.splice(i,1)"
        class="text-red-500 text-sm">
Remove
</button>

</div>

</template>

<button type="button" @click="emails.push('')"
 class="text-sm text-red-500 hover:underline">
➕ Add another notification email
</button>

</div>


<!-- Screening Questions -->
<div class="bg-gray-200 p-6 rounded" x-data="{ questions: <?= htmlspecialchars(!empty($job['screening_questions']) ? $job['screening_questions'] : '[]') ?> }">

<p class="font-semibold mb-1">
(optional) Add questions that candidates should answer
</p>

<p class="text-sm text-gray-600 mb-4">
Create a screening form to reject candidates automatically.
</p>

<template x-for="(q,i) in questions" :key="i">

<div class="bg-white border rounded p-4 mb-4">

<div class="flex justify-between mb-3">

<p class="font-medium">Question</p>

<button type="button" @click="questions.splice(i,1)"
 class="text-red-500 text-sm">
Remove
</button>

</div>

<input class="w-full border rounded px-3 py-2 mb-4"
       placeholder="Write your Yes or No question here"
       x-model="q.text"
       :name="'questions['+i+'][text]'">

<div class="flex gap-6">

<label class="flex gap-2 items-center">
<input type="radio" :name="'questions['+i+'][answer]'" value="yes" x-model="q.answer"> Must answer Yes
</label>

<label class="flex gap-2 items-center">
<input type="radio" :name="'questions['+i+'][answer]'" value="no" x-model="q.answer"> Must answer No
</label>

</div>

</div>

</template>

<button type="button" @click="questions.push({text:'', answer:''})"
 class="text-sm text-red-500 hover:underline">
➕ Add a screening question
</button>

</div>


<!-- Buttons -->
<div class="flex justify-between mt-10">

<a @click="step=3"
 class="cursor-pointer text-[#f3b3ac]">
← Previous
</a>

<button type="submit"
 class="bg-[#f3b3ac] text-white px-12 py-3 rounded">
<?= isset($job) ? 'Update Listing' : 'Create Listing' ?>
</button>

</div>

</div>

<!-- STEP 5 : Confirmation (Optional placeholder, if we want to show success message before redirect, but standard form submit redirects) -->
<div x-show="step===5" x-cloak>
    <div class="text-center py-20">
        <h2 class="text-2xl font-bold mb-4">Submitting...</h2>
    </div>
</div>

</form>

</div>
</div>

</body>
</html>
