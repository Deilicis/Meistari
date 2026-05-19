<?php

declare(strict_types=1);

namespace App\DTOs\CategorySuggestions;

final class SubmitCategorySuggestionDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?int $parentCategoryId,
        public readonly ?string $note,
        public readonly int $suggestedByUserId,
    ) {}
}
