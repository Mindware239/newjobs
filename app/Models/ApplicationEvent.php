<?php

declare(strict_types=1);

namespace App\Models;

class ApplicationEvent extends Model
{
    protected string $table = 'application_events';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'application_id', 'actor_user_id', 'from_status', 'to_status', 'comment'
    ];

    public function application()
    {
        return Application::find($this->attributes['application_id'] ?? 0);
    }
}

