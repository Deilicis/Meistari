<?php

declare(strict_types=1);

use App\Enums\RoleNameEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'roles';
    private const ID = 'id';
    private const NAME = 'name';
    private const CREATED_AT = 'created_at';
    private const UPDATED_AT = 'updated_at';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->id(self::ID);
            $table->enum(self::NAME, RoleNameEnum::values())->unique();
            $table->timestamp(self::CREATED_AT)->useCurrent();
            $table->timestamp(self::UPDATED_AT)->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }
};