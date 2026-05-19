<?php

declare(strict_types=1);

namespace App\Enums;

enum MessageTypeEnum: string
{
    case TEXT = 'text';
    case PROPOSAL = 'proposal';
}
