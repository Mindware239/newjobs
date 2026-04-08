<?php $scripts = '
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
'; ?>

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

        <!-- Error Message Display -->
        <?php if ($error = ($_GET['error'] ?? null)): ?>
        <div class="max-w-4xl mx-auto mb-5">
            <div class="bg-red-50 border border-red-200 rounded-md p-3">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 text-red-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-2 flex-1">
                        <p class="text-sm text-red-600"><?= htmlspecialchars($error) ?></p>
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

        <!-- Payment Gateway Selection -->
        <div class="max-w-2xl mx-auto mb-5">
            <div class="bg-white rounded-md shadow-sm border border-gray-200 p-4">
                <label class="block text-xs font-medium text-gray-700 mb-3 text-center uppercase tracking-wider">Select Payment Gateway</label>
                <div class="flex gap-4 justify-center">
                    <label class="flex items-center gap-2 cursor-pointer p-2 border rounded-md" :class="selectedGateway === 'razorpay' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                        <input type="radio" x-model="selectedGateway" value="razorpay" class="hidden">
                        <img src="https://razorpay.com/favicon.png" class="w-5 h-5" alt="Razorpay">
                        <span class="text-sm font-medium">Razorpay</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer p-2 border rounded-md" :class="selectedGateway === 'cashfree' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                        <input type="radio" x-model="selectedGateway" value="cashfree" class="hidden">
                        <img src="https://www.cashfree.com/favicon.ico" class="w-5 h-5" alt="Cashfree">
                        <span class="text-sm font-medium">Cashfree</span>
                    </label>
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

        <!-- Global Error Message -->
        <div x-show="errorMessage" x-cloak class="max-w-2xl mx-auto mb-5">
            <div class="bg-red-50 border border-red-200 rounded-md p-3 flex items-start gap-3">
                <svg class="h-5 w-5 text-red-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm text-red-700 font-medium" x-text="errorMessage"></p>
                </div>
                <button @click="errorMessage = null" class="text-red-400 hover:text-red-600">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/></svg>
                </button>
            </div>
        </div>

        <!-- Plans Grid -->
        <?php $plansCount = is_array($plans) ? count($plans) : 0; ?>
        <?php if ($plansCount === 0): ?>
        <div class="text-center py-16">
            <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-8 max-w-2xl mx-auto shadow-md">
                <svg class="w-16 h-16 text-yellow-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <h3 class="text-xl font-bold text-yellow-900 mb-2">No Plans Available</h3>
                <p class="text-yellow-800 mb-4">Please run the database migrations to create subscription plans.</p>
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
                    </div>
                    
                    <div class="p-5">
                        <div class="mb-4">
                            <h3 class="text-xl font-semibold text-gray-900 mb-1" x-text="plan.name"></h3>
                            <p class="text-gray-600 text-sm" x-text="plan.description"></p>
                        </div>
                        
                        <div class="mb-4 pb-4 border-b border-gray-200 text-center">
                            <div class="flex items-baseline justify-center mb-1">
                                <span class="text-3xl font-semibold text-gray-900" x-text="'₹' + formatPrice(getFinalPrice(plan, selectedCycle))"></span>
                                <span class="text-gray-500 ml-1.5 text-sm" x-text="'/' + selectedCycle"></span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">*GST as applicable</p>
                        </div>

                        <ul class="space-y-2 mb-5">
                            <template x-for="feat in (plan.features_list || [])" :key="feat">
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-sm text-gray-700" x-text="feat"></span>
                                </li>
                            </template>
                        </ul>

                        <button @click="subscribe(plan.slug)" 
                                :disabled="loadingPlan"
                                class="w-full py-2 rounded-md font-medium text-sm transition-colors shadow-sm hover:shadow disabled:opacity-50"
                                :class="plan.is_featured == 1 ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-900 text-white hover:bg-gray-800'">
                            <span x-show="loadingPlan !== plan.slug" x-text="isCurrentPlan(plan) ? 'Renew / Manage' : 'Get Started'"></span>
                            <span x-show="loadingPlan === plan.slug" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Processing...</span>
                            </span>
                        </button>
                    </div>
                </div>
            </template>
        </div>
        <?php endif; ?>
