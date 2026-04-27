<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Message
 */
class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            Message::ID              => $this->getId(),
            Message::CONVERSATION_ID => $this->getConversationId(),
            Message::SENDER_ID       => $this->getSenderId(),
            Message::BODY            => $this->getBody(),
            Message::READ_AT         => $this->getReadAt(),
            Message::CREATED_AT      => $this->getCreatedAt(),
            'sender'                 => [
                'id'   => $this->sender->getId(),
                'name' => $this->sender->getName(),
            ],
        ];
    }
}
