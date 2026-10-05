<?php

declare(strict_types=1);

namespace App\Models;

class Notification extends Model
{
    protected string $table = 'notifications';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'user_id', 'event', 'type', 'channel', 'title', 'message', 'status', 'link', 'metadata', 'is_read'
    ];

    /**
     * Create notification
     */
    public static function create(int $userId, string $type, string $title, string $message, ?string $link = null, array $metadata = []): self
    {
        $event = $type;
        $type = self::normalizeType($type);
        $notification = new self();
        $data = [
            'user_id' => $userId,
            'event' => $event,
            'type' => $type,
            'channel' => 'in_app',
            'title' => $title,
            'message' => $message,
            'status' => 'sent',
            'link' => $link,
            'metadata' => $metadata,
            'is_read' => 0
        ];

        $notification->fill(self::filterExistingColumns($data));
        $notification->save();
        return $notification;
    }

    private static function normalizeType(string $type): string
    {
        $allowed = ['job_match', 'application_update', 'interview_scheduled', 'message', 'profile_view', 'system'];
        if (in_array($type, $allowed, true)) {
            return $type;
        }

        if (in_array($type, ['candidate_match_employer', 'employer_job_match'], true)) {
            return 'job_match';
        }

        return 'system';
    }

    private static function filterExistingColumns(array $data): array
    {
        static $columns = null;
        if ($columns === null) {
            try {
                $instance = new self();
                $columns = array_map(
                    fn($row) => $row['Field'],
                    $instance->getDb()->fetchAll('DESCRIBE notifications')
                );
            } catch (\Throwable $e) {
                $columns = ['user_id', 'type', 'title', 'message', 'link', 'is_read'];
            }
        }

        return array_intersect_key($data, array_flip($columns));
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
