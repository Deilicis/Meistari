<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Services\Repositories\User\UserLogicRepository;
use Illuminate\Http\RedirectResponse;

class PasswordController extends Controller
{
    public function __construct(
        private readonly UserLogicRepository $userLogicRepository
    ) {
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $this->userLogicRepository->updatePassword($request->user(), $request->toDTO());

        return back();
    }
}