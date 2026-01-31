<?php /** @var array $companies */ ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight">Top companies hiring now</h1>
        <p class="mt-1 text-gray-600 text-sm">Discover leading employers across industries and locations</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-8">
        <a href="/company/featured?industry=MNC" class="group rounded-2xl bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 p-4 flex items-center justify-between transition transform hover:-translate-y-0.5">
            <div>
                <div class="font-semibold text-gray-900">MNCs</div>
                <div class="text-xs text-gray-500"><?= isset($chipCounts['mnc']) ? number_format($chipCounts['mnc']) . '+' : 'Explore companies' ?></div>
            </div>
            <svg class="w-5 h-5 text-indigo-500 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </a>
        <a href="/company/featured?industry=Fintech" class="group rounded-2xl bg-sky-50 hover:bg-sky-100 border border-sky-100 p-4 flex items-center justify-between transition transform hover:-translate-y-0.5">
            <div>
                <div class="font-semibold text-gray-900">Fintech</div>
                <div class="text-xs text-gray-500"><?= isset($chipCounts['fintech']) ? number_format($chipCounts['fintech']) . '+' : 'Innovators in finance' ?></div>
            </div>
            <svg class="w-5 h-5 text-sky-500 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </a>
        <a href="/company/featured?industry=FMCG" class="group rounded-2xl bg-rose-50 hover:bg-rose-100 border border-rose-100 p-4 flex items-center justify-between transition transform hover:-translate-y-0.5">
            <div>
                <div class="font-semibold text-gray-900">FMCG & Retail</div>
                <div class="text-xs text-gray-500"><?= isset($chipCounts['fmcg']) ? number_format($chipCounts['fmcg']) . '+' : 'Consumer brands' ?></div>
            </div>
            <svg class="w-5 h-5 text-rose-500 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </a>
        <a href="/company/featured?industry=Startup" class="group rounded-2xl bg-amber-50 hover:bg-amber-100 border border-amber-100 p-4 flex items-center justify-between transition transform hover:-translate-y-0.5">
            <div>
                <div class="font-semibold text-gray-900">Startups</div>
                <div class="text-xs text-gray-500"><?= isset($chipCounts['startup']) ? number_format($chipCounts['startup']) . '+' : 'High-growth teams' ?></div>
            </div>
            <svg class="w-5 h-5 text-amber-500 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </a>
        <a href="/company/featured?industry=Edtech" class="group rounded-2xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-100 p-4 flex items-center justify-between transition transform hover:-translate-y-0.5">
            <div>
                <div class="font-semibold text-gray-900">Edtech</div>
                <div class="text-xs text-gray-500"><?= isset($chipCounts['edtech']) ? number_format($chipCounts['edtech']) . '+' : 'Learning platforms' ?></div>
            </div>
            <svg class="w-5 h-5 text-emerald-500 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <aside class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-5">
                <div class="font-semibold text-gray-900">All Filters</div>
                <form method="GET" action="/company/featured" class="space-y-4">
                    <div>
                        <label class="text-xs font-medium text-gray-700">Search company</label>
                        <input type="text" name="q" value="<?= htmlspecialchars($filters['q'] ?? '') ?>" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-500 focus:border-gray-500" placeholder="Search company">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700">Location</label>
                        <input type="text" name="location" value="<?= htmlspecialchars($filters['location'] ?? '') ?>" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-500 focus:border-gray-500" placeholder="e.g. Mumbai">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700">Industry</label>
                        <input type="text" name="industry" value="<?= htmlspecialchars($filters['industry'] ?? '') ?>" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-500 focus:border-gray-500" placeholder="e.g. IT Services">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700">Department</label>
                        <select name="department" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-500 focus:border-gray-500">
                            <option value="" <?= (($filters['department'] ?? '') === '') ? 'selected' : '' ?>>All Departments</option>
                            <option value="Engineering - Software & QA" <?= (($filters['department'] ?? '') === 'Engineering - Software & QA') ? 'selected' : '' ?>>Engineering - Software & QA</option>
                            <option value="Sales & Business Development" <?= (($filters['department'] ?? '') === 'Sales & Business Development') ? 'selected' : '' ?>>Sales & Business Development</option>
                            <option value="Finance & Accounting" <?= (($filters['department'] ?? '') === 'Finance & Accounting') ? 'selected' : '' ?>>Finance & Accounting</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700">Experience</label>
                        <div class="mt-2 flex items-center gap-3">
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="radio" name="experience" value="entry" <?= (($filters['experience'] ?? '') === 'entry') ? 'checked' : '' ?> class="rounded">
                                Entry Level
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="radio" name="experience" value="experienced" <?= (($filters['experience'] ?? '') === 'experienced') ? 'checked' : '' ?> class="rounded">
                                Experienced
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="radio" name="experience" value="" <?= (($filters['experience'] ?? '') === '') ? 'checked' : '' ?> class="rounded">
                                All
                            </label>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-medium text-gray-700">Founded from</label>
                            <input type="number" name="year_from" value="<?= htmlspecialchars($filters['year_from'] ?? '') ?>" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-500 focus:border-gray-500" placeholder="1990">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700">Founded to</label>
                            <input type="number" name="year_to" value="<?= htmlspecialchars($filters['year_to'] ?? '') ?>" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-500 focus:border-gray-500" placeholder="2024">
                        </div>
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-black transition font-semibold">Apply Filters</button>
                </form>
            </div>
        </aside>
        <section class="lg:col-span-3">
            <?php if (!empty($companies)): ?>
            <div class="grid md:grid-cols-2 gap-4">
                <?php foreach ($companies as $co): ?>
                <a href="<?= !empty($co['slug']) ? '/company/' . htmlspecialchars($co['slug']) : '/candidate/jobs?company=' . urlencode($co['name'] ?? '') ?>" 
                   class="block bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-gray-100 overflow-hidden flex items-center justify-center shrink-0">
                                <?php if (!empty($co['logo_url'])): ?>
                                    <img src="<?= htmlspecialchars($co['logo_url']) ?>" alt="<?= htmlspecialchars($co['name'] ?? 'Company') ?>" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="hidden w-full h-full items-center justify-center text-gray-600 font-semibold">
                                        <?= strtoupper(substr($co['name'] ?? 'C', 0, 1)) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-gray-600 font-semibold">
                                        <?= strtoupper(substr($co['name'] ?? 'C', 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div class="text-gray-900 font-semibold truncate"><?= htmlspecialchars($co['name'] ?? 'Company') ?></div>
                                    <div class="text-sm text-gray-600">
                                        <?php $rating = (float)($co['rating'] ?? 0); $reviews = (int)($co['reviews_count'] ?? 0); ?>
                                        <span class="inline-flex items-center gap-1">
                                            <span><?= number_format($rating, 1) ?></span>
                                            <span class="text-orange-500">★</span>
                                            <span class="text-gray-500"><?= $reviews ?> reviews</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-2 text-xs text-gray-700 flex flex-wrap gap-2">
                                    <?php if (!empty($co['industry'])): ?>
                                        <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-700"><?= htmlspecialchars($co['industry']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($co['founded_year'])): ?>
                                        <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-700">Founded: <?= htmlspecialchars($co['founded_year']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($co['company_size'])): ?>
                                        <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-700"><?= htmlspecialchars($co['company_size']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center text-gray-600 bg-white rounded-2xl border border-gray-200 p-10">No featured companies found.</div>
            <?php endif; ?>
        </section>
    </div>
</div>
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900">How it works</h2>
            <p class="text-gray-600 mt-2">A simple journey to get hired on Mindware Infotech</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-8 text-center shadow-sm hover:shadow-md transition">
                <div class="flex justify-center mb-5 text-gray-900">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                </div>
                <div class="font-semibold text-lg text-gray-900 mb-2">Create Account</div>
                <div class="text-gray-600 text-sm">Register as a candidate or employer in minutes</div>
            </div>
            <div class="bg-white border border-gray-100 rounded-2xl p-8 text-center shadow-sm hover:shadow-md transition">
                <div class="flex justify-center mb-5 text-gray-900">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M6 2h9l5 5v15a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/>
                        <path stroke-width="1.8" d="M14 2v6h6"/>
                    </svg>
                </div>
                <div class="font-semibold text-lg text-gray-900 mb-2">Upload Resume</div>
                <div class="text-gray-600 text-sm">Build or upload a professional resume</div>
            </div>
            <div class="bg-white border border-gray-100 rounded-2xl p-8 text-center shadow-sm hover:shadow-md transition">
                <div class="flex justify-center mb-5 text-gray-900">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18"/>
                    </svg>
                </div>
                <div class="font-semibold text-lg text-gray-900 mb-2">Find Jobs</div>
                <div class="text-gray-600 text-sm">Explore job opportunities across industries</div>
            </div>
            <div class="bg-white border border-gray-100 rounded-2xl p-8 text-center shadow-sm hover:shadow-md transition">
                <div class="flex justify-center mb-5 text-gray-900">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5 4v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6"/>
                    </svg>
                </div>
                <div class="font-semibold text-lg text-gray-900 mb-2">Apply Job</div>
                <div class="text-gray-600 text-sm">Apply and connect directly with employers</div>
            </div>
        </div>
    </div>
</section>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="/register-candidate" class="px-4 py-3 rounded-xl bg-blue-600 text-white font-semibold text-center hover:bg-blue-700 transition">Register</a>
        <a href="/candidate/dashboard" class="px-4 py-3 rounded-xl bg-gray-900 text-white font-semibold text-center hover:bg-black transition">Upload Resume</a>
        <a href="/jobs" class="px-4 py-3 rounded-xl bg-blue-600 text-white font-semibold text-center hover:bg-blue-700 transition">Browse Jobs</a>
        <a href="/jobs" class="px-4 py-3 rounded-xl bg-blue-600 text-white font-semibold text-center hover:bg-blue-700 transition">Apply</a>
    </div>
</div>
