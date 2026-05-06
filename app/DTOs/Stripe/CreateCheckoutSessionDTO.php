<?php

declare(strict_types=1);

namespace App\DTOs\Stripe;

final class CreateCheckoutSessionDTO
{
    public function __construct(
        public readonly int    $jobRequestId,
        public readonly int    $clientId,
        public readonly int    $masterId,
        public readonly float  $amount,
        public readonly string $jobTitle,
        public readonly string $successUrl,
        public readonly string $cancelUrl,
    ) {}
}
