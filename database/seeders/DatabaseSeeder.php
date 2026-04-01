<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\Role;
use App\Enums\Role\RoleNameEnum;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
        ]);

        $adminRole     = Role::where(Role::NAME, RoleNameEnum::ADMIN->value)->first();
        $moderatorRole = Role::where(Role::NAME, RoleNameEnum::MODERATOR->value)->first();
        $masterRole    = Role::where(Role::NAME, RoleNameEnum::MASTER->value)->first();
        $seekerRole    = Role::where(Role::NAME, RoleNameEnum::SEEKER->value)->first();

        $admin = User::factory()->create([
            User::NAME     => 'Administrators',
            User::EMAIL    => 'admin@meistari.lv',
            User::PASSWORD => bcrypt('password'),
        ]);
        $admin->roles()->attach($adminRole);

        Profile::factory()->create([
            Profile::USER_ID => $admin->getId(),
        ]);

        $moderator = User::factory()->create([
            User::NAME     => 'Moderators',
            User::EMAIL    => 'moderator@meistari.lv',
            User::PASSWORD => bcrypt('password'),
        ]);
        $moderator->roles()->attach($moderatorRole);

        Profile::factory()->create([
            Profile::USER_ID => $moderator->getId(),
        ]);

        $master = User::factory()->create([
            User::NAME     => 'Meistars Tests',
            User::EMAIL    => 'meistars@meistari.lv',
            User::PASSWORD => bcrypt('password'),
        ]);
        $master->roles()->attach($masterRole);
        Profile::factory()->forMaster()->create([
            Profile::USER_ID => $master->getId(),
        ]);

        $seeker = User::factory()->create([
            User::NAME     => 'Meklētājs Tests',
            User::EMAIL    => 'mekletajs@meistari.lv',
            User::PASSWORD => bcrypt('password'),
        ]);
        $seeker->roles()->attach($seekerRole);
        Profile::factory()->create([
            Profile::USER_ID => $seeker->getId(),
        ]);

        User::factory(10)->create()->each(function (User $user) use ($masterRole) {
            $user->roles()->attach($masterRole);
            Profile::factory()->forMaster()->create([
                Profile::USER_ID => $user->getId()
            ]);
        });

        User::factory(10)->create()->each(function (User $user) use ($seekerRole) {
            $user->roles()->attach($seekerRole);
            Profile::factory()->create([
                Profile::USER_ID => $user->getId()
            ]);
        });

        $this->call([
            JobRequestSeeder::class,
            ServiceSeeder::class,
            ApplicationSeeder::class,
            ReviewSeeder::class,
            ConversationSeeder::class,
        ]);
    }
}