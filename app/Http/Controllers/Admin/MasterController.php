<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\EscrowStatusEnum;
use App\Enums\Job\JobStatusEnum;
use App\Enums\Role\RoleNameEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\EscrowHold;
use App\Models\JobRequest;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MasterController extends Controller
{
    private const MSG_UPDATED = 'Meistars atjaunināts.';
    private const MSG_DELETED = 'Meistars dzēsts.';
    private const FLASH_SUCCESS = 'success';
    private const FLASH_ERROR = 'error';

    private const ACTIVE_STATUSES = [
        JobStatusEnum::ACCEPTED,
        JobStatusEnum::IN_PROGRESS,
        JobStatusEnum::AWAITING_CONFIRMATION,
        JobStatusEnum::DISPUTED,
    ];

    public function index(Request $request): Response
    {
        $query = User::with('profile')
            ->whereHas('roles', fn ($q) => $q->where(Role::NAME, RoleNameEnum::MASTER->value));

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where(User::NAME, 'like', "%{$search}%")
                  ->orWhere(User::EMAIL, 'like', "%{$search}%");
            });
        }

        if ($request->get('status') === 'suspended') {
            $query->whereNotNull(User::SUSPENDED_AT);
        } elseif ($request->get('status') === 'active') {
            $query->whereNull(User::SUSPENDED_AT);
        }

        return Inertia::render('Admin/Masters/Index', [
            'masters' => $query->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function show(User $user): Response
    {
        $user->load(['profile']);

        return Inertia::render('Admin/Masters/Show', [
            'master'   => $user,
            'services' => $user->services()
                ->with('category')
                ->latest()
                ->paginate(10, ['*'], 'services_page')
                ->withQueryString(),
            'reviews'  => $user->reviewsReceived()
                ->with('reviewer.profile')
                ->latest()
                ->paginate(10, ['*'], 'reviews_page')
                ->withQueryString(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->only([User::NAME, User::EMAIL]));

        if ($request->has(Profile::IS_VERIFIED) && $user->profile) {
            $user->profile->update([
                Profile::IS_VERIFIED => $request->boolean(Profile::IS_VERIFIED),
            ]);
        }

        return back()->with(self::FLASH_SUCCESS, self::MSG_UPDATED);
    }

    public function suspend(User $user): RedirectResponse
    {
        if ($user->roles()->whereIn(Role::NAME, [RoleNameEnum::ADMIN->value, RoleNameEnum::MODERATOR->value])->exists()) {
            return back()->with(self::FLASH_ERROR, 'Nevar apturēt administratoru vai moderatoru.');
        }

        $user->update([User::SUSPENDED_AT => now()]);

        return back()->with(self::FLASH_SUCCESS, 'Lietotājs apturēts.');
    }

    public function unsuspend(User $user): RedirectResponse
    {
        $user->update([User::SUSPENDED_AT => null]);

        return back()->with(self::FLASH_SUCCESS, 'Lietotāja apturēšana atcelta.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $activeStatuses = array_map(fn (JobStatusEnum $s) => $s->value, self::ACTIVE_STATUSES);

        $reasons = [];

        $activeAsSeeker = $user->jobRequests()
            ->whereIn(JobRequest::STATUS, $activeStatuses)
            ->count();
        if ($activeAsSeeker > 0) {
            $reasons[] = "{$activeAsSeeker} aktīvi sludinājumi";
        }

        $activeAsMaster = JobRequest::where(JobRequest::MASTER_ID, $user->getId())
            ->whereIn(JobRequest::STATUS, $activeStatuses)
            ->count();
        if ($activeAsMaster > 0) {
            $reasons[] = "{$activeAsMaster} aktīvi darbi kā meistaram";
        }

        $heldEscrow = EscrowHold::where(function ($q) use ($user) {
            $q->where(EscrowHold::CLIENT_ID, $user->getId())
              ->orWhere(EscrowHold::MASTER_ID, $user->getId());
        })->where(EscrowHold::STATUS, EscrowStatusEnum::HELD->value)->count();
        if ($heldEscrow > 0) {
            $reasons[] = "{$heldEscrow} aktīvi darījumi";
        }

        if (!empty($reasons)) {
            return back()->with(self::FLASH_ERROR, 'Nevar dzēst: ' . implode(', ', $reasons) . '. Vispirms apturi lietotāju.');
        }

        $user->delete();

        return redirect()->route('admin.masters.index')
            ->with(self::FLASH_SUCCESS, self::MSG_DELETED);
    }
}
