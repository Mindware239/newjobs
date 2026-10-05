<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Models\Candidate;
use App\Models\Conversation;
use App\Models\Employer;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use App\Repositories\ApiChatRepository;

class ChatController extends ApiController
{
    private ApiChatRepository $chatRepository;

    public function __construct()
    {
        $this->chatRepository = new ApiChatRepository();
    }

    public function listConversations(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        if ($user->role === 'employer') {
            $employer = $user->employer();
            if (!$employer) {
                $this->error($response, 'Employer profile not found', 404);
                return;
            }

            $rows = $this->chatRepository->getEmployerConversations((int)$employer->id);
        } else {
            $rows = $this->chatRepository->getCandidateConversations((int)$user->id);
        }

        $conversations = array_map(function (array $row) use ($user): array {
            return [
                'id' => (int)$row['id'],
                'other_user' => [
                    'id' => (int)($user->role === 'employer' ? ($row['candidate_user_id'] ?? 0) : ($row['employer_id'] ?? 0)),
                    'name' => $row['other_name'] ?: ($row['other_email'] ?? 'Unknown'),
                    'email' => $row['other_email'] ?? '',
                ],
                'last_message' => [
                    'body' => $row['last_message_body'] ?? '',
                    'created_at' => $row['last_message_time'] ?? $row['updated_at'],
                ],
                'unread_count' => (int)($row['unread_count'] ?? 0),
                'updated_at' => $row['updated_at'],
            ];
        }, $rows);

        $this->success($response, ['conversations' => $conversations]);
    }

    public function createConversation(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $data = $request->getJsonBody();
        $initialMessage = trim((string)($data['initial_message'] ?? ''));

        [$conversation, $error, $created] = $this->findOrCreateConversationForUser($user, $data);
        if (!$conversation) {
            $this->error($response, $error ?? 'Unable to create conversation', 422);
            return;
        }

        if ($initialMessage !== '') {
            $created = $this->storeMessage($conversation, (int)$user->id, $initialMessage);
            if (!$created) {
                $this->error($response, 'Conversation created but failed to send initial message', 500);
                return;
            }
        }

        $this->success($response, [
            'id' => (int)$conversation->id,
            'conversation_id' => (int)$conversation->id,
        ], 'Conversation ready', $created ? 201 : 200);
    }

    public function getMessages(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $conversation = $this->authorizedConversation($user, $id);
        if (!$conversation) {
            $this->error($response, 'Conversation not found', 404);
            return;
        }

        $this->markConversationReadForUser($conversation, $user);

        $messages = Message::where('conversation_id', '=', $id)
            ->orderBy('created_at', 'ASC')
            ->get();

        $this->success($response, [
            'messages' => array_map(fn($message) => $this->formatMessage($message, (int)$user->id), $messages),
        ]);
    }

    public function sendMessage(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $conversation = $this->authorizedConversation($user, $id);
        if (!$conversation) {
            $this->error($response, 'Conversation not found', 404);
            return;
        }

        $data = $request->getJsonBody();
        $body = trim((string)($data['content'] ?? $data['body'] ?? ''));
        if ($body === '') {
            $this->error($response, 'content is required', 422);
            return;
        }

        $message = $this->storeMessage($conversation, (int)$user->id, $body);
        if (!$message) {
            $this->error($response, 'Failed to send message', 500);
            return;
        }

        $this->success($response, [
            'id' => (int)$message->id,
            'message' => $this->formatMessage($message, (int)$user->id),
        ], 'Message sent', 201);
    }

    public function deleteMessage(Request $request, Response $response, int $id, int $message_id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $conversation = $this->authorizedConversation($user, $id);
        if (!$conversation) {
            $this->error($response, 'Conversation not found', 404);
            return;
        }

        $message = Message::find($message_id);
        if (!$message || (int)$message->conversation_id !== $id || (int)$message->sender_user_id !== (int)$user->id) {
            $this->error($response, 'Cannot delete this message', 403);
            return;
        }

        try {
            $message->delete();
        } catch (\Throwable $e) {
            error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
            $this->error($response, 'Database error occurred. Please try again.', 500);
            return;
        }
        $this->success($response, [], 'Message deleted');
    }

