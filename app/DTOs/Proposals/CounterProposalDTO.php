<?php

declare(strict_types=1);

namespace App\DTOs\Proposals;

class CounterProposalDTO
{
    public function __construct(
        public readonly int     $currentProposalId,
        public readonly int     $counteredByUserId,
        public readonly float   $newAmount,
        public readonly ?string $newNote,
    ) {}
}
