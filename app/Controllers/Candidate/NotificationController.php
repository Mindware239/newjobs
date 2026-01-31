<?php

declare(strict_types=1);

namespace App\Controllers\Candidate;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Candidate;
use App\Models\Notification;

class NotificationController extends BaseController
{

    private function ensureCandidate(Request $request, Response $response): ?Candidate
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->redirect('/login');
            return null;
        }

        $user = User::find($userId);
        if (!$user || !$user->isCandidate()) {
            $response->redirect('/');
            return null;
        }

        $candidate = Candidate::findByUserId($userId);
        if (!$candidate) {
            $candidate = Candidate::createForUser($userId);
        }

        return $candidate;
    }

    /**
     * Helper to format time ago
     */
    private function timeAgo(string $datetime): string
    {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        return date('M d, Y', $time);
    }

    /**
     * Get all notifications
     */
    public function index(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $page = (int)($request->get('page', 1));
        if ($page < 1) { $page = 1; }
        $perPage = (int)($request->get('per_page', 20));
        if ($perPage < 5) { $perPage = 20; }
        if ($perPage > 100) { $perPage = 100; }
        $tab = (string)$request->get('tab', 'all');
        $offset = ($page - 1) * $perPage;

        $qb = Notification::where('user_id', '=', $candidate->attributes['user_id']);
        if ($tab === 'unread') {
            $qb = $qb->where('is_read', '=', 0);
        }
        $total = $qb->count();
        $totalPages = max(1, (int)ceil($total / $perPage));

        $notifications = $qb
            ->orderBy('created_at', 'DESC')
            ->limit($perPage)
            ->offset($offset)
            ->get();

        $formattedNotifications = array_map(function($n) {
            $attr = $n->attributes;
            $attr['time_ago'] = $this->timeAgo($attr['created_at']);
            // Add icon type based on title or type if available
            $title = strtolower($attr['title'] ?? '');
            if (str_contains($title, 'interview')) {
                $attr['icon_type'] = 'interview';
            } elseif (str_contains($title, 'application')) {
                $attr['icon_type'] = 'application';
            } elseif (str_contains($title, 'message') || str_contains($title, 'chat')) {
                $attr['icon_type'] = 'message';
            } else {
                $attr['icon_type'] = 'system';
            }
            return $attr;
        }, $notifications);

        $response->view('candidate/notifications/index', [
            'title' => 'Notifications',
            'candidate' => $candidate,
            'notifications' => $formattedNotifications,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages,
                'tab' => $tab
            ]
        ]);
    }

    /**
     * Get unread notifications (AJAX)
     */
    public function getUnread(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->json(['notifications' => [], 'unread_count' => 0]);
            return;
        }

        $notifications = Notification::where('user_id', '=', $userId)
            ->where('is_read', '=', 0)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        $response->json([
            'notifications' => array_map(fn($n) => $n->attributes, $notifications),
            'unread_count' => Notification::getUnreadCount($userId)
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $notificationId = $request->get('id') ?? 0;
        $notification = Notification::find($notificationId);

        if (!$notification || $notification->attributes['user_id'] != $userId) {
            $response->json(['error' => 'Notification not found'], 404);
            return;
        }

        $notification->markAsRead();
        $response->json(['success' => true]);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $notifications = Notification::where('user_id', '=', $userId)
            ->where('is_read', '=', 0)
            ->get();

        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }

        $response->json(['success' => true]);
    }
    public function delete(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->json(['error' => 'Unauthorized'], 401);
            return;
        }
        $id = (int)($request->param('id') ?? 0);
        $n = Notification::find($id);
        if (!$n || (int)($n->attributes['user_id'] ?? 0) !== (int)$userId) {
            $response->json(['error' => 'Not found'], 404);
            return;
        }
        $ok = $n->delete();
        $response->json(['success' => $ok]);
    }

    public function deleteRead(Request $request, Response $response): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->json(['error' => 'Unauthorized'], 401);
            return;
        }
        $db = \App\Core\Database::getInstance();
        $db->query("DELETE FROM notifications WHERE user_id = :u AND is_read = 1", ['u' => $userId]);
        $response->json(['success' => true]);
    }
}


