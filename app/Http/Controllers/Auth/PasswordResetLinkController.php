<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendPasswordResetLinkRequest;
use App\Services\Repositories\Auth\AuthLogicRepository;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    public function __construct(
        private readonly AuthLogicRepository $authLogicRepository
    ) {
    }

    public function createView(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function sendResetLink(SendPasswordResetLinkRequest $request): RedirectResponse
    {
        $status = $this->authLogicRepository->sendPasswordResetLink($request->toDTO());

        return back()->with('status', __($status));
    }
}