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
        if (!$this->requireAuth($request, $response)) {
            return;
        }

        // Controls & Pagination
        $search = trim((string)($request->get('search') ?? ''));
        $status = trim((string)($request->get('status') ?? 'all')); // all | sent | processing | failed | draft
        $page = max(1, (int)($request->get('page') ?? 1));
        $perPage = max(10, min(50, (int)($request->get('per_page') ?? 20)));
        $offset = ($page - 1) * $perPage;

        // Fetch campaigns with pagination
        $db = Database::getInstance();
        $campaigns = [];
        $total = 0;
        try {
            $where = [];
            $params = [];
            if ($search !== '') {
                $where[] = "(subject LIKE :q OR title LIKE :q)";
                $params['q'] = '%' . $search . '%';
            }
            if ($status !== '' && $status !== 'all') {
                $where[] = "status = :status";
                $params['status'] = $status;
            }
            $whereSql = empty($where) ? '' : (' WHERE ' . implode(' AND ', $where));

            $row = $db->fetchOne("SELECT COUNT(*) as cnt FROM notification_campaigns{$whereSql}", $params);
            $total = (int)($row['cnt'] ?? 0);
            $campaigns = $db->fetchAll(
                "SELECT * FROM notification_campaigns{$whereSql} ORDER BY created_at DESC LIMIT :limit OFFSET :offset",
                array_merge($params, ['limit' => $perPage, 'offset' => $offset])
            );
        } catch (\Throwable $e) {
            error_log("Failed to load campaigns: " . $e->getMessage());
        }

        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        $response->view('admin/bulk-emails/index', [
            'title' => 'Bulk Notification Campaigns',
            'campaigns' => $campaigns,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages
            ],
            'filters' => [
                'search' => $search,
                'status' => $status
            ],
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function create(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) {
            return;
        }
        // Controls
        $role = (string)$request->get('role', 'candidate');
        $search = trim((string)$request->get('search', ''));
        $page = max(1, (int)($request->get('page') ?? 1));
        $perPage = max(10, min(100, (int)($request->get('per_page') ?? 20)));
        $offset = ($page - 1) * $perPage;

        $db = Database::getInstance();
        $usersList = [];
        $total = 0;
        try {
            $params = ['role' => $role];
            $where = "u.status = 'active' AND u.email IS NOT NULL AND u.role = :role";
            if ($search !== '') {
                $where .= " AND (u.email LIKE :q OR u.first_name LIKE :q)";
                $params['q'] = '%' . $search . '%';
            }
            $row = $db->fetchOne("SELECT COUNT(*) as cnt FROM users u WHERE {$where}", $params);
            $total = (int)($row['cnt'] ?? 0);
            $usersList = $db->fetchAll(
                "SELECT u.id, u.email, u.first_name, u.role
                 FROM users u
                 WHERE {$where}
                 ORDER BY u.id DESC
                 LIMIT :limit OFFSET :offset",
                array_merge($params, ['limit' => $perPage, 'offset' => $offset])
            );
        } catch (\Throwable $e) {}

        $response->view('admin/bulk-emails/create', [
            'title' => 'Create Campaign',
            'user' => $this->currentUser,
            'usersList' => $usersList,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'role' => $role,
            'search' => $search
        ], 200, 'admin/layout');
    }

    public function show(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) {
            return;
        }
        $id = (int)$request->param('id');
        $db = Database::getInstance();
        $campaign = $db->fetchOne("SELECT * FROM notification_campaigns WHERE id = :id", ['id' => $id]) ?? [];
        if (!$campaign) {
            $response->redirect('/admin/marketing/campaigns');
            return;
        }
        $response->view('admin/bulk-emails/show', [
            'title' => 'Campaign Details',
            'campaign' => $campaign,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function delete(Request $request, Response $response): void
    {
        if (!$this->requireAuth($request, $response)) {
            return;
        }
        $id = (int)$request->param('id');
        if ($id <= 0) {
            $response->json(['error' => 'Invalid campaign id'], 400);
            return;
        }
        $db = Database::getInstance();
        try {
            $db->query("DELETE FROM notification_campaigns WHERE id = :id", ['id' => $id]);
            $response->json(['success' => true]);
        } catch (\Throwable $e) {
            $response->json(['error' => 'Failed to delete campaign'], 500);
        }
    }

    public function send(Request $request, Response $response): void
    {
        set_time_limit(0); // Allow long execution
        
        if (!$this->requireAuth($request, $response)) {
            return;
        }

        $subject = trim((string)$request->post('subject', ''));
        $bodyHtml = (string)$request->post('body_html', '');
        $filters = $request->post('filters') ?? [];
        $channels = $request->post('channels') ?? ['email'];
        $selectedIds = $request->post('selected_user_ids') ?? [];
        if (is_string($selectedIds)) {
            $selectedIds = array_filter(array_map('intval', explode(',', $selectedIds)));
        } elseif (is_array($selectedIds)) {
            $selectedIds = array_map('intval', $selectedIds);
        }
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

        // 1. Build recipients
        $db = Database::getInstance();
        $recipients = [];

        try {
        if (!empty($selectedIds)) {
            // Manual selection overrides filters
            $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
            $sql = "SELECT u.id, u.email, u.first_name, u.role FROM users u WHERE u.id IN ($placeholders) AND u.email IS NOT NULL AND u.status = 'active'";
            $recipients = $db->fetchAll($sql, $selectedIds);
        } else {
            // Build Query based on filters
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

                if (!empty($filters['incomplete_profile'])) {
                    $conditions[] = "(c.is_profile_complete = 0 OR c.profile_strength < 80)";
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
                $conditions[] = "u.last_login >= DATE_SUB(NOW(), INTERVAL {$days} DAY)";
            }

            $sql = $query . " WHERE " . implode(' AND ', $conditions);
            
            try {
                $recipients = $db->fetchAll($sql, $params);
            } catch (\Throwable $e) {
                $recipients = [];
            }
            }
            
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
