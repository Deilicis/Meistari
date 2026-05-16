<?php

declare(strict_types=1);

namespace App\Http\Requests\Service;

use App\DataTransferObjects\Service\SaveServiceRequestData;
use App\Enums\CategorySuggestionStatusEnum;
use App\Helpers\ValidationRuleHelper;
use App\Models\Category;
use App\Models\CategorySuggestion;
use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled(Service::SLUG) && $this->filled(Service::TITLE)) {
            $this->merge([
                Service::SLUG => Str::slug($this->input(Service::TITLE)),
            ]);
        }
        
        if (! $this->has(Service::IS_ACTIVE)) {
            $this->merge([
                Service::IS_ACTIVE => true,
            ]);
        }
    }

    public function rules(): array
    {
        $serviceId = $this->route('service')?->getId();

        return [
            'pending_category_suggestion_id' => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::INTEGER,
                Rule::exists(CategorySuggestion::TABLE, CategorySuggestion::ID)
                    ->where(fn ($q) => $q->where(CategorySuggestion::STATUS, CategorySuggestionStatusEnum::PENDING->value)),
            ],
            Service::CATEGORY_ID => [
                'required_without:pending_category_suggestion_id',
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::INTEGER,
                ValidationRuleHelper::EXISTS . ':' . Category::TABLE . ',' . Category::ID
            ],
            Service::TITLE => [
                ValidationRuleHelper::REQUIRED,
                ValidationRuleHelper::STRING,
                ValidationRuleHelper::MAX_255
            ],
            Service::SLUG => [
                ValidationRuleHelper::REQUIRED,
                ValidationRuleHelper::STRING,
                ValidationRuleHelper::MAX_255,
                ValidationRuleHelper::UNIQUE . ':' . Service::TABLE . ',' . Service::SLUG . ',' . $serviceId
            ],
            Service::DESCRIPTION => [
                ValidationRuleHelper::REQUIRED,
                ValidationRuleHelper::STRING
            ],
            Service::PRICE => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::NUMERIC,
                'min:0'
            ],
            Service::LOCATION => [
                ValidationRuleHelper::REQUIRED,
                'array',
                'min:1',
                'max:10'
            ],
            Service::LOCATION . '.*' => [
                ValidationRuleHelper::STRING,
                ValidationRuleHelper::MAX_255
            ],
            Service::IS_ACTIVE => [
                ValidationRuleHelper::BOOLEAN
            ],
        ];
    }

    public function toDTO(): SaveServiceRequestData
    {
        $dto = new SaveServiceRequestData();
        
        $dto->userId = $this->user()->id;
        $dto->categoryId = $this->validated(Service::CATEGORY_ID) !== null ? (int) $this->validated(Service::CATEGORY_ID) : 0;
        $dto->pendingCategorySuggestionId = $this->validated('pending_category_suggestion_id') !== null
            ? (int) $this->validated('pending_category_suggestion_id')
            : null;
        $dto->title = $this->validated(Service::TITLE);
        $dto->slug = $this->validated(Service::SLUG);
        $dto->description = $this->validated(Service::DESCRIPTION);
        
        $price = $this->validated(Service::PRICE);
        $dto->price = $price !== null ? (float) $price : null;

        $dto->location = $this->validated(Service::LOCATION);
        $dto->isActive = (bool) $this->validated(Service::IS_ACTIVE);

        return $dto;
    }
}