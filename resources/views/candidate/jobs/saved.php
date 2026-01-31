<?php
$candidate = $candidate ?? null;
$user = $candidate ? $candidate->user() : null;
$userEmail = $user ? $user->attributes['email'] ?? '' : '';
$unreadMessages = $unreadMessages ?? 0;
$unreadNotifications = $unreadNotifications ?? 0;
ob_start();
?>
<div x-data="savedJobsPage()" x-cloak>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">My Jobs</h1>
            <p class="text-gray-600 mt-1">Manage your saved jobs and applications</p>
        </div>
        
        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-8">
            <nav class="flex space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'saved'"
                   :class="activeTab === 'saved' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm relative transition-colors">
                    Saved Jobs
                    <span class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium" 
                          :class="activeTab === 'saved' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'">
                        <?= count($savedJobs ?? []) ?>
                    </span>
                </button>
                <button @click="activeTab = 'applied'"
                   :class="activeTab === 'applied' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Applied
                    <span class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium" 
                          :class="activeTab === 'applied' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'">
                        <?= count($appliedJobs ?? []) ?>    
                    </span>
                </button>
                <a href="/candidate/interviews" 
                   class="whitespace-nowrap py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm transition-colors group">
                    Interviews
                    <span class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 group-hover:bg-gray-200">
                        <?= count($interviewJobs ?? []) ?>
                    </span>
                    <svg class="w-4 h-4 inline-block ml-1 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                </a>
            </nav>
        </div>

        <!-- Saved Jobs Tab -->
        <div x-show="activeTab === 'saved'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <?php if (empty($savedJobs)): ?>
            <!-- Empty State -->
            <div class="text-center py-16 bg-white rounded-2xl border border-dashed border-gray-300">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">No saved jobs yet</h2>
                <p class="text-gray-500 mb-8 max-w-sm mx-auto">Jobs you save will appear here so you can easily find and apply to them later.</p>
                <a href="/candidate/jobs" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                    Browse Jobs
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
            </div>
            <?php else: ?>
            <!-- Saved Jobs Grid -->
            <div class="grid grid-cols-1 gap-6" x-ref="savedList">
                <?php foreach ($savedJobs as $job): ?>
                <div class="group bg-white border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-all duration-300 relative" data-id="<?= $job['id'] ?>">
                    <div class="flex items-start gap-5">
                        <!-- Logo -->
                        <div class="flex-shrink-0">
                            <?php if (!empty($job['company_logo'])): ?>
                                <img src="<?= htmlspecialchars($job['company_logo']) ?>" 
                                     alt="<?= htmlspecialchars($job['company_name'] ?? '') ?>"
                                     class="w-16 h-16 rounded-lg object-contain bg-white border border-gray-100 p-1">
                            <?php else: ?>
                                <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 flex items-center justify-center text-gray-500 font-bold text-2xl">
                                    <?= strtoupper(substr($job['company_name'] ?? 'C', 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors mb-1">       
                                        <a href="/candidate/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id']) ?>">
                                            <?= htmlspecialchars($job['title'] ?? 'Job Title') ?>
                                        </a>
                                    </h3>
                                    <p class="text-gray-600 font-medium"><?= htmlspecialchars($job['company_name'] ?? 'Company') ?></p>
                                </div>
                                <button @click="removeBookmark(<?= $job['id'] ?>)" 
                                        class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Remove from saved">
                                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                                        <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="flex flex-wrap items-center gap-y-2 gap-x-4 mt-3 text-sm text-gray-500">
                                <?php if (!empty($job['location_display'])): ?>
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <?= htmlspecialchars($job['location_display']) ?>
                                </span>
                                <?php endif; ?>
                                
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <?= htmlspecialchars($job['employment_type_display'] ?? 'Full-time') ?>
                                </span>

                                <?php if (!empty($job['salary_display'])): ?>
                                <span class="flex items-center gap-1.5 px-2 py-0.5 bg-blue-50 text-blue-700 rounded font-medium">   
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <?= htmlspecialchars($job['salary_display']) ?>
                                </span>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center gap-3 mt-5 pt-4 border-t border-gray-100">
                                <a href="/candidate/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id']) ?>" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">
                                    View Details
                                </a>
                                <?php if (isset($applications[$job['id']])): ?>
                                <span class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 text-sm font-medium rounded-lg border border-blue-100">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Applied
                                </span>
                                <?php else: ?>
                                <a href="/candidate/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id']) ?>/apply" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">
                                    Apply Now
                                </a>
                                <?php endif; ?>
                                <span class="text-xs text-gray-400 ml-auto">
                                    Saved <?= date('M d, Y', strtotime($job['saved_at'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Applied Jobs Tab -->
        <div x-show="activeTab === 'applied'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
            <?php if (empty($appliedJobs)): ?>
            <div class="text-center py-16 bg-white rounded-2xl border border-dashed border-gray-300">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">No applications yet</h2>
                <p class="text-gray-500 mb-8 max-w-sm mx-auto">Track your applications here. Start applying to jobs to see them listed.</p>
                <a href="/candidate/jobs" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                    Find a Job
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </a>
            </div>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($appliedJobs as $job): ?>
                <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <?php if (!empty($job['company_logo'])): ?>
                                <img src="<?= htmlspecialchars($job['company_logo']) ?>" class="w-12 h-12 rounded-lg object-contain bg-gray-50 border border-gray-100 p-1">
                            <?php else: ?>
                                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 font-bold border border-blue-100">
                                    <?= strtoupper(substr($job['company_name'] ?? 'C', 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h3 class="font-bold text-gray-900">
                                    <a href="/candidate/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id']) ?>" class="hover:text-blue-600 transition">
                                        <?= htmlspecialchars($job['title'] ?? 'Job Title') ?>
                                    </a>
                                </h3>
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($job['company_name'] ?? 'Company') ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="px-3 py-1 bg-blue-50 text-blue-700 text-sm font-semibold rounded-lg border border-blue-100 capitalize">
                                <?= $applications[$job['id']] ?? 'applied' ?>
                            </span>
                            <a href="/candidate/jobs/<?= htmlspecialchars($job['slug'] ?? $job['id']) ?>" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('savedJobsPage', () => ({
        activeTab: 'saved',
        async removeBookmark(jobId) {
            if(!confirm('Are you sure you want to remove this job from saved?')) return;
            
            try {
                const response = await fetch(`/candidate/jobs/${jobId}/bookmark`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });
                const data = await response.json();
                
                // Remove element from DOM without reload
                const el = this.$refs.savedList.querySelector(`[data-id="${jobId}"]`);
                if (el) {
                    el.style.opacity = '0';
                    setTimeout(() => {
                        el.remove();
                        // Optional: Check if empty and show empty state (would require more logic, reload is safer for now if list becomes empty, but let's just leave it)
                        // Or reload if list is empty
                        if (this.$refs.savedList.children.length === 0) {
                            location.reload();
                        }
                    }, 300);
                }
            } catch (error) {
                console.error('Error removing bookmark:', error);
                alert('Failed to remove bookmark');
            }
        }
    }));
});
</script>

<?php
$content = ob_get_clean();
$title = 'My Jobs';
require __DIR__ . '/../layout.php';
?>