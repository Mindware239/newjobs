<h1 class="text-3xl font-bold text-gray-900 mb-6">Billing Settings</h1>

<div class="bg-white p-6 rounded-lg shadow-md">
    <?php 
        $addrRaw = $employer->attributes['address'] ?? '';
        $addr = is_string($addrRaw) ? json_decode($addrRaw, true) : (is_array($addrRaw) ? $addrRaw : []);
        $street = is_array($addr) ? ($addr['street'] ?? '') : '';
        $city = $employer->attributes['city'] ?? ($addr['city'] ?? '');
        $state = $employer->attributes['state'] ?? ($addr['state'] ?? '');
        $postal = $employer->attributes['postal_code'] ?? ($addr['postal_code'] ?? '');
        $country = $employer->attributes['country'] ?? '';
    ?>
    <form id="billingForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-600 mb-1">Company</label>
            <input type="text" value="<?= htmlspecialchars($employer->attributes['company_name'] ?? '') ?>" class="px-3 py-2 border rounded-md w-full" disabled />
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Email</label>
            <input type="email" value="<?= htmlspecialchars($employer->user()->attributes['email'] ?? '') ?>" class="px-3 py-2 border rounded-md w-full" disabled />
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm text-gray-600 mb-1">Street Address</label>
            <input name="address" type="text" value="<?= htmlspecialchars($street) ?>" class="px-3 py-2 border rounded-md w-full" />
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">City</label>
            <input name="city" type="text" value="<?= htmlspecialchars($city) ?>" class="px-3 py-2 border rounded-md w-full" />
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">State</label>
            <input name="state" type="text" value="<?= htmlspecialchars($state) ?>" class="px-3 py-2 border rounded-md w-full" />
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Country</label>
            <input name="country" type="text" value="<?= htmlspecialchars($country) ?>" class="px-3 py-2 border rounded-md w-full" />
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Postal Code</label>
            <input name="postal_code" type="text" value="<?= htmlspecialchars($postal) ?>" class="px-3 py-2 border rounded-md w-full" />
        </div>

        <div class="md:col-span-2 flex items-center gap-3 mt-4">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-700">Save</button>
            <a href="/employer/billing/overview" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md">Back to Overview</a>
        </div>
    </form>

    <div id="msg" class="hidden mt-4 p-3 rounded-md"></div>
</div>

<script>
document.getElementById('billingForm').addEventListener('submit', async function(e){
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    const res = await fetch('/employer/payments/billing-info', { method: 'POST', body: data });
    const ok = res.ok;
    const msgEl = document.getElementById('msg');
    msgEl.classList.remove('hidden');
    msgEl.classList.toggle('bg-green-50', ok);
    msgEl.classList.toggle('text-green-800', ok);
    msgEl.classList.toggle('bg-red-50', !ok);
    msgEl.classList.toggle('text-red-800', !ok);
    msgEl.textContent = ok ? 'Billing information saved' : 'Failed to save billing information';
});
</script>
