<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'conversations';
    private const TABLE_USERS = 'users';
    private const ID = 'id';
    private const SENDER_ID = 'sender_id';
    private const RECEIVER_ID = 'receiver_id';
    private const CREATED_AT = 'created_at';
    private const UPDATED_AT = 'updated_at';
    private const DELETED_AT = 'deleted_at';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->id(self::ID);
            $table->foreignId(self::SENDER_ID)
                  ->constrained(self::TABLE_USERS)
                  ->cascadeOnDelete();
            $table->foreignId(self::RECEIVER_ID)
                  ->constrained(self::TABLE_USERS)
                  ->cascadeOnDelete();
            $table->unique([self::SENDER_ID, self::RECEIVER_ID]);
            $table->timestamp(self::CREATED_AT)->useCurrent();
            $table->timestamp(self::UPDATED_AT)->nullable()->useCurrentOnUpdate();
            $table->softDeletes(self::DELETED_AT);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }
};