<div x-data="subscriptionPlans()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
        <!-- Header Section -->
        <div class="text-center mb-6">
            <h4 class="text-2xl sm:text-3xl font-semibold text-gray-900 mb-2">Choose Your Plan</h4>
            <p class="text-sm sm:text-base text-gray-600 max-w-2xl mx-auto">Select the perfect plan for your hiring needs</p>
        </div>

        <!-- Upgrade Message Alert -->
        <?php if (!empty($upgradeMessage)): ?>
        <div class="max-w-4xl mx-auto mb-5">
            <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-2 flex-1">
                        <p class="text-sm text-blue-900"><?= htmlspecialchars($upgradeMessage) ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Billing Cycle Toggle -->
        <div class="max-w-2xl mx-auto mb-5">
            <div class="bg-white rounded-md shadow-sm border border-gray-200 p-4">
                <label class="block text-xs font-medium text-gray-700 mb-3 text-center uppercase tracking-wider">Select Billing Cycle</label>
                <div class="flex gap-2 max-w-lg mx-auto">
                    <button @click="selectedCycle = 'monthly'" 
                            class="flex-1 py-2 px-3 text-sm font-medium rounded-md transition-colors"
                            :class="selectedCycle === 'monthly' ? 'bg-blue-600 text-white' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200'">
                        Monthly
                    </button>
                    <button @click="selectedCycle = 'quarterly'" 
                            class="flex-1 py-2 px-3 text-sm font-medium rounded-md transition-colors"
                            :class="selectedCycle === 'quarterly' ? 'bg-blue-600 text-white' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200'">
                        <span class="block leading-tight">Quarterly</span>
                        <span class="block text-xs font-normal opacity-75">Save 10%</span>
                    </button>
                    <button @click="selectedCycle = 'annual'" 
                            class="flex-1 py-2 px-3 text-sm font-medium rounded-md transition-colors"
                            :class="selectedCycle === 'annual' ? 'bg-blue-600 text-white' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200'">
                        <span class="block leading-tight">Annual</span>
                        <span class="block text-xs font-normal opacity-75">Save 20%</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Discount Code Input -->
        <div class="max-w-md mx-auto mb-5">
            <div class="bg-blue-50 rounded-md border border-blue-200 p-3">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <label class="block text-sm font-medium text-gray-700">Have a discount code?</label>
                </div>
                <div class="flex gap-2">
                    <input type="text" 
                           x-model="discountCode" 
                           @input="validateDiscount()"
                           @keyup.enter="applyDiscount()"
                           placeholder="Enter promo code"
                           :disabled="validatingDiscount"
                           class="flex-1 px-3 py-1.5 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                           :class="discountApplied ? 'border-green-400 bg-green-50' : (discountError ? 'border-red-400 bg-red-50' : '')">
                    <button @click="applyDiscount()" 
                            :disabled="validatingDiscount || !discountCode || discountError"
                            class="px-4 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!validatingDiscount">Apply</span>
                        <span x-show="validatingDiscount" class="flex items-center">
                            <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </div>
                <!-- Success Message -->
                <div x-show="discountApplied && !discountError" class="mt-2 flex items-center gap-1.5 text-xs text-green-700 bg-green-50 px-2.5 py-1.5 rounded border border-green-200">
                    <svg class="w-3.5 h-3.5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>
                        <span x-show="discountType === 'percentage'">Discount applied: <span x-text="discountPercentage"></span>% off</span>
                        <span x-show="discountType === 'fixed_amount'">Discount applied: ₹<span x-text="formatPrice(discountPercentage)"></span> off</span>
                    </span>
                </div>
                <!-- Error Message -->
                <div x-show="discountError" class="mt-2 flex items-center gap-1.5 text-xs text-red-700 bg-red-50 px-2.5 py-1.5 rounded border border-red-200">
                    <svg class="w-3.5 h-3.5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <span x-text="discountError"></span>
                </div>
            </div>
        </div>

        <!-- Plans Grid -->
        <?php 
        // Debug: Check plans data
        $plansCount = is_array($plans) ? count($plans) : 0;
        ?>
        <?php if ($plansCount === 0): ?>
        <div class="text-center py-16">
            <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-8 max-w-2xl mx-auto shadow-md">
                <svg class="w-16 h-16 text-yellow-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <h3 class="text-xl font-bold text-yellow-900 mb-2">No Plans Available</h3>
                <p class="text-yellow-800 mb-4">Please run the database migrations to create subscription plans.</p>
                <div class="bg-yellow-100 p-4 rounded-lg text-left">
                    <p class="text-sm text-yellow-900 mb-2 font-semibold">Run this SQL in phpMyAdmin:</p>
                    <code class="bg-yellow-200 px-3 py-2 rounded block text-xs mb-4 font-mono">SOURCE scripts/migrations/020_subscription_plans.sql;</code>
                    <p class="text-sm text-yellow-900 mb-2 font-semibold">Or if plans exist but not showing:</p>
                    <code class="bg-yellow-200 px-3 py-2 rounded block text-xs font-mono">UPDATE subscription_plans SET is_active = 1 WHERE is_active IS NULL OR is_active = 0;</code>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 mb-6 max-w-6xl mx-auto">
            <template x-for="plan in plans" :key="plan.id">
                <div class="bg-white rounded-lg shadow border relative transition-shadow hover:shadow-md" 
                     :class="plan.is_featured == 1 ? 'border-blue-500 ring-1 ring-blue-100' : 'border-gray-200'">
                    <div x-show="plan.is_featured == 1" class="absolute -top-2.5 left-1/2 transform -translate-x-1/2 bg-blue-600 text-white text-xs font-medium px-3 py-0.5 rounded-full">
                        MOST POPULAR
                    </div>
                    <div x-show="isCurrentPlan(plan)" class="absolute top-2 left-2 bg-green-600 text-white text-[11px] font-semibold px-2.5 py-0.5 rounded-full shadow">
                        <span>Current plan</span>
                        <span class="ml-1 capitalize" x-text="(currentSubscription?.billing_cycle || 'monthly')"></span>
                    </div>
                    
                    <div class="p-5">
                        <!-- Plan Name -->
                        <div class="mb-4">
                            <h3 class="text-xl font-semibold text-gray-900 mb-1" x-text="plan.name"></h3>
                            <p class="text-gray-600 text-sm" x-text="plan.description"></p>
                        </div>
                        
                        <!-- Pricing Display -->
                        <div class="mb-4 pb-4 border-b border-gray-200">
                            <template x-if="discountApplied && getDiscountAmount(plan, selectedCycle) > 0">
                                <div class="text-center mb-1">
                                    <div class="text-xs text-gray-400 line-through" x-text="'₹' + formatPrice(getPrice(plan, selectedCycle))"></div>
                                </div>
                            </template>
                            <div class="flex items-baseline justify-center mb-1">
                                <span class="text-3xl font-semibold" 
                                      :class="discountApplied && getDiscountAmount(plan, selectedCycle) > 0 ? 'text-green-600' : 'text-gray-900'"
                                      x-text="'₹' + formatPrice(getFinalPrice(plan, selectedCycle))"></span>
                                <span class="text-gray-500 ml-1.5 text-sm" x-text="'/' + selectedCycle"></span>
                            </div>
                            <template x-if="discountApplied && getDiscountAmount(plan, selectedCycle) > 0">
                                <p class="text-xs text-green-600 text-center mt-0.5">
                                    Save ₹<span x-text="formatPrice(getDiscountAmount(plan, selectedCycle))"></span>
                                </p>
                            </template>
                            <p class="text-xs text-gray-500 mt-1 text-center">*GST as applicable</p>
                        </div>

                        <!-- Features List -->
                        <ul class="space-y-2 mb-5">
                            <li class="flex items-start">
                                <svg x-show="plan.max_job_posts >= 0" class="w-4 h-4 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-700">
                                    <span x-show="plan.max_job_posts == -1">Unlimited</span>
                                    <span x-show="plan.max_job_posts != -1 && plan.max_job_posts >= 0" x-text="plan.max_job_posts"></span>
                                    <span x-show="plan.max_job_posts >= 0"> job posts</span>
                                </span>
                            </li>
                            <li class="flex items-start">
                                <svg x-show="plan.max_contacts_per_month >= 0" class="w-4 h-4 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-700">
                                    <span x-show="plan.max_contacts_per_month == -1">Unlimited</span>
                                    <span x-show="plan.max_contacts_per_month != -1 && plan.max_contacts_per_month >= 0" x-text="plan.max_contacts_per_month"></span>
                                    <span x-show="plan.max_contacts_per_month >= 0"> contacts/month</span>
                                </span>
                            </li>
                            <li class="flex items-start">
                                <svg x-show="plan.resume_download_enabled == 1" class="w-4 h-4 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <svg x-show="plan.resume_download_enabled == 0" class="w-4 h-4 text-gray-300 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Resume downloads</span>
                            </li>
                            <li class="flex items-start">
                                <svg x-show="plan.chat_enabled == 1" class="w-4 h-4 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <svg x-show="plan.chat_enabled == 0" class="w-4 h-4 text-gray-300 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Chat with candidates</span>
                            </li>
                            <li class="flex items-start">
                                <svg x-show="plan.candidate_mobile_visible == 1" class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <svg x-show="plan.candidate_mobile_visible == 0" class="w-4 h-4 text-gray-300 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Mobile numbers visible</span>
                            </li>
                            <li class="flex items-start">
                                <svg x-show="plan.job_post_boost == 1" class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <svg x-show="plan.job_post_boost == 0" class="w-4 h-4 text-gray-300 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Job boost</span>
                            </li>
                            <li class="flex items-start">
                                <svg x-show="plan.ai_matching == 1" class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <svg x-show="plan.ai_matching == 0" class="w-4 h-4 text-gray-300 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-700">AI candidate matching</span>
                            </li>
                            <li class="flex items-start">
                                <svg x-show="plan.analytics_dashboard == 1" class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <svg x-show="plan.analytics_dashboard == 0" class="w-4 h-4 text-gray-300 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Analytics dashboard</span>
                            </li>
                        </ul>

                        <!-- Buy Now Button -->
                        <template x-if="isCurrentPlan(plan)">
                            <div class="space-y-2">
                                <button x-show="selectedCycle !== (currentSubscription?.billing_cycle || 'monthly')"
                                        @click="switchCycle(plan.slug)"
                                        class="w-full py-2 rounded-md font-medium text-sm transition-colors shadow-sm hover:shadow bg-blue-600 text-white hover:bg-blue-700">
                                    <span x-text="'Switch to ' + selectedCycle.charAt(0).toUpperCase() + selectedCycle.slice(1)"></span>
                                </button>
                                <a x-show="selectedCycle === (currentSubscription?.billing_cycle || 'monthly')"
                                   href="/employer/billing/overview"
                                   class="block w-full text-center py-2 rounded-md font-medium text-sm transition-colors shadow-sm hover:shadow bg-gray-100 text-gray-800 hover:bg-gray-200">
                                   Manage plan
                                </a>
                                <div class="text-center">
                                    <a href="/employer/billing/invoices" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">View invoices</a>
                                </div>
                            </div>
                        </template>
                        <template x-if="!isCurrentPlan(plan)">
                            <button @click="subscribe(plan.slug)" 
                                    class="w-full py-2 rounded-md font-medium text-sm transition-colors shadow-sm hover:shadow"
                                    :class="plan.is_featured == 1 ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-900 text-white hover:bg-gray-800'">
                                Get Started
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </div>
        <?php endif; ?>
