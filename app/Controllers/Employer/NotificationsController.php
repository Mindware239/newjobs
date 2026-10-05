<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Employer;
use App\Models\Job;
use App\Models\Application;
use App\Models\Notification;

class NotificationsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->view('employer/profile-missing', [
                'title' => 'Complete Your Profile',
                'message' => 'Your employer profile was not found.',
                'user' => $this->currentUser
            ], 200, 'employer/layout');
            return;
        }

        // Get counts for sidebar
        $activeJobsCount = Job::where('employer_id', '=', $employer->id)
            ->where('status', '=', 'published')->count();
        $jobIds = Job::where('employer_id', '=', $employer->id)->pluck('id');
        $totalApplications = !empty($jobIds) 
            ? Application::whereIn('job_id', $jobIds)->count()
            : 0;

        $notifications = Notification::where('user_id', '=', (int)$this->currentUser->id)
            ->orderBy('created_at', 'DESC')
            ->limit(50)
            ->get();

        $response->view('employer/notifications', [
            'title' => 'Notifications',
            'employer' => $employer,
            'jobCount' => $activeJobsCount,
            'applicationCount' => $totalApplications,
            'notifications' => array_map(fn($notification) => $notification->toArray(), $notifications)
        ], 200, 'employer/layout');
    }

    public function getUnread(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $notifications = Notification::where('user_id', '=', (int)$this->currentUser->id)
            ->where('is_read', '=', 0)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        $response->json([
            'notifications' => array_map(fn($notification) => $notification->toArray(), $notifications),
            'unread_count' => Notification::getUnreadCount((int)$this->currentUser->id)
        ]);
    }

    public function markAsRead(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $notification = Notification::find((int)$request->param('id'));
        if (!$notification || (int)($notification->attributes['user_id'] ?? 0) !== (int)$this->currentUser->id) {
            $response->json(['error' => 'Notification not found'], 404);
            return;
        }

        $response->json(['success' => $notification->markAsRead()]);
    }

    public function markAllAsRead(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $db = \App\Core\Database::getInstance();
        $db->query(
            'UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0',
            ['user_id' => (int)$this->currentUser->id]
        );

        $response->json(['success' => true]);
    }

    public function delete(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $notification = Notification::find((int)$request->param('id'));
        if (!$notification || (int)($notification->attributes['user_id'] ?? 0) !== (int)$this->currentUser->id) {
            $response->json(['error' => 'Notification not found'], 404);
            return;
        }

        $response->json(['success' => $notification->delete()]);
    }

    public function deleteRead(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $db = \App\Core\Database::getInstance();
        $db->query(
            'DELETE FROM notifications WHERE user_id = :user_id AND is_read = 1',
            ['user_id' => (int)$this->currentUser->id]
        );

        $response->json(['success' => true]);
    }
    
}

