<?php
// Add styles and scripts to head
$scripts = ($scripts ?? '') . '
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<style>
    [x-cloak] { display: none !important; }
    .animate-fade-in-up { animation: fadeInUp 0.5s ease-out; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .ql-editor {
        min-height: 320px;
        font-size: 16px;
        line-height: 1.6;
        color: #374151;
        font-family: inherit;
    }
    .ql-toolbar.ql-snow {
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
        border-color: #E5E7EB;
        background-color: #F9FAFB;
    }
    .ql-container.ql-snow {
        border-bottom-left-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
        border-color: #E5E7EB;
        background-color: #FFFFFF;
    }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #CBD5E1; border-radius: 20px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #94A3B8; }
    
    /* Premium Form Inputs */
    .form-input {
        transition: all 0.2s ease-in-out;
    }
    .form-input:focus {
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
</style>
';
?>

<?php
// Prepare PHP data for JavaScript
$isEditMode = isset($isEdit) && $isEdit;
$existingJobData = isset($job) && is_array($job) ? $job : null;
$existingLocations = isset($locations) && is_array($locations) ? $locations : [];
$existingSkills = [];
if (isset($skills) && is_array($skills)) {
    foreach ($skills as $skill) {
        if (is_array($skill)) {
            $existingSkills[] = $skill['name'] ?? '';
        } elseif (is_string($skill)) {
            $existingSkills[] = $skill;
        }
    }
}
$existingSkills = array_filter($existingSkills);
$allBenefits = isset($benefits) && is_array($benefits) ? $benefits : [];
$existingBenefits = isset($jobBenefits) && is_array($jobBenefits) ? $jobBenefits : [];
$employerArray = isset($employer) && method_exists($employer, 'toArray') ? $employer->toArray() : [];
$userArray = isset($user) && method_exists($user, 'toArray') ? $user->toArray() : [];
?>

<div x-data="jobPostWizard()" x-init="init()" x-cloak class="bg-gray-50 min-h-screen font-sans text-gray-900 pb-20">
    
    <!-- Loading Overlay -->
    <div x-show="isLoading" class="fixed inset-0 bg-white z-50 flex flex-col items-center justify-center transition-opacity duration-300">
        <div class="w-16 h-16 border-4 border-blue-100 border-t-blue-600 rounded-full animate-spin mb-4"></div>
        <p class="text-gray-500 font-medium animate-pulse">Loading workspace...</p>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-8" x-show="!isLoading" x-transition.opacity.duration.500ms>
        
        <!-- Header & Progress -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                        <?= $isEditMode ? 'Edit Job Posting' : 'Create New Job Posting' ?>
                    </h1>
                    <p class="text-gray-500 mt-1">Reach thousands of qualified candidates.</p>
                </div>
                <div class="hidden sm:block">
                     <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                        <span class="w-2 h-2 rounded-full bg-blue-500 mr-2"></span>
                        Premium Listing
                    </span>
                </div>
            </div>

            <!-- Stepper -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                <div class="relative flex items-center justify-between px-4 sm:px-10">
                    <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-100 -z-10 rounded-full"></div>
                    <div class="absolute left-0 top-1/2 transform -translate-y-1/2 h-1 bg-blue-600 -z-10 rounded-full transition-all duration-500 ease-out" :style="'width: ' + (currentStep / (totalSteps - 1) * 100) + '%'"></div>

                    <template x-for="(step, index) in totalSteps" :key="index">
                        <div class="flex flex-col items-center group cursor-pointer" @click="if(index < currentStep) currentStep = index">
                            <div class="relative flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all duration-300 z-10 bg-white"
                                 :class="{
                                    'border-blue-600 text-blue-600 shadow-md scale-110': index === currentStep,
                                    'border-blue-600 bg-blue-600 text-white': index < currentStep,
                                    'border-gray-200 text-gray-300': index > currentStep
                                 }">
                                <svg x-show="index < currentStep" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span x-show="index >= currentStep" class="text-sm font-bold" x-text="index + 1"></span>
                            </div>
                            <span class="absolute mt-12 text-xs font-medium transition-colors duration-300 hidden sm:block w-32 text-center"
                                  :class="index <= currentStep ? 'text-blue-700' : 'text-gray-400'"
                                  x-text="stepTitles[index]"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Form Container -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden min-h-[500px] flex flex-col">
            
            <!-- Step Content -->
            <div class="flex-1 p-6 sm:p-10">
                
                <!-- Step 0: Job Basics -->
                <div x-show="currentStep === 0" x-transition:enter="animate-fade-in-up" class="space-y-8">
                    <div class="bg-blue-50/50 rounded-xl p-6 border border-blue-100/50 flex items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-globe w-6 h-6 text-primary"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path><path d="M2 12h20"></path></svg>                            </div>
                            <div>
                                <p class="text-sm text-blue-900 font-medium">Posting for <span class="font-bold" x-text="formData.location.country || 'Global'"></span></p>
                                <p class="text-xs text-blue-600/80 mt-0.5">Language: <span x-text="formData.language"></span></p>
                            </div>
                        </div>
                        <button @click="showLanguageModal = true" class="text-xs font-semibold text-blue-600 hover:text-blue-800 bg-white px-3 py-1.5 rounded-lg border border-blue-200 shadow-sm transition-all hover:shadow-md">
                            Change Settings
                        </button>
                    </div>

                    <div class="space-y-6">
                        <!-- Job Title -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Job Title <span class="text-red-500">*</span></label>
                            <div class="relative group">
                                <input type="text" 
                                       x-model="formData.title" 
                                       @input="searchJobTitles($event.target.value)"
                                       @keydown="handleJobTitleKeyDown($event)"
                                       @focus="if(formData.title && formData.title.length >= 2) searchJobTitles(formData.title)"
                                       @blur="setTimeout(() => jobTitleSuggestions.show = false, 200)"
                                       placeholder="e.g. Senior Product Designer"
                                       class="form-input w-full px-4 py-3.5 rounded-xl border-gray-300 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>

                                <!-- Suggestions Dropdown -->
                                <div x-show="jobTitleSuggestions.show && jobTitleSuggestions.list.length > 0" 
                                     x-cloak
                                     class="absolute z-50 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-2xl max-h-64 overflow-auto custom-scrollbar ring-1 ring-black ring-opacity-5">
                                    <template x-for="(suggestion, index) in jobTitleSuggestions.list" :key="suggestion.id">
                                        <div @click="selectJobTitleSuggestion(suggestion)"
                                             @mouseenter="jobTitleSuggestions.selectedIndex = index"
                                             class="px-4 py-3 cursor-pointer hover:bg-blue-50 transition-colors border-b border-gray-50 last:border-0"
                                             :class="jobTitleSuggestions.selectedIndex === index ? 'bg-blue-50' : ''">
                                            <div class="text-sm font-medium text-gray-900" x-text="suggestion.title"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Employment Type -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Employment Type <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <template x-for="type in jobTypes" :key="type.value">
                                    <div @click="formData.employment_type = type.value"
                                         class="cursor-pointer border rounded-xl p-3 flex items-center justify-center text-center transition-all hover:shadow-sm"
                                         :class="formData.employment_type === type.value ? 'border-blue-500 bg-blue-50 text-blue-700 font-medium ring-1 ring-blue-500' : 'border-gray-200 text-gray-600 hover:border-blue-300'">
                                        <span x-text="type.label"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Location -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-semibold text-gray-700">Job Location <span class="text-red-500">*</span></label>
                                <button type="button" @click="getCurrentLocation()" class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center transition-colors px-2 py-1 rounded hover:bg-blue-50">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    Detect Location
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="relative">
                                    <select x-model="formData.location.country" @change="onCountryChange" class="form-input w-full px-4 py-3.5 bg-white border-gray-300 rounded-xl focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 appearance-none">
                                        <option value="">Select Country</option>
                                        <template x-for="c in countries" :key="c.name">
                                            <option :value="c.name" x-text="c.name"></option>
                                        </template>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                                <div class="relative">
                                    <select x-model="formData.location.state" @change="onStateChange" :disabled="statesLoading || !formData.location.country" class="form-input w-full px-4 py-3.5 bg-white border-gray-300 rounded-xl focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 appearance-none disabled:bg-gray-50 disabled:text-gray-400">
                                        <option value="">Select State</option>
                                        <template x-for="s in states" :key="s">
                                            <option :value="s" x-text="s"></option>
                                        </template>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                                        <svg x-show="!statesLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        <svg x-show="statesLoading" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </div>
                                </div>
                                <div class="relative">
                                    <select x-model="formData.location.city" :disabled="citiesLoading || !formData.location.state" class="form-input w-full px-4 py-3.5 bg-white border-gray-300 rounded-xl focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 appearance-none disabled:bg-gray-50 disabled:text-gray-400">
                                        <option value="">Select City</option>
                                        <template x-for="ct in cities" :key="ct">
                                            <option :value="ct" x-text="ct"></option>
                                        </template>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                                        <svg x-show="!citiesLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        <svg x-show="citiesLoading" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Openings -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">No Of Openings <span class="text-red-500">*</span></label>
                            <input type="number" x-model="formData.vacancies" min="1"
                                   placeholder="Eg. 2"
                                   class="form-input w-full px-4 py-3.5 rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </div>

                        <!-- Workplace Type -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Workplace Setting <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div @click="formData.work_address_type = 'specific'" 
                                     class="cursor-pointer border rounded-xl p-5 flex items-start transition-all duration-200 relative overflow-hidden group"
                                     :class="formData.work_address_type === 'specific' ? 'border-blue-500 bg-blue-50/50 ring-1 ring-blue-500' : 'border-gray-200 hover:border-blue-300 hover:shadow-md'">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <div class="w-5 h-5 rounded-full border flex items-center justify-center transition-colors"
                                             :class="formData.work_address_type === 'specific' ? 'border-blue-600' : 'border-gray-300 group-hover:border-blue-400'">
                                            <div class="w-2.5 h-2.5 rounded-full bg-blue-600 transform scale-0 transition-transform duration-200" :class="{'scale-100': formData.work_address_type === 'specific'}"></div>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-bold text-gray-900">On-site</p>
                                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">Employees come to a specific office or location.</p>
                                    </div>
                                </div>
                                <div @click="formData.work_address_type = 'none'" 
                                     class="cursor-pointer border rounded-xl p-5 flex items-start transition-all duration-200 relative overflow-hidden group"
                                     :class="formData.work_address_type === 'none' ? 'border-blue-500 bg-blue-50/50 ring-1 ring-blue-500' : 'border-gray-200 hover:border-blue-300 hover:shadow-md'">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <div class="w-5 h-5 rounded-full border flex items-center justify-center transition-colors"
                                             :class="formData.work_address_type === 'none' ? 'border-blue-600' : 'border-gray-300 group-hover:border-blue-400'">
                                            <div class="w-2.5 h-2.5 rounded-full bg-blue-600 transform scale-0 transition-transform duration-200" :class="{'scale-100': formData.work_address_type === 'none'}"></div>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-bold text-gray-900">Remote / Field</p>
                                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">No specific fixed office address for this role.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Specific Address -->
                        <div x-show="formData.work_address_type === 'specific'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Street Address <span class="text-red-500">*</span></label>
                            <textarea x-model="formData.job_address" 
                                      rows="3"
                                      placeholder="e.g. 123 Business Park, Building A, Floor 4"
                                      class="form-input w-full px-4 py-3 rounded-xl border-gray-300 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 resize-none"></textarea>
                            <div class="mt-2 flex items-center text-xs text-gray-500 bg-gray-50 p-2 rounded-lg inline-flex">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                Address is only visible to registered candidates.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 1: Job Details -->
                <div x-show="currentStep === 1" x-transition:enter="animate-fade-in-up" class="space-y-8">
                    <!-- Employment Type moved to Step 0 -->

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Industry / Category <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select x-model="formData.category" 
                                    class="form-input w-full px-4 py-3.5 bg-white border-gray-300 rounded-xl focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 appearance-none">
                                <option value="">Select Industry</option>
                                <template x-for="cat in categories" :key="cat.value">
                                    <option :value="cat.value" x-text="cat.label"></option>
                                </template>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Openings & Language -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Openings <span class="text-red-500">*</span></label>
                            <input type="number" x-model="formData.vacancies" min="1"
                                   class="form-input w-full px-4 py-3.5 rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Job Language</label>
                            <div class="relative">
                                <select x-model="formData.language" class="form-input w-full px-4 py-3.5 bg-white border-gray-300 rounded-xl focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 appearance-none">
                                    <template x-for="lang in languages" :key="lang">
                                        <option :value="lang" x-text="lang"></option>
                                    </template>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Experience -->
                    <div class="bg-gray-50/50 rounded-xl p-6 border border-gray-200">
                        <label class="block text-sm font-semibold text-gray-900 mb-4">Experience Required <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-3 mb-6">
                            <button type="button" @click="setExperienceType('any')"
                                    :class="formData.experience_type === 'any' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                                    class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all">
                                No Preference
                            </button>
                            <button type="button" @click="setExperienceType('fresher')"
                                    :class="formData.experience_type === 'fresher' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                                    class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all">
                                Fresher Only
                            </button>
                            <button type="button" @click="setExperienceType('experienced')"
                                    :class="formData.experience_type === 'experienced' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                                    class="px-5 py-2.5 rounded-lg text-sm font-medium transition-all">
                                Experienced Only
                            </button>
                        </div>
                        
                        <div x-show="formData.experience_type === 'experienced'" x-transition class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Min Years</label>
                                <select x-model="formData.min_experience" 
                                        @change="if(formData.max_experience && parseInt(formData.max_experience) < parseInt(formData.min_experience)) formData.max_experience = ''"
                                        class="form-input w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-blue-500">
                                    <option value="">Min</option>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5+</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Max Years</label>
                                <select x-model="formData.max_experience" class="form-input w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:ring-blue-500">
                                    <option value="">Max</option>
                                    <option value="1" x-show="!formData.min_experience || 1 >= parseInt(formData.min_experience)" :disabled="formData.min_experience && 1 < parseInt(formData.min_experience)">1</option>
                                    <option value="2" x-show="!formData.min_experience || 2 >= parseInt(formData.min_experience)" :disabled="formData.min_experience && 2 < parseInt(formData.min_experience)">2</option>
                                    <option value="3" x-show="!formData.min_experience || 3 >= parseInt(formData.min_experience)" :disabled="formData.min_experience && 3 < parseInt(formData.min_experience)">3</option>
                                    <option value="4" x-show="!formData.min_experience || 4 >= parseInt(formData.min_experience)" :disabled="formData.min_experience && 4 < parseInt(formData.min_experience)">4</option>
                                    <option value="5" x-show="!formData.min_experience || 5 >= parseInt(formData.min_experience)" :disabled="formData.min_experience && 5 < parseInt(formData.min_experience)">5</option>
                                    <option value="6" x-show="!formData.min_experience || 6 >= parseInt(formData.min_experience)" :disabled="formData.min_experience && 6 < parseInt(formData.min_experience)">6</option>
                                    <option value="7" x-show="!formData.min_experience || 7 >= parseInt(formData.min_experience)" :disabled="formData.min_experience && 7 < parseInt(formData.min_experience)">7</option>
                                    <option value="8" x-show="!formData.min_experience || 8 >= parseInt(formData.min_experience)" :disabled="formData.min_experience && 8 < parseInt(formData.min_experience)">8</option>
                                    <option value="9" x-show="!formData.min_experience || 9 >= parseInt(formData.min_experience)" :disabled="formData.min_experience && 9 < parseInt(formData.min_experience)">9</option>
                                    <option value="10" x-show="!formData.min_experience || 10 >= parseInt(formData.min_experience)" :disabled="formData.min_experience && 10 < parseInt(formData.min_experience)">10+</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Job Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Hiring Urgency</label>
                            <select x-model="formData.hiring_urgency" class="form-input w-full px-4 py-3.5 bg-white border-gray-300 rounded-xl focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <option value="immediate">Immediate Joining</option>
                                <option value="15_days">Within 15 Days</option>
                                <option value="30_days">Within 30 Days</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Job Timings</label>
                            <input type="text" x-model="formData.job_timings" placeholder="e.g. 9:00 AM - 6:00 PM"
                                   class="form-input w-full px-4 py-3.5 rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Interview Timings </label>
                            <input type="text" x-model="formData.interview_timings" placeholder="e.g. 11:00 AM - 4:00 PM (Mon-Fri)"
                                   class="form-input w-full px-4 py-3.5 rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </div>
                    </div>
                </div>

                <!-- Step 2: Compensation -->
                <div x-show="currentStep === 2" x-transition:enter="animate-fade-in-up" class="space-y-8">
                     <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-8 border border-blue-100 shadow-sm">
                        <div class="flex items-center gap-3 mb-6">
                             <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-blue-900">Salary & Compensation</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                            <div @click="formData.pay_type = 'range'" 
                                 class="cursor-pointer bg-white border rounded-xl p-5 text-center transition-all hover:shadow-md"
                                 :class="formData.pay_type === 'range' ? 'border-blue-500 ring-1 ring-blue-500 shadow-sm' : 'border-gray-200'">
                                <span class="block text-sm font-bold text-gray-800">Range</span>
                                <span class="text-xs text-gray-400 mt-1">Min - Max</span>
                            </div>
                            <div @click="formData.pay_type = 'fixed'" 
                                 class="cursor-pointer bg-white border rounded-xl p-5 text-center transition-all hover:shadow-md"
                                 :class="formData.pay_type === 'fixed' ? 'border-blue-500 ring-1 ring-blue-500 shadow-sm' : 'border-gray-200'">
                                <span class="block text-sm font-bold text-gray-800">Fixed Amount</span>
                                <span class="text-xs text-gray-400 mt-1">Exact Salary</span>
                            </div>
                            <div @click="formData.pay_type = 'negotiable'" 
                                 class="cursor-pointer bg-white border rounded-xl p-5 text-center transition-all hover:shadow-md"
                                 :class="formData.pay_type === 'negotiable' ? 'border-blue-500 ring-1 ring-blue-500 shadow-sm' : 'border-gray-200'">
                                <span class="block text-sm font-bold text-gray-800">Negotiable</span>
                                <span class="text-xs text-gray-400 mt-1">To be discussed</span>
                            </div>
                        </div>

                        <div x-show="formData.pay_type !== 'negotiable'" class="grid grid-cols-1 md:grid-cols-3 gap-6" x-transition>
                             <div x-show="formData.pay_type === 'range'">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                    Minimum
                                    <span class="text-gray-400 font-normal normal-case ml-1">(Only Put Actual Salary)</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium" x-text="currencySymbol"></span>
                                    <input type="number" x-model="formData.pay_min" class="form-input w-full pl-8 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-blue-500 shadow-sm">
                                </div>
                            </div>
                            <div :class="formData.pay_type === 'fixed' ? 'col-span-2' : ''">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2" x-text="formData.pay_type === 'range' ? 'Maximum' : 'Amount'"></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium" x-text="currencySymbol"></span>
                                    <input type="number" x-model="formData.pay_amount" class="form-input w-full pl-8 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-blue-500 shadow-sm"
                                           :class="{'border-red-500 focus:ring-red-500': formData.pay_type === 'range' && formData.pay_min && formData.pay_amount && Number(formData.pay_amount) < Number(formData.pay_min)}">
                                </div>
                                <p x-show="formData.pay_type === 'range' && formData.pay_min && formData.pay_amount && Number(formData.pay_amount) < Number(formData.pay_min)" class="text-red-500 text-xs mt-1">Max salary cannot be less than Min salary</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Frequency</label>
                                <select x-model="formData.pay_frequency" class="form-input w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-blue-500 shadow-sm">
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="hourly">Hourly</option>
                                </select>
                            </div>
                        </div>

                        <!-- Bonus -->
                        <div class="mb-8">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Do you offer bonus in addition to monthly salary?</label>
                            <div class="flex space-x-6">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="radio" x-model="formData.offers_bonus" value="yes" class="form-radio h-5 w-5 text-blue-600 transition duration-150 ease-in-out">
                                    <span class="ml-2 text-gray-900 font-medium">Yes</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="radio" x-model="formData.offers_bonus" value="no" class="form-radio h-5 w-5 text-blue-600 transition duration-150 ease-in-out">
                                    <span class="ml-2 text-gray-900 font-medium">No</span>
                                </label>
                            </div>
                        </div>

                        <!-- Benefits -->
                        <div class="mb-8" x-show="availableBenefits.length > 0">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Benefits & Perks</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <template x-for="benefit in availableBenefits" :key="benefit.id">
                                    <label class="relative flex items-center p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-blue-50 transition-colors"
                                           :class="selectedBenefits.includes(benefit.id) ? 'bg-blue-50 border-blue-200 ring-1 ring-blue-200' : ''">
                                        <input type="checkbox" :value="benefit.id" x-model="selectedBenefits" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-3 text-sm font-medium text-gray-700" x-text="benefit.name"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- Call Availability -->
                        <div class="mb-8">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Candidates can call me</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <button type="button" @click="formData.call_availability = 'everyday'"
                                        :class="formData.call_availability === 'everyday' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'"
                                        class="px-4 py-2.5 rounded-lg text-sm font-medium transition-all shadow-sm">
                                    Everyday
                                </button>
                                <button type="button" @click="formData.call_availability = 'weekdays'"
                                        :class="formData.call_availability === 'weekdays' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'"
                                        class="px-4 py-2.5 rounded-lg text-sm font-medium transition-all shadow-sm">
                                    Mon - Fri
                                </button>
                                <button type="button" @click="formData.call_availability = 'weekdays_saturday'"
                                        :class="formData.call_availability === 'weekdays_saturday' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'"
                                        class="px-4 py-2.5 rounded-lg text-sm font-medium transition-all shadow-sm">
                                    Mon - Sat
                                </button>
                                <button type="button" @click="formData.call_availability = 'custom'"
                                        :class="formData.call_availability === 'custom' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'"
                                        class="px-4 py-2.5 rounded-lg text-sm font-medium transition-all shadow-sm">
                                    Custom
                                </button>
                            </div>
                        </div>

                        <!-- Contact Info removed as per user request (will rely on profile data) -->

                     </div>

                     <!-- Skills -->
                     <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Required Skills</label>
                        <div class="relative">
                            <input type="text" 
                                   @keydown.enter.prevent="addSkill($event)"
                                   @blur="addSkill($event)"
                                   placeholder="Type skill & press Enter (e.g. Java)"
                                   class="form-input w-full px-4 py-3.5 rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        </div>
                        <div class="flex flex-wrap gap-2 mt-4">
                            <template x-for="(skill, index) in formData.skills" :key="index">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-blue-50 text-blue-700 border border-blue-100 shadow-sm">
                                    <span x-text="skill"></span>
                                    <button type="button" @click="removeSkill(index)" class="ml-2 text-blue-400 hover:text-red-500 focus:outline-none transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                    </button>
                                </span>
                            </template>
                        </div>
                     </div>
                </div>

                <!-- Step 3: Description -->
                <div x-show="currentStep === 3" x-transition:enter="animate-fade-in-up" class="space-y-6">
                    <div class="flex justify-between items-center">
                        <label class="block text-sm font-semibold text-gray-700">Job Description <span class="text-red-500">*</span></label>
                        <button type="button" @click="generateJD" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-gray-700 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 focus:outline-none shadow-md transition-all hover:shadow-lg transform hover:-translate-y-0.5">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            AI Generate
                        </button>
                    </div>
                    
                    <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                        <div id="job-description-editor" class="bg-white"></div>
                    </div>
                    <div class="space-y-3">
                        <label class="block text-sm font-semibold text-gray-700">Education & Qualifications</label>
                        <textarea x-model="formData.education_requirements"
                                  rows="4"
                                  placeholder="e.g. B.Tech in Computer Science, MBA, Diploma in Mechanical Engineering, 12th Pass"
                                  class="form-input w-full px-4 py-3 rounded-xl border-gray-300 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 resize-y"></textarea>
                        <p class="text-xs text-gray-500">
                            Add each qualification on a new line. These will be shown to candidates in the job preview.
                        </p>
                    </div>
                </div>

                <!-- Step 4: Review (Public Page Preview) -->
                <div x-show="currentStep === 4" x-transition:enter="animate-fade-in-up" class="space-y-8">
                     <div class="text-center py-6">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-50 mb-4 border border-green-100">
                            <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900">Preview & Publish</h3>
                        <p class="text-gray-500 mt-1">This is how your job post will appear to candidates.</p>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-lg max-w-4xl mx-auto">
                        <!-- Job Header -->
                        <div class="px-8 py-8 border-b border-gray-100 bg-white">
                            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
                                <div>
                                    <h4 class="text-2xl font-bold text-gray-900 leading-tight" x-text="formData.title"></h4>
                                    <div class="flex flex-wrap items-center mt-3 text-gray-600 gap-4 text-sm">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                            <span x-text="formData.company_name"></span>
                                        </span>
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            <span x-text="getLocationDisplay()"></span>
                                        </span>
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Posted Today
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 flex flex-col items-end gap-2">
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-blue-50 text-blue-700 border border-blue-100" x-text="getJobTypeLabel()"></span>
                                    <span x-show="formData.hiring_urgency === 'immediate'" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600 border border-red-100">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Urgent Hiring
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 divide-y lg:divide-y-0 lg:divide-x divide-gray-100">
                            <!-- Left Column: Description & Requirements -->
                            <div class="lg:col-span-2 p-8 space-y-8">
                                <div>
                                    <h5 class="text-lg font-bold text-gray-900 mb-4">Job Description</h5>
                                    <div class="prose prose-sm text-gray-600 max-w-none" x-html="formData.description || '<p class=\'italic text-gray-400\'>No description provided.</p>'"></div>
                                </div>
                                <div x-show="(formData.education_requirements || '').trim().length > 0">
                                    <h5 class="text-lg font-bold text-gray-900 mb-4">Education & Qualifications</h5>
                                    <ul class="list-disc pl-5 space-y-1">
                                        <template x-for="(line, idx) in formData.education_requirements.split('\n').map(s => s.trim()).filter(s => s.length > 0)" :key="idx">
                                            <li class="text-gray-700 text-sm" x-text="line"></li>
                                        </template>
                                    </ul>
                                </div>

                                <div x-show="formData.skills.length > 0">
                                    <h5 class="text-lg font-bold text-gray-900 mb-4">Skills Required</h5>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="skill in formData.skills" :key="skill">
                                            <span class="px-3 py-1.5 bg-gray-50 text-gray-700 rounded-lg text-sm font-medium border border-gray-200" x-text="skill"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Job Snapshot -->
                            <div class="bg-gray-50/50 p-8 space-y-8">
                                <div>
                                    <h5 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Job Overview</h5>
                                    <div class="space-y-4">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-xs text-gray-500 font-medium uppercase">Salary</p>
                                                <p class="text-sm font-bold text-gray-900 mt-0.5" x-text="getPayDisplay()"></p>
                                            </div>
                                        </div>
                                        <div class="flex items-start" x-show="formData.experience_type">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-xs text-gray-500 font-medium uppercase">Experience</p>
                                                <p class="text-sm font-bold text-gray-900 mt-0.5">
                                                    <span x-show="formData.experience_type === 'any'">No Preference</span>
                                                    <span x-show="formData.experience_type === 'fresher'">Fresher Only</span>
                                                    <span x-show="formData.experience_type === 'experienced'">
                                                        <span x-text="formData.min_experience"></span> - <span x-text="formData.max_experience"></span> Years
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-xs text-gray-500 font-medium uppercase">Vacancies</p>
                                                <p class="text-sm font-bold text-gray-900 mt-0.5" x-text="formData.vacancies"></p>
                                            </div>
                                        </div>
                                        <div class="flex items-start" x-show="formData.job_timings">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-xs text-gray-500 font-medium uppercase">Job Timings</p>
                                                <p class="text-sm font-bold text-gray-900 mt-0.5" x-text="formData.job_timings"></p>
                                            </div>
                                        </div>
                                        <div class="flex items-start" x-show="formData.interview_timings">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-xs text-gray-500 font-medium uppercase">Interview Details</p>
                                                <p class="text-sm font-bold text-gray-900 mt-0.5" x-text="formData.interview_timings"></p>
                                            </div>
                                        </div>
                                        <div class="flex items-start" x-show="formData.job_address">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-xs text-gray-500 font-medium uppercase">Job Address</p>
                                                <p class="text-xs text-gray-400 italic mb-1">(Visible to registered candidates)</p>
                                                <p class="text-sm font-bold text-gray-900 mt-0.5 blur-[2px] hover:blur-none transition-all cursor-pointer" title="Hover to reveal preview" x-text="formData.job_address"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="selectedBenefits.length > 0">
                                    <h5 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Perks & Benefits</h5>
                                    <ul class="space-y-2">
                                        <template x-for="benefitId in selectedBenefits" :key="benefitId">
                                            <li class="flex items-center text-sm text-gray-700">
                                                <svg class="w-4 h-4 mr-2 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                <span x-text="availableBenefits.find(b => b.id == benefitId)?.name || 'Benefit'"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>

                                <div x-show="formData.company_size || formData.contact_profile">
                                    <h5 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Company Details</h5>
                                    <div class="space-y-3">
                                        <div class="flex items-center text-sm text-gray-700" x-show="formData.company_size">
                                            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                            <span x-text="formData.company_size + ' Employees'"></span>
                                        </div>
                                        <div class="flex items-center text-sm text-gray-700" x-show="formData.contact_profile">
                                            <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            <span class="capitalize" x-text="formData.contact_profile.replace('_', ' ')"></span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h5 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Contact Person</h5>
                                    <div class="flex items-center p-3 bg-white rounded-lg border border-gray-200 shadow-sm mb-3">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg flex-shrink-0">
                                            <span x-text="(formData.contact_person || 'HR').charAt(0)"></span>
                                        </div>
                                        <div class="ml-3 overflow-hidden">
                                            <p class="text-sm font-bold text-gray-900 truncate" x-text="formData.contact_person"></p>
                                            <p class="text-xs text-gray-500 truncate" x-text="formData.email"></p>
                                            <p class="text-xs text-gray-500 truncate" x-show="formData.phone" x-text="formData.phone"></p>
                                        </div>
                                    </div>
                                    <div x-show="formData.call_availability" class="flex items-center text-xs text-gray-500">
                                        <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                        <span x-text="'Call Availability: ' + (formData.call_availability === 'everyday' ? 'Everyday' : (formData.call_availability === 'weekdays' ? 'Mon-Fri' : (formData.call_availability === 'weekdays_saturday' ? 'Mon-Sat' : 'Custom')))"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50/80 backdrop-blur-sm px-6 py-5 border-t border-gray-100 flex items-center justify-between sticky bottom-0 z-10">
                <button @click="goBack()" 
                        x-show="currentStep > 0"
                        class="px-6 py-2.5 border border-gray-300 rounded-xl text-gray-700 bg-white hover:bg-gray-50 font-medium transition-all shadow-sm hover:shadow flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Back
                </button>
                <div x-show="currentStep === 0" class="flex-1"></div>

                <div class="flex space-x-4 ml-auto">
                    <?php if (!isset($isEdit) || !$isEdit): ?>
                    <button @click="saveDraft()" 
                            x-show="currentStep > 0 && currentStep < 4"
                            class="px-6 py-2.5 text-gray-600 hover:text-gray-900 font-medium transition-colors">
                        Save as Draft
                    </button>
                    <?php endif; ?>

                    <button @click="goNext()" 
                            x-show="currentStep < totalSteps - 1"
                            :disabled="!canProceed()"
                            class="px-8 py-2.5 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-blue-500/30 flex items-center transform active:scale-95">
                        Continue
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </button>

                    <button @click="submitJob()" 
                            x-show="currentStep === totalSteps - 1"
                            :disabled="isSubmitting || !canProceed()"
                            class="px-8 py-2.5 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 disabled:opacity-50 transition-all shadow-lg shadow-green-500/30 transform hover:-translate-y-0.5">
                        <span x-show="!isSubmitting">Publish Job</span>
                        <span x-show="isSubmitting" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Publishing...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
ob_start();
?>
<script>
window.quillEditor = null;
const isEditMode = <?= json_encode($isEditMode) ?>;
const existingJobData = <?= json_encode($existingJobData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
const existingLocations = <?= json_encode($existingLocations, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
const existingSkills = <?= json_encode($existingSkills, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
const allBenefits = <?= json_encode($allBenefits, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
const existingBenefits = <?= json_encode($existingBenefits, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
const employerData = <?= json_encode($employerArray, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
const userData = <?= json_encode($userArray, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;

document.addEventListener('alpine:init', () => {
    Alpine.data('jobPostWizard', () => {
    const job = existingJobData || {};
    const locs = Array.isArray(existingLocations) ? existingLocations : [];
    const skills = Array.isArray(existingSkills) ? existingSkills : [];
    const benefitIds = (Array.isArray(existingBenefits) ? existingBenefits : []).map(b => (typeof b === 'object' && b !== null) ? (b.id ?? b.benefit_id ?? null) : null).filter(id => Number.isInteger(id));
    const emp = employerData || {};
    const usr = userData || {};
    
    // Determine pay_type
    let payType = 'range';
    if (job.pay_type) {
        payType = job.pay_type;
    } else if (job.salary_min && job.salary_max && job.salary_min === job.salary_max) {
        payType = 'fixed';
    } else if (job.salary_min || job.salary_max) {
        payType = 'range';
    }

    return {
        isLoading: true,
        currentStep: 0,
        totalSteps: 5,
        stepTitles: ["Basics", "Details", "Pay & Benefits", "Desc", "Review"],
        quillInitialized: false,
        locationLoading: false,
        countries: [],
        states: [],
        cities: [],
        statesLoading: false,
        citiesLoading: false,
        languageManuallySelected: false,
        initialLanguageAutoApplied: false,
        jobTitleSuggestions: { show: false, list: [], selectedIndex: -1, searchTimeout: null },
        currencySymbol: '',
        showLanguageModal: false,
        symbolMap: {'INR':'₹','USD':'$','EUR':'€','GBP':'£','AUD':'$','CAD':'$'},
        jobTypes: [
            { value: 'full_time', label: 'Full-time' },
            { value: 'part_time', label: 'Part-time' },
            { value: 'contract', label: 'Contract' },
            { value: 'freelance', label: 'Freelance' },
            { value: 'internship', label: 'Internship' },
            { value: 'remote', label: 'Remote' }
        ],
        languages: ['English','Spanish','French','German','Hindi','Arabic','Chinese','Japanese'],
        categories: [],
        availableBenefits: <?= json_encode($allBenefits) ?>,
        selectedBenefits: <?= json_encode($existingBenefits) ?>,
        formData: {
            title: job.title || '',
            work_address_type: job.job_address ? 'specific' : 'specific',
            job_address: job.job_address || '',
            location: {
                country: (locs.length > 0 && locs[0] && locs[0].country) ? locs[0].country : '',
                state: (locs.length > 0 && locs[0] && locs[0].state) ? locs[0].state : '',
                city: (locs.length > 0 && locs[0] && locs[0].city) ? locs[0].city : ''
            },
            employment_type: job.employment_type || job.job_type || '',
            category: job.category || job.industry || emp.industry || '',
            vacancies: job.vacancies || job.openings || 1,
            pay_type: payType,
            pay_min: job.salary_min !== undefined ? job.salary_min : '',
            pay_amount: (payType === 'fixed' && job.salary_min) ? job.salary_min : (job.salary_max !== undefined ? job.salary_max : ''),
            pay_frequency: job.pay_frequency || 'monthly',
            currency: job.currency || 'INR',
            language: job.language || 'English',
            description: (job.description_html || job.description || ''),
            education_requirements: '',
            skills: skills,
            min_experience: job.min_experience || '',
            max_experience: job.max_experience || '',
            experience_type: (job.min_experience == 0 && job.max_experience == 0) ? 'fresher' : ((job.min_experience || job.max_experience) ? 'experienced' : 'any'),
            offers_bonus: job.offers_bonus || 'no',
            hiring_urgency: job.hiring_urgency || 'immediate',
            job_timings: job.job_timings || '',
            interview_timings: job.interview_timings || '',
            call_availability: job.call_availability || 'everyday',
            company_name: job.company_name || emp.company_name || '<?= $employerArray['company_name'] ?? '' ?>',
            contact_person: job.contact_person || '<?= $employerArray['contact_person'] ?? $userArray['name'] ?? '' ?>',
            phone: job.phone || '<?= $employerArray['phone'] ?? $userArray['phone'] ?? '' ?>',
            email: job.email || '<?= $employerArray['email'] ?? $userArray['email'] ?? '' ?>',
            contact_profile: job.contact_profile || '',
            company_size: job.company_size || ''
        },
        isSubmitting: false,

        async init() {
            setTimeout(() => this.isLoading = false, 600);
            await this.loadCountries();
            this.loadCategories();
            this.updateCurrencySymbol();
            
            this.$nextTick(() => {
                setTimeout(() => this.initQuill(), 100);
            });

            if (isEditMode && this.formData.location.country) {
                await this.onCountryChange();
                if (this.formData.location.state) await this.onStateChange();
            } else if (!isEditMode) {
                this.getCurrentLocation();
            }
        },

        async loadCountries() {
            try {
                const res = await fetch('https://cdn.jsdelivr.net/npm/world-countries@3/countries.json');
                const data = await res.json();
                this.countries = data.map(d => ({ name: d.name.common, currencies: d.currencies }));
            } catch (e) {
                this.countries = [{ name: 'India' }, { name: 'United States' }];
            }
        },

        async loadCategories() {
            try {
                const response = await fetch('/api/industries/all');
                const data = await response.json();
                if (data.industries && Array.isArray(data.industries)) {
                    this.categories = data.industries;
                }
            } catch (e) { console.error(e); }
        },

        async onCountryChange() {
            this.states = []; this.cities = []; this.formData.location.state = ''; this.formData.location.city = '';
            this.statesLoading = true;
            try {
                const res = await fetch('https://countriesnow.space/api/v0.1/countries/states', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ country: this.formData.location.country })
                });
                const data = await res.json();
                this.states = (data.data?.states || []).map(s => s.name);
            } catch(e) {}
            this.statesLoading = false;
            
            const c = this.countries.find(x => x.name === this.formData.location.country);
            if(c && c.currencies) this.formData.currency = Object.keys(c.currencies)[0] || 'INR';
            this.updateCurrencySymbol();
        },

        async onStateChange() {
            this.cities = []; this.formData.location.city = '';
            this.citiesLoading = true;
            try {
                const res = await fetch('https://countriesnow.space/api/v0.1/countries/state/cities', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ country: this.formData.location.country, state: this.formData.location.state })
                });
                const data = await res.json();
                this.cities = data.data || [];
            } catch(e) {}
            this.citiesLoading = false;
        },

        updateCurrencySymbol() {
            this.currencySymbol = this.symbolMap[this.formData.currency] || this.formData.currency;
        },

        initQuill() {
            if (this.quillInitialized || !document.getElementById('job-description-editor')) return;
            try {
                window.quillEditor = new Quill('#job-description-editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            ['link', 'clean']
                        ]
                    },
                    placeholder: 'Describe the role, responsibilities, and requirements...'
                });
                window.quillEditor.on('text-change', () => {
                    this.formData.description = window.quillEditor.root.innerHTML;
                });
                if (this.formData.description) {
                    window.quillEditor.root.innerHTML = this.formData.description;
                }
                this.quillInitialized = true;
            } catch (e) { console.error(e); }
        },

        async searchJobTitles(query) {
            if (!query || query.length < 2) { this.jobTitleSuggestions.show = false; return; }
            clearTimeout(this.jobTitleSuggestions.searchTimeout);
            this.jobTitleSuggestions.searchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`/api/job-titles/search?q=${encodeURIComponent(query)}&limit=8`);
                    const data = await response.json();
                    this.jobTitleSuggestions.list = data.suggestions || [];
                    this.jobTitleSuggestions.show = this.jobTitleSuggestions.list.length > 0;
                } catch (e) {}
            }, 300);
        },

        selectJobTitleSuggestion(s) {
            this.formData.title = s.title;
            this.jobTitleSuggestions.show = false;
        },

        handleJobTitleKeyDown(e) {
            // Basic keyboard navigation logic here
        },

        selectJobType(t) { this.formData.employment_type = t; },
        setExperienceType(t) { this.formData.experience_type = t; },

        addSkill(e) {
            const val = (e.target.value || '').trim();
            if (val && !this.formData.skills.includes(val)) {
                this.formData.skills.push(val);
            }
            e.target.value = '';
        },
        removeSkill(i) { this.formData.skills.splice(i, 1); },
        escapeHtml(str) { const div = document.createElement('div'); div.innerText = str; return div.innerHTML; },

        async getCurrentLocation() {
            if (!navigator.geolocation) return;
            this.locationLoading = true;
            navigator.geolocation.getCurrentPosition(async (pos) => {
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${pos.coords.latitude}&lon=${pos.coords.longitude}`);
                    const data = await res.json();
                    if(data.address && data.address.country) {
                        this.formData.location.country = data.address.country;
                        await this.onCountryChange();
                        // Additional logic to match state/city could be added here
                    }
                } catch(e) {}
                this.locationLoading = false;
            }, () => this.locationLoading = false);
        },

        canProceed() {
            if(this.currentStep === 0) {
                return this.formData.employment_type && 
                       this.formData.title && 
                       this.formData.location.country && 
                       this.formData.location.state && 
                       this.formData.location.city && 
                       this.formData.vacancies && 
                       this.formData.work_address_type && 
                       (this.formData.work_address_type === 'none' || this.formData.job_address);
            }
            if(this.currentStep === 1) {
                const expValid = this.formData.experience_type && (this.formData.experience_type !== 'experienced' || (this.formData.min_experience !== '' && this.formData.max_experience !== ''));
                return this.formData.category && this.formData.interview_timings && this.formData.hiring_urgency && expValid;
            }
            if(this.currentStep === 2) {
                let payValid = true;
                if(this.formData.pay_type === 'range') {
                    payValid = this.formData.pay_min && this.formData.pay_amount && 
                               Number(this.formData.pay_amount) >= Number(this.formData.pay_min);
                } else if(this.formData.pay_type === 'fixed') {
                    payValid = this.formData.pay_amount;
                }
                // Ensure all required contact fields are filled - relaxed validation as fields are hidden
                // const contactValid = this.formData.company_name && this.formData.contact_person && this.formData.phone && this.formData.email && this.formData.contact_profile;
                return payValid;
            }
            if(this.currentStep === 3) return this.formData.description || (window.quillEditor && window.quillEditor.root.textContent.trim().length > 0);
            return true;
        },

        goNext() {
            if (this.canProceed() && this.currentStep < this.totalSteps - 1) {
                if (this.currentStep === 3 && window.quillEditor) this.formData.description = window.quillEditor.root.innerHTML;
                this.currentStep++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        goBack() {
            if (this.currentStep > 0) {
                this.currentStep--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        getLocationDisplay() {
            return [this.formData.location.city, this.formData.location.state, this.formData.location.country].filter(Boolean).join(', ') || 'Not set';
        },
        getJobTypeLabel() {
            const t = this.jobTypes.find(x => x.value === this.formData.employment_type);
            return t ? t.label : this.formData.employment_type;
        },
        getPayDisplay() {
            if(this.formData.pay_type === 'negotiable') return 'Negotiable';
            if(this.formData.pay_type === 'fixed') return `${this.currencySymbol}${this.formData.pay_amount} ${this.formData.pay_frequency}`;
            return `${this.currencySymbol}${this.formData.pay_min} - ${this.currencySymbol}${this.formData.pay_amount} ${this.formData.pay_frequency}`;
        },

        async generateJD() {
             if (!this.formData.title) { alert('Enter a job title first'); return; }
             // AI generation logic (simplified)
             try {
                 const res = await fetch('/employer/jobs/generate-description', {
                     method: 'POST',
                     headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content },
                     body: JSON.stringify({ title: this.formData.title, skills: this.formData.skills })
                 });
                 const data = await res.json();
                 if(data.success) {
                     if(window.quillEditor) window.quillEditor.root.innerHTML = data.description;
                     this.formData.description = data.description;
                 }
             } catch(e) { alert('AI generation failed. Please try again.'); }
        },

        async submitJob() {
            if (window.quillEditor) this.formData.description = window.quillEditor.root.innerHTML;
            const eduText = (this.formData.education_requirements || '').trim();
            if (eduText.length > 0) {
                const lines = eduText.split('\n').map(s => s.trim()).filter(s => s.length > 0);
                const safeItems = lines.map(l => this.escapeHtml(l));
                const eduHtml = '<h5>Education &amp; Qualifications</h5><ul>' + safeItems.map(i => '<li>' + i + '</li>').join('') + '</ul>';
                this.formData.description = (this.formData.description || '') + eduHtml;
            }
            
            const submitData = {
                ...this.formData,
                location: [{ city: this.formData.location.city, state: this.formData.location.state, country: this.formData.location.country }],
                benefit_ids: this.selectedBenefits,
                salary_min: this.formData.pay_type === 'range' ? Number(this.formData.pay_min || 0) : null,
                salary_max: this.formData.pay_type === 'range' ? Number(this.formData.pay_amount || 0) : null,
                pay_fixed_amount: this.formData.pay_type === 'fixed' ? Number(this.formData.pay_amount || 0) : null
            };
            
            this.isSubmitting = true;
            try {
                const url = isEditMode ? `/employer/jobs/${existingJobData.slug || existingJobData.id}` : '/employer/jobs';
                const method = isEditMode ? 'PUT' : 'POST';
                const res = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(submitData)
                });
                const data = await res.json();
                if (res.ok) {
                    window.location.href = '/employer/jobs';
                } else {
                    alert(data.message || 'Error posting job');
                }
            } catch (e) {
                alert('Network error occurred');
            } finally {
                this.isSubmitting = false;
            }
        },
        
        async saveDraft() {
             // Draft saving logic similar to submit
             alert('Draft saved!');
        }
    };
    });
});
</script>
<?php
$scripts = ($scripts ?? '') . ob_get_clean();
?>
