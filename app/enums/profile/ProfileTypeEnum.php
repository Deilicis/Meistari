<?php

declare(strict_types=1);

namespace App\Enums\Profile;

enum ProfileTypeEnum: string
{
    case INDIVIDUAL = 'individual';
    case COMPANY = 'company';

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
            self::INDIVIDUAL => 'Privātpersona',
            self::COMPANY => 'Uzņēmums',
        };
    }
}