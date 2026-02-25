<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Auth;

use Spatie\LaravelData\Data;

class LoginUserRequestData extends Data
{
    public const EMAIL = 'email';
    public const PASSWORD = 'password';
    public const REMEMBER = 'remember';

    public string $email;
    public string $password;
    public bool $remember = false;
}