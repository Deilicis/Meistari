<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\DTOs\CategorySuggestions\ApproveSuggestionDTO;
use Illuminate\Foundation\Http\FormRequest;

class ApproveSuggestionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'icon' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function toDTO(int $suggestionId): ApproveSuggestionDTO
    {
        return new ApproveSuggestionDTO(
            suggestionId:     $suggestionId,
            reviewedByUserId: $this->user()->getId(),
            icon:             $this->input('icon') ?: null,
        );
    }
}
