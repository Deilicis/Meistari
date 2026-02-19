<?php

declare(strict_types=1);

namespace App\Enums\Role;

enum RoleNameEnum: string
{
    case ADMIN = 'admin';
    case MASTER = 'master';
    case SEEKER = 'seeker';
    
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}