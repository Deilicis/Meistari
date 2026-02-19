<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Role;
use App\Models\Category;
use App\Enums\Role\RoleNameEnum;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $masterRole = Role::where(Role::NAME, RoleNameEnum::MASTER->value)->first();
        $masters = $masterRole->users()->get();
        $categories = Category::all();

        if ($masters->isEmpty() || $categories->isEmpty()) {
            return;
        }

        foreach ($masters as $master) {
            Service::factory(random_int(1, 3))->create([
                Service::USER_ID => $master->getId(),
                Service::CATEGORY_ID => $categories->random()->getId(),
            ]);
        }
    }
}