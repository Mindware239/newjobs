<?php

return [
    'host' => $_ENV['ES_HOST'] ?? 'localhost',
    'port' => (int)($_ENV['ES_PORT'] ?? 9200),
    'username' => $_ENV['ES_USERNAME'] ?? null,
    'password' => $_ENV['ES_PASSWORD'] ?? null,
    'index_prefix' => $_ENV['ES_INDEX_PREFIX'] ?? 'jobportal',
];

