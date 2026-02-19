<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Review;
use App\Models\JobRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            Review::JOB_REQUEST_ID => JobRequest::factory(),
            Review::REVIEWER_ID => User::factory(),
            Review::REVIEWEE_ID => User::factory(),
            Review::RATING => fake()->numberBetween(1, 5),
            Review::COMMENT => fake()->boolean(80) ? fake()->realText(150) : null, // 80% chance to leave a text comment
        ];
    }
}