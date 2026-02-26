<?php

declare(strict_types=1);

namespace App\Http\Requests\JobRequest;

use App\DataTransferObjects\JobRequest\SaveJobRequestData;
use App\Enums\Job\JobStatusEnum;
use App\Helpers\ValidationRuleHelper;
use App\Models\Category;
use App\Models\JobRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled(JobRequest::SLUG) && $this->filled(JobRequest::TITLE)) {
            $this->merge([
                JobRequest::SLUG => Str::slug($this->input(JobRequest::TITLE)),
            ]);
        }

        if (! $this->has(JobRequest::STATUS)) {
            $this->merge([
                JobRequest::STATUS => JobStatusEnum::OPEN->value,
            ]);
        }
    }

    public function rules(): array
    {
        $jobRequestId = $this->route('job_request')?->getId();

        return [
            JobRequest::CATEGORY_ID => [
                ValidationRuleHelper::REQUIRED,
                ValidationRuleHelper::INTEGER,
                ValidationRuleHelper::EXISTS . ':' . Category::TABLE . ',' . Category::ID
            ],
            JobRequest::TITLE => [
                ValidationRuleHelper::REQUIRED,
                ValidationRuleHelper::STRING,
                ValidationRuleHelper::MAX_255
            ],
            JobRequest::SLUG => [
                ValidationRuleHelper::REQUIRED,
                ValidationRuleHelper::STRING,
                ValidationRuleHelper::MAX_255,
                ValidationRuleHelper::UNIQUE . ':' . JobRequest::TABLE . ',' . JobRequest::SLUG . ',' . $jobRequestId
            ],
            JobRequest::DESCRIPTION => [
                ValidationRuleHelper::REQUIRED,
                ValidationRuleHelper::STRING
            ],
            JobRequest::BUDGET => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::NUMERIC,
                'min:0'
            ],
            JobRequest::LOCATION => [
                ValidationRuleHelper::REQUIRED,
                ValidationRuleHelper::STRING,
                ValidationRuleHelper::MAX_255
            ],
            JobRequest::DEADLINE => [
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::DATE,
                'after_or_equal:today'
            ],
            JobRequest::STATUS => [
                ValidationRuleHelper::REQUIRED,
                Rule::enum(JobStatusEnum::class)
            ],
        ];
    }

    public function toDTO(): SaveJobRequestData
    {
        $dto = new SaveJobRequestData();
        
        $dto->userId = $this->user()->id;
        $dto->categoryId = (int) $this->validated(JobRequest::CATEGORY_ID);
        $dto->title = $this->validated(JobRequest::TITLE);
        $dto->slug = $this->validated(JobRequest::SLUG);
        $dto->description = $this->validated(JobRequest::DESCRIPTION);
        
        $budget = $this->validated(JobRequest::BUDGET);
        $dto->budget = $budget !== null ? (float) $budget : null;
        
        $dto->location = $this->validated(JobRequest::LOCATION);
        $dto->deadline = $this->validated(JobRequest::DEADLINE);
        $dto->status = JobStatusEnum::from($this->validated(JobRequest::STATUS));

        return $dto;
    }
}