<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\Complaint\ComplaintStatusEnum;
use App\Enums\Role\RoleNameEnum;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Complaint;
use App\Models\JobRequest;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'seekers' => User::whereHas('roles', fn ($q) => $q->where(Role::NAME, RoleNameEnum::SEEKER->value))->count(),
                'masters' => User::whereHas('roles', fn ($q) => $q->where(Role::NAME, RoleNameEnum::MASTER->value))->count(),
                'moderators' => User::whereHas('roles', fn ($q) => $q->where(Role::NAME, RoleNameEnum::MODERATOR->value))->count(),
                'services' => Service::count(),
                'jobRequests' => JobRequest::count(),
                'pendingComplaints' => Complaint::where(Complaint::STATUS, ComplaintStatusEnum::PENDING->value)->count(),
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
}
