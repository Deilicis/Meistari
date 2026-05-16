<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('active_role')->nullable()->after('remember_token');
        });

        // Backfill from existing role pivot - master takes priority if user somehow has both
        $masterRoleId = DB::table('roles')->where('name', 'master')->value('id');
        $seekerRoleId = DB::table('roles')->where('name', 'seeker')->value('id');

        if ($seekerRoleId) {
            $seekerUserIds = DB::table('role_user')
                ->where('role_id', $seekerRoleId)
                ->pluck('user_id');

            DB::table('users')
                ->whereIn('id', $seekerUserIds)
                ->update(['active_role' => 'seeker']);
        }

        if ($masterRoleId) {
            $masterUserIds = DB::table('role_user')
                ->where('role_id', $masterRoleId)
                ->pluck('user_id');

            DB::table('users')
                ->whereIn('id', $masterUserIds)
                ->update(['active_role' => 'master']);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('active_role');
        });
    }
};
