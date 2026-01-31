<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Services\NotificationService;
use App\Models\NotificationCampaign;

class BulkEmailsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireRole('admin', $request, $response)) {
            return;
        }

        // Fetch recent campaigns (raw query due to limited model helpers)
        $db = Database::getInstance();
        $campaigns = [];
        try {
            $campaigns = $db->fetchAll("SELECT * FROM notification_campaigns ORDER BY created_at DESC LIMIT 20");
        } catch (\Throwable $e) {
            error_log("Failed to load campaigns: " . $e->getMessage());
        }

        $response->view('admin/bulk-emails/index', [
            'title' => 'Bulk Notification Campaigns',
            'campaigns' => $campaigns,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function send(Request $request, Response $response): void
    {
        set_time_limit(0); // Allow long execution
        
        if (!$this->requireRole('admin', $request, $response)) {
            return;
        }

        $subject = trim((string)$request->post('subject', ''));
        $bodyHtml = (string)$request->post('body_html', '');
        $filters = $request->post('filters') ?? [];
        $channels = $request->post('channels') ?? ['email'];
        if (is_string($channels)) {
            $channels = array_filter(array_map('trim', explode(',', $channels)));
        }
        if (empty($channels)) {
            $channels = ['email'];
        }
        // Expected filters: role, skills, location, experience_min, experience_max, active_days, subscription_status

        if ($subject === '' || $bodyHtml === '') {
            $response->json(['error' => 'Subject and Message are required'], 422);
            return;
        }

        // 1. Build Query based on filters
        $db = Database::getInstance();
        $query = "SELECT u.id, u.email, u.first_name, u.role FROM users u ";
        $params = [];
        $conditions = ["u.status = 'active'", "u.email IS NOT NULL"];

        // Role Filter
        $role = $filters['role'] ?? 'candidate';
        $conditions[] = "u.role = :role";
        $params['role'] = $role;

        // Join tables based on role
        if ($role === 'candidate') {
            $query .= " JOIN candidates c ON c.user_id = u.id ";
            
            // Subscription Filter
            if (!empty($filters['subscription_status'])) {
                if ($filters['subscription_status'] === 'premium') {
                    $conditions[] = "c.is_premium = 1 AND c.premium_expires_at > NOW()";
                } elseif ($filters['subscription_status'] === 'free') {
                    $conditions[] = "(c.is_premium = 0 OR c.premium_expires_at <= NOW() OR c.premium_expires_at IS NULL)";
                }
            }

            // Location Filter
            if (!empty($filters['location'])) {
                $conditions[] = "c.city LIKE :location";
                $params['location'] = '%' . $filters['location'] . '%';
            }

            // Experience Filter
            if (!empty($filters['experience_min'])) {
                $conditions[] = "c.total_experience_years >= :exp_min";
                $params['exp_min'] = (int)$filters['experience_min'];
            }

            // Skills Filter (JSON search or text search)
            if (!empty($filters['skills'])) {
                // Simple LIKE search for now as skills might be JSON or text
                $conditions[] = "c.skills_data LIKE :skill";
                $params['skill'] = '%' . $filters['skills'] . '%';
            }

        } elseif ($role === 'employer') {
            $query .= " JOIN employers e ON e.user_id = u.id ";
            
             // Subscription Filter for Employers (check subscription_plan_id or similar)
             if (!empty($filters['subscription_status'])) {
                 // Assuming logic for employer subscription
                 // This might need adjustment based on actual schema
             }

             if (!empty($filters['location'])) {
                $conditions[] = "e.city LIKE :location";
                $params['location'] = '%' . $filters['location'] . '%';
            }
        }

        // Activity Filter (Last Login)
        if (!empty($filters['active_within_days'])) {
            $days = (int)$filters['active_within_days'];
            $conditions[] = "u.last_login >= DATE_SUB(NOW(), INTERVAL :days DAY)";
            $params['days'] = $days;
        }

        $sql = $query . " WHERE " . implode(' AND ', $conditions);
        
        try {
            $recipients = $db->fetchAll($sql, $params);
            
            // 2. Create Campaign Record
            $campaign = new NotificationCampaign();
            $campaign->title = $subject; // Use subject as title for now
            $campaign->subject = $subject;
            $campaign->message = $bodyHtml;
            $campaign->filters = $filters;
            $campaign->channel = implode(',', $channels);
            $campaign->status = 'processing';
            $campaign->recipient_count = count($recipients);
            $campaign->created_by = $this->currentUser->id;
            $campaign->save();

            // 3. Queue Notifications (Multi-Channel)
            $sentCount = 0;
            foreach ($recipients as $recipient) {
                try {
                    // Use unified send method with channel filtering
                    NotificationService::send(
                        (int)$recipient['id'],
                        'marketing_broadcast',
                        $subject,
                        strip_tags($bodyHtml), // Plain text for Push/WhatsApp/InApp
                        [
                            'subject' => $subject,
                            'body_html' => $bodyHtml, // For Email
                            'message' => strip_tags($bodyHtml), // For WhatsApp
                            'user_name' => $recipient['first_name'] ?? 'User',
                            'link' => '/dashboard' // Default link
                        ],
                        null, // Link
                        $channels // Restrict to selected channels
                    );
                    $sentCount++;
                } catch (\Throwable $e) {
                    // Log error but continue
                    error_log("Failed to queue notification for user {$recipient['id']}: " . $e->getMessage());
                }
            }

            // 4. Update Campaign Status
            $campaign->status = 'sent';
            $campaign->sent_at = date('Y-m-d H:i:s');
            $campaign->success_count = $sentCount;
            $campaign->save();

            $response->json([
                'success' => true,
                'message' => "Campaign sent to {$sentCount} recipients",
                'campaign_id' => $campaign->id
            ]);

        } catch (\Throwable $e) {
            error_log("Campaign Error: " . $e->getMessage());
            $response->json(['error' => 'Failed to send campaign: ' . $e->getMessage()], 500);
        }
    }
}
