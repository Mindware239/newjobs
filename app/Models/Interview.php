<?php

declare(strict_types=1);

namespace App\Models;

class Interview extends Model
{
    protected string $table = 'interviews';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'application_id', 'employer_id', 'scheduled_by', 'interview_type',
        'scheduled_start', 'scheduled_end', 'timezone', 'location',
        'meeting_link', 'status',
        'room_name', 'room_password_enc', 'is_premium',
        'started_at', 'ended_at', 'recording_url', 'created_by_role'
    ];

    public function application()
    {
        return Application::find($this->attributes['application_id'] ?? 0);
    }

    public function employer()
    {
        return Employer::find($this->attributes['employer_id'] ?? 0);
    }

    public function generateMeetingLink(): string
    {
        $base = rtrim((string)($_ENV['APP_URL'] ?? ''), '/');
        if ($base === '') {
            $base = '';
        }
        return $base . '/interviews/' . bin2hex(random_bytes(8)) . '/room';
    }
}

