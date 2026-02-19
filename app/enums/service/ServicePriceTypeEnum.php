<?php

declare(strict_types=1);

namespace App\Enums\Service;

enum ServicePriceTypeEnum: string
{
    case HOURLY = 'hourly';
    case FIXED = 'fixed';
    case NEGOTIABLE = 'negotiable';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}