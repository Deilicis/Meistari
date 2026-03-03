<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'profiles';
    private const AVATAR = 'avatar';
    private const DESCRIPTION = 'description';
    private const EXPERIENCE_YEARS = 'experience_years';
    private const PORTFOLIO_IMAGES = 'portfolio_images';

    public function up(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->string(self::AVATAR)->nullable()->after(self::DESCRIPTION);
            $table->unsignedTinyInteger(self::EXPERIENCE_YEARS)->nullable()->after(self::AVATAR);
            $table->json(self::PORTFOLIO_IMAGES)->nullable()->after(self::EXPERIENCE_YEARS);
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->dropColumn([
                self::AVATAR,
                self::EXPERIENCE_YEARS,
                self::PORTFOLIO_IMAGES
            ]);
        });
    }
};