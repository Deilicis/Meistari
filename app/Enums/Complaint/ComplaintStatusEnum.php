<?php

declare(strict_types=1);

namespace App\Enums\Complaint;

enum ComplaintStatusEnum: string
{
    case PENDING   = 'pending';
    case REVIEWED  = 'reviewed';
    case RESOLVED  = 'resolved';
    case DISMISSED = 'dismissed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::PENDING   => 'Gaida izskatīšanu',
            self::REVIEWED  => 'Izskatīts',
            self::RESOLVED  => 'Atrisināts',
            self::DISMISSED => 'Noraidīts',
        };
    }
}
