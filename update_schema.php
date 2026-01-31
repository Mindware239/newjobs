<?php

require_once __DIR__ . '/vendor/autoload.php'; // Assuming composer autoload
// If no vendor autoload, we might need manual requires. 
// Checking directory structure...
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    // Manually require core files if vendor not found (common in some legacy/custom setups)
    require_once __DIR__ . '/app/Core/Database.php';
    require_once __DIR__ . '/app/Core/Env.php'; // If exists
} else {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

use App\Core\Database;

try {
    $db = Database::getInstance();
    echo "Connected to database.\n";

    // 1. Create notification_templates
    $sqlTemplates = "CREATE TABLE IF NOT EXISTS notification_templates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_key VARCHAR(50) NOT NULL,
        channel VARCHAR(20) NOT NULL DEFAULT 'email',
        subject VARCHAR(255),
        content TEXT,
        variables TEXT COMMENT 'Comma separated list of available variables',
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_template (event_key, channel)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $db->query($sqlTemplates);
    echo "Created notification_templates table.\n";

    // 2. Create notification_triggers
    $sqlTriggers = "CREATE TABLE IF NOT EXISTS notification_triggers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_key VARCHAR(50) NOT NULL,
        conditions JSON,
        delay_minutes INT DEFAULT 0,
        action_type VARCHAR(50) DEFAULT 'send_notification',
        template_id INT,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $db->query($sqlTriggers);
    echo "Created notification_triggers table.\n";

    // 3. Update users table for preferences
    // Check if column exists first to avoid error
    try {
        $check = $db->fetchOne("SHOW COLUMNS FROM users LIKE 'notification_preferences'");
        if (!$check) {
            $db->query("ALTER TABLE users ADD COLUMN notification_preferences JSON DEFAULT NULL AFTER phone");
            echo "Added notification_preferences to users table.\n";
        } else {
            echo "notification_preferences column already exists.\n";
        }
    } catch (\Exception $e) {
        // Fallback if fetchOne fails on SHOW COLUMNS (PDO sometimes tricky with this)
        // Just try ADD and catch error
        try {
            $db->query("ALTER TABLE users ADD COLUMN notification_preferences JSON DEFAULT NULL AFTER phone");
            echo "Added notification_preferences to users table.\n";
        } catch (\Exception $ex) {
            echo "Column might already exist: " . $ex->getMessage() . "\n";
        }
    }

    // 4. Update notifications table to support multi-channel
    // Add channel, event, status, metadata if they don't exist
    $colsToAdd = [
        'channel' => "VARCHAR(20) DEFAULT 'in_app' AFTER type",
        'event' => "VARCHAR(50) DEFAULT NULL AFTER user_id", 
        'status' => "VARCHAR(20) DEFAULT 'unread' AFTER message", 
        'metadata' => "JSON DEFAULT NULL AFTER link"
    ];

    foreach ($colsToAdd as $col => $def) {
        try {
            $db->query("ALTER TABLE notifications ADD COLUMN $col $def");
            echo "Added $col to notifications table.\n";
        } catch (\Exception $e) {
            // Ignore if exists
            echo "Column $col might already exist in notifications.\n";
        }
    }

    // 4b. Ensure FCM token column exists on users for push notifications
    try {
        $existsFcm = $db->fetchOne("SHOW COLUMNS FROM users LIKE 'fcm_token'");
        if (!$existsFcm) {
            $db->query("ALTER TABLE users ADD COLUMN fcm_token VARCHAR(255) NULL AFTER apple_name");
            echo "Added fcm_token to users table.\n";
        } else {
            echo "fcm_token column already exists on users.\n";
        }
    } catch (\Exception $e) {
        try {
            $db->query("ALTER TABLE users ADD COLUMN fcm_token VARCHAR(255) NULL AFTER apple_name");
            echo "Added fcm_token to users table.\n";
        } catch (\Exception $ex) {
            echo "Could not add fcm_token (may already exist): " . $ex->getMessage() . "\n";
        }
    }

    // 4c. Create user_push_tokens table for multi-device support
    try {
        $col = $db->fetchOne("SHOW COLUMNS FROM users LIKE 'id'");
        $typeStr = strtolower((string)($col['Type'] ?? $col['type'] ?? 'int(11)'));
        $isBig = str_contains($typeStr, 'bigint');
        $isUnsigned = str_contains($typeStr, 'unsigned');
        $pkType = $isBig ? 'BIGINT UNSIGNED' : 'INT UNSIGNED';
        $fkType = $isBig ? 'BIGINT' : 'INT';
        if ($isUnsigned) { $fkType .= ' UNSIGNED'; }

        $sqlPushTokens = "
            CREATE TABLE IF NOT EXISTS user_push_tokens (
                id {$pkType} AUTO_INCREMENT PRIMARY KEY,
                user_id {$fkType} NOT NULL,
                token VARCHAR(255) NOT NULL,
                device VARCHAR(50) DEFAULT '',
                browser VARCHAR(50) DEFAULT '',
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                UNIQUE KEY unique_user_token (user_id, token),
                CONSTRAINT fk_user_push_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $db->query($sqlPushTokens);
        echo "Created user_push_tokens table.\n";
    } catch (\Exception $e) {
        echo "user_push_tokens table creation error: " . $e->getMessage() . "\n";
    }

    // 5. Create notification_logs table
    $sqlLogs = "CREATE TABLE IF NOT EXISTS notification_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        notification_id INT,
        channel VARCHAR(20),
        status VARCHAR(20) DEFAULT 'sent',
        delivered_at TIMESTAMP NULL,
        opened_at TIMESTAMP NULL,
        clicked_at TIMESTAMP NULL,
        failure_reason TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_notif (notification_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $db->query($sqlLogs);
    echo "Created notification_logs table.\n";

    // 6. Seed basic templates
    $templates = [
        [
            'event_key' => 'job_posted_admin',
            'channel' => 'email',
            'subject' => 'New Job Posted on Platform',
            'content' => "Hi Admin,\n\nA new job \"{{job_title}}\" has been posted by {{company_name}}.\n\nLocation: {{location}}\nApplications Enabled: Yes\n\nView job: {{admin_link}}",
            'variables' => 'job_title,company_name,location,admin_link'
        ],
        [
            'event_key' => 'candidate_match_employer',
            'channel' => 'email',
            'subject' => 'New candidates match your job \"{{job_title}}\"',
            'content' => "Hi {{employer_name}},\n\nWe found {{match_count}} candidates matching your job posting.\n\nTop matches include:\n{{candidate_list}}\n\nView matches: {{dashboard_link}}",
            'variables' => 'employer_name,job_title,match_count,candidate_list,dashboard_link'
        ],
        [
            'event_key' => 'job_match_candidate',
            'channel' => 'email',
            'subject' => '{{job_title}} job matches your profile – Apply now!',
            'content' => "Hi {{candidate_name}},\n\nA new job at {{company_name}} matches your profile.\n\nRole: {{job_title}}\nLocation: {{location}}\nSalary: {{salary_range}}\n\nApply here: {{apply_link}}\n\nDon’t miss out — early applicants get priority!",
            'variables' => 'candidate_name,job_title,company_name,location,salary_range,apply_link'
        ]
    ];

    foreach ($templates as $tpl) {
        try {
            $db->query(
                "INSERT INTO notification_templates (event_key, channel, subject, content, variables) VALUES (:event_key, :channel, :subject, :content, :variables) ON DUPLICATE KEY UPDATE content = VALUES(content), subject = VALUES(subject), variables = VALUES(variables)",
                $tpl
            );
        } catch (\Exception $e) {
            echo "Error seeding template {$tpl['event_key']}: " . $e->getMessage() . "\n";
        }
    }
    echo "Seeded default templates.\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
