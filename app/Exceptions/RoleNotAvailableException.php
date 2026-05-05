<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class RoleNotAvailableException extends Exception
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: 'Jums nav šīs lomas.');
    }
}
