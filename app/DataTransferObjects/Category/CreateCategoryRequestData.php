<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Category;

use App\Models\Category;

class CreateCategoryRequest
{
    public string $name;
    public string $slug;
    public ?string $icon = null;
    public ?int $parentId = null;

    public function toArray(): array
    {
        return [
            Category::NAME => $this->name,
            Category::SLUG => $this->slug,
            Category::ICON => $this->icon,
            Category::PARENT_ID => $this->parentId,
        ];
    }
}