<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\DataTransferObjects\Auth\ConfirmPasswordRequestData;
use App\Helpers\ValidationRuleHelper as Rules;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            ConfirmPasswordRequestData::PASSWORD => [Rules::REQUIRED, Rules::STRING],
        ];
    }

    public function toDTO(): ConfirmPasswordRequestData
    {
        return ConfirmPasswordRequestData::from([
            ConfirmPasswordRequestData::PASSWORD => $this->input(ConfirmPasswordRequestData::PASSWORD),
        ]);
    }
}