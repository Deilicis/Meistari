<?php

declare(strict_types=1);

namespace App\Exceptions\Categories;

use RuntimeException;

class CategoryMergeConflictException extends RuntimeException
{
    public function __construct(string $message = 'Nevar apvienot kategorijas konflikta dēļ.')
    {
        parent::__construct($message);
    }
}
