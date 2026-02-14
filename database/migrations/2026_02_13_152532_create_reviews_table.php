<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'reviews';
    private const TABLE_USERS = 'users';
    private const TABLE_JOB_REQUESTS = 'job_requests';
    private const ID = 'id';
    private const JOB_REQUEST_ID = 'job_request_id';
    private const REVIEWER_ID = 'reviewer_id';
    private const REVIEWEE_ID = 'reviewee_id';
    private const RATING = 'rating';
    private const COMMENT = 'comment';
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
            $table->foreignId(self::REVIEWER_ID)
                  ->constrained(self::TABLE_USERS)
                  ->cascadeOnDelete();
            $table->foreignId(self::REVIEWEE_ID)
                  ->constrained(self::TABLE_USERS)
                  ->cascadeOnDelete();
            $table->unsignedTinyInteger(self::RATING);
            $table->text(self::COMMENT)->nullable();
            $table->unique([self::JOB_REQUEST_ID, self::REVIEWER_ID, self::REVIEWEE_ID]);
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