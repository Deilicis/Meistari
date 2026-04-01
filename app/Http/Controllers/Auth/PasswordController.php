<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Services\Repositories\User\UserLogicRepository;
use Illuminate\Http\RedirectResponse;

class PasswordController extends Controller
{
    private const MSG_PASSWORD_UPDATED = 'Jūsu parole ir veiksmīgi nomainīta!';
    private const FLASH_SUCCESS = 'success';

    public function __construct(
        private readonly UserLogicRepository $userLogicRepository
    ) {
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $this->authLogicRepository->updatePassword($request->toDTO(), $request->user());

        return back()->with(self::FLASH_SUCCESS, self::MSG_PASSWORD_UPDATED);
    }
}
