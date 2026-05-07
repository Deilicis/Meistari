<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\JobRequest;
use App\Models\User;
use App\Models\Category;
use App\Enums\Job\JobStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class JobRequestFactory extends Factory
{
    protected $model = JobRequest::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            JobRequest::USER_ID => User::factory(),
            JobRequest::CATEGORY_ID => Category::factory(),
            JobRequest::TITLE => $title,
            JobRequest::SLUG => Str::slug($title) . '-' . fake()->unique()->numberBetween(1000, 9999),
            JobRequest::DESCRIPTION => fake()->realText(300),
            JobRequest::BUDGET => fake()->randomFloat(2, 50, 1500),
            JobRequest::LOCATION => [fake()->city()],
            JobRequest::DEADLINE => fake()->dateTimeBetween('now', '+2 months')->format('Y-m-d'),
            JobRequest::STATUS => JobStatusEnum::OPEN,
        ];
    }
}