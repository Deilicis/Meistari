<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\DTOs\Categories\CreateCategoryDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'icon'      => ['nullable', 'string', 'max:100'],
        ];
    }

    public function toDTO(): CreateCategoryDTO
    {
        return new CreateCategoryDTO(
            name:     $this->string('name'),
            parentId: $this->integer('parent_id') ?: null,
            icon:     $this->input('icon') ?: null,
        );
    }
}
