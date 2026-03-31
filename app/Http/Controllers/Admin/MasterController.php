<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\Role\RoleNameEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
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

        return Inertia::render('Admin/Masters/Index', [
            'masters' => $query->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(User $user): Response
    {
        $user->load(['profile', 'services.category', 'reviewsReceived.reviewer.profile']);

        return Inertia::render('Admin/Masters/Show', [
            'master' => $user,
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

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.masters.index')
            ->with(self::FLASH_SUCCESS, self::MSG_DELETED);
    }
}
