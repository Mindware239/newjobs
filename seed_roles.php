<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;

// Initialize Database
$db = Database::getInstance();

$roles = [
    [
        'name' => 'Super Admin',
        'slug' => 'super_admin',
        'description' => 'Full access to all system features'
    ],
    [
        'name' => 'Admin',
        'slug' => 'admin',
        'description' => 'Administrative access with some restrictions'
    ],
    [
        'name' => 'Sales Manager',
        'slug' => 'sales_manager',
        'description' => 'Manage sales team, targets, and campaigns'
    ],
    [
        'name' => 'Sales Executive',
        'slug' => 'sales_executive',
        'description' => 'Manage leads and personal targets'
    ],
    [
        'name' => 'Employer',
        'slug' => 'employer',
        'description' => 'Employer account'
    ],
    [
        'name' => 'Candidate',
        'slug' => 'candidate',
        'description' => 'Candidate account'
    ]
];

echo "Seeding roles...\n";

foreach ($roles as $role) {
    // Check if role exists
    $existing = $db->fetchOne("SELECT id FROM roles WHERE slug = :slug", ['slug' => $role['slug']]);
    
    if (!$existing) {
        $db->query(
            "INSERT INTO roles (name, slug, description, created_at) VALUES (:name, :slug, :desc, NOW())",
            [
                'name' => $role['name'],
                'slug' => $role['slug'],
                'desc' => $role['description']
            ]
        );
        echo "Created role: {$role['name']}\n";
    } else {
        echo "Role exists: {$role['name']}\n";
    }
}

echo "Role seeding completed.\n";
