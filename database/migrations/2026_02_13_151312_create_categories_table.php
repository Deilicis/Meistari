<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'categories';
    private const ID = 'id';
    private const NAME = 'name';
    private const SLUG = 'slug';
    private const ICON = 'icon';
    private const PARENT_ID = 'parent_id';
    private const CREATED_AT = 'created_at';
    private const UPDATED_AT = 'updated_at';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->id(self::ID);
            $table->string(self::NAME);
            $table->string(self::SLUG)->unique();
            $table->string(self::ICON)->nullable();
            $table->foreignId(self::PARENT_ID)
                  ->nullable()
                  ->constrained(self::TABLE)
                  ->nullOnDelete();
            $table->timestamp(self::CREATED_AT)->useCurrent();
            $table->timestamp(self::UPDATED_AT)->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }
};