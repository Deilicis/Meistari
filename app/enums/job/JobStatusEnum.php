<?php

declare(strict_types=1);

namespace App\Enums\Job;

enum JobStatusEnum: string
{
    case ACTIVE = 'active';
    case ASSIGNED = 'assigned';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}