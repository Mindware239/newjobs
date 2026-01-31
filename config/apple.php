<?php

return [
    'client_id' => $_ENV['APPLE_CLIENT_ID'] ?? '',
    'team_id' => $_ENV['APPLE_TEAM_ID'] ?? '',
    'key_id' => $_ENV['APPLE_KEY_ID'] ?? '',
    'private_key' => $_ENV['APPLE_PRIVATE_KEY'] ?? '', // Can be file path or key content
    'redirect_uri' => $_ENV['APPLE_REDIRECT_URI'] ?? 'http://localhost:8000/auth/apple/callback',
    'scopes' => [
        'name',
        'email'
    ]
];

