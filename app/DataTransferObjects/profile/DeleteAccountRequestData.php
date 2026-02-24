<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Profile;

use Spatie\LaravelData\Data;

class DeleteAccountRequestData extends Data
{
    public const PASSWORD = 'password';

    public string $password;
}