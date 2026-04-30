<?php

declare(strict_types=1);

namespace App\DTOs\Jobs;

final class CancelJobDTO
{
    public function __construct(
        public readonly int $jobRequestId,
        public readonly int $userId,
        public readonly ?string $reason = null,
    ) {}
}
