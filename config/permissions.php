<?php

return [
    'master' => [
        'dashboard.view',
        'roles.manage',
        'users.manage',
        'sales.manage',
        'verification.manage',
        'employers.manage',
        'candidates.manage',
        'subscriptions.manage',
        'payments.manage',
        'reports.view',
        'settings.manage',
        'impersonate.user',
    ],

    'sales_manager' => [
        'sales.view',
        'sales.assign_leads',
        'sales.view_reports',
    ],

    'sales_executive' => [
        'sales.view_my_leads',
        'sales.update_lead',
    ],

    'verification_manager' => [
        'verification.view_all',
        'verification.assign',
    ],

    'verification_executive' => [
        'verification.view_assigned',
        'verification.update_status',
    ],
];
