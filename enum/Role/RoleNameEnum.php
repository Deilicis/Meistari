<?php

declare(strict_types=1);

namespace App\Enum\Role;

enum RoleNameEnum: string
{
    case ADMIN = 'admin';
    case MASTER = 'master';
    case SEEKER = 'seeker';

    /**
     * * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}