    public function editMessage(Request $request, Response $response, int $id, int $message_id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $conversation = $this->authorizedConversation($user, $id);
        if (!$conversation) {
            $this->error($response, 'Conversation not found', 404);
            return;
        }

        $message = Message::find($message_id);
        if (!$message || (int)$message->conversation_id !== $id || (int)$message->sender_user_id !== (int)$user->id) {
            $this->error($response, 'Cannot edit this message', 403);
            return;
        }

        $data = $request->getJsonBody();
        $body = trim((string)($data['content'] ?? $data['body'] ?? ''));
        if ($body === '') {
            $this->error($response, 'content is required', 422);
            return;
        }

        $message->fill(['body' => $body]);
        try {
            $message->save();
        } catch (\Throwable $e) {
            error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
            $this->error($response, 'Database error occurred. Please try again.', 500);
            return;
        }

        $this->success($response, ['message' => $this->formatMessage($message, (int)$user->id)], 'Message updated');
    }

    public function markAsRead(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $conversation = $this->authorizedConversation($user, $id);
        if (!$conversation) {
            $this->error($response, 'Conversation not found', 404);
            return;
        }

        $this->markConversationReadForUser($conversation, $user);
        $this->success($response, [], 'Conversation marked as read');
    }

    public function deleteConversation(Request $request, Response $response, int $id): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        $conversation = $this->authorizedConversation($user, $id);
        if (!$conversation) {
            $this->error($response, 'Conversation not found', 404);
            return;
        }

