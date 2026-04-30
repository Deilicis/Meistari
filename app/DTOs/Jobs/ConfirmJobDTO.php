<?php

declare(strict_types=1);

namespace App\DTOs\Jobs;

final class ConfirmJobDTO
{
    public function __construct(
        public readonly int $jobRequestId,
        public readonly int $clientId,
    ) {}
}
