<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Profile;

use Spatie\LaravelData\Data;

class UpdateProfileRequestData extends Data
{
    public const NAME = 'name';
    public const EMAIL = 'email';
    public const CITY = 'city';
    public const FIRST_NAME = 'first_name';
    public const LAST_NAME = 'last_name';
    public const COMPANY_NAME = 'company_name';
    public const REG_NUMBER = 'reg_number';
    public const VAT_NUMBER = 'vat_number';
    public const PHONE = 'phone';
    public const BIO = 'bio';

    public string $name;
    public string $email;
    public string $city;
    public ?string $first_name;
    public ?string $last_name;
    public ?string $company_name;
    public ?string $reg_number;
    public ?string $vat_number;
    public ?string $phone;
    public ?string $bio;
}