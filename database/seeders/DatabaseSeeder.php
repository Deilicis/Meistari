<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ([
            'notifications',
            'audit_logs',
            'messages',
            'conversations',
            'reviews',
            'job_disputes',
            'escrow_holds',
            'price_proposals',
            'service_applications',
            'applications',
            'job_requests',
            'services',
            'category_suggestions',
            'categories',
            'profiles',
            'role_user',
            'roles',
            'users',
        ] as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            SystemCategorySeeder::class,
            DemoSeeder::class,
        ]);
    }
}
