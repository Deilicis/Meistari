<?php

declare(strict_types=1);

namespace App\DTOs\CategorySuggestions;

final class MergeSuggestionDTO
{
    public function __construct(
        public readonly int $suggestionId,
        public readonly int $targetCategoryId,
        public readonly int $reviewedByUserId,
    ) {}
}
