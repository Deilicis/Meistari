<?php

declare(strict_types=1);

namespace App\Enums\Job;

enum JobStatusEnum: string
{
    case OPEN = 'open';
    case ACCEPTED = 'accepted';
    case IN_PROGRESS = 'in_progress';
    case AWAITING_CONFIRMATION = 'awaiting_confirmation';
    case COMPLETED = 'completed';
    case DISPUTED = 'disputed';
    case CANCELLED = 'cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::OPEN => 'Atvērts',
            self::ACCEPTED => 'Pieņemts',
            self::IN_PROGRESS => 'Darbā',
            self::AWAITING_CONFIRMATION => 'Gaida apstiprinājumu',
            self::COMPLETED => 'Pabeigts',
            self::DISPUTED => 'Strīdā',
            self::CANCELLED => 'Atcelts',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::OPEN => 'slate',
            self::ACCEPTED => 'blue',
            self::IN_PROGRESS => 'yellow',
            self::AWAITING_CONFIRMATION => 'orange',
            self::COMPLETED => 'green',
            self::DISPUTED => 'red',
            self::CANCELLED => 'gray',
        };
    }
}
