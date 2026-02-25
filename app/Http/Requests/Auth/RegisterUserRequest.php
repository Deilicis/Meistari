<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\DataTransferObjects\Auth\RegisterUserRequestData;
use App\Enums\Profile\ProfileTypeEnum;
use App\Enums\Role\RoleNameEnum;
use App\Helpers\ValidationRuleHelper as Rules;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\RedirectResponse;

class RegisterUserRequest extends FormRequest
{
    public const NAME = 'name';
    public const EMAIL = 'email';
    public const PASSWORD = 'password';
    public const ROLE = 'role';
    public const PROFILE_TYPE = 'profile_type';
    public const CITY = 'city';
    public const FIRST_NAME = 'first_name';
    public const LAST_NAME = 'last_name';
    public const COMPANY_NAME = 'company_name';
    public const REG_NUMBER = 'reg_number';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            self::NAME => [Rules::REQUIRED, Rules::STRING, Rules::MAX_255],
            self::EMAIL => [
                Rules::REQUIRED, Rules::STRING, Rules::LOWERCASE, Rules::EMAIL, Rules::MAX_255, 
                Rule::unique(User::TABLE, User::EMAIL)
            ],
            self::PASSWORD => [Rules::REQUIRED, Rules::CONFIRMED, Password::defaults()],
            
            self::ROLE => [Rules::REQUIRED, new Enum(RoleNameEnum::class)],
            self::PROFILE_TYPE => [Rules::REQUIRED, new Enum(ProfileTypeEnum::class)],
            self::CITY => [Rules::REQUIRED, Rules::STRING, Rules::MAX_255],

            self::FIRST_NAME => [
                Rule::requiredIf($this->input(self::PROFILE_TYPE) === ProfileTypeEnum::INDIVIDUAL->value),
                Rules::NULLABLE, Rules::STRING, Rules::MAX_255
            ],
            self::LAST_NAME => [
                Rule::requiredIf($this->input(self::PROFILE_TYPE) === ProfileTypeEnum::INDIVIDUAL->value),
                Rules::NULLABLE, Rules::STRING, Rules::MAX_255
            ],

            self::COMPANY_NAME => [
                Rule::requiredIf($this->input(self::PROFILE_TYPE) === ProfileTypeEnum::COMPANY->value),
                Rules::NULLABLE, Rules::STRING, Rules::MAX_255
            ],
            self::REG_NUMBER => [
                Rule::requiredIf($this->input(self::PROFILE_TYPE) === ProfileTypeEnum::COMPANY->value),
                Rules::NULLABLE, Rules::STRING, Rules::MAX_255
            ],
        ];
    }

    public function toDTO(): RegisterUserRequestData
    {
        return RegisterUserRequestData::from([
            RegisterUserRequestData::NAME => $this->input(self::NAME),
            RegisterUserRequestData::EMAIL => $this->input(self::EMAIL),
            RegisterUserRequestData::PASSWORD => $this->input(self::PASSWORD),
            RegisterUserRequestData::ROLE => RoleNameEnum::from($this->input(self::ROLE)),
            RegisterUserRequestData::PROFILE_TYPE => ProfileTypeEnum::from($this->input(self::PROFILE_TYPE)),
            RegisterUserRequestData::CITY => $this->input(self::CITY),
            RegisterUserRequestData::FIRST_NAME => $this->input(self::FIRST_NAME),
            RegisterUserRequestData::LAST_NAME => $this->input(self::LAST_NAME),
            RegisterUserRequestData::COMPANY_NAME => $this->input(self::COMPANY_NAME),
            RegisterUserRequestData::REG_NUMBER => $this->input(self::REG_NUMBER),
        ]);
    }

    public function responseRedirect(): RedirectResponse
    {
        return redirect(route('dashboard', absolute: false));
    }
}