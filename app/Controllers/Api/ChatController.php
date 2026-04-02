<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Api\ApiController;
use App\Core\Request;
use App\Core\Response;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\NotificationService;

class ChatController extends ApiController
{
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    /**
     * GET /conversations
     * List conversations for user
     */
    public function listConversations(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('per_page', 20);

        $conversations = Conversation::where(function($query) use ($user) {
            $query->where('user1_id', '=', $user->id)
                  ->orWhere('user2_id', '=', $user->id);
        })
        ->orderBy('last_message_at', 'DESC')
        ->paginate($perPage, $page);

        $data = [];
        foreach ($conversations['data'] as $conv) {
            $otherUser = $conv->user1_id === $user->id ? $conv->user2 : $conv->user1;
            $data[] = [
                'id' => $conv->id,
                'other_user' => [
                    'id' => $otherUser->id,
                    'name' => $otherUser->email,
                    'avatar' => $otherUser->avatar ?? null
                ],
                'last_message' => $conv->lastMessage(),
                'unread_count' => $conv->unreadCount($user->id),
                'last_message_at' => $conv->last_message_at,
                'is_archived' => $conv->archived_by_user === $user->id
            ];
        }

        $this->success($response, [
            'conversations' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $conversations['total'],
                'last_page' => ceil($conversations['total'] / $perPage)
            ]
        ]);
    }

    /**
     * POST /conversations
     * Create new conversation
     */
    public function createConversation(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'user_id' => 'required|numeric',
            'initial_message' => 'sometimes|string'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $otherUserId = (int)$request->input('user_id');
        if ($otherUserId === $user->id) {
            $this->error($response, 'Cannot create conversation with yourself', 400);
            return;
        }

        $otherUser = User::find($otherUserId);
        if (!$otherUser) {
            $this->error($response, 'User not found', 404);
            return;
        }

        // Check if conversation exists
        $existing = Conversation::where(function($query) use ($user, $otherUserId) {
            $query->where('user1_id', '=', $user->id)->where('user2_id', '=', $otherUserId)
                  ->orWhere('user1_id', '=', $otherUserId)->where('user2_id', '=', $user->id);
        })->first();

        if ($existing) {
            $this->success($response, [
                'id' => $existing->id,
                'other_user_id' => $otherUserId
            ], 'Conversation already exists', 200);
            return;
        }

        // Create conversation
        $conversation = new Conversation();
        $conversation->fill([
            'user1_id' => $user->id,
            'user2_id' => $otherUserId
        ])->save();

        // Send initial message if provided
        if ($request->input('initial_message')) {
            $message = new Message();
            $message->fill([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'content' => $request->input('initial_message'),
                'type' => 'text'
            ])->save();

            $conversation->last_message_at = date('Y-m-d H:i:s');
            $conversation->save();

            // Send notification to other user
            $this->notificationService->send(
                $otherUserId,
                'new_message',
                'New message from ' . $user->email,
                ['conversation_id' => $conversation->id]
            );
        }

        $this->success($response, [
            'id' => $conversation->id,
            'other_user_id' => $otherUserId
        ], 'Conversation created', 201);
    }

    /**
     * GET /conversations/{id}/messages
     * Get messages in conversation
     */
    public function getMessages(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $conversation = Conversation::find($id);
        if (!$conversation || !$conversation->hasUser($user->id)) {
            $this->error($response, 'Conversation not found', 404);
            return;
        }

        $page = (int)$request->query('page', 1);
        $perPage = (int)$request->query('per_page', 50);

        $messages = Message::where('conversation_id', '=', $id)
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage, $page);

        // Mark as read
        Message::where('conversation_id', '=', $id)
            ->where('sender_id', '!=', $user->id)
            ->where('read_at', '=', null)
            ->update(['read_at' => date('Y-m-d H:i:s')]);

        $this->success($response, [
            'messages' => $messages['data'],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $messages['total'],
                'last_page' => ceil($messages['total'] / $perPage)
            ]
        ]);
    }

    /**
     * POST /conversations/{id}/messages
     * Send message
     */
    public function sendMessage(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $conversation = Conversation::find($id);
        if (!$conversation || !$conversation->hasUser($user->id)) {
            $this->error($response, 'Conversation not found', 404);
            return;
        }

        $errors = $this->validate($request->getJsonBody(), [
            'content' => 'required|string',
            'type' => 'sometimes|in:text,image,file'
        ]);

        if (!empty($errors)) {
            $this->validationError($response, $errors);
            return;
        }

        $message = new Message();
        $message->fill([
            'conversation_id' => $id,
            'sender_id' => $user->id,
            'content' => $request->input('content'),
            'type' => $request->input('type', 'text'),
            'metadata' => $request->input('metadata') ? json_encode($request->input('metadata')) : null
        ])->save();

        $conversation->last_message_at = date('Y-m-d H:i:s');
        $conversation->save();

        // Get other user
        $otherUserId = $conversation->user1_id === $user->id ? $conversation->user2_id : $conversation->user1_id;

        // Send notification
        $this->notificationService->send(
            $otherUserId,
            'new_message',
            'New message from ' . $user->email,
            ['conversation_id' => $id, 'message_id' => $message->id]
        );

        $this->success($response, [
            'id' => $message->id,
            'content' => $message->content,
            'sender_id' => $user->id,
            'created_at' => $message->created_at
        ], 'Message sent', 201);
    }

    /**
     * DELETE /conversations/{id}/messages/{msg_id}
     * Delete message
     */
    public function deleteMessage(Request $request, Response $response, int $id, int $msg_id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $conversation = Conversation::find($id);
        if (!$conversation || !$conversation->hasUser($user->id)) {
            $this->error($response, 'Conversation not found', 404);
            return;
        }

        $message = Message::find($msg_id);
        if (!$message || $message->sender_id !== $user->id) {
            $this->error($response, 'Cannot delete this message', 403);
            return;
        }

        $message->delete();

        $this->success($response, [], 'Message deleted');
    }

    /**
     * PATCH /conversations/{id}/messages/{msg_id}
     * Edit message
     */
    public function editMessage(Request $request, Response $response, int $id, int $msg_id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $message = Message::find($msg_id);
        if (!$message || $message->sender_id !== $user->id) {
            $this->error($response, 'Cannot edit this message', 403);
            return;
        }

        $message->content = $request->input('content');
        $message->edited_at = date('Y-m-d H:i:s');
        $message->save();

        $this->success($response, ['id' => $message->id, 'content' => $message->content]);
    }

    /**
     * POST /conversations/{id}/read
     * Mark conversation as read
     */
    public function markAsRead(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $conversation = Conversation::find($id);
        if (!$conversation || !$conversation->hasUser($user->id)) {
            $this->error($response, 'Conversation not found', 404);
            return;
        }

        Message::where('conversation_id', '=', $id)
            ->where('sender_id', '!=', $user->id)
            ->where('read_at', '=', null)
            ->update(['read_at' => date('Y-m-d H:i:s')]);

        $this->success($response, [], 'Conversation marked as read');
    }

    /**
     * DELETE /conversations/{id}
     * Delete conversation
     */
    public function deleteConversation(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $conversation = Conversation::find($id);
        if (!$conversation || !$conversation->hasUser($user->id)) {
            $this->error($response, 'Conversation not found', 404);
            return;
        }

        $conversation->delete();

        $this->success($response, [], 'Conversation deleted');
    }

    /**
     * POST /conversations/{id}/block
     * Block user in conversation
     */
    public function blockUser(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $conversation = Conversation::find($id);
        if (!$conversation || !$conversation->hasUser($user->id)) {
            $this->error($response, 'Conversation not found', 404);
            return;
        }

        if ($conversation->user1_id === $user->id) {
            $conversation->blocked_by_user1 = true;
        } else {
            $conversation->blocked_by_user2 = true;
        }
        $conversation->save();

        $this->success($response, [], 'User blocked');
    }

    /**
     * POST /conversations/{id}/archive
     * Archive conversation
     */
    public function archiveConversation(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $conversation = Conversation::find($id);
        if (!$conversation || !$conversation->hasUser($user->id)) {
            $this->error($response, 'Conversation not found', 404);
            return;
        }

        $conversation->archived_by_user = $user->id;
        $conversation->save();

        $this->success($response, [], 'Conversation archived');
    }

    /**
     * GET /conversations/unread-count
     * Get total unread count
     */
    public function unreadCount(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $count = Message::whereIn('conversation_id', function($q) use ($user) {
            $q->select('id')->from('conversations')
              ->where('user1_id', '=', $user->id)
              ->orWhere('user2_id', '=', $user->id);
        })
        ->where('sender_id', '!=', $user->id)
        ->where('read_at', '=', null)
        ->count();

        $this->success($response, ['unread_count' => $count]);
    }
}
