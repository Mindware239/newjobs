<?php
/** @var array $candidate */
/** @var array $applications */
/** @var array $savedJobs */
/** @var array $skills */
/** @var array $education */
/** @var array $experience */
/** @var array $qualityScores */
/** @var array $loginHistory */
/** @var array $employmentDocuments */
/** @var array $nearbyJobs */
/** @var array $activeEmployers */
/** @var \App\Models\User $user */
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-[#f8fafc]">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between mb-8">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="/admin/dashboard" class="text-gray-400 hover:text-primary transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <a href="/admin/candidates" class="ml-2 text-sm font-medium text-gray-500 hover:text-primary transition-colors">Candidates</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="ml-2 text-sm font-medium text-gray-900">Profile Details</span>
                    </div>
                </li>
            </ol>
        </nav>
        <a href="/admin/candidates" class="inline-flex items-center text-sm font-medium text-primary hover:text-primary-700">
            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to List
        </a>
    </div>

    <!-- Main Profile Card -->
    <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden mb-8">
        <!-- Professional Cover -->
        <div class="h-48 bg-gradient-to-r from-primary to-primary-600 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <svg width="100%" height="100%" fill="none"><defs><pattern id="dots" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1" fill="white"/></pattern></defs><rect width="100%" height="100%" fill="url(#dots)"/></svg>
            </div>
            <div class="absolute bottom-4 right-6 flex gap-2">
                <span class="px-4 py-1.5 bg-white/20 backdrop-blur-md text-white text-xs font-bold rounded-full border border-white/30">
                    ID: #<?= $candidate['id'] ?>
                </span>
                <?php $prem = ((int)($candidate['is_premium'] ?? 0) === 1) && !empty($candidate['premium_expires_at']) && strtotime($candidate['premium_expires_at']) > time(); ?>
                <?php if($prem): ?>
                    <span class="px-4 py-1.5 bg-yellow-400 text-yellow-900 text-xs font-bold rounded-full flex items-center shadow-lg">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        PREMIUM
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="px-8 pb-8">
            <div class="flex flex-col md:flex-row items-center md:items-end -mt-16 mb-8 gap-8">
                <!-- Profile Image -->
                <div class="relative group">
                    <div class="h-40 w-40 rounded-3xl ring-8 ring-white bg-white shadow-2xl overflow-hidden">
                        <?php if (!empty($candidate['profile_picture'])): ?>
                            <img class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" 
                                 src="<?= htmlspecialchars($candidate['profile_picture']) ?>" 
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                 alt="">
                            <div class="hidden h-full w-full items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100 text-5xl font-black text-primary">
                                <?= strtoupper(substr($candidate['full_name'] ?? 'U', 0, 1)) ?>
                            </div>
                        <?php else: ?>
                            <div class="h-full w-full flex items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100 text-5xl font-black text-primary">
                                <?= strtoupper(substr($candidate['full_name'] ?? 'U', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button class="absolute bottom-2 right-2 p-2 bg-white rounded-xl shadow-lg border border-gray-100 text-gray-500 hover:text-primary transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </button>
                </div>

                <!-- Info Header -->
                <div class="flex-1 text-center md:text-left">
                    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                        <div>
                            <h1 class="text-4xl font-black text-gray-900 tracking-tight">
                                <?= htmlspecialchars($candidate['full_name'] ?? 'Unknown Candidate') ?>
                            </h1>
                            <p class="text-lg font-bold text-primary mt-1"><?= htmlspecialchars($candidate['professional_title'] ?? 'Aspiring Professional') ?></p>
                            <div class="mt-4 flex flex-wrap items-center justify-center md:justify-start gap-4 text-sm text-gray-500 font-medium">
                                <span class="flex items-center px-3 py-1 bg-gray-50 rounded-full">
                                    <svg class="mr-2 h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <?= htmlspecialchars($candidate['city'] ?? 'Location N/A') ?><?= !empty($candidate['country']) ? ', ' . htmlspecialchars($candidate['country']) : '' ?>
                                </span>
                                <span class="flex items-center px-3 py-1 bg-gray-50 rounded-full">
                                    <svg class="mr-2 h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Joined <?= date('M d, Y', strtotime($candidate['created_at'] ?? 'now')) ?>
                                </span>
                                <span class="flex items-center px-3 py-1 <?= ($candidate['user_status'] ?? '') === 'active' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' ?> rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full mr-2 <?= ($candidate['user_status'] ?? '') === 'active' ? 'bg-green-500' : 'bg-red-500' ?>"></span>
                                    <?= ucfirst($candidate['user_status'] ?? 'unknown') ?>
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                            <?php if (!empty($candidate['resume_url'])): ?>
                                <a href="<?= htmlspecialchars($candidate['resume_url']) ?>" target="_blank" class="flex-1 sm:flex-none inline-flex items-center justify-center px-6 py-3 bg-white border-2 border-primary text-primary font-bold rounded-2xl hover:bg-primary hover:text-white transition-all duration-300 group shadow-lg shadow-primary/10">
                                    <svg class="mr-2 h-5 w-5 transition-transform group-hover:-translate-y-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.586l5.414 5.414a1 1 0 01.586 1.414V19a2 2 0 01-2 2z"/></svg>
                                    Download Resume
                                </a>
                            <?php endif; ?>
                            <button class="flex-1 sm:flex-none inline-flex items-center justify-center px-6 py-3 bg-primary text-white font-bold rounded-2xl hover:bg-primary-600 transition-all duration-300 shadow-xl shadow-primary/20">
                                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Sidebar Info -->
        <div class="lg:col-span-4 space-y-8">
            <!-- Contact Card -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center">
                    <span class="p-2 bg-primary-50 text-primary rounded-xl mr-3">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 01-2 2z"/></svg>
                    </span>
                    Contact Info
                </h3>
                <div class="space-y-6">
                    <div class="group">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Email Address</p>
                        <div class="flex items-center justify-between">
                            <p class="text-gray-900 font-bold truncate pr-4"><?= htmlspecialchars($candidate['email'] ?? 'N/A') ?></p>
                            <?php if(!empty($candidate['is_email_verified'])): ?>
                                <svg class="w-5 h-5 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <?php else: ?>
                                <span class="px-2 py-0.5 bg-red-50 text-red-600 text-[10px] font-black rounded uppercase">Unverified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Phone / Mobile</p>
                        <p class="text-gray-900 font-bold"><?= htmlspecialchars($candidate['phone'] ?? $candidate['mobile'] ?? 'N/A') ?></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Source</p>
                            <span class="inline-flex px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-lg uppercase">
                                <?= htmlspecialchars($candidate['source'] ?? 'Website') ?>
                            </span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Visibility</p>
                            <span class="inline-flex px-2.5 py-1 <?= ($candidate['visibility'] ?? '') === 'public' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?> text-xs font-bold rounded-lg uppercase">
                                <?= ucfirst($candidate['visibility'] ?? 'Limited') ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Management -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 overflow-hidden relative">
                <div class="absolute top-0 left-0 w-1 h-full bg-red-500"></div>
                <h3 class="text-xl font-black text-gray-900 mb-6">Account Control</h3>
                <div class="space-y-3">
                    <?php if (($candidate['user_status'] ?? '') === 'active'): ?>
                        <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/block">
                            <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="w-full flex items-center justify-center px-4 py-3 bg-red-50 text-red-600 font-bold rounded-2xl hover:bg-red-600 hover:text-white transition-all duration-300 group">
                                <svg class="mr-2 h-5 w-5 transition-transform group-hover:rotate-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                Block Account
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/unblock">
                            <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="w-full flex items-center justify-center px-4 py-3 bg-green-50 text-green-600 font-bold rounded-2xl hover:bg-green-600 hover:text-white transition-all duration-300">
                                <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Activate Account
                            </button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/delete" onsubmit="return confirm('CRITICAL: Delete this account forever?');">
                        <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <button type="submit" class="w-full flex items-center justify-center px-4 py-3 bg-white border-2 border-gray-100 text-gray-400 font-bold rounded-2xl hover:bg-red-50 hover:text-red-600 hover:border-red-100 transition-all duration-300">
                            <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Permanently Delete
                        </button>
                    </form>
                </div>
            </div>

            <!-- Premium Subscription -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4">
                    <svg class="w-12 h-12 text-yellow-100 rotate-12" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
                <h3 class="text-xl font-black text-gray-900 mb-6">Membership</h3>
                <div class="space-y-6">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Current Plan</p>
                            <p class="text-gray-900 font-black text-lg"><?= $prem ? 'Premium' : 'Standard' ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Status</p>
                            <span class="px-3 py-1 <?= $prem ? 'bg-yellow-400 text-yellow-900' : 'bg-gray-200 text-gray-600' ?> text-[10px] font-black rounded-full">
                                <?= $prem ? 'ACTIVE' : 'INACTIVE' ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/premium/enable" class="col-span-1">
                            <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <input type="hidden" name="days" value="30">
                            <button type="submit" class="w-full py-3 bg-yellow-400 text-yellow-900 font-bold rounded-2xl hover:bg-yellow-500 transition-all text-xs shadow-lg shadow-yellow-200">Enable 30d</button>
                        </form>
                        <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/premium/disable" class="col-span-1">
                            <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="w-full py-3 bg-gray-100 text-gray-600 font-bold rounded-2xl hover:bg-gray-200 transition-all text-xs">Disable</button>
                        </form>
                    </div>

                    <div class="pt-6 border-t border-gray-100">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4">Adjust Validity</p>
                        <div class="space-y-3">
                            <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/premium/extend" class="flex gap-2">
                                <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <input type="number" name="days" value="7" class="w-20 bg-gray-50 border-0 rounded-xl font-bold text-sm focus:ring-primary">
                                <button class="flex-1 bg-green-500 text-white font-bold py-2 rounded-xl text-xs hover:bg-green-600 transition-all">Extend Days</button>
                            </form>
                            <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/premium/reduce" class="flex gap-2">
                                <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <input type="number" name="days" value="7" class="w-20 bg-gray-50 border-0 rounded-xl font-bold text-sm focus:ring-primary">
                                <button class="flex-1 bg-red-500 text-white font-bold py-2 rounded-xl text-xs hover:bg-red-600 transition-all">Reduce Days</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="lg:col-span-8 space-y-8">
            <!-- Professional Summary & Skills -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-black text-gray-900 flex items-center">
                        <span class="p-2 bg-primary-50 text-primary rounded-xl mr-4">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </span>
                        Professional Skills
                    </h3>
                    <span class="px-4 py-1.5 bg-gray-100 text-gray-600 text-[10px] font-black rounded-full uppercase tracking-widest">
                        <?= count($skills) ?> Total
                    </span>
                </div>
                <?php if (!empty($skills)): ?>
                    <div class="flex flex-wrap gap-3">
                        <?php foreach ($skills as $skill): ?>
                            <div class="flex items-center bg-white border-2 border-gray-50 rounded-2xl p-1 pr-4 hover:border-primary/20 hover:bg-primary-50/30 transition-all duration-300">
                                <div class="w-10 h-10 bg-primary-50 text-primary font-black flex items-center justify-center rounded-xl mr-3 text-xs">
                                    <?= strtoupper(substr($skill['name'] ?? 'S', 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-gray-900"><?= htmlspecialchars($skill['name'] ?? '') ?></p>
                                    <?php if(!empty($skill['proficiency_level'])): ?>
                                        <p class="text-[10px] font-bold text-primary uppercase"><?= htmlspecialchars($skill['proficiency_level']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-10 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200">
                        <p class="text-gray-400 font-bold">No skills documented yet.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Professional Timeline (Experience & Education) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Experience -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xl font-black text-gray-900 mb-8 flex items-center">
                        <span class="p-2 bg-orange-50 text-orange-500 rounded-xl mr-4">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </span>
                        Experience
                    </h3>
                    <?php if (!empty($experience)): ?>
                        <div class="space-y-8 relative before:absolute before:inset-0 before:left-3 before:w-0.5 before:bg-gray-100">
                            <?php foreach ($experience as $exp): ?>
                                <div class="relative pl-10 group">
                                    <div class="absolute left-0 top-1 w-6 h-6 bg-white border-4 border-orange-500 rounded-full z-10 group-hover:scale-125 transition-transform"></div>
                                    <p class="text-[10px] font-black text-orange-500 uppercase tracking-widest mb-1">
                                        <?= !empty($exp['start_date']) ? date('M Y', strtotime($exp['start_date'])) : '' ?> — <?= (int)($exp['is_current'] ?? 0) === 1 ? 'PRESENT' : (!empty($exp['end_date']) ? date('M Y', strtotime($exp['end_date'])) : 'N/A') ?>
                                    </p>
                                    <h4 class="text-base font-black text-gray-900 leading-tight"><?= htmlspecialchars($exp['job_title'] ?? 'Role') ?></h4>
                                    <p class="text-sm font-bold text-gray-500"><?= htmlspecialchars($exp['company_name'] ?? 'Company') ?></p>
                                    <?php if (!empty($exp['description'])): ?>
                                        <p class="text-xs text-gray-400 mt-2 leading-relaxed line-clamp-2"><?= htmlspecialchars($exp['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-400 font-bold italic">Fresher / No experience data.</p>
                    <?php endif; ?>
                </div>

                <!-- Education -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xl font-black text-gray-900 mb-8 flex items-center">
                        <span class="p-2 bg-blue-50 text-blue-500 rounded-xl mr-4">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                        </span>
                        Education
                    </h3>
                    <?php if (!empty($education)): ?>
                        <div class="space-y-8 relative before:absolute before:inset-0 before:left-3 before:w-0.5 before:bg-gray-100">
                            <?php foreach ($education as $edu): ?>
                                <div class="relative pl-10 group">
                                    <div class="absolute left-0 top-1 w-6 h-6 bg-white border-4 border-blue-500 rounded-full z-10 group-hover:scale-125 transition-transform"></div>
                                    <p class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-1">
                                        Graduated: <?= !empty($edu['end_date']) ? date('Y', strtotime($edu['end_date'])) : 'N/A' ?>
                                    </p>
                                    <h4 class="text-base font-black text-gray-900 leading-tight"><?= htmlspecialchars($edu['degree'] ?? 'Degree') ?></h4>
                                    <p class="text-sm font-bold text-gray-500"><?= htmlspecialchars($edu['field_of_study'] ?? 'Field') ?></p>
                                    <p class="text-xs text-gray-400 mt-1"><?= htmlspecialchars($edu['institution'] ?? 'University') ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-400 font-bold italic">Education details not provided.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Activity & Applications Table -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900">Application History</h3>
                    <span class="px-4 py-1.5 bg-primary text-white text-[10px] font-black rounded-full shadow-lg shadow-primary/20">
                        <?= count($applications) ?> APPLICATIONS
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/30">
                            <tr>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Position</th>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Applied Date</th>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Current Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($applications as $app): ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-8 py-5">
                                        <a href="/admin/jobs/<?= $app['job_id'] ?>" class="text-sm font-black text-gray-900 hover:text-primary transition-colors">
                                            <?= htmlspecialchars($app['job_title'] ?? 'N/A') ?>
                                        </a>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-bold text-gray-500">
                                        <?= date('M d, Y', strtotime($app['created_at'] ?? 'now')) ?>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-white border border-gray-200 text-gray-700 shadow-sm">
                                            <?= ucfirst($app['status'] ?? 'pending') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if(empty($applications)): ?>
                                <tr>
                                    <td colspan="3" class="px-8 py-10 text-center text-sm font-bold text-gray-400 italic">No active applications found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-primary { color: #f05537; }
    .bg-primary { background-color: #f05537; }
    .bg-primary-50 { background-color: #fef2f0; }
    .border-primary { border-color: #f05537; }
    .hover\:text-primary:hover { color: #f05537; }
    .hover\:bg-primary:hover { background-color: #f05537; }
    .shadow-primary\/10 { box-shadow: 0 4px 6px -1px rgba(240, 85, 55, 0.1); }
    .shadow-primary\/20 { box-shadow: 0 10px 15px -3px rgba(240, 85, 55, 0.2); }
    .from-primary { --tw-gradient-from: #f05537; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(240, 85, 55, 0)); }
    .to-primary-600 { --tw-gradient-to: #d9442a; }
</style>
