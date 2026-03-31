<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TABLE = 'roles';
    private const COLUMN = 'name';

    public function up(): void
    {
        DB::statement(
            "ALTER TABLE `" . self::TABLE . "` MODIFY COLUMN `" . self::COLUMN . "` ENUM('admin', 'master', 'seeker', 'moderator') NOT NULL"
        );
    }

    public function down(): void
    {
        DB::table(self::TABLE)->where(self::COLUMN, 'moderator')->delete();

        DB::statement(
            "ALTER TABLE `" . self::TABLE . "` MODIFY COLUMN `" . self::COLUMN . "` ENUM('admin', 'master', 'seeker') NOT NULL"
        );
    }
};
