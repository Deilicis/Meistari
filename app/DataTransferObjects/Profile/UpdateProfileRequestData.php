<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Profile;

use App\Enums\Profile\ProfileTypeEnum;
use App\Models\Profile;

class UpdateProfileRequestData
{
    public int $userId;
    public string $name;
    public string $email;
    
    public ProfileTypeEnum $type;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $companyName = null;
    public ?string $regNumber = null;
    public ?string $vatNumber = null;
    public ?string $city = null;
    public ?string $phoneNumber = null;
    public ?string $description = null;
    public mixed $avatar = null; 
    public array $experiences = [];
    public array $portfolioImages = [];
    public array $imagesToDelete = [];
}