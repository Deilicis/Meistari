<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\DataTransferObjects\Auth\UpdatePasswordRequestData;
use App\Helpers\ValidationRuleHelper as Rules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            UpdatePasswordRequestData::CURRENT_PASSWORD => [Rules::REQUIRED, 'current_password'],
            UpdatePasswordRequestData::PASSWORD => [Rules::REQUIRED, Password::defaults(), Rules::CONFIRMED],
        ];
    }

    public function toDTO(): UpdatePasswordRequestData
    {
        return UpdatePasswordRequestData::from([
            UpdatePasswordRequestData::CURRENT_PASSWORD => $this->input(UpdatePasswordRequestData::CURRENT_PASSWORD),
            UpdatePasswordRequestData::PASSWORD => $this->input(UpdatePasswordRequestData::PASSWORD),
        ]);
    }
}