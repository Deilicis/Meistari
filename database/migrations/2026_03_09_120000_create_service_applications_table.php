<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\Job\ApplicationStatusEnum;

return new class extends Migration
{
    private const TABLE = 'service_applications';
    private const ID = 'id';
    private const SERVICE_ID = 'service_id';
    private const USER_ID = 'user_id';
    private const MESSAGE = 'message';
    private const BUDGET_OFFER = 'budget_offer';
    private const STATUS = 'status';
    private const CREATED_AT = 'created_at';
    private const DELETED_AT = 'deleted_at';

    private const SERVICES_TABLE = 'services';
    private const USERS_TABLE = 'users';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->id();
            $table->foreignId(self::SERVICE_ID)->constrained(self::SERVICES_TABLE)->cascadeOnDelete();
            $table->foreignId(self::USER_ID)->constrained(self::USERS_TABLE)->cascadeOnDelete();
            $table->text(self::MESSAGE);
            $table->decimal(self::BUDGET_OFFER, 10, 2)->nullable();
            $table->enum(self::STATUS, [ApplicationStatusEnum::PENDING, ApplicationStatusEnum::ACCEPTED, ApplicationStatusEnum::REJECTED])->default(ApplicationStatusEnum::PENDING);
            $table->softDeletes();
            $table->timestamps();

            $table->unique([self::SERVICE_ID, self::USER_ID]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }
};
