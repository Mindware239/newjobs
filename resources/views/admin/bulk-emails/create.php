<?php
$title = $title ?? 'Create Campaign';
?>
<div class="space-y-6">
    <div class="flex items-center space-x-2">
        <a href="/admin/marketing/campaigns" class="text-muted-foreground hover:text-foreground">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <h1 class="text-2xl font-bold tracking-tight"><?= htmlspecialchars($title) ?></h1>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="rounded-md border bg-card text-card-foreground shadow-sm">
                <div class="p-6">
                    <form id="campaignForm" method="post" action="/admin/marketing/campaigns" class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="subject">
                                Email Subject
                            </label>
                            <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" 
                                id="subject" name="subject" required placeholder="e.g., Special Offer for Premium Candidates">
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="body_html">
                                Email Body (HTML)
                            </label>
                            <textarea class="flex min-h-[300px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" 
                                id="body_html" name="body_html" required placeholder="<h1>Hello {user_name},</h1><p>Your message here...</p>"></textarea>
                            <p class="text-xs text-muted-foreground">Supported variables: {user_name}</p>
                        </div>

                        <!-- Filters Section -->
                        <div class="space-y-4 pt-4 border-t">
                            <h3 class="text-lg font-medium">Target Audience Filters</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium leading-none" for="role">User Role</label>
                                    <select class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" 
                                        id="role" name="filters[role]">
                                        <option value="candidate">Candidates</option>
                                        <option value="employer">Employers</option>
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium leading-none" for="subscription_status">Subscription</label>
                                    <select class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" 
                                        id="subscription_status" name="filters[subscription_status]">
                                        <option value="">All</option>
                                        <option value="premium">Premium / Paid</option>
                                        <option value="free">Free</option>
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium leading-none" for="active_within_days">Active Within</label>
                                    <select class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2" 
                                        id="active_within_days" name="filters[active_within_days]">
                                        <option value="">Any time</option>
                                        <option value="7">Last 7 Days</option>
                                        <option value="30">Last 30 Days</option>
                                        <option value="90">Last 90 Days</option>
                                    </select>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium leading-none" for="location">Location (City)</label>
                                    <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" 
                                        id="location" name="filters[location]" placeholder="e.g. Mumbai">
                                </div>

                                <!-- Candidate Specific Filters -->
                                <div class="space-y-2 candidate-filter">
                                    <label class="text-sm font-medium leading-none" for="skills">Skills (Comma separated)</label>
                                    <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" 
                                        id="skills" name="filters[skills]" placeholder="e.g. PHP, React">
                                </div>

                                <div class="space-y-2 candidate-filter">
                                    <label class="text-sm font-medium leading-none" for="experience_min">Min Experience (Years)</label>
                                    <input type="number" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" 
                                        id="experience_min" name="filters[experience_min]" min="0">
                                </div>
                            </div>
                        </div>

                        <!-- Channels -->
                        <div class="space-y-4 pt-4 border-t">
                            <h3 class="text-lg font-medium">Channels</h3>
                            <p class="text-xs text-muted-foreground">Choose where to send. Default is Email.</p>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="channels[]" value="email" checked>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-blue-500 text-white">Email</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="channels[]" value="push">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-purple-500 text-white">Push</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="channels[]" value="whatsapp">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-green-500 text-white">WhatsApp</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="channels[]" value="in_app">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-amber-500 text-white">In-App</span>
                                </label>
                            </div>
                        </div>

                        <!-- Manual Selection -->
                        <div class="space-y-2 pt-4 border-t">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium">Manual Selection (optional)</h3>
                                <label class="inline-flex items-center text-sm">
                                    <input type="checkbox" id="selectAll" class="mr-2">
                                    Select All
                                </label>
                            </div>
                            <p class="text-xs text-muted-foreground">Tick to send to specific users (overrides filters)</p>
                            <form method="GET" class="flex items-center gap-3">
                                <select name="role" class="flex h-9 w-44 rounded-md border border-input bg-background px-3 py-2 text-sm">
                                    <option value="candidate" <?= ($role ?? 'candidate') === 'candidate' ? 'selected' : '' ?>>Candidates</option>
                                    <option value="employer" <?= ($role ?? '') === 'employer' ? 'selected' : '' ?>>Employers</option>
                                </select>
                                <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search email or name" class="flex h-9 w-[24rem] rounded-md border border-input bg-background px-3 py-2 text-sm">
                                <select name="per_page" class="flex h-9 w-28 rounded-md border border-input bg-background px-3 py-2 text-sm">
                                    <?php $pp = (int)($perPage ?? 20); foreach ([10,20,30,50] as $opt): ?>
                                        <option value="<?= $opt ?>" <?= $pp === $opt ? 'selected' : '' ?>><?= $opt ?>/page</option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-secondary text-secondary-foreground hover:bg-secondary/80 h-9 px-4">Apply</button>
                            </form>
                            <div class="mt-3 max-h-64 overflow-auto border rounded">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-muted">
                                            <th class="p-2 w-10"></th>
                                            <th class="p-2 text-left">Email</th>
                                            <th class="p-2 text-left">Name</th>
                                            <th class="p-2 text-left">Role</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (($usersList ?? []) as $u): ?>
                                            <tr class="border-b">
                                                <td class="p-2"><input type="checkbox" name="selected_user_ids[]" value="<?= (int)$u['id'] ?>"></td>
                                                <td class="p-2"><?= htmlspecialchars((string)$u['email']) ?></td>
                                                <td class="p-2"><?= htmlspecialchars((string)($u['first_name'] ?? '')) ?></td>
                                                <td class="p-2"><?= htmlspecialchars((string)$u['role']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($usersList ?? [])): ?>
                                            <tr><td colspan="4" class="p-3 text-center text-muted-foreground">No users available</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php 
                                $totalPages = isset($total, $perPage) && $perPage > 0 ? (int)ceil($total / $perPage) : 1; 
                                $cur = (int)($page ?? 1); 
                            ?>
                            <?php if ($totalPages > 1): ?>
                            <div class="mt-2 flex items-center justify-between">
                                <div class="text-xs text-muted-foreground">
                                    Showing <?= (($cur - 1) * $perPage) + 1 ?> to <?= min($cur * $perPage, $total) ?> of <?= $total ?> users
                                </div>
                                <div class="flex items-center gap-2">
                                    <?php if ($cur > 1): ?>
                                        <a href="?page=<?= $cur - 1 ?>&per_page=<?= $perPage ?>&role=<?= urlencode($role ?? 'candidate') ?>&search=<?= urlencode($search ?? '') ?>" class="px-3 py-1 border rounded hover:bg-muted">Prev</a>
                                    <?php endif; ?>
                                    <?php 
                                        $start = max(1, $cur - 2);
                                        $end = min($totalPages, $cur + 2);
                                        for ($p = $start; $p <= $end; $p++):
                                    ?>
                                        <a href="?page=<?= $p ?>&per_page=<?= $perPage ?>&role=<?= urlencode($role ?? 'candidate') ?>&search=<?= urlencode($search ?? '') ?>" class="px-3 py-1 border rounded <?= $p === $cur ? 'bg-muted' : 'hover:bg-muted' ?>"><?= $p ?></a>
                                    <?php endfor; ?>
                                    <?php if ($cur < $totalPages): ?>
                                        <a href="?page=<?= $cur + 1 ?>&per_page=<?= $perPage ?>&role=<?= urlencode($role ?? 'candidate') ?>&search=<?= urlencode($search ?? '') ?>" class="px-3 py-1 border rounded hover:bg-muted">Next</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="submit" id="submitBtn" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                                Send Campaign
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="rounded-md border bg-card text-card-foreground shadow-sm">
                <div class="p-6">
                    <h3 class="font-semibold leading-none tracking-tight mb-4">Tips</h3>
                    <ul class="text-sm text-muted-foreground list-disc list-inside space-y-2">
                        <li>Use segmentation to target the right audience.</li>
                        <li>Personalize your message with <code>{user_name}</code>.</li>
                        <li>Keep subject lines concise and engaging.</li>
                        <li>Campaigns are processed in the background.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('role').addEventListener('change', function(e) {
    const isCandidate = e.target.value === 'candidate';
    document.querySelectorAll('.candidate-filter').forEach(el => {
        el.style.display = isCandidate ? 'block' : 'none';
    });
});

