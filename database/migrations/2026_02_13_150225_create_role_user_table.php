<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'role_user';
    private const TABLE_USERS = 'users';
    private const TABLE_ROLES = 'roles';
    private const USER_ID = 'user_id';
    private const ROLE_ID = 'role_id';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->foreignId(self::USER_ID)
                  ->constrained(self::TABLE_USERS)
                  ->cascadeOnDelete();
            $table->foreignId(self::ROLE_ID)
                  ->constrained(self::TABLE_ROLES)
                  ->cascadeOnDelete();
            $table->primary([self::USER_ID, self::ROLE_ID]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }
};