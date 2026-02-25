<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use App\DataTransferObjects\Profile\UpdateProfileRequestData;
use App\Enums\Profile\ProfileTypeEnum;
use App\Helpers\ValidationRuleHelper as Rules;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var \App\Models\User $user */
        $user = $this->user();
        $isIndividual = $user->profile->getType() === ProfileTypeEnum::INDIVIDUAL;
        $isCompany = $user->profile->getType() === ProfileTypeEnum::COMPANY;

        return [
            UpdateProfileRequestData::NAME => [Rules::REQUIRED, Rules::STRING, Rules::MAX_255],
            UpdateProfileRequestData::EMAIL => [
                Rules::REQUIRED, Rules::STRING, Rules::LOWERCASE, Rules::EMAIL, Rules::MAX_255,
                Rule::unique(User::TABLE, User::EMAIL)->ignore($user->getId()),
            ],
            UpdateProfileRequestData::CITY => [Rules::REQUIRED, Rules::STRING, Rules::MAX_255],
            UpdateProfileRequestData::PHONE => [Rules::NULLABLE, Rules::STRING, Rules::MAX_255],
            UpdateProfileRequestData::BIO => [Rules::NULLABLE, Rules::STRING],

            UpdateProfileRequestData::FIRST_NAME => [
                Rule::requiredIf($isIndividual), Rules::NULLABLE, Rules::STRING, Rules::MAX_255
            ],
            UpdateProfileRequestData::LAST_NAME => [
                Rule::requiredIf($isIndividual), Rules::NULLABLE, Rules::STRING, Rules::MAX_255
            ],

            UpdateProfileRequestData::COMPANY_NAME => [
                Rule::requiredIf($isCompany), Rules::NULLABLE, Rules::STRING, Rules::MAX_255
            ],
            UpdateProfileRequestData::REG_NUMBER => [
                Rule::requiredIf($isCompany), Rules::NULLABLE, Rules::STRING, Rules::MAX_255
            ],
            UpdateProfileRequestData::VAT_NUMBER => [
                Rules::NULLABLE, Rules::STRING, Rules::MAX_255
            ],
        ];
    }

    public function toDTO(): UpdateProfileRequestData
    {
        return UpdateProfileRequestData::from([
            UpdateProfileRequestData::NAME => $this->input(UpdateProfileRequestData::NAME),
            UpdateProfileRequestData::EMAIL => $this->input(UpdateProfileRequestData::EMAIL),
            UpdateProfileRequestData::CITY => $this->input(UpdateProfileRequestData::CITY),
            UpdateProfileRequestData::FIRST_NAME => $this->input(UpdateProfileRequestData::FIRST_NAME),
            UpdateProfileRequestData::LAST_NAME => $this->input(UpdateProfileRequestData::LAST_NAME),
            UpdateProfileRequestData::COMPANY_NAME => $this->input(UpdateProfileRequestData::COMPANY_NAME),
            UpdateProfileRequestData::REG_NUMBER => $this->input(UpdateProfileRequestData::REG_NUMBER),
            UpdateProfileRequestData::VAT_NUMBER => $this->input(UpdateProfileRequestData::VAT_NUMBER),
            UpdateProfileRequestData::PHONE => $this->input(UpdateProfileRequestData::PHONE),
            UpdateProfileRequestData::BIO => $this->input(UpdateProfileRequestData::BIO),
        ]);
    }
}