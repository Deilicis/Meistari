<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\JobRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin JobRequest
 */
class JobRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            JobRequest::ID => $this->getId(),
            JobRequest::TITLE => $this->getTitle(),
            JobRequest::SLUG => $this->getSlug(),
            JobRequest::DESCRIPTION => $this->getDescription(),
            JobRequest::BUDGET => $this->getBudget(),
            JobRequest::LOCATION => $this->getLocation(),
            JobRequest::DEADLINE => $this->getDeadline(),
            JobRequest::STATUS => $this->getStatus()->value,
            JobRequest::CREATED_AT => $this->getCreatedAt(),
            
            'category'                    => new CategoryResource($this->whenLoaded('category')),
            'pending_category_suggestion' => $this->whenLoaded('pendingCategorySuggestion', fn () =>
                $this->pendingCategorySuggestion ? [
                    'id'     => $this->pendingCategorySuggestion->getId(),
                    'name'   => $this->pendingCategorySuggestion->getName(),
                    'status' => $this->pendingCategorySuggestion->getStatus()->value,
                ] : null
            ),
        ];
    }
}