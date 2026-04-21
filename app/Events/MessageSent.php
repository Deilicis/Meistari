<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Message $message
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('conversation.' . $this->message->getConversationId());
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->getId(),
            'conversation_id' => $this->message->getConversationId(),
            'sender_id' => $this->message->getSenderId(),
            'body' => $this->message->getBody(),
            'created_at' => $this->message->getCreatedAt(),
            'sender' => [
                'id' => $this->message->sender->getId(),
                'name' => $this->message->sender->getName(),
            ],
        ];
    }
}