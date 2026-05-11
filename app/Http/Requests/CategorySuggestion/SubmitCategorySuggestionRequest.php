<?php

declare(strict_types=1);

namespace App\Http\Requests\CategorySuggestion;

use App\DTOs\CategorySuggestions\SubmitCategorySuggestionDTO;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class SubmitCategorySuggestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'min:2', 'max:100'],
            'parent_category_id' => [
                'nullable',
                'integer',
                'exists:' . Category::TABLE . ',' . Category::ID,
            ],
            'note'               => ['nullable', 'string', 'max:300'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $parentId = $this->input('parent_category_id');
            if ($parentId !== null) {
                $parent = Category::find((int) $parentId);
                if ($parent && $parent->getParentId() !== null) {
                    $v->errors()->add('parent_category_id', 'Norādītā vecākkategorija nav augstākā līmeņa kategorija.');
                }
            }
        });
    }

    public function toDTO(): SubmitCategorySuggestionDTO
    {
        return new SubmitCategorySuggestionDTO(
            name: $this->validated('name'),
            parentCategoryId: $this->validated('parent_category_id') !== null ? (int) $this->validated('parent_category_id') : null,
            note: $this->validated('note'),
            suggestedByUserId: $this->user()->getId(),
        );
    }
}
