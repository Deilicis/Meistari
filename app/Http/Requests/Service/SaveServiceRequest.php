<?php

declare(strict_types=1);

namespace App\Http\Requests\Service;

use App\DataTransferObjects\Service\SaveServiceRequestData;
use App\Enums\Service\ServicePriceTypeEnum;
use App\Helpers\ValidationRuleHelper;
use App\Models\Category;
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
            Service::CATEGORY_ID => [
                ValidationRuleHelper::REQUIRED,
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
            Service::PRICE_TYPE => [
                ValidationRuleHelper::REQUIRED,
                Rule::enum(ServicePriceTypeEnum::class)
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
        $dto->categoryId = (int) $this->validated(Service::CATEGORY_ID);
        $dto->title = $this->validated(Service::TITLE);
        $dto->slug = $this->validated(Service::SLUG);
        $dto->description = $this->validated(Service::DESCRIPTION);
        
        $price = $this->validated(Service::PRICE);
        $dto->price = $price !== null ? (float) $price : null;
        
        $dto->priceType = ServicePriceTypeEnum::from($this->validated(Service::PRICE_TYPE));
        $dto->location = $this->validated(Service::LOCATION);
        $dto->isActive = (bool) $this->validated(Service::IS_ACTIVE);

        return $dto;
    }
}