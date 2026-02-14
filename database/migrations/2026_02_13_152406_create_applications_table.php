<?php

declare(strict_types=1);

use App\Enum\Job\ApplicationStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'applications';
    private const TABLE_USERS = 'users';
    private const TABLE_JOB_REQUESTS = 'job_requests';
    private const ID = 'id';
    private const JOB_REQUEST_ID = 'job_request_id';
    private const USER_ID = 'user_id';
    private const COVER_LETTER = 'cover_letter';
    private const PRICE_OFFER = 'price_offer';
    private const STATUS = 'status';
    private const CREATED_AT = 'created_at';
    private const UPDATED_AT = 'updated_at';
    private const DELETED_AT = 'deleted_at';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->id(self::ID);
            
            $table->foreignId(self::JOB_REQUEST_ID)
                  ->constrained(self::TABLE_JOB_REQUESTS)
                  ->cascadeOnDelete();
            $table->foreignId(self::USER_ID)
                  ->constrained(self::TABLE_USERS)
                  ->cascadeOnDelete();
            $table->text(self::COVER_LETTER);
            $table->decimal(self::PRICE_OFFER, 10, 2);
            $table->enum(self::STATUS, ApplicationStatusEnum::values())
                  ->default(ApplicationStatusEnum::PENDING->value);
            $table->unique([self::JOB_REQUEST_ID, self::USER_ID]);
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