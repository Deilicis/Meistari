<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Repositories\Auth\AuthLogicRepository;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    public function __construct(
        private readonly AuthLogicRepository $authLogicRepository
    ) {
    }

    public function verifyEmail(EmailVerificationRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $this->authLogicRepository->verifyEmail($user);

        return redirect()->intended(route('dashboard', absolute: false) . '?verified=1');
    }
}