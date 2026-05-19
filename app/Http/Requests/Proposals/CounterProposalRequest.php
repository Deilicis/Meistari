<?php

declare(strict_types=1);

namespace App\Http\Requests\Proposals;

use Illuminate\Foundation\Http\FormRequest;

class CounterProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1', 'max:999999.99'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
