<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\CategorySuggestionStatusEnum;
use App\Enums\Complaint\ComplaintStatusEnum;
use App\Enums\EscrowStatusEnum;
use App\Enums\Job\JobStatusEnum;
use App\Enums\Role\RoleNameEnum;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CategorySuggestion;
use App\Models\Complaint;
use App\Models\EscrowHold;
use App\Models\JobRequest;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $pendingComplaints = Complaint::where(Complaint::STATUS, ComplaintStatusEnum::PENDING->value)->count();
        $activeDisputes = JobRequest::where(JobRequest::STATUS, JobStatusEnum::DISPUTED->value)->count();
        $pendingSuggestions = CategorySuggestion::where(CategorySuggestion::STATUS, CategorySuggestionStatusEnum::PENDING->value)->count();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalUsers' => User::count(),
                'seekers' => User::whereHas('roles', fn ($q) => $q->where(Role::NAME, RoleNameEnum::SEEKER->value))->count(),
                'masters' => User::whereHas('roles', fn ($q) => $q->where(Role::NAME, RoleNameEnum::MASTER->value))->count(),
                'moderators' => User::whereHas('roles', fn ($q) => $q->where(Role::NAME, RoleNameEnum::MODERATOR->value))->count(),
                'services' => Service::count(),
                'jobRequests' => JobRequest::count(),
                'pendingComplaints' => $pendingComplaints,
                'activeDisputes' => $activeDisputes,
                'pendingSuggestions' => $pendingSuggestions,
                'escrowHeld' => (float) EscrowHold::where(EscrowHold::STATUS, EscrowStatusEnum::HELD->value)->sum(EscrowHold::AMOUNT),
            ],
            'jobsByStatus' => $this->jobsByStatus(),
            'jobsOverTime' => $this->jobsOverTime(),
            'needsAttention' => [
                'pendingComplaints' => $pendingComplaints,
                'activeDisputes' => $activeDisputes,
                'pendingSuggestions' => $pendingSuggestions,
            ],
            'recentComplaints' => Complaint::with(['reporter:id,name', 'reportedUser:id,name'])
                ->latest()
                ->take(5)
                ->get(),
            'recentAuditLogs' => AuditLog::with('user:id,name')
                ->latest()
                ->take(10)
                ->get(),
        ]);
    }

    /** @return array<string, int> */
    private function jobsByStatus(): array
    {
        $counts = [];
        foreach (JobStatusEnum::cases() as $status) {
            $counts[$status->value] = JobRequest::where(JobRequest::STATUS, $status->value)->count();
        }
        return $counts;
    }

    /** @return array<int, array{week: string, count: int}> */
    private function jobsOverTime(): array
    {
        $weeks = [];
        for ($i = 7; $i >= 0; $i--) {
            $weeks[] = Carbon::now()->startOfWeek()->subWeeks($i)->format('Y-m-d');
        }

        $grouped = JobRequest::query()
            ->where(JobRequest::CREATED_AT, '>=', Carbon::now()->subWeeks(8)->startOfWeek())
            ->get()
            ->groupBy(fn ($job) => $job->created_at->startOfWeek()->format('Y-m-d'))
            ->map(fn ($g) => $g->count());

        return array_map(fn ($w) => ['week' => $w, 'count' => $grouped[$w] ?? 0], $weeks);
    }
}
