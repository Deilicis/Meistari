<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\DataTransferObjects\Auth\LoginUserRequestData;
use App\Helpers\ValidationRuleHelper as Rules;
use Illuminate\Foundation\Http\FormRequest;

class LoginUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            LoginUserRequestData::EMAIL => [
                Rules::REQUIRED,
                Rules::STRING,
                Rules::EMAIL
            ],
            LoginUserRequestData::PASSWORD => [
                Rules::REQUIRED,
                Rules::STRING
            ],
            LoginUserRequestData::REMEMBER => [
                Rules::BOOLEAN
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'E-pasts ir obligāts.',
            'email.string' => 'E-pastam jābūt tekstam.',
            'email.email' => 'Lūdzu ievadiet derīgu e-pasta adresi.',
            'password.required' => 'Parole ir obligāta.',
            'password.string' => 'Parolei jābūt tekstam.',
        ];
    }

    public function toDTO(): LoginUserRequestData
    {
        return LoginUserRequestData::from([
            LoginUserRequestData::EMAIL => $this->input(LoginUserRequestData::EMAIL),
            LoginUserRequestData::PASSWORD => $this->input(LoginUserRequestData::PASSWORD),
            LoginUserRequestData::REMEMBER => $this->boolean(LoginUserRequestData::REMEMBER),
        ]);
    }
}