<?php

/**
 * @var string $title
 * @var \App\Models\Employer $employer
 * @var array $applications
 * @var array $filters
 * @var array $statusCounts
 * @var array $jobs
 */
$currentStatus = $filters['status'] ?? 'all';
$currentSort = $filters['sort_by'] ?? 'date';
?>

<?php
$featureAccess = $subscription['featureAccess'] ?? [];
$usage = $subscription['usage'] ?? [];
$isSubscribed = (($usage['plan_name'] ?? '') !== 'Free');
$canSeeContacts = !empty($featureAccess['candidate_mobile_visible']) && ($usage['contacts_remaining'] ?? 0) > 0;
$canDownloadResume = !empty($featureAccess['resume_download_enabled']) && ($usage['downloads_remaining'] ?? 0) > 0;
$canMessage = $isSubscribed; // Messaging typically requires subscription
?>
<style>
    :root {
        --background: 0 0% 100%;
        --foreground: 222.2 84% 4.9%;
        --card: 0 0% 100%;
        --card-foreground: 222.2 84% 4.9%;
        --popover: 0 0% 100%;
        --popover-foreground: 222.2 84% 4.9%;
        --primary: 221.2 83.2% 53.3%;
        --primary-foreground: 210 40% 98%;
        --secondary: 210 40% 96.1%;
        --secondary-foreground: 222.2 47.4% 11.2%;
        --muted: 210 40% 96.1%;
        --muted-foreground: 215.4 16.3% 46.9%;
        --accent: 210 40% 96.1%;
        --accent-foreground: 222.2 47.4% 11.2%;
        --destructive: 0 84.2% 60.2%;
        --destructive-foreground: 210 40% 98%;
        --border: 214.3 31.8% 91.4%;
        --input: 214.3 31.8% 91.4%;
        --ring: 221.2 83.2% 53.3%;
        --radius: 0.5rem;
    }

    .bg-background {
        background-color: hsl(var(--background));
    }

    .bg-card {
        background-color: hsl(var(--card));
    }

    .bg-primary {
        background-color: hsl(var(--primary));
    }

    .bg-primary\/90:hover {
        background-color: hsl(var(--primary) / 0.9);
    }

    .text-primary-foreground {
        color: hsl(var(--primary-foreground));
    }

    .text-foreground {
        color: hsl(var(--foreground));
    }

    .text-muted-foreground {
        color: hsl(var(--muted-foreground));
    }

    .border-border {
        border-color: hsl(var(--border));
    }

    .ring-offset-background {
        --tw-ring-offset-color: hsl(var(--background));
    }

    .ring-ring {
        --tw-ring-color: hsl(var(--ring));
    }
</style>

