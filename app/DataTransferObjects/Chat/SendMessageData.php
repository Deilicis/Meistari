<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Chat;

use App\Enums\MessageTypeEnum;

final class SendMessageData
{
    public function __construct(
        public readonly int             $conversationId,
        public readonly int             $senderId,
        public readonly string          $body,
        public readonly MessageTypeEnum $type = MessageTypeEnum::TEXT,
        public readonly ?int            $proposalId = null,
    ) {}
}
