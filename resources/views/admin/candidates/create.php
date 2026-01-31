<div class="max-w-4xl mx-auto py-8 sm:px-6 lg:px-8">
    <div class="mb-8 flex items-center justify-between animate-fade-in-down">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Add Candidate</h1>
            <p class="mt-2 text-sm text-gray-600">Manually add a new candidate to the system.</p>
        </div>
        <a href="/admin/candidates" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all transform hover:scale-105">
            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to List
        </a>
    </div>

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden transition-all duration-300 hover:shadow-2xl animate-fade-in-up">
        <form id="addCandidateForm" x-data="{
            isSubmitting: false,
            formData: {
                name: '',
                email: '',
                phone: '',
                location: '',
                category: '',
                skills: '',
                source: 'admin_manual',
                status: 'pending',
                send_email: true
            },
            resumeFile: null,
            successMessage: '',
            errorMessage: ''
        }" @submit.prevent="
            isSubmitting = true;
            successMessage = '';
            errorMessage = '';
            fetch('/admin/candidates/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify(formData)
            })
            .then(r => r.json())
            .then(async (data) => {
                if (data.error) {
                    errorMessage = data.error;
                    isSubmitting = false;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
                if (resumeFile && data.candidate_id) {
                    const fd = new FormData();
                    fd.append('resume', resumeFile);
                    await fetch('/admin/candidates/upload-resume?id=' + encodeURIComponent(data.candidate_id), {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: fd
                    }).then(res => res.json()).catch(() => {});
                }
                successMessage = data.message;
                window.scrollTo({ top: 0, behavior: 'smooth' });
                setTimeout(() => {
                    window.location.href = '/admin/candidates';
                }, 1500);
            })
            .catch(() => { errorMessage = 'An unexpected error occurred. Please try again.'; window.scrollTo({ top: 0, behavior: 'smooth' }); })
            .finally(() => { isSubmitting = false; })
        ">
            <!-- Notifications -->
            <div x-show="successMessage" x-transition class="p-4 bg-green-50 border-l-4 border-green-500 text-green-700 mb-4 mx-6 mt-6 rounded shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium" x-text="successMessage"></p>
                    </div>
                </div>
            </div>
            <div x-show="errorMessage" x-transition class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 mb-4 mx-6 mt-6 rounded shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium" x-text="errorMessage"></p>
                    </div>
                </div>
            </div>

            <!-- Form Content -->
            <div class="px-8 py-8 space-y-8">
                <!-- Basic Info -->
                <div class="transform transition-all duration-300 hover:bg-gray-50 p-4 rounded-xl -mx-4">
                    <h4 class="text-xl font-semibold text-gray-800 border-b border-gray-200 pb-3 mb-5 flex items-center">
                        <span class="bg-blue-100 text-blue-600 rounded-full p-2 mr-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </span>
                        Basic Information
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Name -->
                        <div class="group">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1 group-hover:text-blue-600 transition-colors">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" x-model="formData.name" id="name" required class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out hover:border-gray-400" placeholder="e.g. John Doe">
                        </div>
                        <!-- Email -->
                        <div class="group">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1 group-hover:text-blue-600 transition-colors">Email <span class="text-red-500">*</span></label>
                            <input type="email" x-model="formData.email" id="email" required class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out hover:border-gray-400" placeholder="john@example.com">
                            <p class="mt-2 text-xs text-gray-500 flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                The candidate will receive an email to verify and set password.
                            </p>
                        </div>
                        <!-- Phone -->
                        <div class="group">
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1 group-hover:text-blue-600 transition-colors">Phone</label>
                            <input type="tel" x-model="formData.phone" id="phone" class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out hover:border-gray-400" placeholder="+1 234 567 8900">
                        </div>
                        <!-- Location -->
                        <div class="group">
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-1 group-hover:text-blue-600 transition-colors">Location</label>
                            <input type="text" x-model="formData.location" id="location" class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out hover:border-gray-400" placeholder="City, Country">
                        </div>
                    </div>
                </div>

                <!-- Professional Details -->
                <div class="transform transition-all duration-300 hover:bg-gray-50 p-4 rounded-xl -mx-4">
                    <h4 class="text-xl font-semibold text-gray-800 border-b border-gray-200 pb-3 mb-5 flex items-center">
                        <span class="bg-indigo-100 text-indigo-600 rounded-full p-2 mr-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </span>
                        Professional Details
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Category -->
                         <div class="group">
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1 group-hover:text-indigo-600 transition-colors">Category</label>
                            <div class="relative">
                                <select x-model="formData.category" id="category" class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm appearance-none bg-white transition-all duration-200 ease-in-out hover:border-gray-400">
                                    <option value="">Select Category</option>
                                    <?php foreach (($categories ?? []) as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                        <!-- Skills -->
                        <div class="group">
                            <label for="skills" class="block text-sm font-medium text-gray-700 mb-1 group-hover:text-indigo-600 transition-colors">Skills (comma separated)</label>
                            <input type="text" x-model="formData.skills" id="skills" placeholder="PHP, Laravel, MySQL" class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all duration-200 ease-in-out hover:border-gray-400">
                        </div>
                        <!-- CV -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Upload CV (PDF/DOC/DOCX)</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-indigo-500 transition-colors duration-300 group cursor-pointer relative bg-gray-50 hover:bg-indigo-50/30">
                                <input type="file" @change="resumeFile = $event.target.files[0]" accept=".pdf,.doc,.docx" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-indigo-500 transition-colors duration-300" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <span class="relative cursor-pointer bg-transparent rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Upload a file</span>
                                        </span>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500" x-text="resumeFile ? resumeFile.name : 'PDF, DOC, DOCX up to 10MB'"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Settings -->
                <div class="transform transition-all duration-300 hover:bg-gray-50 p-4 rounded-xl -mx-4">
                    <h4 class="text-xl font-semibold text-gray-800 border-b border-gray-200 pb-3 mb-5 flex items-center">
                        <span class="bg-purple-100 text-purple-600 rounded-full p-2 mr-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </span>
                        System Settings
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Source -->
                        <div class="group">
                            <label for="source" class="block text-sm font-medium text-gray-700 mb-1 group-hover:text-purple-600 transition-colors">Source</label>
                            <div class="relative">
                                <select x-model="formData.source" id="source" class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm appearance-none bg-white transition-all duration-200 ease-in-out hover:border-gray-400">
                                    <option value="admin_manual">Admin Manual</option>
                                    <option value="walk_in">Walk-in</option>
                                    <option value="referral">Referral</option>
                                    <option value="social_media">Social Media</option>
                                    <option value="job_fair">Job Fair</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                        <!-- Status -->
                        <div class="group">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1 group-hover:text-purple-600 transition-colors">Status</label>
                            <div class="relative">
                                <select x-model="formData.status" id="status" class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 sm:text-sm appearance-none bg-white transition-all duration-200 ease-in-out hover:border-gray-400">
                                    <option value="pending">Not Verified</option>
                                    <option value="active">Active</option>
                                    <option value="blocked">Blocked</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                        <!-- Verification -->
                        <div class="md:col-span-2">
                            <div class="rounded-xl bg-blue-50 border border-blue-200 p-6 flex flex-col sm:flex-row items-center justify-between transition-all duration-300 hover:shadow-md hover:bg-blue-50/80">
                                <div class="mb-4 sm:mb-0">
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">Candidate Status: Not Verified</span>
                                    </div>
                                    <p class="mt-2 text-sm text-blue-700 flex items-center">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Verification email will be sent automatically.
                                    </p>
                                </div>
                                <label class="flex items-center space-x-3 cursor-pointer group">
                                    <div class="relative">
                                        <input type="checkbox" x-model="formData.send_email" class="sr-only peer">
                                        <div class="w-10 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700 transition-colors">Send verification email now</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3">
                <a href="/admin/candidates" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="button" @click="formData.send_email = false; document.getElementById('addCandidateForm').dispatchEvent(new Event('submit'))" :disabled="isSubmitting" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save as Draft
                </button>
                <button type="submit" @click="formData.send_email = true" :disabled="isSubmitting" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span x-show="!isSubmitting">Send Invite & Create</span>
                    <span x-show="isSubmitting">Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>
