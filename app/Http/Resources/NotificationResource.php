<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Notification
 */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            Notification::ID => $this->getId(),
            Notification::TYPE => $this->getType()->value,
            Notification::TITLE => $this->getTitle(),
            Notification::BODY => $this->getBody(),
            Notification::ACTION_URL => $this->getActionUrl(),
            Notification::METADATA => $this->getMetadata(),
            Notification::READ_AT => $this->getReadAt()?->toISOString(),
            Notification::CREATED_AT => $this->getCreatedAt()?->toISOString(),
            'is_read' => $this->isRead(),
        ];
    }
}
