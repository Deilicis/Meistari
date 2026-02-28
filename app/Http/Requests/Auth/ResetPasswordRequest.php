<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\DataTransferObjects\Auth\ResetPasswordRequestData;
use App\Helpers\ValidationRuleHelper as Rules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            ResetPasswordRequestData::TOKEN => [
                Rules::REQUIRED
            ],
            ResetPasswordRequestData::EMAIL => [
                Rules::REQUIRED, 
                Rules::EMAIL
            ],
            ResetPasswordRequestData::PASSWORD => [
                Rules::REQUIRED, 
                Rules::CONFIRMED, 
                Password::defaults()
            ],
        ];
    }

    public function toDTO(): ResetPasswordRequestData
    {
        return ResetPasswordRequestData::from([
            ResetPasswordRequestData::TOKEN => $this->input(ResetPasswordRequestData::TOKEN),
            ResetPasswordRequestData::EMAIL => $this->input(ResetPasswordRequestData::EMAIL),
            ResetPasswordRequestData::PASSWORD => $this->input(ResetPasswordRequestData::PASSWORD),
        ]);
    }
}