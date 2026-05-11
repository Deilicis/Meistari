<?php

declare(strict_types=1);

namespace App\Services\Repositories\Chat;

use App\Models\Application;
use App\Models\Conversation;
use App\Models\JobRequest;
use Illuminate\Support\Collection;

class ConversationDbRepository
{
    public function findById(int $id): ?Conversation
    {
        return Conversation::find($id);
    }

    public function findBetweenUsers(int $userA, int $userB): ?Conversation
    {
        return Conversation::where(
            fn ($q) => $q->where(Conversation::SENDER_ID, $userA)->where(Conversation::RECEIVER_ID, $userB)
        )->orWhere(
            fn ($q) => $q->where(Conversation::SENDER_ID, $userB)->where(Conversation::RECEIVER_ID, $userA)
        )->first();
    }

    public function create(int $senderId, int $receiverId): Conversation
    {
        return Conversation::create([
            Conversation::SENDER_ID   => $senderId,
            Conversation::RECEIVER_ID => $receiverId,
        ]);
    }

    public function findRelatedJobForConversation(int $conversationId): ?JobRequest
    {
        $conversation = $this->findById($conversationId);
        if (!$conversation) {
            return null;
        }

        $a = $conversation->getSenderId();
        $b = $conversation->getReceiverId();

        return JobRequest::select('job_requests.*')
            ->join('applications', 'applications.' . Application::JOB_REQUEST_ID, '=', 'job_requests.' . JobRequest::ID)
            ->where(function ($q) use ($a, $b) {
                $q->where('applications.' . Application::USER_ID, $a)
                  ->where('job_requests.' . JobRequest::USER_ID, $b);
            })
            ->orWhere(function ($q) use ($a, $b) {
                $q->where('applications.' . Application::USER_ID, $b)
                  ->where('job_requests.' . JobRequest::USER_ID, $a);
            })
            ->orderByDesc('applications.' . Application::CREATED_AT)
            ->first();
    }

    public function getForUser(int $userId): Collection
    {
        return Conversation::where(Conversation::SENDER_ID, $userId)
            ->orWhere(Conversation::RECEIVER_ID, $userId)
            ->with([
                'sender',
                'receiver',
                'messages' => fn ($q) => $q->latest()->limit(1),
            ])
            ->withCount(['messages as unread_count' => fn ($q) => $q->where('sender_id', '!=', $userId)->whereNull('read_at')])
            ->orderByRaw('(SELECT MAX(m.created_at) FROM messages m WHERE m.conversation_id = conversations.id AND m.deleted_at IS NULL) DESC')
            ->get();
    }
}
