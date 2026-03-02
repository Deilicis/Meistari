<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Service;
use App\Models\User;
use App\Models\Category;
use App\Enums\Service\ServicePriceTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $title = fake()->words(3, true) . ' pakalpojumi';

        return [
            Service::USER_ID => User::factory(),
            Service::CATEGORY_ID => Category::factory(),
            Service::TITLE => ucfirst($title),
            Service::SLUG => Str::slug($title) . '-' . fake()->unique()->numberBetween(1000, 9999),
            Service::DESCRIPTION => fake()->realText(300),
            Service::PRICE => fake()->randomFloat(2, 15, 200),
            Service::PRICE_TYPE => fake()->randomElement(ServicePriceTypeEnum::cases()),
            Service::LOCATION => [fake()->city()],
            Service::IS_ACTIVE => fake()->boolean(90),
        ];
    }
}