<?php

declare(strict_types=1);

namespace App\DTOs\Users;

use App\Enums\Role\RoleNameEnum;

final class SwitchActiveRoleDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly RoleNameEnum $targetRole,
    ) {}
}
