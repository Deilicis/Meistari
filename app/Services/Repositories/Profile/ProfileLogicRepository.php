<?php

declare(strict_types=1);

namespace App\Services\Repositories\Profile;

use App\DataTransferObjects\Profile\DeleteAccountRequestData;
use App\DataTransferObjects\Profile\UpdateProfileRequestData;
use App\Models\Profile;
use App\Models\User;
use App\Services\Repositories\User\UserDbRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

            $profile = $user->profile;
            $avatarPath = $profile->getAvatar();
            $portfolioImages = $profile->getPortfolioImages();

            if ($data->avatar instanceof UploadedFile) {
                if ($avatarPath) {
                    Storage::disk(Profile::STORAGE_DISK)->delete($avatarPath);
                }
                $avatarPath = $data->avatar->store(Profile::AVATAR_DIR, Profile::STORAGE_DISK);
            } elseif ($data->avatar === Profile::AVATAR_DELETE_FLAG && $avatarPath) {
                Storage::disk(Profile::STORAGE_DISK)->delete($avatarPath);
                $avatarPath = null;
            }

            if (!empty($data->imagesToDelete)) {
                foreach ($data->imagesToDelete as $path) {
                    Storage::disk(Profile::STORAGE_DISK)->delete($path);
                    $portfolioImages = array_filter($portfolioImages, fn($p) => $p !== $path);
                }
                $portfolioImages = array_values($portfolioImages);
            }

            if (!empty($data->portfolioImages)) {
                foreach ($data->portfolioImages as $image) {
                    if ($image instanceof UploadedFile) {
                        $portfolioImages[] = $image->store(Profile::PORTFOLIO_DIR, Profile::STORAGE_DISK);
                    }
                }
            }

            $this->profileDbRepository->update($profile, [
                Profile::TYPE => $data->type->value,
                Profile::FIRST_NAME => $data->firstName,
                Profile::LAST_NAME => $data->lastName,
                Profile::COMPANY_NAME => $data->companyName,
                Profile::REG_NUMBER => $data->regNumber,
                Profile::VAT_NUMBER => $data->vatNumber,
                Profile::CITY => $data->city,
                Profile::PHONE => $data->phoneNumber,
                Profile::BIO => $data->description,
                Profile::AVATAR => $avatarPath,
                Profile::EXPERIENCES => $data->experiences,
                Profile::PORTFOLIO_IMAGES => $portfolioImages,
            ]);
        });
    }

    public function deleteAccount(User $user, DeleteAccountRequestData $data): void
    {
        Auth::logout();
        $this->userDbRepository->delete($user);
    }
}
