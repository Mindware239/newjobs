<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout</title>
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://checkout.juspay.in/v2/checkout.js"></script>
</head>

<body class="bg-gray-50">

<!-- HEADER -->
<header class="bg-white border-b">
<div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

<div class="flex-shrink-0">
            <a href="<?php echo $base; ?>">
                <img src="uploads/Mindware-infotech.png" alt="Logo" class="h-10 md:h-14 w-auto">
            </a>
        </div>
 <nav class="flex gap-6 text-sm text-gray-700">
      <a href="/social-employer/newlisting">＋ Post a job</a>
      <a href="/social-employer/listings" class="text-red-500">Job listings</a>
      <a href="/social-employer/application">Applications</a>
      <a href="/social-employer/organisation"class="text-red-500">Organizations & users</a>
       <a href="/social-employer/account"class="text-red-500">Account & profile</a>
      <a href="/logout">Logout</a>
    </nav>

</div>
</header>

<!-- MAIN -->
<div class="max-w-7xl mx-auto px-8 py-12 grid grid-cols-1 md:grid-cols-3 gap-10"
     x-data="checkoutManager()">

<!-- ================= LEFT : ORDER SUMMARY ================= -->

<div class="md:col-span-2 bg-white rounded-xl shadow p-8">

<h2 class="text-xl font-semibold mb-6">Your Order</h2>

<table class="w-full text-sm">

<thead class="border-b">
<tr>
<th class="text-left py-2">Item</th>
<th class="text-center py-2">Qty</th>
<th class="text-right py-2">Total</th>
</tr>
</thead>

<tbody>

<template x-for="item in items" :key="item.name">
<tr class="border-b">
<td class="py-3" x-text="item.name"></td>
<td class="py-3 text-center" x-text="item.qty"></td>
<td class="py-3 text-right"
    x-text="'$' + (item.price * item.qty).toFixed(2)">
</td>
</tr>
</template>

<tr x-show="items.length === 0">
<td colspan="3" class="py-10 text-center text-gray-400 italic">
Your cart is empty
</td>
</tr>

<tr class="font-semibold">
<td class="py-4">Subtotal</td>
<td></td>
<td class="py-4 text-right" x-text="'$' + total().toFixed(2)"></td>
</tr>

<tr>
<td class="py-2">Shipping</td>
<td></td>
<td class="py-2 text-right text-green-600">Free</td>
</tr>

<tr class="text-lg font-bold border-t">
<td class="py-4">Total</td>
<td></td>
<td class="py-4 text-right" x-text="'$' + total().toFixed(2)"></td>
</tr>

</tbody>
</table>

</div>

<!-- ================= RIGHT : BILLING ================= -->

<div class="bg-white rounded-xl shadow p-8">

<h2 class="text-lg font-semibold mb-4">Billing Details</h2>

<form class="space-y-4" id="checkoutForm">

<div class="grid grid-cols-2 gap-4">
<input required placeholder="First name" class="border px-3 py-2 rounded w-full">
<input required placeholder="Last name" class="border px-3 py-2 rounded w-full">
</div>

<input required placeholder="Organization" class="border w-full px-3 py-2 rounded">

<input required placeholder="Phone" class="border w-full px-3 py-2 rounded">

<input required placeholder="Email address" class="border w-full px-3 py-2 rounded">

<hr class="my-4">

<h3 class="font-semibold mb-2">Payment</h3>

<div class="border rounded p-4 space-y-3">
<div class="flex items-center gap-4 text-sm">
    <label class="inline-flex items-center gap-2">
        <input type="radio" name="pay_method" value="card" checked>
        <span>Credit / Debit Card</span>
    </label>
    <label class="inline-flex items-center gap-2">
        <input type="radio" name="pay_method" value="upi">
        <span>UPI</span>
    </label>
</div>

<input placeholder="Card number" class="border w-full px-3 py-2 rounded">

<div class="grid grid-cols-2 gap-3">
<input placeholder="MM / YY" class="border px-3 py-2 rounded">
<input placeholder="CVC" class="border px-3 py-2 rounded">
</div>

<select class="border w-full px-3 py-2 rounded">
<option>India</option>
<option>USA</option>
<option>UK</option>
</select>

</div>

<div class="flex items-center gap-2 mt-3">
<input type="checkbox" required>
<p class="text-sm">I agree to terms & conditions</p>
</div>

<div class="grid grid-cols-2 gap-3 mt-4">
    <button 
    @click.prevent="payWithJuspay()"
    class="bg-indigo-600 hover:bg-indigo-700 text-white w-full py-3 rounded font-semibold">
    PAY WITH JUSPAY
    </button>
    <button 
    @click.prevent="placeOrder()"
    class="bg-red-500 hover:bg-red-600 text-white w-full py-3 rounded font-semibold">
    PLACE ORDER
    </button>
</div>

</form>

</div>

</div>

<!-- FOOTER -->
<footer class="bg-black text-white text-center py-10 mt-20">
<p class="text-lg font-semibold">Mindware Infotech</p>
<p class="text-sm mt-2">© 2025 Mindware Infotech</p>
</footer>

<!-- ================= SCRIPT ================= -->

<script>
function checkoutManager() {
    return {
        items: JSON.parse(localStorage.getItem('employer_cart') || '[]'),

        total() {
            return this.items.reduce((sum, item) => sum + (item.price * item.qty), 0);
        },

        async payWithJuspay() {
            const amount = this.total().toFixed(2);
            const res = await fetch('/social-employer/checkout/juspay/init', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ amount, plan: 'standard' })
            });
            const data = await res.json();
            if (!data || !data.client_auth_token) {
                alert('Payment setup failed');
                return;
            }
            if (window.JuspayCheckout && typeof window.JuspayCheckout.open === 'function') {
                window.JuspayCheckout.open(data.client_auth_token, {
                    onSuccess: async function() {
                        try {
                            await fetch('/social-employer/checkout/juspay/verify', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ order_id: data.order_id })
                            });
                            window.location.href = '/social-employer/newlisting?success=Subscription activated';
                        } catch (e) {
                            alert('Verification failed');
                        }
                    },
                    onFailure: function() {
                        alert('Payment failed');
                    }
                });
            } else {
                alert('Juspay script not available');
            }
        },

        placeOrder() {
            if (this.items.length === 0) {
                alert("Your cart is empty!");
                return;
            }

            const amount = this.total().toFixed(2);
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/social-employer/checkout/complete';
            const amt = document.createElement('input');
            amt.type = 'hidden'; amt.name = 'amount'; amt.value = amount;
            const plan = document.createElement('input');
            plan.type = 'hidden'; plan.name = 'plan'; plan.value = 'standard';
            form.appendChild(amt); form.appendChild(plan);
            document.body.appendChild(form);
            localStorage.removeItem('employer_cart');
            form.submit();
        }
    }
}
</script>

</body>
</html>
