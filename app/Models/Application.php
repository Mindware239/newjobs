<?php

declare(strict_types=1);

namespace App\Models;

class Application extends Model
{
    protected string $table = 'applications';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'job_id', 'candidate_user_id', 'resume_url', 'cover_letter',
        'expected_salary', 'status', 'score', 'source'
    ];

    public function job(): ?Job
    {
        return Job::find($this->attributes['job_id'] ?? 0);
    }

    public function candidate(): ?User
    {
        return User::find($this->attributes['candidate_user_id'] ?? 0);
    }

    public function events()
    {
        return ApplicationEvent::where('application_id', '=', $this->attributes['id'])
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function interviews()
    {
        return Interview::where('application_id', '=', $this->attributes['id'])->get();
    }

    public function updateStatus(string $newStatus, int|string|null $actorId = null, string $comment = ''): bool
    {
        $oldStatus = $this->attributes['status'] ?? 'applied';
        $actorUserId = is_null($actorId) ? null : (int)$actorId;
        $this->attributes['status'] = $newStatus;
        
        if ($this->save()) {
            // Log event
            $event = new ApplicationEvent();
            $event->fill([
                'application_id' => $this->attributes['id'],
                'actor_user_id' => $actorUserId,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'comment' => $comment
            ]);
            $event->save();
            return true;
        }
        return false;
    }
}

