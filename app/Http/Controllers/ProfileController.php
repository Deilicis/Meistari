<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Profile\DeleteAccountRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\Repositories\Profile\ProfileLogicRepository;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileLogicRepository $profileLogicRepository
    ) {
    }

    public function editView(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $this->profileLogicRepository->updateProfile($user, $request->toDTO());

        return Redirect::route('profile.edit');
    }

    public function deleteAccount(DeleteAccountRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $this->profileLogicRepository->deleteAccount($user, $request->toDTO());

        return Redirect::to('/');
    }
}