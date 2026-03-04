<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'profiles';
    private const AVATAR = 'avatar';
    private const EXPERIENCES = 'experiences';
    private const EXPERIENCE_YEARS = 'experience_years';

    public function up(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->dropColumn(self::EXPERIENCE_YEARS);
            $table->json(self::EXPERIENCES)->nullable()->after(self::AVATAR);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->dropColumn(self::EXPERIENCES);
            $table->unsignedTinyInteger(self::EXPERIENCE_YEARS)->nullable()->after(self::AVATAR);
        });
    }
};