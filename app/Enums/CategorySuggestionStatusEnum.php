<?php

declare(strict_types=1);

namespace App\Enums;

enum CategorySuggestionStatusEnum: string
{
    case PENDING  = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case MERGED   = 'merged';

    public function label(): string
    {
        return match ($this) {
            self::PENDING  => 'Gaida apstiprinājumu',
            self::APPROVED => 'Apstiprināts',
            self::REJECTED => 'Noraidīts',
            self::MERGED   => 'Apvienots',
        };
    }
}
