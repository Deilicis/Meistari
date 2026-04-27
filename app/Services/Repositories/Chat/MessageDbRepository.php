<?php

declare(strict_types=1);

namespace App\Services\Repositories\Chat;

use App\DataTransferObjects\Chat\SendMessageData;
use App\Models\Message;
use Illuminate\Support\Collection;

class MessageDbRepository
{
    public function create(SendMessageData $dto): Message
    {
        return Message::create([
            Message::CONVERSATION_ID => $dto->conversationId,
            Message::SENDER_ID       => $dto->senderId,
            Message::BODY            => $dto->body,
        ]);
    }

    public function getForConversation(int $conversationId): Collection
    {
        return Message::where(Message::CONVERSATION_ID, $conversationId)
            ->with('sender')
            ->orderBy(Message::CREATED_AT)
            ->get();
    }

    public function markAsRead(int $conversationId, int $userId): void
    {
        Message::where(Message::CONVERSATION_ID, $conversationId)
            ->where(Message::SENDER_ID, '!=', $userId)
            ->whereNull(Message::READ_AT)
            ->update([Message::READ_AT => now()]);
    }
}
