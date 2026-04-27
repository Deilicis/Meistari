<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Chat;

final class SendMessageData
{
    public function __construct(
        public readonly int $conversationId,
        public readonly int $senderId,
        public readonly string $body,
    ) {}
}