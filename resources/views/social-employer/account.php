<?php $base = $base ?? '/'; ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Account Profile</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-white">

<header class="border-b">
<div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
<div class="flex-shrink-0">
            <a href="<?php echo $base; ?>">
                <img src="<?php echo $base; ?>uploads/Mindware-infotech.png" alt="Logo" class="h-9 sm:h-11 md:h-14 lg:h-16 w-auto">
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

<div class="bg-black text-white">
<div class="max-w-7xl mx-auto px-6 py-3 flex gap-6 text-sm">
<span>Back to Home</span>
<span>Pricing</span>
<span>Hiring insights</span>
<span>About us</span>
<span>Get Help</span>
</div>
</div>

<?php
$isEdit = isset($_GET['edit']);
$isNew  = empty($profile);
?>
<div class="max-w-7xl mx-auto px-10 py-14" 
     x-data="{
        isEditing: <?= (($isNew || $isEdit) ? 'true' : 'false') ?>,
        saving: false,
        error: '',
        success: '',
        form: {
          full_name: '<?= htmlspecialchars($profile['full_name'] ?? '') ?>',
          preferred_name: '<?= htmlspecialchars($profile['preferred_name'] ?? '') ?>',
          pronouns: '<?= htmlspecialchars($profile['pronouns'] ?? '') ?>',
          prefix: '<?= htmlspecialchars($profile['prefix'] ?? '') ?>',
          first_name: '<?= htmlspecialchars($profile['first_name'] ?? '') ?>',
          middle_name: '<?= htmlspecialchars($profile['middle_name'] ?? '') ?>',
          last_name: '<?= htmlspecialchars($profile['last_name'] ?? '') ?>',
          suffix: '<?= htmlspecialchars($profile['suffix'] ?? '') ?>'
        },
        async save() {
          this.saving = true;
          this.error = '';
          this.success = '';
          const url = '<?= $isNew ? '/social-employer/account-save' : '/social-employer/account-update' ?>';
          try {
            const res = await fetch(url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>'
              },
              body: JSON.stringify(this.form)
            });
            const data = await res.json();
            if (!data.success) {
              this.error = data.error || 'Save failed';
            } else {
              this.success = 'Saved';
              this.isEditing = false;
            }
          } catch (e) {
            this.error = 'Network error';
          } finally {
            this.saving = false;
          }
        }
     }">

<!-- ================================================= -->
<!-- ================= FORM ========================== -->
<!-- ================================================= -->

<div x-show="isEditing">

<form action="<?= $isNew ? '/social-employer/account-save' : '/social-employer/account-update' ?>" method="POST" @submit.prevent="save">
<input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">


<div class="flex justify-between mb-8">

<button type="button" @click="isEditing=false" class="text-red-500 text-sm">Cancel</button>

<button type="button" @click="save" class="bg-red-500 hover:bg-red-600 text-white px-20 py-3 rounded" :disabled="saving">
Save
</button>

</div>

<template x-if="error">
  <div class="mb-4 text-red-600" x-text="error"></div>
</template>
<template x-if="success">
  <div class="mb-4 text-green-600" x-text="success"></div>
</template>

<div class="grid grid-cols-3 gap-6 mb-8">

<div class="col-span-2">
<label class="font-semibold text-sm">Your full name *</label>
<input name="full_name" required x-model="form.full_name" class="w-full border rounded px-3 py-2">
</div>

<div>
<label class="font-semibold text-sm">Your preferred name *</label>
<input name="preferred_name" required x-model="form.preferred_name" class="w-full border rounded px-3 py-2">
</div>

<div>
<label class="font-semibold text-sm">Pronouns</label>
<select name="pronouns" class="w-full border rounded px-3 py-2" x-model="form.pronouns">
<option value="">Select</option>
<option>she_her_hers</option>
<option>he_him</option>
<option>they_them</option>
</select>
</div>

</div>

<div class="grid grid-cols-5 gap-4 mb-16">

<div>
<label>Prefix</label>
<select name="prefix" class="w-full border rounded px-2 py-2" x-model="form.prefix">
<option></option>
<option>Mr.</option>
<option>Ms.</option>
<option>Mrs.</option>
<option>Dr.</option>
</select>
</div>

<div>
<label>First name *</label>
<input name="first_name" required x-model="form.first_name" class="w-full border rounded px-2 py-2">
</div>

<div>
<label>Middle name</label>
<input name="middle_name" x-model="form.middle_name" class="w-full border rounded px-2 py-2">
</div>

<div>
<label>Last name *</label>
<input name="last_name" required x-model="form.last_name" class="w-full border rounded px-2 py-2">
</div>

<div>
<label>Suffix</label>
<select name="suffix" class="w-full border rounded px-2 py-2" x-model="form.suffix">
<option></option>
<option>Sr</option>
<option>Jr</option>
</select>
</div>

</div>

<p class="text-sm text-gray-600">
Your name is not shared with candidates.
</p>

</form>

</div>

<div x-show="!isEditing">
<!-- ================================================= -->
<!-- ================= PROFILE VIEW ================= -->
<!-- ================================================= -->

<div class="flex justify-between items-center mb-12">

<div>
<h2 class="font-semibold">
<span x-text="'Hello, ' + (form.preferred_name || 'there') + ' 👋'"></span>
</h2>
<p class="text-gray-600">Your profile details</p>
</div>

<button type="button" @click="isEditing=true" class="bg-red-500 hover:bg-red-600 text-white px-12 py-3 rounded">
Edit profile
</button>

</div>

<div class="grid grid-cols-3 gap-24 mb-16">

<div>
<p class="text-gray-600 text-sm mb-1">Full name</p>
<p class="font-medium" x-text="form.full_name"></p>
</div>

<div>
<p class="text-gray-600 text-sm mb-1">Preferred name</p>
<p class="font-medium" x-text="form.preferred_name"></p>
</div>

<div>
<p class="text-gray-600 text-sm mb-1">Pronouns</p>
<p class="font-medium" x-text="form.pronouns"></p>
</div>

</div>

<p class="text-sm text-gray-600 mt-20">
Your name is not shared with candidates.
</p>

</div>

</body>
</html>
