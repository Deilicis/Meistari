<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'job_requests';
    private const LOCATION = 'location';
    private const IMAGES = 'images';
    private const DELETED_AT = 'deleted_at';
    private const DEADLINE = 'deadline';

    public function up(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            if (Schema::hasColumn(self::TABLE, self::LOCATION)) {
                $table->dropColumn(self::LOCATION);
            }
            $table->json(self::LOCATION)->nullable()->after(self::DEADLINE);
            
            $table->json(self::IMAGES)->nullable()->after(self::LOCATION);
            
            if (!Schema::hasColumn(self::TABLE, self::DELETED_AT)) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->dropColumn([self::IMAGES]);
            
            if (Schema::hasColumn(self::TABLE, self::DELETED_AT)) {
                $table->dropSoftDeletes();
            }
            
            if (Schema::hasColumn(self::TABLE, self::LOCATION)) {
                $table->dropColumn(self::LOCATION);
            }
            $table->string(self::LOCATION)->nullable()->after(self::DEADLINE);
        });
    }
};