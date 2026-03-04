<?php

declare(strict_types=1);

namespace App\Http\Requests\JobRequest;

use App\DataTransferObjects\JobRequest\SaveJobRequestData;
use App\Enums\Job\JobStatusEnum;
use App\Helpers\ValidationRuleHelper as Rules;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class SaveJobRequest extends FormRequest
{
    public const CATEGORY_ID = 'category_id';
    public const TITLE = 'title';
    public const DESCRIPTION = 'description';
    public const BUDGET = 'budget';
    public const DEADLINE = 'deadline';
    public const LOCATION = 'location';
    public const IMAGES = 'images';
    public const IMAGES_TO_DELETE = 'images_to_delete';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            self::CATEGORY_ID => [
                Rules::REQUIRED,
                Rules::INTEGER,
                Rules::EXISTS . ':' . Category::TABLE . ',' . Category::ID,
            ],
            self::TITLE => [
                Rules::REQUIRED,
                Rules::STRING,
                Rules::MAX_255,
            ],
            self::DESCRIPTION => [
                Rules::REQUIRED,
                Rules::STRING,
            ],
            self::BUDGET => [
                Rules::NULLABLE,
                Rules::NUMERIC,
                Rules::MIN . ':0',
            ],
            self::DEADLINE => [
                Rules::NULLABLE,
                Rules::DATE,
            ],
            self::LOCATION => [
                Rules::REQUIRED,
                Rules::ARRAY_RULE,
            ],
            self::LOCATION . '.*' => [
                Rules::STRING,
                Rules::MAX_255,
            ],
            self::IMAGES => [
                Rules::NULLABLE,
                Rules::ARRAY_RULE,
                Rules::MAX . ':10',
            ],
            self::IMAGES . '.*' => [
                Rules::IMAGE,
                Rules::MIMES . ':jpeg,png,jpg,webp',
                Rules::MAX . ':5120',
            ],
            self::IMAGES_TO_DELETE => [
                Rules::NULLABLE,
                Rules::ARRAY_RULE,
            ],
            self::IMAGES_TO_DELETE . '.*' => [
                Rules::STRING,
            ],
        ];
    }

    public function toDTO(): SaveJobRequestData
    {
        $dto = new SaveJobRequestData();
        
        $dto->userId = $this->user()->id;
        $dto->categoryId = (int) $this->validated(self::CATEGORY_ID);
        $dto->title = $this->validated(self::TITLE);
        $dto->description = $this->validated(self::DESCRIPTION);
        $dto->status = JobStatusEnum::ACTIVE;
        
        $dto->budget = $this->validated(self::BUDGET) !== null ? (float) $this->validated(self::BUDGET) : null;
        $dto->deadline = $this->validated(self::DEADLINE);
        $dto->location = $this->validated(self::LOCATION);
        
        $dto->images = $this->file(self::IMAGES) ?? [];
        $dto->imagesToDelete = $this->validated(self::IMAGES_TO_DELETE) ?? [];

        return $dto;
    }
}