<?php

declare(strict_types=1);

namespace App\Services\Users;

use App\Constants\ErrorMessages;
use App\Enums\Role\RoleNameEnum;
use App\Models\User;
use App\Services\Repositories\User\UserDbRepository;

class AddRoleService
{
    public function __construct(
        private readonly UserDbRepository $userDbRepository,
    ) {}

    public function addMasterRole(int $userId): User
    {
        $user = User::with('roles')->findOrFail($userId);

        if ($user->isMaster()) {
            abort(422, ErrorMessages::ROLE_ALREADY_EXISTS);
        }

        $role = $this->userDbRepository->findRoleByName(RoleNameEnum::MASTER->value);
        $this->userDbRepository->assignRole($user, $role);

        return $this->userDbRepository->setActiveRole($user->refresh(), RoleNameEnum::MASTER);
    }

    public function addSeekerRole(int $userId): User
    {
        $user = User::with('roles')->findOrFail($userId);

        if ($user->isSeeker()) {
            abort(422, ErrorMessages::ROLE_ALREADY_EXISTS);
        }

        $role = $this->userDbRepository->findRoleByName(RoleNameEnum::SEEKER->value);
        $this->userDbRepository->assignRole($user, $role);

        return $this->userDbRepository->setActiveRole($user->refresh(), RoleNameEnum::SEEKER);
    }
}
