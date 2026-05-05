<?php

declare(strict_types=1);

namespace App\Services\Repositories\Dashboard;

use App\Enums\Job\ApplicationStatusEnum;
use App\Enums\Job\JobStatusEnum;
use App\Enums\Role\RoleNameEnum;
use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardLogicRepository
{
    public function getDashboardData(User $user): array
    {
        $activeRole = $user->getActiveRole();

        $stats = [];
        $recentServices = [];
        $recentJobRequests = [];
        $recentApplications = [];
        $recentApplicationsReceived = [];

        if ($activeRole === RoleNameEnum::MASTER) {
            $stats = $this->getMasterStats($user);
            $recentServices = $this->getMasterRecentServices($user);
            $recentApplications = $this->getMasterRecentApplications($user);
        } elseif ($activeRole === RoleNameEnum::SEEKER) {
            $stats = $this->getSeekerStats($user);
            $recentJobRequests = $this->getSeekerRecentJobRequests($user);
            $recentApplicationsReceived = $this->getSeekerRecentApplicationsReceived($user);
        }

        return [
            'stats'                      => $stats,
            'recentServices'             => $recentServices,
            'recentJobRequests'          => $recentJobRequests,
            'recentApplications'         => $recentApplications,
            'recentApplicationsReceived' => $recentApplicationsReceived,
        ];
    }

    private function getMasterStats(User $user): array
    {
        return [
            'total_services'       => $user->services()->count(),
            'active_services'      => $user->services()->where('is_active', true)->count(),
            'pending_applications' => $user->applications()->where(Application::STATUS, ApplicationStatusEnum::PENDING->value)->count(),
            'total_applications'   => $user->applications()->count(),
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

    private function getMasterRecentApplications(User $user): Collection
    {
        return $user->applications()
            ->with(['jobRequest.category'])
            ->whereIn(Application::STATUS, [
                ApplicationStatusEnum::PENDING->value,
                ApplicationStatusEnum::ACCEPTED->value,
            ])
            ->latest()
            ->limit(4)
            ->get();
    }

    private function getSeekerStats(User $user): array
    {
        $pendingReceived = Application::whereHas(
            'jobRequest',
            fn ($q) => $q->where('user_id', $user->id)
        )
            ->where(Application::STATUS, ApplicationStatusEnum::PENDING->value)
            ->count();

        return [
            'active_job_requests'           => $user->jobRequests()->where('status', JobStatusEnum::OPEN->value)->count(),
            'total_job_requests'            => $user->jobRequests()->count(),
            'pending_applications_received' => $pendingReceived,
        ];
    }

    private function getSeekerRecentJobRequests(User $user): Collection
    {
        return $user->jobRequests()
            ->with('category')
            ->withCount('applications')
            ->latest()
            ->limit(4)
            ->get();
    }

    private function getSeekerRecentApplicationsReceived(User $user): Collection
    {
        return Application::whereHas(
            'jobRequest',
            fn ($q) => $q->where('user_id', $user->id)
        )
            ->with(['jobRequest', 'user.profile'])
            ->where(Application::STATUS, ApplicationStatusEnum::PENDING->value)
            ->latest()
            ->limit(4)
            ->get();
    }

    private function getAdminStats(User $user): array
    {
        return [];
    }
}