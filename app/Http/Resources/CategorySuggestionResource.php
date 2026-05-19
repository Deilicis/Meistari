<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CategorySuggestion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CategorySuggestion
 */
class CategorySuggestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'parent_category' => $this->whenLoaded('parentCategory', fn () => $this->parentCategory ? [
                'id' => $this->parentCategory->getId(),
                'name' => $this->parentCategory->getName(),
            ] : null),
            'note' => $this->getNote(),
            'status' => $this->getStatus()->value,
            'status_label' => $this->getStatus()->label(),
            'created_at' => $this->getCreatedAt(),
            'suggested_by' => $this->whenLoaded('suggestedBy', fn () => $this->suggestedBy ? [
                'id' => $this->suggestedBy->getId(),
                'name' => $this->suggestedBy->getName(),
            ] : null),
        ];
    }
}
