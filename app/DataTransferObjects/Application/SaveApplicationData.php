<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Application;

use App\Enums\Job\ApplicationStatusEnum;

class SaveApplicationData
{
    public function __construct(
        public readonly int     $jobRequestId,
        public readonly int     $userId,
        public readonly ?string $coverLetter,
        public readonly ?float  $priceOffer,
    ) {}

    public function toArray(): array
    {
        return [
            'job_request_id' => $this->jobRequestId,
            'user_id' => $this->userId,
            'cover_letter' => $this->coverLetter,
            'price_offer' => $this->priceOffer,
            'status' => ApplicationStatusEnum::PENDING->value,
        ];
    }
}
