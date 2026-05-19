<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Profile;
use App\Models\Service;
use App\Models\ServiceApplication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ServiceApplication
 */
class MasterServiceApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profile = $this->user->profile;

        return [
            ServiceApplication::ID => $this->getId(),
            ServiceApplication::MESSAGE => $this->getMessage(),
            ServiceApplication::BUDGET_OFFER => $this->getBudgetOffer(),
            ServiceApplication::STATUS => $this->getStatus()->value,
            ServiceApplication::CREATED_AT => $this->getCreatedAt()?->toISOString(),
            'service' => [
                Service::ID => $this->service->getId(),
                Service::TITLE => $this->service->getTitle(),
            ],
            'applicant' => [
                'id' => $this->user->getId(),
                'name' => $this->user->getName(),
                'avatar_url' => $profile?->getAvatar()
                    ? '/storage/' . $profile->getAvatar()
                    : null,
            ],
        ];
    }
}
