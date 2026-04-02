<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Models\Notification;

class NotificationController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/notifications",
     *     summary="Get user notifications",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Notification list")
     * )
     */
    public function index(Request $request, Response $response): void
    {
        $user = $this->user($request);
        $page = (int)($request->get('page') ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $notifications = Notification::where('user_id', '=', $user->id)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $unreadCount = Notification::getUnreadCount($user->id);

        $this->success($response, [
            'notifications' => array_map(fn($n) => $n->toArray(), $notifications),
            'unread_count' => $unreadCount,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notifications/{id}/read",
     *     summary="Mark notification as read",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Marked as read")
     * )
     */
    public function markAsRead(Request $request, Response $response, array $params): void
    {
        $user = $this->user($request);
        $id = (int)($params['id'] ?? 0);

        $notification = Notification::find($id);

        if (!$notification || (int)$notification->user_id !== $user->id) {
            $this->error($response, 'Notification not found', 404);
            return;
        }

        if ($notification->markAsRead()) {
            $this->success($response, [], 'Notification marked as read');
        } else {
            $this->error($response, 'Failed to update notification', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/notifications/read-all",
     *     summary="Mark all notifications as read",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="All marked as read")
     * )
     */
    public function markAllAsRead(Request $request, Response $response): void
    {
        $user = $this->user($request);

        $db = \App\Core\Database::getInstance();
        $db->query(
            "UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0",
            ['uid' => $user->id]
        );

        $this->success($response, [], 'All notifications marked as read');
    }
}
