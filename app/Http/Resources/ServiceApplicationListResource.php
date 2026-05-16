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
class ServiceApplicationListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profile = $this->service->user->profile;

        return [
            ServiceApplication::ID           => $this->getId(),
            ServiceApplication::MESSAGE      => $this->getMessage(),
            ServiceApplication::BUDGET_OFFER => $this->getBudgetOffer(),
            ServiceApplication::STATUS       => $this->getStatus()->value,
            ServiceApplication::CREATED_AT   => $this->getCreatedAt(),
            'service' => [
                Service::ID          => $this->service->getId(),
                Service::TITLE       => $this->service->getTitle(),
                Service::SLUG        => $this->service->getSlug(),
                Service::DESCRIPTION => $this->service->getDescription(),
                Service::PRICE       => $this->service->getPrice(),
                Service::LOCATION    => $this->service->getLocation(),
                Service::IS_ACTIVE   => $this->service->getIsActive(),
                'category' => $this->service->category
                    ? ['id' => $this->service->category->getId(), 'name' => $this->service->category->getName()]
                    : null,
                'user' => [
                    'id'      => $this->service->user->getId(),
                    'name'    => $this->service->user->getName(),
                    'profile' => $profile ? [
                        Profile::TYPE             => $profile->getType()->value,
                        Profile::FIRST_NAME       => $profile->getFirstName(),
                        Profile::LAST_NAME        => $profile->getLastName(),
                        Profile::COMPANY_NAME     => $profile->getCompanyName(),
                        Profile::CITY             => $profile->getCity(),
                        Profile::BIO              => $profile->getBio(),
                        Profile::AVATAR           => $profile->getAvatar(),
                        Profile::IS_VERIFIED      => $profile->getIsVerified(),
                        Profile::EXPERIENCES      => $profile->getExperiences(),
                        Profile::PORTFOLIO_IMAGES => $profile->getPortfolioImages(),
                    ] : null,
                ],
            ],
        ];
    }
}
