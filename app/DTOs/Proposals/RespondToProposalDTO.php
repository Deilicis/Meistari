<?php

declare(strict_types=1);

namespace App\DTOs\Proposals;

class RespondToProposalDTO
{
    public function __construct(
        public readonly int $proposalId,
        public readonly int $respondedByUserId,
    ) {}
}
