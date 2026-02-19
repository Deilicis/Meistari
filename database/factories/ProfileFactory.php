<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use App\Enums\Profile\ProfileTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        $type = fake()->randomElement(ProfileTypeEnum::cases());
        $isCompany = $type === ProfileTypeEnum::COMPANY;

        return [
            Profile::USER_ID => User::factory(),
            Profile::TYPE => $type,

            Profile::FIRST_NAME => $isCompany ? null : fake()->firstName(),
            Profile::LAST_NAME => $isCompany ? null : fake()->lastName(),

            Profile::COMPANY_NAME => $isCompany ? fake()->company() : null,
            Profile::REG_NUMBER => $isCompany ? (string) fake()->randomNumber(9, true) : null,
            Profile::VAT_NUMBER => $isCompany ? 'LV' . fake()->randomNumber(9, true) : null,
            
            Profile::CITY => fake()->city(),
            Profile::PHONE => fake()->phoneNumber(),
            Profile::BIO => fake()->realText(200),
            Profile::IS_VERIFIED => fake()->boolean(20),
        ];
    }
}