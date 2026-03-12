<?php

declare(strict_types=1);

namespace App\Services\Repositories\Service;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ServiceDbRepository
{
    private const LIKE = 'like';
    private const GTE = '>=';
    private const LTE = '<=';
    private const FILTER_SEARCH = 'search';
    private const FILTER_CATEGORY = 'category_id';
    private const FILTER_PRICE_MIN = 'price_min';
    private const FILTER_PRICE_MAX = 'price_max';
    private const FILTER_IS_ACTIVE = 'is_active';

    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Service::with(['category', 'user'])
            ->latest()
            ->paginate($perPage);
    }

    public function getPublicPaginated(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return Service::with(['user.profile', 'category'])
            ->where(Service::IS_ACTIVE, true)
            ->when($filters[self::FILTER_SEARCH] ?? null, fn ($q, $v) => $q->where(Service::TITLE, self::LIKE, "%{$v}%"))
            ->when($filters[self::FILTER_CATEGORY] ?? null, fn ($q, $v) => $q->where(Service::CATEGORY_ID, $v))
            ->when($filters[self::FILTER_PRICE_MIN] ?? null, fn ($q, $v) => $q->where(Service::PRICE, self::GTE, $v))
            ->when($filters[self::FILTER_PRICE_MAX] ?? null, fn ($q, $v) => $q->where(Service::PRICE, self::LTE, $v))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getByUserId(int $userId): Collection
    {
        return Service::with('category')
            ->where(Service::USER_ID, $userId)
            ->latest()
            ->get();
    }

    public function getById(int $id): Service
    {
        return Service::with(['category', 'user'])->findOrFail($id);
    }

    public function getUserServices(int $userId, array $filters = []): Collection
    {
        $query = Service::query()
            ->where(Service::USER_ID, $userId)
            ->with('category');

        if (!empty($filters[self::FILTER_SEARCH])) {
            $search = '%' . $filters[self::FILTER_SEARCH] . '%';
            $query->where(function ($q) use ($search) {
                $q->where(Service::TITLE, self::LIKE, $search)
                  ->orWhere(Service::DESCRIPTION, self::LIKE, $search);
            });
        }

        if (!empty($filters[self::FILTER_CATEGORY])) {
            $query->where(Service::CATEGORY_ID, $filters[self::FILTER_CATEGORY]);
        }

        if (isset($filters[self::FILTER_IS_ACTIVE]) && $filters[self::FILTER_IS_ACTIVE] !== '') {
            $query->where(Service::IS_ACTIVE, (bool) $filters[self::FILTER_IS_ACTIVE]);
        }

        if (!empty($filters[self::FILTER_PRICE_MIN])) {
            $query->where(Service::PRICE, self::GTE, $filters[self::FILTER_PRICE_MIN]);
        }

        if (!empty($filters[self::FILTER_PRICE_MAX])) {
            $query->where(Service::PRICE, self::LTE, $filters[self::FILTER_PRICE_MAX]);
        }

        return $query->latest()->get();
    }

    public function create(array $data): Service
    {
        return Service::create($data);
    }

    public function update(Service $service, array $data): bool
    {
        return $service->update($data);
    }

    public function delete(Service $service): ?bool
    {
        return $service->delete();
    }
}
