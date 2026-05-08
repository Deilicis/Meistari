<?php

declare(strict_types=1);

namespace App\DTOs\Proposals;

class SubmitProposalDTO
{
    public function __construct(
        public readonly int     $jobApplicationId,
        public readonly int     $proposedByUserId,
        public readonly float   $amount,
        public readonly ?string $note,
    ) {}
}
