<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Application;
use App\Models\JobRequest;
use App\Models\Role;
use App\Enums\Role\RoleNameEnum;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $masterRole = Role::where(Role::NAME, RoleNameEnum::MASTER->value)->first();
        $masters = $masterRole->users()->get();
        $jobRequests = JobRequest::all();

        if ($masters->isEmpty() || $jobRequests->isEmpty()) {
            return;
        }

        foreach ($jobRequests as $job) {
            $randomMasters = $masters->random(random_int(1, min(3, $masters->count())));

            foreach ($randomMasters as $master) {
                Application::factory()->create([
                    Application::JOB_REQUEST_ID => $job->getId(),
                    Application::USER_ID => $master->getId(),
                ]);
            }
        }
    }
}