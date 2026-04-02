<div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="bg-white p-8 rounded-lg shadow-md text-center">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Redirecting to Payment Gateway</h2>
        <p class="text-gray-600 mb-8">Please wait while we prepare your secure checkout with Cashfree.</p>
        
        <div class="flex justify-center items-center mb-8">
            <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        
        <p class="text-sm text-gray-500">If you are not redirected automatically, <a href="#" id="manual-checkout" class="text-blue-600 hover:underline">click here</a>.</p>
    </div>
</div>

<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cashfree = Cashfree({
            mode: "<?= $environment === 'production' ? 'production' : 'sandbox' ?>"
        });

        const paymentSessionId = "<?= $payment_session_id ?>";
        const orderId = "<?= $order_id ?>";

        function startCheckout() {
            cashfree.checkout({
                paymentSessionId: paymentSessionId,
                redirectTarget: "_self"
            });
        }

        document.getElementById('manual-checkout').addEventListener('click', function(e) {
            e.preventDefault();
            startCheckout();
        });

        // Automatically start checkout
        setTimeout(startCheckout, 1000);
    });
</script>
