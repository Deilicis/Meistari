<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Application
 */
class ApplicationListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $jr = $this->jobRequest;
        $seeker = $jr?->user;
        $profile = $seeker?->profile;

        return [
            Application::ID => $this->getId(),
            Application::COVER_LETTER => $this->getCoverLetter(),
            Application::PRICE_OFFER => $this->getPriceOffer(),
            Application::STATUS => $this->getStatus()->value,
            Application::CREATED_AT => $this->getCreatedAt(),
            'job_request' => $jr ? [
                'id' => $jr->getId(),
                'title' => $jr->getTitle(),
                'description' => $jr->getDescription(),
                'budget' => $jr->getBudget(),
                'deadline' => $jr->getDeadline(),
                'location' => $jr->getLocation(),
                'status' => $jr->getStatus()->value,
                'category' => $jr->category ? ['id' => $jr->category->getId(), 'name' => $jr->category->getName()] : null,
                'user' => $seeker ? [
                    'id' => $seeker->id,
                    'name' => $seeker->name,
                    'profile' => $profile ? [
                        'type' => $profile->type,
                        'first_name' => $profile->first_name,
                        'last_name' => $profile->last_name,
                        'company_name' => $profile->company_name,
                        'city' => $profile->city,
                        'avatar' => $profile->avatar,
                    ] : null,
                ] : null,
            ] : null,
        ];
    }
}
