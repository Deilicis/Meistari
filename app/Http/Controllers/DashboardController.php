<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Role\RoleNameEnum;
use App\Services\Repositories\Dashboard\DashboardLogicRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardLogicRepository $dashboardLogicRepo
    ) {
    }

    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        $isStaff = $user->roles
            ->pluck('name')
            ->map(fn ($r) => $r instanceof RoleNameEnum ? $r->value : (string) $r)
            ->intersect([RoleNameEnum::ADMIN->value, RoleNameEnum::MODERATOR->value])
            ->isNotEmpty();

        if ($isStaff) {
            return redirect()->route('admin.dashboard');
        }

        $data = $this->dashboardLogicRepo->getDashboardData($user);

        return Inertia::render('Dashboard', $data);
    }
}
