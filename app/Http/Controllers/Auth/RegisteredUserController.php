<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\Profile\ProfileTypeEnum;
use App\Enums\Role\RoleNameEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Services\Repositories\User\UserLogicRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly UserLogicRepository $userLogicRepository
    ) {
    }

    public function createRegisterView(Request $request): Response
    {
        $role = $request->query('role');
        $initialRole = in_array($role, ['master', 'seeker'], true) ? $role : null;

        return Inertia::render('Auth/Register', [
            'roles' => RoleNameEnum::cases(),
            'profileTypes' => ProfileTypeEnum::cases(),
            'initialRole' => $initialRole,
        ]);
    }

    public function registerUser(RegisterUserRequest $request): RedirectResponse
    {
        $this->userLogicRepository->registerUser($request->toDTO());

        return $request->responseRedirect();
    }
}