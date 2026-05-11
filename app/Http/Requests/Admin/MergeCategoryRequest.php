<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\DTOs\Categories\MergeCategoriesDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class MergeCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'source_id' => ['required', 'integer', 'exists:categories,id'],
            'target_id' => ['required', 'integer', 'exists:categories,id'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->integer('source_id') === $this->integer('target_id')) {
                    $validator->errors()->add('target_id', 'Avota un mērķa kategorija nevar būt vienāda.');
                }
            },
        ];
    }

    public function toDTO(): MergeCategoriesDTO
    {
        return new MergeCategoriesDTO(
            sourceId: $this->integer('source_id'),
            targetId: $this->integer('target_id'),
        );
    }
}
