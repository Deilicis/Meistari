<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Application;
use App\Models\JobRequest;
use App\Models\User;
use App\Enums\Job\ApplicationStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        return [
            Application::JOB_REQUEST_ID => JobRequest::factory(),
            Application::USER_ID => User::factory(),
            Application::COVER_LETTER => fake()->realText(250),
            Application::PRICE_OFFER => fake()->randomFloat(2, 20, 1000),
            Application::STATUS => fake()->randomElement(ApplicationStatusEnum::cases()),
        ];
    }
}