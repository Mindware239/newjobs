<?php
$title = 'Payment Methods';
?>

<div x-data="paymentMethodsManager()" class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Payment Methods</h2>
        <p class="mt-1 text-sm text-gray-500">Manage your cards and UPI IDs for seamless billing.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Sidebar: Saved Methods -->
        <div class="lg:col-span-1">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                Your Saved Methods
            </h3>
            
            <div class="space-y-4">
                <?php if (empty($methods)): ?>
                    <div class="bg-gray-50 border-2 border-dashed rounded-2xl p-8 text-center">
                        <p class="text-gray-400 text-sm">No payment methods saved yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($methods as $m): ?>
                        <div class="bg-white border rounded-2xl p-4 shadow-sm transition-all hover:shadow-md relative group <?= $m['is_default'] ? 'border-indigo-500 ring-1 ring-indigo-100' : 'border-gray-200' ?>">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center border border-gray-100">
                                        <?php if ($m['method_type'] === 'card'): ?>
                                            <span class="text-[10px] font-black text-gray-400"><?= strtoupper($m['brand'] ?? 'CARD') ?></span>
                                        <?php else: ?>
                                            <span class="text-[10px] font-black text-indigo-600">UPI</span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900"><?= htmlspecialchars($m['label']) ?></p>
                                        <p class="text-[11px] text-gray-500"><?= htmlspecialchars($m['details']) ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <?php if (!$m['is_default']): ?>
                                        <button @click="setDefault(<?= $m['id'] ?>)" class="p-1.5 text-gray-400 hover:text-indigo-600" title="Set Default">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </button>
                                    <?php endif; ?>
                                    <button @click="deleteMethod(<?= $m['id'] ?>)" class="p-1.5 text-gray-400 hover:text-red-500" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M6 7h12m-5-4h-2a2 2 0 00-2 2v2h6V5a2 2 0 00-2-2z"></path></svg>
                                    </button>
                                </div>
                            </div>
                            <?php if ($m['is_default']): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700">DEFAULT</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Content: Add New Method -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-3xl shadow-xl shadow-indigo-100/50 border border-gray-100 overflow-hidden">
                <!-- Tab Headers -->
                <div class="flex bg-gray-50/50 p-1.5 gap-1">
                    <button @click="type = 'card'" :class="type === 'card' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:bg-gray-100'" class="flex-1 py-3 text-sm font-bold rounded-2xl transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        Card
                    </button>
                    <button @click="type = 'upi'" :class="type === 'upi' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:bg-gray-100'" class="flex-1 py-3 text-sm font-bold rounded-2xl transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        UPI ID
                    </button>
                </div>

                <div class="p-8">
                    <form @submit.prevent="saveMethod()">
                        <!-- Card Workflow -->
                        <div x-show="type === 'card'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" class="space-y-8">
                            
                            <!-- Interactive Card Preview -->
                            <div class="relative max-w-sm mx-auto h-52 rounded-[24px] p-8 text-white shadow-2xl transition-all duration-700 transform perspective-1000 overflow-hidden"
                                 :class="getCardTheme()">
                                <div class="absolute inset-0 opacity-10 pointer-events-none bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
                                <div class="relative z-10 h-full flex flex-col justify-between">
                                    <div class="flex justify-between items-start">
                                        <div class="w-14 h-10 bg-gradient-to-br from-yellow-200 to-yellow-500 rounded-lg shadow-inner flex items-center justify-center">
                                            <div class="w-10 h-0.5 bg-yellow-700/20 mb-1"></div>
                                        </div>
                                        <div class="h-10 flex items-center" x-html="getBrandIcon()"></div>
                                    </div>
                                    
                                    <div class="text-2xl font-mono tracking-[0.15em] drop-shadow-lg" x-text="formatPreviewNumber()"></div>
                                    
                                    <div class="flex justify-between items-end">
                                        <div class="flex-1 truncate mr-4">
                                            <p class="text-[9px] uppercase tracking-widest opacity-60 mb-1">Card Holder</p>
                                            <p class="text-sm font-bold uppercase tracking-tight" x-text="cardName || 'FULL NAME'"></p>
                                        </div>
                                        <div class="text-right whitespace-nowrap">
                                            <p class="text-[9px] uppercase tracking-widest opacity-60 mb-1">Expires</p>
                                            <p class="text-sm font-mono font-bold" x-text="cardExpiry || 'MM/YY'"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Inputs -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                                <div class="md:col-span-4">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Card Number</label>
                                    <div class="relative">
                                        <input type="text" x-model="cardNumber" @input="handleCardInput" maxlength="19"
                                               :class="errors.cardNumber ? 'border-red-300 ring-red-50' : 'border-gray-200 focus:ring-indigo-100 focus:border-indigo-500'"
                                               class="w-full px-5 py-4 bg-gray-50/50 border rounded-2xl transition-all font-mono text-lg tracking-wider outline-none focus:ring-4"
                                               placeholder="0000 0000 0000 0000">
                                        <div class="absolute right-5 top-1/2 -translate-y-1/2" x-html="getBrandIcon()"></div>
                                    </div>
                                    <p x-show="errors.cardNumber" class="text-[11px] text-red-500 mt-2 font-bold flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                        <span x-text="errors.cardNumber"></span>
                                    </p>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Expiry Date</label>
                                    <input type="text" x-model="cardExpiry" @input="handleExpiryInput" maxlength="5"
                                           :class="errors.cardExpiry ? 'border-red-300 ring-red-50' : 'border-gray-200 focus:ring-indigo-100 focus:border-indigo-500'"
                                           class="w-full px-5 py-4 bg-gray-50/50 border rounded-2xl transition-all font-mono outline-none focus:ring-4"
                                           placeholder="MM / YY">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">CVV / CVC</label>
                                    <input type="password" x-model="cardCvv" maxlength="4" @input="validate('cardCvv')"
                                           :class="errors.cardCvv ? 'border-red-300 ring-red-50' : 'border-gray-200 focus:ring-indigo-100 focus:border-indigo-500'"
                                           class="w-full px-5 py-4 bg-gray-50/50 border rounded-2xl transition-all font-mono outline-none focus:ring-4"
                                           placeholder="•••">
                                </div>

                                <div class="md:col-span-4">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Cardholder Name</label>
                                    <input type="text" x-model="cardName" @input="validate('cardName')"
                                           class="w-full px-5 py-4 bg-gray-50/50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all uppercase font-bold outline-none"
                                           placeholder="AS SHOWN ON CARD">
                                </div>
                            </div>
                        </div>

                        <!-- UPI Workflow -->
                        <div x-show="type === 'upi'" x-transition x-cloak class="space-y-6">
                            <div class="p-6 rounded-3xl bg-indigo-50/50 border border-indigo-100 flex items-center gap-5">
                                <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center shrink-0 shadow-lg shadow-indigo-200">
                                    <span class="text-white font-black tracking-tighter text-sm">UPI</span>
                                </div>
                                <div>
                                    <h4 class="text-indigo-900 font-black text-sm uppercase tracking-wide">Instant Verification</h4>
                                    <p class="text-indigo-700/60 text-xs mt-1 leading-relaxed">Your UPI ID will be verified instantly. Supports all major apps like GPay, PhonePe, and Paytm.</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">UPI ID / VPA</label>
                                <input type="text" x-model="upiId" @input="validate('upiId')"
                                       :class="errors.upiId ? 'border-red-300 ring-red-50' : 'border-gray-200 focus:ring-indigo-100 focus:border-indigo-500'"
                                       class="w-full px-5 py-4 bg-gray-50/50 border rounded-2xl transition-all font-bold outline-none focus:ring-4"
                                       placeholder="username@bank">
                                <p x-show="errors.upiId" class="text-[11px] text-red-500 mt-2 font-bold" x-text="errors.upiId"></p>
                            </div>
                        </div>

                        <!-- Submit Section -->
                        <div class="mt-10 pt-8 border-t border-gray-50 flex items-center justify-between">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative flex items-center">
                                    <input type="checkbox" x-model="isDefault" class="peer h-5 w-5 cursor-pointer appearance-none rounded-lg border-2 border-gray-200 transition-all checked:border-indigo-600 checked:bg-indigo-600">
                                    <svg class="absolute h-5 w-5 text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </div>
                                <span class="text-sm font-bold text-gray-500 group-hover:text-gray-900 transition-colors">Default Method</span>
                            </label>

                            <button type="submit" 
                                    :disabled="loading || !canSubmit()"
                                    class="relative px-10 py-4 bg-indigo-600 text-white rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-indigo-700 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-30 disabled:cursor-not-allowed transition-all shadow-xl shadow-indigo-200 flex items-center gap-3">
                                <span x-show="loading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                                <span x-text="loading ? 'Processing...' : 'Save Method'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function paymentMethodsManager() {
    return {
        type: 'card',
        loading: false,
        cardNumber: '',
        cardExpiry: '',
        cardCvv: '',
        cardName: '',
        upiId: '',
        isDefault: false,
        brand: '',
        errors: {},

        handleCardInput(e) {
            let val = e.target.value.replace(/\D/g, '');
            let formatted = '';
            for (let i = 0; i < val.length; i++) {
                if (i > 0 && i % 4 === 0) formatted += ' ';
                formatted += val[i];
            }
            this.cardNumber = formatted;
            this.detectBrand(val);
            this.validate('cardNumber');
        },

        formatPreviewNumber() {
            if (!this.cardNumber) return '•••• •••• •••• ••••';
            const raw = this.cardNumber.replace(/\s/g, '');
            const visible = raw.slice(-4);
            const masked = '•••• •••• •••• '.slice(0, Math.max(0, this.cardNumber.length - 5));
            return (masked + visible).padEnd(19, '•');
        },

        handleExpiryInput(e) {
            let val = e.target.value.replace(/\D/g, '');
            if (val.length >= 2) {
                this.cardExpiry = val.slice(0, 2) + '/' + val.slice(2, 4);
            } else {
                this.cardExpiry = val;
            }
            this.validate('cardExpiry');
        },

        detectBrand(num) {
            if (/^4/.test(num)) this.brand = 'visa';
            else if (/^5[1-5]|^2[2-7]/.test(num)) this.brand = 'mastercard';
            else if (/^3[47]/.test(num)) this.brand = 'amex';
            else if (/^60|^65|^81/.test(num)) this.brand = 'rupay';
            else this.brand = '';
        },

        getCardTheme() {
            const themes = {
                'visa': 'bg-gradient-to-br from-blue-600 to-indigo-900',
                'mastercard': 'bg-gradient-to-br from-red-500 to-orange-700',
                'amex': 'bg-gradient-to-br from-emerald-500 to-teal-800',
                'rupay': 'bg-gradient-to-br from-indigo-700 to-purple-900'
            };
            return themes[this.brand] || 'bg-gradient-to-br from-gray-700 to-gray-900';
        },

        getBrandIcon() {
            if (!this.brand) return '';
            const labels = { 'visa': 'VISA', 'mastercard': 'MasterCard', 'amex': 'AMEX', 'rupay': 'RuPay' };
            return `<span class="text-xs font-black italic tracking-tighter text-white/90 drop-shadow-sm">${labels[this.brand]}</span>`;
        },

        validate(field) {
            this.errors[field] = '';
            const rawCard = this.cardNumber.replace(/\s/g, '');

            if (field === 'cardNumber' && rawCard) {
                if (rawCard.length < 13) this.errors.cardNumber = 'Number too short';
                else if (!this.luhnCheck(rawCard)) this.errors.cardNumber = 'Invalid card number';
            }

            if (field === 'cardExpiry' && this.cardExpiry.length === 5) {
                const [m, y] = this.cardExpiry.split('/');
                const month = parseInt(m);
                const year = 2000 + parseInt(y);
                const now = new Date();
                if (month < 1 || month > 12) this.errors.cardExpiry = 'Invalid month';
                else if (new Date(year, month, 0) < now) this.errors.cardExpiry = 'Expired';
            }

            if (field === 'upiId' && this.upiId) {
                if (!/^[\w.-]+@[\w.-]+$/.test(this.upiId)) this.errors.upiId = 'Invalid format';
            }
        },

        luhnCheck(num) {
            let sum = 0;
            for (let i = 0; i < num.length; i++) {
                let d = parseInt(num[num.length - 1 - i]);
                if (i % 2 === 1) {
                    d *= 2;
                    if (d > 9) d -= 9;
                }
                sum += d;
            }
            return sum % 10 === 0;
        },

        canSubmit() {
            if (this.type === 'card') {
                return this.cardNumber.length >= 15 && this.cardExpiry.length === 5 && 
                       this.cardCvv.length >= 3 && this.cardName.length > 2 && 
                       !Object.values(this.errors).some(e => e);
            }
            return /^[\w.-]+@[\w.-]+$/.test(this.upiId);
        },

        async saveMethod() {
            this.loading = true;
            try {
                const res = await fetch('/employer/billing/payment-methods', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
                    },
                    body: JSON.stringify({
                        method_type: this.type,
                        card_number: this.cardNumber,
                        card_expiry: this.cardExpiry,
                        card_name: this.cardName,
                        upi_id: this.upiId,
                        brand: this.brand,
                        set_default: this.isDefault ? '1' : '0'
                    })
                });
                
                if (res.ok) window.location.reload();
                else {
                    const data = await res.json().catch(() => ({}));
                    alert(data.error || data.message || 'Failed to save');
                }
            } catch (e) { alert('Network error'); }
            finally { this.loading = false; }
        },

        async deleteMethod(id) {
            if (!confirm('Delete this payment method?')) return;
            const res = await fetch(`/employer/billing/payment-methods/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
                }
            });
            if (res.ok) window.location.reload();
        },

        async setDefault(id) {
            const res = await fetch(`/employer/billing/payment-methods/${id}/default`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
                }
            });
            if (res.ok) window.location.reload();
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
.perspective-1000 { perspective: 1000px; }
</style>
