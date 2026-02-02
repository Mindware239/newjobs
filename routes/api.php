<?php

use App\Core\Router;
use App\Core\Request;
use App\Core\Response;

$router = \App\Core\Router::getInstance();

$router->get('/api/fcm-web-config', function(Request $request, Response $response) {
    $response->json([
        'apiKey' => $_ENV['FCM_WEB_API_KEY'] ?? '',
        'projectId' => $_ENV['FCM_WEB_PROJECT_ID'] ?? '',
        'messagingSenderId' => $_ENV['FCM_WEB_MESSAGING_SENDER_ID'] ?? '',
        'appId' => $_ENV['FCM_WEB_APP_ID'] ?? '',
        'vapidKey' => $_ENV['FCM_VAPID_KEY'] ?? ($_ENV['FCM_WEB_VAPID_KEY'] ?? '')
    ]);
});

$router->post('/api/push/register', function(Request $request, Response $response) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        $response->json(['error' => 'Unauthorized'], 401);
        return;
    }
    $data = $request->getJsonBody() ?? [];
    $token = trim((string)($data['token'] ?? ''));
    if ($token === '') {
        $response->json(['error' => 'token_required'], 400);
        return;
    }
    $db = \App\Core\Database::getInstance();
    try {
        $device = isset($data['device']) ? (string)$data['device'] : (string)($_SERVER['HTTP_SEC_CH_UA_PLATFORM'] ?? '');
        $browser = isset($data['browser']) ? (string)$data['browser'] : (string)($_SERVER['HTTP_USER_AGENT'] ?? '');

        $db->query(
            "INSERT INTO user_push_tokens (user_id, token, device, browser, is_active, created_at, updated_at)
             VALUES (:user_id, :token, :device, :browser, 1, NOW(), NOW())
             ON DUPLICATE KEY UPDATE device = VALUES(device), browser = VALUES(browser), is_active = 1, updated_at = NOW()",
            [
                'user_id' => (int)$userId,
                'token' => $token,
                'device' => mb_substr($device, 0, 50),
                'browser' => mb_substr($browser, 0, 50)
            ]
        );
        $db->query("UPDATE users SET fcm_token = :token WHERE id = :id", ['token' => $token, 'id' => (int)$userId]);
        $response->json(['success' => true]);
    } catch (\Throwable $t) {
        $response->json(['error' => 'update_failed', 'message' => $t->getMessage()], 500);
    }
});

$router->post('/api/push/unsubscribe', function(Request $request, Response $response) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        $response->json(['error' => 'Unauthorized'], 401);
        return;
    }
    $data = $request->getJsonBody() ?? [];
    $token = trim((string)($data['token'] ?? ''));
    if ($token === '') {
        $response->json(['error' => 'token_required'], 400);
        return;
    }
    try {
        $db = \App\Core\Database::getInstance();
        $db->query("UPDATE user_push_tokens SET is_active = 0, updated_at = NOW() WHERE user_id = :uid AND token = :token", [
            'uid' => (int)$userId,
            'token' => $token
        ]);
        $response->json(['success' => true]);
    } catch (\Throwable $t) {
        $response->json(['error' => 'unsubscribe_failed', 'message' => $t->getMessage()], 500);
    }
});

$router->post('/api/push/test', function(Request $request, Response $response) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        $response->json(['error' => 'Unauthorized'], 401);
        return;
    }
    $ok = \App\Services\NotificationService::sendPush((int)$userId, 'Test Notification', 'Browser push is working', '/');
    $response->json(['success' => $ok]);
});

$router->post('/api/notifications/preferences', function(Request $request, Response $response) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        $response->json(['error' => 'Unauthorized'], 401);
        return;
    }
    $data = $request->getJsonBody() ?? [];
    $prefs = [
        'in_app' => isset($data['in_app']) ? (bool)$data['in_app'] : true,
        'email' => isset($data['email']) ? (bool)$data['email'] : true,
        'push' => isset($data['push']) ? (bool)$data['push'] : false,
        'whatsapp' => isset($data['whatsapp']) ? (bool)$data['whatsapp'] : false
    ];
    $db = \App\Core\Database::getInstance();
    try {
        $db->query("UPDATE users SET notification_preferences = :prefs WHERE id = :id", [
            'prefs' => json_encode($prefs, JSON_UNESCAPED_UNICODE),
            'id' => (int)$userId
        ]);
        $response->json(['success' => true, 'preferences' => $prefs]);
    } catch (\Throwable $t) {
        $response->json(['error' => 'update_failed', 'message' => $t->getMessage()], 500);
    }
});

