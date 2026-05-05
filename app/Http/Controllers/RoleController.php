<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\Users\SwitchActiveRoleDTO;
use App\Enums\Role\RoleNameEnum;
use App\Exceptions\RoleNotAvailableException;
use App\Http\Requests\Role\SwitchRoleRequest;
use App\Services\Users\AddRoleService;
use App\Services\Users\RoleSwitchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleSwitchService $roleSwitchService,
        private readonly AddRoleService $addRoleService,
    ) {}

    public function switch(SwitchRoleRequest $request): RedirectResponse
    {
        $targetRole = RoleNameEnum::from($request->validated()['target_role']);

        try {
            $this->roleSwitchService->switchActiveRole(new SwitchActiveRoleDTO(
                userId: $request->user()->getId(),
                targetRole: $targetRole,
            ));
        } catch (RoleNotAvailableException $e) {
            return back()->withErrors(['target_role' => $e->getMessage()]);
        }

        return redirect()->route('dashboard');
    }

    public function addMaster(Request $request): RedirectResponse
    {
        $this->addRoleService->addMasterRole($request->user()->getId());

        return redirect()->route('dashboard')
            ->with('success', 'Meistara loma veiksmīgi pievienota!');
    }

    public function addSeeker(Request $request): RedirectResponse
    {
        $this->addRoleService->addSeekerRole($request->user()->getId());

        return redirect()->route('dashboard')
            ->with('success', 'Meklētāja loma veiksmīgi pievienota!');
    }
}
