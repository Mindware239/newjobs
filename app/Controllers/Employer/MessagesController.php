<?php

declare(strict_types=1);

namespace App\Controllers\Employer;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Job;
use App\Models\Application;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\Candidate;
use App\Models\Notification;
use App\Core\Storage;

class MessagesController extends BaseController
{
    /**
     * Messages inbox - Indeed-style
     * SQL: Fetches conversations with last message, candidate info, job info, unread counts
     */
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

        // Enterprise SQL query: Get conversations with last message, candidate info, job info
        $db = \App\Core\Database::getInstance();
        $employerId = (int)$employer->id;
        
        // Simplified query - use employer_id directly in JOIN to avoid parameter binding issues
        $sql = "SELECT 
                    c.id,
                    c.employer_id,
                    c.candidate_user_id,
                    c.last_message_id,
                    c.unread_employer,
                    c.unread_candidate,
                    c.created_at,
                    c.updated_at,
                    u.email as candidate_email,
                    cand.full_name as candidate_name,
                    cand.profile_picture as candidate_picture,
                    m.body as last_message_body,
                    m.created_at as last_message_time,
                    m.sender_user_id as last_message_sender_id,
                    j.id as job_id,
                    j.title as job_title,
                    j.slug as job_slug
                FROM conversations c
                LEFT JOIN users u ON c.candidate_user_id = u.id
                LEFT JOIN candidates cand ON cand.user_id = u.id
                LEFT JOIN messages m ON c.last_message_id = m.id
                LEFT JOIN applications a ON a.candidate_user_id = c.candidate_user_id
                LEFT JOIN jobs j ON a.job_id = j.id AND j.employer_id = " . $employerId . "
                WHERE c.employer_id = :employer_id
                ORDER BY c.updated_at DESC
                LIMIT 100";

        $conversations = $db->fetchAll($sql, ['employer_id' => $employerId]);

        // Format conversations for view
        $conversationList = [];
        foreach ($conversations as $row) {
            $conversationList[] = [
                'id' => $row['id'],
                'candidate_user_id' => $row['candidate_user_id'],
                'candidate' => [
                    'name' => $row['candidate_name'] ?? $row['candidate_email'] ?? 'Unknown',
                    'email' => $row['candidate_email'] ?? '',
                    'picture' => $row['candidate_picture'] ?? null
                ],
                'last_message' => [
                    'body' => $row['last_message_body'] ?? '',
                    'created_at' => $row['last_message_time'] ?? $row['updated_at']
                ],
                'last_message_time' => $row['last_message_time'] ?? $row['updated_at'],
                'last_message_sender_id' => $row['last_message_sender_id'] ?? null,
                'unread_count' => (int)($row['unread_employer'] ?? 0),
                'job' => ($row['job_id'] ?? null) ? [
                    'id' => $row['job_id'],
                    'title' => $row['job_title'] ?? '',
                    'slug' => $row['job_slug'] ?? null
                ] : null,
                'updated_at' => $row['updated_at']
            ];
        }
        
        // Get counts for sidebar
        $activeJobsCount = Job::where('employer_id', '=', $employer->id)
            ->where('status', '=', 'published')->count();
        $jobIds = Job::where('employer_id', '=', $employer->id)->pluck('id');
        $totalApplications = !empty($jobIds) 
            ? Application::whereIn('job_id', $jobIds)->count()
            : 0;
        
        // Get total unread messages
        $totalUnread = $this->getTotalUnreadMessages($employer->id);

        // Get conversation ID from query string if present
        $selectedConversationId = (int)($request->get('conversation') ?? 0);
        
