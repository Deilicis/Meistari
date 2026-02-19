<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Review;
use App\Models\JobRequest;
use App\Models\User;
use App\Models\Application;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $jobRequests = JobRequest::has('applications')->with(['applications.user', 'user'])->get();

        if ($jobRequests->isEmpty()) {
            return;
        }

        foreach ($jobRequests as $job) {
            /** @var JobRequest $job */
            /** @var Application|null $application */
            $application = $job->applications()->first();

            if (!$application) {
                continue;
            }
            
            /** @var User|null $master */
            $master = $application->user()->first();
            
            /** @var User|null $seeker */
            $seeker = $job->user()->first();

            if (!$master || !$seeker) {
                continue;
            }

            Review::factory()->create([
                Review::JOB_REQUEST_ID => $job->getId(),
                Review::REVIEWER_ID => $seeker->getId(),
                Review::REVIEWEE_ID => $master->getId(),
            ]);

            if (fake()->boolean()) {
                Review::factory()->create([
                    Review::JOB_REQUEST_ID => $job->getId(),
                    Review::REVIEWER_ID => $master->getId(),
                    Review::REVIEWEE_ID => $seeker->getId(),
                ]);
            }
        }
    }
}