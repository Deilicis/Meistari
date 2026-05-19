<?php

declare(strict_types=1);

namespace App\Enums;

enum PriceProposalStatusEnum: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case COUNTERED = 'countered';
    case REJECTED = 'rejected';
    case WITHDRAWN = 'withdrawn';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Gaida atbildi',
            self::ACCEPTED => 'Pieņemts',
            self::COUNTERED => 'Pretpiedāvāts',
            self::REJECTED => 'Noraidīts',
            self::WITHDRAWN => 'Atsaukts',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::ACCEPTED, self::REJECTED, self::WITHDRAWN, self::COUNTERED], true);
    }
}
