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
        try {
            $rows = $this->db->fetchAll(
                "SELECT id, name, slug FROM countries ORDER BY name ASC"
            );

            if (!empty($rows)) {
                return [
                    'countries' => array_map(fn($row) => [
                        'id' => (int)$row['id'],
                        'name' => $row['name'],
                        'slug' => $row['slug'],
                        'code' => $this->countryCode((string)$row['name']),
                    ], $rows)
                ];
            }
        } catch (\Throwable $t) {
            error_log("GeoService getCountries error: " . $t->getMessage());
        }

        return ['countries' => $this->fallbackCountries()];
    }

    public function getStates(string $country = ''): array
    {
        $country = trim($country);
        try {
            $params = [];
            $where = '';
            if ($country !== '') {
                $where = "WHERE co.id = :country_id OR co.name = :country OR co.slug = :country_slug";
                $params = [
                    'country_id' => ctype_digit($country) ? (int)$country : 0,
                    'country' => $this->normalizeCountry($country),
                    'country_slug' => $this->slug($country),
                ];
            }

            $sql = "SELECT DISTINCT s.id, s.name, s.slug, co.id AS country_id, co.name AS country
                    FROM states s
                    INNER JOIN countries co ON s.country_id = co.id
                    {$where}
                    ORDER BY s.name ASC";
            $results = $this->db->fetchAll($sql, $params);
            
            if (empty($results)) {
                return ['states' => $this->fallbackStates($country)];
            }
            
            return [
                'states' => array_map(fn($row) => [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'state' => $row['name'],
                    'slug' => $row['slug'],
                    'country_id' => (int)$row['country_id'],
                    'country' => $row['country'],
                ], $results)
            ];
        } catch (\Throwable $t) {
            error_log("GeoService getStates error: " . $t->getMessage());
            return ['states' => $this->fallbackStates($country)];
        }
    }

    public function getCities(string $state = '', string $country = ''): array
    {
        $state = trim($state);
        $country = trim($country);
        try {
            $params = [];
            $where = [];
            if ($state !== '') {
                $where[] = "(s.id = :state_id OR s.name = :state OR s.slug = :state_slug)";
                $params['state_id'] = ctype_digit($state) ? (int)$state : 0;
                $params['state'] = $state;
                $params['state_slug'] = $this->slug($state);
            }
            if ($country !== '') {
                $where[] = "(co.id = :country_id OR co.name = :country OR co.slug = :country_slug)";
                $params['country_id'] = ctype_digit($country) ? (int)$country : 0;
                $params['country'] = $this->normalizeCountry($country);
                $params['country_slug'] = $this->slug($country);
            }

            $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $sql = "SELECT DISTINCT c.id, c.name, c.slug, c.state_id, s.name AS state, co.id AS country_id, co.name AS country
                    FROM cities c
                    INNER JOIN states s ON c.state_id = s.id
                    INNER JOIN countries co ON s.country_id = co.id
                    {$whereSql}
                    ORDER BY c.name ASC";
            
            $results = $this->db->fetchAll($sql, $params);
            
            if (empty($results)) {
                return ['cities' => $this->fallbackCities($state)];
            }

            return [
                'cities' => array_map(fn($row) => [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'city' => $row['name'],
                    'slug' => $row['slug'],
                    'state_id' => (int)$row['state_id'],
                    'state' => $row['state'],
                    'country_id' => (int)$row['country_id'],
                    'country' => $row['country'],
                ], $results)
            ];
        } catch (\Throwable $t) {
            error_log("GeoService getCities error: " . $t->getMessage());
            return ['cities' => $this->fallbackCities($state)];
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

    private function countryCode(string $country): ?string
    {
        $map = [
            'india' => 'IN',
            'united states' => 'US',
            'united kingdom' => 'GB',
            'russia' => 'RU',
            'armenia' => 'AM',
        ];
        return $map[strtolower($country)] ?? null;
    }

    private function normalizeCountry(string $country): string
    {
        $map = [
            'IN' => 'India',
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'UK' => 'United Kingdom',
            'RU' => 'Russia',
            'AM' => 'Armenia',
        ];
        $upper = strtoupper($country);
        return $map[$upper] ?? $country;
    }

    private function slug(string $value): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $value), '-'));
    }

    private function fallbackCountries(): array
    {
        return [
            ['id' => 1, 'code' => 'IN', 'name' => 'India', 'slug' => 'india'],
            ['id' => 2, 'code' => 'US', 'name' => 'United States', 'slug' => 'united-states'],
            ['id' => 3, 'code' => 'GB', 'name' => 'United Kingdom', 'slug' => 'united-kingdom'],
        ];
    }

    private function fallbackStates(string $country = ''): array
    {
        $country = strtoupper($country);
        $indiaStates = [
            'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Delhi',
            'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka',
            'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Punjab', 'Rajasthan', 'Tamil Nadu',
            'Telangana', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal'
        ];

        $states = ($country === '' || $country === 'IN' || $country === 'INDIA' || $country === '1') ? $indiaStates : [];
        return array_map(fn($name, $idx) => [
            'id' => $idx + 1,
            'name' => $name,
            'state' => $name,
            'slug' => $this->slug($name),
            'country_id' => 1,
            'country' => 'India',
        ], $states, array_keys($states));
    }

    private function fallbackCities(string $state = ''): array
    {
        $commonCities = [
            'uttar-pradesh' => ['Lucknow', 'Kanpur', 'Ghaziabad', 'Agra', 'Meerut', 'Varanasi', 'Prayagraj', 'Noida', 'Gorakhpur'],
            'delhi' => ['New Delhi', 'Delhi', 'North Delhi', 'South Delhi', 'East Delhi', 'West Delhi'],
            'maharashtra' => ['Mumbai', 'Pune', 'Nagpur', 'Thane', 'Nashik', 'Navi Mumbai'],
            'karnataka' => ['Bengaluru', 'Mysuru', 'Mangaluru', 'Belagavi'],
            'kerala' => ['Kochi', 'Thiruvananthapuram', 'Kozhikode', 'Thrissur'],
        ];

        $key = $this->slug($state);
        $cities = $commonCities[$key] ?? [];
        return array_map(fn($name, $idx) => [
            'id' => $idx + 1,
            'name' => $name,
            'city' => $name,
            'slug' => $this->slug($name),
            'state_id' => null,
            'state' => $state,
            'country_id' => 1,
            'country' => 'India',
        ], $cities, array_keys($cities));
    }
}
