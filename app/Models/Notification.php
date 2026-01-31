<?php

declare(strict_types=1);

namespace App\Models;

class Notification extends Model
{
    protected string $table = 'notifications';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'user_id', 'type', 'title', 'message', 'link', 'is_read'
    ];

    /**
     * Create notification
     */
    public static function create(int $userId, string $type, string $title, string $message, ?string $link = null): self
    {
        $notification = new self();
        $notification->fill([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => 0
        ]);
        $notification->save();
        return $notification;
    }

    /**
     * Mark as read
     */
    public function markAsRead(): bool
    {
        $this->attributes['is_read'] = 1;
        return $this->save();
    }

    /**
     * Get unread count for user
     */
    public static function getUnreadCount(int $userId): int
    {
        $notifications = self::where('user_id', '=', $userId)
            ->where('is_read', '=', 0)
            ->get();
        return count($notifications);
    }
}

