<?php

declare(strict_types=1);

use App\Enum\Service\ServicePriceTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'services';
    private const TABLE_USERS = 'users';
    private const TABLE_CATEGORIES = 'categories';
    private const ID = 'id';
    private const USER_ID = 'user_id';
    private const CATEGORY_ID = 'category_id';
    private const TITLE = 'title';
    private const SLUG = 'slug';
    private const DESCRIPTION = 'description';
    private const PRICE = 'price';
    private const PRICE_TYPE = 'price_type';
    private const LOCATION = 'location';
    private const IS_ACTIVE = 'is_active';
    private const CREATED_AT = 'created_at';
    private const UPDATED_AT = 'updated_at';
    private const DELETED_AT = 'deleted_at';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->id(self::ID);

            $table->foreignId(self::USER_ID)
                  ->constrained(self::TABLE_USERS)
                  ->cascadeOnDelete();

            $table->foreignId(self::CATEGORY_ID)
                  ->constrained(self::TABLE_CATEGORIES)
                  ->cascadeOnDelete();

            $table->string(self::TITLE);
            $table->string(self::SLUG)->unique();
            $table->text(self::DESCRIPTION);
            $table->decimal(self::PRICE, 10, 2)->nullable();
            $table->enum(self::PRICE_TYPE, ServicePriceTypeEnum::values())
                  ->default(ServicePriceTypeEnum::HOURLY->value);
            $table->string(self::LOCATION);
            $table->boolean(self::IS_ACTIVE)->default(true);

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