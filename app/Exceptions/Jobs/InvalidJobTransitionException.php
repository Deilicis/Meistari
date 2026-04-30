<?php

declare(strict_types=1);

namespace App\Exceptions\Jobs;

use App\Enums\Job\JobStatusEnum;
use RuntimeException;

class InvalidJobTransitionException extends RuntimeException
{
    public function __construct(
        public readonly JobStatusEnum $from,
        public readonly JobStatusEnum $to,
    ) {
        parent::__construct(
            sprintf(
                'Cannot transition job from "%s" to "%s".',
                $from->value,
                $to->value,
            )
        );
    }
}
