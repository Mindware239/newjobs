<?php

declare(strict_types=1);

namespace App\Models;

class JobLocation extends Model
{
    protected string $table = 'job_locations';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'job_id', 'city_id', 'state_id', 'country_id', 'city', 'state', 'country', 'latitude', 'longitude'
    ];

    public function job(): ?Job
    {
        return Job::find($this->attributes['job_id'] ?? 0);
    }

    public static function getDistinctRaw(): array
    {
        $db = (new static())->getDb();
        return $db->fetchAll("
            SELECT DISTINCT
                COALESCE(NULLIF(c.name, ''), NULLIF(jl.city, ''), '') AS city,
                COALESCE(NULLIF(s.name, ''), NULLIF(jl.state, ''), '') AS state,
                COALESCE(NULLIF(cnt.name, ''), NULLIF(jl.country, ''), '') AS country
            FROM job_locations jl
            LEFT JOIN cities c ON jl.city_id = c.id
            LEFT JOIN states s ON jl.state_id = s.id
            LEFT JOIN countries cnt ON jl.country_id = cnt.id
            ORDER BY country ASC, state ASC, city ASC
            LIMIT 300
        ");
    }
}

