<?php
// Razorpay configuration (TEST MODE)
// Reads from environment; provides safe defaults for development

return [
    'key_id' => $_ENV['RAZORPAY_KEY'] ?? 'rzp_test_S0yTdO9ubSlPGO',
    'key_secret' => $_ENV['RAZORPAY_SECRET'] ?? '7RDtfrCoMZEr7eGoC7MSaVCG',
    'mode' => $_ENV['PAYMENT_MODE'] ?? 'test',
    'webhook_secret' => $_ENV['RAZORPAY_WEBHOOK_SECRET'] ?? 'test_webhook_secret',
    'app_url' => rtrim($_ENV['APP_URL'] ?? ('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')), '/'),
];

