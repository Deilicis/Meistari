<?php

declare(strict_types=1);

namespace App\DTOs\CategorySuggestions;

final class RejectSuggestionDTO
{
    public function __construct(
        public readonly int $suggestionId,
        public readonly int $reviewedByUserId,
        public readonly string $reviewNote,
    ) {}
}
