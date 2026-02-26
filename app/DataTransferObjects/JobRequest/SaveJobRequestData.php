<?php

declare(strict_types=1);

namespace App\DataTransferObjects\JobRequest;

use App\Models\JobRequest;
use App\Enums\Job\JobStatusEnum;

class SaveJobRequestData
{
    public int $userId;
    public int $categoryId;
    public string $title;
    public string $slug;
    public string $description;
    public ?float $budget = null;
    public string $location;
    public ?string $deadline = null;
    public JobStatusEnum $status;

    public function toArray(): array
    {
        return [
            JobRequest::USER_ID => $this->userId,
            JobRequest::CATEGORY_ID => $this->categoryId,
            JobRequest::TITLE => $this->title,
            JobRequest::SLUG => $this->slug,
            JobRequest::DESCRIPTION => $this->description,
            JobRequest::BUDGET => $this->budget,
            JobRequest::LOCATION => $this->location,
            JobRequest::DEADLINE => $this->deadline,
            JobRequest::STATUS => $this->status->value,
        ];
    }
}