<?php

declare(strict_types=1);

namespace App\Services\Repositories\Chat;

use App\Models\Conversation;
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

    public function getForUser(int $userId): Collection
    {
        return Conversation::where(Conversation::SENDER_ID, $userId)
            ->orWhere(Conversation::RECEIVER_ID, $userId)
            ->with([
                'sender',
                'receiver',
                'messages' => fn ($q) => $q->latest()->limit(1),
            ])
            ->latest()
            ->get();
    }
}