document.getElementById('campaignForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerText = 'Sending...';

    const formData = new FormData(this);
    const data = {};
    formData.forEach((value, key) => {
        // Handle nested filters
        if (key.startsWith('filters[')) {
            const filterKey = key.match(/filters\[(.*?)\]/)[1];
            if (!data.filters) data.filters = {};
            data.filters[filterKey] = value;
        } else {
            if (key === 'channels[]') {
                if (!data.channels) data.channels = [];
                data.channels.push(value);
            } else {
                data[key] = value;
            }
        }
    });
    // Manual selected IDs
    const selectedIds = [];
    document.querySelectorAll('input[name=\"selected_user_ids[]\"]:checked').forEach(cb => selectedIds.push(parseInt(cb.value, 10)));
    if (selectedIds.length > 0) {
        data.selected_user_ids = selectedIds;
    }

    fetch(this.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Campaign sent successfully!');
            window.location.href = '/admin/marketing/campaigns';
        } else {
            alert('Error: ' + (result.error || 'Unknown error'));
            btn.disabled = false;
            btn.innerText = 'Send Campaign';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
        btn.disabled = false;
        btn.innerText = 'Send Campaign';
    });
});

document.getElementById('selectAll').addEventListener('change', function(e) {
    const checked = e.target.checked;
    document.querySelectorAll('input[name=\"selected_user_ids[]\"]').forEach(cb => cb.checked = checked);
});
</script>
