<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use App\DataTransferObjects\Profile\UpdateProfileRequestData;
use App\Enums\Profile\ProfileTypeEnum;
use App\Helpers\ValidationRuleHelper;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public const NAME = 'name';
    public const EMAIL = 'email';
    public const TYPE = 'type';
    public const FIRST_NAME = 'first_name';
    public const LAST_NAME = 'last_name';
    public const COMPANY_NAME = 'company_name';
    public const REG_NUMBER = 'reg_number';
    public const VAT_NUMBER = 'vat_number';
    public const CITY = 'city';
    public const PHONE_NUMBER = 'phone_number';
    public const DESCRIPTION = 'description';
    public const AVATAR = 'avatar';
    public const EXPERIENCES = 'experiences';
    public const PORTFOLIO_IMAGES = 'portfolio_images';
    public const IMAGES_TO_DELETE = 'images_to_delete';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $avatarRules = [
            ValidationRuleHelper::NULLABLE
            ];
        if ($this->input(self::AVATAR) === 'delete') {
            $avatarRules[] = ValidationRuleHelper::STRING;
        } else {
            $avatarRules[] = ValidationRuleHelper::IMAGE;
            $avatarRules[] = ValidationRuleHelper::MIMES . ':jpeg,png,jpg,webp';
            $avatarRules[] = ValidationRuleHelper::MAX . ':2048';
        }

        return [
            self::NAME => [
                ValidationRuleHelper::REQUIRED,
                ValidationRuleHelper::STRING,
                ValidationRuleHelper::MAX_255,
            ],
            self::EMAIL => [
                ValidationRuleHelper::REQUIRED,
                ValidationRuleHelper::STRING,
                ValidationRuleHelper::EMAIL,
                ValidationRuleHelper::MAX_255,
                Rule::unique(User::TABLE, User::EMAIL)->ignore($this->user()->id),
            ],
            self::TYPE => [
                ValidationRuleHelper::REQUIRED,
                Rule::enum(ProfileTypeEnum::class),
            ],
            self::FIRST_NAME => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::STRING,
                ValidationRuleHelper::MAX_255,
            ],
            self::LAST_NAME => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::STRING,
                ValidationRuleHelper::MAX_255,
            ],
            self::COMPANY_NAME => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::STRING,
                ValidationRuleHelper::MAX_255,
            ],
            self::REG_NUMBER => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::STRING,
                ValidationRuleHelper::MAX_255,
            ],
            self::VAT_NUMBER => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::STRING,
                ValidationRuleHelper::MAX_255,
            ],
            self::CITY => [
                ValidationRuleHelper::REQUIRED,
                ValidationRuleHelper::STRING,
                ValidationRuleHelper::MAX_255,
            ],
            self::PHONE_NUMBER => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::STRING,
                'regex:/^[0-9\s\+\-\(\)]+$/',
                ValidationRuleHelper::MAX_255,
            ],
            self::DESCRIPTION => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::STRING,
            ],
            self::AVATAR => $avatarRules,
            self::EXPERIENCES => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::ARRAY_RULE,
                ValidationRuleHelper::MAX . ':10',
            ],
            self::EXPERIENCES . '.*.title' => [
                ValidationRuleHelper::REQUIRED,
                ValidationRuleHelper::STRING,
                ValidationRuleHelper::MAX_255,
            ],
            self::EXPERIENCES . '.*.years' => [
                ValidationRuleHelper::REQUIRED,
                ValidationRuleHelper::INTEGER,
                ValidationRuleHelper::MIN . ':0',
                ValidationRuleHelper::MAX . ':70',
            ],
            self::EXPERIENCES . '.*.description' => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::STRING,
            ],
            self::PORTFOLIO_IMAGES => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::ARRAY_RULE,
                ValidationRuleHelper::MAX . ':10',
            ],
            self::PORTFOLIO_IMAGES . '.*' => [
                ValidationRuleHelper::IMAGE,
                ValidationRuleHelper::MIMES . ':jpeg,png,jpg,webp',
                ValidationRuleHelper::MAX . ':5120',
            ],
            self::IMAGES_TO_DELETE => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::ARRAY_RULE,
            ],
            self::IMAGES_TO_DELETE . '.*' => [
                ValidationRuleHelper::STRING,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.regex' => __('validation.custom.phone_number.regex'),
        ];
    }

    public function toDTO(): UpdateProfileRequestData
    {
        $dto = new UpdateProfileRequestData();
        
        $dto->userId = $this->user()->id;
        $dto->name = $this->validated(self::NAME);
        $dto->email = $this->validated(self::EMAIL);
        $dto->type = ProfileTypeEnum::from($this->validated(self::TYPE));
        $dto->firstName = $this->validated(self::FIRST_NAME);
        $dto->lastName = $this->validated(self::LAST_NAME);
        $dto->companyName = $this->validated(self::COMPANY_NAME);
        $dto->regNumber = $this->validated(self::REG_NUMBER);
        $dto->vatNumber = $this->validated(self::VAT_NUMBER);
        $dto->city = $this->validated(self::CITY);
        $dto->phoneNumber = $this->validated(self::PHONE_NUMBER);
        $dto->description = $this->validated(self::DESCRIPTION);
        $dto->avatar = $this->file(self::AVATAR) ?? $this->validated(self::AVATAR);
        $dto->experiences = $this->validated(self::EXPERIENCES) ?? [];
        $dto->portfolioImages = $this->file(self::PORTFOLIO_IMAGES) ?? [];
        $dto->imagesToDelete = $this->validated(self::IMAGES_TO_DELETE) ?? [];

        return $dto;
    }
}