<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title>Complete Your Profile - Mindware Infotech</title>
    <link href="/css/output.css" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border: none;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
            box-shadow: 0 6px 12px rgba(79, 70, 229, 0.3);
            transform: translateY(-2px);
        }
        .text-gradient {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .step-enter {
            animation: slideIn 0.3s ease-out forwards;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('profileForm', () => ({
                currentStep: 1,
                profileStrength: <?= $candidate->attributes['profile_strength'] ?? 0 ?>,
                allSkills: <?= json_encode($allSkills ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                newLanguage: { language: '', proficiency: 'conversational' },
                isPremium: <?= $candidate->isPremium() ? 'true' : 'false' ?>,
                recordingSupported: !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia),
                isRecording: false,
                mediaStream: null,
                mediaRecorder: null,
                recordedChunks: [],
                recordedBlob: null,
                formData: {
                    basic: {
                        full_name: <?php 
                            $userName = $candidate->attributes['full_name'] ?? '';
                            if (empty($userName) && isset($user)) {
                                if (is_array($user)) {
                                    $userName = $user['google_name'] ?? $user['apple_name'] ?? $user['full_name'] ?? '';
                                } elseif (is_object($user) && isset($user->attributes)) {
                                    $userName = $user->attributes['google_name'] ?? $user->attributes['apple_name'] ?? $user->attributes['full_name'] ?? '';
                                }
                            }
                            echo json_encode($userName, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                        ?>,
                        dob: <?= json_encode($candidate->attributes['dob'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        gender: <?= json_encode($candidate->attributes['gender'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        mobile: <?php 
                            $userPhone = $candidate->attributes['mobile'] ?? '';
                            if (empty($userPhone) && isset($user)) {
                                if (is_array($user)) {
                                    $userPhone = $user['phone'] ?? '';
                                } elseif (is_object($user) && isset($user->attributes)) {
                                    $userPhone = $user->attributes['phone'] ?? '';
                                }
                            }
                            echo json_encode($userPhone, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                        ?>,
                        city: <?= json_encode($candidate->attributes['city'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        state: <?= json_encode($candidate->attributes['state'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        country: <?= json_encode($candidate->attributes['country'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        profile_picture: <?= json_encode($candidate->attributes['profile_picture'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        resume_url: <?= json_encode($candidate->attributes['resume_url'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        video_intro_url: <?= json_encode($candidate->attributes['video_intro_url'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        video_type: <?= json_encode($candidate->attributes['video_intro_type'] ?? 'upload', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        video_url: <?= json_encode($candidate->attributes['video_intro_url'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        self_introduction: <?= json_encode($candidate->attributes['self_introduction'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
                    },
                    education: <?= json_encode($existingEducation ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                    experience: <?= json_encode($existingExperience ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                    skills: <?= json_encode($existingSkills ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                    languages: <?= json_encode($existingLanguages ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                    additional: {
                        expected_salary_min: <?= json_encode($candidate->attributes['expected_salary_min'] ?? null) ?>,
                        expected_salary_max: <?= json_encode($candidate->attributes['expected_salary_max'] ?? null) ?>,
                        current_salary: <?= json_encode($candidate->attributes['current_salary'] ?? null) ?>,
                        notice_period: <?= json_encode($candidate->attributes['notice_period'] ?? null) ?>,
                        preferred_job_location: <?= json_encode($candidate->attributes['preferred_job_location'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        portfolio_url: <?= json_encode($candidate->attributes['portfolio_url'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        linkedin_url: <?= json_encode($candidate->attributes['linkedin_url'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        github_url: <?= json_encode($candidate->attributes['github_url'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
                        website_url: <?= json_encode($candidate->attributes['website_url'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
                    }
                },
                addEducation() {
                    this.formData.education.push({ degree: '', field: '', institution: '', year: '', percentage: '' });
                },
                removeEducation(index) {
                    this.formData.education.splice(index, 1);
                },
                addExperience() {
                    this.formData.experience.push({ job_title: '', company: '', start_date: '', end_date: '', description: '', is_current: false });
                },
                removeExperience(index) {
                    this.formData.experience.splice(index, 1);
                },
                addSkill() {
                    this.formData.skills.push({ name: '', level: 'beginner' });
                },
                removeSkill(index) {
                    this.formData.skills.splice(index, 1);
                },
                addLanguage() {
                    this.formData.languages.push({ language: '', proficiency: 'conversational' });
                },
                removeLanguage(index) {
                    this.formData.languages.splice(index, 1);
                },
                async saveSection(section) {
                    let payload = { section };
                    if (section === 'basic') {
                        const b = this.formData.basic;
                        const videoUrl = b.video_type === 'youtube' ? (b.video_url || '') : (b.video_intro_url || '');
                        payload = {
                            section: 'basic',
                            full_name: b.full_name || '',
                            dob: b.dob || '',
                            gender: b.gender || '',
                            mobile: b.mobile || '',
                            city: b.city || '',
                            state: b.state || '',
                            country: b.country || '',
                            self_introduction: b.self_introduction || '',
                            profile_picture: b.profile_picture || '',
                            resume_url: b.resume_url || '',
                            video_intro_type: b.video_type || '',
                            video_intro_url: videoUrl || ''
                        };
                    } else if (section === 'education') {
                        payload = {
                            section: 'education',
                            education: (this.formData.education || []).map(e => ({
                                degree: e.degree || '',
                                field_of_study: e.field || '',
                                institution: e.institution || '',
                                start_date: e.start_date || null,
                                end_date: e.end_date || null,
                                is_current: e.is_current ? 1 : 0,
                                grade: e.percentage || null,
                                description: e.description || null
                            }))
                        };
                    } else if (section === 'experience') {
                        payload = {
                            section: 'experience',
                            experience: (this.formData.experience || []).map(x => ({
                                job_title: x.job_title || '',
                                company_name: x.company || '',
                                start_date: x.start_date || null,
                                end_date: x.end_date || null,
                                is_current: x.is_current ? 1 : 0,
                                description: x.description || null,
                                location: x.location || null
                            }))
                        };
                    } else if (section === 'skills') {
                        payload = {
                            section: 'skills',
                            skills: (this.formData.skills || []).map(s => ({
                                name: s.name || '',
                                proficiency_level: s.level || 'beginner',
                                years_of_experience: s.years_of_experience || null
                            }))
                        };
                    } else if (section === 'languages') {
                        payload = {
                            section: 'languages',
                            languages: (this.formData.languages || []).map(l => ({
                                language: l.language || '',
                                proficiency: l.proficiency || 'conversational'
                            }))
                        };
                    } else if (section === 'additional') {
                        const a = this.formData.additional;
                        payload = {
                            section: 'additional',
                            expected_salary_min: a.expected_salary_min ?? null,
                            expected_salary_max: a.expected_salary_max ?? null,
                            current_salary: a.current_salary ?? null,
                            notice_period: a.notice_period ?? null,
                            preferred_job_location: a.preferred_job_location || '',
                            portfolio_url: a.portfolio_url || '',
                            linkedin_url: a.linkedin_url || '',
                            github_url: a.github_url || '',
                            website_url: a.website_url || ''
                        };
                    }
                    try {
                        const res = await fetch('/candidate/profile/save', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(payload)
                        });
                        const result = await res.json();
                        if (result && result.success) {
                            this.profileStrength = result.profile_strength ?? this.profileStrength;
                        }
                    } catch (e) {}
                },
                async uploadFile(type, file) {
                    const fd = new FormData();
                    fd.append('type', type);
                    fd.append('file', file);
                    const res = await fetch('/candidate/profile/upload', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: fd
                    });
                    const out = await res.json();
                    if (out && out.url) {
                        if (type === 'profile_picture') {
                            this.formData.basic.profile_picture = out.url;
                        } else if (type === 'resume') {
                            this.formData.basic.resume_url = out.url;
                        } else if (type === 'video') {
                            this.formData.basic.video_intro_url = out.url;
                            this.formData.basic.video_type = 'upload';
                        }
                    }
                },
                async startRecording() {
                    if (!this.isPremium) { return; }
                    if (!this.recordingSupported || this.isRecording) { return; }
                    try {
                        this.recordedChunks = [];
                        this.recordedBlob = null;
                        this.mediaStream = await navigator.mediaDevices.getUserMedia({
                            video: { width: 640, height: 360, frameRate: 24 },
                            audio: true
                        });
                        this.$refs.recPreview.srcObject = this.mediaStream;
                        let mime = 'video/webm;codecs=vp9';
                        if (!MediaRecorder.isTypeSupported(mime)) {
                            mime = 'video/webm;codecs=vp8';
                        }
                        if (!MediaRecorder.isTypeSupported(mime)) {
                            mime = 'video/webm';
                        }
                        this.mediaRecorder = new MediaRecorder(this.mediaStream, { mimeType: mime, bitsPerSecond: 800000 });
                        this.mediaRecorder.ondataavailable = e => {
                            if (e.data && e.data.size > 0) this.recordedChunks.push(e.data);
                        };
                        this.mediaRecorder.onstop = () => {
                            this.recordedBlob = new Blob(this.recordedChunks, { type: mime });
                        };
                        this.mediaRecorder.start();
                        this.isRecording = true;
                    } catch (err) {}
                },
                async stopRecording() {
                    if (!this.isRecording || !this.mediaRecorder) { return; }
                    this.mediaRecorder.stop();
                    this.isRecording = false;
                    if (this.mediaStream) {
                        this.mediaStream.getTracks().forEach(t => t.stop());
                        this.mediaStream = null;
                    }
                },
                async saveRecording() {
                    if (!this.recordedBlob) { return; }
                    const file = new File([this.recordedBlob], 'intro.webm', { type: this.recordedBlob.type || 'video/webm' });
                    await this.uploadFile('video', file);
                },
                async deleteVideo() {
                    try {
                        const res = await fetch('/candidate/profile/delete-video', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const out = await res.json();
                        if (out && out.success) {
                            this.formData.basic.video_intro_url = '';
                            this.formData.basic.video_url = '';
                            this.formData.basic.video_type = 'upload';
                            if (typeof out.profile_strength !== 'undefined') {
                                this.profileStrength = out.profile_strength;
                            }
                        } else {
                            alert(out.error || 'Failed to delete video');
                        }
                    } catch (e) {
                        alert('Network error while deleting video');
                    }
                },
                nextStep() {
                    if (this.currentStep < 6) this.currentStep++;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                prevStep() {
                    if (this.currentStep > 1) this.currentStep--;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                async saveProfile() {
                    try {
                        const response = await fetch('/candidate/profile/save', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.formData)
                        });
                        const result = await response.json();
                        if (result.success) {
                            alert('Profile saved successfully!');
                            window.location.reload();
                        } else {
                            alert('Error saving profile: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred while saving profile.');
                    }
                }
            }));
        });
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
    <div x-data="profileForm" x-cloak>
        <!-- Shared Header -->
        <?php $base = $base ?? '/'; require __DIR__ . '/../include/header.php'; ?>
        
        <!-- Profile Strength Indicator -->
        <div class="bg-white border-b border-gray-200 sticky top-0 z-20 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Profile Strength: <strong class="text-indigo-600" x-text="profileStrength + '%'"></strong></span>
                    <a href="/candidate/dashboard" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Skip for now</a>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-2">
                    <h1 class="text-3xl font-bold text-gray-900">Complete Your Profile</h1>
                    <div class="text-right">
                        <div class="text-sm text-gray-600">Profile Strength</div>
                        <div class="text-2xl font-bold text-indigo-600" x-text="profileStrength + '%'"></div>
                    </div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-3 rounded-full transition-all duration-500 ease-out" 
                         :style="'width: ' + profileStrength + '%'"></div>
                </div>
                <div class="flex justify-between mt-2 text-xs text-gray-500">
                    <span>Start</span>
                    <span>50%</span>
                    <span>Complete</span>
                </div>
            </div>

            <!-- Steps Navigation -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8 p-4 overflow-x-auto">
                <div class="flex flex-nowrap gap-3 min-w-max">
                    <template x-for="(step, index) in ['Basic Details', 'Education', 'Experience', 'Skills', 'Languages', 'Additional']">
                        <button @click="currentStep = index + 1" 
                                :class="currentStep === index + 1 ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'"
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 whitespace-nowrap">
                            <span x-text="(index + 1) + '. ' + step"></span>
                        </button>
                    </template>
                </div>
            </div>
            
            <form @submit.prevent="saveProfile" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 md:p-8 min-h-[500px] relative">
                
                <!-- Step 1: Basic Details -->
                <div x-show="currentStep === 1" x-transition:enter="step-enter" class="space-y-6">
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-4">Basic Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" x-model="formData.basic.full_name" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Enter your full name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                            <input type="date" x-model="formData.basic.dob" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                            <select x-model="formData.basic.gender" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                            <input type="tel" x-model="formData.basic.mobile" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Enter mobile number">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <input type="text" x-model="formData.basic.city" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="City">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                            <input type="text" x-model="formData.basic.state" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="State">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                            <input type="text" x-model="formData.basic.country" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Country">
                        </div>
                    </div>

                    <div class="space-y-4 pt-4 border-t">
                        <h3 class="text-lg font-semibold text-gray-800">Introduction</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Self Introduction</label>
                            <textarea x-model="formData.basic.self_introduction" rows="4" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Tell us about yourself..."></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Profile Picture</label>
                                <div class="flex items-center gap-4">
                                    <div class="w-20 h-20 rounded-full overflow-hidden bg-gray-100 flex items-center justify-center">
                                        <img x-show="formData.basic.profile_picture" :src="formData.basic.profile_picture" class="w-full h-full object-cover" alt="">
                                        <span x-show="!formData.basic.profile_picture" class="text-gray-400 text-xs">No Image</span>
                                    </div>
                                    <label class="px-3 py-2 btn-primary rounded-md text-sm cursor-pointer">
                                        <input type="file" class="hidden" accept="image/*" @change="($event.target.files[0]) && uploadFile('profile_picture', $event.target.files[0])">
                                        Upload
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Resume / CV</label>
                                <div class="flex items-center gap-3">
                                    <input type="file" accept=".pdf,.doc,.docx" @change="($event.target.files[0]) && uploadFile('resume', $event.target.files[0])" class="text-sm">
                                    <span x-show="formData.basic.resume_url" class="text-xs text-indigo-600">Uploaded</span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Self-Introduction Video</label>
                            <div class="flex items-center gap-6">
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="radio" x-model="formData.basic.video_type" value="upload">
                                    <span>Upload</span>
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="radio" x-model="formData.basic.video_type" value="youtube">
                                    <span>YouTube Link</span>
                                </label>
                                <label class="flex items-center gap-2 text-sm" :class="!isPremium ? 'opacity-50 cursor-not-allowed' : ''">
                                    <input type="radio" x-model="formData.basic.video_type" value="record" :disabled="!isPremium">
                                    <span>Record</span>
                                </label>
                            </div>
                            <div x-show="formData.basic.video_type === 'upload'">
                                <input type="file" accept="video/mp4,video/*" @change="($event.target.files[0]) && uploadFile('video', $event.target.files[0])" class="text-sm">
                            </div>
                            <div x-show="formData.basic.video_type === 'youtube'">
                                <input type="url" x-model="formData.basic.video_url" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="https://youtube.com/watch?v=...">
                            </div>
                            <div x-show="formData.basic.video_type === 'record'">
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="mb-3 text-sm" x-show="!isPremium">Premium required to record video.</div>
                                    <div x-show="isPremium">
                                        <div class="aspect-video bg-black rounded-lg overflow-hidden">
                                            <video x-ref="recPreview" autoplay playsinline muted class="w-full h-full"></video>
                                        </div>
                                        <div class="mt-3 flex items-center gap-3">
                                            <button type="button" class="px-4 py-2 btn-primary rounded-md text-sm" @click="startRecording()" :disabled="isRecording || !recordingSupported">Start Recording</button>
                                            <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm" @click="stopRecording()" :disabled="!isRecording">Stop</button>
                                            <button type="button" class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-md text-sm" @click="saveRecording()" :disabled="!recordedBlob">Save Video</button>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-2">640×360, 24fps, compressed upload.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2" x-show="formData.basic.video_intro_url">
                                <div class="flex items-center gap-3">
                                    <a :href="formData.basic.video_intro_url"
                                       target="_blank"
                                       class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition text-sm font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        View Video
                                    </a>
                                    <template x-if="isPremium">
                                        <button type="button"
                                                class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition text-sm font-medium"
                                                @click="if (confirm('Delete your introduction video? This cannot be undone.')) deleteVideo()">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7"></path>
                                            </svg>
                                            Delete Video
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="button" class="px-6 py-2 btn-primary rounded-md" @click="saveSection('basic'); nextStep();">Save & Continue</button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Education -->
                <div x-show="currentStep === 2" x-transition:enter="step-enter" class="space-y-6">
                    <div class="flex justify-between items-center border-b pb-4">
                        <h2 class="text-2xl font-bold text-gray-800">Education Details</h2>
                        <button type="button" @click="addEducation()" class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-100 transition">
                            + Add Education
                        </button>
                    </div>
                    
                    <template x-for="(edu, index) in formData.education" :key="index">
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 relative group">
                            <button type="button" @click="removeEducation(index)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Degree/Course</label>
                                    <input type="text" x-model="edu.degree" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. B.Tech, MBA">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Field of Study</label>
                                    <input type="text" x-model="edu.field" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. Computer Science">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Institution/University</label>
                                    <input type="text" x-model="edu.institution" class="w-full rounded-lg border-gray-300 text-sm" placeholder="University Name">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Year of Passing</label>
                                    <input type="number" x-model="edu.year" class="w-full rounded-lg border-gray-300 text-sm" placeholder="YYYY">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Percentage/CGPA</label>
                                    <input type="text" x-model="edu.percentage" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. 85% or 8.5">
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="formData.education.length === 0" class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                        No education details added yet. Click "Add Education" to start.
                    </div>
                    <div class="flex justify-between pt-4">
                        <button type="button" class="px-6 py-2 border rounded-md" @click="prevStep()">Previous</button>
                        <button type="button" class="px-6 py-2 btn-primary rounded-md" @click="saveSection('education'); nextStep();">Save & Continue</button>
                    </div>
                </div>

                <!-- Step 3: Experience -->
                <div x-show="currentStep === 3" x-transition:enter="step-enter" class="space-y-6">
                    <div class="flex justify-between items-center border-b pb-4">
                        <h2 class="text-2xl font-bold text-gray-800">Work Experience</h2>
                        <button type="button" @click="addExperience()" class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-100 transition">
                            + Add Experience
                        </button>
                    </div>

                    <template x-for="(exp, index) in formData.experience" :key="index">
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 relative">
                            <button type="button" @click="removeExperience(index)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Job Title</label>
                                    <input type="text" x-model="exp.job_title" class="w-full rounded-lg border-gray-300 text-sm" placeholder="e.g. Software Engineer">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Company Name</label>
                                    <input type="text" x-model="exp.company" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Company Name">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Start Date</label>
                                    <input type="date" x-model="exp.start_date" class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">End Date</label>
                                    <input type="date" x-model="exp.end_date" :disabled="exp.is_current" class="w-full rounded-lg border-gray-300 text-sm disabled:bg-gray-100">
                                    <div class="mt-1 flex items-center">
                                        <input type="checkbox" x-model="exp.is_current" class="rounded text-indigo-600 focus:ring-indigo-500">
                                        <span class="ml-2 text-xs text-gray-600">Currently Working</span>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
                                    <textarea x-model="exp.description" rows="2" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Brief description of roles and responsibilities"></textarea>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="formData.experience.length === 0" class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                        No experience details added yet. Click "Add Experience" to start.
                    </div>
                    <div class="flex justify-between pt-4">
                        <button type="button" class="px-6 py-2 border rounded-md" @click="prevStep()">Previous</button>
                        <button type="button" class="px-6 py-2 btn-primary rounded-md" @click="saveSection('experience'); nextStep();">Save & Continue</button>
                    </div>
                </div>

                <!-- Step 4: Skills -->
                <div x-show="currentStep === 4" x-transition:enter="step-enter" class="space-y-6">
                    <div class="flex justify-between items-center border-b pb-4">
                        <h2 class="text-2xl font-bold text-gray-800">Skills</h2>
                        <button type="button" @click="addSkill()" class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-100 transition">
                            + Add Skill
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="(skill, index) in formData.skills" :key="index">
                            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex-1">
                                    <input type="text" x-model="skill.name" class="w-full rounded border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Skill Name (e.g. PHP)">
                                </div>
                                <div class="w-32">
                                    <select x-model="skill.level" class="w-full rounded border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="beginner">Beginner</option>
                                        <option value="intermediate">Intermediate</option>
                                        <option value="expert">Expert</option>
                                    </select>
                                </div>
                                <button type="button" @click="removeSkill(index)" class="text-gray-400 hover:text-red-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                    <div x-show="formData.skills.length === 0" class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                        Add your key skills to stand out to recruiters.
                    </div>
                    <div class="flex justify-between pt-4">
                        <button type="button" class="px-6 py-2 border rounded-md" @click="prevStep()">Previous</button>
                        <button type="button" class="px-6 py-2 btn-primary rounded-md" @click="saveSection('skills'); nextStep();">Save & Continue</button>
                    </div>
                </div>

                <!-- Step 5: Languages -->
                <div x-show="currentStep === 5" x-transition:enter="step-enter" class="space-y-6">
                    <div class="flex justify-between items-center border-b pb-4">
                        <h2 class="text-2xl font-bold text-gray-800">Languages</h2>
                        <button type="button" @click="addLanguage()" class="px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-100 transition">
                            + Add Language
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="(lang, index) in formData.languages" :key="index">
                            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex-1">
                                    <input type="text" x-model="lang.language" class="w-full rounded border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Language (e.g. English)">
                                </div>
                                <div class="w-40">
                                    <select x-model="lang.proficiency" class="w-full rounded border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="basic">Basic</option>
                                        <option value="conversational">Conversational</option>
                                        <option value="fluent">Fluent</option>
                                        <option value="native">Native</option>
                                    </select>
                                </div>
                                <button type="button" @click="removeLanguage(index)" class="text-gray-400 hover:text-red-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                    <div class="flex justify-between pt-4">
                        <button type="button" class="px-6 py-2 border rounded-md" @click="prevStep()">Previous</button>
                        <button type="button" class="px-6 py-2 btn-primary rounded-md" @click="saveSection('languages'); nextStep();">Save & Continue</button>
                    </div>
                </div>

                <!-- Step 6: Additional -->
                <div x-show="currentStep === 6" x-transition:enter="step-enter" class="space-y-6">
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-4">Additional Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expected Salary (Min)</label>
                            <input type="number" x-model="formData.additional.expected_salary_min" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Annual Salary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expected Salary (Max)</label>
                            <input type="number" x-model="formData.additional.expected_salary_max" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="Annual Salary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notice Period</label>
                            <select x-model="formData.additional.notice_period" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                <option value="">Select Notice Period</option>
                                <option value="Immediate">Immediate</option>
                                <option value="15 Days">15 Days</option>
                                <option value="30 Days">30 Days</option>
                                <option value="60 Days">60 Days</option>
                                <option value="90 Days">90 Days</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Location</label>
                            <input type="text" x-model="formData.additional.preferred_job_location" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="e.g. Bangalore, Remote">
                        </div>
                    </div>

                    <div class="space-y-4 pt-4 border-t">
                        <h3 class="text-lg font-semibold text-gray-800">Social Links</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn URL</label>
                                <input type="url" x-model="formData.additional.linkedin_url" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="https://linkedin.com/in/...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">GitHub URL</label>
                                <input type="url" x-model="formData.additional.github_url" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="https://github.com/...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Portfolio/Website</label>
                                <input type="url" x-model="formData.additional.portfolio_url" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" placeholder="https://...">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between pt-4">
                        <button type="button" class="px-6 py-2 border rounded-md" @click="prevStep()">Previous</button>
                        <button type="button" class="px-8 py-2 btn-primary rounded-md" @click="saveSection('additional')">Save & Complete Profile</button>
                    </div>
                </div>

                
            </form>
        </div>
    </div>
    <?php include __DIR__ . '/../include/footer.php'; ?>
</body>
</html>
