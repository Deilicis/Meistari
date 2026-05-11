<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\DTOs\CategorySuggestions\RejectSuggestionDTO;
use Illuminate\Foundation\Http\FormRequest;

class RejectSuggestionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'review_note' => ['required', 'string', 'max:500'],
        ];
    }

    public function toDTO(int $suggestionId): RejectSuggestionDTO
    {
        return new RejectSuggestionDTO(
            suggestionId:     $suggestionId,
            reviewedByUserId: $this->user()->getId(),
            reviewNote:       $this->string('review_note'),
        );
    }
}
