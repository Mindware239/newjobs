<?php

return [
    'guards' => [
        'master' => [
            'session_key' => 'masteradmin_id',
        ],
        'admin' => [
            'session_key' => 'admin_id',
        ],
        'employer' => [
            'session_key' => 'employer_id',
        ],
        'candidate' => [
            'session_key' => 'candidate_id',
        ],
    ],
];
