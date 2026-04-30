<?php

declare(strict_types=1);

namespace App\DTOs\Jobs;

final class AcceptApplicationDTO
{
    public function __construct(
        public readonly int $jobRequestId,
        public readonly int $applicationId,
        public readonly int $clientId,
    ) {}
}
