<?php

declare(strict_types=1);

use App\Enums\Job\ApplicationStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TABLE = 'service_applications';
    private const COLUMN = 'status';

    public function up(): void
    {
        $values = implode("','", ApplicationStatusEnum::values());
        DB::statement("ALTER TABLE `" . self::TABLE . "` MODIFY COLUMN `" . self::COLUMN . "` ENUM('{$values}') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `" . self::TABLE . "` MODIFY COLUMN `" . self::COLUMN . "` ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending'");
    }
};
