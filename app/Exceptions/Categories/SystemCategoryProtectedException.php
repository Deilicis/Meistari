<?php

declare(strict_types=1);

namespace App\Exceptions\Categories;

use RuntimeException;

class SystemCategoryProtectedException extends RuntimeException
{
    public function __construct(string $message = 'Sistēmas kategoriju nevar modificēt šādā veidā.')
    {
        parent::__construct($message);
    }
}
