<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'messages';
    private const TABLE_CONVERSATIONS = 'conversations';
    private const TABLE_USERS = 'users';
    private const ID = 'id';
    private const CONVERSATION_ID = 'conversation_id';
    private const SENDER_ID = 'sender_id';
    private const BODY = 'body';
    private const READ_AT = 'read_at';
    private const CREATED_AT = 'created_at';
    private const UPDATED_AT = 'updated_at';
    private const DELETED_AT = 'deleted_at';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->id(self::ID);
            $table->foreignId(self::CONVERSATION_ID)
                  ->constrained(self::TABLE_CONVERSATIONS)
                  ->cascadeOnDelete();
            $table->foreignId(self::SENDER_ID)
                  ->constrained(self::TABLE_USERS)
                  ->cascadeOnDelete();
            $table->text(self::BODY);
            $table->timestamp(self::READ_AT)->nullable();
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