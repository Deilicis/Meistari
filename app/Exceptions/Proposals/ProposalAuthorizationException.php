<?php

declare(strict_types=1);

namespace App\Exceptions\Proposals;

use RuntimeException;

class ProposalAuthorizationException extends RuntimeException
{
    public function __construct(string $message = 'Tev nav tiesību veikt šo darbību ar šo piedāvājumu.')
    {
        parent::__construct($message);
    }
}
