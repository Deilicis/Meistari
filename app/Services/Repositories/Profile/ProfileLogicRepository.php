<?php

declare(strict_types=1);

namespace App\Services\Repositories\Profile;

use App\DataTransferObjects\Profile\DeleteAccountRequestData;
use App\DataTransferObjects\Profile\UpdateProfileRequestData;
use App\Models\Profile;
use App\Models\User;
use App\Services\Repositories\User\UserDbRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileLogicRepository
{
    public function __construct(
        private readonly UserDbRepository $userDbRepository,
        private readonly ProfileDbRepository $profileDbRepository
    ) {
    }

    public function updateProfile(User $user, UpdateProfileRequestData $data): void
    {
        DB::transaction(function () use ($user, $data) {
            $userUpdateData = [
                User::NAME => $data->name,
                User::EMAIL => $data->email,
            ];

            if ($user->getEmail() !== $data->email) {
                $userUpdateData[User::EMAIL_VERIFIED_AT] = null;
            }

            $this->userDbRepository->update($user, $userUpdateData);

            $this->profileDbRepository->update($user->profile, [
                Profile::CITY => $data->city,
                Profile::FIRST_NAME => $data->first_name,
                Profile::LAST_NAME => $data->last_name,
                Profile::COMPANY_NAME => $data->company_name,
                Profile::REG_NUMBER => $data->reg_number,
                Profile::VAT_NUMBER => $data->vat_number,
                Profile::PHONE => $data->phone,
                Profile::BIO => $data->bio,
            ]);
        });
    }

    public function deleteAccount(User $user, DeleteAccountRequestData $data): void
    {
        Auth::logout();

        $this->userDbRepository->delete($user);

        session()->invalidate();
        session()->regenerateToken();
    }
}