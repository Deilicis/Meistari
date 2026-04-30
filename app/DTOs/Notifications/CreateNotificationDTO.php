<?php

declare(strict_types=1);

namespace App\DTOs\Notifications;

use App\Enums\NotificationTypeEnum;

final class CreateNotificationDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly NotificationTypeEnum $type,
        public readonly string $title,
        public readonly ?string $body = null,
        public readonly ?string $actionUrl = null,
        public readonly ?array $metadata = null,
    ) {}
}
