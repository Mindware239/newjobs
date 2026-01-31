<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <title>Premium Plans - Mindware Infotech</title>
        <link rel="icon" type="image/png" href="/uploads/Mindware-infotech.png">
        <link href="/css/output.css" rel="stylesheet">
        <script>
            // Define premiumPlans function before Alpine.js loads
            const PLANS = <?= json_encode($plans, JSON_UNESCAPED_UNICODE) ?>;
            const PREFILL = {
                name: <?= json_encode($candidateName ?? '') ?>,
                email: <?= json_encode($candidateEmail ?? '') ?>,
                contact: <?= json_encode($candidatePhone ?? '') ?>
            };
            function premiumPlans() {
                return {
                    showPaymentModal: false,
                    selectedPlan: null,
                    paymentMethod: 'razorpay',
                    isProcessing: false,
                    get selectedPlanDetails() {
                        if (!this.selectedPlan) return null;
                        return PLANS.find(p => p.id === this.selectedPlan) || null;
                    },
                    selectPlan(planId) {
                        this.selectedPlan = planId;
                        this.showPaymentModal = true;
                    },
                    scrollToPlans() {
                        document.getElementById('pricing-plans')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    },
                    async processPayment() {
                        this.isProcessing = true;
                        try {
                            const response = await fetch('/candidate/premium/payment', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-Token': this.getCsrfToken()
                                },
                                body: JSON.stringify({
                                    plan_id: this.selectedPlan,
                                    payment_method: this.paymentMethod
                                })
                            });

                            const contentType = response.headers.get('content-type') || '';
                            if (contentType.includes('application/json')) {
                                const data = await response.json();
                                if (response.ok && data.success) {
                                    this.startRazorpayCheckout(data.payment_data, data.purchase_id);
                                } else {
                                    alert((data && (data.error || data.message)) || 'Payment initiation failed');
                                }
                            } else {
                                const text = await response.text();
                                alert(text || 'Unexpected response from server. Please try again.');
                            }
                        } catch (error) {
                            alert('Error: ' + error.message);
                        } finally {
                            this.isProcessing = false;
                        }
                    },
                    startRazorpayCheckout(paymentData, purchaseId) {
                        const self = this;
                        const options = {
                            key: paymentData.key,
                            amount: paymentData.amount,
                            currency: paymentData.currency || 'INR',
                            name: 'Mindware Infotech',
                            description: 'Candidate Premium',
                            order_id: paymentData.order_id || undefined,
                            prefill: {
                                name: PREFILL.name || undefined,
                                email: PREFILL.email || undefined,
                                contact: PREFILL.contact || undefined
                            },
                            handler: async function (resp) {
                                try {
                                    const callbackResponse = await fetch('/candidate/premium/payment/callback', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-Token': self.getCsrfToken()
                                        },
                                        body: JSON.stringify({
                                            purchase_id: purchaseId,
                                            payment_id: resp.razorpay_payment_id || '',
                                            status: 'success'
                                        })
                                    });
                                    const ct = callbackResponse.headers.get('content-type') || '';
                                    if (ct.includes('application/json')) {
                                        const result = await callbackResponse.json();
                                        if (callbackResponse.ok && result.success) {
                                            alert('Payment successful. Premium activated.');
                                            if (result.receipt_url) {
                                                window.open(result.receipt_url, '_blank');
                                            }
                                            window.location.href = '/candidate/dashboard';
                                        } else {
                                            alert((result && (result.error || result.message)) || 'Payment confirmation failed');
                                        }
                                    } else {
                                        const text = await callbackResponse.text();
                                        alert(text || 'Payment confirmation response was unexpected.');
                                    }
                                } catch (e) {
                                    alert('Payment confirmation error: ' + e.message);
                                }
                            },
                            modal: {
                                ondismiss: function () {
                                    self.isProcessing = false;
                                }
                            }
                        };
                        const rzp = new Razorpay(options);
                        rzp.open();
                    },
                    getCsrfToken() {
                        return document.querySelector('meta[name="csrf-token"]')?.content || '';
                    }
                }
            }
        </script>
        <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="bg-gray-50">
    <div x-data="premiumPlans()" x-cloak>
        <!-- Shared Header -->
        <?php $base = $base ?? '/'; require __DIR__ . '/../../include/header.php'; ?>

        <!-- Hero Section with Gradient Background -->
        <div class="bg-gradient-to-br from-blue-50 via-blue-50 to-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row items-center justify-between gap-12">
                    <div class="flex-1 text-center lg:text-left">
                        <div class="flex items-center justify-center lg:justify-start gap-3 mb-4">
                            <h1 class="text-5xl sm:text-6xl font-bold text-gray-900">Mindware Pro</h1>
                            <svg class="w-12 h-12 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        </div>
                        <p class="text-2xl text-gray-700 mb-8 font-medium">Be seen. Be prepared. Be ahead.</p>
                        <button @click="scrollToPlans()" class="px-8 py-4 bg-blue-600 text-white text-lg font-semibold rounded-lg hover:bg-blue-700 shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                            Get Mindware Pro
                        </button>
                    </div>
                    <div class="flex-1 hidden lg:flex items-center justify-center">
                        <!-- Hero Image -->
                        <div class="relative w-full max-w-lg">
                            <img src="/uploads/mindware-hero.png" alt="Mindware Pro Features" class="w-full h-auto object-contain">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Premium Status Banner -->
            <?php if ($candidate->isPremium()): ?>
            <div class="bg-blue-50 border-2 border-blue-300 rounded-xl p-6 mb-8 text-center">
                <div class="flex items-center justify-center gap-3 mb-2">
                    <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    <h2 class="text-2xl font-bold text-blue-900">You're a Premium Member!</h2>
                </div>
                <p class="text-blue-800 font-medium">
                    Premium expires: <?= date('M d, Y', strtotime($candidate->attributes['premium_expires_at'] ?? 'now')) ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="bg-white rounded-xl shadow-md p-8 mb-16">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex items-start gap-6">
                        <div class="flex-shrink-0">
                            <img src="/uploads/cl-bulb.svg" alt="Lightbulb Icon" class="w-24 h-24">
                        </div>
                        <div class="flex-1 pt-1">
                            <div class="text-6xl font-bold text-blue-600 mb-3">70%</div>
                            <p class="text-base text-gray-700 leading-relaxed">
                                <span class="font-medium">of recruiters start hiring with profile searches.</span> 
                                <span class="text-gray-600">Be seen with Mindware Pro.</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-6">
                        <div class="flex-1 pt-1">
                            <div class="text-6xl font-bold text-blue-600 mb-3">80%</div>
                            <p class="text-base text-gray-700 leading-relaxed">
                                <span class="font-medium">of users got more job invitations</span> 
                                <span class="text-gray-600">in their first month with Mindware Pro.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feature Comparison Table -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">What you will get</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b-2 border-gray-200">
                                <th class="text-left py-4 px-4 text-lg font-semibold text-gray-900">Features</th>
                                <th class="text-center py-4 px-4 text-lg font-semibold text-gray-900">Current</th>
                                <th class="text-center py-4 px-4 text-lg font-semibold text-gray-900">
                                    <div class="flex items-center justify-center gap-2">
                                        <span>Mindware Pro</span>
                                        <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $features = [
                                'Top profile visibility to recruiters',
                                'Higher search ranking in results',
                                'Priority in job recommendations',
                                'Verified profile badge',
                                'Unlimited job applications',
                                'Advanced analytics dashboard',
                                'Priority customer support',
                                'Hidden job opportunities',
                                'AI-powered profile enhancement',
                                'Resume builder & templates'
                            ];
                            foreach ($features as $feature): ?>
                            <tr class="border-b border-gray-100">
                                <td class="py-4 px-4 text-gray-700 font-medium"><?= htmlspecialchars($feature) ?></td>
                                <td class="py-4 px-4 text-center text-gray-400 text-2xl">—</td>
                                <td class="py-4 px-4 text-center">
                                    <svg class="w-6 h-6 text-blue-600 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Feature Cards Section -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Become a pro. In every move you make.</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Feature Card 01 -->
                    <div class="bg-blue-50 rounded-xl p-6 border-2 border-blue-100 relative">
                        <div class="text-7xl font-bold text-blue-600 mb-4">01</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4 border-b-2 border-blue-500 pb-2 inline-block">
                            Get your AI Job Agent
                        </h3>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700">Real time job alerts for top jobs</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700">Auto-applies on highly relevant jobs</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Feature Card 02 -->
                    <div class="bg-blue-50 rounded-xl p-6 border-2 border-blue-100 relative">
                        <div class="text-7xl font-bold text-blue-600 mb-4">02</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4 border-b-2 border-blue-500 pb-2 inline-block">
                            Power up your profile with ✨AI
                        </h3>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700">Enhance your headline, summary, & more</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700">Get an upgraded professional picture</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Feature Card 03 -->
                    <div class="bg-blue-50 rounded-xl p-6 border-2 border-blue-100 relative">
                        <div class="text-7xl font-bold text-blue-600 mb-4">03</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4 border-b-2 border-blue-500 pb-2 inline-block">
                            Get invitations that others miss
                        </h3>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700">Never miss out on relevant opportunities</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700">Stay ahead with invitations meant for you</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Pricing Plans Section -->
            <div id="pricing-plans" class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Choose Your Plan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($plans as $index => $plan): ?>
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden border-2 transition-all hover:shadow-xl <?= strpos($plan['id'], 'premium') !== false ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-200 hover:border-blue-300' ?>">
                        <?php if (strpos($plan['id'], 'premium') !== false): ?>
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white text-center py-2.5 text-sm font-bold">
                            MOST POPULAR
                        </div>
                        <?php endif; ?>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-3"><?= htmlspecialchars($plan['name']) ?></h3>
                            <div class="mb-4">
                                <span class="text-4xl font-bold text-gray-900">₹<?= number_format($plan['price']) ?></span>
                                <?php if (strpos($plan['id'], 'yearly') !== false): ?>
                                <div class="mt-1">
                                    <span class="text-gray-500 line-through text-lg">₹<?= number_format($plan['price'] * 12) ?></span>
                                    <span class="ml-2 text-blue-600 font-semibold">Save ₹<?= number_format(($plan['price'] * 12) - $plan['price']) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <ul class="space-y-3 mb-6">
                                <?php foreach ($plan['features'] as $feature): ?>
                                <li class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-sm text-gray-700"><?= htmlspecialchars($feature) ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <button @click="selectPlan('<?= $plan['id'] ?>')" 
                                    class="w-full px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 font-semibold shadow-md hover:shadow-lg transition-all transform hover:scale-105">
                                Choose Plan
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-16">
                <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Frequently asked questions</h2>
                <div class="max-w-3xl mx-auto space-y-4">
                    <?php 
                    $faqs = [
                        [
                            'q' => 'What is Mindware Pro?',
                            'a' => 'Mindware Pro brings together everything you need to enhance your profile, build a strong resume, prepare for interviews, and access hidden job opportunities. All in one place.'
                        ],
                        [
                            'q' => 'Who can benefit from Mindware Pro?',
                            'a' => 'Anyone looking for better job opportunities, career growth, or want to stand out to recruiters. Whether you\'re actively job searching or just keeping your options open, Mindware Pro gives you the competitive edge.'
                        ],
                        [
                            'q' => 'How long is Mindware Pro valid?',
                            'a' => 'You can choose from 7-day, 30-day, or yearly plans. Premium membership plans renew automatically unless cancelled. Profile boost plans are one-time purchases for the selected duration.'
                        ],
                        [
                            'q' => 'Can I cancel my subscription anytime?',
                            'a' => 'Yes, you can cancel your subscription at any time. Your premium benefits will remain active until the end of your current billing period.'
                        ],
                        [
                            'q' => 'What payment methods do you accept?',
                            'a' => 'We accept all major credit cards, debit cards, and UPI through Razorpay. All payments are secure and encrypted.'
                        ],
                        [
                            'q' => 'Do I get a refund if I cancel?',
                            'a' => 'We offer a 7-day money-back guarantee for new subscriptions. After that, cancellation takes effect at the end of your current billing cycle.'
                        ]
                    ];
                    foreach ($faqs as $index => $faq): ?>
                    <div x-data="{ open: <?= $index === 0 ? 'true' : 'false' ?> }" class="border-b border-gray-200">
                        <button @click="open = !open" class="w-full py-4 flex items-center justify-between text-left">
                            <span class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($faq['q']) ?></span>
                            <svg class="w-5 h-5 text-gray-500 transform transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform -translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform -translate-y-2"
                             class="pb-4 text-gray-700"
                             style="display: none;">
                            <?= htmlspecialchars($faq['a']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Final CTA -->
            <div class="text-center">
                <button @click="scrollToPlans()" class="px-12 py-4 bg-blue-600 text-white text-lg font-semibold rounded-lg hover:bg-blue-700 shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                    Get Mindware Pro
                </button>
            </div>
        </div>

        <!-- Payment Modal -->
        <div x-show="showPaymentModal" 
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             @click.self="showPaymentModal = false">
            <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
                <h2 class="text-2xl font-bold mb-6">Complete Payment</h2>
                <div class="space-y-4">
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-gray-500">Plan</div>
                                <div id="planNamePreview" class="text-gray-900 font-semibold" x-text="selectedPlanDetails ? selectedPlanDetails.name : '—'"></div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500">Amount</div>
                                <div id="amountPreview" class="text-gray-900 font-semibold" x-text="selectedPlanDetails ? ('₹' + (selectedPlanDetails.price).toLocaleString('en-IN')) : '—'"></div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Test mode: Use card <span class="font-mono">4111 1111 1111 1111</span>, CVV <span class="font-mono">123</span>, OTP <span class="font-mono">123456</span>.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <select x-model="paymentMethod" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                            <option value="razorpay">Razorpay</option>
                            <option value="stripe">Stripe</option>
                            <option value="paypal">PayPal</option>
                        </select>
                    </div>
                    <div class="pt-4 flex gap-3">
                        <button @click="showPaymentModal = false" 
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                        <button @click="processPayment()" 
                                :disabled="isProcessing"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="!isProcessing" id="payBtnText" x-text="selectedPlanDetails ? ('Pay ₹' + (selectedPlanDetails.price).toLocaleString('en-IN')) : 'Pay Now'"></span>
                            <span x-show="isProcessing">Processing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
    <?php include __DIR__ . '/../../include/footer.php'; ?>
</body>
</html>
