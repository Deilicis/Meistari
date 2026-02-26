<?php

declare(strict_types=1);

namespace App\Http\Requests\Category;

use App\DataTransferObjects\Category\SaveCategoryRequestData;
use App\Helpers\ValidationRuleHelper;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class SaveCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled(Category::SLUG) && $this->filled(Category::NAME)) {
            $this->merge([
                Category::SLUG => Str::slug($this->input(Category::NAME)),
            ]);
        }
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->getId();

        return [
            Category::NAME => [
                ValidationRuleHelper::REQUIRED, 
                ValidationRuleHelper::STRING, 
                ValidationRuleHelper::MAX_255
            ],
            Category::SLUG => [
                ValidationRuleHelper::REQUIRED, 
                ValidationRuleHelper::STRING, 
                ValidationRuleHelper::MAX_255, 
                ValidationRuleHelper::UNIQUE . ':' . Category::TABLE . ',' . Category::SLUG . ',' . $categoryId
            ],
            Category::ICON => [
                ValidationRuleHelper::NULLABLE, 
                ValidationRuleHelper::STRING, 
                ValidationRuleHelper::MAX_255
            ],
            Category::PARENT_ID => [
                ValidationRuleHelper::NULLABLE, 
                ValidationRuleHelper::INTEGER, 
                ValidationRuleHelper::EXISTS . ':' . Category::TABLE . ',' . Category::ID
            ],
        ];
    }

    public function toDTO(): SaveCategoryRequestData
    {
        $dto = new SaveCategoryRequestData();
        
        $dto->name = $this->validated(Category::NAME);
        $dto->slug = $this->validated(Category::SLUG);
        $dto->icon = $this->validated(Category::ICON);
        
        $parentId = $this->validated(Category::PARENT_ID);
        $dto->parentId = $parentId ? (int) $parentId : null;

        return $dto;
    }
}