<main class="flex-1 p-6">
    <?php if (isset($currentJob) && $currentJob): ?>
        <!-- Job Specific Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-2">
                <a href="/employer/jobs" class="text-gray-500 hover:text-gray-700 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($currentJob->title) ?></h1>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500 ml-9">
                <span><?= htmlspecialchars($currentJob->location ?? 'Location') ?></span>
                <?php if (!empty($currentJob->area)): ?>
                    <span>•</span>
                    <span><?= htmlspecialchars($currentJob->area) ?></span>
                <?php endif; ?>
                <span>•</span>
                <span><?= htmlspecialchars($employer->phone ?? $employer->mobile ?? 'Phone') ?></span>
                <span>•</span>
                <?php
                $minSal = $currentJob->salary_min ?? 0;
                $maxSal = $currentJob->salary_max ?? 0;
                $salText = ($minSal || $maxSal) ? '₹' . number_format($minSal) . ' - ₹' . number_format($maxSal) : 'Not disclosed';
                ?>
                <span><?= $salText ?></span>
                <span>•</span>
                <span><?= htmlspecialchars($currentJob->education ?? 'Any Education') ?></span>
                <span>•</span>
                <span><?= htmlspecialchars($currentJob->language ?? 'Any Language') ?></span>
                <span>•</span>
                <?php
                $minExp = $currentJob->min_experience ?? 0;
                $maxExp = $currentJob->max_experience ?? 0;
                $expText = ($minExp || $maxExp) ? $minExp . '-' . $maxExp . ' Years of Exp' : 'Any Exp';
                ?>
                <span><?= $expText ?></span>
                <span>•</span>
                <span><?= ucfirst(str_replace('_', ' ', $currentJob->employment_type ?? 'Full Time')) ?></span>
            </div>
        </div>

        <!-- Job Specific Tabs -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8 overflow-x-auto">
                <?php
                $tabStatus = $filters['status'] ?? 'all';
                ?>
                <a href="?job_id=<?= $currentJob->id ?>&status=all"
                    class="<?= ($tabStatus == 'all' && ($filters['source'] ?? '') !== 'database') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-lg">
                    Responses (<?= $statusCounts['total'] ?? 0 ?>)
                </a>

                <a href="?job_id=<?= $currentJob->id ?>&status=shortlist"
                    class="<?= ($tabStatus == 'shortlist') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-lg">
                    Hot Leads (<?= $statusCounts['shortlisted_count'] ?? 0 ?>)
                </a>

                <a href="?job_id=<?= $currentJob->id ?>&source=database"
                    class="<?= (($filters['source'] ?? '') === 'database') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-lg">
                    Database (<?= $databaseCount ?? 0 ?>)
                </a>

                <a href="?job_id=<?= $currentJob->id ?>&status=new"
                    class="<?= ($tabStatus == 'new') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-lg">
                    Total Leads (<?= $statusCounts['new_count'] ?? 0 ?>)
                </a>
            </nav>
        </div>

    <?php else: ?>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-foreground">All Candidates</h1>
                <p class="text-muted-foreground text-sm mt-1">Manage and review all job applications</p>
            </div>
            <button onclick="window.location.href='/employer/candidates/add'" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 h-10 px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground shadow-lg rounded-xl gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-plus h-4 w-4">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <line x1="19" x2="19" y1="8" y2="14"></line>
                    <line x1="22" x2="16" y1="11" y2="11"></line>
                </svg>
                Add Candidate
            </button>
        </div>

        <!-- Subscription Warning -->
        <?php if (!$isSubscribed || (!$canSeeContacts && !$canDownloadResume)): ?>
            <div class="mb-6">
                <div class="rounded-md bg-yellow-50 p-4 border border-yellow-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-8 4a1 1 0 100-2 1 1 0 000 2zm1-9a1 1 0 00-2 0v5a1 1 0 002 0V5z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-800">
                                Upgrade to view candidate contact details and download resumes.
                                <?php if (($usage['contacts_remaining'] ?? 0) <= 0): ?>
                                    You've reached your contact view limit.
                                <?php endif; ?>
                                <?php if (($usage['downloads_remaining'] ?? 0) <= 0): ?>
                                    Resume download limit reached.
                                <?php endif; ?>
                            </p>
                            <div class="mt-2">
                                <a href="/employer/subscription/plans?upgrade=1&feature=contact_view" class="inline-flex items-center px-3 py-1.5 border border-yellow-300 text-xs font-medium rounded-md text-yellow-900 bg-yellow-100 hover:bg-yellow-200">Upgrade Plan</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-card rounded-2xl border border-border p-1 mb-6 overflow-x-auto">
            <div dir="ltr" data-orientation="horizontal">
                <div role="tablist" aria-orientation="horizontal" class="inline-flex items-center justify-center rounded-md text-muted-foreground bg-transparent h-auto p-0 gap-1 flex-wrap" tabindex="0" data-orientation="horizontal" style="outline: none;">
                    <?php
                    $tabs = [
                        'all' => 'All',
                        'new' => 'New',
                        'contacting' => 'Contacting',
                        'interviewing' => 'Interviewed',
                        'rejected' => 'Rejected',
                        'hired' => 'Hired',
                        'shortlist' => 'Shortlist',
                        // 'undecided' => 'Undecided'
                    ];

                    foreach ($tabs as $key => $label):
                        $countKey = ($key === 'all') ? 'total' : ($key === 'shortlist' ? 'shortlisted_count' : $key . '_count');
                        $count = $statusCounts[$countKey] ?? 0;
                        $isActive = $currentStatus === $key;
                    ?>
                        <button type="button"
                            onclick="window.location.href='?status=<?= $key ?>'"
                            role="tab"
                            aria-selected="<?= $isActive ? 'true' : 'false' ?>"
                            data-state="<?= $isActive ? 'active' : 'inactive' ?>"
                            id="tab-trigger-<?= $key ?>"
                            class="inline-flex items-center justify-center whitespace-nowrap ring-offset-background data-[state=active]:shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-primary data-[state=active]:text-primary-foreground rounded-xl px-4 py-2.5 text-sm font-medium transition-all">
                            <?= $label ?> <span class="ml-2 text-xs opacity-70"><?= $count ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!isset($currentJob) || !$currentJob): ?>
        <!-- Filter Section -->
        <div class="bg-card rounded-2xl border border-border p-4 mb-6">
            <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
                <div class="flex flex-wrap gap-3 items-center">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-muted-foreground">Job:</span>
                        <select onchange="updateJob(this.value)" class="flex h-10 items-center justify-between border bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 w-48 rounded-xl border-border">
                            <option value="">All Jobs</option>
                            <?php foreach ($jobs as $job): ?>
                                <?php $jid = $job->attributes['id'] ?? $job->id ?? null; ?>
                                <option value="<?= $jid ?>" <?= ($filters['job_id'] ?? '') == $jid ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($job->attributes['title'] ?? $job->title ?? 'Job') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-muted-foreground">Sort by:</span>
                        <select onchange="updateSort(this.value)" class="flex h-10 items-center justify-between border bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 w-52 rounded-xl border-border">
                            <option value="date" <?= ($currentSort ?? 'date') === 'date' ? 'selected' : '' ?>>Application date (newest first)</option>
                            <option value="location" <?= ($currentSort ?? 'date') === 'location' ? 'selected' : '' ?>>Closest to location</option>
                            <option value="interest" <?= ($currentSort ?? 'date') === 'interest' ? 'selected' : '' ?>>Most interested</option>
                        </select>
                    </div>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>
                        <input value="<?= htmlspecialchars($filters['search'] ?? '') ?>" onkeydown="if(event.key==='Enter'){updateSearch(this.value)}" onchange="updateSearch(this.value)" class="flex h-10 border bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm pl-10 w-64 rounded-xl border-border" placeholder="Search name, email, skills...">
                    </div>

                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="toggleFilters()" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3 rounded-xl gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-filter h-4 w-4">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3">
                            </polygon>
                        </svg>
                        Filters
                    </button>
                    <div class="flex items-center border border-border rounded-xl p-1">
                        <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 bg-primary text-primary-foreground hover:bg-primary/90 rounded-lg h-8 w-8 p-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-list h-4 w-4">
                                <path d="M3 12h.01"></path>
                                <path d="M3 18h.01"></path>
                                <path d="M3 6h.01"></path>
                                <path d="M8 12h13"></path>
                                <path d="M8 18h13"></path>
                                <path d="M8 6h13"></path>
                            </svg>
                        </button>
                        <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground rounded-lg h-8 w-8 p-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-grid3x3 h-4 w-4">
                                <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                                <path d="M3 9h18"></path>
                                <path d="M3 15h18"></path>
                                <path d="M9 3v18"></path>
                                <path d="M15 3v18"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>


    <!-- Applications Grid -->
    <div class="flex flex-col md:flex-row gap-6">
        <!-- Left Sidebar Filters -->
        <aside id="filtersSidebar" class="hidden md:block w-72 shrink-0 transition-all duration-300 ease-in-out">
            <div class="bg-card rounded-xl border border-border p-5 sticky top-20">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-foreground">All Filters</h3>
                    <a href="/employer/applications" class="text-sm text-primary hover:underline">Clear All</a>
                </div>

                <div class="space-y-6">


                    <!-- Job Filter (Accordion) removed to avoid duplication with top bar -->

                    <!-- Location Distance -->
                    <div class="border-t border-border pt-4">
                        <button class="flex items-center justify-between w-full text-sm font-semibold text-foreground group" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180')">
                            Location
                            <svg class="w-4 h-4 text-muted-foreground group-hover:text-foreground transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="mt-3 space-y-3 <?= !empty($filters['location']) ? '' : 'hidden' ?>">
                            <input type="text" placeholder="City, State..." value="<?= htmlspecialchars($filters['location'] ?? '') ?>" onchange="updateLocation(this.value)" class="w-full h-9 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium text-muted-foreground">Distance</span>
                                    <span class="text-xs bg-primary/10 text-primary px-1.5 py-0.5 rounded">km</span>
                                </div>
                                <?php foreach ([5, 10, 25, 50] as $km): ?>
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="radio" name="location_distance" value="<?= $km ?>" onclick="updateDistance(<?= $km ?>)" class="text-primary focus:ring-primary h-4 w-4 border-input" <?= ($filters['location_distance'] ?? '') == $km ? 'checked' : '' ?>>
                                        <span class="text-sm text-muted-foreground"><?= $km ?> km</span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Experience Filter -->
                    <div class="border-t border-border pt-4">
                        <button class="flex items-center justify-between w-full text-sm font-semibold text-foreground group" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180')">
                            Experience
                            <svg class="w-4 h-4 text-muted-foreground group-hover:text-foreground transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="mt-3 space-y-3 <?= (!empty($filters['min_experience']) || !empty($filters['max_experience'])) ? '' : 'hidden' ?>">
                            <div class="flex items-center gap-2">
                                <div class="w-1/2">
                                    <label class="text-xs text-muted-foreground mb-1 block">Min (Years)</label>
                                    <input type="number" min="0" max="50" step="0.5" value="<?= htmlspecialchars($filters['min_experience'] ?? '') ?>" onchange="updateExperience('min', this.value)" class="w-full h-8 rounded-md border border-input bg-background px-3 text-sm">
                                </div>
                                <div class="w-1/2">
                                    <label class="text-xs text-muted-foreground mb-1 block">Max (Years)</label>
                                    <input type="number" min="0" max="50" step="0.5" value="<?= htmlspecialchars($filters['max_experience'] ?? '') ?>" onchange="updateExperience('max', this.value)" class="w-full h-8 rounded-md border border-input bg-background px-3 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active In Filter -->
                    <div class="border-t border-border pt-4">
                        <button class="flex items-center justify-between w-full text-sm font-semibold text-foreground group" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180')">
                            Active In
                            <svg class="w-4 h-4 text-muted-foreground group-hover:text-foreground transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="mt-3 space-y-2 <?= !empty($filters['active_in']) ? '' : 'hidden' ?>">
                            <?php $active = (int)($filters['active_in'] ?? 0); ?>
                            <?php foreach ([1, 3, 7, 14, 30] as $day): ?>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="radio" name="active_in" value="<?= $day ?>" onclick="setActiveIn(<?= $day ?>)" class="text-primary focus:ring-primary h-4 w-4 border-input" <?= $active === $day ? 'checked' : '' ?>>
                                    <span class="text-sm text-muted-foreground">Last <?= $day ?> Day<?= $day > 1 ? 's' : '' ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Matching Skills Filter -->
                    <div class="border-t border-border pt-4">
                        <button class="flex items-center justify-between w-full text-sm font-semibold text-foreground group" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180')">
                            Matching Skills
                            <svg class="w-4 h-4 text-muted-foreground group-hover:text-foreground transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="mt-3 space-y-2 <?= !empty($filters['skills']) ? '' : 'hidden' ?>">
                            <?php
                            $jobSkills = [];
                            if (isset($currentJob) && $currentJob) {
                                $jobSkills = $currentJob->skills();
                            } elseif (!empty($filters['job_id'])) {
                                // Dynamic fallback: find the job from $jobs array
                                foreach ($jobs as $j) {
                                    $jid = $j->attributes['id'] ?? $j->id ?? null;
                                    if ($jid == $filters['job_id']) {
                                        $jobSkills = $j->skills();
                                        break;
                                    }
                                }
                            }

                            $selectedSkills = isset($filters['skills']) ? (is_array($filters['skills']) ? $filters['skills'] : explode(',', $filters['skills'])) : [];

                            if (empty($jobSkills)) {
                                // If no job specific skills, show generic popular skills
                                $jobSkills = [
                                    ['name' => 'Communication'],
                                    ['name' => 'Sales'],
                                    ['name' => 'Marketing'],
                                    ['name' => 'Java'],
                                    ['name' => 'Python'],
                                    ['name' => 'Leadership']
                                ];
                            }

                            foreach ($jobSkills as $skill):
                                $sName = $skill['name'] ?? '';
                                if (!$sName) continue;
                                $isChecked = in_array($sName, $selectedSkills);
                            ?>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="checkbox" value="<?= htmlspecialchars($sName) ?>" onchange="updateSkills(this.value, this.checked)" class="text-primary focus:ring-primary h-4 w-4 border-input" <?= $isChecked ? 'checked' : '' ?>>
                                    <span class="text-sm text-muted-foreground"><?= htmlspecialchars($sName) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- English Fluency Filter -->
                    <div class="border-t border-border pt-4">
                        <button class="flex items-center justify-between w-full text-sm font-semibold text-foreground group" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('svg').classList.toggle('rotate-180')">
                            English Fluency
                            <svg class="w-4 h-4 text-muted-foreground group-hover:text-foreground transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="mt-3 space-y-2 <?= !empty($filters['language']) ? '' : 'hidden' ?>">
                            <?php $lang = $filters['language'] ?? ''; ?>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="radio" name="language_fluency" value="" onclick="updateLanguage('')" class="text-primary focus:ring-primary h-4 w-4 border-input" <?= $lang === '' ? 'checked' : '' ?>>
                                <span class="text-sm text-muted-foreground">Any</span>
                            </label>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="radio" name="language_fluency" value="Good" onclick="updateLanguage('Good')" class="text-primary focus:ring-primary h-4 w-4 border-input" <?= $lang === 'Good' ? 'checked' : '' ?>>
                                <span class="text-sm text-muted-foreground">Good</span>
                            </label>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="radio" name="language_fluency" value="Fluent" onclick="updateLanguage('Fluent')" class="text-primary focus:ring-primary h-4 w-4 border-input" <?= $lang === 'Fluent' ? 'checked' : '' ?>>
                                <span class="text-sm text-muted-foreground">Fluent</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button onclick="location.reload()" class="w-full bg-primary hover:bg-primary/90 text-primary-foreground font-medium py-2.5 rounded-xl transition-colors shadow-lg shadow-primary/20">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Applications List Column -->
        <div class="flex-1 min-w-0 space-y-4">
            <!-- List Header (Search & Sort) -->
            <?php if (isset($currentJob) && $currentJob): ?>
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-card p-4 rounded-xl border border-border mb-4">
                    <div class="flex items-center gap-4 w-full sm:w-auto">
                        <!-- Select All Checkbox -->
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="selectAll" class="rounded border-input text-primary focus:ring-primary h-4 w-4" onclick="toggleSelectAll(this)">
                            <label for="selectAll" class="text-sm font-medium text-foreground cursor-pointer">Select All</label>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <!-- Sort -->
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-muted-foreground whitespace-nowrap">Sort by:</span>
                            <select class="h-9 rounded-lg border border-input bg-background px-3 py-1 text-sm focus:ring-2 focus:ring-primary focus:outline-none" onchange="updateSort(this.value)">
                                <option value="match_score" <?= ($currentSort ?? 'date') === 'match_score' ? 'selected' : '' ?>>Relevant</option>
                                <option value="date" <?= ($currentSort ?? 'date') === 'date' ? 'selected' : '' ?>>Newest</option>
                                <option value="status" <?= ($currentSort ?? 'date') === 'status' ? 'selected' : '' ?>>Status</option>
                            </select>
                        </div>

                        <!-- Filter Icon -->
                        <button class="p-2 rounded-lg border border-input hover:bg-accent text-muted-foreground hover:text-foreground transition-colors" onclick="toggleFilters()">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                        </button>

                        <!-- Search Icon/Input -->
                        <div class="relative">
                            <input type="text" placeholder="Search..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>" class="w-40 h-9 pl-9 rounded-lg border border-input bg-background text-sm focus:ring-2 focus:ring-primary focus:outline-none" onchange="updateSearch(this.value)">
                            <svg class="w-4 h-4 absolute left-3 top-2.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-card p-4 rounded-xl border border-border">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button class="md:hidden inline-flex items-center justify-center rounded-xl border border-input bg-background h-10 w-10 text-sm font-medium hover:bg-accent hover:text-accent-foreground" onclick="openFilters()">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                        </button>
                        <span class="font-semibold text-foreground"><?= count($applications) ?> Candidates</span>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <span class="text-sm text-muted-foreground whitespace-nowrap hidden sm:inline">Sort by:</span>
                        <select class="h-10 rounded-xl border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 w-full sm:w-48" onchange="updateSort(this.value)">
                            <option value="date" <?= ($currentSort ?? 'date') === 'date' ? 'selected' : '' ?>>Newest Applied</option>
                            <option value="match_score" <?= ($currentSort ?? 'date') === 'match_score' ? 'selected' : '' ?>>Best Match</option>
                            <option value="status" <?= ($currentSort ?? 'date') === 'status' ? 'selected' : '' ?>>Status</option>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <div class="space-y-4">
                <?php if (empty($applications)): ?>
                    <div class="bg-card rounded-xl border border-border p-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-muted mb-4">
                            <svg class="h-8 w-8 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-foreground">No applications found</h3>
                        <p class="mt-1 text-muted-foreground">Applications from candidates will appear here.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($applications as $app): ?>
                        <?php $aid = $app['application_id'] ?? null; ?>
                        <div class="group bg-card rounded-xl border border-border p-5 hover:shadow-md transition-all duration-200">
                            <div class="flex gap-4">
                                <!-- Checkbox & Avatar Column -->
                                <div class="flex items-start gap-4 shrink-0">
                                    <div class="pt-1 hidden md:block">
                                        <input type="checkbox" class="row-select rounded border-input text-primary focus:ring-primary h-4 w-4" data-app-id="<?= $aid ?? '' ?>">
                                    </div>
                                    <div class="relative shrink-0">
                                        <?php if (!empty($app['profile_picture'])): ?>
                                            <img src="<?= htmlspecialchars($app['profile_picture']) ?>"
                                                alt="<?= htmlspecialchars($app['full_name'] ?? 'Candidate') ?>"
                                                class="w-16 h-16 rounded-full object-cover border border-border">
                                        <?php else: ?>
                                            <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center border border-primary/20">
                                                <span class="text-primary font-bold text-2xl">
                                                    <?= strtoupper(substr($app['full_name'] ?? $app['candidate_email'] ?? 'U', 0, 1)) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <!-- Status Indicator Dot (Optional) -->
                                        <span class="absolute bottom-0 right-0 w-4 h-4 rounded-full border-2 border-card bg-green-500"></span>
                                    </div>
                                </div>

                                <!-- Main Content Column -->
                                <div class="flex-1 min-w-0">
                                    <!-- Header: Name, Meta, Match -->
                                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <a href="<?= $aid ? '/employer/applications/' . $aid : '#' ?>"
                                                    onclick="openPreview(event, this)"
                                                    data-candidate="<?= htmlspecialchars(json_encode($app), ENT_QUOTES, 'UTF-8') ?>"
                                                    class="text-xl font-bold text-foreground hover:text-primary transition-colors truncate">
                                                    <?= htmlspecialchars($app['full_name'] ?? $app['candidate_email'] ?? 'Unknown') ?>
                                                </a>
                                                <span class="text-sm text-muted-foreground"><?= htmlspecialchars($app['experience_years'] ?? '2') ?>Yrs, <?= htmlspecialchars($app['gender'] ?? 'Male') ?></span>

                                                <?php if (!empty($app['overall_match_score'])): ?>
                                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200 flex items-center gap-1">
                                                        <?= $app['overall_match_score'] ?>% Match
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-muted-foreground">
                                                <span class="flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <?= htmlspecialchars($app['experience_years'] ?? '3') ?> years
                                                </span>
                                                <span class="flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <?= htmlspecialchars($app['current_salary'] ?? '25,000') ?>/mo
                                                </span>
                                                <span class="flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    </svg>
                                                    <?= htmlspecialchars($app['location_display'] ?? 'Not specified') ?>
                                                </span>
                                            </div>

                                            <div class="mt-1 text-sm text-muted-foreground flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                                                </svg>
                                                <?= htmlspecialchars($app['languages'] ?? 'English, Hindi') ?>
                                            </div>
                                        </div>

                                        <!-- Relevance Feedback -->
                                        <div class="flex items-center gap-2 text-sm text-muted-foreground shrink-0 mt-2 md:mt-0">
                                            <span class="hidden sm:inline">Is this candidate relevant?</span>
                                            <button class="hover:text-green-600 transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                                                </svg></button>
                                            <button class="hover:text-red-600 transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"></path>
                                                </svg></button>
                                        </div>
                                    </div>

                                    <!-- Status & Job Info -->
                                    <div class="mt-4 flex flex-wrap items-center gap-3">
                                        <?php
                                        $statusColors = [
                                            'applied' => 'bg-blue-100 text-blue-700 border-blue-200',
                                            'shortlisted' => 'bg-green-100 text-green-700 border-green-200',
                                            'rejected' => 'bg-red-100 text-red-700 border-red-200',
                                            'interview' => 'bg-purple-100 text-purple-700 border-purple-200',
                                            'hired' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                            'screening' => 'bg-orange-100 text-orange-700 border-orange-200',
                                            'matched' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                        ];
                                        $statusKey = $app['status'] ?? (($filters['source'] ?? '') === 'database' ? 'matched' : 'applied');
                                        if (($app['status'] ?? '') === 'suggested') $statusKey = 'matched';

                                        $statusClass = $statusColors[$statusKey] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                                        $statusLabel = ucfirst($statusKey === 'screening' ? 'Reviewing' : ($statusKey === 'matched' ? 'Matched' : $statusKey));
                                        ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border <?= $statusClass ?>">
                                            <?= $statusLabel ?>
                                        </span>
                                        <?php if (($filters['source'] ?? '') !== 'database'): ?>
                                            <span class="text-sm text-muted-foreground">Applied <?= $app['applied_at_formatted'] ?? date('M d, Y', strtotime($app['applied_at'] ?? 'now')) ?></span>
                                        <?php else: ?>
                                            <span class="text-sm text-muted-foreground">Matched <?= date('M d, Y', strtotime($app['created_at'] ?? 'now')) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (($filters['source'] ?? '') !== 'database'): ?>
                                        <div class="mt-1 text-sm">
                                            Job applied to: <a href="#" class="text-primary hover:underline font-medium"><?= htmlspecialchars($app['job_title'] ?? 'N/A') ?></a>
                                        </div>
                                    <?php endif; ?>

                                    <hr class="my-4 border-border">

                                    <!-- Details Grid -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                                        <!-- Column 1 -->
                                        <div class="space-y-3">
                                            <div class="flex items-start gap-2 text-sm">
                                                <span class="w-24 text-muted-foreground shrink-0 pt-0.5">Skills:</span>
                                                <div class="flex flex-wrap gap-1.5">
                                                    <?php
                                                    $limit = 5;
                                                    $chips = [];
                                                    $seen = [];
                                                    $matched = $app['matched_skills'] ?? [];
                                                    $extra = $app['extra_relevant_skills'] ?? [];
                                                    foreach ($matched as $sk) {
                                                        if (count($chips) >= $limit) break;
                                                        $key = strtolower(trim($sk));
                                                        if (!$key || isset($seen[$key])) continue;
                                                        $seen[$key] = true;
                                                        $chips[] = ['text' => $sk, 'matched' => true];
                                                    }
                                                    // Fill with others if needed
                                                    if (count($chips) < $limit) {
                                                        foreach ($extra as $sk) {
                                                            if (count($chips) >= $limit) break;
                                                            $key = strtolower(trim($sk));
                                                            if (!$key || isset($seen[$key])) continue;
                                                            $seen[$key] = true;
                                                            $chips[] = ['text' => $sk, 'matched' => false];
                                                        }
                                                    }
                                                    ?>
                                                    <?php foreach ($chips as $chip): ?>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border <?= $chip['matched'] ? 'bg-green-50 text-green-700 border-green-200' : 'bg-secondary/50 text-secondary-foreground border-border' ?>">
                                                            <?= htmlspecialchars($chip['text']) ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <div class="flex items-start gap-2 text-sm">
                                                <span class="w-24 text-muted-foreground shrink-0 pt-0.5">Experience:</span>
                                                <span class="text-foreground break-words"><?= htmlspecialchars($app['experience_years'] ?? '3') ?> years at <?= htmlspecialchars($app['industry'] ?? 'Software & IT Services') ?></span>
                                            </div>
                                            <div class="flex items-start gap-2 text-sm">
                                                <span class="w-24 text-muted-foreground shrink-0 pt-0.5">Industry:</span>
                                                <span class="text-foreground break-words"><?= htmlspecialchars($app['industry'] ?? 'Software & IT Services') ?></span>
                                            </div>
                                            <div class="flex items-center gap-2 text-sm">
                                                <span class="w-24 text-muted-foreground shrink-0">Stage:</span>
                                                <?php if (($filters['source'] ?? '') === 'database'): ?>
                                                    <span class="text-sm font-medium text-gray-500 italic">Not Applied</span>
                                                <?php else: ?>
                                                    <select class="h-8 rounded-lg border border-input  bg-[#f9fafb] text-sm focus:ring-primary focus:border-primary w-40"
                                                        <?= $aid ? '' : 'disabled' ?>
                                                        <?= $aid ? 'onchange="updateApplicationStatus(' . $aid . ', this.value)"' : '' ?>>
                                                        <?php foreach (
                                                            [
                                                                'applied' => 'New',
                                                                'interview' => 'Interview',
                                                                'shortlisted' => 'Shortlisted',
                                                                'hired' => 'Hired',
                                                                'rejected' => 'Rejected'
                                                            ] as $val => $label
                                                        ): ?>
                                                            <option value="<?= $val ?>" <?= ($app['status'] ?? 'applied') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Column 2 -->
                                        <div class="space-y-3">
                                            <div class="flex items-start gap-2 text-sm">
                                                <span class="w-24 text-muted-foreground shrink-0 pt-0.5">Education:</span>
                                                <span class="text-foreground break-words"><?= htmlspecialchars($app['education'] ?? 'B.Tech in Computer Science') ?></span>
                                            </div>
                                            <div class="flex items-start gap-2 text-sm">
                                                <span class="w-24 text-muted-foreground shrink-0 pt-0.5">Salary:</span>
                                                <span class="text-foreground break-words">Current: <?= htmlspecialchars($app['current_salary'] ?? '25,000') ?>/mo • Expected: <?= htmlspecialchars($app['expected_salary'] ?? '40,000-50,000') ?>/mo</span>
                                            </div>
                                            <div class="flex items-start gap-2 text-sm">
                                                <span class="w-24 text-muted-foreground shrink-0 pt-0.5">Active:</span>
                                                <span class="text-foreground flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <?= htmlspecialchars($app['last_active'] ?? 'Today') ?>
                                                </span>
                                            </div>
                                            <!-- Contact Info (If unlocked) -->
                                            <?php if ($canSeeContacts): ?>
                                                <div class="flex gap-2 text-sm">
                                                    <span class="w-24 text-muted-foreground shrink-0">Contact:</span>
                                                    <div class="flex gap-3">
                                                        <?php if (!empty($app['phone'])): ?>
                                                            <span class="flex items-center gap-1 text-foreground">
                                                                <svg class="w-3.5 h-3.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                                </svg>
                                                                <?= htmlspecialchars($app['phone']) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($app['candidate_email'])): ?>
                                                            <a href="mailto:<?= htmlspecialchars($app['candidate_email']) ?>" class="flex items-center gap-1 text-primary hover:underline">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                                </svg>
                                                                Email
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($canDownloadResume && !empty($app['resume_url'])): ?>
                                                <div class="flex gap-2 text-sm">
                                                    <span class="w-24 text-muted-foreground shrink-0">Resume:</span>
                                                    <a href="<?= htmlspecialchars($app['resume_url']) ?>" target="_blank" class="flex items-center gap-1 text-primary hover:underline">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                        Download Resume
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Footer Actions -->
                                    <div class="mt-6 flex flex-wrap justify-end items-center gap-3 border-t border-border pt-4">

                                        <button <?= $aid ? 'onclick="updateApplicationStatus(' . $aid . ', \'shortlisted\')"' : 'disabled' ?> class="p-2 text-muted-foreground hover:text-green-600 hover:bg-green-50 rounded-full transition-colors" title="Shortlist">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                        <button <?= $aid ? 'onclick="openInterviewModal(' . $aid . ')"' : 'disabled' ?> class="p-2 text-muted-foreground hover:text-blue-600 hover:bg-blue-50 rounded-full transition-colors" title="Schedule Interview">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>
                                        <button <?= $aid ? 'onclick="updateApplicationStatus(' . $aid . ', \'rejected\')"' : 'disabled' ?> class="p-2 text-muted-foreground hover:text-red-600 hover:bg-red-50 rounded-full transition-colors" title="Reject">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>

                                        <div class="h-6 w-px bg-border mx-2"></div>

                                        <?php if ($canMessage): ?>
                                            <button onclick="event.stopPropagation(); startMessage(<?= $app['candidate_user_id'] ?? 0 ?>, <?= $app['job_id'] ?? 'null' ?>, <?= $aid ? $aid : 'null' ?>)"
                                                class="inline-flex items-center justify-center px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-xl hover:bg-primary/90 transition-colors shadow-sm gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                                </svg>
                                                Message
                                            </button>
                                        <?php else: ?>
                                            <button class="inline-flex items-center justify-center px-4 py-2 bg-muted text-muted-foreground text-sm font-medium rounded-xl cursor-not-allowed border border-border gap-2" disabled>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                                </svg>
                                                Message
                                            </button>
                                        <?php endif; ?>
                                        <?php 
                                            $rawPhone = (string)($app['phone'] ?? ($app['candidate_mobile'] ?? ''));
                                            $phoneDigits = preg_replace('/\D+/', '', $rawPhone);
                                            $candidateName = $app['full_name'] ?? ($app['candidate_email'] ?? 'Candidate');
                                            $jobTitle = $app['job_title'] ?? '';
                                            $jobSlug = $app['job_slug'] ?? '';
                                            $jobCurrency = $app['job_currency'] ?? ($currentJob->currency ?? 'INR');
                                            $salaryMin = $app['job_salary_min'] ?? ($currentJob->salary_min ?? null);
                                            $salaryMax = $app['job_salary_max'] ?? ($currentJob->salary_max ?? null);
                                            $locationText = $currentJob->city ?? ($app['location_display'] ?? '');
                                            $waDetails = [
                                                'currency' => $jobCurrency,
                                                'salary_min' => $salaryMin,
                                                'salary_max' => $salaryMax,
                                                'location' => $locationText
                                            ];
                                        ?>
                                        <button onclick="sendWhatsApp('<?= $phoneDigits ?>','<?= htmlspecialchars($candidateName, ENT_QUOTES) ?>','<?= htmlspecialchars($jobTitle, ENT_QUOTES) ?>','<?= htmlspecialchars($jobSlug, ENT_QUOTES) ?>','<?= htmlspecialchars(json_encode($waDetails), ENT_QUOTES) ?>')"
                                            class="inline-flex items-center justify-center px-4 py-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground text-sm font-medium rounded-xl transition-colors gap-2" title="Send WhatsApp">
                                            <img src="https://img.icons8.com/ios-filled/50/25D366/whatsapp--v1.png" alt="Send WhatsApp" class="w-5 h-5">
                                            Send WhatsApp
                                        </button>
                                        <button <?= $aid ? 'onclick="openAddNote(' . $aid . ')"' : 'disabled' ?> class="inline-flex items-center justify-center px-4 py-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground text-sm font-medium rounded-xl transition-colors gap-2">
                                            Add note
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div id="filtersPanel" class="hidden fixed inset-0 z-50">
            <div class="absolute inset-0" style="background: rgba(0,0,0,0.3)" onclick="closeFilters()"></div>
            <div class="absolute left-0 top-0 h-full w-full sm:w-[360px] bg-white shadow-xl border-r border-gray-200 p-4 overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Filters</h3>
                    <button class="p-2 rounded hover:bg-gray-100" onclick="closeFilters()">
                        <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                <div class="space-y-6">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Job</label>
                        <select id="filtersJob" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            <option value="">All jobs</option>
                            <?php foreach ($jobs as $job): ?>
                                <?php $jid = $job->attributes['id'] ?? $job->id ?? null; ?>
                                <option value="<?= $jid ?>" <?= ($filters['job_id'] ?? '') == $jid ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($job->attributes['title'] ?? $job->title ?? 'Job') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Status</label>
                        <select id="filtersStatus" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            <option value="all" <?= ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' ?>>All</option>
                            <option value="new" <?= ($filters['status'] ?? '') === 'new' ? 'selected' : '' ?>>New</option>
                            <option value="contacting" <?= ($filters['status'] ?? '') === 'contacting' ? 'selected' : '' ?>>Contacting</option>
                            <option value="interviewing" <?= ($filters['status'] ?? '') === 'interviewing' ? 'selected' : '' ?>>Interviewed</option>
                            <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                            <option value="hired" <?= ($filters['status'] ?? '') === 'hired' ? 'selected' : '' ?>>Hired</option>
                            <option value="shortlist" <?= ($filters['status'] ?? '') === 'shortlist' ? 'selected' : '' ?>>Shortlist</option>
                            <option value="undecided" <?= ($filters['status'] ?? '') === 'undecided' ? 'selected' : '' ?>>Undecided</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Location</label>
                        <input id="filtersLocation" type="text" value="<?= htmlspecialchars($filters['location'] ?? '') ?>" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="City, state, country">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Interest marked</label>
                        <select id="filtersInterest" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                            <option value="" <?= empty($filters['interest'] ?? '') ? 'selected' : '' ?>>All</option>
                            <option value="shortlisted" <?= ($filters['interest'] ?? '') === 'shortlisted' ? 'selected' : '' ?>>Shortlisted</option>
                            <option value="rejected" <?= ($filters['interest'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                            <option value="undecided" <?= ($filters['interest'] ?? '') === 'undecided' ? 'selected' : '' ?>>Undecided</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-between">
                        <button type="button" onclick="applyFilters()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Apply</button>
                        <a href="/employer/applications" class="text-sm text-gray-700 hover:text-gray-900">Clear all</a>
                    </div>
                </div>
            </div>
        </div>

        <div id="interviewModal" class="hidden fixed inset-0 z-50">
            <div class="absolute inset-0" style="background: rgba(0,0,0,0.4)" onclick="closeInterviewModal()"></div>
            <div class="relative bg-card rounded-2xl border border-border p-6 w-full max-w-lg mx-auto mt-24 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-foreground">Schedule Interview</h3>
                    <button class="p-2 rounded hover:bg-accent" onclick="closeInterviewModal()">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                    </button>
                </div>
                <input type="hidden" id="imApplicationId">
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Interview Type</label>
                        <select id="imType" class="w-full px-3 py-2 border border-input rounded-md text-sm">
                            <option value="phone">Phone Interview</option>
                            <option value="video">Video Interview</option>
                            <option value="onsite">On-site Interview</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Date</label>
                        <input id="imDate" type="date" class="w-full px-3 py-2 border border-input rounded-md text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">Start Time</label>
                            <input id="imStart" type="time" class="w-full px-3 py-2 border border-input rounded-md text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">End Time</label>
                            <input id="imEnd" type="time" class="w-full px-3 py-2 border border-input rounded-md text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Timezone</label>
                        <select id="imTz" class="w-full px-3 py-2 border border-input rounded-md text-sm">
                            <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
                            <option value="UTC">UTC</option>
                            <option value="Asia/Dubai">Asia/Dubai</option>
                            <option value="America/New_York">America/New_York</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Location</label>
                        <input id="imLocation" type="text" placeholder="Enter interview location" class="w-full px-3 py-2 border border-input rounded-md text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Meeting Link</label>
                        <input id="imLink" type="url" placeholder="https://meet.example.com/abc" class="w-full px-3 py-2 border border-input rounded-md text-sm">
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-3">
                    <button class="px-4 py-2 border border-input rounded-md" onclick="closeInterviewModal()">Cancel</button>
                    <button class="px-4 py-2 bg-primary text-primary-foreground rounded-md" onclick="scheduleInterviewFromModal()">Schedule Interview</button>
                </div>
            </div>
        </div>

        <script>
            // Bulk selection logic moved to bottom script block to avoid duplication
            const COMPANY_NAME = <?= json_encode($companyName ?? ($employer->attributes['company_name'] ?? $employer->company_name ?? 'Company')) ?>;
            const EMPLOYER_PHONE = <?= json_encode($employerPhone ?? '') ?>;

            function updateSort(value) {
                const url = new URL(window.location);
                url.searchParams.set('sort_by', value);
                window.location.href = url.toString();
            }

            function openInterviewModal(applicationId) {
                document.getElementById('imApplicationId').value = applicationId || '';
                const tzDefault = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Asia/Kolkata';
                const tzSel = document.getElementById('imTz');
                if ([...tzSel.options].some(o => o.value === tzDefault)) tzSel.value = tzDefault; else tzSel.value = 'Asia/Kolkata';
                document.getElementById('interviewModal').classList.remove('hidden');
            }
            function closeInterviewModal() {
                document.getElementById('interviewModal').classList.add('hidden');
            }
            function scheduleInterviewFromModal() {
                const id = document.getElementById('imApplicationId').value;
                const type = document.getElementById('imType').value;
                const date = document.getElementById('imDate').value;
                const start = document.getElementById('imStart').value;
                const end = document.getElementById('imEnd').value;
                const tz = document.getElementById('imTz').value;
                const location = document.getElementById('imLocation').value;
                const link = document.getElementById('imLink').value;
                if (!id || !date || !start || !end) { alert('Please fill required fields'); return; }
                const startDt = `${date} ${start}:00`;
                const endDt = `${date} ${end}:00`;
                fetch('/employer/interviews/schedule', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        application_id: parseInt(id, 10),
                        interview_type: type,
                        scheduled_start: startDt,
                        scheduled_end: endDt,
                        timezone: tz,
                        location,
                        meeting_link: link
                    })
                }).then(r => r.json()).then(data => {
                    if (data && data.error) {
                        alert('Error: ' + data.error);
                    } else {
                        closeInterviewModal();
                        alert('Interview scheduled');
                    }
                }).catch(() => alert('Network error'));
            }
            function formatWhatsAppNumber(num) {
                const digits = String(num || '').replace(/\D+/g, '');
                if (digits.length === 10) return '91' + digits; // default India code
                return digits;
            }
            function safeParse(j) {
                try { return j ? JSON.parse(j) : {}; } catch (e) { return {}; }
            }
            function currencySymbol(c) {
                const map = { 'INR': '₹', 'USD': '$', 'EUR': '€', 'GBP': '£' };
                return map[String(c || '').toUpperCase()] || (c || '');
            }
            function formatSalary(details) {
                const min = details.salary_min, max = details.salary_max;
                if (min || max) {
                    const sym = currencySymbol(details.currency);
                    if (min && max) return `${sym} ${min} - ${max} per month`;
                    if (min) return `${sym} ${min} per month`;
                    return `${sym} ${max} per month`;
                }
                return '';
            }
            function buildWhatsAppMessage(candidateName, jobTitle, jobSlug, detailsJson) {
                const name = (candidateName || 'there').trim();
                const title = (jobTitle || 'the role').trim();
                const link = jobSlug ? (location.origin + '/job/' + jobSlug) : (location.origin + '/jobs');
                const details = safeParse(detailsJson);
                const salaryText = formatSalary(details);
                const locText = (details.location || '').trim();
                const lines = [
                    `👋 Hello ${name},`,
                    ``,
                    `We’re hiring for: *${title}*`,
                    `🏢 Company: *${COMPANY_NAME}*`,
                    ...(salaryText ? [`💰 Salary: ${salaryText}`] : []),
                    ...(locText ? [`📍 Location: ${locText}`] : []),
                    `🔗 Details: ${link}`
                ];
                if (EMPLOYER_PHONE) {
                    lines.push(`📞 Contact: ${EMPLOYER_PHONE}`);
                }
                lines.push(``, `If interested, please reply here or call.`);
                return lines.join('\n');
            }
            function sendWhatsApp(phoneDigits, candidateName, jobTitle, jobSlug, detailsJson) {
                const text = buildWhatsAppMessage(candidateName, jobTitle, jobSlug, detailsJson);
                const phone = formatWhatsAppNumber(phoneDigits);
                const ua = navigator.userAgent || '';
                const isMobile = /Android|iPhone|iPad|iPod|Windows Phone/i.test(ua);
                const base = isMobile ? 'https://api.whatsapp.com/send' : 'https://web.whatsapp.com/send';
                const url = phone ? `${base}?phone=${encodeURIComponent(phone)}&text=${encodeURIComponent(text)}` 
                                  : `${base}?text=${encodeURIComponent(text)}`;
                window.open(url, '_blank');
            }

            function updateSearch(value) {
                const url = new URL(window.location);
                const v = (value || '').trim();
                if (v.length > 0) {
                    url.searchParams.set('search', v);
                } else {
                    url.searchParams.delete('search');
                }
                window.location.href = url.toString();
            }

            function updateJob(value) {
                const url = new URL(window.location);
                if (value) {
                    url.searchParams.set('job_id', value);
                } else {
                    url.searchParams.delete('job_id');
                }
                window.location.href = url.toString();
            }

            function updateSkills(skill, checked) {
                const url = new URL(window.location);
                let currentSkills = url.searchParams.get('skills');
                let skillsArray = currentSkills ? currentSkills.split(',') : [];

                if (checked) {
                    if (!skillsArray.includes(skill)) {
                        skillsArray.push(skill);
                    }
                } else {
                    skillsArray = skillsArray.filter(s => s !== skill);
                }

                if (skillsArray.length > 0) {
                    url.searchParams.set('skills', skillsArray.join(','));
                } else {
                    url.searchParams.delete('skills');
                }
                window.location.href = url.toString();
            }

            function updateExperience(type, value) {
                const url = new URL(window.location);
                const param = type === 'min' ? 'min_experience' : 'max_experience';

                if (value && value !== '') {
                    url.searchParams.set(param, value);
                } else {
                    url.searchParams.delete(param);
                }
                window.location.href = url.toString();
            }

            function updateLanguage(value) {
                const url = new URL(window.location);
                if (value) {
                    url.searchParams.set('language', value);
                } else {
                    url.searchParams.delete('language');
                }
                window.location.href = url.toString();
            }

            function updateLocation(value) {
                const url = new URL(window.location);
                if (value) {
                    url.searchParams.set('location', value);
                } else {
                    url.searchParams.delete('location');
                }
                window.location.href = url.toString();
            }

            function updateDistance(value) {
                const url = new URL(window.location);
                if (value) {
                    url.searchParams.set('location_distance', value);
                } else {
                    url.searchParams.delete('location_distance');
                }
                window.location.href = url.toString();
            }

            function updateInterest(value) {
                const url = new URL(window.location);
                if (value) {
                    url.searchParams.set('interest', value);
                } else {
                    url.searchParams.delete('interest');
                }
                window.location.href = url.toString();
            }

            function openAddNote(applicationId) {
                const note = prompt('Add a note for this application');
                if (!note) return;
                fetch(`/employer/applications/${applicationId}/note`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ note })
                }).then(r => r.json()).then(data => {
                    if (data && data.success) {
                        alert('Note added');
                    } else {
                        alert('Error: ' + (data.error || 'Failed to add note'));
                    }
                }).catch(() => alert('Network error'));
            }

            function openInterviewPrompt(applicationId) {
                const when = prompt('Enter interview start (YYYY-MM-DD HH:MM)');
                if (!when) return;
                const dt = when.replace(' ', 'T');
                const start = new Date(dt);
                if (isNaN(start.getTime())) { alert('Invalid date/time'); return; }
                const end = new Date(start.getTime() + 30 * 60 * 1000);
                const tz = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Asia/Kolkata';
                const fmt = d => d.toISOString().slice(0,19).replace('T',' ');
                const payload = {
                    application_id: applicationId,
                    interview_type: 'phone',
                    scheduled_start: fmt(start),
                    scheduled_end: fmt(end),
                    timezone: tz
                };
                fetch('/employer/interviews/schedule', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify(payload)
                }).then(r => r.json()).then(data => {
                    if (data && data.error) {
                        alert('Error: ' + data.error);
                    } else {
                        alert('Interview scheduled');
                    }
                }).catch(() => alert('Network error'));
            }

            async function updateApplicationStatus(applicationId, status, comment = '') {
                const statusMap = {
                    'shortlisted': 'shortlisted',
                    'rejected': 'rejected',
                    'undecided': 'screening'
                };

                const dbStatus = statusMap[status] || status;

                try {
                    const response = await fetch(`/employer/applications/${applicationId}/status`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            status: dbStatus,
                            comment
                        })
                    });

                    const data = await response.json();
                    if (data.message) {
                        location.reload();
                    } else {
                        alert(data.error || 'Failed to update status');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred');
                }
            }

            function addNote(applicationId, currentStatus) {
                const note = prompt('Add internal note for this candidate:');
                if (note && note.trim().length > 0) {
                    updateApplicationStatus(applicationId, currentStatus, note.trim());
                }
            }

            async function generateScore(jobId, applicationId) {
                if (!confirm('Calculate match score for this candidate? This will analyze skills, experience, location, and other factors.')) {
                    return;
                }

                try {
                    const response = await fetch(`/employer/applications/${applicationId}/generate-score`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('Match score generated successfully!');
                        location.reload();
                    } else {
                        alert('Failed to generate score: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            }

            function showMoreActions(applicationId) {
                // TODO: Implement dropdown menu with more actions
                window.location.href = `/employer/applications/${applicationId}`;
            }

            function toggleIncludeApplied(checked) {
                const url = new URL(window.location);
                url.searchParams.set('include_applied', checked ? '1' : '0');
                window.location.href = url.toString();
            }

            function toggleHasResume(checked) {
                const url = new URL(window.location);
                if (checked) url.searchParams.set('has_resume', '1');
                else url.searchParams.delete('has_resume');
                window.location.href = url.toString();
            }

            function setActiveIn(days) {
                const url = new URL(window.location);
                if (days && days > 0) url.searchParams.set('active_in', String(days));
                else url.searchParams.delete('active_in');
                window.location.href = url.toString();
            }

            function updateSalary(type, value) {
                const url = new URL(window.location);
                const param = type === 'min' ? 'salary_min' : 'salary_max';

                if (value && value !== '') {
                    url.searchParams.set(param, value);
                } else {
                    url.searchParams.delete(param);
                }
                window.location.href = url.toString();
            }

            function updateEducation(level, checked) {
                const url = new URL(window.location);
                let currentEdu = url.searchParams.get('education');
                let eduArray = currentEdu ? currentEdu.split(',') : [];

                if (checked) {
                    if (!eduArray.includes(level)) {
                        eduArray.push(level);
                    }
                } else {
                    eduArray = eduArray.filter(e => e !== level);
                }

                if (eduArray.length > 0) {
                    url.searchParams.set('education', eduArray.join(','));
                } else {
                    url.searchParams.delete('education');
                }
                window.location.href = url.toString();
            }

            function toggleFilters() {
                const sidebar = document.getElementById('filtersSidebar');
                if (!sidebar) return;
                const isMobile = window.matchMedia('(max-width: 767px)').matches;
                if (isMobile) {
                    openFilters();
                    return;
                }
                if (sidebar.classList.contains('md:hidden')) {
                    sidebar.classList.remove('md:hidden');
                    sidebar.classList.add('md:block');
                } else {
                    sidebar.classList.remove('md:block');
                    sidebar.classList.add('md:hidden');
                }
            }

            function openFilters() {
                const p = document.getElementById('filtersPanel');
                if (p) p.classList.remove('hidden');
            }

            function closeFilters() {
                const p = document.getElementById('filtersPanel');
                if (p) p.classList.add('hidden');
            }

            function applyFilters() {
                const url = new URL(window.location);
                const job = document.getElementById('filtersJob')?.value || '';
                const status = document.getElementById('filtersStatus')?.value || 'all';
                const location = document.getElementById('filtersLocation')?.value || '';
                const interest = document.getElementById('filtersInterest')?.value || '';
                if (job) url.searchParams.set('job_id', job);
                else url.searchParams.delete('job_id');
                if (status && status !== 'all') url.searchParams.set('status', status);
                else url.searchParams.delete('status');
                if (location) url.searchParams.set('location', location);
                else url.searchParams.delete('location');
                if (interest) url.searchParams.set('interest', interest);
                else url.searchParams.delete('interest');
                window.location.href = url.toString();
            }
            async function startMessage(candidateUserId, jobId, applicationId) {
                if (!candidateUserId || candidateUserId === 0) {
                    alert('Candidate user ID not found');
                    return;
                }

                try {
                    const response = await fetch('/employer/messages/start', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            candidate_user_id: candidateUserId,
                            job_id: jobId || null,
                            application_id: applicationId || null,
                            initial_message: 'Hello, I saw your application and would like to discuss further.'
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        window.location.href = '/employer/messages?conversation=' + data.conversation_id;
                    } else {
                        alert('Error: ' + (data.error || 'Failed to start conversation'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while starting the conversation');
                }
            }
        </script>

        <!-- Bulk Action Bar -->
        <div id="bulkBar" class="hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-gray-200 shadow-lg">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="text-sm text-gray-700">Selected <span id="bulkCount" class="font-semibold">0</span></div>
                <div class="flex items-center gap-2">
                    <button onclick="bulkAction('shortlist')" class="px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded-md hover:bg-green-700">Shortlist</button>
                    <button onclick="bulkAction('reject')" class="px-3 py-1.5 bg-red-600 text-white text-xs font-semibold rounded-md hover:bg-red-700">Reject</button>
                    <button onclick="bulkAction('interview')" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-md hover:bg-indigo-700">Move to Interview</button>
                    <button onclick="const ids=Array.from(selected); if(ids.length){alert('Opening message composer for '+ids.length+' candidates');}" class="px-3 py-1.5 border border-gray-300 text-gray-800 text-xs font-semibold rounded-md hover:bg-gray-50">Message</button>
                </div>
            </div>
        </div>

        <!-- Candidate Preview Drawer -->
        <div id="candidateDrawer" class="hidden fixed top-0 right-0 h-full w-full sm:w-[420px] bg-white border-l border-gray-200 shadow-2xl z-50">
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Candidate Preview</h3>
                <button class="p-2 rounded hover:bg-gray-100" onclick="document.getElementById('candidateDrawer').classList.add('hidden')">
                    <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
            <div id="drawerContent" class="p-4 overflow-y-auto h-[calc(100%-56px)]">
                <div class="animate-pulse space-y-2">
                    <div class="h-4 bg-gray-100 rounded"></div>
                    <div class="h-4 bg-gray-100 rounded w-1/2"></div>
                    <div class="h-32 bg-gray-100 rounded"></div>
                </div>
            </div>
            <div id="pageConfig"
                data-can-see-contacts="<?= $canSeeContacts ? '1' : '0' ?>"
                data-can-download-resume="<?= $canDownloadResume ? '1' : '0' ?>"
                data-is-subscribed="<?= $isSubscribed ? '1' : '0' ?>"></div>
            <script>
                function openPreview(e, el) {
                    e.preventDefault();
                    const drawer = document.getElementById('candidateDrawer');
                    const content = document.getElementById('drawerContent');
                    if (!drawer || !content) return;
                    let data = {};
                    try {
                        const raw = el?.dataset?.candidate || '{}';
                        data = JSON.parse(raw);
                    } catch (_) {
                        return;
                    }

                    // Track View
                    if (data.candidate_id) {
                         fetch(`/employer/candidates/${data.candidate_id}/view`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            }
                        }).catch(err => console.error('Failed to track view', err));
                    }

                    const config = document.getElementById('pageConfig');
                    const canSeeContacts = config?.dataset?.canSeeContacts === '1';
                    const canDownloadResume = config?.dataset?.canDownloadResume === '1';
                    const isSubscribed = config?.dataset?.isSubscribed === '1';
                    const esc = s => (s ? String(s).replace(/[&<>"']/g, m => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    } [m])) : '');
                    const firstLetter = esc((data.full_name || data.candidate_email || 'U').charAt(0).toUpperCase());
                    const matched = Array.isArray(data.matched_skills) ? data.matched_skills : [];
                    const extra = Array.isArray(data.extra_relevant_skills) ? data.extra_relevant_skills : [];
                    const skills = [...matched.map(s => ({
                        t: s,
                        m: true
                    })), ...extra.map(s => ({
                        t: s,
                        m: false
                    }))].slice(0, 10);
                    const skillsHtml = skills.length ?
                        skills.map(s => `<span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium border ${s.m ? 'bg-green-50 text-green-700 border-green-200' : 'bg-gray-100 text-gray-700 border-gray-200'}">${esc(s.t)}</span>`).join('') :
                        '<span class="text-sm text-gray-500">No skills listed</span>';
                    let contactHtml = '';
                    if (canSeeContacts) {
                        const phone = esc(data.phone || '');
                        const email = esc(data.candidate_email || '');
                        contactHtml = `
                ${phone ? `<div class="flex items-center gap-2 text-sm text-gray-700"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg><span>${phone}</span></div>` : ''}
                ${email ? `<div class="flex items-center gap-2 text-sm text-gray-700 mt-2"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg><a href="mailto:${email}" class="text-blue-600 hover:underline">${email}</a></div>` : ''}`;
                    } else {
                        contactHtml = `
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3 text-sm text-yellow-800">
                    <p class="font-medium">Contact details locked</p>
                    <p class="mt-1">Upgrade your plan to view candidate phone numbers and emails.</p>
                    <a href="/employer/subscription/plans?upgrade=1" class="inline-block mt-2 text-xs font-semibold text-yellow-900 underline">Upgrade Now</a>
                </div>`;
                    }
                    let resumeHtml = '';
                    if (canDownloadResume && data.resume_url) {
                        const url = `/employer/candidates/${data.candidate_id}/resume`;
                        resumeHtml = `<a href="${url}" target="_blank" class="flex items-center justify-center w-full px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"><svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>Download Resume</a>`;
                    } else if (data.resume_url) {
                        resumeHtml = `<button disabled class="flex items-center justify-center w-full px-4 py-2 border border-gray-200 bg-gray-50 text-gray-400 text-sm font-medium rounded-md cursor-not-allowed"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>Download Resume (Locked)</button>`;
                    }
                    const html = `
            <div class="flex items-start gap-4 mb-6">
                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-primary text-xl font-bold shrink-0">${firstLetter}</div>
                <div>
                    <h4 class="text-xl font-bold text-gray-900">${esc(data.full_name || 'Unknown Candidate')}</h4>
                    <p class="text-sm text-gray-500">${esc(data.job_title || 'Applicant')}</p>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 capitalize">${esc(data.status || (data.application_id ? 'Applied' : 'Matched'))}</span>
                        ${data.overall_match_score ? `<span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">${esc(data.overall_match_score)}% Match</span>` : ''}
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                <div>
                    <h5 class="text-sm font-semibold text-gray-900 mb-3">Professional Details</h5>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><span class="block text-gray-500 text-xs">Experience</span><span class="font-medium">${esc(data.experience_years || '')} Years</span></div>
                        <div><span class="block text-gray-500 text-xs">Current Salary</span><span class="font-medium">${esc(data.current_salary || '')}</span></div>
                        <div><span class="block text-gray-500 text-xs">Expected Salary</span><span class="font-medium">${esc(data.expected_salary || '')}</span></div>
                        <div><span class="block text-gray-500 text-xs">Location</span><span class="font-medium">${esc(data.location_display || data.location || '')}</span></div>
                    </div>
                </div>
                <div>
                    <h5 class="text-sm font-semibold text-gray-900 mb-3">Skills</h5>
                    <div class="flex flex-wrap gap-2">${skillsHtml}</div>
                </div>
                <div>
                    <h5 class="text-sm font-semibold text-gray-900 mb-3">Contact Information</h5>
                    ${contactHtml}
                </div>
                <div>
                    <h5 class="text-sm font-semibold text-gray-900 mb-3">Resume</h5>
                    ${resumeHtml}
                </div>
                <div class="pt-4 border-t border-gray-100">
                    ${data.application_id ? `<a href="/employer/applications/${esc(data.application_id)}" class="block w-full text-center px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors font-medium">View Full Application</a>` : `<button disabled class="block w-full text-center px-4 py-2 bg-muted text-muted-foreground rounded-lg border border-border font-medium cursor-not-allowed">Open Full Application</button>`}
                </div>
            </div>`;
                    content.innerHTML = html;
                    drawer.classList.remove('hidden');
                }

                // Toggle Select All
                function toggleSelectAll(source) {
                    const checkboxes = document.querySelectorAll('.row-select');
                    checkboxes.forEach(cb => {
                        cb.checked = source.checked;
                        updateSelectedSet(cb);
                    });
                    updateBulkBar();
                }

                // Selected Set
                const selected = new Set();

                // Update Selected Set
                function updateSelectedSet(cb) {
                    if (cb.checked) {
                        selected.add(cb.dataset.appId);
                    } else {
                        selected.delete(cb.dataset.appId);
                    }
                }

                // Listen for individual checkbox changes
                document.addEventListener('change', function(e) {
                    if (e.target.classList.contains('row-select')) {
                        updateSelectedSet(e.target);
                        updateBulkBar();

                        // Update "Select All" checkbox state
                        const allCheckboxes = document.querySelectorAll('.row-select');
                        const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
                        const selectAll = document.getElementById('selectAll');
                        if (selectAll) selectAll.checked = allChecked;
                    }
                });

                // Update Bulk Action Bar
                function updateBulkBar() {
                    const bar = document.getElementById('bulkBar');
                    const count = document.getElementById('bulkCount');

                    if (selected.size > 0) {
                        bar.classList.remove('hidden');
                        count.textContent = selected.size;
                    } else {
                        bar.classList.add('hidden');
                    }
                }

                // Bulk Actions
                async function bulkAction(action) {
                    if (selected.size === 0) return;

                    if (!confirm(`Are you sure you want to ${action} ${selected.size} candidates?`)) return;

                    try {
                        const response = await fetch('/employer/applications/bulk-status', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            },
                            body: JSON.stringify({
                                ids: Array.from(selected),
                                status: action === 'reject' ? 'rejected' : (action === 'shortlist' ? 'shortlist' : 'interview')
                            })
                        });

                        const data = await response.json();
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + (data.error || 'Failed to update status'));
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred');
                    }
                }
            </script>
