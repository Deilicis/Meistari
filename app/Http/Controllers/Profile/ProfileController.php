<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\DeleteAccountRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\Repositories\Profile\ProfileLogicRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileLogicRepository $profileLogicRepository
    ) {
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->profileLogicRepository->updateProfile($request->toDTO(), $request->user());

        return back()->with('success', 'Profila informācija veiksmīgi atjaunināta!');
    }

    public function destroy(DeleteAccountRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $this->profileLogicRepository->deleteProfile($request->toDTO(), $user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('info', 'Jūsu konts ir veiksmīgi izdzēsts.');
    }
}