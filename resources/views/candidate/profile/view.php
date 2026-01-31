<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>My Profile - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <style>
        header .container { max-width: 1200px; padding-left: 24px; padding-right: 24px; }
        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
    <?php $base = '/'; require __DIR__ . '/../../include/header.php'; ?>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Profile Header -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
            <div class="flex items-start gap-6">
                    <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden">
                        <?php if (!empty($candidate->attributes['profile_picture'])): ?>
                        <img src="<?= htmlspecialchars($candidate->attributes['profile_picture']) ?>" 
                             alt="Profile" class="w-full h-full object-cover">
                        <?php else: ?>
                        <?php 
                        $candidateName = $candidate->attributes['full_name'] ?? null;
                        $user = $candidate->user();
                        if (empty($candidateName) && $user) {
                            $candidateName = $user->attributes['google_name'] ?? $user->attributes['apple_name'] ?? null;
                        }
                        $initials = strtoupper(substr($candidateName ?? 'U', 0, 1));
                        // Try to get Google picture if no profile picture
                        if (empty($candidate->attributes['profile_picture']) && $user && !empty($user->attributes['google_picture'])) {
                            echo '<img src="' . htmlspecialchars($user->attributes['google_picture']) . '" alt="Profile" class="w-full h-full object-cover">';
                        } else {
                            echo '<span class="text-3xl text-gray-400">' . $initials . '</span>';
                        }
                        ?>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <?php 
                        $candidateName = $candidate->attributes['full_name'] ?? null;
                        $user = $candidate->user();
                        if (empty($candidateName) && $user) {
                            // Try to get name from user's Google/Apple data
                            $candidateName = $user->attributes['google_name'] ?? $user->attributes['apple_name'] ?? null;
                        }
                        $displayName = $candidateName ?: 'Your Name';
                        ?>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">
                            <?= htmlspecialchars($displayName) ?>
                        </h1>
                        <div class="flex items-center gap-4 text-gray-600 mb-4">
                            <?php if (!empty($candidate->attributes['city'])): ?>
                            <div class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 rounded-full border border-blue-100">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="text-sm font-medium"><?= htmlspecialchars($candidate->attributes['city']) ?><?= !empty($candidate->attributes['state']) ? ', ' . htmlspecialchars($candidate->attributes['state']) : '' ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($candidate->attributes['mobile'])): ?>
                            <div class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 rounded-full border border-blue-100">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <span class="text-sm font-medium"><?= htmlspecialchars($candidate->attributes['mobile']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-600">Profile Strength:</span>
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" 
                                         style="width: <?= $candidate->attributes['profile_strength'] ?? 0 ?>%"></div>
                                </div>
                            <span class="text-sm font-semibold text-blue-700">
                                <?= $candidate->attributes['profile_strength'] ?? 0 ?>%
                            </span>
                        </div>
                            <?php if ($candidate->isPremium()): ?>
                            <span class="px-3 py-1 bg-gradient-to-r from-orange-100 to-orange-200 text-orange-800 rounded-full text-sm font-bold shadow-sm border border-orange-200 flex items-center gap-1">
                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                Premium Member
                            </span>
                            <?php endif; ?>
                            <?php if ($candidate->attributes['is_verified'] ?? 0): ?>
                            <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-sm font-semibold border border-blue-200 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Verified
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="/candidate/profile/complete" 
                       class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-md hover:from-blue-700 hover:to-blue-800 flex items-center gap-2 font-medium shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5 duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Profile
                    </a>
                    <a href="/candidate/change-password" 
                       class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 flex items-center gap-2 font-medium shadow-sm hover:shadow transition transform hover:-translate-y-0.5 duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Change Password
                    </a>
                </div>
            </div>

            <!-- Top Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Basic Information -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
                <h2 class="text-2xl font-bold mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Basic Information
                </h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <label class="text-sm text-gray-600">Date of Birth</label>
                        </div>
                        <p class="font-semibold pl-10">
                            <?= $candidate->attributes['dob'] ? date('M d, Y', strtotime($candidate->attributes['dob'])) : 'Not provided' ?>
                        </p>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <label class="text-sm text-gray-600">Gender</label>
                        </div>
                        <p class="font-semibold pl-10">
                            <?= ucfirst($candidate->attributes['gender'] ?? 'Not specified') ?>
                        </p>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <label class="text-sm text-gray-600">Location</label>
                        </div>
                        <p class="font-semibold pl-10">
                            <?= htmlspecialchars(trim(($candidate->attributes['city'] ?? '') . ', ' . ($candidate->attributes['state'] ?? '') . ', ' . ($candidate->attributes['country'] ?? ''), ', ')) ?: 'Not provided' ?>
                        </p>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <label class="text-sm text-gray-600">Mobile</label>
                        </div>
                        <p class="font-semibold pl-10">
                            <?= htmlspecialchars($candidate->attributes['mobile'] ?? 'Not provided') ?>
                        </p>
                    </div>
                </div>
                <?php if (!empty($candidate->attributes['self_introduction'])): ?>
                <div class="mt-6 pt-6 border-t">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <label class="text-sm text-gray-600">About Me</label>
                    </div>
                    <div class="pl-10 text-gray-700 leading-relaxed">
                        <?= nl2br(htmlspecialchars($candidate->attributes['self_introduction'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <!-- Resume & Video -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
                <h2 class="text-2xl font-bold mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Resume & Video
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <label class="text-sm text-gray-600">Resume/CV</label>
                        </div>
                        <?php if (!empty($candidate->attributes['resume_url'])): ?>
                        <div class="pl-10">
                            <a href="<?= htmlspecialchars($candidate->attributes['resume_url']) ?>" 
                               target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition font-medium">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Resume
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        </div>
                        <?php else: ?>
                        <p class="text-gray-500 pl-10 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            No resume uploaded
                        </p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <label class="text-sm text-gray-600">Introduction Video</label>
                        </div>
                        <?php if (!empty($candidate->attributes['video_intro_url'])): ?>
                        <div class="pl-10">
                            <?php if ($candidate->attributes['video_intro_type'] === 'youtube'): ?>
                            <a href="<?= htmlspecialchars($candidate->attributes['video_intro_url']) ?>" 
                               target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition font-medium">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                                View Video
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                            <?php if ($candidate->isPremium()): ?>
                            <button type="button" 
                                    class="inline-flex items-center gap-2 ml-3 px-4 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition font-medium"
                                    onclick="deleteCandidateVideo(this)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7"></path>
                                </svg>
                                Delete Video
                            </button>
                            <?php endif; ?>
                            <?php else: ?>
                            <a href="<?= htmlspecialchars($candidate->attributes['video_intro_url']) ?>" 
                               target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition font-medium">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                View Video
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                            <?php if ($candidate->isPremium()): ?>
                            <button type="button" 
                                    class="inline-flex items-center gap-2 ml-3 px-4 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition font-medium"
                                    onclick="deleteCandidateVideo(this)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7"></path>
                                </svg>
                                Delete Video
                            </button>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-gray-500 pl-10 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            No video uploaded
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            </div>

            <!-- Middle Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Education -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold flex items-center gap-2">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                        </svg>
                        Education
                    </h2>
                    <?php if (empty($education)): ?>
                    <a href="/candidate/profile/complete" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Education
                    </a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($education)): ?>
                <div class="space-y-6">
                    <?php foreach ($education as $edu): ?>
                    <div class="relative pl-8 border-l-2 border-indigo-200 hover:border-indigo-500 transition-colors duration-300">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-indigo-100 border-2 border-indigo-500"></div>
                        <div class="bg-gray-50 rounded-lg p-4 hover:bg-indigo-50 transition-colors duration-300 group">
                            <div class="flex flex-wrap justify-between items-start gap-2 mb-2">
                                <div>
                                    <h3 class="font-bold text-gray-900 text-lg group-hover:text-indigo-700 transition-colors"><?= htmlspecialchars($edu['degree'] ?? '') ?></h3>
                                    <p class="text-indigo-600 font-medium"><?= htmlspecialchars($edu['institution'] ?? '') ?></p>
                                </div>
                                <div class="text-sm text-gray-500 bg-white px-3 py-1 rounded-full border shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <?php if (!empty($edu['start_date'])): ?>
                                    <span><?= date('Y', strtotime($edu['start_date'])) ?></span>
                                    <?php endif; ?>
                                    <span>-</span>
                                    <?php if ($edu['is_current'] ?? 0): ?>
                                    <span class="text-indigo-700 font-semibold">Present</span>
                                    <?php elseif (!empty($edu['end_date'])): ?>
                                    <span><?= date('Y', strtotime($edu['end_date'])) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if (!empty($edu['field_of_study'])): ?>
                            <p class="text-sm text-gray-600 mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                <?= htmlspecialchars($edu['field_of_study']) ?>
                            </p>
                            <?php endif; ?>

                            <?php if (!empty($edu['grade'])): ?>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 text-sm font-medium rounded-full border border-emerald-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"></path>
                                </svg>
                                Grade: <?= htmlspecialchars($edu['grade']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                    </svg>
                    <p class="text-gray-500 mb-3">No education details added yet</p>
                    <a href="/candidate/profile/complete" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm">
                        Add Education Details
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <!-- Experience -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold flex items-center gap-2">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Work Experience
                    </h2>
                    <?php if (empty($experience)): ?>
                    <a href="/candidate/profile/complete" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Experience
                    </a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($experience)): ?>
                <div class="space-y-6">
                    <?php foreach ($experience as $exp): ?>
                    <div class="relative pl-8 border-l-2 border-indigo-200 hover:border-indigo-500 transition-colors duration-300">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-indigo-100 border-2 border-indigo-500"></div>
                        <div class="bg-gray-50 rounded-lg p-4 hover:bg-indigo-50 transition-colors duration-300 group">
                            <div class="flex flex-wrap justify-between items-start gap-2 mb-2">
                                <div>
                                    <h3 class="font-bold text-gray-900 text-lg group-hover:text-indigo-700 transition-colors"><?= htmlspecialchars($exp['job_title'] ?? '') ?></h3>
                                    <p class="text-indigo-600 font-medium text-base"><?= htmlspecialchars($exp['company_name'] ?? '') ?></p>
                                </div>
                                <div class="text-sm text-gray-500 bg-white px-3 py-1 rounded-full border shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <?php if (!empty($exp['start_date'])): ?>
                                    <span><?= date('M Y', strtotime($exp['start_date'])) ?></span>
                                    <?php endif; ?>
                                    <span>-</span>
                                    <?php if ($exp['is_current'] ?? 0): ?>
                                    <span class="text-indigo-700 font-semibold">Present</span>
                                    <?php elseif (!empty($exp['end_date'])): ?>
                                    <span><?= date('M Y', strtotime($exp['end_date'])) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($exp['location'])): ?>
                            <p class="text-sm text-gray-500 mb-3 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <?= htmlspecialchars($exp['location']) ?>
                            </p>
                            <?php endif; ?>

                            <?php if (!empty($exp['description'])): ?>
                            <div class="mt-3 text-gray-600 text-sm leading-relaxed bg-white/50 p-3 rounded border border-gray-100">
                                <?= nl2br(htmlspecialchars($exp['description'])) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-gray-500 mb-3">No work experience added yet</p>
                    <a href="/candidate/profile/complete" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm">
                        Add Work Experience
                    </a>
                </div>
                <?php endif; ?>
            </div>
            </div>

            <!-- Bottom Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Skills -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold flex items-center gap-2">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        Skills
                    </h2>
                    <?php if (empty($skills)): ?>
                    <a href="/candidate/profile/complete" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Skills
                    </a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($skills)): ?>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($skills as $skill): ?>
                    <div class="bg-white border border-gray-200 rounded-full px-4 py-2 flex items-center gap-2 shadow-sm hover:shadow-md transition-all hover:border-indigo-300 group">
                        <svg class="w-4 h-4 text-indigo-500 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        <span class="font-semibold text-gray-800"><?= htmlspecialchars($skill['name'] ?? $skill['skill_name'] ?? '') ?></span>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">
                            <?= ucfirst($skill['proficiency_level'] ?? 'intermediate') ?>
                        </span>
                        <?php if (!empty($skill['years_of_experience'])): ?>
                        <span class="text-xs text-gray-400">• <?= $skill['years_of_experience'] ?> yrs</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    <p class="text-gray-500 mb-3">No skills added yet</p>
                    <a href="/candidate/profile/complete" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm">
                        Add Skills
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <!-- Languages -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold flex items-center gap-2">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                        Languages
                    </h2>
                    <?php if (empty($languages)): ?>
                    <a href="/candidate/profile/complete" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Languages
                    </a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($languages)): ?>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($languages as $lang): ?>
                    <span class="bg-white border border-gray-200 rounded-full px-4 py-2 flex items-center gap-2 shadow-sm hover:shadow-md transition-all hover:border-indigo-300 group">
                        <svg class="w-4 h-4 text-emerald-500 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                        <span class="font-semibold text-gray-800"><?= htmlspecialchars($lang['language'] ?? '') ?></span>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full"><?= ucfirst($lang['proficiency'] ?? 'conversational') ?></span>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                    </svg>
                    <p class="text-gray-500 mb-3">No languages added yet</p>
                    <a href="/candidate/profile/complete" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm">
                        Add Languages
                    </a>
                </div>
                <?php endif; ?>
            </div>
            </div>

            <!-- Additional Information -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
                <h2 class="text-2xl font-bold mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Additional Information
                </h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <label class="text-sm text-gray-600">Expected Salary</label>
                        </div>
                        <p class="font-semibold pl-10">
                            <?php if (!empty($candidate->attributes['expected_salary_min']) || !empty($candidate->attributes['expected_salary_max'])): ?>
                            ₹<?= number_format($candidate->attributes['expected_salary_min'] ?? 0) ?> - 
                            ₹<?= number_format($candidate->attributes['expected_salary_max'] ?? 0) ?>
                            <?php else: ?>
                            Not specified
                            <?php endif; ?>
                        </p>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <label class="text-sm text-gray-600">Current Salary</label>
                        </div>
                        <p class="font-semibold pl-10">
                            <?= !empty($candidate->attributes['current_salary']) ? '₹' . number_format($candidate->attributes['current_salary']) : 'Not specified' ?>
                        </p>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <label class="text-sm text-gray-600">Notice Period</label>
                        </div>
                        <p class="font-semibold pl-10">
                            <?= !empty($candidate->attributes['notice_period']) ? $candidate->attributes['notice_period'] . ' days' : 'Not specified' ?>
                        </p>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <label class="text-sm text-gray-600">Preferred Location</label>
                        </div>
                        <p class="font-semibold pl-10">
                            <?= htmlspecialchars($candidate->attributes['preferred_job_location'] ?? 'Not specified') ?>
                        </p>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="mt-6 pt-6 border-t">
                    <h3 class="font-semibold mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        Social Links
                    </h3>
                    <div class="flex flex-wrap gap-3">
                        <?php if (!empty($candidate->attributes['linkedin_url'])): ?>
                        <a href="<?= htmlspecialchars($candidate->attributes['linkedin_url']) ?>" 
                           target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition transform hover:-translate-y-0.5 duration-200 font-medium">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                            LinkedIn
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($candidate->attributes['github_url'])): ?>
                        <a href="<?= htmlspecialchars($candidate->attributes['github_url']) ?>" 
                           target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition transform hover:-translate-y-0.5 duration-200 font-medium">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/>
                            </svg>
                            GitHub
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($candidate->attributes['portfolio_url'])): ?>
                        <a href="<?= htmlspecialchars($candidate->attributes['portfolio_url']) ?>" 
                           target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition transform hover:-translate-y-0.5 duration-200 font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                            </svg>
                            Portfolio
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($candidate->attributes['website_url'])): ?>
                        <a href="<?= htmlspecialchars($candidate->attributes['website_url']) ?>" 
                           target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition transform hover:-translate-y-0.5 duration-200 font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                            </svg>
                            Website
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                        <?php endif; ?>
                        <?php if (empty($candidate->attributes['linkedin_url']) && empty($candidate->attributes['github_url']) && empty($candidate->attributes['portfolio_url']) && empty($candidate->attributes['website_url'])): ?>
                        <p class="text-gray-500 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            No social links added
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
</div>
<script>
async function deleteCandidateVideo(btn) {
    if (!confirm('Delete your introduction video? This cannot be undone.')) return;
    try {
        const res = await fetch('/candidate/profile/delete-video', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        const out = await res.json();
        if (out && out.success) {
            location.reload();
        } else {
            alert(out.error || 'Failed to delete video');
        }
    } catch (e) {
        alert('Network error while deleting video');
    }
}
</script>
<?php include __DIR__ . '/../../include/footer.php'; ?>
</body>
</html>
