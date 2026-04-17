<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class GeoService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getCountries(): array
    {
        return [
            'countries' => [
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
            ]
        ];
    }

    public function getStates(string $country = ''): array
    {
        try {
            $sql = "SELECT DISTINCT s.name AS state
                    FROM states s
                    INNER JOIN countries co ON s.country_id = co.id
                    WHERE co.name = :country OR co.code = :country
                    ORDER BY s.name ASC";
            $results = $this->db->fetchAll($sql, ['country' => $country]);
            
            if (empty($results)) {
                $common = [
                    'IN' => ['Andhra Pradesh', 'Assam', 'Bihar', 'Delhi', 'Gujarat', 'Karnataka', 'Maharashtra', 'Tamil Nadu', 'Uttar Pradesh', 'West Bengal'],
                    'US' => ['Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia'],
                    'GB' => ['England', 'Scotland', 'Wales', 'Northern Ireland'],
                ];
                return ['states' => $common[strtoupper($country)] ?? $common[$country] ?? []];
            }
            
            return ['states' => array_map(fn($row) => $row['state'], $results)];
        } catch (\Throwable $t) {
            error_log("GeoService getStates error: " . $t->getMessage());
            return ['states' => []];
        }
    }

    public function getCities(string $state = '', string $country = ''): array
    {
        try {
            $sql = "SELECT DISTINCT c.name AS city
                    FROM cities c
                    INNER JOIN states s ON c.state_id = s.id
                    INNER JOIN countries co ON s.country_id = co.id
                    WHERE (s.name = :state) AND (co.name = :country OR co.code = :country OR :country = '')
                    ORDER BY c.name ASC";
            
            $results = $this->db->fetchAll($sql, ['state' => $state, 'country' => $country]);
            return ['cities' => array_map(fn($row) => $row['city'], $results)];
        } catch (\Throwable $t) {
            error_log("GeoService getCities error: " . $t->getMessage());
            return ['cities' => []];
        }
    }

    public function detectLocation(string $acceptLang = ''): array
    {
        $country = 'India';
        $source = 'default';

        if (!empty($acceptLang)) {
            $source = 'accept-language';
            $primary = explode(',', $acceptLang)[0] ?? '';
            $parts = explode('-', $primary);
            $region = strtoupper(trim($parts[1] ?? ''));
            
            $map = [
                'US' => 'United States', 'GB' => 'United Kingdom', 'CA' => 'Canada', 
                'AU' => 'Australia', 'IN' => 'India', 'DE' => 'Germany', 
                'FR' => 'France', 'ES' => 'Spain', 'IT' => 'Italy'
            ];
            if (isset($map[$region])) {
                $country = $map[$region];
            }
        }

        return ['country' => $country, 'source' => $source];
    }
}
