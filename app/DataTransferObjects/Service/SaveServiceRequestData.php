<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Service;

use App\Models\Service;
use App\Enums\Service\ServicePriceTypeEnum;

class SaveServiceRequestData
{
    public int $userId;
    public int $categoryId;
    public string $title;
    public string $slug;
    public string $description;
    public ?float $price = null;
    public ServicePriceTypeEnum $priceType;
    public string $location;
    public bool $isActive;

    public function toArray(): array
    {
        return [
            Service::USER_ID => $this->userId,
            Service::CATEGORY_ID => $this->categoryId,
            Service::TITLE => $this->title,
            Service::SLUG => $this->slug,
            Service::DESCRIPTION => $this->description,
            Service::PRICE => $this->price,
            Service::PRICE_TYPE => $this->priceType->value,
            Service::LOCATION => $this->location,
            Service::IS_ACTIVE => $this->isActive,
        ];
    }
}