<?php
// Test script for cleanParams logic

function cleanParams(string $sql, array $params): array
{
    $matches = [];
    // Match named parameters like :name, :id_1, etc.
    preg_match_all('/:[a-zA-Z0-9_]+/', $sql, $matches);
    
    // Get unique parameter names including the colon
    $usedParams = array_unique($matches[0]);
    
    $cleaned = [];
    foreach ($usedParams as $param) {
        // Remove the leading colon to get the key
        $key = substr($param, 1);
        if (array_key_exists($key, $params)) {
            $cleaned[$key] = $params[$key];
        } else {
            echo "Warning: Parameter $key found in SQL but missing in params!\n";
        }
    }
    
    return $cleaned;
}

// Test Case 1: Extra params
$sql1 = "SELECT * FROM jobs WHERE id = :id";
$params1 = ['id' => 1, 'extra' => 2];
$cleaned1 = cleanParams($sql1, $params1);
echo "Test 1 (Extra params): " . (count($cleaned1) === 1 && isset($cleaned1['id']) ? "PASS" : "FAIL") . "\n";
print_r($cleaned1);

// Test Case 2: Missing params
$sql2 = "SELECT * FROM jobs WHERE id = :id AND status = :status";
$params2 = ['id' => 1];
$cleaned2 = cleanParams($sql2, $params2);
echo "Test 2 (Missing params): " . (count($cleaned2) === 1 ? "PASS (Warning expected)" : "FAIL") . "\n";
print_r($cleaned2);

// Test Case 3: Params with similar names
$sql3 = "SELECT * FROM jobs WHERE title LIKE :keyword OR desc LIKE :keyword_desc";
$params3 = ['keyword' => 'test', 'keyword_desc' => 'test', 'keyword_other' => 'test'];
$cleaned3 = cleanParams($sql3, $params3);
echo "Test 3 (Similar names): " . (count($cleaned3) === 2 ? "PASS" : "FAIL") . "\n";
print_r($cleaned3);

// Test Case 4: Params inside strings (Potential False Positive)
$sql4 = "SELECT * FROM jobs WHERE created_at > '2023-01-01 10:00:00'";
$params4 = [];
$cleaned4 = cleanParams($sql4, $params4);
echo "Test 4 (Time string): " . (count($cleaned4) === 0 ? "PASS" : "FAIL") . "\n";
// Note: regex matches :00, :00. But cleanParams checks if they exist in $params.
// Since '00' is likely not in $params, it is stripped. Correct.

// Test Case 5: The Location Scenario
$sql5 = "SELECT COUNT(DISTINCT j.id) as total FROM jobs j WHERE (j.status = 'published') AND ((EXISTS (SELECT 1 FROM job_locations jl WHERE jl.job_id = j.id AND jl.city_id = :loc_id_0) OR EXISTS (SELECT 1 FROM job_locations jl WHERE jl.job_id = j.id AND jl.state_id = :loc_id_1) OR EXISTS (SELECT 1 FROM job_locations jl WHERE jl.job_id = j.id AND jl.country_id = :loc_id_2)))";
$params5 = [
    'loc_id_0' => 1,
    'loc_id_1' => 2,
    'loc_id_2' => 3,
    'cand_loc_like' => '%Kochi%' // Unused in count
];
$cleaned5 = cleanParams($sql5, $params5);
echo "Test 5 (Location Scenario): " . (!isset($cleaned5['cand_loc_like']) && count($cleaned5) === 3 ? "PASS" : "FAIL") . "\n";
print_r($cleaned5);
