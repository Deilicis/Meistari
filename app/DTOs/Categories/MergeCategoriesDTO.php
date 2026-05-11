<?php

declare(strict_types=1);

namespace App\DTOs\Categories;

class MergeCategoriesDTO
{
    public function __construct(
        public readonly int $sourceId,
        public readonly int $targetId,
    ) {}
}
