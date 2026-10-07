<?php

return [
    'client_id' => $_ENV['GOOGLE_CLIENT_ID'] ?? '',
    'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? '',
    // Back to the site the visitor is on (the old GOOGLE_REDIRECT_URI pointed at mindwareinfotech.com).
    'redirect_uri' => \App\Helpers\OAuthRedirect::uri('google'),
    'scopes' => [
        'openid',
        'profile',
        'email'
    ]
];

