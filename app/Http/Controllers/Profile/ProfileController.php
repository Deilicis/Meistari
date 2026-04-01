<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\DeleteAccountRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\Repositories\Profile\ProfileLogicRepository;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    private const MSG_UPDATED = 'Profila informācija veiksmīgi atjaunināta!';
    private const MSG_DELETED = 'Jūsu konts ir veiksmīgi izdzēsts.';
    private const FLASH_SUCCESS = 'success';
    private const FLASH_INFO = 'info';

    public function __construct(
        private readonly ProfileLogicRepository $profileLogicRepository
    ) {
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $this->profileLogicRepository->updateProfile($request->user(), $request->toDTO());

        return back()->with(self::FLASH_SUCCESS, self::MSG_UPDATED);
    }

    public function destroy(DeleteAccountRequest $request): RedirectResponse
    {
        $user = $request->user();

        $this->profileLogicRepository->deleteAccount($user, $request->toDTO());

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with(self::FLASH_INFO, self::MSG_DELETED);
    }
}
