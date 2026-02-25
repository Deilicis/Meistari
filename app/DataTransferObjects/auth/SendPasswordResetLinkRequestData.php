<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Auth;

use Spatie\LaravelData\Data;

class SendPasswordResetLinkRequestData extends Data
{
    public const EMAIL = 'email';

    public string $email;
}