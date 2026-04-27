<?php

declare(strict_types=1);

namespace App\Services\Repositories\Chat;

use App\DataTransferObjects\Chat\SendMessageData;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Collection;

class ChatLogicRepository
{
    public function __construct(
        private readonly ConversationDbRepository $conversationDb,
        private readonly MessageDbRepository $messageDb,
    ) {}

    public function getOrCreateConversation(int $senderId, int $receiverId): Conversation
    {
        return $this->conversationDb->findBetweenUsers($senderId, $receiverId)
            ?? $this->conversationDb->create($senderId, $receiverId);
    }

    public function getConversationsForUser(int $userId): Collection
    {
        return $this->conversationDb->getForUser($userId);
    }

    public function getMessages(int $conversationId, int $userId): Collection
    {
        $this->messageDb->markAsRead($conversationId, $userId);

        return $this->messageDb->getForConversation($conversationId);
    }

    public function sendMessage(SendMessageData $dto): Message
    {
        $message = $this->messageDb->create($dto);
        $message->load('sender');

        try {
            MessageSent::dispatch($message);
        } catch (\Throwable) {
            // Broadcasting failure does not prevent message delivery
        }

        return $message;
    }
}
