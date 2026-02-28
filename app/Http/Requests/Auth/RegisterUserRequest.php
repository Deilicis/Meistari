<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\DataTransferObjects\Auth\RegisterUserRequestData;
use App\Enums\Profile\ProfileTypeEnum;
use App\Enums\Role\RoleNameEnum;
use App\Helpers\ValidationRuleHelper as Rules;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $profileType = $this->input(RegisterUserRequestData::PROFILE_TYPE);
        $isIndividual = $profileType === ProfileTypeEnum::INDIVIDUAL->value;
        $isCompany = $profileType === ProfileTypeEnum::COMPANY->value;

        return [
            RegisterUserRequestData::NAME => [
                Rules::REQUIRED,
                Rules::STRING,
                Rules::MAX_255,
            ],
            RegisterUserRequestData::EMAIL => [
                Rules::REQUIRED,
                Rules::STRING,
                Rules::LOWERCASE,
                Rules::EMAIL,
                Rules::MAX_255,
                Rule::unique(User::TABLE, User::EMAIL),
            ],
            RegisterUserRequestData::PASSWORD => [
                Rules::REQUIRED,
                Rules::CONFIRMED,
                Password::defaults(),
            ],
            RegisterUserRequestData::ROLE => [
                Rules::REQUIRED,
                new Enum(RoleNameEnum::class),
            ],
            RegisterUserRequestData::PROFILE_TYPE => [
                Rules::REQUIRED,
                new Enum(ProfileTypeEnum::class),
            ],
            RegisterUserRequestData::CITY => [
                Rules::REQUIRED,
                Rules::STRING,
                Rules::MAX_255,
            ],
            RegisterUserRequestData::FIRST_NAME => [
                Rule::requiredIf($isIndividual),
                Rules::NULLABLE,
                Rules::STRING,
                Rules::MAX_255,
            ],
            RegisterUserRequestData::LAST_NAME => [
                Rule::requiredIf($isIndividual),
                Rules::NULLABLE,
                Rules::STRING,
                Rules::MAX_255,
            ],
            RegisterUserRequestData::COMPANY_NAME => [
                Rule::requiredIf($isCompany),
                Rules::NULLABLE,
                Rules::STRING,
                Rules::MAX_255,
            ],
            RegisterUserRequestData::REG_NUMBER => [
                Rule::requiredIf($isCompany),
                Rules::NULLABLE,
                Rules::STRING,
                Rules::MAX_255,
            ],
        ];
    }

    public function toDTO(): RegisterUserRequestData
    {
        return RegisterUserRequestData::from([
            RegisterUserRequestData::NAME => $this->input(RegisterUserRequestData::NAME),
            RegisterUserRequestData::EMAIL => $this->input(RegisterUserRequestData::EMAIL),
            RegisterUserRequestData::PASSWORD => $this->input(RegisterUserRequestData::PASSWORD),
            RegisterUserRequestData::ROLE => RoleNameEnum::from($this->input(RegisterUserRequestData::ROLE)),
            RegisterUserRequestData::PROFILE_TYPE => ProfileTypeEnum::from($this->input(RegisterUserRequestData::PROFILE_TYPE)),
            RegisterUserRequestData::CITY => $this->input(RegisterUserRequestData::CITY),
            RegisterUserRequestData::FIRST_NAME => $this->input(RegisterUserRequestData::FIRST_NAME),
            RegisterUserRequestData::LAST_NAME => $this->input(RegisterUserRequestData::LAST_NAME),
            RegisterUserRequestData::COMPANY_NAME => $this->input(RegisterUserRequestData::COMPANY_NAME),
            RegisterUserRequestData::REG_NUMBER => $this->input(RegisterUserRequestData::REG_NUMBER),
        ]);
    }

    public function responseRedirect(): RedirectResponse
    {
        return redirect(route('dashboard', absolute: false));
    }
}