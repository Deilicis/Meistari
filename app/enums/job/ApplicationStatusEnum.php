<?php

declare(strict_types=1);

namespace App\Enums\Job;

enum ApplicationStatusEnum: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Gaida apstiprinājumu',
            self::ACCEPTED => 'Pieņemts',
            self::REJECTED => 'Noraidīts',
            self::COMPLETED => 'Pabeigts',
            self::CANCELLED => 'Atcelts',
        };
    }
}