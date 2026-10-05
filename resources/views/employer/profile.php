<?php

/**
 * @var string $title
 * @var \App\Models\Employer $employer
 * @var \App\Models\User $user
 * @var array $address
 * @var array $kycDocuments
 */
$uploadedDocs = [];
foreach (($kycDocuments ?? []) as $doc) {
    $type = $doc['doc_type'] ?? '';
    if ($type !== '' && !isset($uploadedDocs[$type])) {
        $uploadedDocs[$type] = $doc;
    }
}
$basicInfoComplete = (bool)($basicInfoComplete ?? (method_exists($employer, 'isBasicInfoComplete') && $employer->isBasicInfoComplete()));
$addressComplete = (bool)($addressComplete ?? (method_exists($employer, 'isAddressComplete') && $employer->isAddressComplete()));
?>

<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">My Profile</h1>
    <?php
    $needsCompletion = method_exists($employer, 'isProfileComplete') && !$employer->isProfileComplete();
    $kycStatus = $employer->kyc_status ?? '';
    $submitted = !empty($_GET['submitted']);
    if ($needsCompletion):
        ?>
        <div class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-md p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 8v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Please complete your profile to enable job posting.</span>
            </div>
        </div>
    <?php elseif ($submitted || ($kycStatus !== 'approved' && !$needsCompletion)): ?>
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-md p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7 20h10a2 2 0 002-2V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Thank you for completing your profile. Your account is under review.</span>
            </div>
        </div>
    <?php endif; ?>

    <div x-data="profileForm" x-init="init()" class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center justify-center gap-6">
                <?php if ($basicInfoComplete): ?>
                <div class="flex items-center gap-2 rounded-lg px-2 py-1 bg-emerald-50">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center bg-emerald-600 text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="text-sm font-medium text-emerald-700">Basic Info Saved</span>
                </div>
                <?php else: ?>
                <button type="button" @click="goToStep(1)" class="flex items-center gap-2 rounded-lg px-2 py-1 hover:bg-gray-50 transition">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center"
                         :class="(currentStep===1)?'bg-primary text-white':((currentStep>1||validateStep1())?'bg-emerald-600 text-white':'bg-gray-100 text-gray-500')">
                        <template x-if="currentStep>1 || validateStep1()">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </template>
                        <template x-if="!(currentStep>1 || validateStep1())">
                            <span>1</span>
                        </template>
                    </div>
                    <span class="text-sm font-medium" :class="currentStep===1?'text-primary-600':'text-gray-600'">Basic Info</span>
                </button>
                <?php endif; ?>
                <div class="h-0.5 w-10"
                     :class="(currentStep>1||validateStep1())?'bg-emerald-200':'bg-gray-200'"></div>
                <button type="button" @click="goToStep(2)" class="flex items-center gap-2 rounded-lg px-2 py-1 hover:bg-gray-50 transition">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center"
                         :class="(currentStep===2)?'bg-primary text-white':((currentStep>2||validateStep2())?'bg-emerald-600 text-white':'bg-gray-100 text-gray-500')">
                        <template x-if="currentStep>2 || validateStep2()">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </template>
                        <template x-if="!(currentStep>2 || validateStep2())">
                            <span>2</span>
                        </template>
                    </div>
                    <span class="text-sm font-medium" :class="currentStep===2?'text-primary-600':'text-gray-600'">Address</span>
                </button>
                <div class="h-0.5 w-10"
                     :class="(currentStep>2||validateStep2())?'bg-emerald-200':'bg-gray-200'"></div>
                <button type="button" @click="goToStep(3)" class="flex items-center gap-2 rounded-lg px-2 py-1 hover:bg-gray-50 transition">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center"
                         :class="(currentStep===3)?'bg-primary text-white':'bg-gray-100 text-gray-500'">
                        <span>3</span>
                    </div>
                    <span class="text-sm font-medium" :class="currentStep===3?'text-primary-600':'text-gray-600'">Documents</span>
                </button>
            </div>
        </div>
        <?php if ($basicInfoComplete): ?>
        <div class="bg-white border border-emerald-200 rounded-xl p-4" x-show="currentStep !== 1">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Registration details are already saved</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        <?= htmlspecialchars($employer->company_name ?? 'Your company', ENT_QUOTES) ?>
                        <?php if (!empty($employer->industry)): ?> · <?= htmlspecialchars($employer->industry, ENT_QUOTES) ?><?php endif; ?>
                        <?php if (!empty($employer->size)): ?> · <?= htmlspecialchars($employer->size, ENT_QUOTES) ?> employees<?php endif; ?>
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Continue with address and verification documents only.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <!-- Company Information -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 hover:shadow transition-shadow space-y-4" x-show="currentStep === 1">
            <div class="flex items-center gap-2 mb-4">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-briefcase h-5 w-5 text-primary"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path><rect width="20" height="14" x="2" y="6" rx="2"></rect></svg>                <h2 class="text-xl font-semibold text-gray-900">Company Information</h2>
            </div>
            
            <form @submit.prevent="updateProfile" class="space-y-4">
                <input type="hidden" name="_token" :value="csrfToken">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Register As *</label>
                        <select x-model="formData.register_as"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537] hover:border-[#f05537]/50 transition">
                            <option value="company">Company / Business</option>
                            <option value="individual">Individual / Proprietor</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Company Name *</label>
                        <input type="text" 
                               x-model="formData.company_name"
                               required
                               placeholder="Enter company name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537] hover:border-[#f05537]/50 transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Website</label>
                        <input type="url" 
                               x-model="formData.website"
                               placeholder="https://example.com"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537] hover:border-[#f05537]/50 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tax ID / GSTIN</label>
                        <input type="text" 
                               x-model="formData.tax_id"
                               @input="validateGstin()"
                               placeholder="15-digit GSTIN (India)"
                               :class="gstinError ? 'border-red-500' : ''"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537] hover:border-[#f05537]/50 transition">
                        <p x-show="gstinError" class="text-[10px] text-red-500 mt-1" x-text="gstinError"></p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Company Description</label>
                    <textarea x-model="formData.description"
                              rows="4"
                              placeholder="Brief description of your company..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537] hover:border-[#f05537]/50 transition"></textarea>
                </div>

                <template x-if="formData.register_as === 'company'">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Company Type *</label>
                            <select x-model="formData.company_type"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537] hover:border-[#f05537]/50 transition">
                                <option value="">Select Company Type</option>
                                <option value="proprietorship">Proprietorship</option>
                                <option value="partnership">Partnership</option>
                                <option value="private_limited">Private Limited</option>
                                <option value="public_limited">Public Limited</option>
                                <option value="llp">Limited Liability Partnership (LLP)</option>
                                <option value="opc">One Person Company (OPC)</option>
                                <option value="government">Government / PSU</option>
                                <option value="non_profit">Non-Profit (NGO / Trust)</option>
                                <option value="startup">Startup</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Industry Type *</label>
                            <select x-model="formData.industry"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537] hover:border-[#f05537]/50 transition">
                                <option value="">Select Industry</option>
                                <option value="IT/Software">IT/Software</option>
                                <option value="Finance">Finance</option>
                                <option value="Healthcare">Healthcare</option>
                                <option value="Education">Education</option>
                                <option value="Manufacturing">Manufacturing</option>
                                <option value="Retail">Retail</option>
                                <option value="Real Estate">Real Estate</option>
                                <option value="Hospitality">Hospitality</option>
                                <option value="Other">Other</option>
                            </select>
                            <div x-show="formData.industry === 'Other'" class="mt-2">
                                <input type="text"
                                       x-model="formData.industry_custom"
                                       placeholder="Enter your industry"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537] hover:border-[#f05537]/50 transition">
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="formData.register_as === 'individual'">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Profession Type *</label>
                            <select x-model="formData.profession_type"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537] hover:border-[#f05537]/50 transition">
                                <option value="">Select Profession Type</option>
                                <option>HR Consultant</option>
                                <option>Freelancer Recruiter</option>
                                <option>Staffing Partner</option>
                                <option>Career Consultant</option>
                                <option>Trainer</option>
                                <option>Placement Consultant</option>
                                <option>Business Owner</option>
                                <option>Recruitment Freelancer</option>
                                <option>Hiring Partner</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Service Category *</label>
                            <input type="text" 
                                   x-model="formData.service_category"
                                   required
                                   placeholder="e.g. Recruitment, Training"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537] hover:border-[#f05537]/50 transition">
                        </div>
                    </div>
                </template>

                <!-- Logo Upload -->
                <div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Company Size *</label>
                            <select x-model="formData.company_size"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537] hover:border-[#f05537]/50 transition">
                                <option value="">Select size</option>
                                <option value="1-10">1-10 employees</option>
                                <option value="11-50">11-50 employees</option>
                                <option value="51-200">51-200 employees</option>
                                <option value="201-500">201-500 employees</option>
                                <option value="501-1000">501-1000 employees</option>
                                <option value="1001+">1001+ employees</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Company Logo</label>
                            <div class="flex items-center space-x-4">
                                <?php if (!empty($employer->logo_url)): ?>
                                    <img src="<?= htmlspecialchars($employer->logo_url) ?>" 
                                         alt="Company Logo" 
                                         class="h-20 w-20 object-cover rounded-md">
                                <?php endif; ?>
                                <input type="file" 
                                       name="logo"
                                       accept="image/*"
                                       @change="handleLogoUpload"
                                       class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537] hover:border-[#f05537]/50 transition">
                            </div>
                        </div>
                    </div>
                </div>
          
                
                <!-- Contact Information (inside same card) -->
                <div class="flex items-center gap-2">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail h-5 w-5 text-primary"><rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>                    <h3 class="text-lg font-semibold text-gray-900">Contact Information</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-start">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" 
                               x-model="formData.email"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Number *</label>
                        <div class="flex items-center gap-2">
                            <select x-model="selectedPhoneCountryCode" @change="onPhoneCountryChange()" class="w-28 px-3 py-2 border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-[#f05537]">
                                <template x-for="c in countries" :key="c.code">
                                    <option :value="c.code" x-text="c.code + ' ' + c.phone"></option>
                                </template>
                            </select>
                            <input type="tel" 
                                   x-model="formData.phone_local"
                                   @input="formData.phone_local = formData.phone_local.replace(/\D+/g, '').slice(0, 10); validateIndianMobile()"
                                   placeholder="Mobile number"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537]">
                        </div>
                        <p x-show="phoneError" class="text-[10px] text-red-500 mt-1" x-text="phoneError"></p>
                        <p class="text-xs text-gray-500 mt-1">Your phone is stored with the selected country dial code.</p>
                    </div>
                </div>
                
            </form>
        </div>

        <!-- Contact Information removed (now inside Step 1 card) -->

        <!-- Address Information -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 hover:shadow transition-shadow" x-show="currentStep === 2">
            <div class="flex items-center gap-2 mb-4">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users h-5 w-5 flex-shrink-0 text-white/70 group-hover:text-white"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>                <h2 class="text-xl font-semibold text-gray-900">Address</h2>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Country *</label>
                    <div class="relative">
                        <span x-show="flagEmoji"
                              x-text="flagEmoji"
                              :title="formData.country ? (formData.country + ' (' + phonePrefix + ')') : 'Select country'"
                              class="absolute left-3 top-1/2 -translate-y-1/2 text-lg leading-none"></span>
                        <select x-model="selectedCountryCode"
                                @change="onCountryChange()"
                                required
                                :title="formData.country ? (formData.country + ' (' + phonePrefix + ')') : 'Select country'"
                                class="w-full pl-12 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537] bg-white hover:border-[#f05537]/50 transition">
                            <option value="">Select country</option>
                            <template x-for="c in countries" :key="c.code">
                            <option :value="c.code" x-text="countryFlag(c.code) + ' ' + c.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">State *</label>
                        <input type="text" 
                               x-model="formData.address.state"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537] hover:border-[#f05537]/50 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">City *</label>
                        <input type="text" 
                               x-model="formData.address.city"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537] hover:border-[#f05537]/50 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Postal Code *</label>
                    <input type="text" 
                           x-model="formData.address.postal_code"
                           @input="formData.address.postal_code = formData.address.postal_code.replace(/\D+/g, '').slice(0, 6); schedulePinLookup()"
                           maxlength="6"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537] hover:border-[#f05537]/50 transition">
                    <p x-show="pinCodeError" class="text-[10px] text-red-500 mt-1" x-text="pinCodeError"></p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Street Address *</label>
                    <textarea x-model="formData.address.street"
                              rows="2"
                              required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#f05537] hover:border-[#f05537]/50 transition"></textarea>
                </div>

                <div class="space-y-3">
                    <button type="button" @click="useMyLocation()" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Use my location
                    </button>
                    <div id="map" class="w-full h-48 rounded-lg border border-gray-200"></div>
                    <p class="text-xs text-gray-500">Drag the marker to your exact location. Address fields update automatically.</p>
                    <p x-show="locationNotice" x-text="locationNotice" class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-3 py-2"></p>
                </div>
            </div>
        </div>

        <!-- Document Verifications -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 hover:shadow transition-shadow" x-show="currentStep === 3">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7 20h10a2 2 0 002-2V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <h2 class="text-xl font-semibold text-gray-900">Document Verification</h2>
            </div>
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Business License -->
                    <label class="rounded-lg border-2 border-dashed border-gray-300 p-4 cursor-pointer flex items-center gap-3 min-h-24 hover:border-[#f05537] hover:bg-orange-50 transition"
                           :class="documents.business_license ? 'border-emerald-300 bg-emerald-50' : ''">
                        <span class="w-11 h-11 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 6V5a2 2 0 012-2h2a2 2 0 012 2v1m-9 0h10a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2zm3 6h6"/></svg>
                        </span>
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-gray-800">Business License <span class="text-red-500">*</span> <span class="text-xs font-normal text-gray-400">Max 2 MB</span></div>
                            <div class="text-xs text-gray-500" x-show="!documents.business_license">Upload PDF, JPG or PNG</div>
                            <div class="text-xs text-emerald-700 font-medium" x-show="documents.business_license" x-text="documents.business_license?.name"></div>
                            <?php if (!empty($uploadedDocs['business_license'])): ?>
                                <div class="text-xs text-emerald-700 font-medium" x-show="!documents.business_license">
                                    Uploaded: <?= htmlspecialchars($uploadedDocs['business_license']['file_name'] ?? 'Business license', ENT_QUOTES) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V8m0 0l-3 3m3-3l3 3M4 16.5V18a2 2 0 002 2h12a2 2 0 002-2v-1.5"/></svg>
                        <input type="file" x-ref="doc_business_license" accept=".pdf,.jpg,.jpeg,.png" @change="handleDocSelected('business_license', $event)" class="hidden">
                    </label>

                    <!-- Tax ID / GST -->
                    <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                        <div class="flex items-center gap-2 text-gray-800 font-semibold">
                            <span class="w-9 h-9 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14h6M9 10h6M8 21h8l1-1 1 1 1-1V5a2 2 0 00-2-2H7a2 2 0 00-2 2v15l1 1 1-1 1 1z"/></svg>
                            </span>
                            <span>Tax ID / GST Certificate <span class="text-red-500">*</span> <span class="text-xs text-gray-400">Max 2 MB</span></span>
                        </div>
                        <input type="text" x-model="formData.tax_id" @input="validateGstin()" placeholder="GST Number / Tax ID" class="w-full px-3 py-2 border border-gray-300 rounded-md" :class="gstinError ? 'border-red-500' : ''">
                        <p x-show="gstinError" class="text-[10px] text-red-500 mt-1" x-text="gstinError"></p>
                        <label class="rounded-lg border-2 border-dashed border-gray-300 p-3 cursor-pointer flex items-center gap-3 min-h-20 hover:border-[#f05537] hover:bg-orange-50 transition"
                               :class="documents.tax_id ? 'border-emerald-300 bg-emerald-50' : ''">
                            <span class="w-9 h-9 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01.8-7.9A5.5 5.5 0 0118.5 10H19a3 3 0 010 6h-3m-4-5v8m0-8l-3 3m3-3l3 3"/></svg>
                            </span>
                            <div class="flex-1">
                                <div class="text-xs text-gray-500" x-show="!documents.tax_id">Upload GST certificate</div>
                                <div class="text-xs text-emerald-700 font-medium" x-show="documents.tax_id" x-text="documents.tax_id?.name"></div>
                                <?php if (!empty($uploadedDocs['tax_id'])): ?>
                                    <div class="text-xs text-emerald-700 font-medium" x-show="!documents.tax_id">
                                        Uploaded: <?= htmlspecialchars($uploadedDocs['tax_id']['file_name'] ?? 'Tax ID document', ENT_QUOTES) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="file" x-ref="doc_tax_id" accept=".pdf,.jpg,.jpeg,.png" @change="handleDocSelected('tax_id', $event)" class="hidden">
                        </label>
                    </div>

                    <!-- Address Proof -->
                    <label class="rounded-lg border-2 border-dashed border-gray-300 p-4 cursor-pointer flex items-center gap-3 min-h-24 hover:border-[#f05537] hover:bg-orange-50 transition"
                           :class="documents.address_proof ? 'border-emerald-300 bg-emerald-50' : ''">
                        <span class="w-11 h-11 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s7-4.4 7-11a7 7 0 10-14 0c0 6.6 7 11 7 11z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 10.5h.01"/></svg>
                        </span>
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-gray-800">Address Proof <span class="text-red-500">*</span> <span class="text-xs font-normal text-gray-400">Max 2 MB</span></div>
                            <div class="text-xs text-gray-500" x-show="!documents.address_proof">Upload utility bill or address document</div>
                            <div class="text-xs text-emerald-700 font-medium" x-show="documents.address_proof" x-text="documents.address_proof?.name"></div>
                            <?php if (!empty($uploadedDocs['address_proof'])): ?>
                                <div class="text-xs text-emerald-700 font-medium" x-show="!documents.address_proof">
                                    Uploaded: <?= htmlspecialchars($uploadedDocs['address_proof']['file_name'] ?? 'Address proof', ENT_QUOTES) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V8m0 0l-3 3m3-3l3 3M4 16.5V18a2 2 0 002 2h12a2 2 0 002-2v-1.5"/></svg>
                        <input type="file" x-ref="doc_address_proof" accept=".pdf,.jpg,.jpeg,.png" @change="handleDocSelected('address_proof', $event)" class="hidden">
                    </label>

                    <!-- Additional Documents -->
                    <label class="rounded-lg border-2 border-dashed border-gray-300 p-4 cursor-pointer flex items-center gap-3 min-h-24 hover:border-[#f05537] hover:bg-orange-50 transition"
                           :class="documents.other ? 'border-emerald-300 bg-emerald-50' : ''">
                        <span class="w-11 h-11 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3h7l5 5v13H7a2 2 0 01-2-2V5a2 2 0 012-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 3v5h5M9 13h6M9 17h4"/></svg>
                        </span>
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-gray-800">Additional Documents <span class="text-xs font-normal text-gray-400">Max 2 MB</span></div>
                            <div class="text-xs text-gray-500" x-show="!documents.other">Upload supporting document</div>
                            <div class="text-xs text-emerald-700 font-medium" x-show="documents.other" x-text="documents.other?.name"></div>
                            <?php if (!empty($uploadedDocs['other'])): ?>
                                <div class="text-xs text-emerald-700 font-medium" x-show="!documents.other">
                                    Uploaded: <?= htmlspecialchars($uploadedDocs['other']['file_name'] ?? 'Additional document', ENT_QUOTES) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V8m0 0l-3 3m3-3l3 3M4 16.5V18a2 2 0 002 2h12a2 2 0 002-2v-1.5"/></svg>
                        <input type="file" x-ref="doc_other" accept=".pdf,.jpg,.jpeg,.png" @change="handleDocSelected('other', $event)" class="hidden">
                    </label>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" x-model="agreeTerms" class="border-gray-300 rounded">
                    <span>I agree to the <a href="/terms" class="text-primary">Terms and Conditions</a> and <a href="/privacy" class="text-primary">Privacy Policy</a> <span class="text-red-500">*</span></span>
                </label>
            </div>
        </div>

        <!-- Wizard Navigation -->
        <div class="flex justify-between">
            <div>
                <a href="/employer/dashboard" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium">Cancel</a>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="prevStep" x-show="currentStep>1 && !basicInfoComplete"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium">Back</button>
                <button type="button" @click="updateProfile" x-show="currentStep===1"
                        :disabled="isSubmitting || !validateStep1()"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium disabled:opacity-50 disabled:cursor-not-allowed">Save</button>
                <button type="button" @click="saveAddress" x-show="currentStep===2"
                        :disabled="isSubmitting || !validateStep2()"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium disabled:opacity-50 disabled:cursor-not-allowed">Save Address</button>
                <button type="button" @click="nextStep" x-show="currentStep<3"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                <button type="button" @click="submitKyc" x-show="currentStep===3"
                        :disabled="isSubmitting || !agreeTerms"
                        class="px-6 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!isSubmitting">Submit for Verification</span>
                    <span x-show="isSubmitting">Submitting...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    const knownIndustries = ['IT/Software','Finance','Healthcare','Education','Manufacturing','Retail','Real Estate','Hospitality','Other'];
    const savedIndustry = <?= json_encode($employer->industry ?? '') ?>;
    const savedCompanySize = <?= json_encode($employer->size ?? '') ?>;
    const savedCompanyType = <?= json_encode($employer->company_type ?? ($address['company_type'] ?? '')) ?>;
    const savedAddress = <?= json_encode($address ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
    const normalizeAddress = (address) => {
        const source = address && typeof address === 'object' ? address : {};

        return {
            country: source.country || '',
            state: source.state || '',
            city: source.city || '',
            postal_code: source.postal_code || '',
            street: source.street || ''
        };
    };

    Alpine.data('profileForm', () => ({
        isSubmitting: false,
        savedAddress: savedAddress,
        currentStep: <?= (int)($startStep ?? 1) ?>,
        basicInfoComplete: <?= $basicInfoComplete ? 'true' : 'false' ?>,
        addressComplete: <?= $addressComplete ? 'true' : 'false' ?>,
        gstinError: '',
        pinCodeError: '',
        phoneError: '',
        locationNotice: '',
        agreeTerms: true,
        csrfToken: document.querySelector('meta[name="csrf-token"]').content,
        countries: [],
        selectedCountryCode: '',
        selectedPhoneCountryCode: '',
        phonePrefix: '',
        flagUrl: '',
        flagEmoji: '',
        map: null,
        marker: null,
        documents: {
            business_license: null,
            tax_id: null,
            address_proof: null,
            other: null
        },
        formData: {
            register_as: '<?= htmlspecialchars($employer->register_as ?? 'company', ENT_QUOTES) ?>',
            company_name: '<?= htmlspecialchars($employer->company_name ?? '', ENT_QUOTES) ?>',
            website: '<?= htmlspecialchars($employer->website ?? '', ENT_QUOTES) ?>',
            description: <?= json_encode($employer->description ?? '') ?>,
            company_type: savedCompanyType,
            profession_type: '<?= htmlspecialchars($employer->profession_type ?? '', ENT_QUOTES) ?>',
            service_category: '<?= htmlspecialchars($employer->service_category ?? '', ENT_QUOTES) ?>',
            industry: knownIndustries.includes(savedIndustry) ? savedIndustry : (savedIndustry ? 'Other' : ''),
            industry_custom: knownIndustries.includes(savedIndustry) ? '' : savedIndustry,
            company_size: savedCompanySize === '1000+' ? '1001+' : savedCompanySize,
            email: '<?= htmlspecialchars($user->email ?? '', ENT_QUOTES) ?>',
            phone_local: '',
            country: <?= json_encode($employer->country ?? ($address['country'] ?? '')) ?>,
            tax_id: '<?= htmlspecialchars($employer->tax_id ?? ($address['tax_id'] ?? ''), ENT_QUOTES) ?>',
            address: normalizeAddress(savedAddress)
        },
        async init() {
            this.hydrateSavedAddress();
            await this.loadCountries();
            this.initializeCountryFromExisting();
            this.initializePhoneFromExisting('<?= htmlspecialchars($user->phone ?? '', ENT_QUOTES) ?>');
            if (!this.selectedPhoneCountryCode && this.countries && this.countries.length) {
                const def = this.countries.find(c => c.code === 'IN') || this.countries[0];
                this.selectedPhoneCountryCode = def.code;
                this.onPhoneCountryChange();
            }
            this.initMap();
            this.$nextTick(() => setTimeout(() => this.autoDetectLocation(), 400));
        },
        hydrateSavedAddress() {
            if (!savedAddress || typeof savedAddress !== 'object') return;
            const normalizedAddress = normalizeAddress(savedAddress);

            this.formData.address = {
                ...this.formData.address,
                ...normalizedAddress
            };

            if (normalizedAddress.country) {
                this.formData.country = normalizedAddress.country;
            }

            if (window.DEBUG_PROFILE_ADDRESS) {
                console.log('Hydrated employer address', normalizedAddress);
                console.log('Normalized street', this.formData.address.street);
            }
        },
        validateGstin() {
            const value = (this.formData.tax_id || '').toString().trim().toUpperCase();
            this.formData.tax_id = value;
            if (!value) {
                this.gstinError = '';
                return true;
            }
            const ok = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{3}$/.test(value);
            this.gstinError = ok ? '' : 'Please enter valid GSTIN number.';
            return ok;
        },
        validateIndianPinCode() {
            const pin = (this.formData.address.postal_code || '').toString().trim();
            const isIndia = (this.formData.country || '').toLowerCase() === 'india';
            if (!pin || !isIndia) {
                this.pinCodeError = '';
                return true;
            }
            const ok = /^[0-9]{6}$/.test(pin);
            this.pinCodeError = ok ? '' : 'Pin Code must be exactly 6 digits';
            return ok;
        },
        validateIndianMobile() {
            const phone = (this.formData.phone_local || '').toString().replace(/\D+/g, '');
            const selected = this.countries.find(c => c.code === this.selectedPhoneCountryCode);
            const isIndia = (selected?.code || '') === 'IN' || (selected?.phone || '') === '+91';
            if (!phone || !isIndia) {
                this.phoneError = '';
                return true;
            }
            const ok = /^[0-9]{10}$/.test(phone);
            this.phoneError = ok ? '' : 'Mobile Number must be 10 digits';
            return ok;
        },
        handleDocSelected(type, event) {
            const file = event.target.files && event.target.files[0];
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) { alert('File size must be less than 2MB'); event.target.value=''; return; }
            this.documents[type] = file;
        },
        async loadCountries() {
            const cached = localStorage.getItem('countries_v1');
            if (cached) {
                try { this.countries = JSON.parse(cached); } catch(e) { this.countries = []; }
                this.countries = this.normalizeCountries(this.countries);
                if (this.countries.length < 10) this.countries = [];
            }
            if (!this.countries || this.countries.length === 0) {
                try {
                    const res = await fetch('/api/countries', { headers: { 'Accept': 'application/json' }});
                    const json = await res.json();
                    this.countries = this.normalizeCountries(json.countries || json.data?.countries || []);
                    if (this.countries.length < 10) throw new Error('Local country list incomplete');
                    localStorage.setItem('countries_v1', JSON.stringify(this.countries));
                } catch (err) {
                    try {
                        // Bundled list (the public restcountries v3.1 API is deprecated)
                        const res = await fetch('/js/data/countries.json');
                        const json = await res.json();
                        this.countries = this.normalizeCountries(json);
                        localStorage.setItem('countries_v1', JSON.stringify(this.countries));
                    } catch (_) {
                        this.countries = this.fallbackCountries();
                    }
                }
            }
            this.countries = this.normalizeCountries(this.countries);
        },
        fallbackCountries() {
            return [
                { name: 'India', code: 'IN', phone: '+91' },
                { name: 'United States', code: 'US', phone: '+1' },
                { name: 'United Kingdom', code: 'GB', phone: '+44' },
                { name: 'Canada', code: 'CA', phone: '+1' },
                { name: 'Australia', code: 'AU', phone: '+61' },
                { name: 'United Arab Emirates', code: 'AE', phone: '+971' },
                { name: 'Germany', code: 'DE', phone: '+49' },
                { name: 'France', code: 'FR', phone: '+33' },
                { name: 'Singapore', code: 'SG', phone: '+65' }
            ];
        },
        normalizeCountries(list) {
            const phoneByCode = { IN:'+91', US:'+1', GB:'+44', CA:'+1', AU:'+61', AE:'+971', DE:'+49', FR:'+33', SG:'+65', RU:'+7', AM:'+374' };
            const codeByName = {
                india:'IN', 'united states':'US', 'united kingdom':'GB', canada:'CA', australia:'AU',
                'united arab emirates':'AE', germany:'DE', france:'FR', singapore:'SG', russia:'RU', armenia:'AM'
            };
            const seen = new Set();
            const normalized = (Array.isArray(list) ? list : []).map(c => {
                const name = (c.name || c.country || '').toString().trim();
                const inferred = codeByName[name.toLowerCase()] || '';
                const code = (c.code || c.cca2 || inferred || '').toString().trim().toUpperCase();
                const phone = (c.phone || c.dial_code || phoneByCode[code] || '').toString().trim();
                return { name, code, phone };
            }).filter(c => c.name && c.code && !seen.has(c.code) && seen.add(c.code));
            return normalized.length ? normalized.sort((a,b) => a.name.localeCompare(b.name)) : this.fallbackCountries();
        },
        countryFlag(code) {
            code = (code || '').toString().trim().toUpperCase();
            if (!/^[A-Z]{2}$/.test(code)) return '';
            return code.replace(/./g, ch => String.fromCodePoint(127397 + ch.charCodeAt(0)));
        },
        initializeCountryFromExisting() {
            if (!this.formData.country) return;
            const match = this.countries.find(c => c.name.toLowerCase() === this.formData.country.toLowerCase());
            if (match) {
                this.selectedCountryCode = match.code;
                this.phonePrefix = match.phone;
                this.flagUrl = `https://flagcdn.com/24x18/${match.code.toLowerCase()}.png`;
                this.flagEmoji = this.countryFlag(match.code);
            }
        },
        initializePhoneFromExisting(existingFull) {
            if (!existingFull) return;
            // Try to split existing phone into prefix + local number
            const found = this.countries.find(c => existingFull.startsWith(c.phone));
            if (found) {
                this.phonePrefix = found.phone;
                this.selectedCountryCode = found.code;
                this.formData.phone_local = existingFull.replace(found.phone, '').trim();
                this.formData.country = found.name;
                this.flagUrl = `https://flagcdn.com/24x18/${found.code.toLowerCase()}.png`;
                this.selectedPhoneCountryCode = found.code;
                return;
            }
            const digits = existingFull.replace(/\D+/g, '');
            if (digits.length === 10) {
                const india = this.countries.find(c => c.code === 'IN') || this.countries[0];
                if (india) {
                    this.selectedPhoneCountryCode = india.code;
                    this.phonePrefix = india.phone;
                    this.flagUrl = `https://flagcdn.com/24x18/${india.code.toLowerCase()}.png`;
                    this.flagEmoji = this.countryFlag(india.code);
                }
                this.formData.phone_local = digits;
            }
        },
        onPhoneCountryChange() {
            const selected = this.countries.find(c => c.code === this.selectedPhoneCountryCode);
            if (selected) {
                this.phonePrefix = selected.phone;
                this.flagUrl = `https://flagcdn.com/24x18/${selected.code.toLowerCase()}.png`;
                this.flagEmoji = this.countryFlag(selected.code);
            }
        },
        onCountryChange() {
            const selected = this.countries.find(c => c.code === this.selectedCountryCode);
            if (selected) {
                this.phonePrefix = selected.phone;
                this.formData.country = selected.name;
                this.flagUrl = `https://flagcdn.com/24x18/${selected.code.toLowerCase()}.png`;
                this.flagEmoji = this.countryFlag(selected.code);
            } else {
                this.phonePrefix = '';
                this.formData.country = '';
                this.flagUrl = '';
                this.flagEmoji = '';
            }
        },
        initMap() {
            // Guard: don't initialize twice
            if (this.map) return;
            const el = document.getElementById('map');
            if (el && el._leaflet_id) return;
            if (!window.L) {
                const linkCss = document.createElement('link');
                linkCss.rel = 'stylesheet';
                linkCss.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(linkCss);
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                script.onload = () => this._setupMap();
                document.body.appendChild(script);
            } else {
                this._setupMap();
            }
        },
        hasAddressData() {
            const a = this.formData.address || {};
            return !!(this.formData.country || a.state || a.city || a.postal_code || a.street);
        },
        async autoDetectLocation() {
            if (this.hasAddressData()) {
                return;
            }

            await this.detectCountryFallback();

            if (!('geolocation' in navigator)) {
                this.locationNotice = 'Automatic GPS location is not supported here. Country was detected from browser settings; complete the remaining address manually.';
                return;
            }

            const canAsk = window.isSecureContext || ['localhost', '127.0.0.1'].includes(window.location.hostname);
            if (!canAsk) {
                this.locationNotice = 'GPS auto-fill requires HTTPS. Country was detected from browser settings; complete the remaining address manually.';
                return;
            }

            try {
                if (navigator.permissions && navigator.permissions.query) {
                    const perm = await navigator.permissions.query({ name: 'geolocation' });
                    if (perm.state === 'denied') {
                        this.locationNotice = 'Location permission is denied in this browser. Country was detected from browser settings; allow Location from the address bar for full auto-fill.';
                        return;
                    }
                }
            } catch (_) {}

            this.getBrowserLocation(false);
        },
        async detectCountryFallback() {
            try {
                const res = await fetch('/api/location/detect', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                const country = data.country || data.data?.country || this.guessCountryFromTimezone();
                if (!country || this.formData.country) return;
                const match = this.countries.find(c => c.name.toLowerCase() === country.toLowerCase());
                if (match) {
                    this.selectedCountryCode = match.code;
                    this.onCountryChange();
                }
            } catch (_) {
                const country = this.guessCountryFromTimezone();
                if (!country || this.formData.country) return;
                const match = this.countries.find(c => c.name.toLowerCase() === country.toLowerCase());
                if (match) {
                    this.selectedCountryCode = match.code;
                    this.onCountryChange();
                }
            }
        },
        guessCountryFromTimezone() {
            const tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
            if (/Kolkata|Calcutta/i.test(tz)) return 'India';
            if (/Dubai/i.test(tz)) return 'United Arab Emirates';
            if (/Singapore/i.test(tz)) return 'Singapore';
            if (/London/i.test(tz)) return 'United Kingdom';
            if (/Berlin/i.test(tz)) return 'Germany';
            if (/Paris/i.test(tz)) return 'France';
            if (/New_York|Chicago|Denver|Los_Angeles/i.test(tz)) return 'United States';
            if (/Toronto|Vancouver/i.test(tz)) return 'Canada';
            if (/Sydney|Melbourne|Perth/i.test(tz)) return 'Australia';
            return '';
        },
        _setupMap() {
            const el = document.getElementById('map');
            if (!el || el._leaflet_id) return;
            this.map = L.map(el, {zoomControl: true, zoomAnimation: true, markerZoomAnimation: true, inertia: true}).setView([20.0, 0.0], 2);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(this.map);
            this.marker = L.marker(this.map.getCenter(), {draggable: true, autoPan: true, autoPanPadding: [50,50]}).addTo(this.map);
            this.marker.on('dragend', () => {
                const {lat, lng} = this.marker.getLatLng();
                this.reverseGeocode(lat, lng);
            });
            // Fix tile size/cropping when container becomes visible
            setTimeout(() => { try { this.map.invalidateSize(true); } catch(_) {} }, 100);
            window.addEventListener('resize', () => { try { this.map.invalidateSize(true); } catch(_) {} });
        },
        useMyLocation() {
            this.locationNotice = '';
            // If browser exposes explicit permissions policy and it denies geolocation, bail early
            try {
                if (document.permissionsPolicy && typeof document.permissionsPolicy.allowsFeature === 'function') {
                    if (document.permissionsPolicy.allowsFeature('geolocation') === false) {
                        this.locationNotice = 'Location access is blocked by the browser or site policy. You can still complete the address manually.';
                        return;
                    }
                }
            } catch (_) {}
            if (!('geolocation' in navigator)) {
                this.locationNotice = 'This browser does not support automatic location detection. You can still complete the address manually.';
                return;
            }
            this.getBrowserLocation(true);
        },
        getBrowserLocation(overwriteAddress) {
            const opts = { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 };
            navigator.geolocation.getCurrentPosition(
                pos => {
                    this.locationNotice = '';
                    const {latitude, longitude} = pos.coords;
                    if (this.map) {
                        this.map.setView([latitude, longitude], 15, {animate: true});
                        this.map.once('moveend', () => { try { this.map.invalidateSize(true); } catch(_) {} });
                    }
                    if (this.marker) this.marker.setLatLng([latitude, longitude], {draggable:true});
                    this.reverseGeocode(latitude, longitude, overwriteAddress);
                },
                err => {
                    console.warn('Geolocation error', err);
                    this.locationNotice = (err && err.code === 1)
                        ? 'Location permission was denied. Manual address entry is still available. To use auto location, allow location access from the browser address bar.'
                        : 'Unable to detect location right now. You can still complete the address manually.';
                },
                opts
            );
        },
        setAddressValue(field, value, overwriteAddress) {
            value = (value || '').toString().trim();
            if (!value) return;
            if (overwriteAddress || !this.formData.address[field]) {
                this.formData.address[field] = value;
            }
        },
        async reverseGeocode(lat, lng, overwriteAddress = true) {
            try {
                const url = `/api/geo/reverse?lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`;
                const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
                const json = await res.json();
                const addr = json.address || {};
                const streetResult = [addr.road, addr.neighbourhood, addr.suburb, addr.hamlet].filter(Boolean).join(', ');
                this.setAddressValue('street', streetResult, overwriteAddress);
                let city = addr.city || addr.town || addr.village || addr.city_district || addr.suburb || addr.municipality || '';
                let state = addr.state || addr.state_district || addr.county || '';
                const norm = (s) => (s || '').toString().trim();
                city = norm(city);
                state = norm(state);
                this.setAddressValue('city', city, overwriteAddress);
                this.setAddressValue('state', state, overwriteAddress);
                this.setAddressValue('postal_code', addr.postcode || '', overwriteAddress);
                const cc = (addr.country_code || '').toUpperCase();
                if (cc) {
                    this.selectedCountryCode = cc;
                    this.onCountryChange();
                }
                // Ensure map marker is exactly at resolved point for best tile alignment
                if (this.marker) this.marker.setLatLng([lat, lng]);
                if (this.map) this.map.setView([lat, lng], Math.max(this.map.getZoom(), 15), {animate:true});
            } catch (e) {
                console.warn('Reverse geocode failed', e);
            }
        },
        // Debounced PIN-to-location lookup
        _pinTimer: null,
        schedulePinLookup() {
            this.validateIndianPinCode();
            clearTimeout(this._pinTimer);
            this._pinTimer = setTimeout(() => this.geocodeByPostalCode(), 600);
        },
        async geocodeByPostalCode(overwriteAddress = false) {
            const pin = (this.formData.address.postal_code || '').toString().trim();
            if (!pin || pin.length < 5) return; // wait for likely-complete PIN
            const parts = [];
            parts.push(pin);
            if (this.formData.address.city) parts.push(this.formData.address.city);
            if (this.formData.address.state) parts.push(this.formData.address.state);
            if (this.formData.country) parts.push(this.formData.country);
            const q = parts.join(', ');
            try {
                const url = `/api/geo/search?q=${encodeURIComponent(q)}&limit=1`;
                const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
                const data = await res.json();
                if (Array.isArray(data) && data[0] && data[0].lat && data[0].lon) {
                    const lat = parseFloat(data[0].lat);
                    const lon = parseFloat(data[0].lon);
                    if (isFinite(lat) && isFinite(lon)) {
                        if (this.map) {
                            this.map.setView([lat, lon], 15, {animate:true});
                            this.map.once('moveend', () => { try { this.map.invalidateSize(true); } catch(_) {} });
                        }
                        if (this.marker) this.marker.setLatLng([lat, lon]);
                        // Keep manually entered address fields unless caller explicitly allows overwrite.
                        this.reverseGeocode(lat, lon, overwriteAddress);
                    }
                }
            } catch (e) {
                console.warn('PIN geocode failed', e);
            }
        },
        handleLogoUpload(event) {
            // Logo will be handled by FormData
        },
        validateStep1() {
            if (this.basicInfoComplete) return true;
            const industryOk = this.formData.industry && (this.formData.industry !== 'Other' || (this.formData.industry_custom || '').trim() !== '');
            const companyOk = this.formData.register_as === 'company'
                ? !!(this.formData.company_type && industryOk && this.formData.company_size)
                : !!(this.formData.profession_type && this.formData.service_category);
            return !!(this.formData.company_name && companyOk && this.formData.email && this.validateGstin());
        },
        validateStep2() {
            const a = this.formData.address || {};
            return !!(this.formData.country && a.state && a.city && a.postal_code && a.street && this.validateIndianPinCode());
        },
        async goToStep(step) {
            if (step < 1 || step > 3 || step === this.currentStep) return;
            if (this.basicInfoComplete && step === 1) return;
            if ((this.currentStep === 1 && this.validateStep1()) || (this.currentStep === 2 && this.validateStep2())) {
                const saved = await this.updateProfile(true);
                if (!saved) return;
            }
            this.currentStep = step;
            this.$nextTick(() => {
                if (step === 2 && this.map) {
                    setTimeout(() => { try { this.map.invalidateSize(true); } catch(_) {} }, 100);
                }
            });
        },
        async nextStep() {
            if (this.currentStep === 1 && !this.validateStep1()) { alert('Please complete required company fields'); return; }
            if (this.currentStep === 2 && !this.validateStep2()) { alert('Please complete address details'); return; }
            if (this.currentStep === 1 || this.currentStep === 2) {
                const saved = await this.updateProfile(true);
                if (!saved) return;
            }
            if (this.currentStep < 3) this.currentStep += 1;
        },
        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep -= 1;
                if (this.basicInfoComplete && this.currentStep === 1) {
                    this.currentStep = 2;
                }
            }
        },
        applyProfileResponse(data) {
            if (data.employer?.address) {
                try {
                    const updatedAddress = typeof data.employer.address === 'string'
                        ? JSON.parse(data.employer.address)
                        : data.employer.address;
                    const normalizedAddress = normalizeAddress(updatedAddress);

                    this.formData.address = {
                        ...this.formData.address,
                        ...normalizedAddress
                    };

                    if (normalizedAddress.country) {
                        this.formData.country = normalizedAddress.country;
                    }
                } catch(e) {
                    console.error('Address parse failed', e);
                }
            }

            if (typeof data.basic_info_complete === 'boolean') {
                this.basicInfoComplete = data.basic_info_complete;
            }

            if (typeof data.address_complete === 'boolean') {
                this.addressComplete = data.address_complete;
            }
        },
        async saveAddress() {
            if (!this.validateStep2()) {
                alert('Please complete address details');
                return;
            }

            const saved = await this.updateProfile(true);
            if (!saved) return;

            this.addressComplete = true;
            this.currentStep = 3;
        },
        async updateProfile(silent = false) {
            if (!this.validateGstin() || !this.validateIndianPinCode() || !this.validateIndianMobile()) {
                if (!silent) alert(this.gstinError || this.pinCodeError || this.phoneError);
                return false;
            }
            this.isSubmitting = true;
            
            try {
                this.formData.address.country = this.formData.country || this.formData.address.country || '';
                const formData = new FormData();
                formData.append('register_as', this.formData.register_as || 'company');
                formData.append('company_name', this.formData.company_name);
                formData.append('website', this.formData.website || '');
                formData.append('description', this.formData.description || '');
                formData.append('industry', this.formData.industry || '');
                if (this.formData.industry === 'Other' && (this.formData.industry_custom || '').trim() !== '') {
                    formData.append('industry_custom', this.formData.industry_custom.trim());
                }
                formData.append('company_type', this.formData.company_type || '');
                formData.append('profession_type', this.formData.profession_type || '');
                formData.append('service_category', this.formData.service_category || '');
                formData.append('company_size', this.formData.company_size);
                formData.append('email', this.formData.email);
                const fullPhone = (this.phonePrefix || '') + (this.formData.phone_local ? (' ' + this.formData.phone_local) : '');
                if (this.formData.phone_local) {
                    formData.append('phone', fullPhone.trim());
                }
                formData.append('country', this.formData.country);
                formData.append('address', JSON.stringify(this.formData.address));
                formData.append('tax_id', this.formData.tax_id || '');
                formData.append('_token', this.csrfToken);

                // Add logo if selected
                const logoInput = document.querySelector('input[name="logo"]');
                if (logoInput && logoInput.files[0]) {
                    formData.append('logo', logoInput.files[0]);
                }

                const response = await fetch('/employer/profile', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.status === 403 && data.refresh_csrf) {
                    this.csrfToken = data.csrf_token;
                    // Update meta tag too
                    const meta = document.querySelector('meta[name="csrf-token"]');
                    if (meta) meta.content = data.csrf_token;
                    
                    if (!silent) {
                        return this.updateProfile(silent); // Retry once with new token
                    }
                }

                if (response.ok && data.success) {
                    this.applyProfileResponse(data);
                    if (!silent) {
                        alert('Profile updated successfully!');
                    }
                    return true;
                } else {
                    alert('Error: ' + (data.error || 'Failed to update profile'));
                    return false;
                }
            } catch (error) {
                alert('Error: ' + error.message);
                return false;
            } finally {
                this.isSubmitting = false;
            }
        },
        async submitKyc() {
            if (!this.agreeTerms) { alert('Please agree to the Terms and Conditions'); return; }
            this.isSubmitting = true;
            try {
                const saved = await this.updateProfile(true);
                if (!saved) {
                    throw new Error('Profile details were not saved. Please check the required fields and try again.');
                }
                const upload = async (type, file) => {
                    if (!file) return;
                    const fd = new FormData();
                    fd.append('doc_type', type);
                    fd.append('file', file);
                    const csrf = this.csrfToken;
                    if (csrf) fd.append('_token', csrf);
                    const res = await fetch('/employer/kyc/documents', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-Token': csrf
                        },
                        body: fd
                    });
                    if (!res.ok) {
                        let j; try { j = await res.json(); } catch(e) {}
                        throw new Error(j?.error || 'Failed to upload ' + type);
                    }
                };
                await upload('business_license', this.documents.business_license);
                await upload('tax_id', this.documents.tax_id);
                await upload('address_proof', this.documents.address_proof);
                await upload('other', this.documents.other);
                alert('Documents submitted. Redirecting to verification status.');
                window.location.href = '/employer/kyc';
            } catch (e) {
                alert('Error: ' + e.message);
            } finally {
                this.isSubmitting = false;
            }
        }
    }));
});
</script>












