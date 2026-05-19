<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profile = $this->resource->user?->profile;

        return [
            'id' => $this->resource->getId(),
            'cover_letter' => $this->resource->getCoverLetter(),
            'price_offer' => $this->resource->getPriceOffer(),
            'status' => $this->resource->getStatus()->value,
            'created_at' => $this->resource->getCreatedAt()?->toISOString(),
            'user' => $this->resource->user ? [
                'id' => $this->resource->user->getId(),
                'name' => $this->resource->user->getName(),
                'profile' => $profile ? [
                    'type' => $profile->getType()?->value,
                    'first_name' => $profile->getFirstName(),
                    'last_name' => $profile->getLastName(),
                    'company_name' => $profile->getCompanyName(),
                    'city' => $profile->getCity(),
                    'avatar' => $profile->getAvatar(),
                ] : null,
            ] : null,
        ];
    }
}
