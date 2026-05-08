<?php

declare(strict_types=1);

namespace App\Exceptions\Proposals;

use RuntimeException;

class InvalidProposalActionException extends RuntimeException
{
    public function __construct(string $message = 'Šo darbību nevar veikt ar šo piedāvājumu.')
    {
        parent::__construct($message);
    }
}
