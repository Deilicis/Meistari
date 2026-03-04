<?php

declare(strict_types=1);

namespace App\DataTransferObjects\JobRequest;

use App\Enums\Job\JobStatusEnum;
use Illuminate\Http\UploadedFile;

class SaveJobRequestData
{
    public int $userId;
    public int $categoryId;
    public string $title;
    public string $description;
    public JobStatusEnum $status;
    public ?float $budget = null;
    public ?string $deadline = null;
    /** @var array<string> */
    public array $location = [];
    /** @var array<UploadedFile> */
    public array $images = [];
    /** @var array<string> */
    public array $imagesToDelete = [];
}