<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Conversation
 */
class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $authId = $request->user()->getId();
        $otherUser = $this->getSenderId() === $authId ? $this->receiver : $this->sender;
        $last = $this->messages->first();

        return [
            Conversation::ID => $this->getId(),
            Conversation::CREATED_AT => $this->getCreatedAt(),
            'other_user' => [
                'id' => $otherUser->getId(),
                'name' => $otherUser->getName(),
            ],
            'last_message' => $last ? (new MessageResource($last))->toArray($request) : null,
            'unread_count' => (int) ($this->unread_count ?? 0),
        ];
    }
}
