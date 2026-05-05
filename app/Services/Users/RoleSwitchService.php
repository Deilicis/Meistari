<?php

declare(strict_types=1);

namespace App\Services\Users;

use App\DTOs\Users\SwitchActiveRoleDTO;
use App\Enums\Role\RoleNameEnum;
use App\Exceptions\RoleNotAvailableException;
use App\Models\User;
use App\Services\Repositories\User\UserDbRepository;

class RoleSwitchService
{
    public function __construct(
        private readonly UserDbRepository $userDbRepository,
    ) {}

    public function switchActiveRole(SwitchActiveRoleDTO $dto): User
    {
        $user = User::with('roles')->findOrFail($dto->userId);

        match ($dto->targetRole) {
            RoleNameEnum::MASTER => $user->isMaster() ?: throw new RoleNotAvailableException(),
            RoleNameEnum::SEEKER => $user->isSeeker() ?: throw new RoleNotAvailableException(),
            default              => throw new RoleNotAvailableException(),
        };

        return $this->userDbRepository->setActiveRole($user, $dto->targetRole);
    }
}
