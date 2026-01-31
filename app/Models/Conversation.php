<?php

declare(strict_types=1);

namespace App\Models;

class Conversation extends Model
{
    protected string $table = 'conversations';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'employer_id', 'candidate_user_id', 'last_message_id',
        'unread_employer', 'unread_candidate'
    ];

    public function employer()
    {
        return Employer::find($this->attributes['employer_id'] ?? 0);
    }

    public function candidate()
    {
        return User::find($this->attributes['candidate_user_id'] ?? 0);
    }

    public function messages()
    {
        return Message::where('conversation_id', '=', $this->attributes['id'])
            ->orderBy('created_at', 'ASC')
            ->get();
    }

    public function lastMessage()
    {
        if ($this->attributes['last_message_id'] ?? null) {
            return Message::find($this->attributes['last_message_id']);
        }
        return null;
    }
}

