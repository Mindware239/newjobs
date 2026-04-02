<?php

declare(strict_types=1);

return [
    'app_id' => $_ENV['CASHFREE_APP_ID'] ?? '',
    'secret_key' => $_ENV['CASHFREE_SECRET_KEY'] ?? '',
    'environment' => $_ENV['CASHFREE_ENVIRONMENT'] ?? 'sandbox',
    'api_version' => $_ENV['CASHFREE_API_VERSION'] ?? '2023-08-01',
    'base_url' => ($_ENV['CASHFREE_ENVIRONMENT'] === 'production') 
        ? 'https://api.cashfree.com/pg/' 
        : 'https://sandbox.cashfree.com/pg/'
];
