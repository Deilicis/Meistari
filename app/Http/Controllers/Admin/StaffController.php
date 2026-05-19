<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\Role\RoleNameEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    private const MSG_CREATED = 'Darbinieks pievienots.';
    private const MSG_UPDATED = 'Darbinieks atjaunināts.';
    private const MSG_DELETED = 'Darbinieks dzēsts.';
    private const MSG_SELF_DELETE = 'Nevar dzēst savu kontu.';
    private const MSG_ADMIN_DELETE = 'Administratora kontu nevar dzēst šeit.';
    private const FLASH_SUCCESS = 'success';
    private const FLASH_ERROR = 'error';

    public function index(): Response
    {
        $staff = User::with(['roles', 'profile'])
            ->whereHas('roles', fn ($q) => $q->whereIn(Role::NAME, [
                RoleNameEnum::ADMIN->value,
                RoleNameEnum::MODERATOR->value,
            ]))
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Staff/Index', [
            'staff' => $staff,
        ]);
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $role = Role::where(Role::NAME, $request->validated('role'))->firstOrFail();

        $user = User::create([
            User::NAME => $request->validated(User::NAME),
            User::EMAIL => $request->validated(User::EMAIL),
            User::PASSWORD => bcrypt($request->validated('password')),
        ]);

        $user->forceFill([User::EMAIL_VERIFIED_AT => now()])->save();

        $user->roles()->attach($role);

        return back()->with(self::FLASH_SUCCESS, self::MSG_CREATED);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->only([User::NAME, User::EMAIL]));

        return back()->with(self::FLASH_SUCCESS, self::MSG_UPDATED);
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->getId() === auth()->id()) {
            return back()->with(self::FLASH_ERROR, self::MSG_SELF_DELETE);
        }

        $isAdmin = $user->roles()->where(Role::NAME, RoleNameEnum::ADMIN->value)->exists();
        if ($isAdmin) {
            return back()->with(self::FLASH_ERROR, self::MSG_ADMIN_DELETE);
        }

        $user->delete();

        return redirect()->route('admin.staff.index')
            ->with(self::FLASH_SUCCESS, self::MSG_DELETED);
    }
}
