<?php

declare(strict_types=1);

namespace App\Services\Repositories\Chat;

use App\DataTransferObjects\Chat\SendMessageData;
use App\DTOs\Notifications\CreateNotificationDTO;
use App\Enums\NotificationTypeEnum;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\NotificationService;
use Illuminate\Support\Collection;

class ChatLogicRepository
{
    public function __construct(
        private readonly ConversationDbRepository $conversationDb,
        private readonly MessageDbRepository $messageDb,
        private readonly NotificationService $notificationService,
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

        $conversation = $this->conversationDb->findById($dto->conversationId);
        if ($conversation) {
            $receiverId = $conversation->getSenderId() === $dto->senderId
                ? $conversation->getReceiverId()
                : $conversation->getSenderId();

            $this->notificationService->create(new CreateNotificationDTO(
                userId: $receiverId,
                type: NotificationTypeEnum::NEW_MESSAGE,
                title: 'Jauna ziņa no ' . $message->sender->getName(),
                body: mb_strimwidth($dto->body, 0, 80, '...'),
                actionUrl: route('chat.show', $dto->conversationId),
                metadata: ['conversation_id' => $dto->conversationId, 'sender_id' => $dto->senderId],
            ));
        }

        return $message;
    }
}
