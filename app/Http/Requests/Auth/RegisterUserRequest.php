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
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'           => 'Vārds ir obligāts.',
            'name.string'             => 'Vārdam jābūt tekstam.',
            'name.max'                => 'Vārds nedrīkst pārsniegt 255 rakstzīmes.',
            'email.required'          => 'E-pasts ir obligāts.',
            'email.string'            => 'E-pastam jābūt tekstam.',
            'email.lowercase'         => 'E-pastam jābūt ar mazajiem burtiem.',
            'email.email'             => 'Lūdzu ievadiet derīgu e-pasta adresi.',
            'email.max'               => 'E-pasts nedrīkst pārsniegt 255 rakstzīmes.',
            'email.unique'            => 'Šis e-pasts jau ir reģistrēts.',
            'password.required'       => 'Parole ir obligāta.',
            'password.confirmed'      => 'Paroles nesakrīt.',
            'password.min'            => 'Parolei jābūt vismaz 8 rakstzīmes garai.',
            'role.required'           => 'Loma ir obligāta.',
            'profile_type.required'   => 'Profila veids ir obligāts.',
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
        ]);
    }

    public function responseRedirect(): RedirectResponse
    {
        return redirect(route('dashboard', absolute: false));
    }
}