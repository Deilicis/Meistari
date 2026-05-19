<?php

declare(strict_types=1);

namespace App\Enums;

enum EscrowStatusEnum: string
{
    case HELD = 'held';
    case RELEASED = 'released';
    case REFUNDED = 'refunded';
}
