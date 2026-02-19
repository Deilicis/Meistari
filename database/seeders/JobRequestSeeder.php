<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\JobRequest;
use App\Models\Role;
use App\Models\Category;
use App\Enums\Role\RoleNameEnum;
use Illuminate\Database\Seeder;

class JobRequestSeeder extends Seeder
{
    public function run(): void
    {
        $seekerRole = Role::where(Role::NAME, RoleNameEnum::SEEKER->value)->first();
        $seekers = $seekerRole->users()->get();
        $categories = Category::all();

        if ($seekers->isEmpty() || $categories->isEmpty()) {
            return;
        }

        foreach ($seekers as $seeker) {
            JobRequest::factory(random_int(1, 3))->create([
                JobRequest::USER_ID => $seeker->getId(),
                JobRequest::CATEGORY_ID => $categories->random()->getId(),
            ]);
        }
    }
}