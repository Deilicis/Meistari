<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Services\Repositories\Auth\AuthLogicRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly AuthLogicRepository $authLogicRepository
    ) {
    }

    public function createLoginView(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    public function loginUser(LoginUserRequest $request): RedirectResponse
    {
        $this->authLogicRepository->login($request->toDTO(), $request->ip());

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function logoutUser(): RedirectResponse
    {
        $this->authLogicRepository->logout();

        return redirect('/');
    }
}