<?php

declare(strict_types=1);

namespace App\Events;

use App\Enums\MessageTypeEnum;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Message $message
    ) {}

    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('conversation.' . $this->message->getConversationId());
    }

    public function broadcastWith(): array
    {
        return (new MessageResource($this->message))->toArray(request());
    }
}