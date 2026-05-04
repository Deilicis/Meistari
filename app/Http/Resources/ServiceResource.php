<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Service
 */
class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            Service::ID => $this->getId(),
            Service::TITLE => $this->getTitle(),
            Service::SLUG => $this->getSlug(),
            Service::DESCRIPTION => $this->getDescription(),
            Service::PRICE => $this->getPrice(),
            Service::PRICE_TYPE => $this->getPriceType()->value,
            Service::LOCATION => $this->getLocation(),
            Service::IS_ACTIVE          => $this->getIsActive(),
            Service::CREATED_AT         => $this->getCreatedAt(),
            'applications_count'        => $this->whenCounted('applications'),
            'category'                  => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}