        $response->view('employer/messages', [
            'title' => 'Messages',
            'employer' => $employer,
            'conversations' => $conversationList,
            'jobCount' => $activeJobsCount,
            'applicationCount' => $totalApplications,
            'unreadCount' => $totalUnread,
            'selectedConversationId' => $selectedConversationId
        ], 200, 'employer/layout');
    }

    /**
     * Get conversation details with messages
     */
    public function getConversation(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        $conversationId = (int)$request->param('id');
        $conversation = Conversation::find($conversationId);

        if (!$conversation || $conversation->attributes['employer_id'] !== $employer->id) {
            $response->json(['error' => 'Conversation not found'], 404);
            return;
        }

        // Mark employer's messages as read
        $this->markConversationAsRead($conversationId, $employer->id, 'employer');

        // Get messages
        $messages = $conversation->messages();
        $candidate = $conversation->candidate();
        $candidateProfile = Candidate::findByUserId($conversation->attributes['candidate_user_id']);
        
        // Get job info if available
        $job = null;
        $jobIds = Job::where('employer_id', '=', $employer->id)->pluck('id');
        if (!empty($jobIds)) {
            $application = Application::where('candidate_user_id', '=', $conversation->attributes['candidate_user_id'])
                ->whereIn('job_id', $jobIds)
                ->first();
            if ($application) {
                $job = Job::find($application->attributes['job_id']);
            }
        }

        $response->json([
            'conversation' => $conversation->attributes,
            'messages' => array_map(fn($m) => $this->formatMessage($m), $messages),
            'candidate' => $candidate ? [
                'id' => $candidate->id,
                'email' => $candidate->attributes['email'] ?? '',
                'name' => $candidateProfile ? ($candidateProfile->attributes['full_name'] ?? $candidate->attributes['email']) : $candidate->attributes['email']
            ] : null,
            'job' => $job ? [
                'id' => $job->attributes['id'],
                'title' => $job->attributes['title'],
                'slug' => $job->attributes['slug'] ?? null
            ] : null
        ]);
    }

    /**
     * Send message - Enterprise logic with notifications
     */
    public function sendMessage(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        // Handle both JSON and FormData
        $data = $request->getJsonBody();
        if (empty($data)) {
            // FormData - get from POST
            $conversationId = (int)($request->post('conversation_id') ?? 0);
            $body = trim($request->post('body') ?? '');
        } else {
            $conversationId = (int)($data['conversation_id'] ?? 0);
            $body = trim($data['body'] ?? '');
        }

        // Check for files (handle multiple files) - Check $_FILES directly
        $hasFiles = false;
        $fileError = null;
        
        // Check all possible file keys in $_FILES
        if (!empty($_FILES)) {
            foreach ($_FILES as $key => $file) {
                // Check if it's an attachment (attachment_0, attachment_1, or just 'attachment')
                if (strpos($key, 'attachment') === 0 && isset($file['error'])) {
                    if ($file['error'] === UPLOAD_ERR_OK) {
                        $hasFiles = true;
                        break;
                    } else {
                        // File was attempted but has an error - still count as "file present" for validation
                        // but we'll handle the error later
                        $hasFiles = true;
                        $fileError = $file['error'];
                        $fileName = $file['name'] ?? 'unknown';
                        break;
                    }
                }
            }
        }
        
        // Debug: Log file detection
        error_log("Message Send - Body: " . ($body ?: 'empty'));
        error_log("Message Send - Has files: " . ($hasFiles ? 'YES' : 'NO'));
        error_log("Message Send - \$_FILES keys: " . implode(', ', array_keys($_FILES ?? [])));
        if (!empty($_FILES)) {
            foreach ($_FILES as $key => $file) {
                error_log("Message Send - File '$key': error=" . ($file['error'] ?? 'N/A') . ", name=" . ($file['name'] ?? 'N/A') . ", size=" . ($file['size'] ?? 'N/A'));
            }
        }

        if (empty($body) && !$hasFiles) {
            $response->json(['error' => 'Message body or attachment is required'], 422);
            return;
        }
        
        // Check for file upload errors before processing
        if ($hasFiles && $fileError !== null && $fileError !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File size exceeds server limit. Maximum file size allowed is ' . ini_get('upload_max_filesize'),
                UPLOAD_ERR_FORM_SIZE => 'File size exceeds form limit',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            $errorMsg = $errorMessages[$fileError] ?? 'File upload error (code: ' . $fileError . ')';
            $response->json(['error' => $errorMsg], 422);
            return;
        }

        $conversation = Conversation::find($conversationId);
        if (!$conversation || $conversation->attributes['employer_id'] !== $employer->id) {
            $response->json(['error' => 'Conversation not found'], 404);
            return;
        }

        $userId = $this->currentUser->id;

        // Handle multiple file attachments (WhatsApp style)
        $attachments = [];
        $storage = new Storage();
        
        // Check for multiple files (attachment_0, attachment_1, etc.)
        $fileIndex = 0;
        while ($request->hasFile("attachment_$fileIndex")) {
            $file = $request->file("attachment_$fileIndex");
            if ($file && $file['error'] === UPLOAD_ERR_OK) {
                $filePath = $storage->store($file, 'messages/' . $conversationId);
                $attachments[] = [
                    'name' => $file['name'],
                    'url' => $storage->url($filePath),
                    'size' => $file['size'],
                    'type' => $file['type']
                ];
            }
            $fileIndex++;
        }
        
        // Also check for single 'attachment' (backward compatibility)
        if ($request->hasFile('attachment') && empty($attachments)) {
            $file = $request->file('attachment');
            if ($file && $file['error'] === UPLOAD_ERR_OK) {
                $filePath = $storage->store($file, 'messages/' . $conversationId);
                $attachments[] = [
                    'name' => $file['name'],
                    'url' => $storage->url($filePath),
                    'size' => $file['size'],
                    'type' => $file['type']
                ];
            }
        }

        // Create message
        $message = new Message();
        $message->fill([
            'conversation_id' => $conversationId,
            'sender_user_id' => $userId,
            'body' => $body,
            'attachments' => !empty($attachments) ? json_encode($attachments, JSON_UNESCAPED_UNICODE) : null,
            'is_read' => 0
        ]);

        if ($message->save()) {
            $messageId = $message->id ?? $message->attributes['id'] ?? null;
            
            if (!$messageId) {
                error_log("Message saved but no ID found. Attributes: " . json_encode($message->attributes));
                $response->json(['error' => 'Message saved but ID not found'], 500);
                return;
            }
            
            // Update conversation: last_message_id, unread_candidate, updated_at
            $conversation->fill([
                'last_message_id' => $messageId,
                'unread_candidate' => ($conversation->attributes['unread_candidate'] ?? 0) + 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $conversation->save();

            // Create notification for candidate
            $candidateUserId = $conversation->attributes['candidate_user_id'];
            if ($candidateUserId) {
                try {
                    Notification::create(
                        $candidateUserId,
                        'message',
                        'New message from employer',
                        substr($body, 0, 100) . (strlen($body) > 100 ? '...' : ''),
                        '/candidate/chat/' . $conversationId
                    );
                } catch (\Exception $e) {
                    error_log("Failed to create notification: " . $e->getMessage());
                    // Don't fail the request if notification fails
                }
            }

            try {
                $formattedMessage = $this->formatMessage($message);
                $response->json([
                    'success' => true,
                    'message' => $formattedMessage
                ], 201);
            } catch (\Exception $e) {
                error_log("Error formatting message in response: " . $e->getMessage());
                $response->json([
                    'success' => true,
                    'message' => [
                        'id' => $messageId,
                        'body' => $body,
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ], 201);
            }
        } else {
            error_log("Failed to save message. Attributes: " . json_encode($message->attributes));
            $response->json(['error' => 'Failed to send message'], 500);
        }
    }

    /**
     * Get messages for a conversation (for polling/real-time)
     */
    public function getMessages(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        $conversationId = (int)$request->param('id');
        $lastMessageId = (int)($request->get('last_message_id') ?? 0);
        
        $conversation = Conversation::find($conversationId);

        if (!$conversation || $conversation->attributes['employer_id'] !== $employer->id) {
            $response->json(['error' => 'Conversation not found'], 404);
            return;
        }

        // Mark as read
        $this->markConversationAsRead($conversationId, $employer->id, 'employer');

        // Get messages (new ones if lastMessageId provided)
        $query = Message::where('conversation_id', '=', $conversationId);
        if ($lastMessageId > 0) {
            $query = $query->where('id', '>', $lastMessageId);
        }
        
        $messages = $query->orderBy('created_at', 'ASC')->get();
        
        $response->json([
            'messages' => array_map(fn($m) => $this->formatMessage($m), $messages)
        ]);
    }

    /**
     * Get unread count
     */
    public function getUnreadCount(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        $totalUnread = $this->getTotalUnreadMessages($employer->id);
        $response->json(['unread_count' => $totalUnread]);
    }

    /**
     * Start conversation with candidate - Auto-create if not exists
     * Can be called from application view or candidate list
     */
    public function startConversation(Request $request, Response $response): void
    {
        if (!$this->requireRole('employer', $request, $response)) {
            return;
        }

        $employer = $this->currentUser->employer();
        if (!$employer) {
            $response->json(['error' => 'Employer profile not found'], 404);
            return;
        }

        // Subscription gating: require active subscription with chat_enabled and remaining messages
        $subscription = \App\Models\EmployerSubscription::getCurrentForEmployer((int)$employer->id);
        $plan = $subscription ? $subscription->plan() : null;
        $active = $subscription && ($subscription->isActive() || $subscription->isInGracePeriod());
        $chatEnabled = $plan ? (bool)$plan->hasFeature('chat_enabled') : false;
        $canChat = $active && $chatEnabled && $subscription->canUseFeature('max_chat_messages');
        if (!$canChat) {
            $response->json([
                'error' => 'Messaging requires an active subscription',
                'requires_upgrade' => true,
                'redirect' => '/employer/subscription/plans?upgrade=1&feature=chat'
            ], 402);
            return;
        }

        $data = $request->getJsonBody();
        $candidateUserId = (int)($data['candidate_user_id'] ?? 0);
        $jobId = (int)($data['job_id'] ?? 0);
        $initialMessage = trim($data['initial_message'] ?? '');

        if (!$candidateUserId) {
            $response->json(['error' => 'Candidate user ID is required'], 422);
            return;
        }

        // Check if conversation already exists
        $existing = Conversation::where('employer_id', '=', $employer->id)
            ->where('candidate_user_id', '=', $candidateUserId)
            ->first();

        if ($existing) {
            // If initial message provided, send it
            if (!empty($initialMessage)) {
                $message = new Message();
                $message->fill([
                    'conversation_id' => $existing->id,
                    'sender_user_id' => $this->currentUser->id,
                    'body' => $initialMessage,
                    'is_read' => 0
                ]);
                if ($message->save()) {
                    $existing->fill([
                        'last_message_id' => $message->id,
                        'unread_candidate' => ($existing->attributes['unread_candidate'] ?? 0) + 1,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    $existing->save();

                    // Create notification
                    Notification::create(
                        $candidateUserId,
                        'message',
                        'New message from employer',
                        substr($initialMessage, 0, 100) . (strlen($initialMessage) > 100 ? '...' : ''),
                        '/candidate/chat/' . $existing->id
                    );
                }
            }

            $response->json([
                'success' => true,
                'conversation_id' => $existing->id,
                'message' => 'Conversation already exists'
            ]);
            return;
        }

        // Create new conversation
        $conversation = new Conversation();
        $conversation->fill([
            'employer_id' => $employer->id,
            'candidate_user_id' => $candidateUserId,
            'unread_employer' => 0,
            'unread_candidate' => 0
        ]);

        if ($conversation->save()) {
            // Send initial message if provided
            if (!empty($initialMessage)) {
                $message = new Message();
                $message->fill([
                    'conversation_id' => $conversation->id,
                    'sender_user_id' => $this->currentUser->id,
                    'body' => $initialMessage,
                    'is_read' => 0
                ]);
                if ($message->save()) {
                    $conversation->fill([
                        'last_message_id' => $message->id,
                        'unread_candidate' => 1,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    $conversation->save();

                    // Create notification
                    Notification::create(
                        $candidateUserId,
                        'message',
                        'New message from employer',
                        substr($initialMessage, 0, 100) . (strlen($initialMessage) > 100 ? '...' : ''),
                        '/candidate/chat/' . $conversation->id
                    );
                    // Increment usage for chat messages
                    if ($subscription) {
                        $subscription->incrementUsage('max_chat_messages', 1);
                        \App\Models\SubscriptionUsageLog::logUsage(
                            (int)($subscription->attributes['id'] ?? 0),
                            (int)$employer->id,
                            'chat_message',
                            null,
                            $jobId ?: null,
                            null,
                            ['source' => 'applications_list']
                        );
                    }
                }
            }

            $response->json([
                'success' => true,
                'conversation_id' => $conversation->id
            ], 201);
        } else {
            $response->json(['error' => 'Failed to create conversation'], 500);
        }
    }

    /**
     * Get conversations with optimized SQL query
     */
    private function getConversations(int $employerId): array
    {
        $db = \App\Core\Database::getInstance();
        
        // Simplified query - use employer_id directly in JOIN to avoid parameter binding issues
        $sql = "SELECT 
                    c.id,
                    c.candidate_user_id,
                    c.last_message_id,
                    c.unread_employer,
                    c.updated_at,
                    u.email as candidate_email,
                    cand.full_name as candidate_name,
                    cand.profile_picture as candidate_picture,
                    m.body as last_message_body,
                    m.created_at as last_message_time,
                    j.id as job_id,
                    j.title as job_title
                FROM conversations c
                LEFT JOIN users u ON c.candidate_user_id = u.id
                LEFT JOIN candidates cand ON cand.user_id = u.id
                LEFT JOIN messages m ON c.last_message_id = m.id
                LEFT JOIN applications a ON a.candidate_user_id = c.candidate_user_id
                LEFT JOIN jobs j ON a.job_id = j.id AND j.employer_id = " . (int)$employerId . "
                WHERE c.employer_id = :employer_id
                ORDER BY c.updated_at DESC
                LIMIT 100";

        $conversations = $db->fetchAll($sql, ['employer_id' => $employerId]);

        $result = [];
        foreach ($conversations as $row) {
            $result[] = [
                'id' => $row['id'],
                'candidate' => [
                    'id' => $row['candidate_user_id'],
                    'email' => $row['candidate_email'] ?? '',
                    'name' => $row['candidate_name'] ?? $row['candidate_email'] ?? 'Unknown',
                    'picture' => $row['candidate_picture'] ?? null
                ],
                'last_message' => [
                    'body' => $row['last_message_body'] ?? '',
                    'created_at' => $row['last_message_time'] ?? $row['updated_at']
                ],
                'unread_count' => (int)($row['unread_employer'] ?? 0),
                'job' => $row['job_id'] ? [
                    'id' => $row['job_id'],
                    'title' => $row['job_title']
                ] : null,
                'updated_at' => $row['updated_at']
            ];
        }

        return $result;
    }

    /**
     * Get total unread messages count
     */
    private function getTotalUnreadMessages(int $employerId): int
    {
        $sql = "SELECT SUM(unread_employer) as total FROM conversations WHERE employer_id = :employer_id";
        $result = \App\Core\Database::getInstance()->fetchOne($sql, ['employer_id' => $employerId]);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Mark conversation as read - Reset unread counters
     */
    private function markConversationAsRead(int $conversationId, int $employerId, string $type): void
    {
        $conversation = Conversation::find($conversationId);
        if ($conversation && $conversation->attributes['employer_id'] === $employerId) {
            // Reset unread count
            $conversation->fill(['unread_employer' => 0]);
            $conversation->save();

            // Mark all incoming messages as read
            // Use current user's ID (which is the employer's user_id)
            $employerUserId = $this->currentUser->id;
            
            $sql = "UPDATE messages SET is_read = 1 
                    WHERE conversation_id = :conversation_id 
                    AND sender_user_id != :user_id 
                    AND is_read = 0";
            \App\Core\Database::getInstance()->query($sql, [
                'conversation_id' => $conversationId,
                'user_id' => $employerUserId
            ]);
        }
    }

    /**
     * Format message for JSON response
     */
    private function formatMessage(Message $message): array
    {
        try {
            $messageId = $message->id ?? $message->attributes['id'] ?? 0;
            $sender = $message->sender();
            $attachments = [];
            
            if (!empty($message->attributes['attachments'])) {
                $decoded = json_decode($message->attributes['attachments'], true);
                if (is_array($decoded)) {
                    $attachments = $decoded;
                }
            }

            $senderData = null;
            if ($sender) {
                $senderId = $sender->id ?? $sender->attributes['id'] ?? 0;
                $senderData = [
                    'id' => $senderId,
                    'name' => $sender->attributes['name'] ?? $sender->attributes['full_name'] ?? '',
                    'email' => $sender->attributes['email'] ?? '',
                    'role' => $sender->attributes['role'] ?? ''
                ];
            }

            return [
                'id' => $messageId,
                'conversation_id' => $message->attributes['conversation_id'] ?? 0,
                'sender_user_id' => $message->attributes['sender_user_id'] ?? 0,
                'sender' => $senderData,
                'body' => $message->attributes['body'] ?? '',
                'attachments' => $attachments,
                'is_read' => (bool)($message->attributes['is_read'] ?? 0),
                'created_at' => $message->attributes['created_at'] ?? date('Y-m-d H:i:s'),
                'is_own' => $sender && ($sender->id ?? $sender->attributes['id'] ?? 0) === $this->currentUser->id
            ];
        } catch (\Exception $e) {
            error_log("Error formatting message: " . $e->getMessage());
            // Return minimal message data
            return [
                'id' => $message->attributes['id'] ?? 0,
                'conversation_id' => $message->attributes['conversation_id'] ?? 0,
                'sender_user_id' => $message->attributes['sender_user_id'] ?? 0,
                'body' => $message->attributes['body'] ?? '',
                'attachments' => [],
                'is_read' => false,
                'created_at' => $message->attributes['created_at'] ?? date('Y-m-d H:i:s'),
                'is_own' => true
            ];
        }
    }
}