// Discount Code Validation API (requires auth)
$router->post('/api/discount-code/validate', function(Request $request, Response $response) {
    session_start();
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        $response->json(['error' => 'Unauthorized'], 401);
        return;
    }
    
    $data = $request->getJsonBody() ?? [];
    $code = strtoupper(trim($data['code'] ?? ''));
    $planId = (int)($data['plan_id'] ?? 0);
    $billingCycle = $data['billing_cycle'] ?? 'monthly';
    
    if (empty($code)) {
        $response->json(['valid' => false, 'error' => 'Discount code is required']);
        return;
    }
    
    try {
        $discount = \App\Models\DiscountCode::findByCode($code);
        
        if (!$discount) {
            $response->json(['valid' => false, 'error' => 'Invalid discount code']);
            return;
        }
        
        if (!$discount->isValid()) {
            $response->json(['valid' => false, 'error' => 'This discount code is no longer valid']);
            return;
        }
        
        // Check if applicable to plan
        if ($planId > 0 && !$discount->isApplicableToPlan($planId, $billingCycle)) {
            $response->json(['valid' => false, 'error' => 'This discount code is not applicable to the selected plan']);
            return;
        }
        
        // Check max uses per user if employer
        $maxUsesPerUser = (int)($discount->attributes['max_uses_per_user'] ?? 0);
        if ($maxUsesPerUser > 0) {
            $employer = \App\Models\User::find($userId)->employer();
            if ($employer) {
                $db = \App\Core\Database::getInstance();
                $usedByUser = $db->fetchOne(
                    "SELECT COUNT(*) as count FROM employer_subscriptions 
                     WHERE employer_id = :employer_id AND discount_code = :code",
                    ['employer_id' => $employer->id, 'code' => $code]
                );
                $usedCount = (int)($usedByUser['count'] ?? 0);
                if ($usedCount >= $maxUsesPerUser) {
                    $response->json(['valid' => false, 'error' => 'You have already used this discount code']);
                    return;
                }
            }
        }
        
        $discountType = $discount->attributes['discount_type'] ?? 'percentage';
        $discountValue = (float)($discount->attributes['discount_value'] ?? 0);
        
        $response->json([
            'valid' => true,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'description' => $discount->attributes['description'] ?? ''
        ]);
    } catch (\Exception $e) {
        error_log("Discount code validation error: " . $e->getMessage());
        $response->json(['valid' => false, 'error' => 'Error validating discount code'], 500);
    }
});

// Job Titles Autocomplete API (Public - no auth required)
$router->get('/api/job-titles/search', function(Request $request, Response $response) {
    $query = $request->get('q') ?? '';
    $limit = (int)($request->get('limit') ?? 10);
    
    if (empty($query) || strlen($query) < 2) {
        $response->json(['suggestions' => []]);
        return;
    }
    
    $db = \App\Core\Database::getInstance();
    $searchQuery = '%' . $query . '%';
    $startQuery = $query . '%';
    
    // Use direct limit value (PDO doesn't always bind LIMIT correctly)
    $limit = max(1, min(50, $limit)); // Ensure limit is between 1 and 50
    
    $sql = "SELECT id, title, slug FROM job_titles 
            WHERE is_active = 1 AND LOWER(title) LIKE LOWER(:query)
            ORDER BY 
                CASE 
                    WHEN LOWER(title) = LOWER(:exact) THEN 1
                    WHEN LOWER(title) LIKE LOWER(:start) THEN 2
                    ELSE 3
                END,
                usage_count DESC,
                title ASC
            LIMIT " . (int)$limit;
    
    try {
        $results = $db->fetchAll($sql, [
            'query' => $searchQuery,
            'exact' => $query,
            'start' => $startQuery
        ]);
        
        $suggestions = array_map(function($row) {
            return [
                'id' => $row['id'],
                'title' => $row['title'],
                'slug' => $row['slug']
            ];
        }, $results);
        
        $response->json(['suggestions' => $suggestions]);
    } catch (\Exception $e) {
        error_log("Job titles search error: " . $e->getMessage());
        $response->json(['suggestions' => [], 'error' => 'Search failed'], 500);
    }
});

