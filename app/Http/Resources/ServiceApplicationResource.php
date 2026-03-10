<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ServiceApplication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ServiceApplication
 */
class ServiceApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            ServiceApplication::ID           => $this->getId(),
            ServiceApplication::SERVICE_ID   => $this->getServiceId(),
            ServiceApplication::USER_ID      => $this->getUserId(),
            ServiceApplication::MESSAGE      => $this->getMessage(),
            ServiceApplication::BUDGET_OFFER => $this->getBudgetOffer(),
            ServiceApplication::STATUS       => $this->getStatus()->value,
            ServiceApplication::CREATED_AT   => $this->getCreatedAt(),
        ];
    }
}
