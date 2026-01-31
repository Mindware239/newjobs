<?php

declare(strict_types=1);

namespace App\Controllers\Candidate;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Candidate;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Employer;
use App\Models\Notification;
use App\Models\Job;
use App\Models\Application;

class ChatController extends BaseController
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
     * List all conversations - Indeed-style inbox
     * SQL: Fetches conversations with last message preview, unread counts, employer info
     */
    public function index(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $userId = $candidate->attributes['user_id'];
        
        // Enterprise SQL query: Get conversations with last message, employer info, job info
        $sql = "SELECT 
                    c.id,
                    c.employer_id,
                    c.candidate_user_id,
                    c.last_message_id,
                    c.unread_candidate,
                    c.unread_employer,
                    c.created_at,
                    c.updated_at,
                    e.company_name,
                    e.logo_url as employer_logo,
                    m.body as last_message_body,
                    m.created_at as last_message_time,
                    m.sender_user_id as last_message_sender_id,
                    j.id as job_id,
                    j.title as job_title,
                    j.slug as job_slug
                FROM conversations c
                LEFT JOIN employers e ON c.employer_id = e.id
                LEFT JOIN messages m ON c.last_message_id = m.id
                LEFT JOIN applications a ON a.candidate_user_id = c.candidate_user_id
                LEFT JOIN jobs j ON a.job_id = j.id AND j.employer_id = c.employer_id
                WHERE c.candidate_user_id = :user_id
                ORDER BY c.updated_at DESC
                LIMIT 100";

        $db = \App\Core\Database::getInstance();
        $conversations = $db->fetchAll($sql, ['user_id' => $userId]);

        // Format conversations for view
        $conversationList = [];
        foreach ($conversations as $row) {
            $conversationList[] = [
                'id' => $row['id'],
                'employer_id' => $row['employer_id'],
                'employer_name' => $row['company_name'] ?? 'Unknown Employer',
                'employer_logo' => $row['employer_logo'] ?? null,
                'last_message' => $row['last_message_body'] ?? '',
                'last_message_time' => $row['last_message_time'] ?? $row['updated_at'],
                'last_message_sender_id' => $row['last_message_sender_id'] ?? null,
                'unread_count' => (int)($row['unread_candidate'] ?? 0),
                'job_id' => $row['job_id'] ?? null,
                'job_title' => $row['job_title'] ?? null,
                'job_slug' => $row['job_slug'] ?? null,
                'updated_at' => $row['updated_at']
            ];
        }

        // Get total unread count
        $unreadSql = "SELECT SUM(unread_candidate) as total FROM conversations WHERE candidate_user_id = :user_id";
        $unreadResult = $db->fetchOne($unreadSql, ['user_id' => $userId]);
        $totalUnread = (int)($unreadResult['total'] ?? 0);

        // Get selected conversation ID from query string if present
        $selectedConversationId = (int)($request->get('id') ?? 0);
        
        $response->view('candidate/chat/index', [
            'title' => 'Messages',
            'candidate' => $candidate,
            'conversations' => $conversationList,
            'totalUnread' => $totalUnread,
            'selectedConversationId' => $selectedConversationId
        ]);
    }

    /**
     * View conversation - Indeed-style chat view
     */
    public function show(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $conversationId = (int)($request->param('id') ?? $request->get('id') ?? 0);
        $conversation = Conversation::find($conversationId);

        if (!$conversation || $conversation->attributes['candidate_user_id'] != $candidate->attributes['user_id']) {
            $response->redirect('/candidate/chat');
            return;
        }

        // Mark messages as read
        $this->markConversationAsRead($conversation, $candidate->attributes['user_id']);

        $employer = $conversation->employer();
        $messages = $conversation->messages();

        // Get job info if available
        $job = null;
        $jobIds = Job::where('employer_id', '=', $conversation->attributes['employer_id'])->pluck('id');
        if (!empty($jobIds)) {
            $application = Application::where('candidate_user_id', '=', $candidate->attributes['user_id'])
                ->whereIn('job_id', $jobIds)
                ->first();
            if ($application) {
                $job = Job::find($application->attributes['job_id']);
            }
        }

        $response->view('candidate/chat/show', [
            'title' => 'Chat with ' . ($employer ? $employer->attributes['company_name'] : 'Employer'),
            'candidate' => $candidate,
            'conversation' => $conversation->attributes,
            'employer' => $employer ? $employer->attributes : [],
            'job' => $job ? $job->attributes : null,
            'messages' => array_map(function($m) use ($candidate) {
                $msgData = $m->attributes;
                $msgData['is_own'] = ($msgData['sender_user_id'] == $candidate->attributes['user_id']);
                $msgData['attachments'] = !empty($msgData['attachments']) 
                    ? json_decode($msgData['attachments'], true) 
                    : [];
                return $msgData;
            }, $messages)
        ]);
    }

    /**
     * Send message - Enterprise logic with notifications
     */
    public function sendMessage(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $data = $request->getJsonBody() ?? $request->all();
        $conversationId = (int)($data['conversation_id'] ?? 0);
        $body = trim($data['body'] ?? '');
        $hasAttachment = $request->hasFile('attachment') || !empty($data['attachment']);

        if (empty($body) && !$hasAttachment) {
            $response->json(['error' => 'Message body is required'], 422);
            return;
        }

        if (empty($body) && $hasAttachment) {
            $body = 'Sent an attachment';
        }

        $conversation = Conversation::find($conversationId);
        if (!$conversation || $conversation->attributes['candidate_user_id'] != $candidate->attributes['user_id']) {
            $response->json(['error' => 'Conversation not found'], 404);
            return;
        }

        $userId = $candidate->attributes['user_id'];

        // Handle file attachment (optional)
        $attachments = [];
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            if ($file && $file['error'] === UPLOAD_ERR_OK) {
                $storage = new \App\Core\Storage();
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
            // Update conversation: last_message_id, unread_employer, updated_at
            $conversation->fill([
                'last_message_id' => $message->id,
                'unread_employer' => ($conversation->attributes['unread_employer'] ?? 0) + 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $conversation->save();

            // Create notification for employer
            $employer = $conversation->employer();
            if ($employer) {
                $employerUser = User::where('id', '=', $employer->attributes['user_id'] ?? 0)->first();
                if ($employerUser) {
                    Notification::create(
                        $employerUser->id,
                        'message',
                        'New message from candidate',
                        substr($body, 0, 100) . (strlen($body) > 100 ? '...' : ''),
                        '/employer/messages?conversation=' . $conversationId
                    );
                }
            }

            $response->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'conversation_id' => $conversationId,
                    'sender_user_id' => $userId,
                    'body' => $body,
                    'attachments' => $attachments,
                    'is_read' => 0,
                    'created_at' => $message->attributes['created_at'],
                    'is_own' => true
                ]
            ]);
        } else {
            $response->json(['error' => 'Failed to send message'], 500);
        }
    }

    /**
     * Get new messages (for polling/real-time)
     */
    public function getMessages(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;

        $conversationId = (int)($request->get('conversation_id') ?? 0);
        $lastMessageId = (int)($request->get('last_message_id') ?? 0);

        $conversation = Conversation::find($conversationId);
        if (!$conversation || $conversation->attributes['candidate_user_id'] != $candidate->attributes['user_id']) {
            $response->json(['error' => 'Conversation not found'], 404);
            return;
        }

        // Get new messages since last_message_id
        $query = Message::where('conversation_id', '=', $conversationId);
        if ($lastMessageId > 0) {
            $query = $query->where('id', '>', $lastMessageId);
        }

        $messages = $query->orderBy('created_at', 'ASC')->get();
        
        // Mark incoming messages as read
        foreach ($messages as $msg) {
            if ($msg->attributes['sender_user_id'] != $candidate->attributes['user_id']) {
                $msg->markAsRead();
            }
        }

        // Update unread count
        $this->markConversationAsRead($conversation, $candidate->attributes['user_id']);

        $response->json([
            'success' => true,
            'messages' => array_map(function($m) use ($candidate) {
                $msgData = $m->attributes;
                $msgData['is_own'] = ($msgData['sender_user_id'] == $candidate->attributes['user_id']);
                $msgData['attachments'] = !empty($msgData['attachments']) 
                    ? json_decode($msgData['attachments'], true) 
                    : [];
                return $msgData;
            }, $messages)
        ]);
    }

    /**
     * Start conversation with employer - Auto-create if not exists
     * Can be called from job application or direct message button
     */
    public function startConversation(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) return;
        
        if (!$candidate->isPremium()) {
            $accept = strtolower($request->header('Accept', '') ?? '');
            if (strpos($accept, 'application/json') !== false || $request->isAjax()) {
                $response->json([
                    'error' => 'Premium required',
                    'message' => 'Direct chat is a premium feature. Please upgrade to start a conversation.',
                    'upgrade_url' => '/candidate/premium/plans'
                ], 403);
            } else {
                $response->redirect('/candidate/premium/plans');
            }
            return;
        }

        $data = $request->getJsonBody() ?? $request->all();
        $employerId = (int)($data['employer_id'] ?? 0);
        $jobId = (int)($data['job_id'] ?? 0);
        $initialMessage = trim($data['initial_message'] ?? '');

        if (!$employerId) {
            $response->json(['error' => 'Employer ID is required'], 422);
            return;
        }

        // Check if conversation already exists
        $existing = Conversation::where('candidate_user_id', '=', $candidate->attributes['user_id'])
            ->where('employer_id', '=', $employerId)
            ->first();

        if ($existing) {
            // If initial message provided, send it
            if (!empty($initialMessage)) {
                $message = new Message();
                $message->fill([
                    'conversation_id' => $existing->id,
                    'sender_user_id' => $candidate->attributes['user_id'],
                    'body' => $initialMessage,
                    'is_read' => 0
                ]);
                if ($message->save()) {
                    $existing->fill([
                        'last_message_id' => $message->id,
                        'unread_employer' => ($existing->attributes['unread_employer'] ?? 0) + 1,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    $existing->save();
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
            'employer_id' => $employerId,
            'candidate_user_id' => $candidate->attributes['user_id'],
            'unread_employer' => 0,
            'unread_candidate' => 0
        ]);

        if ($conversation->save()) {
            // Send initial message if provided
            if (!empty($initialMessage)) {
                $message = new Message();
                $message->fill([
                    'conversation_id' => $conversation->id,
                    'sender_user_id' => $candidate->attributes['user_id'],
                    'body' => $initialMessage,
                    'is_read' => 0
                ]);
                if ($message->save()) {
                    $conversation->fill([
                        'last_message_id' => $message->id,
                        'unread_employer' => 1,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    $conversation->save();

                    // Create notification
                    $employer = Employer::find($employerId);
                    if ($employer) {
                        $employerUser = User::where('id', '=', $employer->attributes['user_id'] ?? 0)->first();
                        if ($employerUser) {
                            Notification::create(
                                $employerUser->id,
                                'message',
                                'New message from candidate',
                                substr($initialMessage, 0, 100) . (strlen($initialMessage) > 100 ? '...' : ''),
                                '/employer/messages?conversation=' . $conversation->id
                            );
                        }
                    }
                }
            }

            $response->json([
                'success' => true,
                'conversation_id' => $conversation->id
            ]);
        } else {
            $response->json(['error' => 'Failed to create conversation'], 500);
        }
    }

    /**
     * Mark conversation as read - Reset unread counters
     */
    private function markConversationAsRead(Conversation $conversation, int $userId): void
    {
        // Mark all incoming messages as read
        $messages = Message::where('conversation_id', '=', $conversation->id)
            ->where('sender_user_id', '!=', $userId)
            ->where('is_read', '=', 0)
            ->get();

        foreach ($messages as $msg) {
            $msg->markAsRead();
        }

        // Update unread count to 0
        $conversation->fill(['unread_candidate' => 0]);
        $conversation->save();
    }

    /**
     * Get unread count for candidate
     */
    public function getUnreadCount(Request $request, Response $response): void
    {
        $candidate = $this->ensureCandidate($request, $response);
        if (!$candidate) {
            $response->json(['unread_count' => 0]);
            return;
        }

        $sql = "SELECT SUM(unread_candidate) as total FROM conversations WHERE candidate_user_id = :user_id";
        $result = \App\Core\Database::getInstance()->fetchOne($sql, ['user_id' => $candidate->attributes['user_id']]);
        $totalUnread = (int)($result['total'] ?? 0);

        $response->json(['unread_count' => $totalUnread]);
    }
}
