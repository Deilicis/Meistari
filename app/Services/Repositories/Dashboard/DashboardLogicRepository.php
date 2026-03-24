<?php

declare(strict_types=1);

namespace App\Services\Repositories\Dashboard;

use App\Enums\Job\JobStatusEnum;
use App\Enums\Role\RoleNameEnum;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardLogicRepository
{
    public function getDashboardData(User $user): array
    {
        $roles = $user->roles->pluck('name')
            ->map(fn ($r) => $r instanceof RoleNameEnum ? $r->value : (string) $r)
            ->toArray();

        $stats = [];
        $recentServices = [];
        $recentJobRequests = [];

        if (in_array(RoleNameEnum::MASTER->value, $roles)) {
            $stats = $this->getMasterStats($user);
            $recentServices = $this->getMasterRecentServices($user);
        } elseif (in_array(RoleNameEnum::SEEKER->value, $roles)) {
            $stats = $this->getSeekerStats($user);
            $recentJobRequests = $this->getSeekerRecentJobRequests($user);
        } elseif (in_array(RoleNameEnum::ADMIN->value, $roles)) {
            $stats = $this->getAdminStats($user);
        }

        return [
            'roles'             => $roles,
            'stats'             => $stats,
            'recentServices'    => $recentServices,
            'recentJobRequests' => $recentJobRequests,
        ];
    }

    private function getMasterStats(User $user): array
    {
        return [
            'total_services'      => $user->services()->count(),
            'active_services'     => $user->services()->where('is_active', true)->count(),
            'active_applications' => 0,
        ];
    }

    private function getMasterRecentServices(User $user): Collection
    {
        return $user->services()
            ->with('category')
            ->latest()
            ->limit(4)
            ->get();
    }

    private function getSeekerStats(User $user): array
    {
        return [
            'active_job_requests' => $user->jobRequests()->where('status', JobStatusEnum::ACTIVE->value)->count(),
            'total_job_requests'  => $user->jobRequests()->count(),
        ];
    }

    private function getSeekerRecentJobRequests(User $user): Collection
    {
        return $user->jobRequests()
            ->with('category')
            ->latest()
            ->limit(4)
            ->get();
    }

    private function getAdminStats(User $user): array
    {
        return [];
    }
}