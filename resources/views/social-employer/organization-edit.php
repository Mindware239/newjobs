<?php $base = $base ?? '/'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Organization</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-800">
    <!-- HEADER -->
    <header class="border-b">
      <div class="max-w-[1200px] mx-auto px-6 py-4 flex justify-between items-center">
        <div class="flex-shrink-0">
            <a href="<?= $base ?>">
                <img src="<?= $base ?>uploads/Mindware-infotech.png" alt="Logo" class="h-10 md:h-14 w-auto">
            </a>
        </div>
        <nav class="flex gap-6 text-sm text-gray-700">
          <a href="/social-employer/newlisting">＋ Post a job</a>
          <a href="/social-employer/listings" class="text-red-500">Job listings</a>
          <a href="/social-employer/application">Applications</a>
          <a href="/social-employer/organisation" class="text-red-500">Organizations & users</a>
          <a href="/social-employer/account" class="text-red-500">Account & profile</a>
          <a href="/logout">Logout</a>
        </nav>
      </div>
    </header>
    <!-- BLACK BAR -->
    <nav class="bg-black text-white">
      <div class="max-w-[1200px] mx-auto px-6 py-3 flex gap-6 text-sm">
        <a href="/" class="hover:underline">◀ Back to Home</a>
        <a href="/pricing" class="hover:underline">Pricing</a>
        <a href="/blog" class="hover:underline">Hiring insights</a>
        <a href="/aboutus" class="hover:underline">About us</a>
        <a href="/supports" class="hover:underline">Get Help</a>
      </div>
    </nav>
    <div class="max-w-3xl mx-auto p-6">
        <div class="mb-4">
            <a href="/social-employer/organisation" class="text-indigo-600 text-sm">&larr; Back to Organizations</a>
        </div>
        <div class="bg-white border rounded-xl shadow-sm p-6">
            <h1 class="text-xl font-semibold mb-4">Edit Organization</h1>
            <form method="post" action="/social/organizations/<?= (int)($org['id'] ?? 0) ?>/update" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium">Organization Name</label>
                        <input name="organization_name" value="<?= htmlspecialchars((string)($org['organization_name'] ?? '')) ?>" class="mt-1 w-full border rounded p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Acronyms</label>
                        <input name="acronyms" value="<?= htmlspecialchars((string)($org['acronyms'] ?? '')) ?>" class="mt-1 w-full border rounded p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Organization Type</label>
                        <input name="organization_type" value="<?= htmlspecialchars((string)($org['organization_type'] ?? '')) ?>" class="mt-1 w-full border rounded p-2">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_agency" value="1" <?= ((int)($org['is_agency'] ?? 0) === 1) ? 'checked' : '' ?>>
                        <label class="text-sm">Is Agency</label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Website</label>
                        <input name="website" value="<?= htmlspecialchars((string)($org['website'] ?? '')) ?>" class="mt-1 w-full border rounded p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">EIN</label>
                        <input name="ein" value="<?= htmlspecialchars((string)($org['ein'] ?? '')) ?>" class="mt-1 w-full border rounded p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Staff Count</label>
                        <input type="number" name="staff_count" value="<?= (int)($org['staff_count'] ?? 0) ?>" class="mt-1 w-full border rounded p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Mission Focus</label>
                        <input name="mission_focus" value="<?= htmlspecialchars((string)($org['mission_focus'] ?? '')) ?>" class="mt-1 w-full border rounded p-2">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium">Mission</label>
                        <textarea name="mission" class="mt-1 w-full border rounded p-2" rows="3"><?= htmlspecialchars((string)($org['mission'] ?? '')) ?></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium">Impact</label>
                        <textarea name="impact" class="mt-1 w-full border rounded p-2" rows="3"><?= htmlspecialchars((string)($org['impact'] ?? '')) ?></textarea>
                    </div>
                </div>
                <div class="pt-2">
                    <label class="block text-sm font-medium mb-1">Logo</label>
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 border rounded flex items-center justify-center bg-white">
                            <?php $logo = (string)($org['logo_url'] ?? ''); ?>
                            <?php if ($logo): ?>
                                <img src="<?= htmlspecialchars($logo) ?>" class="max-w-full max-h-full object-contain" alt="Logo" onerror="this.onerror=null;this.src='/uploads/mindware-infotechlogo.png'">
                            <?php else: ?>
                                <span class="text-xs text-gray-500">No logo</span>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="logo" accept=".png,.jpg,.jpeg">
                    </div>
                </div>
                <div class="pt-4">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
