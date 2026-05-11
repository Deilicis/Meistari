<?php

declare(strict_types=1);

namespace App\DTOs\Categories;

class UpdateCategoryDTO
{
    public function __construct(
        public readonly string  $name,
        public readonly ?string $icon,
        public readonly ?int    $parentId,
        public readonly bool    $regenerateSlug,
    ) {}
}
