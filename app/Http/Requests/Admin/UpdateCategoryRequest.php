<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\DTOs\Categories\UpdateCategoryDTO;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:100'],
            'parent_id'       => ['nullable', 'integer', 'exists:categories,id'],
            'icon'            => ['nullable', 'string', 'max:100'],
            'regenerate_slug' => ['nullable', 'boolean'],
        ];
    }

    public function toDTO(): UpdateCategoryDTO
    {
        return new UpdateCategoryDTO(
            name:           $this->string('name'),
            icon:           $this->input('icon') ?: null,
            parentId:       $this->integer('parent_id') ?: null,
            regenerateSlug: (bool) $this->input('regenerate_slug', false),
        );
    }
}
