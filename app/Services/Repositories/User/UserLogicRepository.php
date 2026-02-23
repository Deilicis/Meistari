<?php

declare(strict_types=1);

namespace App\Services\Repositories\User;

use App\DataTransferObjects\Auth\RegisterUserRequestData;
use App\DataTransferObjects\Auth\UpdatePasswordRequestData;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserLogicRepository
{
    public function __construct(
        private readonly UserDbRepository $userDbRepository
    ) {
    }

    public function registerUser(RegisterUserRequestData $data): User
    {
        return DB::transaction(function () use ($data) {
            
            $user = $this->userDbRepository->create([
                User::NAME => $data->name,
                User::EMAIL => $data->email,
                User::PASSWORD => Hash::make($data->password),
            ]);

            $role = $this->userDbRepository->findRoleByName($data->role->value);
            $this->userDbRepository->assignRole($user, $role);

            $this->userDbRepository->createProfile([
                Profile::USER_ID => $user->getId(),
                Profile::TYPE => $data->profile_type,
                Profile::CITY => $data->city,
                Profile::FIRST_NAME => $data->first_name,
                Profile::LAST_NAME => $data->last_name,
                Profile::COMPANY_NAME => $data->company_name,
                Profile::REG_NUMBER => $data->reg_number,
            ]);

            event(new Registered($user));

            Auth::login($user);

            return $user;
        });
    }
    public function updatePassword(User $user, UpdatePasswordRequestData $data): void
    {
        $this->userDbRepository->update($user, [
            User::PASSWORD => Hash::make($data->password),
        ]);
    }
}