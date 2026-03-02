<?php

declare(strict_types=1);

namespace App\Services\Repositories\Service;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ServiceDbRepository
{
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Service::with(['category', 'user'])
            ->latest()
            ->paginate($perPage);
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

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where(Service::TITLE, 'like', $search)
                  ->orWhere(Service::DESCRIPTION, 'like', $search);
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where(Service::CATEGORY_ID, $filters['category_id']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where(Service::IS_ACTIVE, (bool) $filters['is_active']);
        }

        if (!empty($filters['price_min'])) {
            $query->where(Service::PRICE, '>=', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $query->where(Service::PRICE, '<=', $filters['price_max']);
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