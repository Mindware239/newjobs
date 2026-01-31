<?php

declare(strict_types=1);

namespace App\Models;

class Message extends Model
{
    protected string $table = 'messages';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'conversation_id', 'sender_user_id', 'body', 'attachments', 'is_read'
    ];

    public function conversation()
    {
        return Conversation::find($this->attributes['conversation_id'] ?? 0);
    }

    public function sender()
    {
        return User::find($this->attributes['sender_user_id'] ?? 0);
    }

    public function markAsRead(): bool
    {
        $this->attributes['is_read'] = 1;
        return $this->save();
    }
}

