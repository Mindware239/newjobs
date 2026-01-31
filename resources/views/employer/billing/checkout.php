<div class="p-6">
    <h1 class="text-2xl font-semibold mb-4">Pay with Razorpay</h1>
    <p class="text-gray-600 mb-2">Amount: ₹<?= number_format($amount, 2) ?></p>
    <p class="text-sm text-gray-600 mb-6">Use test card 4111 1111 1111 1111 (CVV 123, OTP 123456) or UPI success@razorpay.</p>
    <button id="payBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Pay Now</button>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('payBtn').addEventListener('click', function(){
    const options = {
        key: "<?= htmlspecialchars($key) ?>",
        amount: "<?= (int)round($amount * 100) ?>",
        currency: "INR",
        name: "<?= htmlspecialchars($_ENV['COMPANY_NAME'] ?? ($_ENV['APP_NAME'] ?? 'Job Portal')) ?>",
        description: "Subscription Payment",
        order_id: "<?= htmlspecialchars($orderId) ?>",
        prefill: {},
        notes: {
            emp_pay_id: "<?= (int)$empPayId ?>",
            subscription_payment_id: "<?= (int)$subscriptionPaymentId ?>"
        },
        handler: function (response) {
            fetch('/payment/verify', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= $csrfToken ?>'
                },
                body: JSON.stringify({
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature,
                    emp_pay_id: "<?= (int)$empPayId ?>",
                    subscription_payment_id: "<?= (int)$subscriptionPaymentId ?>",
                    _token: '<?= $csrfToken ?>'
                })
            }).then(r => {
                window.location.href = '/employer/billing/success?sub_pay_id=<?= (int)$subscriptionPaymentId ?>';
            }).catch(() => {
                window.location.href = '/employer/billing/failed?reason=verify_error';
            });
        }
    };
    const rzp = new Razorpay(options);
    rzp.on('payment.failed', function (resp) {
        const reason = resp.error && resp.error.description ? resp.error.description : 'Payment failed';
        window.location.href = '/employer/billing/failed?reason=' + encodeURIComponent(reason);
    });
    rzp.open();
});
</script>

