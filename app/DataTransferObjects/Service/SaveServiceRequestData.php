<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Service;

use App\Models\Service;

class SaveServiceRequestData
{
    public int $userId;
    public int $categoryId;
    public string $title;
    public string $slug;
    public string $description;
    public ?float $price = null;
    public array $location;
    public bool $isActive;
    public ?int $pendingCategorySuggestionId = null;

    public function toArray(): array
    {
        $data = [
            Service::USER_ID     => $this->userId,
            Service::CATEGORY_ID => $this->categoryId,
            Service::TITLE       => $this->title,
            Service::SLUG        => $this->slug,
            Service::DESCRIPTION => $this->description,
            Service::PRICE       => $this->price,
            Service::LOCATION    => $this->location,
            Service::IS_ACTIVE   => $this->isActive,
        ];

        if ($this->pendingCategorySuggestionId !== null) {
            $data[Service::PENDING_CATEGORY_SUGGESTION_ID] = $this->pendingCategorySuggestionId;
        }

        return $data;
    }
}