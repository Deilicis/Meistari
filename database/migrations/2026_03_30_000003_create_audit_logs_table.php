<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'audit_logs';
    private const USERS_TABLE = 'users';
    private const USER_ID = 'user_id';
    private const ACTION = 'action';
    private const AUDITABLE_TYPE = 'auditable_type';
    private const AUDITABLE_ID = 'auditable_id';
    private const OLD_VALUES = 'old_values';
    private const NEW_VALUES = 'new_values';
    private const IP_ADDRESS = 'ip_address';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->id();
            $table->foreignId(self::USER_ID)->nullable()->constrained(self::USERS_TABLE)->nullOnDelete();
            $table->string(self::ACTION);
            $table->string(self::AUDITABLE_TYPE);
            $table->unsignedBigInteger(self::AUDITABLE_ID);
            $table->json(self::OLD_VALUES)->nullable();
            $table->json(self::NEW_VALUES)->nullable();
            $table->string(self::IP_ADDRESS)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index([self::AUDITABLE_TYPE, self::AUDITABLE_ID]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }
};
