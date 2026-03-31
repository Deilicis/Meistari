<?php

declare(strict_types=1);

namespace App\Enums\Role;

enum RoleNameEnum: string
{
    case ADMIN     = 'admin';
    case MASTER    = 'master';
    case SEEKER    = 'seeker';
    case MODERATOR = 'moderator';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::ADMIN     => 'Administrators',
            self::MASTER    => 'Meistars',
            self::SEEKER    => 'Meklētājs',
            self::MODERATOR => 'Moderators',
        };
    }
}
