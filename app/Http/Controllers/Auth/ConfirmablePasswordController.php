<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConfirmPasswordRequest;
use App\Services\Repositories\Auth\AuthLogicRepository;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ConfirmablePasswordController extends Controller
{
    public function __construct(
        private readonly AuthLogicRepository $authLogicRepository
    ) {
    }

    public function showConfirmView(): Response
    {
        return Inertia::render('Auth/ConfirmPassword');
    }

    public function confirmPassword(ConfirmPasswordRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        
        $this->authLogicRepository->confirmPassword($user, $request->toDTO());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}