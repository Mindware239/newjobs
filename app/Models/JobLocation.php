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
}

