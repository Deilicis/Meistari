<?php

declare(strict_types=1);

namespace App\Services\Repositories\Dashboard;

use App\Enums\Job\JobStatusEnum;
use App\Enums\Role\RoleNameEnum;
use App\Models\User;

class DashboardLogicRepository
{
    public function getDashboardData(User $user): array
    {
        $roles = $user->roles->pluck('name')->toArray();
        $stats = [];

        if (in_array(RoleNameEnum::MASTER->value, $roles)) {
            $stats = $this->getMasterStats($user);
        } elseif (in_array(RoleNameEnum::SEEKER->value, $roles)) {
            $stats = $this->getSeekerStats($user);
        } elseif (in_array(RoleNameEnum::ADMIN->value, $roles)) {
            $stats = $this->getAdminStats($user);
        }

        return [
            'roles' => $roles,
            'stats' => $stats,
        ];
    }

    private function getMasterStats(User $user): array
    {
        return [
            'total_services' => $user->services()->count(),
            'active_applications' => 0, // Pabeigt
        ];
    }

    private function getSeekerStats(User $user): array
    {
        return [
            'active_job_requests' => $user->jobRequests()->where('status', JobStatusEnum::OPEN->value)->count(),
        ];
    }

    private function getAdminStats(User $user): array
    {
        return [
            // ...
        ];
    }
}