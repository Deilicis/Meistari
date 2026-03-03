<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Profile;

use App\Enums\Profile\ProfileTypeEnum;
use App\Models\Profile;

class UpdateProfileRequestData
{
    public int $userId;
    public ProfileTypeEnum $type;
    public ?string $phoneNumber = null;
    public ?string $description = null;
    public mixed $avatar = null; 
    public ?int $experienceYears = null;
    public array $portfolioImages = [];
    public array $imagesToDelete = [];

    public function toArray(): array
    {
        return [
            Profile::USER_ID => $this->userId,
            Profile::TYPE => $this->type->value,
            Profile::PHONE_NUMBER => $this->phoneNumber,
            Profile::DESCRIPTION => $this->description,
            Profile::EXPERIENCE_YEARS => $this->experienceYears,
        ];
    }
}