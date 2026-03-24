<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Application
 */
class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            Application::ID             => $this->getId(),
            Application::JOB_REQUEST_ID => $this->getJobRequestId(),
            Application::STATUS         => $this->getStatus()->value,
            Application::CREATED_AT     => $this->getCreatedAt(),
        ];
    }
}
