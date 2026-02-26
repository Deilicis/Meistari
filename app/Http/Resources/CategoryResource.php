<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            Category::ID => $this->getId(),
            Category::NAME => $this->getName(),
            Category::SLUG => $this->getSlug(),
            Category::ICON => $this->getIcon(),
            Category::PARENT_ID => $this->getParentId(),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}