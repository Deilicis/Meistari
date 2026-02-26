<?php

declare(strict_types=1);

namespace App\Helpers;

class ValidationRuleHelper
{
    public const REQUIRED = 'required';
    public const NULLABLE = 'nullable';
    public const STRING = 'string';
    public const EMAIL = 'email';
    public const LOWERCASE = 'lowercase';
    public const CONFIRMED = 'confirmed';
    public const MAX_255 = 'max:255';
    public const BOOLEAN = 'boolean';
    public const CURRENT_PASSWORD = 'current_password';
    public const INTEGER = 'integer';
    public const UNIQUE = 'unique';
    public const EXISTS = 'exists';
    public const DATE = 'date';
    public const NUMERIC = 'numeric';
}