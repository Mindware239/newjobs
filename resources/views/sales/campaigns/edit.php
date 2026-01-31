<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Edit Campaign</h1>
            <p class="text-slate-500 dark:text-slate-400">Update campaign details and settings</p>
        </div>
        <a href="/sales/manager/campaigns" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 flex items-center gap-2 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Campaigns
        </a>
    </div>

    <form action="/sales/manager/campaigns/<?= $campaign['id'] ?>/update" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <!-- Left Column: Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Details Card -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Campaign Details
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Campaign Name</label>
                        <input type="text" name="name" required value="<?= htmlspecialchars($campaign['name']) ?>" placeholder="e.g. Q1 Sales Drive 2026" class="w-full bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Campaign Type</label>
                        <select name="type" class="w-full bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                            <option value="Email" <?= $campaign['type'] === 'Email' ? 'selected' : '' ?>>Email Marketing</option>
                            <option value="Social" <?= $campaign['type'] === 'Social' ? 'selected' : '' ?>>Social Media</option>
                            <option value="PPC" <?= $campaign['type'] === 'PPC' ? 'selected' : '' ?>>PPC / Ads</option>
                            <option value="Webinar" <?= $campaign['type'] === 'Webinar' ? 'selected' : '' ?>>Webinar / Event</option>
                            <option value="Cold Call" <?= $campaign['type'] === 'Cold Call' ? 'selected' : '' ?>>Cold Outreach</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                        <select name="status" class="w-full bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                            <option value="Draft" <?= $campaign['status'] === 'Draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="Scheduled" <?= $campaign['status'] === 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                            <option value="Active" <?= $campaign['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                            <option value="Paused" <?= $campaign['status'] === 'Paused' ? 'selected' : '' ?>>Paused</option>
                            <option value="Completed" <?= $campaign['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description / Brief</label>
                        <textarea name="description" rows="4" placeholder="Describe the goals and details of this campaign..." class="w-full bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white"><?= htmlspecialchars($campaign['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Targeting & Content Card -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Targeting & Channels
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Target Audience</label>
                        <input type="text" name="audience" value="<?= htmlspecialchars($campaign['audience'] ?? '') ?>" placeholder="e.g. CTOs in FinTech" class="w-full bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Specific Channel</label>
                        <input type="text" name="channel" value="<?= htmlspecialchars($campaign['channel'] ?? '') ?>" placeholder="e.g. LinkedIn Ads" class="w-full bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Schedule & Budget -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 018 0z" />
                    </svg>
                    Schedule
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Start Date</label>
                        <input type="date" name="start_date" value="<?= $campaign['start_date'] ?? '' ?>" class="w-full bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">End Date</label>
                        <input type="date" name="end_date" value="<?= $campaign['end_date'] ?? '' ?>" class="w-full bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Budget
                </h3>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Total Budget (₹)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-slate-500 dark:text-slate-400">₹</span>
                        <input type="number" step="0.01" name="budget" value="<?= $campaign['budget'] ?? '' ?>" placeholder="0.00" class="w-full bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg pl-8 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Estimated reach: ~5k - 10k users</p>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Expected Revenue</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-slate-500 dark:text-slate-400">₹</span>
                            <input type="number" step="0.01" name="expected_revenue" value="<?= $campaign['expected_revenue'] ?? '' ?>" placeholder="0.00" class="w-full bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg pl-8 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Expected Leads</label>
                        <input type="number" name="expected_leads" value="<?= $campaign['expected_leads'] ?? '' ?>" placeholder="0" class="w-full bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/20 transition-all transform hover:scale-[1.02]">
                    Update Campaign
                </button>
                <a href="/sales/manager/campaigns" class="w-full bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 px-4 py-3 rounded-xl text-sm font-medium border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-center transition-colors">
                    Cancel
                </a>
            </div>
    </form>
    
    <div class="mt-8 border-t border-slate-200 dark:border-slate-700 pt-6">
        <form action="/sales/manager/campaigns/<?= $campaign['id'] ?>/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this campaign? This action cannot be undone.');">
            <button type="submit" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete Campaign
            </button>
        </form>
    </div>
</div>
