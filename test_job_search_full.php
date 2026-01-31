<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;

// Mock inputs
$keyword = 'Banking';
$location = 'Cochin';
$page = 1;
$perPage = 20;

echo "Testing Job Search with keyword='$keyword', location='$location'...\n";

$db = Database::getInstance();
$params = [];
$whereConditions = ["j.status = 'published'"];

// --- KEYWORD FILTER ---
if ($keyword) {
    $whereConditions[] = "(j.title LIKE :keyword OR j.description LIKE :keyword_desc OR j.short_description LIKE :keyword_short)";
    $params['keyword'] = "%{$keyword}%";
    $params['keyword_desc'] = "%{$keyword}%";
    $params['keyword_short'] = "%{$keyword}%";
}

// --- LOCATION FILTER (The logic we fixed) ---
if ($location) {
    $parts = array_values(array_filter(array_map('trim', explode(',', $location)), fn($p) => $p !== ''));
    $partConditions = [];
    foreach ($parts as $idx => $part) {
        $slugPart = strtolower(str_replace(' ', '-', $part));
        
        // The FIXED query
        $locEntity = $db->fetchOne(
            "SELECT id, 'city' as type FROM cities WHERE name = :name1 OR slug = :slug1
             UNION
             SELECT id, 'state' as type FROM states WHERE name = :name2 OR slug = :slug2
             UNION
             SELECT id, 'country' as type FROM countries WHERE name = :name3 OR slug = :slug3",
            [
                'name1' => $part, 'slug1' => $slugPart,
                'name2' => $part, 'slug2' => $slugPart,
                'name3' => $part, 'slug3' => $slugPart
            ]
        );
        
        if ($locEntity) {
            echo "Found location entity: " . json_encode($locEntity) . "\n";
            $paramId = "loc_id_{$idx}";
            if ($locEntity['type'] === 'city') {
                $partConditions[] = "EXISTS (SELECT 1 FROM job_locations jl WHERE jl.job_id = j.id AND jl.city_id = :{$paramId})";
            } elseif ($locEntity['type'] === 'state') {
                $partConditions[] = "EXISTS (SELECT 1 FROM job_locations jl WHERE jl.job_id = j.id AND jl.state_id = :{$paramId})";
            } else {
                $partConditions[] = "EXISTS (SELECT 1 FROM job_locations jl WHERE jl.job_id = j.id AND jl.country_id = :{$paramId})";
            }
            $params[$paramId] = $locEntity['id'];
        } else {
            echo "Location entity not found for: $part\n";
            $pCity = "loc_part_city_{$idx}";
            $pState = "loc_part_state_{$idx}";
            $pCountry = "loc_part_country_{$idx}";
            $pJson = "loc_part_json_{$idx}";
            $partConditions[] = "(EXISTS (
                SELECT 1 FROM job_locations jl 
                LEFT JOIN cities c ON jl.city_id = c.id
                LEFT JOIN states s ON jl.state_id = s.id
                LEFT JOIN countries co ON jl.country_id = co.id
                WHERE jl.job_id = j.id 
                AND (c.name LIKE :{$pCity} OR s.name LIKE :{$pState} OR co.name LIKE :{$pCountry})
            ) OR j.locations LIKE :{$pJson})";
            $likeVal = "%{$part}%";
            $params[$pCity] = $likeVal;
            $params[$pState] = $likeVal;
            $params[$pCountry] = $likeVal;
            $params[$pJson] = $likeVal;
        }
    }
    if (!empty($partConditions)) {
        $whereConditions[] = '(' . implode(' OR ', $partConditions) . ')';
    } else {
        $whereConditions[] = "j.locations LIKE :location_search";
        $params['location_search'] = "%{$location}%";
    }
}

// --- BUILD MAIN QUERY ---
$whereClause = implode(' AND ', $whereConditions);
$countSql = "SELECT COUNT(DISTINCT j.id) as total FROM jobs j WHERE {$whereClause}";

// Helper function mock
function cleanParams(string $sql, array $params) {
    $matches = [];
    preg_match_all('/:[a-zA-Z0-9_]+/', $sql, $matches);
    $usedParams = array_unique($matches[0]);
    $cleaned = [];
    foreach ($usedParams as $param) {
        $key = substr($param, 1);
        if (array_key_exists($key, $params)) {
            $cleaned[$key] = $params[$key];
        }
    }
    return $cleaned;
}

// Execute Count
try {
    $countParams = cleanParams($countSql, $params);
    echo "Count SQL: $countSql\n";
    echo "Count Params: " . json_encode($countParams) . "\n";
    $totalResult = $db->fetchOne($countSql, $countParams);
    echo "Total Jobs Found: " . ($totalResult['total'] ?? 0) . "\n";
} catch (\Exception $e) {
    echo "Count Error: " . $e->getMessage() . "\n";
}

// Execute Main Query
$sql = "SELECT DISTINCT j.* FROM jobs j WHERE {$whereClause} LIMIT 20";
try {
    $queryParams = cleanParams($sql, $params);
    echo "Main SQL: $sql\n";
    // echo "Main Params: " . json_encode($queryParams) . "\n";
    $results = $db->fetchAll($sql, $queryParams);
    echo "Jobs Retrieved: " . count($results) . "\n";
    foreach ($results as $job) {
        echo " - Job: {$job['title']} (ID: {$job['id']})\n";
    }
} catch (\Exception $e) {
    echo "Main Query Error: " . $e->getMessage() . "\n";
}
