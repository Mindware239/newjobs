<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class CookieService
{
    public static function ensureSchema(): void
    {
        $db = Database::getInstance();
        try {
            $db->execute("SET foreign_key_checks = 1");
        } catch (\Throwable $e) {
        }
        $db->execute("
            CREATE TABLE IF NOT EXISTS cookie_categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL UNIQUE,
                description TEXT NULL,
                is_mandatory TINYINT(1) DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                sort_order INT DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $db->execute("
            CREATE TABLE IF NOT EXISTS cookie_definitions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                category_id INT NOT NULL,
                cookie_name VARCHAR(100) NOT NULL,
                provider VARCHAR(100) DEFAULT 'internal',
                purpose TEXT NULL,
                duration_type ENUM('session','persistent') DEFAULT 'session',
                duration_days INT DEFAULT 0,
                is_third_party TINYINT(1) DEFAULT 0,
                is_http_only TINYINT(1) DEFAULT 0,
                is_secure TINYINT(1) DEFAULT 0,
                same_site ENUM('Lax','Strict','None') DEFAULT 'Lax',
                is_active TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_cat (category_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $db->execute("
            CREATE TABLE IF NOT EXISTS cookie_policy_versions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                version_number VARCHAR(50) NOT NULL,
                policy_text LONGTEXT NULL,
                effective_from DATETIME NOT NULL,
                is_active TINYINT(1) DEFAULT 1,
                requires_reconsent TINYINT(1) DEFAULT 0,
                created_by_admin_id INT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $db->execute("
            CREATE TABLE IF NOT EXISTS user_cookie_consents (
              id INT AUTO_INCREMENT PRIMARY KEY,
              user_id INT NULL,
              email VARCHAR(255) NULL,
              anonymous_id VARCHAR(64) NULL,
              session_id VARCHAR(64) NULL,
              ip_hash VARCHAR(128) NULL,
              country_code VARCHAR(10) NULL,
              region_code VARCHAR(20) NULL,
              device_type VARCHAR(50) NULL,
              browser_name VARCHAR(50) NULL,
              consent_version_id INT NOT NULL,
              essential TINYINT(1) DEFAULT 1,
              functional TINYINT(1) DEFAULT 0,
              analytics TINYINT(1) DEFAULT 0,
              marketing TINYINT(1) DEFAULT 0,
              performance TINYINT(1) DEFAULT 0,
              consent_source VARCHAR(20) DEFAULT 'banner',
              consent_method VARCHAR(20) DEFAULT 'explicit',
              consent_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
              consent_linked_at DATETIME NULL,
              expires_at DATETIME NULL,
              revoked_at DATETIME NULL,
              deleted_at DATETIME NULL,
              created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              INDEX idx_ver (consent_version_id),
              INDEX idx_anon (anonymous_id),
              INDEX idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $db->execute("
            CREATE TABLE IF NOT EXISTS cookie_consent_audit_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                consent_id INT NOT NULL,
                action VARCHAR(20) NOT NULL,
                previous_values JSON NULL,
                new_values JSON NULL,
                ip_hash VARCHAR(128) NULL,
                user_agent VARCHAR(255) NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_consent (consent_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $db->execute("
            CREATE TABLE IF NOT EXISTS tracking_visitors (
                id INT AUTO_INCREMENT PRIMARY KEY,
                visitor_uuid VARCHAR(64) NOT NULL UNIQUE,
                user_id INT NULL,
                first_visit_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_visit_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                total_sessions INT DEFAULT 0,
                total_page_views INT DEFAULT 0,
                device_fingerprint_hash VARCHAR(128) NULL,
                ip_hash VARCHAR(128) NULL,
                country VARCHAR(100) NULL,
                city VARCHAR(100) NULL,
                referrer VARCHAR(255) NULL,
                utm_source VARCHAR(100) NULL,
                utm_medium VARCHAR(100) NULL,
                utm_campaign VARCHAR(100) NULL,
                deleted_at DATETIME NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $db->execute("
            CREATE TABLE IF NOT EXISTS visitor_sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                visitor_id INT NOT NULL,
                session_id VARCHAR(64) NOT NULL,
                login_status VARCHAR(20) DEFAULT 'guest',
                started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                ended_at DATETIME NULL,
                pages_viewed INT DEFAULT 0,
                actions_count INT DEFAULT 0,
                is_bounce TINYINT(1) DEFAULT 0,
                device_type VARCHAR(50) NULL,
                browser VARCHAR(50) NULL,
                os VARCHAR(50) NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_vis (visitor_id),
                INDEX idx_ended (ended_at),
                UNIQUE KEY uniq_session (visitor_id, session_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $db->execute("
            CREATE TABLE IF NOT EXISTS behavior_events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                visitor_id INT NOT NULL,
                user_id INT NULL,
                event_type VARCHAR(50) NOT NULL,
                event_category VARCHAR(50) NULL,
                event_data JSON NULL,
                page_url VARCHAR(255) NULL,
                referrer VARCHAR(255) NULL,
                device_type VARCHAR(50) NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_v (visitor_id),
                INDEX idx_t (event_type),
                INDEX idx_v_time (visitor_id, created_at),
                INDEX idx_type_time (event_type, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $db->execute("
            CREATE TABLE IF NOT EXISTS heatmap_events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                visitor_id INT NOT NULL,
                page_url VARCHAR(255) NOT NULL,
                click_x INT DEFAULT 0,
                click_y INT DEFAULT 0,
                scroll_depth INT DEFAULT 0,
                viewport_width INT DEFAULT 0,
                viewport_height INT DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_p (page_url),
                INDEX idx_v2 (visitor_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $db->execute("
            CREATE TABLE IF NOT EXISTS consent_script_controls (
                id INT AUTO_INCREMENT PRIMARY KEY,
                script_name VARCHAR(100) NOT NULL,
                script_src VARCHAR(255) NOT NULL,
                category_required VARCHAR(50) NOT NULL,
                is_blocked_by_default TINYINT(1) DEFAULT 1,
                is_active TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $db->execute("
            CREATE TABLE IF NOT EXISTS global_cookie_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                auto_expiry_months INT DEFAULT 12,
                geo_based_strict_mode TINYINT(1) DEFAULT 0,
                force_reconsent TINYINT(1) DEFAULT 0,
                block_scripts_until_consent TINYINT(1) DEFAULT 1,
                log_ip_anonymized TINYINT(1) DEFAULT 1,
                cross_device_sync TINYINT(1) DEFAULT 0,
                data_retention_days INT DEFAULT 365,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $db->execute("
            CREATE TABLE IF NOT EXISTS admin_cookie_actions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_id INT NOT NULL,
                action_type VARCHAR(50) NOT NULL,
                description TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $db->execute("
            CREATE TABLE IF NOT EXISTS consent_geo_rules (
                id INT AUTO_INCREMENT PRIMARY KEY,
                country_code VARCHAR(10),
                strict_mode TINYINT(1) DEFAULT 0,
                require_explicit TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $row = $db->fetchOne("SELECT id FROM cookie_policy_versions WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
        if (!$row) {
            $db->execute("INSERT INTO cookie_policy_versions (version_number, policy_text, effective_from, is_active, requires_reconsent, created_by_admin_id) VALUES (:v, :t, NOW(), 1, 0, NULL)", [
                'v' => '1.0',
                't' => 'Default cookie policy'
            ]);
        }
        // Seed categories if absent
        try {
            $exists = (int)($db->fetchOne("SELECT COUNT(*) c FROM cookie_categories")['c'] ?? 0);
            if ($exists === 0) {
                $cats = [
                    ['Strictly Necessary', 'Required for core site functions', 1, 1, 10],
                    ['Functional', 'Enhances features and personalization', 0, 1, 20],
                    ['Analytics & Performance', 'Measures performance and usage', 0, 1, 30],
                    ['Advertising & Targeting', 'Personalized ads and retargeting', 0, 1, 40]
                ];
                foreach ($cats as $c) {
                    $db->execute("INSERT INTO cookie_categories (name, description, is_mandatory, is_active, sort_order) VALUES (:n, :d, :m, :a, :o)", [
                        'n' => $c[0], 'd' => $c[1], 'm' => $c[2], 'a' => $c[3], 'o' => $c[4]
                    ]);
                }
            }
        } catch (\Throwable $e) {
        }
        $gs = $db->fetchOne("SELECT id FROM global_cookie_settings LIMIT 1");
        if (!$gs) {
            $db->execute("INSERT INTO global_cookie_settings (auto_expiry_months, data_retention_days) VALUES (12, 365)");
        }
        try {
            $db->execute("ALTER TABLE cookie_definitions ADD CONSTRAINT fk_cookie_def_category FOREIGN KEY (category_id) REFERENCES cookie_categories(id) ON DELETE CASCADE ON UPDATE CASCADE");
        } catch (\Throwable $e) {
        }
        try {
            $db->execute("ALTER TABLE user_cookie_consents ADD CONSTRAINT fk_consents_version FOREIGN KEY (consent_version_id) REFERENCES cookie_policy_versions(id) ON DELETE RESTRICT ON UPDATE CASCADE");
        } catch (\Throwable $e) {
        }
        try {
            $db->execute("ALTER TABLE cookie_consent_audit_logs ADD CONSTRAINT fk_audit_consent FOREIGN KEY (consent_id) REFERENCES user_cookie_consents(id) ON DELETE CASCADE ON UPDATE CASCADE");
        } catch (\Throwable $e) {
        }
        try {
            $db->execute("ALTER TABLE visitor_sessions ADD CONSTRAINT fk_sessions_visitor FOREIGN KEY (visitor_id) REFERENCES tracking_visitors(id) ON DELETE CASCADE ON UPDATE CASCADE");
        } catch (\Throwable $e) {
        }
        try {
            $db->execute("ALTER TABLE behavior_events ADD CONSTRAINT fk_events_visitor FOREIGN KEY (visitor_id) REFERENCES tracking_visitors(id) ON DELETE CASCADE ON UPDATE CASCADE");
        } catch (\Throwable $e) {
        }
        try {
            $db->execute("ALTER TABLE heatmap_events ADD CONSTRAINT fk_heatmap_visitor FOREIGN KEY (visitor_id) REFERENCES tracking_visitors(id) ON DELETE CASCADE ON UPDATE CASCADE");
        } catch (\Throwable $e) {
        }
        try {
            $db->execute("ALTER TABLE visitor_sessions DROP INDEX uniq_session");
        } catch (\Throwable $e) {
        }
        try {
            $db->execute("ALTER TABLE visitor_sessions ADD UNIQUE KEY uniq_session (visitor_id, session_id)");
        } catch (\Throwable $e) {
        }
        try {
            $db->execute("ALTER TABLE user_cookie_consents DROP COLUMN ip_address");
        } catch (\Throwable $e) {
        }
        try {
            $db->execute("ALTER TABLE cookie_consent_audit_logs DROP COLUMN ip_address");
        } catch (\Throwable $e) {
        }
    }
    public static function linkAnonymousConsent(int $userId, string $email = '', ?string $sessionId = null, ?string $anonymousId = null): void
    {
        $db = Database::getInstance();
        $ver = $db->fetchOne("SELECT id FROM cookie_policy_versions WHERE is_active = 1 ORDER BY effective_from DESC LIMIT 1");
        $verId = (int)($ver['id'] ?? 0);
        if ($verId <= 0) {
            return;
        }
        $params = ['ver' => $verId];
        $where = [];
        if ($sessionId) {
            $where[] = 'session_id = :sid';
            $params['sid'] = $sessionId;
        }
        if ($anonymousId) {
            $where[] = 'anonymous_id = :anon';
            $params['anon'] = $anonymousId;
        }
        if (empty($where)) {
            return;
        }
        $sql = "SELECT id, essential, functional, analytics, marketing, performance 
                FROM user_cookie_consents 
                WHERE consent_version_id = :ver 
                  AND revoked_at IS NULL 
                  AND user_id IS NULL 
                  AND (" . implode(' OR ', $where) . ")
                ORDER BY id DESC LIMIT 1";
        $row = null;
        try {
            $row = $db->fetchOne($sql, $params);
        } catch (\Throwable $e) {
            $row = null;
        }
        if (!$row) {
            return;
        }
        $prev = [
            'essential' => (int)($row['essential'] ?? 1) === 1,
            'functional' => (int)($row['functional'] ?? 0) === 1,
            'analytics' => (int)($row['analytics'] ?? 0) === 1,
            'marketing' => (int)($row['marketing'] ?? 0) === 1,
            'performance' => (int)($row['performance'] ?? 0) === 1
        ];
        $db->execute("UPDATE user_cookie_consents SET user_id = :uid, email = :email, consent_linked_at = NOW(), anonymous_id = NULL WHERE id = :id", [
            'uid' => $userId,
            'email' => $email !== '' ? $email : null,
            'id' => (int)$row['id']
        ]);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $secret = $_ENV['IP_HASH_SECRET'] ?? ($_ENV['APP_KEY'] ?? 'mw-secret');
        $ipHash = $ip ? hash('sha256', $ip . $secret) : null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        try {
            $db->execute("
                INSERT INTO cookie_consent_audit_logs (consent_id, action, previous_values, new_values, ip_hash, user_agent)
                VALUES (:cid, 'linked', :prev, :newv, :ip_hash, :ua)
            ", [
                'cid' => (int)$row['id'],
                'prev' => json_encode($prev),
                'newv' => json_encode(['linked' => true, 'user_id' => $userId, 'email' => $email !== '' ? $email : null]),
                'ip_hash' => $ipHash,
                'ua' => $ua
            ]);
        } catch (\Throwable $e) {}
    }
}
