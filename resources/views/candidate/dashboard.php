<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Candidate Dashboard - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <script>
        // Define dashboard function before Alpine.js loads
        function dashboard() {
            return {
                recommendedJobs: <?= json_encode($jobs ?? []) ?>,
                applications: <?= json_encode($applications ?? []) ?>,
                bookmarkedJobs: <?= json_encode($bookmarkedJobs ?? []) ?>,
                recentViews: <?= json_encode($recentViews ?? []) ?>,
                stats: {
                    applications: <?= $stats['applications'] ?? count($applications ?? []) ?>,
                    shortlisted: <?= $stats['shortlisted'] ?? count(array_filter($applications ?? [], fn($a) => strtolower($a['status'] ?? '') === 'shortlisted')) ?>,
                    interviews: <?= $stats['interviews'] ?? count(array_filter($applications ?? [], fn($a) => strtolower($a['status'] ?? '') === 'interview')) ?>,
                    hired: <?= $stats['hired'] ?? count(array_filter($applications ?? [], fn($a) => strtolower($a['status'] ?? '') === 'hired')) ?>,
                    profile_views: <?= $stats['profile_views'] ?? (int)($candidate->attributes['profile_views'] ?? 0) ?>,
                    saved: <?= count($bookmarkedJobs ?? []) ?>
                },
                autoApplyEnabled: <?= ((int)($candidate->attributes['auto_apply_enabled'] ?? 0)) === 1 ? 'true' : 'false' ?>,
                async bookmarkJob(jobId) {
                    try {
                        const response = await fetch(`/candidate/jobs/${jobId}/bookmark`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-Token': this.getCsrfToken()
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            const jobIndex = this.recommendedJobs.findIndex(j => j.id === jobId);
                            if (jobIndex > -1) {
                                const job = this.recommendedJobs[jobIndex];
                                job.is_bookmarked = !job.is_bookmarked;
                                if (job.is_bookmarked) {
                                    if (!this.bookmarkedJobs.find(j => j.id === jobId)) {
                                        this.bookmarkedJobs.unshift(job);
                                    }
                                } else {
                                    this.bookmarkedJobs = this.bookmarkedJobs.filter(j => j.id !== jobId);
                                }
                            }
                        }
                    } catch (error) {
                        console.error('Bookmark error:', error);
                    }
                },
                async saveAutoApply() {
                    try {
                        const payload = {
                            section: 'auto_apply',
                            auto_apply_enabled: this.autoApplyEnabled ? 1 : 0
                        };
                        const res = await fetch('/candidate/profile/save', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': this.getCsrfToken()
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if (data && data.success) {
                            alert('Auto-apply opt-in saved');
                        } else {
                            alert('Failed to save settings');
                        }
                    } catch (e) {
                        alert('Error saving settings');
                    }
                },
                getCsrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.content || '';
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.1);
        }
        .job-card {
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }
        .job-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.1);
            border-color: #bfdbfe;
        }
        .btn-primary {
            background: #2563eb;
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
        }
        .btn-primary:hover {
            background: #1d4ed8;
            box-shadow: 0 6px 12px rgba(37, 99, 235, 0.3);
            transform: translateY(-2px);
        }
        .btn-secondary {
            transition: all 0.3s ease;
            border: 1.5px solid #e5e7eb;
        }
        .btn-secondary:hover {
            border-color: #2563eb;
            background: #eff6ff;
            color: #2563eb;
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div x-data="dashboard()" x-cloak>
        <?php $base = $base ?? '/'; require __DIR__ . '/../include/header.php'; ?>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Welcome Message -->
            <?php 
            $candidateName = $candidate->attributes['full_name'] ?? null;
            $user = $candidate->user();
            if (empty($candidateName) && $user) {
                // Try to get name from user's Google/Apple data
                $candidateName = $user->attributes['google_name'] ?? $user->attributes['apple_name'] ?? null;
            }
            $displayName = $candidateName ?: 'User';
            $userEmail = $user->attributes['email'] ?? '';
            ?>
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-8 text-gray-900">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl sm:text-4xl font-bold mb-2">
                            Welcome back, <?= htmlspecialchars($displayName) ?>!
                        </h1>
                        <p class="text-gray-600 text-lg">
                            <?= !empty($userEmail) ? htmlspecialchars($userEmail) : 'Here’s what’s happening with your job search' ?>
                        </p>
                    </div>
                    <a href="/candidate/jobs" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Find Jobs
                    </a>
                </div>
            </div>
            
            <style>
                @keyframes wave {
                    0% { transform: rotate(0.0deg) }
                    10% { transform: rotate(14.0deg) }
                    20% { transform: rotate(-8.0deg) }
                    30% { transform: rotate(14.0deg) }
                    40% { transform: rotate(-4.0deg) }
                    50% { transform: rotate(10.0deg) }
                    60% { transform: rotate(0.0deg) }
                    100% { transform: rotate(0.0deg) }
                }
                .animate-wave { animation: wave 2.5s infinite; }
            </style>
            
            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
                <a href="/candidate/applications" class="block rounded-xl border border-gray-200 bg-white p-4 md:p-5 stat-card">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-2xl font-bold text-gray-900" x-text="stats.applications"></div>
                            <div class="text-sm text-gray-600">Applications</div>
                        </div>
                    </div>
                </a>
                <a href="/candidate/interviews" class="block rounded-xl border border-gray-200 bg-white p-4 md:p-5 stat-card">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-2xl font-bold text-gray-900" x-text="stats.interviews"></div>
                            <div class="text-sm text-gray-600">Interviews</div>
                        </div>
                    </div>
                </a>
                <div class="rounded-xl border border-gray-200 bg-white p-4 md:p-5 stat-card">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-2xl font-bold text-gray-900" x-text="stats.profile_views"></div>
                            <div class="text-sm text-gray-600">Profile Views</div>
                        </div>
                    </div>
                </div>
                <a href="/candidate/jobs/saved" class="block rounded-xl border border-gray-200 bg-white p-4 md:p-5 stat-card">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-2xl font-bold text-gray-900" x-text="stats.saved"></div>
                            <div class="text-sm text-gray-600">Saved Jobs</div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Recommended Jobs -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">Recommended Jobs for You</h2>
                            <a href="/candidate/jobs" class="text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1 transition">
                                View All
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                        <div class="space-y-4" x-show="recommendedJobs.length > 0">
                            <template x-for="job in recommendedJobs" :key="job.id">
                                <div class="border border-gray-200 rounded-xl p-5 job-card bg-white">
                                    <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                                <a :href="'/candidate/jobs/' + (job.slug || job.id)" class="hover:text-gray-700 transition" x-text="job.title"></a>
                                            </h3>
                                            <p class="text-sm text-gray-600 font-medium mb-3" x-text="job.company_name"></p>
                                            <div class="flex flex-wrap gap-2 mb-3">
                                                <span class="inline-flex items-center text-xs bg-blue-50 text-blue-700 px-3 py-1.5 rounded-full font-medium" x-text="job.location"></span>
                                                <span class="inline-flex items-center text-xs bg-blue-50 text-blue-700 px-3 py-1.5 rounded-full font-medium" 
      x-text="'Match: ' + job.match_score + '%'"></span>
                                            </div>

                                            <div class="flex items-center gap-1 text-sm font-semibold text-gray-800">
                                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span x-text="'₹' + job.salary_min + ' - ₹' + job.salary_max"></span>
                                            </div>
                                        </div>
                                        <div class="flex flex-row sm:flex-col gap-2 w-full sm:w-auto sm:min-w-[120px]">
                                            <button @click="bookmarkJob(job.id)" 
                                                    class="flex-1 sm:flex-none px-4 py-2.5 text-sm font-medium border-2 rounded-lg transition-all duration-200 flex items-center justify-center gap-2"
                                                    :class="job.is_bookmarked ? 'border-gray-800 bg-gray-100 text-gray-800 hover:bg-gray-200' : 'border-gray-300 bg-white text-gray-700 hover:border-gray-800 hover:bg-gray-50'">
                                                <svg x-show="!job.is_bookmarked" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                                </svg>
                                                <svg x-show="job.is_bookmarked" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                                </svg>
                                                <span x-show="!job.is_bookmarked">Save</span>
                                                <span x-show="job.is_bookmarked">Saved</span>
                                            </button>
                                            <a :href="'/candidate/jobs/' + (job.slug || job.id)" 
                                               class="flex-1 sm:flex-none px-4 py-2.5 text-sm font-semibold text-white rounded-lg btn-primary text-center flex items-center justify-center gap-2">
                                                View
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div x-show="recommendedJobs.length === 0" class="text-center py-8 text-gray-500">
                            <p>No recommended jobs yet. Complete your profile for better matches!</p>
                        </div>
                    </div>

                    <!-- Recent Applications -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-2xl font-bold mb-6 text-gray-900">Recent Applications</h2>
                        <div class="space-y-4" x-show="applications.length > 0">
                            <template x-for="app in applications" :key="app.id">
                                <div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition-all duration-200 bg-white">
                                    <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-gray-900 text-lg mb-1" x-text="app.job_title"></h3>
                                            <p class="text-sm text-gray-600 font-medium mb-3" x-text="app.company_name"></p>
                                            <div class="flex items-center gap-3 flex-wrap">
                                                <span class="inline-flex items-center text-xs px-3 py-1.5 rounded-full font-medium" 
                                                      :class="{
                                                          'bg-blue-100 text-blue-700': app.status === 'applied',
                                                          'bg-blue-50 text-blue-600': app.status === 'screening',
                                                          'bg-blue-200 text-blue-800': app.status === 'shortlisted',
                                                          'bg-blue-600 text-white': app.status === 'interview',
                                                          'bg-blue-900 text-white': app.status === 'hired',
                                                          'bg-blue-800 text-white': app.status === 'offer',
                                                          'bg-gray-100 text-gray-700': app.status === 'rejected'
                                                      }"
                                                      x-text="app.status_label"></span>
                                                <span class="text-xs text-gray-500 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span x-text="app.applied_at"></span>
                                                </span>
                                            </div>
                                            <!-- Interview Details -->
                                            <div x-show="app.status === 'interview' && app.interview" 
                                                 x-cloak
                                                 class="mt-3 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span class="text-sm font-semibold text-gray-900">Interview Scheduled</span>
                                                </div>
                                                <div class="space-y-1.5 text-xs text-gray-700">
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                        <span><strong>Date:</strong> <span x-text="app.interview?.date"></span></span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <span><strong>Time:</strong> <span x-text="app.interview?.start_time"></span> - <span x-text="app.interview?.end_time"></span></span>
                                                    </div>
                                                    <div x-show="app.interview?.type === 'onsite' && app.interview?.location" class="flex items-center gap-2">
                                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        </svg>
                                                        <span><strong>Location:</strong> <span x-text="app.interview?.location"></span></span>
                                                    </div>
                                                    <div x-show="app.interview?.type === 'video' && app.interview?.meeting_link" class="flex items-center gap-2">
                                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                                        </svg>
                                                        <a :href="app.interview?.meeting_link" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline">
                                                            <strong>Join Meeting:</strong> Click here
                                                        </a>
                                                    </div>
                                                    <div x-show="app.interview?.type === 'phone'" class="flex items-center gap-2">
                                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                        </svg>
                                                        <span><strong>Type:</strong> Phone Interview</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="w-full sm:w-auto">
                                            <a :href="'/candidate/jobs/' + (app.job_slug || app.job_id)" 
                                               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-800 border-2 border-gray-800 rounded-lg hover:bg-gray-800 hover:text-white transition-all duration-200">
                                                View Job
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div x-show="applications.length === 0" class="text-center py-12 text-gray-500">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="mb-4">You haven't applied to any jobs yet.</p>
                            <a href="/candidate/jobs" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-800 text-white font-semibold rounded-lg hover:bg-gray-900 transition-all duration-200 shadow-md hover:shadow-lg">
                                Browse Jobs
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                        <h3 class="font-bold text-lg text-gray-900 mb-5 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Quick Links
                        </h3>
                        <div class="space-y-3">
                            <a href="/candidate/jobs" 
                               class="group block w-full px-4 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-700 text-center font-semibold transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Search Jobs
                            </a>
                            <a href="/candidate/chat" 
                               class="group relative block w-full px-4 py-3 border-2 border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 text-center font-medium transition-all duration-200 flex items-center justify-center gap-2 text-gray-700">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                Messages
                                <?php if (isset($unreadMessages) && $unreadMessages > 0): ?>
                                <span class="absolute top-1 right-1 bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-semibold"><?= $unreadMessages ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="/candidate/profile" 
                               class="group block w-full px-4 py-3 border-2 border-gray-200 rounded-lg hover:border-blue-400 hover:bg-gray-50 text-center font-medium transition-all duration-200 flex items-center justify-center gap-2 text-gray-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                View Profile
                            </a>
                            <a href="/candidate/profile/complete?edit=1" 
                               class="group block w-full px-4 py-3 border-2 border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 text-center font-medium transition-all duration-200 flex items-center justify-center gap-2 text-gray-700">    
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h11a2 2 0 002-2v-6m-1.414-5.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Profile
                            </a>
                            <a href="/candidate/applications" 
                               class="group block w-full px-4 py-3 border-2 border-gray-200 rounded-lg hover:border-blue-400 hover:bg-gray-50 text-center font-medium transition-all duration-200 flex items-center justify-center gap-2 text-gray-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                My Applications
                            </a>
                        </div>
                    </div>

                    <?php if ($candidate->isPremium()): ?>
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mt-6">
                        <h3 class="font-bold text-lg text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Auto-Apply Settings
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-sm text-gray-700">Enable Auto-Apply</label>
                                <input type="checkbox" x-model="autoApplyEnabled" class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            </div>
                            <button @click="saveAutoApply" class="w-full px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                Save Opt-in
                            </button>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="bg-gray-50 rounded-xl border border-gray-200 p-6 mt-6">
                        <div class="text-sm text-gray-700 mb-3">Auto-apply is a premium feature.</div>
                        <a href="/candidate/premium/plans" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                            Upgrade to Premium
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Bookmarked Jobs -->
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                            <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                </svg>
                                Saved Jobs
                            </h3>
                            <a href="/candidate/jobs/saved" class="text-sm text-gray-700 hover:text-gray-900 font-medium hover:underline">View All</a>
                        </div>
                        <div class="space-y-3" x-show="bookmarkedJobs.length > 0">
                            <template x-for="job in bookmarkedJobs" :key="job.id">
                                    <a :href="'/candidate/jobs/' + (job.slug || job.id)" 
                                   class="block border border-gray-200 rounded-lg p-3 hover:border-blue-500 hover:shadow-md transition-all duration-200 bg-white">
                                    <div class="font-semibold text-sm text-gray-900 hover:text-blue-600 transition" x-text="job.title"></div>
                                    <p class="text-xs text-gray-500 mt-1" x-text="job.company_name"></p>
                                </a>
                            </template>
                        </div>
                        <div x-show="bookmarkedJobs.length === 0" class="text-sm text-gray-500 text-center py-4">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                            </svg>
                            No saved jobs yet
                        </div>
                    </div>

                    <!-- Recently Viewed -->
                    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                        <h3 class="font-bold text-lg text-gray-900 mb-5 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Recently Viewed
                        </h3>
                        <div class="space-y-3" x-show="recentViews.length > 0">
                            <template x-for="job in recentViews" :key="job.id">
                                    <a :href="'/candidate/jobs/' + (job.slug || job.id)" 
                                   class="block border border-gray-200 rounded-lg p-3 hover:border-gray-400 hover:shadow-md transition-all duration-200 bg-white">
                                    <div class="font-semibold text-sm text-gray-900 hover:text-gray-700 transition" x-text="job.title"></div>
                                    <p class="text-xs text-gray-500 mt-1" x-text="job.company_name"></p>
                                </a>
                            </template>
                        </div>
                        <div x-show="recentViews.length === 0" class="text-sm text-gray-500 text-center py-4">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            No recent views
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include __DIR__ . '/../include/footer.php'; ?>
</body>
</html>
