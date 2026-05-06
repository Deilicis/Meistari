<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Application;
use App\Models\JobRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Application
 */
class SeekerJobApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profile = $this->user?->profile;

        return [
            Application::ID           => $this->getId(),
            Application::COVER_LETTER => $this->getCoverLetter(),
            Application::PRICE_OFFER  => $this->getPriceOffer(),
            Application::STATUS       => $this->getStatus()->value,
            Application::CREATED_AT   => $this->getCreatedAt()?->toISOString(),
            'job_request' => $this->jobRequest ? [
                JobRequest::ID    => $this->jobRequest->getId(),
                JobRequest::TITLE => $this->jobRequest->getTitle(),
            ] : null,
            'applicant' => $this->user ? [
                'id'         => $this->user->getId(),
                'name'       => $this->user->getName(),
                'avatar_url' => $profile?->getAvatar()
                    ? '/storage/' . $profile->getAvatar()
                    : null,
            ] : null,
        ];
    }
}
