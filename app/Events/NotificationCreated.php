<?php

declare(strict_types=1);

namespace App\Events;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Notification $notification,
    ) {}

    public function broadcastAs(): string
    {
        return 'NotificationCreated';
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('notifications.' . $this->notification->getUserId());
    }

    public function broadcastWith(): array
    {
        return (new NotificationResource($this->notification))->resolve();
    }
}
