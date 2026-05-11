<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\DTOs\CategorySuggestions\MergeSuggestionDTO;
use Illuminate\Foundation\Http\FormRequest;

class MergeSuggestionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'target_category_id' => ['required', 'integer', 'exists:categories,id'],
        ];
    }

    public function toDTO(int $suggestionId): MergeSuggestionDTO
    {
        return new MergeSuggestionDTO(
            suggestionId:     $suggestionId,
            targetCategoryId: $this->integer('target_category_id'),
            reviewedByUserId: $this->user()->getId(),
        );
    }
}
