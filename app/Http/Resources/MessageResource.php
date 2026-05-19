<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\MessageTypeEnum;
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
        $data = [
            Message::ID => $this->getId(),
            Message::CONVERSATION_ID => $this->getConversationId(),
            Message::SENDER_ID => $this->getSenderId(),
            Message::BODY => $this->getBody(),
            Message::TYPE => $this->getType()->value,
            Message::PROPOSAL_ID => $this->getProposalId(),
            Message::READ_AT => $this->getReadAt(),
            Message::CREATED_AT => $this->getCreatedAt(),
            'sender' => [
                'id' => $this->sender->getId(),
                'name' => $this->sender->getName(),
            ],
            'proposal' => null,
        ];

        if ($this->getType() === MessageTypeEnum::PROPOSAL && $this->proposal) {
            $data['proposal'] = (new PriceProposalResource($this->proposal))->toArray($request);
        }

        return $data;
    }
}
