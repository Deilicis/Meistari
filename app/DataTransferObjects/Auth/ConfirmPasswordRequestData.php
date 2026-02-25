<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Auth;

use Spatie\LaravelData\Data;

class ConfirmPasswordRequestData extends Data
{
    public const PASSWORD = 'password';

    public string $password;
}