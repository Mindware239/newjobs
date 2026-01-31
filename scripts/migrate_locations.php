<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables if .env exists
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

use App\Core\Database;

try {
    $db = Database::getInstance();
    echo "Connected to database.\n";

    // 1. Get all job_locations that have city name but no city_id
    $locations = $db->fetchAll("SELECT * FROM job_locations WHERE city_id IS NULL AND city IS NOT NULL AND city != ''");
    
    echo "Found " . count($locations) . " locations to migrate.\n";

    $updated = 0;
    $failed = 0;

    foreach ($locations as $loc) {
        $cityName = trim($loc['city']);
        
        // Try to find city in cities table
        $city = $db->fetchOne("SELECT id FROM cities WHERE name = :name LIMIT 1", ['name' => $cityName]);
        
        if ($city) {
            $cityId = $city['id'];
            
            // Update job_locations
            $db->execute("UPDATE job_locations SET city_id = :city_id WHERE id = :id", [
                'city_id' => $cityId,
                'id' => $loc['id']
            ]);
            $updated++;
        } else {
            // City not found - maybe create it? 
            // For now, just log.
            // echo "City not found: $cityName (Location ID: {$loc['id']})\n";
            $failed++;
        }
        
        if (($updated + $failed) % 100 === 0) {
            echo "Processed " . ($updated + $failed) . " locations...\n";
        }
    }

    echo "Migration complete.\n";
    echo "Updated: $updated\n";
    echo "Failed (City not found): $failed\n";
    
    if ($failed > 0) {
        echo "Note: Some locations could not be mapped because the city was not found in the 'cities' table.\n";
        echo "You may need to populate the 'cities' table first or add missing cities.\n";
    } else {
        echo "All locations mapped successfully.\n";
        echo "You can now safely drop the text columns (city, state, country) from job_locations table if you wish.\n";
        echo "SQL: ALTER TABLE job_locations DROP COLUMN city, DROP COLUMN state, DROP COLUMN country;\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