</div>
<script>
    const plansData = <?= json_encode($plans ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    console.log("Plans data from PHP:", plansData);
    console.log("Plans count:", plansData.length);
    
    document.addEventListener("alpine:init", () => {
        // Initialize discount data
        const initialDiscountCode = "<?= htmlspecialchars($discountCode ?? '') ?>";
        const initialDiscount = <?= $discount ? json_encode($discount->attributes) : 'null' ?>;
        
        Alpine.data("subscriptionPlans", () => ({
            plans: plansData,
            currentSubscription: <?= json_encode($currentSubscription ?? null) ?>,
            selectedCycle: "monthly",
            discountCode: initialDiscountCode,
            discountApplied: <?= $discount ? 'true' : 'false' ?>,
            discountPercentage: <?= $discount ? (float)$discount->attributes['discount_value'] : 0 ?>,
            discountType: "<?= $discount ? ($discount->attributes['discount_type'] ?? 'percentage') : 'percentage' ?>",
            discountError: null,
            validatingDiscount: false,
            
            getPrice(plan, cycle) {
                const priceField = "price_" + cycle;
                return parseFloat(plan[priceField]) || 0;
            },
            
            formatPrice(price) {
                return new Intl.NumberFormat("en-IN").format(price);
            },
            
            getDiscountAmount(plan, cycle) {
                if (!this.discountApplied) return 0;
                const basePrice = this.getPrice(plan, cycle);
                if (this.discountType === 'percentage') {
                    const discount = (basePrice * this.discountPercentage) / 100;
                    return Math.round(discount * 100) / 100;
                } else {
                    return Math.min(this.discountPercentage, basePrice);
                }
            },
            
            getFinalPrice(plan, cycle) {
                const basePrice = this.getPrice(plan, cycle);
                const discount = this.getDiscountAmount(plan, cycle);
                return Math.max(0, basePrice - discount);
            },
            
            isCurrentPlan(plan) {
                const cur = this.currentSubscription;
                return !!cur && (parseInt(cur.plan_id, 10) === parseInt(plan.id, 10));
            },
            
            async validateDiscount() {
                if (!this.discountCode || this.discountCode.trim() === '') {
                    this.discountApplied = false;
                    this.discountError = null;
                    return;
                }
                
                this.validatingDiscount = true;
                this.discountError = null;
                
                try {
                    const response = await fetch('/api/discount-code/validate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': this.getCsrfToken()
                        },
                        body: JSON.stringify({
                            code: this.discountCode,
                            plan_id: 0, // Will validate when plan is selected
                            billing_cycle: this.selectedCycle
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.valid) {
                        this.discountApplied = true;
                        this.discountPercentage = data.discount_value;
                        this.discountType = data.discount_type;
                        this.discountError = null;
                    } else {
                        this.discountApplied = false;
                        this.discountError = data.error || 'Invalid discount code';
                        this.discountPercentage = 0;
                    }
                } catch (error) {
                    console.error('Discount validation error:', error);
                    this.discountApplied = false;
                    this.discountError = 'Error validating discount code';
                    this.discountPercentage = 0;
                } finally {
                    this.validatingDiscount = false;
                }
            },
            
            applyDiscount() {
                if (this.discountCode) {
                    window.location.href = "/employer/subscription/plans?discount=" + encodeURIComponent(this.discountCode);
                }
            },
            
            async subscribe(planSlug) {
                try {
                    // If already on this plan, route to change-plan to switch cycle
                    if (this.currentSubscription && this.currentSubscription.plan_id) {
                        const curPlanId = parseInt(this.currentSubscription.plan_id, 10);
                        const target = this.plans.find(p => parseInt(p.id, 10) === curPlanId);
                        if (target && target.slug === planSlug) {
                            return this.switchCycle(planSlug);
                        }
                    }
                    const response = await fetch("/employer/subscription/subscribe", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-Token": this.getCsrfToken()
                        },
                        body: JSON.stringify({
                            plan_slug: planSlug,
                            billing_cycle: this.selectedCycle,
                            discount_code: this.discountCode || null,
                            auto_renew: false
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        if (data.requires_payment) {
                            this.initiatePayment(data.payment_gateway, data.payment_id);
                        } else {
                            // Subscription activated without payment (free/trial)
                            const redirectUrl = data.redirect || "/employer/subscription/dashboard";
                            window.location.href = redirectUrl;
                        }
                    } else {
                        // Friendlier UI: show inline message instead of alert
                        console.warn("Subscription error:", data.error || "Failed to subscribe");
                    }
                } catch (error) {
                    console.error("Subscription error:", error);
                    // Soft fail, keep UI responsive
                }
            },
            
            async switchCycle(planSlug) {
                try {
                    const response = await fetch("/employer/subscription/change-plan", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-Token": this.getCsrfToken()
                        },
                        body: JSON.stringify({
                            plan_slug: planSlug,
                            billing_cycle: this.selectedCycle
                        })
                    });
                    const data = await response.json();
                    if (data.success) {
                        if (data.requires_payment) {
                            // Redirect to transactions or payment page
                            window.location.href = "/employer/billing/transactions";
                        } else {
                            window.location.href = "/employer/billing/overview";
                        }
                    } else {
                        console.warn("Change plan error:", data.error || "Failed to change plan");
                    }
                } catch (e) {
                    console.error("Change plan exception:", e);
                }
            },
            
            initiatePayment(gatewayData, paymentId) {
                if (!paymentId) {
                    console.warn("Missing paymentId for gateway initiation");
                    window.location.href = "/employer/subscription/dashboard";
                    return;
                }
                window.location.href = "/payment/create-order?payment_id=" + encodeURIComponent(String(paymentId));
            },
            
            getCsrfToken() {
                return document.querySelector("meta[name=\"csrf-token\"]")?.content || "";
            },
            
            // Initialize discount on component mount
            init() {
                // Set initial discount values if code exists
                if (initialDiscountCode && initialDiscount) {
                    this.discountCode = initialDiscountCode;
                    this.discountApplied = true;
                    this.discountPercentage = parseFloat(initialDiscount.discount_value || 0);
                    this.discountType = initialDiscount.discount_type || 'percentage';
                }
            }
        }));
    });
</script>  
