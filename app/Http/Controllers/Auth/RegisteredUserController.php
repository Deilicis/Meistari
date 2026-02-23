<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\Profile\ProfileTypeEnum;
use App\Enums\Role\RoleNameEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Services\Repositories\User\UserLogicRepository;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly UserLogicRepository $userLogicRepository
    ) {
    }

    public function createRegisterView(): Response
    {
        return Inertia::render('Auth/Register', [
            'roles' => RoleNameEnum::cases(),
            'profileTypes' => ProfileTypeEnum::cases(),
        ]);
    }

    public function registerUser(RegisterUserRequest $request): RedirectResponse
    {
        $this->userLogicRepository->registerUser($request->toDTO());

        return $request->responseRedirect();
    }
}