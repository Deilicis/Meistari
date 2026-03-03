<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use App\DataTransferObjects\Profile\UpdateProfileRequestData;
use App\Enums\Profile\ProfileTypeEnum;
use App\Helpers\ValidationRuleHelper;
use App\Models\Profile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public const IMAGES_TO_DELETE = 'images_to_delete';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            Profile::TYPE => [
                ValidationRuleHelper::REQUIRED,
                Rule::enum(ProfileTypeEnum::class)
            ],
            Profile::PHONE => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::STRING,
                ValidationRuleHelper::MAX_255
            ],
            Profile::BIO => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::STRING
            ],
            
            Profile::AVATAR => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::IMAGE,
                ValidationRuleHelper::MIMES . ':jpeg,png,jpg,webp',
                ValidationRuleHelper::MAX . ':2048'
            ],
            Profile::EXPERIENCE_YEARS => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::INTEGER,
                ValidationRuleHelper::MIN . ':0',
                ValidationRuleHelper::MAX . ':70'
            ],
            Profile::PORTFOLIO_IMAGES => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::ARRAY_RULE,
                ValidationRuleHelper::MAX . ':10'
            ],
            Profile::PORTFOLIO_IMAGES . '.*' => [
                ValidationRuleHelper::IMAGE,
                ValidationRuleHelper::MIMES . ':jpeg,png,jpg,webp',
                ValidationRuleHelper::MAX . ':5120'
            ],
            self::IMAGES_TO_DELETE => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::ARRAY_RULE
            ],
            self::IMAGES_TO_DELETE . '.*' => [
                ValidationRuleHelper::STRING
            ]
        ];
    }

    public function toDTO(): UpdateProfileRequestData
    {
        $dto = new UpdateProfileRequestData();
        
        $dto->userId = $this->user()->id;
        $dto->type = ProfileTypeEnum::from($this->validated(Profile::TYPE));
        $dto->phoneNumber = $this->validated(Profile::PHONE);
        $dto->description = $this->validated(Profile::BIO);
        
        $dto->avatar = $this->file(Profile::AVATAR);
        
        $experience = $this->validated(Profile::EXPERIENCE_YEARS);
        $dto->experienceYears = $experience !== null ? (int) $experience : null;
        
        $dto->portfolioImages = $this->file(Profile::PORTFOLIO_IMAGES) ?? [];
        $dto->imagesToDelete = $this->validated(self::IMAGES_TO_DELETE) ?? [];

        return $dto;
    }
}