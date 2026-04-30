<?php

declare(strict_types=1);

namespace App\Services\Jobs;

use App\Enums\Job\JobStatusEnum;
use App\Exceptions\Jobs\InvalidJobTransitionException;

final class JobStateMachine
{
    private const TRANSITIONS = [
        'open'                  => ['accepted', 'cancelled'],
        'accepted'              => ['in_progress', 'cancelled'],
        'in_progress'           => ['awaiting_confirmation'],
        'awaiting_confirmation' => ['completed', 'disputed'],
        'disputed'              => ['completed', 'cancelled'],
        'completed'             => [],
        'cancelled'             => [],
    ];

    public function canTransition(JobStatusEnum $from, JobStatusEnum $to): bool
    {
        return in_array($to->value, self::TRANSITIONS[$from->value] ?? [], true);
    }

    public function assertCanTransition(JobStatusEnum $from, JobStatusEnum $to): void
    {
        if (!$this->canTransition($from, $to)) {
            throw new InvalidJobTransitionException($from, $to);
        }
    }
}
