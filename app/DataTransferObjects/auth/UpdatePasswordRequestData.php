<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Auth;

use Spatie\LaravelData\Data;

class UpdatePasswordRequestData extends Data
{
    public const CURRENT_PASSWORD = 'current_password';
    public const PASSWORD = 'password';

    public string $current_password;
    public string $password;
}