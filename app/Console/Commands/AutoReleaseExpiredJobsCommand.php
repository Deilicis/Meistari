<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Jobs\JobLifecycleService;
use Illuminate\Console\Command;

class AutoReleaseExpiredJobsCommand extends Command
{
    protected $signature   = 'jobs:auto-release';
    protected $description = 'Auto-release escrow for jobs awaiting confirmation past 7 days';

    public function handle(JobLifecycleService $service): int
    {
        $count = $service->autoReleaseExpired();
        $this->info("Auto-released {$count} jobs.");

        return self::SUCCESS;
    }
}
