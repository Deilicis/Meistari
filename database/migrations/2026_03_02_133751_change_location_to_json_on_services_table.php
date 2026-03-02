<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'services';
    private const ID = 'id';
    private const LOCATION = 'location';

    public function up(): void
    {
        DB::table(self::TABLE)->orderBy(self::ID)->chunk(100, function ($services) {
            foreach ($services as $service) {
                if (is_string($service->location) && !str_starts_with($service->location, '[')) {
                    DB::table(self::TABLE)
                        ->where(self::ID, $service->id)
                        ->update([
                            self::LOCATION => json_encode([$service->location], JSON_UNESCAPED_UNICODE)
                        ]);
                }
            }
        });

        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->json(self::LOCATION)->change();
        });
    }

    public function down(): void
    {
        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->string(self::LOCATION)->change();
        });
    }
};