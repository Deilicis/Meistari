<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'users';
    private const TABLE_SESSIONS = 'sessions';
    private const TABLE_RESET_TOKENS = 'password_reset_tokens';
    private const ID = 'id';
    private const NAME = 'name';
    private const EMAIL = 'email';
    private const EMAIL_VERIFIED_AT = 'email_verified_at';
    private const PASSWORD = 'password';
    private const REMEMBER_TOKEN = 'remember_token';
    private const CREATED_AT = 'created_at';
    private const UPDATED_AT = 'updated_at';
    private const DELETED_AT = 'deleted_at';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->id(self::ID);
            $table->string(self::NAME);
            $table->string(self::EMAIL)->unique();
            $table->timestamp(self::EMAIL_VERIFIED_AT)->nullable();
            $table->string(self::PASSWORD);
            $table->string(self::REMEMBER_TOKEN, 100)->nullable();
            $table->timestamp(self::CREATED_AT)->useCurrent();
            $table->timestamp(self::UPDATED_AT)->nullable()->useCurrentOnUpdate();
            $table->softDeletes(self::DELETED_AT);
        });

        Schema::create(self::TABLE_RESET_TOKENS, function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create(self::TABLE_SESSIONS, function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
        Schema::dropIfExists(self::TABLE_RESET_TOKENS);
        Schema::dropIfExists(self::TABLE_SESSIONS);
    }
};