// Location Autocomplete API (Public - no auth required)
// Location Autocomplete API (Public - no auth required)
$router->get('/api/locations/search', function(Request $request, Response $response) {

    $query = trim($request->get('q') ?? '');
    $limit = (int)($request->get('limit') ?? 10);

    if (strlen($query) < 2) {
        return $response->json(['suggestions' => []]);
    }

    $limit = max(1, min(20, $limit)); // hard safety cap

    $db = \App\Core\Database::getInstance();

    try {
        /**
         * IMPORTANT:
         * - city name comes from cities.name
         * - state name from states.name
         * - country name from countries.name
         * - job_locations only holds IDs
         */
        $sql = "
            SELECT 
                c.id            AS city_id,
                c.name          AS city,
                s.name          AS state,
                co.name         AS country,
                COUNT(DISTINCT jl.job_id) AS job_count
            FROM job_locations jl
            INNER JOIN cities c     ON c.id = jl.city_id
            LEFT JOIN states s      ON s.id = c.state_id
            LEFT JOIN countries co  ON co.id = s.country_id
            WHERE LOWER(c.name) LIKE :search
            GROUP BY c.id, c.name, s.name, co.name
            ORDER BY
                CASE
                    WHEN LOWER(c.name) = :exact THEN 1
                    WHEN LOWER(c.name) LIKE :starts THEN 2
                    ELSE 3
                END,
                job_count DESC,
                c.name ASC
            LIMIT {$limit}
        ";

        $params = [
            'search' => '%' . strtolower($query) . '%',
            'exact'  => strtolower($query),
            'starts' => strtolower($query) . '%'
        ];

        $rows = $db->fetchAll($sql, $params);

        $suggestions = array_map(function ($row) {
            $display = $row['city'];

            if (!empty($row['state'])) {
                $display .= ', ' . $row['state'];
            }
            if (!empty($row['country']) && $row['country'] !== 'India') {
                $display .= ', ' . $row['country'];
            }

            return [
                'city_id'   => (int)$row['city_id'],
                'city'      => $row['city'],
                'state'     => $row['state'] ?? '',
                'country'   => $row['country'] ?? 'India',
                'display'   => $display,
                'job_count' => (int)$row['job_count'],
                // SEO-ready slug (frontend can use this)
                'slug'      => strtolower(str_replace(' ', '-', $row['city']))
            ];
        }, $rows);

        return $response->json(['suggestions' => $suggestions]);

    } catch (\Throwable $e) {
        error_log('[Location Search ERROR] ' . $e->getMessage());
        return $response->json(['suggestions' => []], 500);
    }
});


// Get All Locations for Filter (Public - no auth required)
$router->get('/api/locations/all', function(Request $request, Response $response) {
    $db = \App\Core\Database::getInstance();
    
    try {
        // Include fallback to raw jl.city/state/country when reference IDs are null
        $sql = "SELECT 
                    COALESCE(c.name, jl.city) AS city, 
                    COALESCE(s.name, jl.state) AS state, 
                    COALESCE(cnt.name, jl.country) AS country,
                    COUNT(*) AS job_count
                FROM job_locations jl
                LEFT JOIN cities c ON jl.city_id = c.id
                LEFT JOIN states s ON jl.state_id = s.id
                LEFT JOIN countries cnt ON jl.country_id = cnt.id
                WHERE (COALESCE(c.name, jl.city) IS NOT NULL AND COALESCE(c.name, jl.city) != '')
                   OR (COALESCE(s.name, jl.state) IS NOT NULL AND COALESCE(s.name, jl.state) != '')
                   OR (COALESCE(cnt.name, jl.country) IS NOT NULL AND COALESCE(cnt.name, jl.country) != '')
                GROUP BY COALESCE(c.name, jl.city), COALESCE(s.name, jl.state), COALESCE(cnt.name, jl.country)
                ORDER BY job_count DESC, city ASC, state ASC";
        
        $results = $db->fetchAll($sql, []);
        
        $locations = array_map(function($row) {
            $display = trim($row['city'] ?? '');
            if (!empty($row['state'])) {
                $display .= ', ' . trim($row['state']);
            }
            if (!empty($row['country'])) {
                $display .= ', ' . trim($row['country']);
            }
            $value = trim($row['city'] ?? '');
            if ($value === '') {
                $value = trim($row['state'] ?? '');
            }
            if ($value === '') {
                $value = trim($row['country'] ?? '');
            }
            return [
                'label' => $display,
                'value' => $value,
                'count' => (int)($row['job_count'] ?? 0)
            ];
        }, $results);
        
        $response->json(['locations' => $locations]);
    } catch (\Exception $e) {
        error_log("Get all locations error: " . $e->getMessage());
        $response->json(['locations' => [], 'error' => $e->getMessage()], 500);
    }
});

