<?php

/**
 * @var string $title
 * @var \App\Models\Employer $employer
 * @var \App\Models\User $user
 * @var array $address
 */
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
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center"
                         :class="(currentStep===1)?'bg-indigo-600 text-white':((currentStep>1||validateStep1())?'bg-emerald-600 text-white':'bg-gray-100 text-gray-500')">
                        <template x-if="currentStep>1 || validateStep1()">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </template>
                        <template x-if="!(currentStep>1 || validateStep1())">
                            <span>1</span>
                        </template>
                    </div>
                    <span class="text-sm font-medium" :class="currentStep===1?'text-indigo-700':'text-gray-600'">Basic Info</span>
                </div>
                <div class="h-0.5 w-10"
                     :class="(currentStep>1||validateStep1())?'bg-emerald-200':'bg-gray-200'"></div>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center"
                         :class="(currentStep===2)?'bg-indigo-600 text-white':((currentStep>2||validateStep2())?'bg-emerald-600 text-white':'bg-gray-100 text-gray-500')">
                        <template x-if="currentStep>2 || validateStep2()">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </template>
                        <template x-if="!(currentStep>2 || validateStep2())">
                            <span>2</span>
                        </template>
                    </div>
                    <span class="text-sm font-medium" :class="currentStep===2?'text-indigo-700':'text-gray-600'">Address</span>
                </div>
                <div class="h-0.5 w-10"
                     :class="(currentStep>2||validateStep2())?'bg-emerald-200':'bg-gray-200'"></div>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center"
                         :class="(currentStep===3)?'bg-indigo-600 text-white':'bg-gray-100 text-gray-500'">
                        <span>3</span>
                    </div>
                    <span class="text-sm font-medium" :class="currentStep===3?'text-indigo-700':'text-gray-600'">Documents</span>
                </div>
            </div>
        </div>
        <!-- Company Information -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 hover:shadow transition-shadow space-y-4" x-show="currentStep === 1">
            <div class="flex items-center gap-2 mb-4">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-briefcase h-5 w-5 text-indigo-500"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path><rect width="20" height="14" x="2" y="6" rx="2"></rect></svg>                <h2 class="text-xl font-semibold text-gray-900">Company Information</h2>
            </div>
            
            <form @submit.prevent="updateProfile" class="space-y-4">
                <input type="hidden" name="_token" :value="csrfToken">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Company Name *</label>
                        <input type="text" 
                               x-model="formData.company_name"
                               required
                               placeholder="Enter company name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#5b6bd5] hover:border-[#5b6bd5]/50 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Website</label>
                        <input type="url" 
                               x-model="formData.website"
                               placeholder="https://example.com"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#5b6bd5] hover:border-[#5b6bd5]/50 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Company Description</label>
                    <textarea x-model="formData.description"
                              rows="4"
                              placeholder="Brief description of your company..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#5b6bd5] hover:border-[#5b6bd5]/50 transition"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Company Type *</label>
                        <select x-model="formData.company_type"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#5b6bd5] hover:border-[#5b6bd5]/50 transition">
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
                            <option value="freelancer">Freelancer / Individual</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Industry Type *</label>
                        <select x-model="formData.industry"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#5b6bd5] hover:border-[#5b6bd5]/50 transition">
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
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#5b6bd5] hover:border-[#5b6bd5]/50 transition">
                        </div>
                    </div>
                </div>

                <!-- Logo Upload -->
                <div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Company Size *</label>
                            <select x-model="formData.company_size"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#5b6bd5] hover:border-[#5b6bd5]/50 transition">
                                <option value="">Select size</option>
                                <option value="1-10">1-10 employees</option>
                                <option value="11-50">11-50 employees</option>
                                <option value="51-200">51-200 employees</option>
                                <option value="201-500">201-500 employees</option>
                                <option value="501-1000">501-1000 employees</option>
                                <option value="1000+">1000+ employees</option>
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
                                       class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#5b6bd5] hover:border-[#5b6bd5]/50 transition">
                            </div>
                        </div>
                    </div>
                </div>
          
                
                <!-- Contact Information (inside same card) -->
                <div class="flex items-center gap-2">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail h-5 w-5 text-indigo-500"><rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>                    <h3 class="text-lg font-semibold text-gray-900">Contact Information</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-start">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" 
                               x-model="formData.email"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#5b6bd5]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Number *</label>
                        <div class="flex items-center gap-2">
                            <select x-model="selectedPhoneCountryCode" @change="onPhoneCountryChange()" class="w-28 px-3 py-2 border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-[#5b6bd5]">
                                <template x-for="c in countries" :key="c.code">
                                    <option :value="c.code" x-text="c.code + ' ' + c.phone"></option>
                                </template>
                            </select>
                            <input type="tel" 
                                   x-model="formData.phone_local"
                                   placeholder="Mobile number"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#5b6bd5]">
                        </div>
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
                        <img :src="flagUrl" alt=""
                             :title="formData.country ? (formData.country + ' (' + phonePrefix + ')') : 'Select country'"
                             class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-4 rounded-sm border border-gray-200 hover:scale-125 transition-transform"
                             x-show="flagUrl">
                        <select x-model="selectedCountryCode"
                                @change="onCountryChange()"
                                required
                                :title="formData.country ? (formData.country + ' (' + phonePrefix + ')') : 'Select country'"
                                class="w-full pl-12 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#5b6bd5] bg-white hover:border-[#5b6bd5]/50 transition">
                            <option value="">Select country</option>
                            <template x-for="c in countries" :key="c.code">
                            <option :value="c.code" x-text="c.code + ' ' + c.name"></option>
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
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#5b6bd5] hover:border-[#5b6bd5]/50 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">City *</label>
                        <input type="text" 
                               x-model="formData.address.city"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#5b6bd5] hover:border-[#5b6bd5]/50 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Postal Code *</label>
                    <input type="text" 
                           x-model="formData.address.postal_code"
                           @input="schedulePinLookup()"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#5b6bd5] hover:border-[#5b6bd5]/50 transition">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Street Address *</label>
                    <textarea x-model="formData.address.street"
                              rows="2"
                              required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#5b6bd5] hover:border-[#5b6bd5]/50 transition"><?= htmlspecialchars($address['street'] ?? '', ENT_QUOTES) ?></textarea>
                </div>

                <div class="space-y-3">
                    <button type="button" @click="useMyLocation()" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Use my location
                    </button>
                    <div id="map" class="w-full h-48 rounded-lg border border-gray-200"></div>
                    <p class="text-xs text-gray-500">Drag the marker to your exact location. Address fields update automatically.</p>
                </div>
            </div>
        </div>

        <!-- Document Verifications -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5 hover:shadow transition-shadow" x-show="currentStep === 3">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7 20h10a2 2 0 002-2V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <h2 class="text-xl font-semibold text-gray-900">Document Verification</h2>
            </div>
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Business License -->
                    <label class="rounded-lg border-2 border-dashed border-gray-300 p-4 cursor-pointer flex items-center gap-3 min-h-24">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16M12 12l4-4m-4 4l-4-4"></path></svg>
                        <div class="flex-1">
                            <div class="text-sm text-gray-700">Business License <span class="text-red-500">*</span> <span class="text-xs text-gray-400">Max 2 MB</span></div>
                            <div class="text-xs text-gray-500" x-show="!documents.business_license">Choose file or drag here</div>
                            <div class="text-xs text-emerald-700 font-medium" x-show="documents.business_license" x-text="documents.business_license?.name"></div>
                        </div>
                        <input type="file" x-ref="doc_business_license" accept=".pdf,.jpg,.jpeg,.png" @change="handleDocSelected('business_license', $event)" class="hidden">
                    </label>

                    <!-- Tax ID / GST -->
                    <div class="rounded-lg border border-gray-200 p-4 space-y-3">
                        <div class="flex items-center gap-2 text-gray-800 font-medium">
                            <span>Tax ID / GST Certificate <span class="text-red-500">*</span> <span class="text-xs text-gray-400">Max 2 MB</span></span>
                        </div>
                        <input type="text" x-model="formData.tax_id" placeholder="GST Number / Tax ID" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <label class="rounded-lg border-2 border-dashed border-gray-300 p-3 cursor-pointer flex items-center gap-3 min-h-20">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16M12 12l4-4m-4 4l-4-4"></path></svg>
                            <div class="flex-1">
                                <div class="text-xs text-gray-500" x-show="!documents.tax_id">Choose file or drag here</div>
                                <div class="text-xs text-emerald-700 font-medium" x-show="documents.tax_id" x-text="documents.tax_id?.name"></div>
                            </div>
                            <input type="file" x-ref="doc_tax_id" accept=".pdf,.jpg,.jpeg,.png" @change="handleDocSelected('tax_id', $event)" class="hidden">
                        </label>
                    </div>

                    <!-- Address Proof -->
                    <label class="rounded-lg border-2 border-dashed border-gray-300 p-4 cursor-pointer flex items-center gap-3 min-h-24">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16M12 12l4-4m-4 4l-4-4"></path></svg>
                        <div class="flex-1">
                            <div class="text-sm text-gray-700">Address Proof <span class="text-red-500">*</span> <span class="text-xs text-gray-400">Max 2 MB</span></div>
                            <div class="text-xs text-gray-500" x-show="!documents.address_proof">Choose file or drag here</div>
                            <div class="text-xs text-emerald-700 font-medium" x-show="documents.address_proof" x-text="documents.address_proof?.name"></div>
                        </div>
                        <input type="file" x-ref="doc_address_proof" accept=".pdf,.jpg,.jpeg,.png" @change="handleDocSelected('address_proof', $event)" class="hidden">
                    </label>

                    <!-- Additional Documents -->
                    <label class="rounded-lg border-2 border-dashed border-gray-300 p-4 cursor-pointer flex items-center gap-3 min-h-24">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16M12 12l4-4m-4 4l-4-4"></path></svg>
                        <div class="flex-1">
                            <div class="text-sm text-gray-700">Additional Documents <span class="text-xs text-gray-400">Max 2 MB</span></div>
                            <div class="text-xs text-gray-500" x-show="!documents.other">Choose file or drag here</div>
                            <div class="text-xs text-emerald-700 font-medium" x-show="documents.other" x-text="documents.other?.name"></div>
                        </div>
                        <input type="file" x-ref="doc_other" accept=".pdf,.jpg,.jpeg,.png" @change="handleDocSelected('other', $event)" class="hidden">
                    </label>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" x-model="agreeTerms" class="border-gray-300 rounded">
                    <span>I agree to the <a href="/terms" class="text-indigo-600">Terms and Conditions</a> and <a href="/privacy" class="text-indigo-600">Privacy Policy</a> <span class="text-red-500">*</span></span>
                </label>
            </div>
        </div>

        <!-- Wizard Navigation -->
        <div class="flex justify-between">
            <div>
                <a href="/employer/dashboard" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium">Cancel</a>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="prevStep" x-show="currentStep>1"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium">Back</button>
                <button type="button" @click="updateProfile" x-show="currentStep===1"
                        :disabled="isSubmitting || !validateStep1()"
                        class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium disabled:opacity-50 disabled:cursor-not-allowed">Save</button>
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
    Alpine.data('profileForm', () => ({
        isSubmitting: false,
        currentStep: 1,
        agreeTerms: true,
        csrfToken: document.querySelector('meta[name="csrf-token"]').content,
        countries: [],
        selectedCountryCode: '',
        selectedPhoneCountryCode: '',
        phonePrefix: '',
        flagUrl: '',
        map: null,
        marker: null,
        documents: {
            business_license: null,
            tax_id: null,
            address_proof: null,
            other: null
        },
        formData: {
            company_name: '<?= htmlspecialchars($employer->company_name ?? '', ENT_QUOTES) ?>',
            website: '<?= htmlspecialchars($employer->website ?? '', ENT_QUOTES) ?>',
            description: <?= json_encode($employer->description ?? '') ?>,
            company_type: '<?= htmlspecialchars($employer->company_type ?? '', ENT_QUOTES) ?>',
            industry: '<?= htmlspecialchars($employer->industry ?? '', ENT_QUOTES) ?>',
            industry_custom: '',
            company_size: '<?= htmlspecialchars($employer->size ?? '', ENT_QUOTES) ?>',
            email: '<?= htmlspecialchars($user->email ?? '', ENT_QUOTES) ?>',
            phone_local: '',
            country: '<?= htmlspecialchars($employer->country ?? '', ENT_QUOTES) ?>',
            tax_id: '<?= htmlspecialchars($employer->tax_id ?? '', ENT_QUOTES) ?>',
            address: {
                state: <?= json_encode($address['state'] ?? '') ?>,
                city: <?= json_encode($address['city'] ?? '') ?>,
                postal_code: <?= json_encode($address['postal_code'] ?? '') ?>,
                street: <?= json_encode($address['street'] ?? '') ?>
            }
        },
        async init() {
            await this.loadCountries();
            this.initializeCountryFromExisting();
            this.initializePhoneFromExisting('<?= htmlspecialchars($user->phone ?? '', ENT_QUOTES) ?>');
            if (!this.selectedPhoneCountryCode && this.countries && this.countries.length) {
                const def = this.countries.find(c => c.code === 'IN') || this.countries[0];
                this.selectedPhoneCountryCode = def.code;
                this.onPhoneCountryChange();
            }
            this.initMap();
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
            }
            if (!this.countries || this.countries.length === 0) {
                try {
                    const res = await fetch('https://restcountries.com/v3.1/all?fields=name,idd,cca2');
                    const json = await res.json();
                    this.countries = json.map(c => ({
                        name: c?.name?.common || '',
                        code: c?.cca2 || '',
                        phone: (c?.idd?.root || '') + ((c?.idd?.suffixes && c.idd.suffixes[0]) || '')
                    })).filter(c => c.name && c.code && c.phone).sort((a,b)=>a.name.localeCompare(b.name));
                    localStorage.setItem('countries_v1', JSON.stringify(this.countries));
                } catch (err) {
                    this.countries = [
                        { name: 'India', code: 'IN', phone: '+91' },
                        { name: 'United States', code: 'US', phone: '+1' },
                        { name: 'United Kingdom', code: 'GB', phone: '+44' },
                        { name: 'Canada', code: 'CA', phone: '+1' },
                        { name: 'Australia', code: 'AU', phone: '+61' }
                    ];
                }
            }
        },
        initializeCountryFromExisting() {
            if (!this.formData.country) return;
            const match = this.countries.find(c => c.name.toLowerCase() === this.formData.country.toLowerCase());
            if (match) {
                this.selectedCountryCode = match.code;
                this.phonePrefix = match.phone;
                this.flagUrl = `https://flagcdn.com/24x18/${match.code.toLowerCase()}.png`;
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
            }
        },
        onPhoneCountryChange() {
            const selected = this.countries.find(c => c.code === this.selectedPhoneCountryCode);
            if (selected) {
                this.phonePrefix = selected.phone;
                this.flagUrl = `https://flagcdn.com/24x18/${selected.code.toLowerCase()}.png`;
            }
        },
        onCountryChange() {
            const selected = this.countries.find(c => c.code === this.selectedCountryCode);
            if (selected) {
                this.phonePrefix = selected.phone;
                this.formData.country = selected.name;
                this.flagUrl = `https://flagcdn.com/24x18/${selected.code.toLowerCase()}.png`;
            } else {
                this.phonePrefix = '';
                this.formData.country = '';
                this.flagUrl = '';
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
            // If browser exposes explicit permissions policy and it denies geolocation, bail early
            try {
                if (document.permissionsPolicy && typeof document.permissionsPolicy.allowsFeature === 'function') {
                    if (document.permissionsPolicy.allowsFeature('geolocation') === false) {
                        alert('Location access is blocked by site policy. Please enter address manually.');
                        return;
                    }
                }
            } catch (_) {}
            if (!('geolocation' in navigator)) {
                alert('Geolocation not supported in this browser. Please enter address manually.');
                return;
            }
            const opts = { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 };
            navigator.geolocation.getCurrentPosition(
                pos => {
                    const {latitude, longitude} = pos.coords;
                    if (this.map) {
                        this.map.setView([latitude, longitude], 15, {animate: true});
                        this.map.once('moveend', () => { try { this.map.invalidateSize(true); } catch(_) {} });
                    }
                    if (this.marker) this.marker.setLatLng([latitude, longitude], {draggable:true});
                    this.reverseGeocode(latitude, longitude);
                },
                err => {
                    console.warn('Geolocation error', err);
                    const msg = (err && err.code === 1)
                        ? 'Permission denied. Please allow location access or enter address manually.'
                        : 'Unable to access location. Please enter address manually.';
                    alert(msg);
                },
                opts
            );
        },
        async reverseGeocode(lat, lng) {
            try {
                const url = `/api/geo/reverse?lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`;
                const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
                const json = await res.json();
                const addr = json.address || {};
                this.formData.address.street = [addr.road, addr.neighbourhood, addr.suburb, addr.hamlet].filter(Boolean).join(', ');
                let city = addr.city || addr.town || addr.village || addr.city_district || addr.suburb || addr.municipality || '';
                let state = addr.state || addr.state_district || addr.county || '';
                // Normalize common Indian naming quirks
                const norm = (s) => (s || '').toString().trim();
                city = norm(city);
                state = norm(state);
                if (/delhi/i.test(city) && !/delhi/i.test(state)) {
                    state = 'Delhi';
                }
                if (/national capital territory/i.test(state)) {
                    state = 'Delhi';
                }
                this.formData.address.city = city;
                this.formData.address.state = state;
                this.formData.address.postal_code = addr.postcode || '';
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
            clearTimeout(this._pinTimer);
            this._pinTimer = setTimeout(() => this.geocodeByPostalCode(), 600);
        },
        async geocodeByPostalCode() {
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
                        // Re-run reverseGeocode to normalize fields from coordinates
                        this.reverseGeocode(lat, lon);
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
            const industryOk = this.formData.industry && (this.formData.industry !== 'Other' || (this.formData.industry_custom || '').trim() !== '');
            return !!(this.formData.company_name && this.formData.company_type && industryOk && this.formData.company_size && this.formData.email);
        },
        validateStep2() {
            const a = this.formData.address || {};
            return !!(this.formData.country && a.state && a.city && a.postal_code && a.street);
        },
        async nextStep() {
            if (this.currentStep === 1 && !this.validateStep1()) { alert('Please complete required company fields'); return; }
            if (this.currentStep === 2 && !this.validateStep2()) { alert('Please complete address details'); return; }
            if (this.currentStep === 2) {
                await this.updateProfile(true);
            }
            if (this.currentStep < 3) this.currentStep += 1;
        },
        prevStep() {
            if (this.currentStep > 1) this.currentStep -= 1;
        },
        async updateProfile(silent = false) {
            this.isSubmitting = true;
            
            try {
                const formData = new FormData();
                formData.append('company_name', this.formData.company_name);
                formData.append('website', this.formData.website || '');
                formData.append('description', this.formData.description || '');
                formData.append('industry', this.formData.industry || '');
                if (this.formData.industry === 'Other' && (this.formData.industry_custom || '').trim() !== '') {
                    formData.append('industry_custom', this.formData.industry_custom.trim());
                }
                formData.append('company_type', this.formData.company_type || '');
                formData.append('company_size', this.formData.company_size);
                formData.append('email', this.formData.email);
                const fullPhone = (this.phonePrefix || '') + (this.formData.phone_local ? (' ' + this.formData.phone_local) : '');
                formData.append('phone', fullPhone.trim());
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
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    if (!silent) {
                        alert('Profile updated successfully!');
                        window.location.href = '/employer/profile?submitted=1';
                    }
                } else {
                    alert('Error: ' + (data.error || 'Failed to update profile'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            } finally {
                this.isSubmitting = false;
            }
        },
        async submitKyc() {
            if (!this.agreeTerms) { alert('Please agree to the Terms and Conditions'); return; }
            this.isSubmitting = true;
            try {
                await this.updateProfile(true);
                const upload = async (type, file) => {
                    if (!file) return;
                    const fd = new FormData();
                    fd.append('doc_type', type);
                    fd.append('file', file);
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    if (csrf) fd.append('_token', csrf);
                    const res = await fetch('/employer/kyc/documents', { method: 'POST', body: fd });
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

