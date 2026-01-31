<h1 class="text-3xl font-bold text-gray-900 mb-6">Payment Methods</h1>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">Saved Methods</h2>
        <?php if (!empty($methods)): ?>
        <ul class="space-y-3">
            <?php foreach ($methods as $m): ?>
            <?php $isDefault = (bool)($m['is_default'] ?? false); ?>
            <li class="p-4 border rounded-lg flex items-center justify-between bg-white shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-md bg-blue-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 11h14a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a2 2 0 012-2z"></path></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900"><?= htmlspecialchars($m['label'] ?? 'Method') ?></p>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($m['details'] ?? '') ?></p>
                    </div>
                    <?php if ($isDefault): ?>
                                <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800 inline-flex items-center gap-1">   
                            <svg class="w-4 h-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927l.951 1.928 2.129.31-1.54 1.5.364 2.121-1.904-1-1.904 1 .364-2.121-1.54-1.5 2.129-.31.951-1.928z"></path></svg>
                            Default
                        </span>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-2">
                    <button class="px-3 py-1.5 text-sm bg-blue-50 text-blue-700 rounded-md inline-flex items-center gap-1">
                        <svg class="w-4 h-4 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927l.951 1.928 2.129.31-1.54 1.5.364 2.121-1.904-1-1.904 1 .364-2.121-1.54-1.5 2.129-.31.951-1.928z"></path></svg>
                        Set as Default
                    </button>
                    <button class="px-3 py-1.5 text-sm bg-gray-50 text-gray-700 rounded-md inline-flex items-center gap-1">
                        <svg class="w-4 h-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L7.5 20.5H4v-3.5L16.732 3.732z"></path></svg>
                        Edit
                    </button>
                    <button class="px-3 py-1.5 text-sm bg-gray-50 text-blue-600 rounded-md inline-flex items-center gap-1">
                        <svg class="w-4 h-4 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M6 7h12m-5-4h-2a2 2 0 00-2 2v2h6V5a2 2 0 00-2-2z"></path></svg>
                        Remove
                    </button>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <div class="p-6 border rounded-lg bg-blue-50 text-center">
            <svg class="mx-auto w-12 h-12 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 11h14a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a2 2 0 012-2z"></path></svg>
            <p class="mt-2 text-blue-600">No payment methods saved.</p>
        </div>
        <?php endif; ?>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">Add New Method</h2>
        <?php if (!empty($message)): ?><div class="p-3 mb-4 bg-green-50 text-green-700 border border-green-200 rounded-md"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <form id="payment-method-form" method="POST" action="/employer/billing/payment-methods" class="space-y-4">
            <input type="hidden" name="_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>" />

            <div x-data="{ type: 'card' }" class="space-y-4">
                <div class="flex items-center gap-2 bg-blue-50 rounded-md p-1">
                    <button type="button" @click="type='card'" :class="type==='card' ? 'px-3 py-1.5 rounded-md bg-white border border-blue-200 text-gray-900 inline-flex items-center gap-2' : 'px-3 py-1.5 rounded-md text-gray-600 inline-flex items-center gap-2'">
                        <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 11h14a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a2 2 0 012-2z"></path></svg>
                        Card
                    </button>
                    <button type="button" @click="type='upi'" :class="type==='upi' ? 'px-3 py-1.5 rounded-md bg-white border border-gray-200 text-gray-900 inline-flex items-center gap-2' : 'px-3 py-1.5 rounded-md text-gray-600 inline-flex items-center gap-2'">
                        <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 2h6a2 2 0 012 2v16a2 2 0 01-2 2H9a2 2 0 01-2-2V4a2 2 0 012-2zM12 18h.01"></path></svg>
                        UPI
                    </button>
                    <button type="button" @click="type='netbanking'" :class="type==='netbanking' ? 'px-3 py-1.5 rounded-md bg-white border border-gray-200 text-gray-900 inline-flex items-center gap-2' : 'px-3 py-1.5 rounded-md text-gray-600 inline-flex items-center gap-2'">
                        <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9-4 9 4-9 4-9-4"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21h16"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 10h16"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 14h16"></path>
                        </svg>
                        Netbanking
                    </button>
                    <input type="hidden" name="method_type" :value="type">
                </div>

                <div x-show="type==='card'" class="space-y-4" x-cloak>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Card number</label>
                        <div class="relative">
                            <svg class="w-5 h-5 text-blue-500 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 11h14a2 2 0 012 2v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a2 2 0 012-2z"></path></svg>
                            <input id="card-number" name="card_number" type="text" class="w-full pl-10 pr-14 py-2 border rounded-md focus:ring-2 focus:ring-[#dbeafe]" placeholder="1234 5678 9012 3456" />
                            <div id="card-brand" class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-600"></div>
                        </div>
                        <p id="card-number-error" class="text-xs text-red-600 mt-1 hidden">Invalid card number</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expiry date</label>
                            <div class="relative">
                                <svg class="w-5 h-5 text-blue-500 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zM3 11h18"></path></svg>
                                <input id="card-expiry" name="card_expiry" type="text" class="w-full pl-10 py-2 border rounded-md focus:ring-2 focus:ring-[#dbeafe]" placeholder="MM/YY" />
                            </div>
                            <p id="card-expiry-error" class="text-xs text-red-600 mt-1 hidden">Expired card</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Security code</label>
                            <div class="relative">
                                <svg class="w-5 h-5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11V7a4 4 0 10-8 0v4m16 0V7a4 4 0 10-8 0v4m-6 4h12a2 2 0 002-2V9H4v4a2 2 0 002 2z"></path></svg>
                                <input id="card-cvv" name="card_cvv" type="text" class="w-full pl-10 py-2 border rounded-md focus:ring-2 focus:ring-[#dbeafe]" placeholder="3 digits" />
                            </div>
                            <p class="text-xs text-gray-500 mt-1">3-digit code on the back of your card</p>
                            <p id="card-cvv-error" class="text-xs text-red-600 mt-1 hidden">Wrong CVV</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name on card</label>
                        <div class="relative">
                            <svg class="w-5 h-5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A7 7 0 1118.879 17.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <input id="card-name" name="card_name" type="text" class="w-full pl-10 py-2 border rounded-md focus:ring-2 focus:ring-[#dbeafe]" placeholder="John Doe" />
                        </div>
                        <p id="card-name-error" class="text-xs text-red-600 mt-1 hidden">This field is required</p>
                    </div>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="set_default" value="1">
                        <span class="text-sm text-gray-700">Set as default payment method</span>
                    </label>
                </div>

                <div x-show="type==='netbanking'" class="space-y-4" x-cloak>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Bank</label>
                        <div x-data="{ open:false, bank:'' }" class="relative">
                            <button type="button" @click="open=!open" class="w-full px-3 py-2 border rounded-md flex items-center justify-between">
                                <span class="flex items-center gap-2" x-text="bank || 'Choose bank'"></span>
                                <svg class="w-4 h-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <input type="hidden" id="netbanking-bank" name="netbanking_bank" :value="bank">
                            <div x-show="open" x-cloak class="absolute z-10 w-full mt-1 bg-white border rounded-md shadow">
                                <?php 
                                    $banks = ['HDFC','ICICI','SBI','Axis','Kotak']; 
                                    $logoMap = [
                                        'HDFC' => 'https://commons.wikimedia.org/wiki/Special:FilePath/HDFC_Bank_Logo.svg',
                                        'ICICI' => 'https://commons.wikimedia.org/wiki/Special:FilePath/ICICI_Bank_Logo.svg',
                                        'SBI' => 'https://commons.wikimedia.org/wiki/Special:FilePath/SBI-logo.svg',
                                        'Axis' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Axis_Bank_logo.svg',
                                        'Kotak' => 'https://en.wikipedia.org/wiki/Special:FilePath/Kotak_Mahindra_Group_logo.svg',
                                    ];
                                    foreach ($banks as $bk): 
                                ?>
                                <button type="button" @click="bank='<?= $bk ?>'; open=false" class="w-full text-left px-3 py-2 hover:bg-blue-50 flex items-center gap-2">
                                    <img src="<?= htmlspecialchars($logoMap[$bk]) ?>" alt="<?= $bk ?> logo" class="w-6 h-6 grayscale opacity-80 hover:opacity-100 transition" />
                                    <span><?= $bk ?> Bank</span>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <p id="netbanking-bank-error" class="text-xs text-red-600 mt-1 hidden">Please select a bank</p>
                    </div>
                </div>

                <div x-show="type==='upi'" class="space-y-4" x-cloak>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">UPI ID</label>
                        <div class="relative">
                            <input id="upi-id" name="upi_id" type="text" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#dbeafe]" placeholder="name@bank" />
                        </div>
                        <p id="upi-id-error" class="text-xs text-red-600 mt-1 hidden">Enter a valid UPI ID</p>
                        <div class="mt-3 flex items-center gap-4">
    <img src="https://commons.wikimedia.org/wiki/Special:FilePath/Google_Pay_Logo.svg"
         alt="Google Pay"
         class="w-12 h-12 grayscale opacity-80 hover:opacity-100 transition" />

    <img src="https://commons.wikimedia.org/wiki/Special:FilePath/PhonePe_Logo.svg"
         alt="PhonePe"
         class="w-12 h-12 grayscale opacity-80 hover:opacity-100 transition" />     

    <img src="https://commons.wikimedia.org/wiki/Special:FilePath/Paytm_Logo_(standalone).svg"
         alt="Paytm"
         class="w-12 h-12 grayscale opacity-80 hover:opacity-100 transition" />

    <img src="https://commons.wikimedia.org/wiki/Special:FilePath/BHIM_SVG_Logo.svg"
         alt="BHIM UPI"
         class="w-12 h-12 grayscale opacity-80 hover:opacity-100 transition" />
</div>

                    </div>
                </div>
            </div>

            <button id="save-method-btn" class="px-4 py-2 bg-[#eef2ff] text-gray-900 rounded-md hover:bg-[#e0e7ff]">Save Method</button>
            <p class="text-xs text-blue-600 mt-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l7 4v5a10 10 0 01-7 9 10 10 0 01-7-9V6l7-4"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path></svg>      
                Your payment details are encrypted and securely stored.
            </p>
            <p class="text-xs text-gray-500 mt-6">By saving a payment method, you agree to our Terms & Privacy Policy.</p>
        </form>
    </div>
</div>

<script>
// Brand detection
const brandEl = document.getElementById('card-brand');
const numberEl = document.getElementById('card-number');
const expiryEl = document.getElementById('card-expiry');
const cvvEl = document.getElementById('card-cvv');
const nameEl = document.getElementById('card-name');
const upiEl = document.getElementById('upi-id');
const bankEl = document.getElementById('netbanking-bank');
const formEl = document.getElementById('payment-method-form');
const saveBtn = document.getElementById('save-method-btn');

function detectBrand(num) {
    const n = (num || '').replace(/\s+/g, '');
    if (/^4\d{0,}$/.test(n)) return 'visa';
    if (/^(5[1-5]\d{0,}|2(2[2-9]\d{0,}|[3-6]\d{0,}|7[01]\d{0,}|720\d{0,}))$/.test(n)) return 'mastercard';
    if (/^(60|65|6521|81)\d{0,}$/.test(n)) return 'rupay';
    if (/^3[47]\d{0,}$/.test(n)) return 'amex';
    return '';
}
function luhnValid(num) {
    const s = (num || '').replace(/\s+/g, '');
    if (!/^\d{12,19}$/.test(s)) return false;
    let sum = 0, dbl = false;
    for (let i = s.length - 1; i >= 0; i--) {
        let d = parseInt(s[i], 10);
        if (dbl) {
            d *= 2;
            if (d > 9) d -= 9;
        }
        sum += d;
        dbl = !dbl;
    }
    return sum % 10 === 0;
}
function expiryValid(mmYY) {
    const m = (mmYY || '').trim();
    const match = /^(\d{2})\/(\d{2})$/.exec(m);
    if (!match) return false;
    const mm = parseInt(match[1], 10);
    const yy = parseInt(match[2], 10);
    if (mm < 1 || mm > 12) return false;
    const year = 2000 + yy;
    const now = new Date();
    const end = new Date(year, mm, 0); // end of month
    return end >= new Date(now.getFullYear(), now.getMonth(), 1);
}
function cvvValid(cvv) {
    return /^\d{3}$/.test(cvv || '');
}
function show(el, show) {
    if (!el) return;
    el.classList.toggle('hidden', !show);
}

numberEl?.addEventListener('input', () => {
    const brand = detectBrand(numberEl.value);
    const common = 'class="w-8 h-5 opacity-80"';
    let svg = '';
    if (brand === 'visa') {
        svg = `<svg ${common} viewBox="0 0 36 24" fill="none" stroke="currentColor"><rect x="2" y="4" width="32" height="16" rx="3"></rect><text x="6" y="16" fill="currentColor" font-size="8">VISA</text></svg>`;
    } else if (brand === 'mastercard') {
        svg = `<svg ${common} viewBox="0 0 36 24" fill="none" stroke="currentColor"><circle cx="14" cy="12" r="6"></circle><circle cx="22" cy="12" r="6"></circle></svg>`;
    } else if (brand === 'rupay') {
        svg = `<svg ${common} viewBox="0 0 36 24" fill="none" stroke="currentColor"><rect x="6" y="7" width="24" height="10" rx="2"></rect><path d="M10 12h16"></path></svg>`;
    } else if (brand === 'amex') {
        svg = `<svg ${common} viewBox="0 0 36 24" fill="none" stroke="currentColor"><rect x="4" y="6" width="28" height="12" rx="2"></rect><text x="8" y="15" fill="currentColor" font-size="6">AMEX</text></svg>`;
    }
    brandEl.innerHTML = svg;
});

formEl?.addEventListener('submit', (e) => {
    const type = document.querySelector('input[name=method_type]')?.value || 'card';
    let ok = true;
    // Reset errors
    show(document.getElementById('card-number-error'), false);
    show(document.getElementById('card-expiry-error'), false);
    show(document.getElementById('card-cvv-error'), false);
    show(document.getElementById('card-name-error'), false);
    show(document.getElementById('upi-id-error'), false);
    show(document.getElementById('netbanking-bank-error'), false);

    if (type === 'card') {
        if (!luhnValid(numberEl?.value)) { show(document.getElementById('card-number-error'), true); ok = false; }
        if (!expiryValid(expiryEl?.value)) { show(document.getElementById('card-expiry-error'), true); ok = false; }
        if (!cvvValid(cvvEl?.value)) { show(document.getElementById('card-cvv-error'), true); ok = false; }
        if (!nameEl?.value?.trim()) { show(document.getElementById('card-name-error'), true); ok = false; }
    } else if (type === 'upi') {
        const re = /^[a-zA-Z0-9.\-_]{2,}@[a-zA-Z]{2,}$/;
        if (!re.test(upiEl?.value || '')) { show(document.getElementById('upi-id-error'), true); ok = false; }
    } else if (type === 'netbanking') {
        if (!bankEl?.value) { show(document.getElementById('netbanking-bank-error'), true); ok = false; }
    }

    if (!ok) {
        e.preventDefault();
        return;
    }
    // Loading state
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<svg class="animate-spin h-5 w-5 inline-block mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0A12 12 0 000 12h4zm2 5.291A8 8 0 014 12H0a12 12 0 008 10.392V20z"></path></svg>Saving...';
});
</script>
