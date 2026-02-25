<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Auth;

use App\Enums\Profile\ProfileTypeEnum;
use App\Enums\Role\RoleNameEnum;
use Spatie\LaravelData\Data;

class RegisterUserRequestData extends Data
{
    public const NAME = 'name';
    public const EMAIL = 'email';
    public const PASSWORD = 'password';
    public const ROLE = 'role';
    public const PROFILE_TYPE = 'profile_type';
    public const CITY = 'city';
    public const FIRST_NAME = 'first_name';
    public const LAST_NAME = 'last_name';
    public const COMPANY_NAME = 'company_name';
    public const REG_NUMBER = 'reg_number';

    public string $name;
    public string $email;
    public string $password;
    public RoleNameEnum $role;
    public ProfileTypeEnum $profile_type;
    public string $city;
    public ?string $first_name;
    public ?string $last_name;
    public ?string $company_name;
    public ?string $reg_number;
}