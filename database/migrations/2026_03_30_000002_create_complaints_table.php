<?php

use App\Enums\Complaint\ComplaintStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'complaints';
    private const USERS_TABLE = 'users';
    private const REPORTER_ID = 'reporter_id';
    private const REPORTED_USER_ID = 'reported_user_id';
    private const ENTITY_TYPE = 'reported_entity_type';
    private const ENTITY_ID = 'reported_entity_id';
    private const REASON = 'reason';
    private const STATUS = 'status';
    private const RESOLVED_BY = 'resolved_by';
    private const RESOLUTION_NOTE = 'resolution_note';
    private const RESOLVED_AT = 'resolved_at';

    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->id();
            $table->foreignId(self::REPORTER_ID)->constrained(self::USERS_TABLE)->cascadeOnDelete();
            $table->foreignId(self::REPORTED_USER_ID)->constrained(self::USERS_TABLE)->cascadeOnDelete();
            $table->string(self::ENTITY_TYPE)->nullable();
            $table->unsignedBigInteger(self::ENTITY_ID)->nullable();
            $table->text(self::REASON);
            $table->enum(self::STATUS, ComplaintStatusEnum::values())->default(ComplaintStatusEnum::PENDING->value);
            $table->foreignId(self::RESOLVED_BY)->nullable()->constrained(self::USERS_TABLE)->nullOnDelete();
            $table->text(self::RESOLUTION_NOTE)->nullable();
            $table->timestamp(self::RESOLVED_AT)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }
};
