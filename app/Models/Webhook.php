<?php

declare(strict_types=1);

namespace App\Models;

class Webhook extends Model
{
    protected string $table = 'webhooks';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'employer_id', 'url', 'events', 'secret', 'active', 'last_delivery_at'
    ];

    public function employer()
    {
        return Employer::find($this->attributes['employer_id'] ?? 0);
    }

    public function shouldTrigger(string $event): bool
    {
        if (!$this->attributes['active']) {
            return false;
        }
        
        $events = json_decode($this->attributes['events'] ?? '[]', true);
        return in_array($event, $events);
    }
}

