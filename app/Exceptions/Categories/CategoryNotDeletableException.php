<?php

declare(strict_types=1);

namespace App\Exceptions\Categories;

use RuntimeException;

class CategoryNotDeletableException extends RuntimeException
{
    public function __construct(string $message = 'Šo kategoriju nevar dzēst.')
    {
        parent::__construct($message);
    }
}
