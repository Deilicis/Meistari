<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Auth;

use Spatie\LaravelData\Data;

class ResetPasswordRequestData extends Data
{
    public const TOKEN = 'token';
    public const EMAIL = 'email';
    public const PASSWORD = 'password';

    public string $token;
    public string $email;
    public string $password;
}