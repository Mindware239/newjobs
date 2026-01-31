<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job - Job Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Quill Rich Text Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        #job-description-editor {
            min-height: 300px;
        }
        .ql-editor {
            min-height: 300px;
            font-size: 14px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div x-data="jobPostForm()" x-init="init()" x-cloak>
        <!-- Header -->
        <nav class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <a href="/" class="text-2xl font-bold text-blue-600">Job Portal</a>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/employer/dashboard" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Profile
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - Form -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h1 class="text-2xl font-bold text-gray-900 mb-6">Post a Job</h1>
                        
                        <!-- Basic Job Details -->
                        <div class="mb-8">
                            <h2 class="text-xl font-semibold mb-4">Basic Job Detail</h2>
                            
                            <!-- Job Type -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Job Type <span class="text-red-500">*</span>
                                </label>
                                <div class="flex space-x-4">
                                    <button @click="formData.employment_type = 'full_time'" 
                                            :class="formData.employment_type === 'full_time' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border'"
                                            class="px-6 py-2 rounded-md font-medium transition">
                                        Full Time
                                    </button>
                                    <button @click="formData.employment_type = 'part_time'"
                                            :class="formData.employment_type === 'part_time' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border'"
                                            class="px-6 py-2 rounded-md font-medium transition">
                                        Part Time
                                    </button>
                                </div>
                            </div>

                            <!-- Job Title -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Job Title <span class="text-red-500">*</span>
                                </label>
                                <input type="text" x-model="formData.title" 
                                       placeholder="Enter the Job Title"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Job Location -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Job Location <span class="text-red-500">*</span>
                                    <button type="button" @click="getCurrentLocation()" 
                                            class="ml-2 text-xs text-blue-600 hover:text-blue-800 font-medium">
                                        📍 Use My Current Location
                                    </button>
                                    <span x-show="locationLoading" class="ml-2 text-xs text-gray-500">Detecting location...</span>
                                </label>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <select x-model="formData.location.state" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                            <option value="">Select State</option>
                                            <option value="Andhra Pradesh">Andhra Pradesh</option>
                                            <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                                            <option value="Assam">Assam</option>
                                            <option value="Bihar">Bihar</option>
                                            <option value="Chhattisgarh">Chhattisgarh</option>
                                            <option value="Goa">Goa</option>
                                            <option value="Gujarat">Gujarat</option>
                                            <option value="Haryana">Haryana</option>
                                            <option value="Himachal Pradesh">Himachal Pradesh</option>
                                            <option value="Jharkhand">Jharkhand</option>
                                            <option value="Karnataka">Karnataka</option>
                                            <option value="Kerala">Kerala</option>
                                            <option value="Madhya Pradesh">Madhya Pradesh</option>
                                            <option value="Maharashtra">Maharashtra</option>
                                            <option value="Manipur">Manipur</option>
                                            <option value="Meghalaya">Meghalaya</option>
                                            <option value="Mizoram">Mizoram</option>
                                            <option value="Nagaland">Nagaland</option>
                                            <option value="Odisha">Odisha</option>
                                            <option value="Punjab">Punjab</option>
                                            <option value="Rajasthan">Rajasthan</option>
                                            <option value="Sikkim">Sikkim</option>
                                            <option value="Tamil Nadu">Tamil Nadu</option>
                                            <option value="Telangana">Telangana</option>
                                            <option value="Tripura">Tripura</option>
                                            <option value="Uttar Pradesh">Uttar Pradesh</option>
                                            <option value="Uttarakhand">Uttarakhand</option>
                                            <option value="West Bengal">West Bengal</option>
                                            <option value="Delhi">Delhi (NCT)</option>
                                            <option value="Jammu and Kashmir">Jammu and Kashmir</option>
                                            <option value="Ladakh">Ladakh</option>
                                            <option value="Puducherry">Puducherry</option>
                                        </select>
                                    </div>
                                    <div>
                                        <input type="text" x-model="formData.location.city" 
                                               placeholder="e.g. New Delhi, Mumbai, Bangalore"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    <span x-show="formData.location.state && formData.location.city">
                                        Selected: <strong x-text="formData.location.city + ', ' + formData.location.state"></strong>
                                    </span>
                                    <span x-show="!formData.location.state || !formData.location.city" class="text-red-500">
                                        Please select state and enter city
                                    </span>
                                </p>
                            </div>

                            <!-- No of Openings -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    No Of Openings <span class="text-red-500">*</span>
                                </label>
                                <input type="number" x-model="formData.vacancies" 
                                       placeholder="Eg. 2"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- Candidate Requirement -->
                        <div class="mb-8">
                            <h2 class="text-xl font-semibold mb-4">Candidate Requirement</h2>
                            
                            <!-- Experience -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Total Experience of Candidate <span class="text-red-500">*</span>
                                </label>
                                <div class="flex space-x-4 mb-4">
                                    <button @click="setExperienceType('any')"
                                            :class="formData.experience_type === 'any' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border'"
                                            class="px-6 py-2 rounded-md font-medium transition">
                                        Any
                                    </button>
                                    <button @click="setExperienceType('fresher')"
                                            :class="formData.experience_type === 'fresher' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border'"
                                            class="px-6 py-2 rounded-md font-medium transition">
                                        Fresher Only
                                    </button>
                                    <button @click="setExperienceType('experienced')"
                                            :class="formData.experience_type === 'experienced' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border'"
                                            class="px-6 py-2 rounded-md font-medium transition">
                                        Experienced Only
                                    </button>
                                </div>
                                
                                <div x-show="formData.experience_type === 'experienced'" class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Minimum Experience</label>
                                        <select x-model="formData.min_experience" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-md">
                                            <option value="">Select Min</option>
                                            <option value="0">Fresher</option>
                                            <option value="1">1 Year</option>
                                            <option value="2">2 Years</option>
                                            <option value="3">3 Years</option>
                                            <option value="5">5+ Years</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Maximum Experience</label>
                                        <select x-model="formData.max_experience" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-md">
                                            <option value="">Select Max</option>
                                            <option value="1">1 Year</option>
                                            <option value="2">2 Years</option>
                                            <option value="3">3 Years</option>
                                            <option value="5">5 Years</option>
                                            <option value="10">10+ Years</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Salary -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Monthly In-hand Salary <span class="text-red-500">*</span>
                                    <span class="text-gray-500 text-xs">(Only Put Actual Salary)</span>
                                </label>
                                <div class="flex items-center space-x-2">
                                    <input type="number" x-model="formData.salary_min" 
                                           placeholder="Eg. 10000"
                                           class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                    <span class="text-gray-600">to</span>
                                    <input type="number" x-model="formData.salary_max" 
                                           placeholder="Eg. 15000"
                                           class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            <!-- Bonus -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Do you offer bonus in addition to monthly salary? <span class="text-red-500">*</span>
                                </label>
                                <div class="flex space-x-4">
                                    <label class="flex items-center">
                                        <input type="radio" x-model="formData.offers_bonus" value="yes" class="mr-2">
                                        <span>Yes</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" x-model="formData.offers_bonus" value="no" class="mr-2">
                                        <span>No</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Job Description -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Job Info / Job Description <span class="text-red-500">*</span>
                                </label>
                                <div id="job-description-editor" class="border border-gray-300 rounded-md bg-white">
                                    <!-- Quill editor will be initialized here -->
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    Use the toolbar above to format your job description. You can add bullet points, bold text, and more.
                                </p>
                            </div>

                            <!-- Call Availability -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Candidates can call me <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                    <button @click="formData.call_availability = 'everyday'"
                                            :class="formData.call_availability === 'everyday' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border'"
                                            class="px-4 py-2 rounded-md text-sm font-medium transition">
                                        Everyday
                                    </button>
                                    <button @click="formData.call_availability = 'weekdays'"
                                            :class="formData.call_availability === 'weekdays' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border'"
                                            class="px-4 py-2 rounded-md text-sm font-medium transition">
                                        Monday to Friday
                                    </button>
                                    <button @click="formData.call_availability = 'weekdays_saturday'"
                                            :class="formData.call_availability === 'weekdays_saturday' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border'"
                                            class="px-4 py-2 rounded-md text-sm font-medium transition">
                                        Monday to Saturday
                                    </button>
                                    <button @click="formData.call_availability = 'custom'"
                                            :class="formData.call_availability === 'custom' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border'"
                                            class="px-4 py-2 rounded-md text-sm font-medium transition">
                                        Custom
                                    </button>
                                </div>
                            </div>

                            <!-- Skills -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Skills
                                </label>
                                <input type="text" 
                                       @keydown.enter.prevent="addSkill($event)"
                                       placeholder="Type to search for skills"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <template x-for="skill in formData.skills" :key="skill">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                                            <span x-text="skill"></span>
                                            <button @click="removeSkill(skill)" class="ml-2 text-blue-600 hover:text-blue-800">×</button>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Timings -->
                        <div class="mb-8">
                            <h2 class="text-xl font-semibold mb-4">Timings</h2>
                            
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Job Timings <span class="text-red-500">*</span>
                                </label>
                                <input type="text" x-model="formData.job_timings" 
                                       placeholder="9:30 AM - 6:30 PM | Monday to Saturday"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Please mention job timings correctly otherwise candidates may not join</p>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Interview Details <span class="text-red-500">*</span>
                                </label>
                                <input type="text" x-model="formData.interview_timings" 
                                       placeholder="11:00 AM - 4:00 PM | Monday to Saturday"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- About Your Company -->
                        <div class="mb-8">
                            <h2 class="text-xl font-semibold mb-4">About Your Company</h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Company Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" x-model="formData.company_name" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Contact Person Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" x-model="formData.contact_person" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number <span class="text-red-500">*</span>
                                </label>
                                <div class="flex">
                                    <select class="px-3 py-2 border border-gray-300 rounded-l-md bg-gray-50">
                                        <option>+91</option>
                                    </select>
                                    <input type="tel" x-model="formData.phone" 
                                           placeholder="8527522688"
                                           class="flex-1 px-4 py-2 border border-gray-300 rounded-r-md focus:ring-2 focus:ring-blue-500">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Candidates will call you on this number.</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Id <span class="text-red-500">*</span>
                                </label>
                                <input type="email" x-model="formData.email" 
                                       placeholder="hr@company.com"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Candidates will send resumes on this email-id.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Contact Person Profile <span class="text-red-500">*</span>
                                    </label>
                                    <select x-model="formData.contact_profile" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select</option>
                                        <option value="owner">Owner/Partner</option>
                                        <option value="hr">HR Manager</option>
                                        <option value="recruiter">Recruiter</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Size of Organization <span class="text-red-500">*</span>
                                    </label>
                                    <select x-model="formData.company_size" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select</option>
                                        <option value="1-10">1-10</option>
                                        <option value="11-50">11-50</option>
                                        <option value="51-200">51-200</option>
                                        <option value="201-500">201-500</option>
                                        <option value="501-1000">501-1000</option>
                                        <option value="1001+">1001+</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    How soon do you want to fill the position? <span class="text-red-500">*</span>
                                </label>
                                <div class="flex space-x-4">
                                    <button @click="formData.hiring_urgency = 'immediate'"
                                            :class="formData.hiring_urgency === 'immediate' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border'"
                                            class="px-6 py-2 rounded-md font-medium transition">
                                        Immediately (1-2 weeks)
                                    </button>
                                    <button @click="formData.hiring_urgency = 'can_wait'"
                                            :class="formData.hiring_urgency === 'can_wait' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border'"
                                            class="px-6 py-2 rounded-md font-medium transition">
                                        Can wait
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Job Address <span class="text-red-500">*</span>
                                    <span class="text-gray-500 text-xs">(Address ONLY shown to registered candidates)</span>
                                </label>
                                <textarea x-model="formData.job_address" 
                                          rows="3"
                                          placeholder="Dwarka Sector 12, delhi"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"></textarea>
                                <p class="text-xs text-gray-500 mt-1">Please fill complete address, mention Landmark near your office</p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-4">
                            <?php if (!isset($isEdit) || !$isEdit): ?>
                            <button @click="saveDraft()" 
                                    class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Save as Draft
                            </button>
                            <?php endif; ?>
                            <button @click="submitJob()" 
                                    :disabled="isSubmitting"
                                    class="px-8 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                                <span x-show="!isSubmitting"><?= isset($isEdit) && $isEdit ? 'Update Job' : 'Submit' ?></span>
                                <span x-show="isSubmitting"><?= isset($isEdit) && $isEdit ? 'Updating...' : 'Submitting...' ?></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Performance Insights -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow p-6 sticky top-8">
                        <h2 class="text-xl font-semibold mb-4">Performance Insights Hub</h2>
                        
                        <!-- Job Reach Meter -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-medium">Job Reach Meter</h3>
                                <span class="text-gray-400 text-xs">ℹ</span>
                            </div>
                            <div class="relative w-full h-32 flex items-center justify-center">
                                <svg class="w-32 h-32 transform -rotate-90">
                                    <circle cx="64" cy="64" r="56" stroke="#e5e7eb" stroke-width="8" fill="none"/>
                                    <circle cx="64" cy="64" r="56" stroke="#3b82f6" stroke-width="8" fill="none"
                                            stroke-dasharray="351.86" stroke-dashoffset="300" class="transition-all"/>
                                </svg>
                                <div class="absolute text-center">
                                    <p class="text-xs text-gray-500">Not enough data</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Based on your requirement parameters</p>
                        </div>

                        <!-- Salary Trends -->
                        <div>
                            <h3 class="font-medium mb-3 flex items-center">
                                <span class="mr-2">📊</span>
                                Salary trends for your requirements are shown here
                            </h3>
                            <div class="space-y-2">
                                <div>
                                    <p class="text-sm text-gray-600">Minimum Salary</p>
                                    <p class="text-lg font-semibold" x-text="formData.salary_min ? '₹' + formData.salary_min : '--'"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Maximum Salary</p>
                                    <p class="text-lg font-semibold" x-text="formData.salary_max ? '₹' + formData.salary_max : '--'"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let quillEditor = null;
        const isEditMode = <?= isset($isEdit) && $isEdit ? 'true' : 'false' ?>;
        const jobId = <?= isset($job) && isset($job['id']) ? (int)$job['id'] : 'null' ?>;

        function jobPostForm() {
            return {
                isSubmitting: false,
                quillInitialized: false,
                locationLoading: false,
                formData: {
                    employment_type: '<?= isset($job) ? htmlspecialchars($job['employment_type'] ?? 'full_time', ENT_QUOTES) : 'full_time' ?>',
                    title: '<?= isset($job) ? htmlspecialchars($job['title'] ?? '', ENT_QUOTES) : '' ?>',
                    location: { 
                        state: '<?= isset($locations) && !empty($locations) ? htmlspecialchars($locations[0]['state'] ?? '', ENT_QUOTES) : '' ?>', 
                        city: '<?= isset($locations) && !empty($locations) ? htmlspecialchars($locations[0]['city'] ?? '', ENT_QUOTES) : '' ?>' 
                    },
                    vacancies: <?= isset($job) ? (int)($job['vacancies'] ?? 1) : '1' ?>,
                    experience_type: 'any',
                    min_experience: '',
                    max_experience: '',
                    salary_min: '<?= isset($job) ? ($job['salary_min'] ?? '') : '' ?>',
                    salary_max: '<?= isset($job) ? ($job['salary_max'] ?? '') : '' ?>',
                    offers_bonus: 'no',
                    description: '<?= isset($job) ? htmlspecialchars($job['description'] ?? '', ENT_QUOTES) : '' ?>',
                    call_availability: 'everyday',
                    skills: <?= isset($skills) ? json_encode(array_column($skills, 'name')) : '[]' ?>,
                    job_timings: '',
                    interview_timings: '',
                    company_name: '<?= isset($employer) ? htmlspecialchars($employer->company_name ?? '', ENT_QUOTES) : '' ?>',
                    contact_person: '',
                    phone: '',
                    email: '',
                    contact_profile: '',
                    company_size: '',
                    hiring_urgency: 'immediate',
                    job_address: ''
                },
                init() {
                    // Initialize Quill editor after Alpine.js is ready
                    this.$nextTick(() => {
                        this.initQuill();
                    });
                },
                initQuill() {
                    if (this.quillInitialized || quillEditor) return;
                    
                    const editorElement = document.getElementById('job-description-editor');
                    if (!editorElement) return;
                    
                    quillEditor = new Quill('#job-description-editor', {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                [{ 'indent': '-1'}, { 'indent': '+1' }],
                                [{ 'align': [] }],
                                ['link'],
                                [{ 'color': [] }, { 'background': [] }],
                                ['clean']
                            ]
                        },
                        placeholder: 'Enter job description... (No character limit)'
                    });

                    // Sync Quill content with formData.description
                    quillEditor.on('text-change', () => {
                        this.formData.description = quillEditor.root.innerHTML;
                    });

                    // If there's existing content, set it
                    if (this.formData.description) {
                        // If it's HTML from Quill, use it directly, otherwise convert to HTML
                        if (this.formData.description.includes('<')) {
                            quillEditor.root.innerHTML = this.formData.description;
                        } else {
                            quillEditor.root.textContent = this.formData.description;
                        }
                    }

                    this.quillInitialized = true;
                },
                setExperienceType(type) {
                    this.formData.experience_type = type;
                    if (type !== 'experienced') {
                        this.formData.min_experience = '';
                        this.formData.max_experience = '';
                    }
                },
                addSkill(event) {
                    const skill = event.target.value.trim();
                    if (skill && !this.formData.skills.includes(skill)) {
                        this.formData.skills.push(skill);
                        event.target.value = '';
                    }
                },
                removeSkill(skill) {
                    this.formData.skills = this.formData.skills.filter(s => s !== skill);
                },
                async getCurrentLocation() {
                    this.locationLoading = true;
                    if (!navigator.geolocation) {
                        alert('Geolocation is not supported by your browser');
                        this.locationLoading = false;
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        async (position) => {
                            try {
                                const lat = position.coords.latitude;
                                const lon = position.coords.longitude;
                                
                                // Use reverse geocoding API to get location
                                const response = await fetch(
                                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1`
                                );
                                const data = await response.json();
                                
                                if (data && data.address) {
                                    const address = data.address;
                                    // Extract city and state from address
                                    const city = address.city || address.town || address.village || address.suburb || '';
                                    const state = address.state || '';
                                    
                                    if (city) this.formData.location.city = city;
                                    if (state) this.formData.location.state = state;
                                    
                                    if (city && state) {
                                        alert(`Location detected: ${city}, ${state}`);
                                    } else {
                                        alert('Location detected but could not extract city/state. Please select manually.');
                                    }
                                } else {
                                    alert('Could not determine location. Please select manually.');
                                }
                            } catch (error) {
                                console.error('Geocoding error:', error);
                                alert('Error fetching location details. Please select manually.');
                            }
                            this.locationLoading = false;
                        },
                        (error) => {
                            console.error('Geolocation error:', error);
                            alert('Could not get your location. Please select manually.');
                            this.locationLoading = false;
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        }
                    );
                },
                async submitJob() {
                    // Validate location
                    if (!this.formData.location.state || !this.formData.location.city) {
                        alert('Please select state and enter city for job location');
                        return;
                    }
                    
                    // Get latest content from Quill editor
                    if (quillEditor) {
                        this.formData.description = quillEditor.root.innerHTML;
                    }
                    
                    // Format location as array for backend
                    const submitData = {
                        ...this.formData,
                        location: [{
                            city: this.formData.location.city,
                            state: this.formData.location.state,
                            country: 'India'
                        }]
                    };
                    
                    this.isSubmitting = true;
                    try {
                        const url = isEditMode && jobId ? `/employer/jobs/${jobId}` : '/employer/jobs';
                        const method = isEditMode && jobId ? 'PUT' : 'POST';
                        
                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': this.getCsrfToken()
                            },
                            body: JSON.stringify(submitData)
                        });
                        const data = await response.json();
                        if (response.ok) {
                            alert(isEditMode ? 'Job updated successfully!' : 'Job posted successfully!');
                            window.location.href = '/employer/jobs';
                        } else {
                            alert('Error: ' + (data.error || (isEditMode ? 'Failed to update job' : 'Failed to post job')));
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                    } finally {
                        this.isSubmitting = false;
                    }
                },
                async saveDraft() {
                    // Get latest content from Quill editor
                    if (quillEditor) {
                        this.formData.description = quillEditor.root.innerHTML;
                    }
                    // Similar to submit but with status=draft
                    this.formData.status = 'draft';
                    await this.submitJob();
                },
                getCsrfToken() {
                    // Get CSRF token from meta tag or cookie
                    return document.querySelector('meta[name="csrf-token"]')?.content || '';
                }
            }
        }
    </script>
</body>
</html>

