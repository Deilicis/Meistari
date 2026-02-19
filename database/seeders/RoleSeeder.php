<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Enums\Role\RoleNameEnum;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RoleNameEnum::cases() as $role) {
            Role::updateOrCreate(
                [Role::NAME => $role->value],
                [Role::NAME => $role]
            );
        }
    }
}