// Get All Industries for Filter (Public - no auth required)
$router->get('/api/industries/all', function(Request $request, Response $response) {
    $db = \App\Core\Database::getInstance();
    $limit = (int)($request->get('limit') ?? 0); // 0 means no limit
    
    try {
        // First, get all categories from job_categories table
        $categoriesSql = "SELECT 
                            id,
                            name,
                            slug,
                            sort_order
                          FROM job_categories 
                          WHERE is_active = 1 
                          ORDER BY sort_order ASC, name ASC";
        
        $categories = $db->fetchAll($categoriesSql, []);
        
        // If job_categories table doesn't exist or is empty, return empty array
        if (empty($categories)) {
            $response->json(['industries' => [], 'error' => 'No categories found in database']);
            return;
        }
        
        // Get job counts for each category
        $categoriesWithCounts = [];
        foreach ($categories as $category) {
            $categoryName = $category['name'] ?? '';
            
            // Count jobs with this category
            $countSql = "SELECT COUNT(DISTINCT j.id) as job_count
                         FROM jobs j
                         WHERE j.category = ? AND j.status = 'published'";
            
            $countResult = $db->fetchOne($countSql, [$categoryName]);
            $jobCount = (int)($countResult['job_count'] ?? 0);
            
            $categoriesWithCounts[] = [
                'value' => $categoryName,
                'label' => $categoryName,
                'count' => $jobCount,
                'id' => (int)($category['id'] ?? 0),
                'slug' => $category['slug'] ?? ''
            ];
        }
        
        // Sort by job count (descending) then by sort_order
        usort($categoriesWithCounts, function($a, $b) {
            if ($a['count'] !== $b['count']) {
                return $b['count'] - $a['count']; // Higher count first
            }
            // If counts are equal, maintain sort_order
            return 0;
        });
        
        // Apply limit if specified
        if ($limit > 0 && $limit < count($categoriesWithCounts)) {
            $categoriesWithCounts = array_slice($categoriesWithCounts, 0, $limit);
        }
        
        $response->json(['industries' => $categoriesWithCounts]);
    } catch (\Exception $e) {
        error_log("Get all industries error: " . $e->getMessage());
        
        // If job_categories table doesn't exist, try fallback to old method
        try {
            $sql = "SELECT DISTINCT 
                        COALESCE(j.category, e.industry) as category,
                        COUNT(DISTINCT j.id) as job_count
                    FROM jobs j
                    LEFT JOIN employers e ON j.employer_id = e.id
                    WHERE (j.category IS NOT NULL AND j.category != '' AND j.category != 'NULL')
                       OR (j.category IS NULL AND e.industry IS NOT NULL AND e.industry != '' AND e.industry != 'NULL')
                    GROUP BY COALESCE(j.category, e.industry)
                    HAVING job_count > 0
                    ORDER BY job_count DESC, category ASC";
            
            if ($limit > 0) {
                $sql .= " LIMIT " . (int)$limit;
            }
            
            $results = $db->fetchAll($sql, []);
            $industries = array_map(function($row) {
                return [
                    'value' => $row['category'] ?? '',
                    'label' => $row['category'] ?? '',
                    'count' => (int)($row['job_count'] ?? 0)
                ];
            }, $results);
            
            $response->json(['industries' => $industries]);
        } catch (\Exception $e2) {
            error_log("Fallback industries query error: " . $e2->getMessage());
            $response->json(['industries' => [], 'error' => 'Failed to fetch industries']);
        }
    }
});

