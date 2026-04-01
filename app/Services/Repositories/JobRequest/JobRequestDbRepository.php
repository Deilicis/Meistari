<?php

declare(strict_types=1);

namespace App\Services\Repositories\JobRequest;

use App\Enums\Job\JobStatusEnum;
use App\Models\Application;
use App\Models\Category;
use App\Models\JobRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class JobRequestDbRepository
{
    private const LIKE = 'like';
    private const GTE = '>=';
    private const LTE = '<=';
    private const FILTER_SEARCH = 'search';
    private const FILTER_CATEGORY = 'category_id';
    private const FILTER_STATUS = 'status';
    private const FILTER_BUDGET_MIN = 'budget_min';
    private const FILTER_BUDGET_MAX = 'budget_max';

    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return JobRequest::with(['category', 'user'])
            ->latest()
            ->paginate($perPage);
    }

    public function getPublicPaginated(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = JobRequest::with(['category', 'user.profile'])
            ->withCount('applications')
            ->where(JobRequest::STATUS, JobStatusEnum::ACTIVE->value);

        if (!empty($filters[self::FILTER_SEARCH])) {
            $query->where(JobRequest::TITLE, self::LIKE, '%' . $filters[self::FILTER_SEARCH] . '%');
        }
        if (!empty($filters[self::FILTER_CATEGORY])) {
            $query->whereIn(JobRequest::CATEGORY_ID, $this->resolveCategoryIds((int) $filters[self::FILTER_CATEGORY]));
        }
        if (!empty($filters[self::FILTER_BUDGET_MIN])) {
            $query->where(JobRequest::BUDGET, self::GTE, $filters[self::FILTER_BUDGET_MIN]);
        }
        if (!empty($filters[self::FILTER_BUDGET_MAX])) {
            $query->where(JobRequest::BUDGET, self::LTE, $filters[self::FILTER_BUDGET_MAX]);
        }

        return $query->latest()->paginate($perPage);
    }

    public function getByUserId(int $userId, array $filters = []): Collection
    {
        $query = JobRequest::with(['category'])
            ->withCount('applications')
            ->where(JobRequest::USER_ID, $userId);

        if (!empty($filters[self::FILTER_SEARCH])) {
            $query->where(JobRequest::TITLE, self::LIKE, '%' . $filters[self::FILTER_SEARCH] . '%');
        }

        if (!empty($filters[self::FILTER_CATEGORY])) {
            $query->whereIn(JobRequest::CATEGORY_ID, $this->resolveCategoryIds((int) $filters[self::FILTER_CATEGORY]));
        }

        if (!empty($filters[self::FILTER_STATUS])) {
            $query->where(JobRequest::STATUS, $filters[self::FILTER_STATUS]);
        }

        if (!empty($filters[self::FILTER_BUDGET_MIN])) {
            $query->where(JobRequest::BUDGET, self::GTE, $filters[self::FILTER_BUDGET_MIN]);
        }

        if (!empty($filters[self::FILTER_BUDGET_MAX])) {
            $query->where(JobRequest::BUDGET, self::LTE, $filters[self::FILTER_BUDGET_MAX]);
        }

        return $query->latest()->get();
    }

    public function getById(int $id): JobRequest
    {
        return JobRequest::with(['category', 'user', 'applications'])->findOrFail($id);
    }

    public function create(array $data): JobRequest
    {
        return JobRequest::create($data);
    }

    public function update(JobRequest $jobRequest, array $data): bool
    {
        return $jobRequest->update($data);
    }

    public function delete(JobRequest $jobRequest): ?bool
    {
        return $jobRequest->delete();
    }

    public function setAssigned(JobRequest $jobRequest): JobRequest
    {
        $jobRequest->update([JobRequest::STATUS => JobStatusEnum::ASSIGNED->value]);
        return $jobRequest;
    }

    public function setCompleted(JobRequest $jobRequest): JobRequest
    {
        $jobRequest->update([JobRequest::STATUS => JobStatusEnum::COMPLETED->value]);
        return $jobRequest;
    }

    private function resolveCategoryIds(int $categoryId): array
    {
        $childIds = Category::where(Category::PARENT_ID, $categoryId)
            ->pluck(Category::ID)
            ->toArray();

        return array_merge([$categoryId], $childIds);
    }
}
