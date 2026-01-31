<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            <li>
                <a href="/admin/dashboard" class="text-gray-400 hover:text-gray-500 transition-colors">
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
                    <a href="/admin/candidates" class="ml-2 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">Candidates</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span class="ml-2 text-sm font-medium text-gray-900" aria-current="page">Profile Details</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Profile Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="h-32 bg-gradient-to-r from-blue-600 to-indigo-600"></div>
        <div class="px-6 pb-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-end -mt-12 mb-6">
                <div class="relative h-28 w-28 rounded-xl ring-4 ring-white bg-white shadow-md overflow-hidden">
                    <?php if (!empty($candidate['profile_picture'])): ?>
                        <img class="h-full w-full object-cover" src="<?= htmlspecialchars($candidate['profile_picture']) ?>" alt="">
                    <?php else: ?>
                        <div class="h-full w-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 text-3xl font-bold text-gray-500">
                            <?= strtoupper(substr($candidate['full_name'] ?? 'U', 0, 2)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mt-4 sm:mt-0 sm:ml-6 text-center sm:text-left flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">
                                <?= htmlspecialchars($candidate['full_name'] ?? 'Unknown Candidate') ?>
                            </h1>
                            <div class="mt-1 flex items-center justify-center sm:justify-start text-sm text-gray-500 space-x-4">
                                <?php if(!empty($candidate['city']) || !empty($candidate['country'])): ?>
                                    <span class="flex items-center">
                                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <?= htmlspecialchars($candidate['city'] ?? '') ?><?= !empty($candidate['city']) && !empty($candidate['country']) ? ', ' : '' ?><?= htmlspecialchars($candidate['country'] ?? '') ?>
                                    </span>
                                <?php endif; ?>
                                <span class="flex items-center">
                                    <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Joined <?= date('M Y', strtotime($candidate['created_at'] ?? 'now')) ?>
                                </span>
                            </div>
                        </div>
                        <div class="mt-4 sm:mt-0 flex space-x-3" x-data="{ showUploadModal: false }">
                            <?php if (!empty($candidate['resume_url'])): ?>
                                <a href="<?= htmlspecialchars($candidate['resume_url']) ?>" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.586l5.414 5.414a1 1 0 01.586 1.414V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Resume
                                </a>
                            <?php endif; ?>
                            
                            <button @click="showUploadModal = true" type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                <?= !empty($candidate['resume_url']) ? 'Update Resume' : 'Upload Resume' ?>
                            </button>

                            <!-- Upload Modal -->
                            <div x-show="showUploadModal" style="display: none;" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div x-show="showUploadModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                    <div x-show="showUploadModal" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                        <form id="uploadResumeForm" enctype="multipart/form-data">
                                            <input type="hidden" name="id" value="<?= $candidate['id'] ?>">
                                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                <div class="sm:flex sm:items-start">
                                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                        </svg>
                                                    </div>
                                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                            Upload Resume
                                                        </h3>
                                                        <div class="mt-2">
                                                            <p class="text-sm text-gray-500">
                                                                Upload a new resume for <?= htmlspecialchars($candidate['full_name'] ?? 'this candidate') ?>. Supported formats: PDF, DOC, DOCX. Max size: 5MB.
                                                            </p>
                                                            <div class="mt-4">
                                                                <input type="file" name="resume" accept=".pdf,.doc,.docx" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                <button type="button" onclick="uploadResume()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                    Upload
                                                </button>
                                                <button @click="showUploadModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <script>
                            function uploadResume() {
                                const form = document.getElementById('uploadResumeForm');
                                const formData = new FormData(form);
                                
                                fetch('/admin/candidates/upload-resume', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        window.location.reload();
                                    } else {
                                        alert(data.error || 'Upload failed');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('An error occurred during upload');
                                });
                            }
                            </script>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Badges -->
            <div class="flex flex-wrap gap-3 sm:ml-34 mt-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border <?= ($candidate['user_status'] ?? '') === 'active' ? 'bg-green-50 text-green-700 border-green-100' : 'bg-red-50 text-red-700 border-red-100' ?>">
                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 <?= ($candidate['user_status'] ?? '') === 'active' ? 'bg-green-500' : 'bg-red-500' ?>"></span>
                    User: <?= ucfirst($candidate['user_status'] ?? 'unknown') ?>
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border <?= ($candidate['profile_status'] ?? '') === 'active' ? 'bg-blue-50 text-blue-700 border-blue-100' : 'bg-gray-50 text-gray-700 border-gray-200' ?>">
                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 <?= ($candidate['profile_status'] ?? '') === 'active' ? 'bg-blue-500' : 'bg-gray-500' ?>"></span>
                    Profile: <?= ucfirst($candidate['profile_status'] ?? 'unknown') ?>
                </span>
                <?php $isPremium = ((int)($candidate['is_premium'] ?? 0) === 1) && !empty($candidate['premium_expires_at']) && strtotime($candidate['premium_expires_at']) > time(); ?>
                <?php if($isPremium): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-100">
                        <svg class="w-3 h-3 mr-1.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 9a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zm7-11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0V6h-1a1 1 0 110-2h1V3a1 1 0 011-1zm0 9a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Premium Member
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Profile Info Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Personal Information
                    </h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Email Address</dt>
                        <dd class="text-sm font-medium text-gray-900 break-all"><?= htmlspecialchars($candidate['email'] ?? 'N/A') ?></dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</dt>
                        <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($candidate['phone'] ?? 'N/A') ?></dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Source</dt>
                        <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($candidate['source'] ?? 'N/A') ?></dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</dt>
                        <dd class="text-sm font-medium text-gray-900"><?= htmlspecialchars($candidate['created_by'] ?? 'N/A') ?></dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Visibility</dt>
                        <dd class="text-sm font-medium text-gray-900 flex items-center">
                            <span class="w-2 h-2 rounded-full mr-2 <?= ($candidate['visibility'] ?? '') === 'public' ? 'bg-green-500' : 'bg-gray-400' ?>"></span>
                            <?= ucfirst($candidate['visibility'] ?? 'N/A') ?>
                        </dd>
                    </div>
                    <div class="space-y-1">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</dt>
                        <dd class="text-sm font-medium text-gray-900"><?= $candidate['last_login'] ? date('M d, Y H:i', strtotime($candidate['last_login'])) : 'Never' ?></dd>
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Email Verification</dt>
                        <dd class="mt-1">
                            <?php if (!empty($candidate['is_email_verified'])): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    Verified
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                    Unverified
                                </span>
                                <?php if (!empty($candidate['verification_expires_at']) && strtotime($candidate['verification_expires_at']) > time()): ?>
                                    <p class="text-xs text-gray-500 mt-1">Verification link expires: <?= date('M d, Y H:i', strtotime($candidate['verification_expires_at'])) ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </dd>
                    </div>
                </div>
            </div>

            <!-- Skills -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Skills
                </h2>
                <?php if (!empty($skills)): ?>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($skills as $skill): ?>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                            <?= htmlspecialchars($skill['name'] ?? '') ?>
                            <?php if(!empty($skill['proficiency_level'])): ?>
                                <span class="ml-2 pl-2 border-l border-indigo-200 text-xs text-indigo-600">
                                    <?= htmlspecialchars($skill['proficiency_level']) ?>
                                </span>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p class="text-sm text-gray-500 italic">No skills added yet.</p>
                <?php endif; ?>
            </div>

            <!-- Applications -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.586l5.414 5.414a1 1 0 01.586 1.414V19a2 2 0 01-2 2z" />
                        </svg>
                        Applications
                        <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2.5 rounded-full text-xs"><?= count($applications) ?></span>
                    </h2>
                </div>
                <div class="space-y-4">
                    <?php foreach ($applications as $app): ?>
                    <div class="flex items-start justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div>
                            <a href="/admin/jobs/<?= $app['job_id'] ?>" class="text-base font-semibold text-gray-900 hover:text-blue-600 transition-colors">
                                <?= htmlspecialchars($app['job_title'] ?? 'N/A') ?>
                            </a>
                            <p class="text-sm text-gray-500 mt-1">Applied on <?= date('M d, Y', strtotime($app['created_at'] ?? 'now')) ?></p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white border border-gray-200 text-gray-800 shadow-sm">
                            <?= ucfirst($app['status'] ?? 'pending') ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                    <?php if(empty($applications)): ?>
                        <p class="text-sm text-gray-500 italic">No applications found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Saved Jobs -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    Saved Jobs
                    <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2.5 rounded-full text-xs"><?= count($savedJobs) ?></span>
                </h2>
                <?php if (!empty($savedJobs)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($savedJobs as $job): ?>
                        <div class="p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors">
                            <a href="<?= !empty($job['slug']) ? '/admin/jobs/' . htmlspecialchars($job['slug']) : '#' ?>" class="block font-medium text-gray-900 hover:text-blue-600 truncate">
                                <?= htmlspecialchars($job['title'] ?? 'N/A') ?>
                            </a>
                            <p class="text-sm text-gray-500 mt-1 truncate"><?= htmlspecialchars($job['company_name'] ?? '') ?></p>
                            <div class="mt-2 flex items-center text-xs text-gray-400">
                                <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Saved <?= !empty($job['saved_at']) ? date('M d, Y', strtotime($job['saved_at'])) : '—' ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p class="text-sm text-gray-500 italic">No saved jobs.</p>
                <?php endif; ?>
            </div>

            <!-- Education -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                    </svg>
                    Education
                </h2>
                <?php if (!empty($education)): ?>
                <div class="space-y-6">
                    <?php foreach ($education as $edu): ?>
                        <div class="relative pl-6 border-l-2 border-gray-200 pb-2 last:pb-0">
                            <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-white border-2 border-purple-500"></div>
                            <h3 class="text-base font-semibold text-gray-900"><?= htmlspecialchars($edu['degree'] ?? '') ?></h3>
                            <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($edu['field_of_study'] ?? '') ?></p>
                            <p class="text-sm text-gray-600"><?= htmlspecialchars($edu['institution'] ?? '') ?></p>
                            <p class="text-xs text-gray-500 mt-1">
                                <?= !empty($edu['start_date']) ? date('M Y', strtotime($edu['start_date'])) : '' ?> 
                                <?= !empty($edu['end_date']) ? ' - ' . date('M Y', strtotime($edu['end_date'])) : '' ?>
                                <?= (int)($edu['is_current'] ?? 0) === 1 ? ' (Current)' : '' ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p class="text-sm text-gray-500 italic">No education records.</p>
                <?php endif; ?>
            </div>

            <!-- Experience -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Experience
                </h2>
                <?php if (!empty($experience)): ?>
                <div class="space-y-6">
                    <?php foreach ($experience as $exp): ?>
                        <div class="relative pl-6 border-l-2 border-gray-200 pb-2 last:pb-0">
                            <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-white border-2 border-orange-500"></div>
                            <h3 class="text-base font-semibold text-gray-900"><?= htmlspecialchars($exp['job_title'] ?? '') ?></h3>
                            <div class="flex items-center text-sm font-medium text-gray-700">
                                <?= htmlspecialchars($exp['company_name'] ?? '') ?>
                                <?php if(!empty($exp['location'])): ?>
                                    <span class="mx-1.5 text-gray-300">•</span>
                                    <span class="text-gray-500"><?= htmlspecialchars($exp['location']) ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 mb-2">
                                <?= !empty($exp['start_date']) ? date('M Y', strtotime($exp['start_date'])) : '' ?> 
                                <?= !empty($exp['end_date']) ? ' - ' . date('M Y', strtotime($exp['end_date'])) : '' ?>
                                <?= (int)($exp['is_current'] ?? 0) === 1 ? ' (Current)' : '' ?>
                            </p>
                            <?php if (!empty($exp['description'])): ?>
                                <div class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg"><?= nl2br(htmlspecialchars($exp['description'])) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p class="text-sm text-gray-500 italic">No experience records.</p>
                <?php endif; ?>
            </div>

            <!-- Resume & Media -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Media
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="text-sm font-medium text-gray-700 mb-2">Resume</div>
                        <?php if (!empty($candidate['resume_url'])): ?>
                            <a href="<?= htmlspecialchars($candidate['resume_url']) ?>" target="_blank" class="flex items-center justify-center gap-2 px-4 py-2 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.586l5.414 5.414a1 1 0 01.586 1.414V19a2 2 0 01-2 2z" />
                                </svg>
                                View Resume
                            </a>
                        <?php else: ?>
                            <div class="text-sm text-gray-500 italic text-center py-2">No resume uploaded</div>
                        <?php endif; ?>
                    </div>
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="text-sm font-medium text-gray-700 mb-2">Intro Video</div>
                        <?php if (!empty($candidate['video_intro_url'])): ?>
                            <a href="<?= htmlspecialchars($candidate['video_intro_url']) ?>" target="_blank" class="flex items-center justify-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                View Video
                            </a>
                        <?php else: ?>
                            <div class="text-sm text-gray-500 italic text-center py-2">No video uploaded</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quality Scores -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Quality Scores
                </h2>
                <?php if (!empty($qualityScores)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overall</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resume</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skill Match</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interview</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($qualityScores as $qs): ?>
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-blue-600"><?= (int)($qs['overall_score'] ?? 0) ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?= (int)($qs['resume_completeness_score'] ?? 0) ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?= (int)($qs['skill_match_percentage'] ?? 0) ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?= (int)($qs['interview_score'] ?? 0) ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"><?= !empty($qs['updated_at']) ? date('M d, Y', strtotime($qs['updated_at'])) : '—' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-sm text-gray-500 italic">No quality scores recorded.</p>
                <?php endif; ?>
            </div>
            
            <!-- Location Based Jobs -->
            <?php if (!empty($nearbyJobs ?? [])): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Location-Based Jobs
                </h2>
                <div class="space-y-3">
                    <?php foreach ($nearbyJobs as $job): ?>
                        <div class="flex items-center justify-between p-3 rounded-lg border border-gray-100 hover:bg-gray-50 transition-colors">
                            <div>
                                <a href="<?= !empty($job['slug']) ? '/admin/jobs/' . htmlspecialchars($job['slug']) : '#' ?>" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                    <?= htmlspecialchars($job['title'] ?? 'N/A') ?>
                                </a>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($job['company_name'] ?? '') ?></p>
                            </div>
                            <span class="text-xs text-gray-400"><?= !empty($job['created_at']) ? date('M d', strtotime($job['created_at'])) : '—' ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Actions Sidebar -->
        <div class="space-y-6">
            <!-- Account Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Actions</h3>
                <div class="space-y-3">
                    <?php if (($candidate['user_status'] ?? '') === 'active'): ?>
                        <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/block">
                            <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                Block Candidate
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/unblock">
                            <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Unblock Candidate
                            </button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/delete" onsubmit="return confirm('Are you sure? This cannot be undone.');">
                        <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                            <svg class="-ml-1 mr-2 h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete Account
                        </button>
                    </form>
                </div>
            </div>

            <!-- Premium Status -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Premium Membership</h3>
                <div class="space-y-4">
                    <?php $prem = ((int)($candidate['is_premium'] ?? 0) === 1) && !empty($candidate['premium_expires_at']) && strtotime($candidate['premium_expires_at']) > time(); ?>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-600">Status</span>
                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full <?= $prem ? 'bg-amber-100 text-amber-800' : 'bg-gray-200 text-gray-800' ?>">
                                <?= $prem ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                        <?php if($prem): ?>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Expires</span>
                            <span class="text-sm font-medium text-gray-900"><?= !empty($candidate['premium_expires_at']) ? date('M d, Y', strtotime($candidate['premium_expires_at'])) : '—' ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/premium/enable" class="col-span-1">
                            <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <input type="hidden" name="days" value="30">
                            <button type="submit" class="w-full px-3 py-2 text-xs font-medium bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
                                Enable 30d
                            </button>
                        </form>
                        <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/premium/disable" onsubmit="return confirm('Disable premium?');" class="col-span-1">
                            <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="w-full px-3 py-2 text-xs font-medium bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                                Disable
                            </button>
                        </form>
                        
                        <div class="col-span-2 pt-2 border-t border-gray-100 mt-2">
                            <p class="text-xs text-gray-500 mb-2 font-medium uppercase">Adjust Duration</p>
                            <div class="flex flex-col space-y-2">
                                <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/premium/extend" class="flex items-center gap-2">
                                    <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <input type="number" min="1" name="days" value="7" class="w-16 px-2 py-1 border rounded text-xs">
                                    <button type="submit" class="flex-1 px-3 py-1.5 text-xs bg-green-600 text-white rounded hover:bg-green-700">Extend Days</button>
                                </form>
                                <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/premium/reduce" class="flex items-center gap-2">
                                    <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                    <input type="number" min="1" name="days" value="7" class="w-16 px-2 py-1 border rounded text-xs">
                                    <button type="submit" class="flex-1 px-3 py-1.5 text-xs bg-red-600 text-white rounded hover:bg-red-700">Reduce Days</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Suggest to Employer -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Suggest to Employer</h3>
                <form method="POST" action="/admin/candidates/<?= $candidate['id'] ?>/suggest" class="space-y-3">
                    <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Employer ID</label>
                        <input type="number" name="employer_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. 123" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Job ID (Optional)</label>
                        <input type="number" name="job_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. 456">
                    </div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Suggest Candidate
                    </button>
                </form>
            </div>
            
            <!-- Login History -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Recent Logins</h3>
                <?php if (!empty($loginHistory)): ?>
                <ul class="space-y-3">
                    <?php foreach ($loginHistory as $lh): ?>
                    <li class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="flex-1"><?= !empty($lh['logged_in_at']) ? date('M d, H:i', strtotime($lh['logged_in_at'])) : '—' ?></span>
                        <?php if(!empty($lh['ip_address'])): ?>
                            <span class="text-xs bg-gray-100 px-2 py-0.5 rounded text-gray-500 font-mono"><?= htmlspecialchars($lh['ip_address']) ?></span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                    <p class="text-sm text-gray-500 italic">No login history recorded.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