</div>

<script>
    const plansData = <?= json_encode($plans ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    
    document.addEventListener("alpine:init", () => {
        const initialDiscountCode = "<?= htmlspecialchars($discountCode ?? '') ?>";
        const initialDiscount = <?= ($discount ?? null) ? json_encode($discount) : 'null' ?>;
        
        Alpine.data("subscriptionPlans", () => ({
            plans: plansData,
            currentSubscription: <?= json_encode($currentSubscription ?? null) ?>,
            selectedCycle: "monthly",
            selectedGateway: "razorpay",
            discountCode: initialDiscountCode,
            discountApplied: !!initialDiscount,
            discountPercentage: initialDiscount ? parseFloat(initialDiscount.discount_value || 0) : 0,
            discountType: initialDiscount ? (initialDiscount.discount_type || 'percentage') : 'percentage',
            discountError: null,
            validatingDiscount: false,
            loadingPlan: null,
            errorMessage: null,
            
            getPrice(plan, cycle) {
                return parseFloat(plan["price_" + cycle]) || 0;
            },
            
            formatPrice(price) {
                return new Intl.NumberFormat("en-IN").format(price);
            },
            
            getDiscountAmount(plan, cycle) {
                if (!this.discountApplied) return 0;
                const basePrice = this.getPrice(plan, cycle);
                if (this.discountType === 'percentage') {
                    return Math.round((basePrice * this.discountPercentage) / 100);
                }
                return Math.min(this.discountPercentage, basePrice);
            },
            
            getFinalPrice(plan, cycle) {
                return Math.max(0, this.getPrice(plan, cycle) - this.getDiscountAmount(plan, cycle));
            },
            
            isCurrentPlan(plan) {
                return this.currentSubscription && parseInt(this.currentSubscription.plan_id) === parseInt(plan.id);
            },
            
            generateUUID() {
                if (typeof crypto !== 'undefined' && crypto.randomUUID) return crypto.randomUUID();
                return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                    var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                    return v.toString(16);
                });
            },

            async subscribe(planSlug) {
                if (this.loadingPlan) return;
                this.loadingPlan = planSlug;
                this.errorMessage = null;
                const idempotencyKey = this.generateUUID();

                try {
                    let csrfToken = this.getCsrfToken();
                    if (!csrfToken) {
                        csrfToken = document.querySelector('input[name="_token"]')?.value;
                        if (!csrfToken) {
                            this.errorMessage = "Security token missing. Please refresh the page.";
                            this.loadingPlan = null;
                            return;
                        }
                    }

                    const response = await fetch("/employer/subscription/subscribe", {
                        method: "POST",
                        headers: { 
                            "Content-Type": "application/json", 
                            "X-CSRF-Token": csrfToken,
                            "X-Requested-With": "XMLHttpRequest"
                        },
                        body: JSON.stringify({
                            plan_slug: planSlug,
                            billing_cycle: this.selectedCycle,
                            gateway: this.selectedGateway,
                            discount_code: this.discountCode || null,
                            idempotency_key: idempotencyKey
                        })
                    });
                    
                    const responseText = await response.text();
                    let result;
                    try {
                        result = JSON.parse(responseText);
                    } catch (e) {
                        console.error("Non-JSON response:", responseText);
                        this.errorMessage = "Server error. Please try again later.";
                        this.loadingPlan = null;
                        return;
                    }

                    // Handle standardized envelope {status: true, message: "...", data: {...}}
                    const isSuccess = result.status === true || result.success === true || (result.data && result.data.success === true);
                    const data = result.data || result;
                    const errorMsg = result.message || data.error || data.message || "Failed to subscribe";

                    if (isSuccess) {
                        if (data.requires_payment) {
                            this.initiatePayment(data.payment_gateway, data.payment_id);
                        } else {
                            window.location.href = "/employer/subscription/dashboard?status=activated";
                        }
                    } else {
                        if (data.refresh_csrf && data.csrf_token) {
                            const meta = document.querySelector('meta[name="csrf-token"]');
                            if (meta) meta.content = data.csrf_token;
                            this.errorMessage = errorMsg + " (Session refreshed. Please try again.)";
                        } else {
                            this.errorMessage = errorMsg;
                        }
                        this.loadingPlan = null;
                    }
                } catch (error) {
                    console.error("Subscription error:", error);
                    this.errorMessage = "An unexpected error occurred. Please check your internet connection.";
                    this.loadingPlan = null;
                }
            },
            
            initiatePayment(gatewayData, paymentId) {
                if (!gatewayData) {
                    this.errorMessage = "Payment gateway configuration missing.";
                    this.loadingPlan = null;
                    return;
                }

                if (gatewayData.gateway === 'cashfree') {
                    window.location.href = gatewayData.payment_url;
                    return;
                }

                if (gatewayData.gateway === 'razorpay') {
                    const options = {
                        "key": gatewayData.key,
                        "amount": gatewayData.amount,
                        "currency": "INR",
                        "name": gatewayData.name,
                        "description": "Subscription Payment",
                        "order_id": gatewayData.order_id,
                        "prefill": gatewayData.prefill,
                        "handler": async (response) => {
                            try {
                                const verifyRes = await fetch("/employer/subscription/payment/callback", {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': this.getCsrfToken() },
                                    body: JSON.stringify({
                                        razorpay_payment_id: response.razorpay_payment_id,
                                        razorpay_order_id: response.razorpay_order_id,
                                        razorpay_signature: response.razorpay_signature,
                                        payment_id: paymentId
                                    })
                                });
                                
                                const verifyResult = await verifyRes.json();
                                const isVerifySuccess = verifyResult.status === true || verifyResult.success === true;
                                const verifyData = verifyResult.data || verifyResult;

                                if (isVerifySuccess) {
                                    window.location.href = "/employer/subscription/dashboard?payment=success";
                                } else {
                                    this.errorMessage = verifyResult.message || verifyData.error || "Payment verification failed";
                                    this.loadingPlan = null;
                                }
                            } catch (e) {
                                console.error("Verification Error:", e);
                                this.errorMessage = "Payment verification error. Please contact support.";
                                this.loadingPlan = null;
                            }
                        },
                        "modal": { 
                            "ondismiss": () => { 
                                this.loadingPlan = null; 
                                this.errorMessage = "Payment cancelled by user.";
                            } 
                        }
                    };
                    const rzp = new Razorpay(options);
                    rzp.on('payment.failed', (response) => {
                        this.errorMessage = response.error.description || "Payment failed";
                        this.loadingPlan = null;
                    });
                    rzp.open();
                }
            },
            
            getCsrfToken() { return document.querySelector("meta[name=\"csrf-token\"]")?.content || ""; },
            
            async validateDiscount() {
                if (!this.discountCode) { this.discountApplied = false; return; }
                this.validatingDiscount = true;
                try {
                    const res = await fetch('/api/discount-code/validate', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': this.getCsrfToken() },
                        body: JSON.stringify({ code: this.discountCode, plan_id: 0, billing_cycle: this.selectedCycle })
                    });
                    const result = await res.json();
                    const data = result.data || result;
                    
                    if (data.valid) {
                        this.discountApplied = true;
                        this.discountPercentage = data.discount_value;
                        this.discountType = data.discount_type;
                        this.discountError = null;
                    } else {
                        this.discountApplied = false;
                        this.discountError = data.error || result.message || 'Invalid code';
                    }
                } catch (e) {
                    this.discountApplied = false;
                    this.discountError = 'Validation failed';
                } finally { this.validatingDiscount = false; }
            },
            
            applyDiscount() { this.validateDiscount(); }
        }));
    });
</script>
