<?php

declare(strict_types=1);

namespace App\DTOs\Categories;

class CreateCategoryDTO
{
    public function __construct(
        public readonly string  $name,
        public readonly ?int    $parentId,
        public readonly ?string $icon,
    ) {}
}
