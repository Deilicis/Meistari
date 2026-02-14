<?php

declare(strict_types=1);

use App\Enum\Profile\ProfileTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'profiles';
    private const TABLE_USERS = 'users';
    private const ID = 'id';
    private const USER_ID = 'user_id';
    private const TYPE = 'type';
    private const FIRST_NAME = 'first_name';
    private const LAST_NAME = 'last_name';
    private const COMPANY_NAME = 'company_name';
    private const REG_NUMBER = 'reg_number';
    private const VAT_NUMBER = 'vat_number';
    private const CITY = 'city';
    private const PHONE = 'phone';
    private const BIO = 'bio';
    private const IS_VERIFIED = 'is_verified';
    private const CREATED_AT = 'created_at';
    private const UPDATED_AT = 'updated_at';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->id(self::ID);
            $table->foreignId(self::USER_ID)
                  ->unique()
                  ->constrained(self::TABLE_USERS)
                  ->cascadeOnDelete();
            $table->enum(self::TYPE, ProfileTypeEnum::values());
            $table->string(self::FIRST_NAME)->nullable();
            $table->string(self::LAST_NAME)->nullable();
            $table->string(self::COMPANY_NAME)->nullable();
            $table->string(self::REG_NUMBER)->nullable();
            $table->string(self::VAT_NUMBER)->nullable();
            $table->string(self::CITY);
            $table->string(self::PHONE)->nullable();
            $table->text(self::BIO)->nullable();
            $table->boolean(self::IS_VERIFIED)->default(false);
            $table->timestamp(self::CREATED_AT)->useCurrent();
            $table->timestamp(self::UPDATED_AT)->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }
};