        try {
            $conversation->delete();
        } catch (\Throwable $e) {
            error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
            $this->error($response, 'Database error occurred. Please try again.', 500);
            return;
        }
        $this->success($response, [], 'Conversation deleted');
    }

    public function blockUser(Request $request, Response $response, int $id): void
    {
        $this->error($response, 'Block user is not supported by the current chat schema', 501);
    }

    public function archiveConversation(Request $request, Response $response, int $id): void
    {
        $this->error($response, 'Archive conversation is not supported by the current chat schema', 501);
    }

    public function unreadCount(Request $request, Response $response): void
    {
        $user = $this->user($request);
        if (!$user) {
            $this->error($response, 'Unauthorized', 401);
            return;
        }

        if ($user->role === 'employer') {
            $employer = $user->employer();
            if (!$employer) {
                $this->error($response, 'Employer profile not found', 404);
                return;
            }

            $unreadCount = $this->chatRepository->getEmployerUnreadCount((int)$employer->id);
        } else {
            $unreadCount = $this->chatRepository->getCandidateUnreadCount((int)$user->id);
        }

        $this->success($response, ['unread_count' => (int)($unreadCount ?? 0)]);
    }

    private function authorizedConversation(User $user, int $conversationId): ?Conversation
    {
        $conversation = Conversation::find($conversationId);
        if (!$conversation) {
            return null;
        }

        if ($user->role === 'employer') {
            $employer = $user->employer();
            if (!$employer || (int)$conversation->employer_id !== (int)$employer->id) {
                return null;
            }
            return $conversation;
        }

        return (int)$conversation->candidate_user_id === (int)$user->id ? $conversation : null;
    }

    private function findOrCreateConversationForUser(User $user, array $data): array
    {
        if ($user->role === 'employer') {
            $employer = $user->employer();
            if (!$employer) {
                return [null, 'Employer profile not found', false];
            }

            $candidate = $this->resolveCandidateParticipant($data);
            if (!$candidate) {
                return [null, 'candidate_id, candidate_user_id, or user_id is required', false];
            }
            $candidateUserId = (int)$candidate->user_id;

            $conversation = Conversation::where('employer_id', '=', (int)$employer->id)
                ->where('candidate_user_id', '=', $candidateUserId)
                ->first();

            if ($conversation) {
                return [$conversation, null, false];
            }

            $conversation = new Conversation();
            $conversation->fill([
                'employer_id' => (int)$employer->id,
                'candidate_user_id' => $candidateUserId,
                'unread_employer' => 0,
                'unread_candidate' => 0,
            ]);

            try {
                $saved = $conversation->save();
            } catch (\Throwable $e) {
                error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
                return [null, 'Database error occurred. Please try again.', false];
            }
            return [$saved ? $conversation : null, $saved ? null : 'Failed to create conversation', (bool)$saved];
        }

        $employer = $this->resolveEmployerParticipant($data);
        if (!$employer) {
            return [null, 'employer_id or user_id is required', false];
        }

        if ((int)$employer->user_id === (int)$user->id) {
            return [null, 'Cannot create conversation with yourself', false];
        }

        $conversation = Conversation::where('candidate_user_id', '=', (int)$user->id)
            ->where('employer_id', '=', (int)$employer->id)
            ->first();

        if ($conversation) {
            return [$conversation, null, false];
        }

        $conversation = new Conversation();
        $conversation->fill([
            'employer_id' => (int)$employer->id,
            'candidate_user_id' => (int)$user->id,
            'unread_employer' => 0,
            'unread_candidate' => 0,
        ]);

        try {
            $saved = $conversation->save();
        } catch (\Throwable $e) {
            error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
            return [null, 'Database error occurred. Please try again.', false];
        }
        return [$saved ? $conversation : null, $saved ? null : 'Failed to create conversation', (bool)$saved];
    }

    private function resolveEmployerParticipant(array $data): ?Employer
    {
        $employerId = (int)($data['employer_id'] ?? 0);
        if ($employerId > 0) {
            return Employer::find($employerId);
        }

        $userId = (int)($data['user_id'] ?? 0);
        return $userId > 0 ? Employer::findByUserId($userId) : null;
    }

    private function resolveCandidateParticipant(array $data): ?Candidate
    {
        $candidateId = (int)($data['candidate_id'] ?? 0);
        if ($candidateId > 0) {
            return Candidate::find($candidateId);
        }

        $candidateUserId = (int)($data['candidate_user_id'] ?? ($data['user_id'] ?? 0));
        return $candidateUserId > 0 ? Candidate::findByUserId($candidateUserId) : null;
    }

    private function storeMessage(Conversation $conversation, int $senderUserId, string $body): ?Message
    {
        $message = new Message();
        $message->fill([
            'conversation_id' => (int)$conversation->id,
            'sender_user_id' => $senderUserId,
            'body' => $body,
            'attachments' => null,
            'is_read' => 0,
        ]);

        try {
            if (!$message->save()) {
                return null;
            }
        } catch (\Throwable $e) {
            error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
            return null;
        }

        $isEmployerSender = (int)$conversation->candidate_user_id !== $senderUserId;
        $conversation->fill([
            'last_message_id' => (int)$message->id,
            'unread_employer' => $isEmployerSender ? 0 : ((int)($conversation->unread_employer ?? 0) + 1),
            'unread_candidate' => $isEmployerSender ? ((int)($conversation->unread_candidate ?? 0) + 1) : 0,
        ]);
        
        try {
            $conversation->save();
        } catch (\Throwable $e) {
            error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
        }

        $recipientUserId = $isEmployerSender
            ? (int)$conversation->candidate_user_id
            : (int)(Employer::find((int)$conversation->employer_id)?->user_id ?? 0);

        if ($recipientUserId > 0) {
            Notification::create(
                $recipientUserId,
                'message',
                $isEmployerSender ? 'New message from employer' : 'New message from candidate',
                strlen($body) > 100 ? substr($body, 0, 100) . '...' : $body,
                $isEmployerSender
                    ? '/candidate/chat/' . (int)$conversation->id
                    : '/employer/messages?conversation=' . (int)$conversation->id
            );
        }

        return $message;
    }

    private function markConversationReadForUser(Conversation $conversation, User $user): void
    {
        $messages = Message::where('conversation_id', '=', (int)$conversation->id)
            ->where('sender_user_id', '!=', (int)$user->id)
            ->where('is_read', '=', 0)
            ->get();

        foreach ($messages as $message) {
            $message->markAsRead();
        }

        if ($user->role === 'employer') {
            $conversation->fill(['unread_employer' => 0]);
        } else {
            $conversation->fill(['unread_candidate' => 0]);
        }

        try {
            $conversation->save();
        } catch (\Throwable $e) {
            error_log("API Error in " . get_class($this) . ": " . $e->getMessage());
        }
    }

    private function formatMessage(Message $message, int $currentUserId): array
    {
        $attachments = [];
        if (!empty($message->attachments)) {
            $decoded = json_decode((string)$message->attachments, true);
            if (is_array($decoded)) {
                $attachments = $decoded;
            }
        }

        return [
            'id' => (int)$message->id,
            'conversation_id' => (int)$message->conversation_id,
            'sender_user_id' => (int)$message->sender_user_id,
            'body' => $message->body ?? '',
            'attachments' => $attachments,
            'is_read' => (bool)$message->is_read,
            'created_at' => $message->created_at,
            'is_own' => (int)$message->sender_user_id === $currentUserId,
        ];
    }
}
