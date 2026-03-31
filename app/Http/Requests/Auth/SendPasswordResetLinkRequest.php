<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\DataTransferObjects\Auth\SendPasswordResetLinkRequestData;
use App\Helpers\ValidationRuleHelper as Rules;
use Illuminate\Foundation\Http\FormRequest;

class SendPasswordResetLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            SendPasswordResetLinkRequestData::EMAIL => [
                Rules::REQUIRED, 
                Rules::EMAIL
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'E-pasts ir obligāts.',
            'email.email'    => 'Lūdzu ievadiet derīgu e-pasta adresi.',
        ];
    }

    public function toDTO(): SendPasswordResetLinkRequestData
    {
        return SendPasswordResetLinkRequestData::from([
            SendPasswordResetLinkRequestData::EMAIL => $this->input(SendPasswordResetLinkRequestData::EMAIL),
        ]);
    }
}