<?php

return [
    'host' => $_ENV['REDIS_HOST'] ?? 'localhost',
    'port' => (int)($_ENV['REDIS_PORT'] ?? 6379),
    'password' => $_ENV['REDIS_PASSWORD'] ?? null,
    'database' => (int)($_ENV['REDIS_DB'] ?? 0),
];

