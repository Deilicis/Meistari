<?php

declare(strict_types=1);

namespace App\Exceptions\Proposals;

use RuntimeException;

class ApplicationNotNegotiableException extends RuntimeException
{
    public function __construct(string $message = 'Šis pieteikums vairs nav aktīvs sarunai.')
    {
        parent::__construct($message);
    }
}
