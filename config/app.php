<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'Job Portal',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'debug' => $_ENV['APP_DEBUG'] === 'true',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Asia/Kolkata',
    'locale' => $_ENV['APP_LOCALE'] ?? 'en',
];