// Detect user location (country) server-side without external IP APIs
$router->post('/api/location/detect', function(Request $request, Response $response) {
    $acceptLang = $request->header('Accept-Language', '');
    $defaultCountry = 'India';
    $country = $defaultCountry;
    $source = 'default';

    if (!empty($acceptLang)) {
        $source = 'accept-language';
        $primary = explode(',', $acceptLang)[0] ?? '';
        $parts = explode('-', $primary);
        $region = strtoupper(trim($parts[1] ?? ''));
        $map = [
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'IN' => 'India',
            'DE' => 'Germany',
            'FR' => 'France',
            'ES' => 'Spain',
            'IT' => 'Italy',
            'NL' => 'Netherlands',
            'SE' => 'Sweden',
            'NO' => 'Norway',
            'DK' => 'Denmark',
            'FI' => 'Finland',
            'IE' => 'Ireland',
            'NZ' => 'New Zealand',
            'SG' => 'Singapore',
            'JP' => 'Japan',
        ];
        if (!empty($region) && isset($map[$region])) {
            $country = $map[$region];
        }
    }

    $response->json([
        'country' => $country,
        'source' => $source
    ]);
});

// Get Countries API
$router->get('/api/countries', function(Request $request, Response $response) {
    $countries = [
        ['code' => 'US', 'name' => 'United States'],
        ['code' => 'GB', 'name' => 'United Kingdom'],
        ['code' => 'CA', 'name' => 'Canada'],
        ['code' => 'AU', 'name' => 'Australia'],
        ['code' => 'IN', 'name' => 'India'],
        ['code' => 'DE', 'name' => 'Germany'],
        ['code' => 'FR', 'name' => 'France'],
        ['code' => 'ES', 'name' => 'Spain'],
        ['code' => 'IT', 'name' => 'Italy'],
        ['code' => 'NL', 'name' => 'Netherlands'],
        ['code' => 'SE', 'name' => 'Sweden'],
        ['code' => 'NO', 'name' => 'Norway'],
        ['code' => 'DK', 'name' => 'Denmark'],
        ['code' => 'FI', 'name' => 'Finland'],
        ['code' => 'IE', 'name' => 'Ireland'],
        ['code' => 'NZ', 'name' => 'New Zealand'],
        ['code' => 'SG', 'name' => 'Singapore'],
        ['code' => 'JP', 'name' => 'Japan'],
    ];
    $response->json(['countries' => $countries]);
});

// Get States API
$router->get('/api/states', function(Request $request, Response $response) {
    $country = $request->get('country', '');
    $db = \App\Core\Database::getInstance();
    
    try {
        $sql = "SELECT DISTINCT s.name AS state
                FROM job_locations jl
                LEFT JOIN states s ON jl.state_id = s.id
                LEFT JOIN countries co ON jl.country_id = co.id
                WHERE co.name = :country AND s.name IS NOT NULL AND s.name != ''
                ORDER BY s.name ASC";
        $results = $db->fetchAll($sql, ['country' => $country]);
        $states = array_map(function($row) {
            return $row['state'];
        }, $results);
        
        // If no states found, return common states for the country
        if (empty($states)) {
            $commonStates = [
                'US' => ['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia'],
                'GB' => ['England', 'Scotland', 'Wales', 'Northern Ireland'],
                'IN' => ['Andhra Pradesh', 'Assam', 'Bihar', 'Delhi', 'Gujarat', 'Karnataka', 'Maharashtra', 'Tamil Nadu', 'Uttar Pradesh', 'West Bengal'],
            ];
            $states = $commonStates[$country] ?? [];
        }
        
        $response->json(['states' => $states]);
    } catch (\Exception $e) {
        error_log("Get states error: " . $e->getMessage());
        $response->json(['states' => []]);
    }
});

// Get Cities API
$router->get('/api/cities', function(Request $request, Response $response) {
    $state = $request->get('state', '');
    $country = $request->get('country', '');
    $db = \App\Core\Database::getInstance();
    
    try {
        $sql = "SELECT DISTINCT c.name AS city
                FROM job_locations jl
                LEFT JOIN cities c ON jl.city_id = c.id
                LEFT JOIN states s ON jl.state_id = s.id
                LEFT JOIN countries co ON jl.country_id = co.id
                WHERE s.name = :state AND co.name = :country AND c.name IS NOT NULL AND c.name != ''
                ORDER BY c.name ASC";
        $results = $db->fetchAll($sql, ['state' => $state, 'country' => $country]);
        $cities = array_map(function($row) {
            return $row['city'];
        }, $results);
        
        $response->json(['cities' => $cities]);
    } catch (\Exception $e) {
        error_log("Get cities error: " . $e->getMessage());
        $response->json(['cities' => []]);
    }
});
