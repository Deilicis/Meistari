<?php

declare(strict_types=1);

namespace App\DTOs\Jobs;

final class MarkJobCompleteDTO
{
    public function __construct(
        public readonly int $jobRequestId,
        public readonly int $masterId,
        public readonly ?string $completionNote = null,
    ) {}
}
