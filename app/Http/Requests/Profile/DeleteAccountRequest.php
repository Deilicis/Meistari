<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use App\DataTransferObjects\Profile\DeleteAccountRequestData;
use App\Helpers\ValidationRuleHelper as Rules;
use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            DeleteAccountRequestData::PASSWORD => [Rules::REQUIRED, 'current_password'],
        ];
    }

    public function toDTO(): DeleteAccountRequestData
    {
        return DeleteAccountRequestData::from([
            DeleteAccountRequestData::PASSWORD => $this->input(DeleteAccountRequestData::PASSWORD),
        ]);
    }
}