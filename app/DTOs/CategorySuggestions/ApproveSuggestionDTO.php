<?php

declare(strict_types=1);

namespace App\DTOs\CategorySuggestions;

final class ApproveSuggestionDTO
{
    public function __construct(
        public readonly int     $suggestionId,
        public readonly int     $reviewedByUserId,
        public readonly ?string $icon = null,
    ) {}
}
